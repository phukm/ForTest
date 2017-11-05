<?php
return array(
    'controllers' => array(
        'factories' => array(
            'PupilMnt\Controller\Pupil' => 'PupilMnt\Factory\PupilControllerFactory',
            'PupilMnt\Controller\ImportPupil' => 'PupilMnt\Factory\ImportPupilControllerFactory'
        )
    ),
    'router' => array(
        'routes' => array(
            'pupil-mnt' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/pupil',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'PupilMnt\Controller',
                        'controller' => 'Pupil',
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
                            'route' => '/[:controller[/:action][/:id][/page[/:page]][/activate[/:activate]][/search[/:search]]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                                'page' => '[0-9]+'
                            ),
                            'defaults' => array()
                        )
                    )
                )
            )
        )
    ),
     'service_manager' => array(
         'invokables' => array(
             'PupilMnt\Service\PupilServiceInterface' => 'PupilMnt\Service\PupilService',
             'PupilMnt\Service\ImportPupilServiceInterface' => 'PupilMnt\Service\ImportPupilService',
             'PupilMnt\Service\ExportPupilServiceInterface' => 'PupilMnt\Service\ExportPupilService',
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
            'PupilMnt' => __DIR__ . '/../view'
        )
    )
);
