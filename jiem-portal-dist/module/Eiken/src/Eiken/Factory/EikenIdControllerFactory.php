<?php
namespace Eiken\Factory;

use Eiken\Controller\EikenIdController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EikenIdControllerFactory implements FactoryInterface
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
        $eikenIdService = $realServiceLocator->get('Eiken\Service\EikenIdServiceInterface');
        $eikenOrgService = $realServiceLocator->get('Eiken\Service\ApplyEikenOrgServiceInterface');
        return new EikenIdController($eikenIdService, $eikenOrgService);
    }
}