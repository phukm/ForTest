<?php
return array(
    'controllers' => array(
        'factories' => array(
            'Report\Controller\Report' => 'Report\Factory\ReportControllerFactory',
            'Report\Controller\EikenLevelRate' => 'Report\Factory\EikenLevelRateControllerFactory',
           )
    ),
    'service_manager' => array(
        'invokables' => array(
            'Report\Service\EikenLevelRateServiceInterface' => 'Report\Service\EikenLevelRateService',
            'Report\Service\ReportServiceInterface' => 'Report\Service\ReportService',
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
            'report' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/report',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Report\Controller',
                        'controller'    => 'Report',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]][/search/:search]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'Report' => __DIR__ . '/../view',
        ),
    ),
);
