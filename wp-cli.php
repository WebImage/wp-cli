#!/bin/env php
<?php

use App\WpCli\Application;
use WebImage\Config\Config;

require(__DIR__ . '/vendor/autoload.php');

$app = Application::create(new Config(require __DIR__ . '/app/config/config.php'));
$app->run();

echo 'Finished' . PHP_EOL;