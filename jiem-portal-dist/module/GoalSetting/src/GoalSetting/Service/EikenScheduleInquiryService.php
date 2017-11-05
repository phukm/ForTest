<?php

namespace GoalSetting\Service;

use GoalSetting\Service\ServiceInterface\EikenScheduleInquiryServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class EikenScheduleInquiryService implements EikenScheduleInquiryServiceInterface, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function getEikenSchedulesByYear($yearFrom, $yearTo, $examName) {
        
        return $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->getEikenSchedulesByYear($yearFrom, $yearTo, $examName);
    }

    public function getHolidaysByDate($dateFrom, $dateTo)
    {
        return $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->getHolidaysByDate($dateFrom, $dateTo);
    }
    
    public function getOrganizationByNo($orgNumber = '')
    {
        return $this->getEntityManager()->getRepository('Application\Entity\Organization')->getOrganizationByNo($orgNumber);
    }

}
