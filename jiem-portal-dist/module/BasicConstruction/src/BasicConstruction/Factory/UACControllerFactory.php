<?php
namespace BasicConstruction\Factory;

use BasicConstruction\Controller\UACController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UACControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        $dantaiService = $realServiceLocator->get('Application\Service\DantaiServiceInterface');
        $uacService = $realServiceLocator->get('BasicConstruction\Service\UACServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');

        return new UACController($dantaiService, $uacService, $entityManager);
    }

}