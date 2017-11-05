<?php
namespace Satellite\Factory;

use Satellite\Controller\EikenController;
use Zend\ServiceManager\FactoryInterface;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EikenControllerFactory implements FactoryInterface
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
        $eikenService = $realServiceLocator->get('Satellite\Service\EikenServiceInterface'); 
        $dantaiService = $realServiceLocator->get('Application\Service\DantaiServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');   
        return new EikenController($dantaiService, $eikenService, $entityManager);
    }
}