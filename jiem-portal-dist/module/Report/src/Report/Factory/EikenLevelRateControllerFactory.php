<?php
namespace Report\Factory;

use Report\Controller\EikenLevelRateController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EikenLevelRateControllerFactory implements FactoryInterface
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
        $eikenLevelRateService = $realServiceLocator->get('Report\Service\EikenLevelRateServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');
        return new EikenLevelRateController($dantaiService, $eikenLevelRateService, $entityManager);
    }
}