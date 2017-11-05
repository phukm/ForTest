<?php

namespace ConsoleInvitation\Service\Factory;

use ConsoleInvitation\Service\AutoMappingService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AutoMappingServiceFactory implements FactoryInterface{
    
    public function createService(ServiceLocatorInterface $serviceLocator) {
        return new AutoMappingService($serviceLocator);
    }

}
