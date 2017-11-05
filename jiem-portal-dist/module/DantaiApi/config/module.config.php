<?php

return array(
    'service_manager' => array(
        'invokables' => array(
            'DantaiApi\Service\DantaiApiServiceInterface' => 'DantaiApi\Service\DantaiApiService',
            'DantaiApi\Service\MappingApiServiceInterface' => 'DantaiApi\Service\MappingApiService',
          )
    ),
    'controllers' => array(
        'factories' => array(
            'DantaiApi\Controller\DantaiApi' => 'DantaiApi\Factory\DantaiApiControllerFactory',
             'DantaiApi\Controller\MappingApi' => 'DantaiApi\Factory\MappingApiControllerFactory',
          )
    ),
    // The following section is new` and should be added to your file
    'router' => array(
        'routes' => array(
            'dantai-api' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/payment/update-status[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'DantaiApi\Controller\DantaiApi',
                    ),
                ),
            ),
            'mapping-api' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/mappingapi',
                    'constraints' => array(
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'DantaiApi\Controller\MappingApi',
                    ),
                ),
            ),
            'rest-api' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/rest-api[/:action][/:orgNo]',
                    'defaults' => array(
                        'controller' => 'DantaiApi\Controller\DantaiApi',
                        'action' => '',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
