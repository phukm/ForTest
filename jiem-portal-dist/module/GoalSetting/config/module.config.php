<?php
$env = getenv('APP_ENV') ?  : 'production';
return array(
    'controllers' => array(
        'invokables' => array(),
        // 'GoalSetting\Controller\Index' => 'GoalSetting\Controller\IndexController'
        'GoalSetting\Controller\GraduationGoalSetting' => 'GoalSetting\Controller\GraduationGoalSettingController',
        'GoalSetting\Controller\EikenScheduleInquiry' => 'GoalSetting\Controller\EikenScheduleInquiryController',
        'factories' => array(
            'GoalSetting\Controller\GoalPass' => 'GoalSetting\Factory\GoalPassControllerFactory',
            'GoalSetting\Controller\StudyGear' => 'GoalSetting\Factory\StudyGearControllerFactory',
            'GoalSetting\Controller\GraduationGoalSetting' => 'GoalSetting\Factory\GraduationGoalSettingControllerFactory',
            'GoalSetting\Controller\EikenScheduleInquiry' => 'GoalSetting\Factory\EikenScheduleInquiryControllerFactory'
        )
    ),
    'router' => array(
        'routes' => array(
            'goal-setting' => array(
                'type' => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route' => '/goalsetting',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'GoalSetting\Controller',
                        'controller' => 'GoalPass',
                        'action' => 'provincialcity'
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
                            'route' => '/[:controller[/:action][/:id][/type[/:type]][/date[/:date]][/schoolyear[/:schoolyear]][/class[/:class]][/level[/:level]][/page[/:page]][/search/:search][/token/:token]]',

                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                                'page' => '[0-9]+'
                            ),
                        //'defaults' => array('action' => 'index')
                        )
                    )
                )
            ),
        )
    )
    ,
    'service_manager' => array(
        'invokables' => array(
            'GoalSetting\Service\GoalPassServiceInterface' => 'GoalSetting\Service\GoalPassService',
            'GoalSetting\Service\StudyGearServiceInterface' => 'GoalSetting\Service\StudyGearService',
            'Application\Service\DantaiServiceInterface' => 'Application\Service\DantaiService',
            'GoalSetting\Service\GraduationGoalSettingServiceInterface' => 'GoalSetting\Service\GraduationGoalSettingService',
            'GoalSetting\Service\EikenScheduleInquiryServiceInterface' => 'GoalSetting\Service\EikenScheduleInquiryService'
        )
    ),
    'view_helpers' => array(
        'invokables' => array(
            'NativePaginationHelper' => 'GoalSetting\Helper\PaginationHelper'
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
            'GoalSetting' => __DIR__ . '/../view'
        )
    ),
    'goalsetting_config' => array(
        'organization_group_1' => array('00', '01', '02', '03', '04', '05', '20', '40', '41', '50', '51', '52', '53', '54', '55'),
        'organization_group_2' => array('10', '15', '16', '22', '25', '30', '31', '35', '36', '45', '46'),
    )
);
