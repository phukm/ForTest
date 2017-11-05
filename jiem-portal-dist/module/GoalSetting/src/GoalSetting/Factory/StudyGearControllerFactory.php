<?php
namespace GoalSetting\Factory;

use GoalSetting\Controller\StudyGearController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class StudyGearControllerFactory implements FactoryInterface
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
        $studyGearService = $realServiceLocator->get('GoalSetting\Service\StudyGearServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');
        return new StudyGearController($dantaiService, $studyGearService, $entityManager);
    }
}