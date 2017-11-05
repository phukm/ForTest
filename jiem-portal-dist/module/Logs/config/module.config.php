<?php
$env = getenv('APP_ENV') ?  : 'production';

return array(
    'service_manager' => array(
        'invokables' => array(
            'Logs\Service\ActivityServiceInterface' => 'Logs\Service\ActivityService',
            'Logs\Service\ApplyEikenServiceInterface' => 'Logs\Service\ApplyEikenService'
        )
    ),
    'controllers' => array(
        'factories' => array(
            'Logs\Controller\Activity' => 'Logs\Factory\ActivityControllerFactory',
            'Logs\Controller\ApplyEiken' => 'Logs\Factory\ApplyEikenControllerFactory'
        )
    ),
    'router' => array(
        'routes' => array(
            'logs' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/logs',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Logs\Controller',
                        'controller' => 'Activity',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action][/:id][/page[/:page]][/search/:search][/isExportExcel/:isExportExcel]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                                'page' => '[0-9]+',
                                'isExportExcel' => '[0-9]+'
                            ),
                            'defaults' => array()
                        )
                    )
                )
            )
        )
    ),
    
    'translator' => array(
        'locale' => 'ja_JP',
        'translation_file_patterns' => array(
            array(
                'base_dir' => __DIR__ . '/../languages/phpArray',
                'type' => 'phpArray',
                'pattern' => '%s.php'
            )
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'Logs' => __DIR__ . '/../view'
        )
    ),
);