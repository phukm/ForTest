<?php

namespace ConsoleInvitation\Service\Factory;

use ConsoleInvitation\Service\ConfigDateService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConfigDateServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ConfigDateService($serviceLocator);
    }
}
