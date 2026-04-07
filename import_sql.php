<?php
require 'vendor/autoload.php';

// Load environment variables
$envFile = '.env';
$lines = file($envFile);
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || strpos($line, '#') === 0) continue;
    
    if (strpos($line, '=') !== false) {
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, '"\'');
    }
}

// Get database connection string
$databaseUrl = $_ENV['DATABASE_URL'] ?? null;
if (!$databaseUrl) {
    die("DATABASE_URL not found in .env file\n");
}

// Parse the DSN
if (preg_match('/mysql:\/\/([^:]+)(?::([^@]*))?@([^:]+):(\d+)\/(.+)\?/', $databaseUrl, $matches)) {
    $user = $matches[1];
    $password = $matches[2] ?? '';
    $host = $matches[3];
    $port = $matches[4];
    $database = $matches[5];
} else {
    die("Invalid DATABASE_URL format\n");
}

// Connect to MySQL
$dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $password ?: null);
    echo "Connected to database: $database\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

// Read and execute SQL file
$sql = file_get_contents('agrimans.sql');
if (!$sql) {
    die("Could not read agrimans.sql\n");
}

// Split by semicolon and execute statements
$statements = array_filter(array_map('trim', explode(';', $sql)));
$count = 0;

foreach ($statements as $statement) {
    if (empty($statement) || strpos($statement, '--') === 0) {
        continue;
    }
    
    try {
        $pdo->exec($statement);
        $count++;
        if ($count % 10 === 0) {
            echo "Executed $count statements...\n";
        }
    } catch (PDOException $e) {
        echo "Warning: " . $e->getMessage() . "\n";
    }
}

echo "\n✓ Successfully imported $count SQL statements!\n";
