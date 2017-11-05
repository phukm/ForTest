<?php
namespace Logs\Factory;

use Logs\Controller\ApplyEikenController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ApplyEikenControllerFactory implements FactoryInterface
{
    /**
     * Create Service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        $dantaiService        = $realServiceLocator->get('Application\Service\DantaiServiceInterface');
        $applyEikenService         = $realServiceLocator->get('Logs\Service\ApplyEikenServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');

        return new ApplyEikenController($dantaiService, $applyEikenService, $entityManager);
    }
}
