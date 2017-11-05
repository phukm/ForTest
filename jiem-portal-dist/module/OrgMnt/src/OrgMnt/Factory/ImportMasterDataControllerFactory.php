<?php

namespace OrgMnt\Factory;

use OrgMnt\Controller\ImportMasterDataController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImportMasterDataControllerFactory implements FactoryInterface
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
        $realServiceLocator              = $serviceLocator->getServiceLocator();
        $dantaiService                   = $realServiceLocator->get('Application\Service\DantaiServiceInterface');
        $importMasterDataService         = $realServiceLocator->get('OrgMnt\Service\ImportMasterDataServiceInterface');
        $entityManager                   = $realServiceLocator->get('doctrine.entitymanager.orm_default');
        
        return new ImportMasterDataController($dantaiService, $importMasterDataService, $entityManager);
    }
}