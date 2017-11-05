<?php

namespace OrgMnt\Factory;

use OrgMnt\Controller\OrgSchoolYearController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OrgSchoolYearControllerFactory implements FactoryInterface
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
        $orgSchoolYearService         = $realServiceLocator->get('OrgMnt\Service\OrgSchoolYearServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');

        return new OrgSchoolYearController($dantaiService, $orgSchoolYearService, $entityManager);
    }
}