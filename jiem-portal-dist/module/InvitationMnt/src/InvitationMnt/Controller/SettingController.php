<?php

/*
 * Date: 16/06/2015
 * @method Setting : list, update
 */

namespace InvitationMnt\Controller;

use Application\Entity\SemiVenue;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Doctrine\ORM\EntityManager;
use InvitationMnt\Service\ServiceInterface\SettingServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Dantai\PrivateSession;
use Zend\Json\Json;
use Dantai\Utility\DateHelper;
use InvitationMnt\InvitationConst;

class SettingController extends AbstractActionController {

    const CROSS_EDITING = 'cross-edit-invtn-setting';
    const CROSS_EDITING_MESG = 'cross-edit-invtn-setting-message';
    const CROSS_EDITING_DATA = 'cross-edit-invtn-setting-data';
    
    protected $error = '';

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var SettingServiceInterface
     */
    protected $settingService;

    /**
     *
     * @var EntityManager
     */
    protected $em;
    protected $dateStart;
    protected $dateEnd;
    protected $orgId;
    protected $organizationCode;
    protected $organizationNo;

    public function __construct(DantaiServiceInterface $dantaiService, SettingServiceInterface $settingService, EntityManager $entityManager) {
        $this->dantaiService = $dantaiService;
        $this->settingService = $settingService;
        $this->em = $entityManager;
        $this->dateStart = date('Y');
        $this->dateEnd = date('Y') + 2;
        $user = $this->dantaiService->getCurrentUser();
        $this->orgId = $user['organizationId'];
        $this->organizationNo = $user['organizationNo'];
        $this->organizationCode = (int) $user['organizationCode'];
    }

    public function indexAction() {
        $trans = $this->params()->fromQuery('trans');
        $searchVisible = 0;
        if ($trans == 1) {
            $checkValid = $this->checkEikenSchedule($this->getCurrentEikenSchedule()->id);
            if ($checkValid) {
                return $this->redirect()->toRoute(null, array(
                    'module' => 'invitation-mnt',
                    'controller' => 'setting',
                    'action' => 'add'
                ));
            } else {
                return $this->redirect()->toRoute(null, array(
                    'module' => 'invitation-mnt',
                    'controller' => 'setting',
                    'action' => 'index'
                ));
            }
        }
        $this->dateStart = 2010;
        $app = $this->getServiceLocator()->get('Application');
        $request = $app->getRequest();
        $config = $this->getServiceLocator()->get('Config');
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $inv = array();
        $resultError = false;
        if ($request->isPost()) {
            PrivateSession::setData('postInvSetting', $request->getPost());
            PrivateSession::setData('search', 1);
            return $this->redirect()->toRoute(null, array('module' => 'invitation-mnt', 'controller' => 'setting', 'action' => 'index'));
        }
        $data = PrivateSession::getData('postInvSetting');
        $searchVisible = PrivateSession::getData('search');
        if (PrivateSession::isEmpty('postInvSetting')) {
            $data['EikenSchedule'] = $this->getCurrentEikenSchedule()->id;
            $data['year'] = date('Y');
        }
        if (!empty($data['year'])) {
            $invSetting = $this->getEntityManager()->getRepository('Application\Entity\InvitationSetting')->getInvSetting($data['EikenSchedule'], $this->orgId, $data['year']);
            if (!empty($invSetting)) {
                foreach ($invSetting as $key => $val) {
                    $inv[] = array(
                        'Id' => $val['id'],
                        'Year' => $val['year'],
                        'Kai' => $val['kai'],
                        'HallType' => $config['HallType'][$val['hallType']],
                        'InvitationType' => $config['InvitationType'][$val['invitationType']],
                        'PaymentType' => empty($config['PaymentType'][$val['paymentType']]) ? '' : $config['PaymentType'][$val['paymentType']],
                        'Deadline' => empty($val["deadLine"]) ? '' : $val["deadLine"]->format('Y/m/d')
                    );
                }
            } else {
                $this->error = $translator->translate('MSG013');
                $resultError = true;
            }
        } else {
            $this->error = $translator->translate('MSG001');
        }
        $invEikenSchedule = $this->getEikenSchedule();
        if (empty($invEikenSchedule['year'])) {
            $this->error = $translator->translate('MSG013');
        }
        if (!empty($this->flashMessenger()->getMessages())) {
            $this->error = current($this->flashMessenger()->getMessages());
        }
        
        $jsMessages = Json::encode($this->dantaiService->getCrossEditingMessage('Application\Entity\InvitationSetting'));

        return new ViewModel(array(
            'CurrentEikenSchedule' => $this->getCurrentEikenSchedule($data['year'], $data['EikenSchedule']),
            'Translate' => $this->getTranslation(),
            'jsMessages' => $jsMessages,
            'resultError' => $resultError,
            'error' => $this->error,
            'inv' => $inv,
            'invYear' => $invEikenSchedule['year'],
            'invDeadline' => $invEikenSchedule['deadline'],
            'searchVisible' => $searchVisible
        ));
    }

    public function getCurrentEikenSchedule($year = '', $id = '') {
        if (!empty($id)) {
            $val = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->find($id);

            return (object) array('year' => $val->getYear(), 'kai' => $val->getKai(), 'id' => $val->getId());
        } else {
            $data = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->findBy(array('year' => empty($year) ? date('Y') : $year), array('kai' => 'ASC'));
            if (!empty($data)) {
                foreach ($data as $val) {
                    if (!empty($val->getDeadlineTo()) && date('Y-m-d') <= $val->getDeadlineTo()->format('Y-m-d')) {
                        return (object)array('year' => $val->getYear(), 'kai' => $val->getKai(), 'id' => ($year != '') ? '' : $val->getId());
                    }
                }
            }
        }

        return (object) array('year' => $year, 'kai' => '', 'id' => '');
    }

    public function clearAction() {
        // clear session page.
        PrivateSession::clear('postInvSetting');

        return $this->redirect()->toRoute(null, array('module' => 'invitation-mnt', 'controller' => 'setting', 'action' => 'index'));
    }

    public function addAction() {
        $em = $this->getEntityManager();
        $this->dateEnd = date('Y') + 1;
        $data = $this->getData();
        $data["paymentMethod"] = $this->settingService->getPaymentMethod($this->orgId, $this->getCurrentEikenSchedule()->id);
        $data['year'] = $this->getCurrentEikenSchedule()->year;
        $data['kai'] = $this->getCurrentEikenSchedule()->kai;
        $data['semiMainVenue'] = '';
        $semiMainVenue = $this->dantaiService->getSemiMainVenueOrigin($this->orgId, $this->getCurrentEikenSchedule()->id);
        $data['semiMainVenue'] = $semiMainVenue;

        // R4 S2 UAT update
        $applyEikenOrg = $this->settingService->getApplyEikenOrg($this->orgId, $this->getCurrentEikenSchedule()->id);
        $data["refundStatus"] = $applyEikenOrg ? $applyEikenOrg->getStatusRefund() : null;
        $dataSpceial = $em->getRepository('Application\Entity\SpecialPrice')
                            ->findOneBy(array(
                                'organizationId' => $this->orgId,
                                'year' => $this->getCurrentEikenSchedule()->year,
                                'kai' => $this->getCurrentEikenSchedule()->kai
                            ));
        $data['flgSpecial'] = $dataSpceial ? 1: 0;
        if (!empty($this->flashMessenger()->getMessages())) {
            $data['error'] = current($this->flashMessenger()->getMessages());
        }

        return new ViewModel($data);
    }

    public function editAction() {
        $this->dateEnd = date('Y') + 1;
        $data = $this->getData();
        if ($this->flashMessenger()->getMessages()) {
            $data['error'] = current($this->flashMessenger()->getMessages());
        }
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $id = $this->params()->fromRoute('id', 0);
        $em = $this->getEntityManager();
        /* @var $invSetting \Application\Entity\InvitationSetting */
        $invSetting = $em->getRepository('Application\Entity\InvitationSetting')->findOneBy(array('organizationId'=>$this->orgId,'id'=>$id));
        if ($invSetting) {
            //        is Generate Letter
            if(!empty($invSetting->getStatus()) && $invSetting->getStatus() == 1 && \Dantai\PublicSession::isOrgAdminOrOrgUser()){
                $data['flagPopup'] = 1;
            }
            $data['invSetting'] = $invSetting;
            $data['paymentMethod'] = $this->settingService->getPaymentMethod($this->orgId,$invSetting->getEikenSchedule()->getId());
            // R4 S2 UAT update
            $applyEikenOrg = $this->settingService->getApplyEikenOrg($this->orgId,$this->getCurrentEikenSchedule()->id);
            $data["refundStatus"] = $applyEikenOrg ? $applyEikenOrg->getStatusRefund() : null;

            $semiVenue = $this->dantaiService->getSemiMainVenueOrigin($this->orgId, $invSetting->getEikenScheduleId());

            $data['showBeneficiary'] = !empty($semiVenue) ? true : false;
            $dataSpecial = $em->getRepository('Application\Entity\SpecialPrice')
                                ->findOneBy(array(
                                'organizationId' => $this->orgId,
                                'year' => $this->getCurrentEikenSchedule()->year,
                                'kai' => $this->getCurrentEikenSchedule()->kai
                            ));
            $data['flgSpecial'] = $dataSpecial ? 1: 0;
            return new ViewModel($data);
        } else {
            return $this->redirect()->toRoute(null, array(
                        'module' => 'invitation-mnt',
                        'controller' => 'setting',
                        'action' => 'index'
            ));
        }
    }

    public function updateAction() {
        $app = $this->getServiceLocator()->get('Application');
        $request = $app->getRequest();
        $data = $request->getPost();  
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $returnRoute = array();
        
        if ($this->validate('update', $returnRoute)) {
            $this->updateData($data, 'update');
            return $this->redirect()->toRoute(null, $returnRoute);
        } else {
            $this->flashMessenger()->addMessage($this->error);
            return $this->redirect()->toRoute(null, $returnRoute);
        }
    }

    public function showAction() {
        $data = $this->getData();
        if (!empty($this->flashMessenger()->getMessages())) {
            $data['error'] = current($this->flashMessenger()->getMessages());
        }
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $id = $this->params()->fromRoute('id', 0);
        if ($id) {
            $invSetting = $this->settingService->getInvitationSetting($id);
        }else{
            return $this->redirect()->toRoute(null, array(
                        'module' => 'invitation-mnt',
                        'controller' => 'setting',
                        'action' => 'index'
            ));
        }
        if (!empty($invSetting)) {
            $data['invSetting'] = $invSetting;
            $data['status'] = $this->checkCurrentDate($invSetting->getEikenScheduleId());
            $data['paymentMethod'] = $this->settingService->getPaymentMethod($this->orgId,$invSetting->getEikenSchedule()->getId());
            $semiVenue = $this->dantaiService->getSemiMainVenueOrigin($this->orgId, $invSetting->getEikenSchedule()->getId());
            $data['showBeneficiary'] = !empty($semiVenue) ? true : false;

            return new ViewModel($data);
        } else {
            return $this->redirect()->toRoute(null, array(
                        'module' => 'invitation-mnt',
                        'controller' => 'setting',
                        'action' => 'index'
            ));
        }
    }

    public function saveAction() {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        
        if ($this->validate()) {
            $request = $this->getServiceLocator()->get('Application')->getRequest();
            if (!$request->isPost()) {
                return $this->redirect()->toUrl('/error/index');
            }
            $data = $request->getPost();
            if ($this->checkEikenSchedule((int) $data["EikenSchedule"])) {
                $invi = $this->updateData($data, 'insert');
                $this->flashMessenger()->clearCurrentMessages();
                $result = array();
                $result['error'] = false;
                if ($data["InvitationType"] == '2' || $data["InvitationType"] == '1') {
                    $result['type'] = 1;
                    $result['message'] = $translator->translate('MSG091');
                } else {
                    $result['type'] = 2;
                    $result['message'] = $translator->translate('MSG092');
                    $result['id'] = $invi->getId();
                }
                $response = $this->getResponse();
                $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
                $response->setContent(json_encode($result));

                // Change session ApplyEikenOrg status
                $currentStatus = PrivateSession::getData('applyEikenStatus');
                $currentStatus['hasInvitationSetting'] = true;
                PrivateSession::setData('applyEikenStatus', $currentStatus);
                return $response;
            } else {
                $kai = $this->getEntityManager()
                        ->getRepository('Application\Entity\EikenSchedule')
                        ->find((int) $data["EikenSchedule"])
                        ->getKai();
                $this->flashMessenger()->addMessage(sprintf($translator->translate('MSG030'), $data['year'], $kai));

                return $this->redirect()->toRoute(null, array(
                            'module' => 'invitation-mnt',
                            'controller' => 'setting',
                            'action' => 'add'
                ));
            }
        } else {
            $this->flashMessenger()->addMessage($this->error);
            
            return $this->redirect()->toRoute(null, array(
                        'module' => 'invitation-mnt',
                        'controller' => 'setting',
                        'action' => 'add'
            ));
        }
    }

    function getData() {
        $config = $this->getServiceLocator()->get('Config');
        $Organization = $this->getEntityManager()
                ->getRepository('Application\Entity\Organization')
                ->findOneBy(array(
            'id' => $this->orgId
        ));
        $OrgNameKanji = $Organization ? $Organization->getOrgNameKanji() : '';
        $EikenLevel = $this->getEntityManager()
                ->getRepository('Application\Entity\EikenLevel')
                ->findAll(); // get from EikenLevel
        $invEikenSchedule = $this->getEikenSchedule();
        $combini = $this->getEntityManager()
                ->getRepository('Application\Entity\Combini')
                ->getCombinis();
        $template = $this->getEntityManager()
                ->getRepository('Application\Entity\TemplateInvitationMsg')
                ->getTemplateInvitationMsg();
        $doubleEikenMessage = $this->getEntityManager()
                ->getRepository('Application\Entity\DoubleEikenMessages')
                ->getMessages();
        $data = array(
            'CurrentEikenSchedule' => $this->getCurrentEikenSchedule(),
            'OrganizationCode' => $config['OrganizationCode'],
            'InvOrganizationCode' => $this->organizationCode,
            'OrgNameKanji' => $OrgNameKanji,
            'EikenLevel' => $EikenLevel,
            'template' => $template,
            'combini' => $combini,
            'doubleEiken' => $doubleEikenMessage,
            'invYear' => $invEikenSchedule['year'],
            'invDeadline' => $invEikenSchedule['deadline'],
            'InvitationType' => $config['InvitationType'],
            'HallType' => $config['HallType'],
            'ExamDay' => $config['ExamDay'],
            'PaymentType' => $config['PaymentType'],
            'OrganizationPayment' => $config['OrganizationPayment'],
            'PersonalPayment' => $config['PersonalPayment'],
            'Translate' => $this->getTranslation(),
            'definitionSpecial' => $this->dantaiService->getDefinitionSpecial($this->organizationNo)
        );

        return $data;
    }

    function updateData($data, $type = 'insert') {
        $em = $this->getEntityManager();
        $paymentMethodExist = true; // true if payment method has been created
        $flagPaymentMethod = true; // true if payment method can be changed
        if ($type == 'insert') {
            $inv = new \Application\Entity\InvitationSetting();
            // Set session flag to log activities
            PrivateSession::setData('create-activity-log-flag', true);
        } else {
            $inv = $em->getReference('Application\Entity\InvitationSetting', (int) $data['id']);           
            // Set session flag to log activities
            PrivateSession::setData('create-activity-log-flag', false);
        }
        
        // get payment method by OrganizationId and EikenScheduleId
        $paymentMethod = $this->settingService->getPaymentMethod($this->orgId,(int) $data["EikenSchedule"]);
        if(!isset($paymentMethod)){
            $paymentMethodExist = false;
            $paymentMethod = new \Application\Entity\PaymentMethod(); 
        }

        foreach ($data as $name => $val) {
            if (is_array($val)) {
                $data[$name] = json_encode($val);
            }
        }
        $data["Deadline"] = empty($data["Deadline"]) ? null : new \DateTime(date('Y-m-d H:i:s', strtotime($data["Deadline"])));
        $data["issueDate"] = empty($data["issueDate"]) ? null : new \DateTime(date('Y-m-d H:i:s', strtotime($data["issueDate"])));
        if ($data["InvitationType"] == 3) {
            $data["ExamDay"] = null;
            $data["ExamPlace"] = null;
            $data["Deadline"] = null;
            $data["PaymentType"] = null;
            $data["PersonalPayment"] = null;
            $data["OrganizationPayment"] = null;
            $data['DoubleEiken'] = null;
            $data["Combini"] = null;
            $data["PrintMessage"] = null;
            $data['PaymentBill'] == null;
            $data['PublicFunding'] == null;
        }
        if ($data["InvitationType"] == 3 || $data["InvitationType"] == 4 || empty($data["PrintMessage"])) {
            $data["Message2"] = null;
            $data["Message1"] = null;
            $data['TemplateMsg2'] = null;
            $data['TemplateMsg1'] = null;
        }
        if ($data["InvitationType"] == 1) {
            $data['TemplateMsg2'] = null;
            $data["Message2"] = null;
        }
        
        // set invitation setting data.
        $inv->setOrganization($em->getReference('Application\Entity\Organization', $this->orgId));
        $inv->setEikenSchedule(empty($data["EikenSchedule"]) ? null : $em->getReference('Application\Entity\EikenSchedule', (int) $data["EikenSchedule"]));
        $inv->setHallType(empty($data["HallType"]) ? 0 : (int) $data["HallType"]);
        $inv->setListEikenLevel(empty($data["ListEikenLevel"]) ? null : (string) $data["ListEikenLevel"]);
        $inv->setInvitationType(empty($data["InvitationType"]) ? 0 : (int) $data["InvitationType"]);
        $inv->setPrintMessage(empty($data["PrintMessage"]) ? 0 : $data["PrintMessage"]);
        $inv->setExamDay(empty($data["ExamDay"]) ? null : (string) $data["ExamDay"]);
        $inv->setExamPlace(empty($data["ExamPlace"]) ? null : (string) $data["ExamPlace"]);
        $inv->setDeadline($data["Deadline"]);
        $inv->setDoubleEiken(empty($data["DoubleEiken"]) ? null : $em->getReference('Application\Entity\DoubleEikenMessages', (int) $data["DoubleEiken"]));
        $inv->setDoubleEikenMessage(empty($data["DoubleEikenMessage"]) ? null : (string) $data["DoubleEikenMessage"]);
        $inv->setTemplateMsg2(empty($data['TemplateMsg2']) ? null : $em->getReference('Application\Entity\TemplateInvitationMsg', (int) $data['TemplateMsg2']));
        $inv->setMessage2(empty($data["Message2"]) ? null : (string) $data["Message2"]);
        $inv->setTemplateMsg1(empty($data['TemplateMsg1']) ? null : $em->getReference('Application\Entity\TemplateInvitationMsg', (int) $data['TemplateMsg1']));
        $inv->setMessage1(empty($data["Message1"]) ? null : (string) $data["Message1"]);
        $inv->setOrganizationName(empty($data['orgName']) ? null : (string) $data['orgName']);
        $inv->setPrincipalName(empty($data['principalName']) ? null : (string) $data['principalName']);
        $inv->setIssueDate($data["issueDate"]);
        $inv->setPersonalTitle($data["personalTitle"]);
        //set nguoi duoc huong gia discount
        $inv->setBeneficiary($data["Beneficiary"] === null ? null : (int)$data['Beneficiary']);
        if($flagPaymentMethod){
            $inv->setCombini(empty($data["Combini"]) ? null : (string) $data["Combini"]);
            $inv->setPersonalPayment(empty($data["PersonalPayment"]) ? null : (string) $data["PersonalPayment"]);
            $inv->setOrganizationPayment(!array_key_exists('OrganizationPayment', $data) ? null : (int) $data["OrganizationPayment"]);
            $inv->setPaymentType(($data['PaymentType'] == null) ? null : (int) $data["PaymentType"]);
        }
        
        if('update' == $type || 'insert' == $type){
            // add or update invitation setting.
            $em->persist($inv);
            $em->flush();
            $id = $inv->getId();
            $em->clear();
        }
        
        // set entity reference
        if($paymentMethodExist){
            $paymentMethod = $em->getReference('Application\Entity\PaymentMethod', (int) $paymentMethod->getId());
        }     
        // set payment method data.
        $paymentMethod->setPaymentBill(($data['PaymentBill'] == null) ? null : (int) $data["PaymentBill"]);
        $paymentMethod->setPublicFunding(($data['PublicFunding'] == null) ? null : (int) $data["PublicFunding"]);
        $paymentMethod->setOrganization($em->getReference('Application\Entity\Organization', $this->orgId));
        $paymentMethod->setEikenSchedule(empty($data["EikenSchedule"]) ? null : $em->getReference('Application\Entity\EikenSchedule', (int) $data["EikenSchedule"]));
        $paymentMethod->setInvitationSetting($em->getReference('Application\Entity\InvitationSetting', $id));
        if('insert' == $type || ('update' == $type && $flagPaymentMethod)){

            // add new payment method.
            $em->persist($paymentMethod);
            $em->flush();
            $em->clear();
        }

        return $inv;
    }
    
    function validateDate($date, $format = 'Y-m-d H:i:s') {
        $d = \DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) == $date;
    }

    function validate($type = 'create', &$returnRoute = array()) {
        $app = $this->getServiceLocator()->get('Application');
        $request = $app->getRequest();
        $returnRoute = array(
            'module' => 'invitation-mnt',
            'controller' => 'setting',
            'action' => 'index'
        );
        
        if ($request->isPost()) {
            $translator = $this->getServiceLocator()->get('MVCTranslator');
            $data = $request->getPost()->toArray();

            if(!empty($data['EikenSchedule'])){
                $appEndDate = $this->em->getReference('Application\Entity\EikenSchedule', $data['EikenSchedule'])
                    ->getDeadlineTo()
                    ->format('Y/m/d');
            }
            
            if(!empty($data['issueDate'])){
                $this->error = $this->settingService->validateIssueDate($data['issueDate'], $appEndDate);        
            }
            
            if('update' == $type){
                // Check gen-invitation progress
                $checkProcessLog = $this->em->getRepository('Application\Entity\ProcessLog')->findOneBy(array('orgId'=>$this->orgId,'scheduleId'=>$data['EikenSchedule']));
                if($checkProcessLog){
                    $this->flashMessenger()->addMessage($translator->translate('MSG073'));
                    $returnRoute['action'] = 'edit';
                    $returnRoute['id'] = $data['id'];
                    
                    return false;
                }
            }
            
            if (empty($data['EikenSchedule']) || empty($data['InvitationType']) || !isset($data['HallType']) ) {
                $this->error = $translator->translate('MSG001');
            } else if (isset($data['HallType']) && isset($data['InvitationType']) && $data['InvitationType'] != 3 && !isset($data['ExamDay'])) {
                $this->error = $translator->translate('MSG001');
            } else if (empty($data['ListEikenLevel'])) {
                $this->error = $translator->translate('MSG038');
            } else if ($this->checkCurrentDate($data["EikenSchedule"])) {
                $EikenSchedule = $this->getEntityManager()
                        ->getRepository('Application\Entity\EikenSchedule')
                        ->find($data["EikenSchedule"]);
                $this->error = sprintf($translator->translate('MSG029'), $data['year'], $EikenSchedule->getKai());
            }
            if ($data['InvitationType'] != 3) {
                if (!$this->validateDate($data["Deadline"], 'Y/m/d')) {
                    $this->error = $translator->translate('MSG011');
                } else
                if ($this->checkDeadline($data["Deadline"], (int)$data["EikenSchedule"])) {
                    $this->error = sprintf($translator->translate('MSG057'), $data['year'], $data["EikenSchedule"]);
                } elseif (!isset($data['PublicFunding']) || !isset($data['PaymentBill'])){
                    $this->error = $translator->translate('MSG001');
                }    
            }
            
            if($this->error) {
                $returnRoute['action'] = 'edit';
                $returnRoute['id'] = $data['id'];
                
                return false;
            }
            
            return true;
        }

        return false;
    }

    function checkDeadline($deadline, $eikenScheduleId) {
        if (empty($deadline) && empty($eikenScheduleId)) {
            return false;
        }
        $eikenSchedule = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->find($eikenScheduleId);
        if (empty($eikenSchedule)) {
            return false;
        }
        $deadlineTo = $eikenSchedule->getDeadlineTo()->format('Y/m/d');
        if ($deadline <= $deadlineTo) {
            return false;
        }
        return true;
    }

    function checkCurrentDate($EikenScheduleId) {
        $EikenSchedule = $this->getEntityManager()
                ->getRepository('Application\Entity\EikenSchedule')
                ->find($EikenScheduleId);
        if (!empty($EikenSchedule)) {
            $deadlineTo = $EikenSchedule->getDeadlineTo()->format('Y/m/d');
            $currentDate = date('Y/m/d');
            if ($currentDate <= $deadlineTo) {
                return false;
            } else
                return true;
        } else {
            return true;
        }
    }

    function checkEikenSchedule($EikenScheduleId) {
        $check = $this->getEntityManager()
                ->getRepository('Application\Entity\InvitationSetting')
                ->findOneBy(array(
            'eikenSchedule' => $EikenScheduleId,
            'organization' => $this->orgId
        ));
        if (empty($check)) {
            return true;
        } 
        return false;  
    }

    function getEikenSchedule() {
        $deadline = array();
        $data = $this->getEntityManager()
                ->getRepository('Application\Entity\EikenSchedule')
                ->getAllEikenSchedule();
        if (!empty($data)) {
            foreach ($data as $val) {
                $year[$val['year']] = $val['year'];
                $result[$year[$val['year']]][$val['kai']] = $val['id'];
                // $deadline[$year[$val['year']]][$val['kai']]['deadlineFrom'] = empty($val['deadlineFrom']) ? '' : $val['deadlineFrom']->format('Y/m/d');
                // $deadline[$year[$val['year']]][$val['kai']]['deadlineTo'] = empty($val['deadlineTo']) ? '' : $val['deadlineTo']->format('Y/m/d');
                $deadline[$year[$val['year']]][$val['id']] = empty($val['deadlineTo']) ? '' : $val['deadlineTo']->format('Y/m/d');
            }
        }
        $results['deadline'] = $deadline;
        // Customer set date
        for ($i = $this->dateEnd; $i >= $this->dateStart; $i--) {
            $results['year'][$i] = empty($result[$i]) ? array() : $result[$i];
        }

        return $results;
    }

    function checkExitsAction() {
        $app = $this->getServiceLocator()->get('Application');
        $request = $app->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $data = $this->checkEikenSchedule($data['EikenSchedule']);
        } else
            $data = true;
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($data));

        return $response;
    }

    public function getTranslation() {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $sl  = $this->getServiceLocator();
        $vhm = $sl->get('viewhelpermanager');
        $url = $vhm->get('url');
        $urlPolicy = $url('org-mnt/default', array(
                                                'controller' => 'org',
                                                'action' => 'policy-grade-class'
                                            ));
        $messages = array(
            'MSG404' => $translator->translate('MSG404'),
            'MSG001' => $translator->translate('MSG001'),
            'MSG011' => $translator->translate('MSG011'),
            'MSG013' => $translator->translate('MSG013'),
            'MSG029' => $translator->translate('MSG029'), // year-kai
            'MSG030' => $translator->translate('MSG030'),
            'MSG031' => $translator->translate('MSG031'),
            'MSG037' => $translator->translate('MSG037'),
            'MSG038' => $translator->translate('MSG038'),
            'MSG041' => $translator->translate('MSG041'),
            'MSG056' => $translator->translate('MSG056'),
            'MSG057' => $translator->translate('MSG057'),
            'MSG053' => $translator->translate('MSG053'),
            'MSG029-1' => $translator->translate('MSG029-1'),
            'MSG030-1' => $translator->translate('MSG030-1'),
            'MSG039' => $translator->translate('MSG039'),
            'MSG031-1' => $translator->translate('MSG031-1'),
            'MSG045' => $translator->translate('MSG045'),
            'SGHMSG48' => $translator->translate('SGHMSG48'),
            'MSG_Allow' => $translator->translate('MSG_Allow'),
            'MSG_Contact' => $translator->translate('MSG_ContactEiken'),
            'R4_MSG10' => $translator->translate('R4_MSG10'),
            'R4_MSG11' => $translator->translate('R4_MSG11'),
            'msgAllowChangeTestSite' => $translator->translate('msgAllowChangeTestSite'),
            'msgNotAllowChangeTestSite' => $translator->translate('msgNotAllowChangeTestSite'),
            'msgConfirmChangeTestSite' => $translator->translate('msgConfirmChangeTestSite'),
            'msgConfirmChangeTestSiteHallType' => $translator->translate('msgConfirmChangeTestSiteHallType'),
            'btnContinueChange' => $translator->translate('btnContinueChange'),
            'R4_MSG_when_refund_status_equal_2_warning_collective_payment' => $translator->translate('R4_MSG_when_refund_status_equal_2_warning_collective_payment'),
            'MSGChangeBeneficiary' => $translator->translate('MSGChangeBeneficiary'),
            'okConfirmGenarateEx' => $translator->translate('okConfirmGenarateEx'),
            'cancelConfirmGenarateEx' => $translator->translate('cancelConfirmGenarateEx'),
            'MSGConfirmGenarateEx' => $translator->translate('MSGConfirmGenarateEx'),
            'MSGPopupWaringGradeClassECSetting' => sprintf($translator->translate('MSGPopupWaringGradeClassECSetting'), $urlPolicy),
        );

        return json_encode($messages);
    }

    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    /**
     * author : PhucVV3
     * function :previewAction
     * description : preview template for required select
     * date : 28/7/2015
     */
    public function previewAction() {
        $app = $this->getServiceLocator()->get('Application');

        $request = $app->getRequest();
        if (!$request->isPost()) {
            return $this->redirect()->toRoute(null, array(
                'module'     => 'invitation-mnt',
                'controller' => 'setting',
                'action'     => 'index'
            ));
        }

        $data = $request->getPost();

        foreach ($data as $name => $val) {
            if (is_array($val)) {
                $data[$name] = json_encode($val);
            }
        }
        switch ($data['InvitationType']) {
            case 1:
                // eikne-version
                return $this->previewViewModel($data, 'eiken', 'Page 2', 'under');
                break;
            case 2:
                // 'school-version';
                return $this->previewViewModel($data, 'school');
                break;
            case 3:
                // 'only-enavi';
                return $this->previewViewModel($data, 'einavi', '');
                break;
            case 4:
                // 'payment-only';
                if ($data['PaymentType'] == 0) {
                    return $this->previewViewModel($data, 'payment-personal', '');
                } else {
                    return $this->previewViewModel($data, 'payment-organization', '');
                }
                break;
            default:
                break;
        }

        return new ViewModel();
    }

    // phucVV3
    // change day E to Japan
    public function changeDay($day) {
        $aryDay = array(
            'Fri' => '金',
            'Sat' => '土',
            'Sun' => '日',
            'Mon' => '月',
            'Tue' => '火',
            'Wed' => '水',
            'Thu' => '木'
        );

        return ($aryDay[$day]) ? $aryDay[$day] : '日';
    }

    // end
    /**
     * author : PhucVV3
     * function :previewViewModel
     * param : $data request from form submit
     * button btnPreview
     * description : return value to ViewModel
     * date : 28/7/2015
     */
    public function previewViewModel($data, $template, $page = 'Page 2', $style = '') {
        $numDay = $data['ExamDay'];
        $eikenScheduleDay = $this->em->getRepository('Application\Entity\EikenSchedule')->findOneBy(array(
            'year' => $data['year'],
            'kai' => $data['EikenScheduleHidden']
        ));
        $sysConfig = $this->em->getRepository('Application\Entity\SystemConfig')->findOneBy(array(
            'configKey' => 'eiken_ads'
        ));
        $deadLine = date_create($data['Deadline']);
        $dayRound2 = $this->changeDay(($eikenScheduleDay->getRound2Day2ExamDate()
                        ->format('D')));
        $dayRound2A = $this->changeDay(($eikenScheduleDay->getRound2Day1ExamDate()
                        ->format('D')));
        $dayRound2B = $this->changeDay(($eikenScheduleDay->getRound2Day2ExamDate()
                        ->format('D')));
        $examDay = $this->getDayByEikenSchedule($eikenScheduleDay,$numDay);
        $deadDay = $this->changeDay(date('D', strtotime($data['Deadline'])));
        $combini = explode('"', substr($data["Combini"], 1, strlen($data["Combini"]) - 2));
        $aryCombini = array();
        for ($i = 0; $i < count($combini); $i++) {
            ($combini[$i] != '' && $combini[$i] != ',') ? $aryCombini[$i] = $combini[$i] : '';
        }
        $combini = array();
        if (!empty($data["Combini"])) {
            $listCombini = json_decode($data["Combini"], true);
            $combini = $this->em->getRepository('\Application\Entity\Combini')->getCombinisByIds($listCombini);
        }
        $arayEchedule = array();
        $hallTypeC = $this->getServiceLocator()->get('Config');
        $hallType = ($data['HallType'] == 0) ? $hallTypeC['HallType'][0] : $hallTypeC['HallType'][1];
        $priceOfHall = array();
        $semiMainVenue = !empty($data['Beneficiary']) && $data['Beneficiary'] == InvitationConst::BENEFICIARY_IS_STUDENT ? 1 : 0;
        if (!empty($data["ListEikenLevel"])) {
            $listEikenLevel = json_decode($data["ListEikenLevel"], true);
            $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
            $priceEikenLevel = $dantaiService->getListPriceOfOrganization($this->organizationNo, $listEikenLevel);
            $priceOfHall = $priceEikenLevel ? $priceEikenLevel[$semiMainVenue ? 0 : $data['HallType']] : array();
        }
        $viewModel = new ViewModel(array(
            'templ' => $template,
            'page' => $page,
            'yearDeadLine' => date_format($deadLine, 'Y'),
            'monthDeadLine' => date_format($deadLine, 'm'),
            'dayDeadLine' => date_format($deadLine, 'd'),
            'deadDay' => $deadDay,
            'hallType' => $hallType,
            'sysConfig' => $sysConfig,
            'combini' => $aryCombini,
            'eikenScheduleDay' => $eikenScheduleDay,
            'arayEchedule' => $arayEchedule,
            'dayRound2' => $dayRound2,
            'dayRound2A' => $dayRound2A,
            'dayRound2B' => $dayRound2B,
            'priceOfHall' => $priceOfHall,
            'data' => $data,
            'examDay' =>$examDay,
            'combini' => $combini,
            'orgName' => $data['orgName'] != null ? $data['orgName'] : '',
            'personalTitle' => $data['personalTitle'] != null ? $data['personalTitle'] : '',
            'principalName' => $data['principalName'] != null ? $data['principalName'] : '',
            'issueYear' => $data['issueDate'] != null ? (date('Y',strtotime($data['issueDate']))-1988) : '',
            'issueMonth' => $data['issueDate'] != null ? date('m',strtotime($data['issueDate'])) : '',
            'issueDate' => $data['issueDate'] != null ? date('d',strtotime($data['issueDate'])) : '',
            'listEikenLevel' => $data['ListEikenLevel'] ? $data['ListEikenLevel'] : ''
        ));
        $viewModel->setTemplate('invitation-mnt/setting/template/main-template.phtml');
        $viewModel->setTerminal(true);
        $this->layout()->setTerminal(true);
        $this->layout()->terminate();
        return $viewModel;
    }

    // end
    public function getDayByEikenSchedule($eikenScheduleDay,$numDay) {
        if (NULL != $eikenScheduleDay) {
            $exam = array();
            if ($numDay == 1) {
                $exam[0][0] = $eikenScheduleDay->getFriDate() ? $eikenScheduleDay->getFriDate()->format('Y-m-d') : '';
                $exam[0][1] = '金';
            }
            if ($numDay == 2) {
                $exam[0][0] = $eikenScheduleDay->getSatDate() ? $eikenScheduleDay->getSatDate()->format('Y-m-d') : '';
                $exam[0][1] = '土';
            }
            if ($numDay == 3) {
                $exam[0][0] = $eikenScheduleDay->getSunDate() ? $eikenScheduleDay->getSunDate()->format('Y-m-d') : '';
                $exam[0][1] = '日';
            }
            if ($numDay == 4) {
                $exam[0][0] = $eikenScheduleDay->getFriDate() ? $eikenScheduleDay->getFriDate()->format('Y-m-d') : '';
                $exam[0][1] = '金';
                $exam[1][0] = $eikenScheduleDay->getSatDate() ? $eikenScheduleDay->getSatDate()->format('Y-m-d') : '';
                $exam[1][1] = '土';
            }
        }
        return $exam;
    }
    
    public function loadExpiredPaymentDateAction() {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $result = array(
            'status' => 0,
            'content' => sprintf($translator->translate('DESCRIPTION_EXPIRED_PAYMENT_DATE'), '...', '...'),
        );
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $year = isset($params['year']) ? intval($params['year']) : 0;
            $kai = isset($params['kai']) ? intval($params['kai']) : 0;
            if ($year > 0 && $kai > 0) {
                /* @var $eikenSchedule \Application\Entity\EikenSchedule */
                $eikenSchedule = $this->em->getRepository('Application\Entity\EikenSchedule')->findOneBy(array(
                    'year' => $year,
                    'kai' => $kai,
                    'isDelete' => 0,
                ));
                if ($eikenSchedule) {
                    $combiniPaymentDate = $eikenSchedule->getCombiniDeadline() != Null ? $eikenSchedule->getCombiniDeadline()->format('Y/m/d') : '';
                    $creditPaymentDate = $eikenSchedule->getCreditCardDeadline() != Null ? $eikenSchedule->getCreditCardDeadline()->format('Y/m/d') : '';
                    $result['status'] = 1;
                    $result['content'] = sprintf($translator->translate('DESCRIPTION_EXPIRED_PAYMENT_DATE'), date('Y年n月j日',strtotime($combiniPaymentDate)), date('Y年n月j日',strtotime($creditPaymentDate)));
                }
            }
        }
        return $this->getResponse()->setContent(Json::encode($result));
    }
    
    // function check show popup warning for grade and class : #F1GNCJIEMDPR6-8
    public function isNotShowPopupAction() {
        $params = $this->getRequest()->getPost();
        $year = isset($params['year']) ? intval($params['year']) : 0;
        $eikenScheduleId = isset($params['scheduleId']) ? intval($params['scheduleId']) : 0;
        
        /* @var $inv \Application\Entity\ProcessLog */
        $process = $this->em->getRepository('Application\Entity\ProcessLog')->findOneBy(array(
            "orgId" => $this->orgId,
            "scheduleId" => $eikenScheduleId
        ));
        
        $result = array('status' => 1);
        if(!$process){
            if ($year) {
                $isNotShowMSG = $this->dantaiService->isNotShowMSGGradeClass($year,$this->orgId);
                if($isNotShowMSG === false){
                    $result['status'] = 0;
                }
            }
        }
        return $this->getResponse()->setContent(Json::encode($result));
    }
}
