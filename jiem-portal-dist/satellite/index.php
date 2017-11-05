<?php
//require_once '../maintenance.php';
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));
// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}
defined('BASE_PATH') or define('BASE_PATH', realpath(dirname(__DIR__)));
defined('PUBLIC_PATH') or define('PUBLIC_PATH', BASE_PATH . '/satellite');
defined('DATA_PATH') or define('DATA_PATH', BASE_PATH . '/data');
// Setup autoloading
require 'satelliteInit_autoloader.php';
// Run the application!
Zend\Mvc\Application::init(require 'config/satellite.config.php')->run();
