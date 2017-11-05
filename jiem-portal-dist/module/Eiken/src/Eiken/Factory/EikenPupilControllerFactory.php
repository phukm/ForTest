<?php
namespace Eiken\Factory;

use Eiken\Controller\EikenPupilController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EikenPupilControllerFactory implements FactoryInterface
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
        $eikenPupilService = $realServiceLocator->get('Eiken\Service\ApplyEikenPupilServiceInterface');
        $eikenOrgService = $realServiceLocator->get('Eiken\Service\ApplyEikenOrgServiceInterface');
        return new EikenPupilController($eikenPupilService, $eikenOrgService);
    }
}