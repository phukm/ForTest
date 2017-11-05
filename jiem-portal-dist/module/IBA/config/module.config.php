<?php
$env = getenv('APP_ENV') ?  : 'production';
return array(
    'controllers' => array(
        'invokables' => array(),
        // 'IBA\Controller\IBA' => 'IBA\Controller\IBAController'
        
        'factories' => array(
            'IBA\Controller\IBA' => 'IBA\Factory\IBAControllerFactory'
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'IBA\Service\IBAServiceInterface' => 'IBA\Service\IBAService',
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
            'i-b-a' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/iba',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'IBA\Controller',
                        'controller' => 'IBA',
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
                            'route' => '/[:controller[/:action][/:id][/back[/:back]][?token=:token]][/po[/:po]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                                'back' => '[0-9]+',
                                'token' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'po' => '[0-9]+',
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
            'IBA' => __DIR__ . '/../view'
        )
    ),
);