<?php
$env = getenv('APP_ENV') ?  : 'production';
return array(
    'doctrine' => array(
        'driver' => array(
            'application_entities' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../../Application/src/Application/Entity'
                )
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Application\Entity' => 'application_entities'
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Satellite\Controller\Satellite' => 'Satellite\Controller\SatelliteController',
            'Satellite\Controller\PaymentEikenExam' => 'Satellite\Controller\PaymentEikenExamController'
        ),
        'factories' => array(
            // 'Satellite\Controller\Satellite' => 'Satellite\Controller\SatelliteController',
            'Satellite\Controller\Einavi' => 'Satellite\Factory\EinaviControllerFactory',
            'Satellite\Controller\Eiken' => 'Satellite\Factory\EikenControllerFactory'
        )
    ),
    'router' => array(
        'routes' => array(
            'satellite' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Satellite\Controller',
                        'controller' => 'satellite',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '[:controller[/:action][/:id]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+'
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
                        '__NAMESPACE__' => 'Satellite\Controller',
                        'controller' => 'satellite',
                        'action' => 'logout'
                    )
                )
            ),
            'einavi' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/einavi',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Satellite\Controller',
                        'controller' => 'einavi',
                        'action' => 'loginon'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '[/:action]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array()
                        )
                    )
                )
            ),
            'login' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/login',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Satellite\Controller',
                        'controller' => 'Satellite',
                        'action' => 'login'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '[/:action]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array()
                        )
                    )
                )
            ),
            'eiken' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/eiken',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Satellite\Controller',
                        'controller' => 'eiken',
                        'action' => 'show'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '[/:action][/:id]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array()
                        )
                    )
                )
            ),
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/mobile' => __DIR__ . '/../view/layout/layout-mobile.phtml',
            'layout/layoutlogin' => __DIR__ . '/../view/layout/layoutlogin.phtml',
            'layout/mlayoutlogin' => __DIR__ . '/../view/layout/mlayoutlogin.phtml',
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        )
    ),
    'service_manager' => array(
        'invokables' => array(
            'Satellite\Service\EinaviServiceInterface' => 'Satellite\Service\EinaviService',
            'Satellite\Service\PaymentEikenExamService' => 'Satellite\Service\PaymentEikenExamService',
            'Satellite\Service\EikenServiceInterface' => 'Satellite\Service\EikenService',
            'Application\Service\DantaiServiceInterface' => 'Application\Service\DantaiService'
        ),
        'factories' => array(
            'translator' => 'MvcTranslator',
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory'
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
    'navigation' => array(
        'default' => array(
            array(
                'label' => 'ホーム',
                'route' => 'einavi/default',
                'pages' => array(
                    array(
                        'label' => '英ナビ！利用開始',
                        'route' => 'einavi/default',
                        'controller' => 'einavi',
                        'action' => 'loginon',
                        'pages' => array(
                            array(
                                'label' => '英ナビ！ID取得',
                                'controller' => 'einavi',
                                'action' => 'register'
                            )
                        )
                    ),
                    array(
                        'label' => 'パスワードをお忘れの方',
                        'route' => 'einavi/default',
                        'controller' => 'einavi',
                        'action' => 'forgot-password',
                    ),
                    array(
                        'label' => '使い方',
                        'route' => 'einavi/default',
                        'controller' => 'einavi',
                        'action' => 'user-manual'
                    )
                )
            ),
            array(
                'label' => 'ホーム',
                'route' => 'payment-eiken-exam/default',
                'pages' => array(
                    array(
                        'label' => 'クレジット支払',
                        'controller' => 'payment-eiken-exam',
                        'action' => 'pay-by-credit'
                    ),
                    array(
                        'label' => 'クレジット支払確認',
                        'controller' => 'payment-eiken-exam',
                        'action' => 'payment-confirm'
                    ),
                    array(
                        'label' => '申し込み情報',
                        'controller' => 'payment-eiken-exam',
                        'action' => 'payment-infomation'
                    )
                )
            ),
            array(
                'label' => 'ホーム',
                'route' => 'satellite/default',
                'pages' => array(
                    array(
                        'label' => '特定商取引法に基づく表記',
                        'controller' => 'satellite',
                        'action' => 'commercial_law'
                    ),
                    array(
                        'label' => '使い方',
                        'controller' => 'satellite',
                        'action' => 'user-manual'
                    ),
                )
            ),
            array(
                'label' => 'ホーム',
                'route' => 'eiken/default',
                'pages' => array(
                    array(
                        'label' => '英検申し込み',
                        'controller' => 'eiken',
                        'action' => 'show'
                    ),
                    array(
                        'label' => '英検申し込み',
                        'controller' => 'eiken',
                        'action' => 'test-site-exemption'
                    ),array(
                        'label' => '申し込み情報確認',
                        'controller' => 'eiken',
                        'action' => 'confirmation'
                    ),
                    array(
                        'label' => '英検申し込み',
                        'controller' => 'eiken',
                        'action' => 'apply-eiken'
                    )
                )
            )
        )
    )
);
