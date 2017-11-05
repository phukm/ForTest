<?php
require 'init_autoloader.php';

use Zend\Mvc\Application;

$application = Application::init(include 'config/application.config.php');

/* @var $cli \Symfony\Component\Console\Application */
$cli = $application->getServiceManager()->get('doctrine.cli');
$cli->run();