<?php

namespace ConsoleInvitation\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class InvitationGenerator implements FactoryInterface{
    
    public function createService(ServiceLocatorInterface $serviceLocator) {
        return new \ConsoleInvitation\Service\InvitationGenerator(
                $serviceLocator,
                $serviceLocator->get('ConsoleInvitation\Service\HTMLGenerator'));
    }

}
