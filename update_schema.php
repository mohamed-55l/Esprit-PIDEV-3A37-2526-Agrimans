<?php
require 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new App\Kernel('dev', true);
$kernel->boot();

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

$application = new Application($kernel);
$application->setAutoExit(false);

$conn = $kernel->getContainer()->get('doctrine')->getConnection();
$conn->executeStatement('SET FOREIGN_KEY_CHECKS = 0;');

$input = new ArrayInput([
    'command' => 'doctrine:schema:update',
    '--force' => true,
]);
$output = new BufferedOutput();
$application->run($input, $output);

$conn->executeStatement('SET FOREIGN_KEY_CHECKS = 1;');

echo $output->fetch();
echo "\nSchema updated successfully.";
