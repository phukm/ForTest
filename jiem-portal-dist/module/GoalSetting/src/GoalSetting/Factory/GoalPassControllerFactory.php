<?php
namespace GoalSetting\Factory;

use GoalSetting\Controller\GoalPassController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GoalPassControllerFactory implements FactoryInterface
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
        $goalPassService = $realServiceLocator->get('GoalSetting\Service\GoalPassServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');
        return new GoalPassController($dantaiService, $goalPassService, $entityManager);
    }
}