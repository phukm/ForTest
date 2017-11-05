<?php

namespace OrgMnt\Factory;

use OrgMnt\Controller\SchoolYearController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SchoolYearControllerFactory implements FactoryInterface
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
        $schoolYearService         = $realServiceLocator->get('OrgMnt\Service\SchoolYearServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');

        return new SchoolYearController($dantaiService, $schoolYearService, $entityManager);
    }
}