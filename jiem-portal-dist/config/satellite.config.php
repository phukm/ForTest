<?php
/**
 * If you need an environment-specific system or application configuration,
 * there is an example in the documentation
 * @see http://framework.zend.com/manual/current/en/tutorials/config.advanced.html#environment-specific-system-configuration
 * @see http://framework.zend.com/manual/current/en/tutorials/config.advanced.html#environment-specific-application-configuration
 */

/**
 * This get predefined evironment from environment variable:
 * - Unix environment variable if is console application
 * - Aapache environment variable defined in htaccess or vhost.conf
 *
 * The available value should be:
 * - development : for developping / debugging / testing purpose
 * - staging : for customer testing purpose
 * - production : by default, deploying to staging / production environment
 */
$env = getenv('APP_ENV') ?  : 'production';

// Please specify installed module the would be exposed into web page
$modules = array(
    'DoctrineModule',
    'DoctrineORMModule',
    'Satellite',
    'DantaiApi'
);

// Inject zend developer tool for debuging / profiling on each developing page
if ($env == 'development') {
    //$modules[] = 'ZendDeveloperTools';
}

return array(
    // This should be an array of module namespaces used in the application.
    'modules' => $modules,
    // These are various options for the listeners attached to the ModuleManager
    'module_listener_options' => array(
        // This should be an array of paths in which modules reside.
        // If a string key is provided, the listener will consider that a module
        // namespace, the value of that key the specific path to that module's
        // Module class.
        'module_paths' => array(
            './module',
            './vendor'
        ),
        
        // An array of paths from which to glob configuration files after
        // modules are loaded. These effectively override configuration
        // provided by modules themselves. Paths may use GLOB_BRACE notation.
        'config_glob_paths' => array(
            // sprintf('config/autoload/{,*.}{global,%s,local}.php', $env),
            // 'config/autoload/{,*.}{global,local}.php',
            'config/autoload/{{,*.}global,{,*.}local}.php',
            'config/autoload/'.$env.'/{{,*.}global,{,*.}local}.php'
        ),
        
        // Whether or not to enable a configuration cache.
        // If enabled, the merged configuration will be cached and used in
        // subsequent requests.
        'config_cache_enabled' => ($env == 'production'),
        
        // The key used to create the configuration cache file name.
        'config_cache_key' => $env . '-jiemdp-config-satellite',
        
        // Whether or not to enable a module class map cache.
        // If enabled, creates a module class map cache which will be used
        // by in future requests, to reduce the autoloading process.
        'module_map_cache_enabled' => ($env == 'production'),
        
        // The key used to create the class map cache file name.
        'module_map_cache_key' => $env . '-jiemdp-module-map-satellite',
        
        // The path in which to cache merged configuration.
        'cache_dir' => 'data/cache/config',
        
        // Whether or not to enable modules dependency checking.
        // Enabled by default, prevents usage of modules that depend on other modules
        // that weren't loaded.
        'check_dependencies' => ($env == 'production')
    )
);
// Used to create an own service manager. May contain one or more child arrays.
// 'service_listener_options' => array(
// array(
// 'service_manager' => $stringServiceManagerName,
// 'config_key' => $stringConfigKey,
// 'interface' => $stringOptionalInterface,
// 'method' => $stringRequiredMethodName,
// ),
// )
// Initial configuration with which to seed the ServiceManager.
// Should be compatible with Zend\ServiceManager\Config.
// 'service_manager' => array(),

