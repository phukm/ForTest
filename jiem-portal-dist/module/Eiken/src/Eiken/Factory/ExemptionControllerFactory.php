<?php
namespace Eiken\Factory;

use Eiken\Controller\ExemptionController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ExemptionControllerFactory implements FactoryInterface
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
        $dantaiService        = $realServiceLocator->get('Application\Service\DantaiServiceInterface');
        $exemptionService = $realServiceLocator->get('Eiken\Service\ExemptionServiceInterface');    
        return new ExemptionController($dantaiService, $exemptionService);
    }
}