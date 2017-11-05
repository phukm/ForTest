<?php
//require_once '../maintenance.php';
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
//ini_set('session.gc_maxlifetime', 30);
//ini_set('session.cookie_lifetime', 30);
chdir(dirname(__DIR__));
// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}
defined('FIX_KEY_1') or define('FIX_KEY_1', 'yLJdqm');
defined('FIX_KEY_2') or define('FIX_KEY_2', '5FPgNi4dO');
defined('FIX_KEY_3') or define('FIX_KEY_3', '6YS5o');
defined('BASE_PATH') or define('BASE_PATH', realpath(dirname(__DIR__)));
defined('PUBLIC_PATH') or define('PUBLIC_PATH', BASE_PATH . '/public');
defined('DATA_PATH') or define('DATA_PATH', BASE_PATH . '/data');
// Setup autoloading
require 'init_autoloader.php';
// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
