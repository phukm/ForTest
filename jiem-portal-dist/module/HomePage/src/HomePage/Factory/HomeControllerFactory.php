<?php
namespace HomePage\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use HomePage\Controller\HomePageController;

class HomeControllerFactory implements FactoryInterface
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
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');
        $dantaiService = $realServiceLocator->get('Application\Service\DantaiServiceInterface');
        $homeService = new \HomePage\Service\HomeService($entityManager, $realServiceLocator); 
        
        return new HomePageController($dantaiService, $homeService);
    }
}