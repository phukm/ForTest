<?php

namespace InvitationMnt\Service;

use InvitationMnt\Service\ServiceInterface\SettingServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Dantai\PrivateSession;


class SettingService implements SettingServiceInterface, ServiceLocatorAwareInterface
{
    use \Application\Controller\ControllerAwareTrait;
    use ServiceLocatorAwareTrait;
    
    private $orgId;
    private $em;
    private $translate;
    
    public function __construct() {
        $user = PrivateSession::getData('userIdentity');
        $this->orgId = $user['organizationId'];
    }
    
     /*
     * Function to get payment method by OrgId and EikenScheduleId
     */
    public function getPaymentMethod($OrgId, $eikenScheduleId) {
        // Get payment method of current kai and current orgId
        $PaymentMethod = $this->getEntityManager()
                    ->getRepository('Application\Entity\PaymentMethod')
                    ->findOneBy(array(              
                        'eikenSchedule' => $eikenScheduleId,
                        'organization' => $OrgId,
        ));
        
        return $PaymentMethod;
    }
    
    public function getTranslator()
    {
        if (null === $this->translate) {
            $this->translate = $this->getServiceLocator()->get('MVCTranslator');
        }
        return $this->translate;
    }
    
    public function getEntityManager()
    {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }
    
    public function validateIssueDate($issueDate, $appEndDate)
    {
        $error = '';
        if(!empty($issueDate)){
            if (!$this->validateDate($issueDate, 'Y/m/d')) {
                $error = $this->getTranslator()->translate('MSG011');
            }elseif($issueDate > $appEndDate){
                $error = $this->getTranslator()->translate('R4_MSG11');
            }
        }
        return $error;
    }
    
    function validateDate($date, $format = 'Y-m-d H:i:s') {
        $d = \DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) == $date;
    }
    
    private $invitation;
    public function setInvitationSettingRepository($inv = null){
        $this->invitation = $inv ? $inv : $this->getEntityManager()->getRepository('Application\Entity\InvitationSetting');
    }
    
    public function getInvitationSetting($id)
    {
        if(!$this->invitation){
            $this->setInvitationSettingRepository();
        }
        return $invSetting = $this->getEntityManager()->getRepository('Application\Entity\InvitationSetting')->findOneBy(
            array(
                'organizationId' => $this->orgId,
                'id' => $id
            ));
    }
    
    public function getApplyEikenOrg($orgId, $eikenSchedule)
    {
        $applyEiken = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenOrg')
                ->findOneBy(array(
                    'organization' => $orgId,
                    'eikenSchedule' => $eikenSchedule
                ));
        return $applyEiken;
    }
    
    public function getInvitationSettingByEikenSchedule($orgId, $eikenSchedule)
    {
        if(!$this->invitation){
            $this->setInvitationSettingRepository();
        }
        /* @var $invSetting \Application\Entity\InvitationSetting */
        return $invSetting = $this->getEntityManager()->getRepository('Application\Entity\InvitationSetting')->findOneBy(
            array(
                'organizationId' => $orgId,
                'eikenSchedule' => $eikenSchedule
            ));
    }

}
