<?php
namespace History\Factory;

use History\Controller\IbaHistoryController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IbaHistoryControllerFactory implements FactoryInterface {
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
        $ibaHistoryService = $realServiceLocator->get('History\Service\IbaHistoryServiceInterface');
        $eikenHistoryService = $realServiceLocator->get('History\Service\EikenHistoryServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');
        
        return new IbaHistoryController($dantaiService, $eikenHistoryService, $ibaHistoryService,$entityManager);
    }

}
