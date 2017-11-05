<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'HomePage\Controller\Index' => 'HomePage\Controller\IndexController'
        ),
        'factories' => array(
            'HomePage\Controller\HomePage' => 'HomePage\Factory\HomeControllerFactory'
        )
    ),
    'router' => array(
        'routes' => array(
            'home-page' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/homepage',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'HomePage\Controller',
                        'controller' => 'HomePage',
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
                            'route' => '/[:controller[/:action][/year[/:year]][/kai[/:kai]][/kyu[/:kyu]][/orgscy[/:orgscy]][/key[/:key]][/ord[/:ord]][/page[/:page]][/search/:search]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'type' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array()
                        )
                    )
                )
            ),
            'download-eiken-id' => array(
                'type' => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/download-eiken-id-and-password[/year[/:year]][/kai[/:kai]]',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'HomePage\Controller',
                        'controller' => 'HomePage',
                        'action' => 'downloadEikenId'
                    )
                ),
            )
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'HomePage\Service\HomeServiceInterface' => 'HomePage\Service\HomeService',
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
            'HomePage' => __DIR__ . '/../view'
        )
    ),
    'view_helpers' => array(
        'invokables' => array(
            'EikenPaginationHelper' => 'Eiken\Helper\PaginationHelper'
        )
    ),
);
