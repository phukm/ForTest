<?php
namespace History\Factory;

use History\Service\MappingEikenResultService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MappingEikenResultServiceFactory implements FactoryInterface {
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new MappingEikenResultService($serviceLocator);
    }

}
