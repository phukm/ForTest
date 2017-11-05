<?php
namespace GoalSetting\Factory;

use GoalSetting\Controller\GraduationGoalSettingController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GraduationGoalSettingControllerFactory implements FactoryInterface
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
        $graduationGoalSetting = $realServiceLocator->get('GoalSetting\Service\GraduationGoalSettingServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');
        return new GraduationGoalSettingController($graduationGoalSetting, $entityManager);
    }
}