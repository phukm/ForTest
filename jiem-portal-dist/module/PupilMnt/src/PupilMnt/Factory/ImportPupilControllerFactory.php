<?php
namespace PupilMnt\Factory;

use PupilMnt\Controller\ImportPupilController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImportPupilControllerFactory implements FactoryInterface
{

    /**
     * Create Service
     *
     * @param ServiceLocatorInterface $serviceLocator            
     *
     * @return mixed
     */

    public function createService(ServiceLocatorInterface $serviceLocator) {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        $dantaiService = $realServiceLocator->get('Application\Service\DantaiServiceInterface');
        $pupilService = $realServiceLocator->get('PupilMnt\Service\ImportPupilServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');

        return new ImportPupilController($dantaiService, $pupilService, $entityManager);
    }

}