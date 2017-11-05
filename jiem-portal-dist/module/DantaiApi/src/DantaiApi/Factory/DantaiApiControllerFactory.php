<?php
namespace DantaiApi\Factory;

use DantaiApi\Controller\DantaiApiController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DantaiApiControllerFactory implements FactoryInterface
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
        $dantaiService = $realServiceLocator->get('Application\Service\DantaiServiceInterface');
        $DantaiApiService = $realServiceLocator->get('DantaiApi\Service\DantaiApiServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');
        return new DantaiApiController($dantaiService, $DantaiApiService, $entityManager);

    }
}