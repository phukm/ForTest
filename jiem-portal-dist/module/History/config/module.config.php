<?php
return array(
    'service_manager' => array(
        'invokables' => array(
            'History\Service\EikenHistoryServiceInterface' => 'History\Service\EikenHistoryService',
            'History\Service\IbaHistoryServiceInterface'   => 'History\Service\IbaHistoryService',
            'History\Service\MappingEikenResultService'    => 'History\Service\MappingEikenResultService',
        ),
        'factories' => array(
            'History\Service\MappingEikenResultServiceFactory'    => 'History\Factory\MappingEikenResultServiceFactory',
            'History\Service\MappingIbaResultService'      => 'History\Factory\MappingIbaResultServiceFactory'
        )
    ),
    'controllers' => array(
        'factories' => array(
            'History\Controller\Eiken' => 'History\Factory\EikenHistoryControllerFactory',
            'History\Controller\Iba' => 'History\Factory\IbaHistoryControllerFactory',
        )
    ),
    'router' => array(
        'routes' => array(
            'history' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/history',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'History\Controller',
                        'controller' => 'EikenHistory',
                        'action' => 'exam-result'
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
                            'route' => '/[:controller[/:action][/:id][/year[/:year]][/kai[/:kai]][/jisshiId[/:jisshiId]][/examType[/:examType]][/page[/:page]][/search/:search][/examdate[/:examdate]]][/isExportExcel/:isExportExcel]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                                'page' => '[0-9]+',
                                'year' => '[0-9]+',
                                'kai' => '[0-9]+'
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
            'History' => __DIR__ . '/../view'
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
);
