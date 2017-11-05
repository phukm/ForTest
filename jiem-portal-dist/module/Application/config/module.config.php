<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
$env = getenv('APP_ENV') ? : 'production';
return array(
    'doctrine' => array(
        'driver' => array(
            'application_entities' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../src/Application/Entity'
                )
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Application\Entity' => 'application_entities'
                )
            )
        ),
        'authentication' => array(
            'orm_default' => array(
                // should be the key you use to get doctrine's entity manager out of zf2's service locator
                'objectManager' => 'Doctrine\ORM\EntityManager',
                // fully qualified name of your user class
                'identityClass' => 'Application\Entity\User',
                // the identity property of your class
                'identityProperty' => 'id',
                // the password property of your class
                'credentialProperty' => 'password',
                // a callable function to hash the password with
                'credentialCallable' => 'Application\Entity\User::hashPassword'
            )
        )
    ),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'HomePage\Controller\HomePage',
                        'action' => 'index'
                    )
                )
            ),
            'zipcode' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/zipcode',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'zipcode'
                    )
                )
            )
        )
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory'
        ),
        'factories' => array(
            'translator' => 'MvcTranslator',
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory'
        ),
        'invokables' => array(
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
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController'
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/satellite' => __DIR__ . '/../view/layout/satellite.phtml',
            'layout/admin' => __DIR__ . '/../view/layout/admin.phtml',
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        )
    ),
    'view_helpers' => array(
        'invokables' => array(
            'PaginationHelper' => 'Application\Helper\PaginationHelper',
            'DateHelper' => 'Dantai\Utility\DateHelper'
        )
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array()
        )
    ),
    'session' => array(
        'php_ini' => array(
            'gc_maxlifetime' => 3600,
            'gc_divisor' => 1,
            'use_cookies' => 1,
            'cookie_lifetime' => 0
        ),
        'time_out_duration' => 3600
    ),
    'navigation' => array(
        'default' => array(
            array(
                'label' => 'ユーザ情報詳細',
                'route' => 'profile',
                'pages' => array(
                    array(
                        'label' => 'ユーザ情報編集',
                        'route' => 'editProfile',
                        'pages' => array(
                            array(
                                'label' => 'パスワード変更',
                                'route' => 'changePassword'
                            )
                        )
                    )
                )
            ),
            array(
                'label' => 'マスタ設定 ',
                'route' => 'logs',
                'pages' => array(
                    array(
                        'label' => 'アクセス、操作ログ',
                        'route' => 'logs/default',
                        'controller' => 'activity',
                        'action' => 'index'
                    )
                )
            ),
            array(
                'label' => 'マスタ設定 ',
                'route' => 'logs',
                'pages' => array(
                    array(
                        'label' => '申込確定後の変更情報',
                        'route' => 'logs/default',
                        'controller' => 'apply-eiken',
                        'action' => 'index'
                    )
                )
            ),
            array(
                'label' => 'ホーム',
                'route' => 'home-page',
                'pages' => array(
                    array(
                        'label' => '目標達成状況',
                        'route' => 'home-page/default',
                        'controller' => 'homepage',
                        'action' => 'detaila'
                    ),
                    array(
                        'label' => 'サイトマップ',
                        'route' => 'home-page/default',
                        'controller' => 'homepage',
                        'action' => 'site-map'
                    ),
                    array(
                        'label' => '個人情報保護方針',
                        'route' => 'home-page/default',
                        'controller' => 'homepage',
                        'action' => 'privacy-policy'
                    ),
                    array(
                        'label' => 'サイトポリシー',
                        'route' => 'home-page/default',
                        'controller' => 'homepage',
                        'action' => 'policy'
                    ),
                    array(
                        'label' => '使い方',
                        'route' => 'home-page/default',
                        'controller' => 'homepage',
                        'action' => 'user-manual'
                    ),
                    array(
                        'label' => 'システム利用規程',
                        'route' => 'home-page/default',
                        'controller' => 'homepage',
                        'action' => 'terms-of-use'
                    ),
                )
            ),
            array(
                'label' => '学習進捗管理',
                'route' => 'goal-setting',
                'pages' => array(
                    array(
                        'label' => 'スタギア学習進捗照会（個人）',
                        'route' => 'goal-setting/default',
                        'controller' => 'studygear',
                        'action' => 'index',
                        'pages' => array(
                            array(
                                'label' => '履歴詳細',
                                'route' => 'goal-setting/default',
                                'controller' => 'studygear',
                                'action' => 'show'
                            )
                        )
                    ),
                    array(
                        'label' => 'スタディギア学習進捗照会（学年、クラス）',
                        'route' => 'goal-setting/default',
                        'controller' => 'studygear',
                        'action' => 'listhistorystudy',
                        'pages' => array(
                            array(
                                'label' => 'スタギア学習進捗照会',
                                'id' => 'studygeardetail',
                                'route' => 'goal-setting/default',
                                'controller' => 'studygear',
                                'action' => 'studygeardetail'
                            )
                        )
                    )
                )
            ),
            array(
                'label' => '目標設定・学習計画',
                'route' => 'goal-setting',
                'pages' => array(
                    array(
                        'label' => '年間スケジュール',
                        'route' => 'goal-setting/default',
                        'id' => 'eikenscheduleinquiry',
                        'controller' => 'eikenscheduleinquiry',
                        'action' => 'index'
                    ),
                    array(
                        'label' => '卒業時目標詳細',
                        'route' => 'goal-setting/default',
                        'controller' => 'graduationgoalsetting',
                        'action' => 'index'
                    ),
                    array(
                        'label' => '目標設定',
                        'route' => 'goal-setting/default',
                        'pages' => array(
                            array(
                                'label' => '年度別学年別取得率照会',
                                'route' => 'goal-setting/default',
                                'controller' => 'goalpass',
                                'action' => 'goalofyears'
                            ),
                            array(
                                'label' => '全国・都道府県取得率照会',
                                'route' => 'goal-setting/default',
                                'controller' => 'goalpass',
                                'action' => 'provincialcity'
                            ),
                            array(
                                'label' => '目標設定',
                                'route' => 'goal-setting/default',
                                'controller' => 'graduationgoalsetting',
                                'action' => 'edit'
                            ),
                            array(
                                'label' => '目標設定',
                                'route' => 'goal-setting/default',
                                'controller' => 'goalyearsetting',
                                'action' => 'index'
                            ),
                            array(
                                'label' => '目標設定',
                                'route' => 'goal-setting/default',
                                'controller' => 'goalyearsetting',
                                'action' => 'edit'
                            )
                        )
                    )
                )
            ),
            array(
                'label' => '申込履歴・試験結果 ',
                'route' => 'history/default',
                'pages' => array(
                    array(
                        'label' => '試験結果照会',
                        'route' => 'eikenorg',
                        'action' => 'index',
                        'pages' => array(
                            array(
                                'label' => '団体申込情報',
                                'route' => 'eikenorg',
                                'action' => 'index'
                            )
                        )
                    ),
                    array(
                        'label' => '試験結果照会',
                        'route' => 'history/default',
                        'pages' => array(
                            array(
                                'label' => '団体試験結果',
                                'route' => 'history/default',
                                'controller' => 'eiken',
                                'action' => 'exam-result',
                                'pages' => array(
                                    array(
                                        'label' => '英検取込結果確認',
                                        'id' => 'confirm-exam-result',
                                        'route' => 'history/default',
                                        'controller' => 'eiken',
                                        'action' => 'confirm-exam-result'
                                    ),
                                    array(
                                        'label' => '英検生徒名簿突合せ',
                                        'route' => 'history/default',
                                        'controller' => 'eiken',
                                        'action' => 'mapping-result'
                                    ),
                                    array(
                                        'label' => '英検生徒名簿突合せ',
                                        'route' => 'history/default',
                                        'controller' => 'eiken',
                                        'action' => 'eiken-mapping-result',
                                        'pages' => array(
                                            array(
                                                'label' => '生徒名簿突合せ結果',
                                                'route' => 'history/default',
                                                'controller' => 'eiken',
                                                'action' => 'eiken-confirm-result',
                                            )
                                        ),
                                    ),
                                    array(
                                        'label' => '英検IBA生徒名簿突合せ',
                                        'route' => 'history/default',
                                        'controller' => 'iba',
                                        'action' => 'iba-mapping-result',
                                        'pages' => array(
                                            array(
                                                'label' => '生徒名簿突合せ結果',
                                                'route' => 'history/default',
                                                'controller' => 'iba',
                                                'action' => 'iba-confirm-result',
                                            )
                                        ),
                                    ),
                                    array(
                                        'label' => '英検試験結果一覧',
                                        'route' => 'history/default',
                                        'controller' => 'eiken',
                                        'action' => 'pupil-achievement'
                                    ),
                                )
                            ),
                            array(
                                'label' => '英検IBA個人成績表',
                                'route' => 'history/default',
                                'controller' => 'iba',
                                'action' => 'inquiry'
                            ),
                            array(
                                'label' => '団体試験結果',
                                'route' => 'history/default',
                                'controller' => 'eiken',
                                'action' => 'exam-result',
                                'pages' => array(
                                    array(
                                        'label' => ' 英検IBA取込結果確認',
                                        'route' => 'history/default',
                                        'controller' => 'iba',
                                        'action' => 'confirm-exam-result'
                                    ),
                                    array(
                                        'label' => '英検IBA生徒名簿突合せ',
                                        'route' => 'history/default',
                                        'controller' => 'iba',
                                        'action' => 'mapping-result'
                                    ),
                                    array(
                                        'label' => '英検IBA試験結果一覧',
                                        'route' => 'history/default',
                                        'controller' => 'iba',
                                        'action' => 'pupil-achievement'
                                    )
                                )
                            ),
                            array(
                                'label' => '個人試験結果',
                                'route' => 'history/default',
                                'controller' => 'eiken',
                                'action' => 'exam-history-list',
                                'pages' => array(
                                    array(
                                        'label' => '英検履歴',
                                        'route' => 'history/default',
                                        'controller' => 'eiken',
                                        'action' => 'eiken-history-pupil'
                                    ),
                                    array(
                                        'label' => '英検IBA履歴',
                                        'route' => 'history/default',
                                        'controller' => 'iba',
                                        'action' => 'iba-history-pupil'
                                    ),
                                    array(
                                        'label' => '英検個人成績表',
                                        'route' => 'history/default',
                                        'controller' => 'eiken',
                                        'action' => 'pupil-achievement',
                                        'pages' => array(
                                            array(
                                                'label' => '2015年度第1回実用英語技能検定',
                                                'id' => 'personal_achievement',
                                                'route' => 'history/default',
                                                'controller' => 'eiken',
                                                'action' => 'personal-achievement',
                                            )
                                        )
                                    ),
                                    array(
                                        'label' => '英検IBA履歴',
                                        'route' => 'history/default',
                                        'controller' => 'iba',
                                        'action' => 'pupil-achievement',
                                        'pages' => array(
                                            array(
                                                'label' => '英検IBA個人成績表',
                                                'route' => 'history/default',
                                                'controller' => 'iba',
                                                'action' => 'detail'
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            ),
            array(
                'label' => '各種検定申込 ',
                'action' => 'policy',
                'route' => 'eikenorg',
                'pages' => array(
                    array(
                        'label' => '支払情報',
                        'route' => 'payment',
                        'action' => 'paymentstatus',
                        'pages' => array(
                            array(
                                'label' => '支払情報詳細',
                                'route' => 'payment',
                                'action' => 'paymentdetails'
                            )
                        )
                    ),
                    array(
                        'label' => '一免者参照',
                        'route' => 'exemption',
                        'action' => 'list'
                    ),
                    array(
                        'label' => '英検申込',
                        'route' => 'eikenorg',
                        'action' => 'policy',
                        'pages' => array(
                            array(
                                'label' => '申込フロー',
                                'route' => 'eikenorg',
                                'action' => 'navigator'
                            ),
                            array(
                                'label' => '個人情報保護方針の確認',
                                'route' => 'eikenorg',
                                'action' => 'policy'
                            ),
                            array(
                                'label' => '団体申込情報詳細',
                                'id' => 'app_eik_org_applyeikendetails',
                                'route' => 'eikenorg',
                                'action' => 'applyeikendetails'
                            ),
                            array(
                                'label' => '申込情報確認',
                                'id' => 'app_eik_org_confirmation',
                                'route' => 'eikenorg',
                                'action' => 'confirmation'
                            ),
                            array(
                                'label' => '準会場規定確認',
                                'route' => 'eikenorg',
                                'action' => 'standard-confirmation'
                            ),
                            array(
                                'label' => '申込情報登録',
                                'id' => 'app_eik_org_create',
                                'route' => 'eikenorg',
                                'action' => 'create',
                                'pages' => array(
                                    array(
                                        'id' => 'app_eik_pupil_list',
                                        'label' => '本会場受験者情報入力',
                                        'route' => 'eikenpupil',
                                        'action' => 'index',
                                        'pages' => array(
                                            array(
                                                'label' => '本会場申込情報登録',
                                                'route' => 'eikenpupil',
                                                'action' => 'create'
                                            ),
                                            array(
                                                'label' => '本会場申込情報編集',
                                                'route' => 'eikenpupil',
                                                'action' => 'edit'
                                            ),
                                            array(
                                                'label' => '本会場申込情報詳細',
                                                'route' => 'eikenpupil',
                                                'action' => 'view'
                                            ),
                                            array(
                                                'label' => '英検ID取得',
                                                'route' => 'eikenid',
                                                'action' => 'register'
                                            ),
                                            array(
                                                'label' => '英検ID参照',
                                                'route' => 'eikenid',
                                                'action' => 'reference'
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    ),
                    array(
                        'label' => '英検申込',
                        'route' => 'eikenorg',
                        'pages' => array(
                            array(
                                'label' => '受験案内状',
                                'route' => 'invitation-mnt/default',
                                'controller' => 'setting',
                                'action' => 'index',
                                'pages' => array(
                                    array(
                                        'label' => '受験案内状設定',
                                        'route' => 'invitation-mnt/default',
                                        'controller' => 'setting',
                                        'action' => 'index',
                                        'pages' => array(
                                            array(
                                                'label' => '受験案内状登録',
                                                'route' => 'invitation-mnt/default',
                                                'controller' => 'setting',
                                                'action' => 'add'
                                            ),
                                            array(
                                                'label' => '受験案内状詳細',
                                                'route' => 'invitation-mnt/default',
                                                'controller' => 'setting',
                                                'action' => 'show'
                                            ),
                                            array(
                                                'label' => '受験案内状編集',
                                                'route' => 'invitation-mnt/default',
                                                'controller' => 'setting',
                                                'action' => 'edit'
                                            )
                                        )
                                    ),
                                    array(
                                        'label' => '目標級設定',
                                        'route' => 'invitation-mnt/default',
                                        'controller' => 'recommended',
                                        'action' => 'index'
                                    ),
                                    array(
                                        'label' => '受験案内状作成',
                                        'route' => 'invitation-mnt/default',
                                        'controller' => 'generate',
                                        'action' => 'index',
                                        'pages' => array(
                                            array(
                                                'id' => 'inv-generate',
                                                'label' => '生徒別編集',
                                                'route' => 'invitation-mnt/default',
                                                'controller' => 'generate',
                                                'action' => 'show',
                                                'pages' => array(
                                                    array(
                                                        'label' => 'メッセージ編集',
                                                        'route' => 'invitation-mnt/default',
                                                        'controller' => 'generate',
                                                        'action' => 'edit'
                                                    )
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    ),
                    array(
                        'label' => '英検IBA申込',
                        'id' => 'iba-policy',
                        'route' => 'i-b-a/default',
                        'controller' => 'iba',
                        'action' => 'policy',
                        'pages' => array(
                            array(
                                'label' => '申込規約確認',
                                'id' => 'app_iba_org_policy',
                                'route' => 'i-b-a/default',
                                'controller' => 'iba',
                                'action' => 'policy'
                            ),
                            array(
                                'label' => '英検IBA申込規約',
                                'id' => 'app_iba_org_policy',
                                'route' => 'i-b-a/default',
                                'controller' => 'iba',
                                'action' => 'onlyPolicy'
                            ),
                            array(
                                'label' => '申込情報登録',
                                'id' => 'app_iba_org_add',
                                'route' => 'i-b-a/default',
                                'controller' => 'iba',
                                'action' => 'add'
                            ),
                            array(
                                'label' => '申込情報詳細',
                                'id' => 'app_iba_org_show',
                                'route' => 'i-b-a/default',
                                'controller' => 'iba',
                                'action' => 'show'
                            )
                        )
                    )
                )
            ),
            array(
                'label' => '成績分析',
                'route' => 'report/default',
                'pages' => array(
                    array(
                        'label' => '英検受験実績',
                        'route' => 'home-page/default',
                        'controller' => 'homepage',
                        'action' => 'detailc'
                    ),
                    array(
                        'label' => '英検合格実績',
                        'route' => 'home-page/default',
                        'controller' => 'homepage',
                        'action' => 'detailb1'
                    ),
                    array(
                        'label' => '英検取得状況',
                        'route' => 'home-page/default',
                        'controller' => 'homepage',
                        'action' => 'detailb2'
                    ),
                    array(
                        'label' => '目標達成状況',
                        'route' => 'home-page/default',
                        'controller' => 'homepage',
                        'action' => 'achieve-goal'
                    ),
                    array(
                        'label' => 'CSEスコア推移',
                        'route' => 'report/default',
                        'controller' => 'report',
                        'action' => 'csescoretotal'
                    )
                )
            ),
            array(
                'label' => '団体情報管理',
                'route' => 'org-mnt/default',
                'id' => 'dantaiBreadCumb',
                'pages' => array(
                    array(
                        'label' => '学年情報',
                        'route' => 'org-mnt/default',
                        'controller' => 'orgschoolyear',
                        'action' => 'index',
                        'pages' => array(
                            array(
                                'label' => '学年情報登録',
                                'route' => 'org-mnt/default',
                                'controller' => 'orgschoolyear',
                                'action' => 'add'
                            ),
                            array(
                                'label' => '学年情報詳細',
                                'route' => 'org-mnt/default',
                                'controller' => 'orgschoolyear',
                                'action' => 'show'
                            ),
                            array(
                                'label' => '学年情報編集',
                                'route' => 'org-mnt/default',
                                'controller' => 'orgschoolyear',
                                'action' => 'edit'
                            )
                        )
                    ),
                    array(
                        'label' => '団体検索',
                        'route' => 'org-mnt',
                        'pages' => array(
                            array(
                                'label' => '未確定団体検索',
                                'route' => 'org-mnt/default',
                                'controller' => 'org',
                                'action' => 'undetermined'
                            ),
                            array(
                                'label' => '一般検索',
                                'route' => 'org-mnt/default',
                                'controller' => 'org',
                                'action' => 'index',
                                'pages' => array(
                                    array(
                                        'label' => 'アップロード',
                                        'route' => 'org-mnt/default',
                                        'controller' => 'importmasterdata',
                                        'action' => 'index'
                                    ),
                                ),
                            ),
                        )
                    ),
                    array(
                        'label' => '団体情報詳細',
                        'route' => 'org-mnt/default',
                        'controller' => 'org',
                        'action' => 'show'
                    ),
                    array(
                        'label' => 'クラス情報',
                        'route' => 'org-mnt/default',
                        'controller' => 'class',
                        'action' => 'index',
                        'pages' => array(
                            array(
                                'label' => 'クラス情報登録 ',
                                'route' => 'org-mnt/default',
                                'controller' => 'class',
                                'action' => 'add'
                            ),
                            array(
                                'label' => 'クラス情報詳細',
                                'route' => 'org-mnt/default',
                                'controller' => 'class',
                                'action' => 'show'
                            ),
                            array(
                                'label' => 'クラス情報編集',
                                'route' => 'org-mnt/default',
                                'controller' => 'class',
                                'action' => 'edit'
                            )
                        )
                    ),
                    array(
                        'label' => '生徒情報',
                        'route' => 'pupil-mnt/default',
                        'controller' => 'pupil',
                        'action' => 'index',
                        'pages' => array(
                            array(
                                'label' => '生徒情報登録',
                                'route' => 'pupil-mnt/default',
                                'controller' => 'pupil',
                                'action' => 'add'
                            ),
                            array(
                                'label' => '生徒情報詳細',
                                'route' => 'pupil-mnt/default',
                                'controller' => 'pupil',
                                'action' => 'show'
                            ),
                            array(
                                'label' => '生徒情報編集',
                                'route' => 'pupil-mnt/default',
                                'controller' => 'pupil',
                                'action' => 'edit'
                            ),
                            array(
                                'label' => 'アップロード',
                                'route' => 'pupil-mnt/default',
                                'controller' => 'pupil',
                                'action' => 'import'
                            ),
                            array(
                                'label' => 'アップロード',
                                'route' => 'pupil-mnt/default',
                                'controller' => 'import-pupil',
                                'action' => 'index'
                            ),
                            array(
                                'label' => '氏名（漢字）の分割',
                                'route' => 'pupil-mnt/default',
                                'controller' => 'import-pupil',
                                'action' => 'seperate-name'
                            )
                        )
                    ),
                    array(
                        'label' => 'ユーザ情報',
                        'route' => 'org-mnt/default',
                        'controller' => 'user',
                        'action' => 'index',
                        'pages' => array(
                            array(
                                'label' => 'ユーザ情報登録',
                                'route' => 'org-mnt/default',
                                'controller' => 'user',
                                'action' => 'add'
                            ),
                            array(
                                'label' => 'ユーザ情報詳細',
                                'route' => 'org-mnt/default',
                                'controller' => 'user',
                                'action' => 'show'
                            ),
                            array(
                                'label' => 'ユーザ情報編集',
                                'route' => 'org-mnt/default',
                                'controller' => 'user',
                                'action' => 'edit'
                            )
                        )
                    ),
                    array(
                        'label' => '基準級設定',
                        'route' => 'invitation-mnt/default',
                        'controller' => 'standard',
                        'action' => 'index',
                        'pages' => array(
                            array(
                                'label' => '基準級登録',
                                'route' => 'invitation-mnt/default',
                                'controller' => 'standard',
                                'action' => 'add'
                            ),
                            array(
                                'label' => '基準級詳細',
                                'route' => 'invitation-mnt/default',
                                'controller' => 'standard',
                                'action' => 'show'
                            ),
                            array(
                                'label' => '基準級編集',
                                'route' => 'invitation-mnt/default',
                                'controller' => 'standard',
                                'action' => 'edit'
                            )
                        )
                    )
                )
            )
        )
    )
);
