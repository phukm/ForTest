<?php

$env = getenv('APP_ENV') ? : 'production';

return array(
    'controllers' => array(
        'factories' => array(
            'AccessKey\Controller\AccessKey' => 'AccessKey\Factory\AccessKeyControllerFactory'
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'AccessKey\Service\AccessKeyServiceInterface' => 'AccessKey\Service\AccessKeyService',
            'Application\Service\DantaiServiceInterface' => 'Application\Service\DantaiService'
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
    'router' => array(
        'routes' => array(
            'access-key' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/access-key',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'AccessKey\Controller',
                        'controller' => 'AccessKey',
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
                            'route' => '/[:controller[/:action][/:id]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+'
                            ),
                            'defaults' => array()
                        )
                    )
                )
            )
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'AcccesKey' => __DIR__ . '/../view'
        )
    ),
);

