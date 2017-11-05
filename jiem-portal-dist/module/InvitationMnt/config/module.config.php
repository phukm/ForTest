<?php
$env = getenv('APP_ENV') ?: 'production';
return array(
    'controllers'          => array(
        'invokables' => array(),
        'factories'  => array(
            'InvitationMnt\Controller\Recommended' => 'InvitationMnt\Factory\RecommendedControllerFactory',
            'InvitationMnt\Controller\Generate'    => 'InvitationMnt\Factory\GenerateControllerFactory',
            'InvitationMnt\Controller\Setting'     => 'InvitationMnt\Factory\SettingControllerFactory',
            'InvitationMnt\Controller\Standard'    => 'InvitationMnt\Factory\StandardControllerFactory'
        )
    ),
    'router'               => array(
        'routes' => array(
            'invitation-mnt' => array(
                'type'          => 'Literal',
                'options'       => array(
                    // Change this to something specific to your module
                    'route'    => '/invitation',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'InvitationMnt\Controller',
                        'controller'    => 'Invitation',
                        'action'        => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:controller[/:action][/:id][/year[/:year]][/kai[/:kai]][/page[/:page]]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'         => '[0-9]+',
                                'year'       => '[0-9]+',
                                'kai'        => '[0-9]+',
                                'page'       => '[0-9]+'
                            ),
                            'defaults'    => array()
                        )
                    )
                )
            )
        )
    ),
    'service_manager'      => array(
        'invokables' => array(
            'InvitationMnt\Service\RecommendedServiceInterface' => 'InvitationMnt\Service\RecommendedService',
            'InvitationMnt\Service\GenerateServiceInterface'    => 'InvitationMnt\Service\GenerateService',
            'InvitationMnt\Service\SettingServiceInterface'     => 'InvitationMnt\Service\SettingService',
            'InvitationMnt\Service\StandardServiceInterface'    => 'InvitationMnt\Service\StandardService'
        )
    ),
    'translator'           => array(
        'locale'                    => 'ja_JP',
        'translation_file_patterns' => array(
            array(
                'base_dir' => __DIR__ . '/../languages/phpArray',
                'type'     => 'phpArray',
                'pattern'  => '%s.php'
            )
        )
    ),
    'view_manager'         => array(
        'template_path_stack' => array(
            'invitation-mnt' => __DIR__ . '/../view'
        )
    ),
);


