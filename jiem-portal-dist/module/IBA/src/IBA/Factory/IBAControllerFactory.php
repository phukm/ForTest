<?php
namespace IBA\Factory;

use IBA\Controller\IBAController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IBAControllerFactory implements FactoryInterface
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
        $iBAService = $realServiceLocator->get('IBA\Service\IBAServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');
        return new IBAController($dantaiService, $iBAService, $entityManager);
    }
}