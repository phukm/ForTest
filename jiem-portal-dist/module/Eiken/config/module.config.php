<?php
$env = getenv('APP_ENV') ?  : 'production';

return array(
    'service_manager' => array(
        'invokables' => array(
            'Eiken\Service\ApplyEikenPupilServiceInterface' => 'Eiken\Service\ApplyEikenPupilService',
            'Eiken\Service\ApplyEikenOrgServiceInterface' => 'Eiken\Service\ApplyEikenOrgService',
            'Eiken\Service\EikenIdServiceInterface' => 'Eiken\Service\EikenIdService',
            'Eiken\Service\PaymentServiceInterface' => 'Eiken\Service\PaymentService',
            'Eiken\Service\ExemptionServiceInterface' => 'Eiken\Service\ExemptionService'
        )
    ),
    'controllers' => array(
        'factories' => array(
            'Eiken\Controller\EikenOrg' => 'Eiken\Factory\EikenOrgControllerFactory',
            'Eiken\Controller\EikenId' => 'Eiken\Factory\EikenIdControllerFactory',
            'Eiken\Controller\EikenPupil' => 'Eiken\Factory\EikenPupilControllerFactory',
            'Eiken\Controller\Payment' => 'Eiken\Factory\PaymentControllerFactory',
            'Eiken\Controller\Exemption' => 'Eiken\Factory\ExemptionControllerFactory',
        )
    ),
    'router' => array(
        'routes' => array(
            'eikenpupil' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/eiken/eikenpupil[/:action][/:id][/page[/:page]][/levelid[/:levelid]][/infoid[/:infoid]][/search/:search]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'page' => '[0-9]+',
                        'levelid' => '[0-9]+',
                        'infoid' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Eiken\Controller\EikenPupil',
                        'action' => 'index'
                    )
                )
            ),
            'eikenorg' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/eiken/eikenorg[/:action][/:id][/page[/:page]][/search/:search]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Eiken\Controller\EikenOrg',
                        'action' => 'create'
                    )
                )
            ),
            'eikenid' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/eiken/eikenid[/:action][/:id][/levelid[/:levelid]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'levelid' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Eiken\Controller\EikenId',
                        'action' => ''
                    )
                )
            ),

            'payment' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/eiken/payment[/:action][/:id][/page[/:page]][/search/:search][/isExportExcel/:isExportExcel]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'page' => '[0-9]+',
                        'isExportExcel' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Eiken\Controller\Payment',
                        'action' => ''
                    )
                )
            ),
              'exemption' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/eiken/exemption[/:action][/:id][/page[/:page]][/year[/:year]][/kai[/:kai]][/eikenid[/:eikenid]][/name[/:name]][/search/:search]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Eiken\Controller\Exemption',
                        'action' => ''
                    )
                )
            )
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
            'Eiken' => __DIR__ . '/../view'
        )
    ),
    'view_helpers' => array(
        'invokables' => array(
            'EikenPaginationHelper' => 'Eiken\Helper\PaginationHelper'
        )
    ),
    
);
