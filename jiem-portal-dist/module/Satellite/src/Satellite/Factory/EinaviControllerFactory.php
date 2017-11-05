<?php
namespace Satellite\Factory;

use Satellite\Controller\EinaviController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EinaviControllerFactory implements FactoryInterface
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
        $einaviService = $realServiceLocator->get('Satellite\Service\EinaviServiceInterface');    
        return new EinaviController($einaviService);
    }
}