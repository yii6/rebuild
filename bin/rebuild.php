<?php

declare(strict_types=1);

use Rebuild\Command\StartCommand;
use Rebuild\Config\ConfigFactory;
use Symfony\Component\Console\Application;

require_once 'vendor/autoload.php';
// php bin/rebuild.php
$index = new \App\Controller\IndexController();
echo $index->index();
exit;
!defined('BASE_PATH') && define('BASE_PATH', dirname(__DIR__, 1));




$application = new Application();
$config = new ConfigFactory();
$config = $config();
$commands = $config->get('commands');
foreach ($commands as $command) {
    if ($command === StartCommand::class) {
        $application->add(new StartCommand($config));
    } else {
        $application->add(new $command());
    }
}
$application->run();
