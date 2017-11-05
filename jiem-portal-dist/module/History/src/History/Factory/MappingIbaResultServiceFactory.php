<?php
namespace History\Factory;

use History\Service\MappingIbaResultService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MappingIbaResultServiceFactory implements FactoryInterface {
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new MappingIbaResultService($serviceLocator);
    }

}
