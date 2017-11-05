<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'BasicConstruction\Controller\User' => 'BasicConstruction\Controller\UserController'
        ),
        'factories' => array(
            'BasicConstruction\Controller\Uac' => 'BasicConstruction\Factory\UACControllerFactory'
        )
    ),
    'router' => array(
        'routes' => array(
            'basic-construction' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/basicConstruction',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'BasicConstruction\Controller',
                        'controller' => 'BasicConstruction',
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
                            'route' => '/[:controller[/:action[/]]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array()
                        )
                    )
                )
            ),
            'forgot' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/user/[:action[/][?token=:token]]',
                    'defaults' => array(
                        '__NAMESPACE__' => 'BasicConstruction\Controller',
                        'controller' => 'User',
                        'action' => 'forgot',
                        'token' => null
                    )
                ),
                'may_terminate' => true,
            ),
            'login' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/login',
                    'defaults' => array(
                        '__NAMESPACE__' => 'BasicConstruction\Controller',
                        'controller' => 'Uac',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action[/]]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array()
                        )
                    )
                )
            ),
            'logout' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/logout',
                    'defaults' => array(
                        '__NAMESPACE__' => 'BasicConstruction\Controller',
                        'controller' => 'Uac',
                        'action' => 'logout'
                    )
                )
            ),
            
            'profile' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/profile',
                    'defaults' => array(
                        '__NAMESPACE__' => 'BasicConstruction\Controller',
                        'controller' => 'Uac',
                        'action' => 'profile'
                    )
                )
            ),
            
            'editProfile' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/edit-profile',
                    'defaults' => array(
                        '__NAMESPACE__' => 'BasicConstruction\Controller',
                        'controller' => 'Uac',
                        'action' => 'editProfile'
                    )
                )
            ),
            
            'changePassword' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/change-password',
                    'defaults' => array(
                        '__NAMESPACE__' => 'BasicConstruction\Controller',
                        'controller' => 'Uac',
                        'action' => 'changePassword'
                    )
                )
            ),
            
            'changePasswordFirst' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/change-password-first',
                    'defaults' => array(
                        '__NAMESPACE__' => 'BasicConstruction\Controller',
                        'controller' => 'Uac',
                        'action' => 'changePasswordFirst'
                    )
                )
            ),
            
            'policy' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/policy-first-login',
                    'defaults' => array(
                        '__NAMESPACE__' => 'BasicConstruction\Controller',
                        'controller' => 'Uac',
                        'action' => 'policy'
                    )
                )
            ),
            
            'accessDenied' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/access-denied',
                    'defaults' => array(
                        '__NAMESPACE__' => 'BasicConstruction\Controller',
                        'controller' => 'Uac',
                        'action' => 'accessDenied'
                    )
                )
            ),
            
            'inactivated' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/inactivated',
                    'defaults' => array(
                        '__NAMESPACE__' => 'BasicConstruction\Controller',
                        'controller' => 'Uac',
                        'action' => 'inactivated'
                    )
                )
            )
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'BasicConstruction\Service\UACServiceInterface' => 'BasicConstruction\Service\UACService',
            'BasicConstruction.ForgotUserService' => 'BasicConstruction\Service\ForgotUserService',
        ),
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
            'BasicConstruction' => __DIR__ . '/../view'
        )
    )
);
