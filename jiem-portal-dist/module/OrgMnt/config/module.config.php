<?php
$env = getenv('APP_ENV') ?  : 'production';

return array(
    'controllers' => array(
        'factories' => array(
            'OrgMnt\Controller\Class' => 'OrgMnt\Factory\ClassControllerFactory',
            'OrgMnt\Controller\Org' => 'OrgMnt\Factory\OrgControllerFactory',
            'OrgMnt\Controller\OrgSchoolYear' => 'OrgMnt\Factory\OrgSchoolYearControllerFactory',
            'OrgMnt\Controller\SchoolYear' => 'OrgMnt\Factory\SchoolYearControllerFactory',
            'OrgMnt\Controller\User' => 'OrgMnt\Factory\UserControllerFactory',
            'OrgMnt\Controller\ImportMasterData' => 'OrgMnt\Factory\ImportMasterDataControllerFactory'
        )
    ),
    'router' => array(
        'routes' => array(
            'org-mnt' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/org',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'OrgMnt\Controller',
                        'controller' => 'Org',
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
                            'route' => '/[:controller[/:action][/:id][/page[/:page]][/search/:search]]',
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
            'OrgMnt\Service\ClassServiceInterface' => 'OrgMnt\Service\ClassService',
            'OrgMnt\Service\OrgServiceInterface' => 'OrgMnt\Service\OrgService',
            'OrgMnt\Service\OrgSchoolYearServiceInterface' => 'OrgMnt\Service\OrgSchoolYearService',
            'OrgMnt\Service\SchoolYearServiceInterface' => 'OrgMnt\Service\SchoolYearService',
            'OrgMnt\Service\UserServiceInterface' => 'OrgMnt\Service\UserService',
            'OrgMnt\Service\ImportMasterDataServiceInterface' => 'OrgMnt\Service\ImportMasterDataService'
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
            'OrgMnt' => __DIR__ . '/../view'
        )
    )
);
