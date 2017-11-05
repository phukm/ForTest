<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'ConsoleInvitation\Controller\ConsoleInvitation' => 'ConsoleInvitation\Controller\ConsoleInvitationController',
            'ConsoleInvitation\Controller\ConsoleLearningProgress' => 'ConsoleInvitation\Controller\ConsoleLearningProgressController',
            'ConsoleInvitation\Controller\ConsoleAutoMapping' => 'ConsoleInvitation\Controller\ConsoleAutoMappingController',
            'ConsoleInvitation\Controller\Report' => 'ConsoleInvitation\Controller\ReportController',
            'ConsoleInvitation\Controller\Example' => 'ConsoleInvitation\Controller\ExampleController',
            'ConsoleInvitation\Controller\ConsoleUser' => 'ConsoleInvitation\Controller\ConsoleUserController',
            'ConsoleInvitation\Controller\ConsoleCallApi' => 'ConsoleInvitation\Controller\ConsoleCallApiController',
            'ConsoleInvitation\Controller\ConsoleConfigDate' => 'ConsoleInvitation\Controller\ConsoleConfigDateController',
            'ConsoleInvitation\Controller\Eiken' => 'ConsoleInvitation\Controller\EikenController'
       )
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'console-invitation' => array(
                    'options' => array(
                        'route' => 'console-invitation',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleInvitation',
                            'action' => 'index'
                        )
                    )
                ),
                'example' => array(
                    'options' => array(
                        'route' => 'example [<id>]',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\Example',
                            'action' => 'index'
                        )
                    )
                ),
                'iba-mapping' => array(
                    'options' => array(
                        'route' => 'iba-mapping <id>',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\Example',
                            'action' => 'iba-mapping'
                        )
                    )
                ),
                'renderOneFile' => array(
                    'options' => array(
                        'route' => 'render-one-file <id>',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleInvitation',
                            'action' => 'render-one-file'
                        )
                    )
                ),
                'renderEinavi' => array(
                    'options' => array(
                        'route' => 'render-einavi <id>',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleInvitation',
                            'action' => 'einavi'
                        )
                    )
                ),
                'renderSchool' => array(
                    'options' => array(
                        'route' => 'render-school <id>',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleInvitation',
                            'action' => 'school'
                        )
                    )
                ),
                'convertToPdf' => array(
                    'options' => array(
                        'route' => 'convert-to-pdf',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleInvitation',
                            'action' => 'convert-to-pdf'
                        )
                    )
                ),
                'invitationLetterOfClass' => array(
                    'options' => array(
                        'route' => 'render-class-invitation-letter <classId>',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleInvitation',
                            'action' => 'render-class-invitation-letter'
                        )
                    )
                ),
                'einavidf' => array(
                    'options' => array(
                        'route' => 'einavipdf',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleInvitation',
                            'action' => 'einavipdf'
                        )
                    )
                ),
                'send-mail' => array(
                    'options' => array(
                        'route' => 'email <to> <a> <b>',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleInvitation',
                            'action' => 'send-mail'
                        )
                    )
                ),
                'segment' => array(
                    'options' => array(
                        'route' => 'cli <action> [<id>]',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleInvitation'
                        )
                    )
                ),
                'learning-progress' => array(
                    'options' => array(
                        'route' => 'learning-progress <action> [<date>]',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleLearningProgress'
                        )
                    )
                ),                
                'auto-mapping' => array(
                    'options' => array(
                        'route' => 'auto-mapping <action>',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleAutoMapping'
                        )
                    )
                ),
                'user-status' => array(
                    'options' => array(
                        'route' => 'user-status <action> ',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleUser'
                        )
                    )
                ),
                'report' => array(
                    'options' => array(
                        'route' => 'report <action> [<id>]',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\Report'
                        )
                    )
                ),
                'callApi' => array(
                    'options' => array(
                        'route' => 'call-api <action>',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleCallApi'
                        )
                    )
                ),
                'renderPayment' => array(
                    'options' => array(
                        'route' => 'render-payment <id>',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleInvitation',
                            'action' => 'payment'
                        )
                    )
                ),
                'importConfigDate' => array(
                    'options' => array(
                        'route' => 'import-config-date [<fileName>]',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleConfigDate',
                            'action' => 'import-config-date'
                        )
                    )
                ),
                'exportConfigDate' => array(
                    'options' => array(
                        'route' => 'export-config-date [<year>] [<kai>]',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleConfigDate',
                            'action' => 'export-config-date'
                        )
                    )
                ),
                'eiken' => array(
                    'options' => array(
                        'route' => 'eiken <action> [<year>] [<kai>]',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\eiken',
                            'action' => 'index'
                        )
                    )
                ),
                'save-downloaded-file-s3' => array(
                    'options' => array(
                        'route' => 'save-downloaded-file-s3 [<year>] [<kai>] [<type>]',
                        'defaults' => array(
                            'controller' => 'ConsoleInvitation\Controller\ConsoleInvitation',
                            'action' => 'saveDownloadedFileFromS3'
                        )
                    )
                )
            )
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'ConsoleInvitation' => __DIR__ . '/../view'
        )
    ),

    'service_manager' => array(
        'factories' => array(
            'ConsoleInvitation\Service\HTMLGenerator' => 'ConsoleInvitation\Service\Factory\HTMLGenerator',
            'ConsoleInvitation\Service\InvitationGenerator' => 'ConsoleInvitation\Service\Factory\InvitationGenerator',
            'ConsoleInvitation\Service\AutoMappingService' => 'ConsoleInvitation\Service\Factory\AutoMappingServiceFactory',
            'ConsoleInvitation\Service\ConfigDateService' => 'ConsoleInvitation\Service\Factory\ConfigDateServiceFactory'
        )
    ),

    'ConsoleInvitation' => array(
        'moduleDir' => realpath(__DIR__ . '/../'),
        'moduleViewPath' => realpath(__DIR__ . '/../view/console-invitation'),
        'htmlExportDir' => realpath(__DIR__ . '/../../../data/htmlTemplate/'),
        'pdfExportDir' => realpath(__DIR__ . '/../../../data/eikenPdf/'),
        'imagePath' => realpath(__DIR__ . '/../../../public/'),

        'einavi_studygear_input_path' => 'input/',
        'einavi_studygear_output_path' => 'output/',
        'einavi_studygear_file_key' => realpath(__DIR__ . '/../../../data/KeySFTP/eikengp-priv.key'),
        'einavi_studygear_file_input_path' => realpath(__DIR__ . '/../../../data/einavi/Input'),
        'einavi_studygear_file_output_path' => realpath(__DIR__ . '/../../../data/einavi/Output'),
        'econtext_combini_file_path' => realpath(__DIR__ . '/../../../data/econtextCombini/'),
        'econtext_combini_mail_address' => 'kojin@mail.eiken.or.jp',
    )
);
