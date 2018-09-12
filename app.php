#!/usr/bin/env php
<?php

use Ever2BoostPHP\Command\Ever2BoostPHP;
use Symfony\Component\Console\Application;

require __DIR__.'/vendor/autoload.php';

$command = new Ever2BoostPHP();
$application = new Application(Ever2BoostPHP::class, Ever2BoostPHP::VERSION);
$application->add($command);
$application->setDefaultCommand($command->getName(), true);
$application->run();
