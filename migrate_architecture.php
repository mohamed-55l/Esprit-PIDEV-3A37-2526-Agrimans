<?php

$srcDir = __DIR__ . '/src';
$modulesDir = $srcDir . '/Modules';

if (!is_dir($modulesDir)) {
    die("Modules directory not found.\n");
}

function getFilesRecursively($dir) {
    if (!is_dir($dir)) return [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $files = [];
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() !== '.gitkeep') {
            $files[] = $file->getPathname();
        }
    }
    return $files;
}

$moduleFiles = getFilesRecursively($modulesDir);

// 1. Check for collisions
echo "Checking for collisions...\n";
$destinations = [];
foreach ($moduleFiles as $file) {
    // e.g. path format: src/Modules/Animal/Controller/AnimalController.php
    $relativePath = str_replace(str_replace('\\', '/', $modulesDir) . '/', '', str_replace('\\', '/', $file));
    $parts = explode('/', $relativePath); // [Animal, Controller, AnimalController.php]

    if (count($parts) >= 3) {
        $module = array_shift($parts); // Animal
        // The rest is e.g. Controller/AnimalController.php
        $targetRest = implode('/', $parts);
        $targetFile = $srcDir . '/' . $targetRest;

        if (isset($destinations[$targetFile])) {
            die("Collision detected! Multiple files mapping to: " . $targetFile . "\n");
        }
        if (file_exists($targetFile)) {
            die("Collision detected! File already exists at: " . $targetFile . "\n");
        }
        $destinations[$targetFile] = $file;
    } else {
        echo "Skipping unknown structure: $relativePath\n";
    }
}

// 2. Move files
echo "Moving files...\n";
foreach ($destinations as $targetFile => $sourceFile) {
    $targetDir = dirname($targetFile);
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    rename($sourceFile, $targetFile);
    echo "Moved: " . basename($sourceFile) . "\n";
}

// 3. Update Namespaces and Uses across ALL src files
echo "Updating namespaces and uses in src/...\n";
$allSrcFiles = getFilesRecursively($srcDir);
foreach ($allSrcFiles as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        $content = file_get_contents($file);
        $originalContent = $content;

        // Replace App\Modules\ModName\Folder with App\Folder
        $content = preg_replace('/App\\\\Modules\\\\[A-Za-z0-9_]+\\\\([A-Za-z0-9_]+)/', 'App\\\\$1', $content);

        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            echo "Updated references in: " . basename($file) . "\n";
        }
    }
}
echo "Migration script completed successfully.\n";
