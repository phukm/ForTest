<?php
namespace History\Factory;

use History\Controller\EikenHistoryController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EikenHistoryControllerFactory implements FactoryInterface {
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
        $eikenHistoryService = $realServiceLocator->get('History\Service\EikenHistoryServiceInterface');

        return new EikenHistoryController($dantaiService, $eikenHistoryService);
    }

}
