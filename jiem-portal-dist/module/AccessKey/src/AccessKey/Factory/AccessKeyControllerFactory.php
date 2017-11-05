<?php

namespace AccessKey\Factory;

use AccessKey\Controller\AccessKeyController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AccessKeyControllerFactory implements FactoryInterface
{

    /**
     * Create service
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        $dantaiService = $realServiceLocator->get('Application\Service\DantaiServiceInterface');
        $accessKeyService = $realServiceLocator->get('AccessKey\Service\AccessKeyServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');

        return new AccessKeyController($dantaiService, $accessKeyService, $entityManager);
    }

}
