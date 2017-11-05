<?php
return array(
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'host' => 'localhost',
                    'port' => '3306',
                    'user' => 'jiemdpdev',
                    'password' => 'jiemdpdev',
                    'dbname' => 'jiemdpdev21',
                    'charset' => 'utf8'
                )
            )
        )
    )
);
