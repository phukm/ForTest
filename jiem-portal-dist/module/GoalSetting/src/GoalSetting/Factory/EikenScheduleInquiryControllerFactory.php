<?php
namespace GoalSetting\Factory;

use GoalSetting\Controller\EikenScheduleInquiryController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EikenScheduleInquiryControllerFactory implements FactoryInterface
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
        $eikenScheduleInquiry = $realServiceLocator->get('GoalSetting\Service\EikenScheduleInquiryServiceInterface');
        return new EikenScheduleInquiryController($eikenScheduleInquiry);
    }
}