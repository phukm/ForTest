<?php

namespace OrgMnt\Factory;

use OrgMnt\Controller\OrgController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OrgControllerFactory implements FactoryInterface
{
    /**
     * Create Service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        $dantaiService        = $realServiceLocator->get('Application\Service\DantaiServiceInterface');
        $orgService         = $realServiceLocator->get('OrgMnt\Service\OrgServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');

        return new OrgController($dantaiService, $orgService, $entityManager);
    }
}