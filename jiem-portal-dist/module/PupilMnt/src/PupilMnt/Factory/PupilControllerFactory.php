<?php
namespace PupilMnt\Factory;

use PupilMnt\Controller\PupilController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PupilControllerFactory implements FactoryInterface
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
        $pupilService = $realServiceLocator->get('PupilMnt\Service\PupilServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');
        
        return new PupilController($dantaiService, $pupilService, $entityManager);
    }
}