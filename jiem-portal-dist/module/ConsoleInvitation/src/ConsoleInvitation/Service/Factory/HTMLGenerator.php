<?php

namespace ConsoleInvitation\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HTMLGenerator implements FactoryInterface{
    
    public function createService(ServiceLocatorInterface $serviceLocator) {
        return new \ConsoleInvitation\Service\HTMLGenerator($serviceLocator->get('Config')['ConsoleInvitation']);
    }

}
