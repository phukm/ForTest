<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/IBA for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace IBA\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use IBA\Service\ServiceInterface\IBAServiceInterface;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;
use IBA\Form\PolicyForm;
use IBA\Form\RegisterIBAForm;
use stdClass;
use Application\Entity\ApplyIBAOrg;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Dantai\PrivateSession;
use History\Service\MappingIbaResultService;

class IBAController extends AbstractActionController {

    const IBAPolicySessionKey = 'IBAPolicyData';
    const IBA_REGISTER_DATA_KEY = 'IBARegisterData';
    const IBA_STATUS_DRAFT = 'DRAFT';
    const IBA_STATUS_PENDING = 'PENDING';
    const IBA_STATUS_CONFIRMED = 'CONFIRMED';
    const USER_SERVICETYPE_ALL = 'All';
    const USER_SERVICETYPE_IBA = 'IBA';
    const PURPOSE_OTHER = 'other';
    const OPTIONAPPLY_0 = '0';

    protected $org_id;
    protected $orgNo;
    protected $orgNameKanji;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var \IBA\Service\IBAService
     */
    protected $iBAService;

    /**
     *
     * @var EntityManager
     */
    protected $em;
    protected $mappingIBAResultService;

    public function __construct(DantaiServiceInterface $dantaiService, IBAServiceInterface $iBAService, EntityManager $entityManager) {
        $this->dantaiService = $dantaiService;
        $this->iBAService = $iBAService;
        $this->em = $entityManager;
        $user = $this->dantaiService->getCurrentUser();
        $this->org_id = $user['organizationId'];
        $this->orgNo = $user['organizationNo'];
        $this->orgNameKanji = $user['organizationName'];
    }
    
    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->mappingIBAResultService = new MappingIbaResultService($this->getServiceLocator());
        return parent::onDispatch($e);
    }

    public function showAction() {
        $request = $this->getRequest();
        $ibaId = intval($this->params('id', 0));
        $em = $this->getEntityManager();
        $applyIBAOrg = $em->getRepository('\Application\Entity\ApplyIBAOrg');

        $jsonMessage = \Dantai\Utility\JsonModelHelper:: getInstance();
        $jsonMessage->setFail();
        if ($request->isPost()) {
            $dataIBA = $this->params()->fromPost();

            $hasTempData = false;
            $isNoNameKana = false;
            if (!\Dantai\PublicSession::isEmpty(\IBA\Controller\IBAController::IBA_REGISTER_DATA_KEY . $dataIBA['token'])) {
                $tempData = $this->iBAService->getTempRegisterData($dataIBA['token'], true);
                $dataIBA = array_merge($tempData, $dataIBA);
                $dataIBA['orgNameKanji'] = $this->orgNameKanji;
                $hasTempData = true;
                $year = strtotime($dataIBA['testDate']);    
                $year = (date("Y", $year));
                $isNoNameKana = $this->mappingIBAResultService->isNoNameKanna($year);
            }
            
            $isRegisterTestDate = false;
            if (isset($dataIBA['testDate'])) {
                $isRegisterTestDate = $applyIBAOrg->isExistTestDateApplyIBAOrg($this->org_id, self::IBA_STATUS_DRAFT, $dataIBA['testDate'], $ibaId);
            }
            if (!$isRegisterTestDate) {
                if ($dataIBA['status'] === self::IBA_STATUS_PENDING) {
                    $dataIBA['moshikomiId'] = $this->iBAService->getMoshikomiId($ibaId);
                    $isSendMailUpdate = $this->iBAService->isSendMailUpdate($ibaId);
                    $ibaId = $this->saveRegister($dataIBA, $dataIBA['idIBA']);
                    \Dantai\PublicSession::clear(\IBA\Controller\IBAController::IBA_REGISTER_DATA_KEY . $dataIBA['token']);
                    $this->sendMailRequestApply($ibaId, $isSendMailUpdate);
                    $jsonMessage->setSuccess();
                    $jsonMessage->setData([self::IBA_STATUS_PENDING]);
                } else if ($dataIBA['status'] === self::IBA_STATUS_CONFIRMED) {
                    if ($hasTempData) {
                        $ibaId = $this->saveRegister(array_merge($dataIBA, ['status' => self::IBA_STATUS_PENDING]), $dataIBA['idIBA']);
                        \Dantai\PublicSession::clear(\IBA\Controller\IBAController::IBA_REGISTER_DATA_KEY . $dataIBA['token']);
                    }
                    $res = $this->iBAService->sendApplyIBAdataToApi(
                            $this->getEntityManager()->getRepository('Application\Entity\ApplyIBAOrg')->find(intval($dataIBA['idIBA']))
                    );

                    if ($res->kekka !== '10') {
                        $jsonMessage->setFail();
                        $jsonMessage->addMessage($this->translate('Apply_IBA_MSG11'));
                    } else {
                        $jsonMessage->setData([self::IBA_STATUS_CONFIRMED]);
                        $ibaId = $this->saveRegister($dataIBA, $dataIBA['idIBA']);
                        $this->sendMailNoticeComfirmed(
                                $this->getEntityManager()->getRepository('Application\Entity\ApplyIBAOrg')->find(intval($ibaId))
                        );
                        $jsonMessage->setSuccess();
                    }
                }
            } else {
                $jsonMessage->addMessage($this->translate('test-date-duplicate'));
            }
        }
        $form = new RegisterIBAForm($this->getServiceLocator());
        $viewModel = new ViewModel();

        $user = $this->dantaiService->getCurrentUser();

        $token = $this->getRequest()->getQuery('token');
        // get user serviceType
        $em = $this->getEntityManager();
        $userObject = $em->getRepository('Application\Entity\User')->find($user['id']);
        $serviceType = $userObject->getServiceType();

        if (!$this->objectExistsIBA($ibaId) && \Dantai\PublicSession::isEmpty(self::IBA_REGISTER_DATA_KEY . $token)) {
            return $this->notFoundAction();
        }
        if (\Dantai\PublicSession::isEmpty(self::IBA_REGISTER_DATA_KEY . $token)) {
            $IBAItem = ($ibaId > 0) ? $em->getRepository('Application\Entity\ApplyIBAOrg')->find($ibaId) : null;
            $token = false;
        } else {
            $IBAItem = $this->iBAService->getTempRegisterData($token);
        }

        $optionMenu = explode(',', $IBAItem->getOptionMenu());

        $totalPrice = $this->iBAService->calculatePrice($IBAItem);

        $ListBack = '';
        $getListBack = $this->params('back');
        if (!empty($getListBack)) {
            $ListBack = $getListBack;
        }

        $cityItem = $em->getRepository('Application\Entity\City')->findOneBy(array('cityCode' => $IBAItem->getPrefectureCode()));

        $cityName = '';
        if ($cityItem) {
            $cityName = $cityItem->getCityName();
        }

        $status = $IBAItem->getStatus();
        if ($status == self::IBA_STATUS_CONFIRMED || ( $status == self::IBA_STATUS_PENDING && ( $user['roleId'] == 1 || ( $user['roleId'] == 2 && $serviceType == self:: USER_SERVICETYPE_IBA ) ))) {
            $this->setBreadCumbs('app_iba_org_show', $this->translate('iba-show-cf-bc'));
        }
        $objOrg = $em->getRepository('Application\Entity\Organization')->find($this->org_id);
        $orgName = '';
        if(!empty($objOrg)){
            $orgName = $objOrg->getOrgNameKanji();
        }
        return $viewModel->setVariables(array(
                    'form' => $form,
                    'jsMessages' => $jsonMessage,
                    'roleId' => $user['roleId'],
                    'serviceType' => $serviceType,
                    'ibaInfo' => $IBAItem,
                    'id' => $ibaId,
                    'totalPrice' => $totalPrice,
                    'optionMenu' => $optionMenu,
                    'backList' => $ListBack,
                    'cityName' => $cityName,
                    'token' => $token,
                    'isNoNameKana' => isset($isNoNameKana) ? $isNoNameKana : 0,
                    'warningMsg' => $this->translate('msg-warning-noname-kana'),
                    'nameKanjiOrg' => $orgName,
        ));
    }

    public function addAction() {
        $requestId = intval($this->params('id'));
        $back = intval($this->params('back', 0));
        $token = $this->getRequest()->getQuery('token');
        $dataPolicy = \Dantai\PublicSession::getData(self::IBAPolicySessionKey . $token);
        if (empty($dataPolicy) && $requestId <= 0 && \Dantai\PublicSession::isEmpty(self::IBA_REGISTER_DATA_KEY . $token)) {
            return $this->redirect()->toRoute('i-b-a/default', array(
                        'controller' => 'iba',
                        'action' => 'policy',
            ));
        }

        $form = new RegisterIBAForm($this->getServiceLocator());

        $viewModel = new ViewModel();
        $em = $this->getEntityManager();
        $status = self::IBA_STATUS_DRAFT;
        $request = $this->getRequest();

        $IBAObject = NULL;
        $aryOptionMenu = array();
        $oldTestDate = '';

        if ($requestId) {
            $this->setBreadCumbs('app_iba_org_add', $this->translate('iba-edit-bc'));

            if (!$this->objectExistsIBA($requestId)) {
                return $this->notFoundAction();
            }

            $IBAObject = $em->getRepository('Application\Entity\ApplyIBAOrg')->find($requestId);
            if (NULL != $IBAObject) {
                $statusIBA = $IBAObject->getStatus();
                //phucvv
                $aryOptionMenu = explode(',', $IBAObject->getOptionMenu());
                $form->setHydrator(new \Zend\Stdlib\Hydrator\ArraySerializable());
                $form->bind(new \ArrayObject($IBAObject->toArray()));
                //end
                if (!empty($statusIBA)) {
                    $status = $statusIBA;
                }
                $oldTestDate = empty($IBAObject->getTestDate()) ? '' : $IBAObject->getTestDate()->format('Y/m/d');
            }
        }
        if (!\Dantai\PublicSession::isEmpty(self::IBA_REGISTER_DATA_KEY . $token)) {
//             $this->setBreadCumbs('app_iba_org_add',$this->translate('iba-edit-bc'));
            $IBAObject = $this->iBAService->getTempRegisterData($token);
            $status = $IBAObject->getStatus();
            $aryOptionMenu = explode(',', $IBAObject->getOptionMenu());
            $form->setHydrator(new \Zend\Stdlib\Hydrator\ArraySerializable());
            $form->bind(new \ArrayObject($IBAObject->toArray()));
        }
        if ($request->isPost()) {
            $posts = $request->getPost();
            $form->setData($posts);
            // minhbn1 <add DateCurentSmaller Filter >
            if (isset($IBAObject) && !$IBAObject->getId()) {
                // when add new
                $form->setDateCurrentSmallerFilter();
            } elseif (isset($IBAObject) && $IBAObject->getId() && $IBAObject->getStatus() == self::IBA_STATUS_PENDING && $oldTestDate != $posts->testDate) {
                //when update and change the testDate
                $form->setDateCurrentSmallerFilter();
            } elseif (isset($IBAObject) && $IBAObject->getId() && $IBAObject->getStatus() != self::IBA_STATUS_PENDING) {
                //when update and change the testDate
                $form->setDateCurrentSmallerFilter();
            }
            //
            if ($form->isValid()) {
                $registerData = $this->params()->fromPost();
                $registerData['isValid'] = 1;
                $registerData['tempId'] = $requestId;
                $registerToken = $this->iBAService->saveTempRegisterData($this->removeDataNull($registerData), $token);
//                $id = $this->saveRegister($registerData, $requestId, $status);
                $route = ['controller' => 'iba',
                    'action' => 'show',
                    'token' => $registerToken];
                $requestId ? $route['id'] = $requestId : '';
                $back ? $route['back'] = $back : '';
                return $this->redirect()->toRoute('i-b-a/default', $route);
            }
        }
        // set city default
        $OrgObject = $em->getRepository('Application\Entity\Organization')->find($this->org_id);
        $cityOrgDefault = '';
        if (!empty($OrgObject)) {
            if ($OrgObject->getCity()) {
                $cityOrgDefault = $OrgObject->getCity()->getCityCode();
            }
        }
        // get API
        if (empty($IBAObject)) {
            $form->get('prefectureCode')->setValue($cityOrgDefault);
            $result = $this->iBAService->getAPIOrg();
            $dataMapping = $this->mappingDataAPIToEntity($result, $dataPolicy, '');
            if (!empty($dataMapping)) {
                $form->setHydrator(new \Zend\Stdlib\Hydrator\ObjectProperty());
                $form->bind($dataMapping);
            }
        }
        return $viewModel->setVariables(array(
                    'form' => $form,
                    'status' => $status,
                    'id' => $requestId,
                    'IBAObject' => $IBAObject,
                    'aryOptionMenu' => $aryOptionMenu,
                    'back' => $back,
                    'token' => $token,
                    'oldTestDate' => $oldTestDate
        ));
    }

    public function saveDraftAction() {
        $postData = $this->params()->fromPost();
        if ($postData) {
            $postData['isValid'] = 0;
            $postData['status'] = self::IBA_STATUS_DRAFT;
            if (array_key_exists('testDate', $data)) {
                if (!preg_match('/^[0-9]{4}\/(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])$/', $postData['testDate'])) {
                    $postData['testDate'] = '';
                }
            }
            $requestId = intval($postData['idDraft']);

            if (!empty($postData['optionMenu'])) {
                $postData['optionMenu'] = implode(',', array_keys($postData['optionMenu']));
            }
            $this->saveRegister($postData, $requestId);
        }

        return $this->redirect()->toRoute('eikenorg', array(
                    'controller' => 'eikenorg',
                    'action' => 'index'
        ));
    }

    public function sendMailRequestApply($applyIBAOrgId, $isUpdate = false) {
        $em = $this->getEntityManager();
        $viewModel = new ViewModel();
        /* @var $applyIBAOrg \Application\Entity\ApplyIBAOrg */
        $applyIBAOrg = $this->getEntityManager()->getRepository('\Application\Entity\ApplyIBAOrg')->find($applyIBAOrgId);
        if (empty($applyIBAOrg)) {
            return;
        }
        $template = $isUpdate ? 'request-update-apply.phtml' : 'request-apply.phtml';
        $subject = $isUpdate ? $this->translate('subject-mail-update-request-apply-IBA'):$this->translate('subject-mail-request-apply-IBA');
        if (!$isUpdate) {
            $data = array();
            $data['registrationDate'] = date('Y-m-d H:i:s');
            $this->saveRegister($data, $applyIBAOrgId);
        }

        $totalPrice = $this->iBAService->calculatePrice($applyIBAOrg);
        $applyIBA = $applyIBAOrg->toArray('Y/m/d H:i:s');
        $objOrg = $em->getRepository('Application\Entity\Organization')->find($this->org_id);
        $orgName = '';
        if(!empty($objOrg)){
            $orgName = $objOrg->getOrgNameKanji();
        }

        $em = $this->getEntityManager();
        $cityItem = $em->getRepository('Application\Entity\City')->findOneBy(array('cityCode' => $applyIBAOrg->getPrefectureCode()));
        if ($cityItem) {
            $cityName = $cityItem->getCityName();
        }

        $viewModel->setTerminal(true)
                ->setTemplate('iba/mail-template/' . $template)
                ->setVariables(array(
                    'applyIBA' => $applyIBA,
                    'id' => $applyIBAOrgId,
                    'orgName' => $applyIBAOrg->getOrgNameKanji(),
                    'totalPrice' => $totalPrice,
                    'cityName' => $cityName,
                    'orgNameKanji' => $orgName 
        ));
        $mailBody = $this->getServiceLocator()
                ->get('viewrenderer')
                ->render($viewModel);
        $listMail = $this->getEntityManager()
                ->getRepository('\Application\Entity\User')
                ->getListMailServiceManagerIBA();
        $listMailTo = array();
        foreach ($listMail as $row) {
            $listMailTo[] = $row['emailAddress'];
        }
        if (empty($listMailTo)) {
            return;
        }
        $ses = \Dantai\Aws\AwsSesClient::getInstance();
        $ses->send($subject, $mailBody, $listMailTo);
    }

    /**
     * 
     * @param \Application\Entity\ApplyIBAOrg $applyIBA
     */
    public function sendMailNoticeComfirmed($applyIBA) {
        
        $em = $this->getEntityManager();
        $cityItem = $em->getRepository('Application\Entity\City')->findOneBy(array('cityCode' => $applyIBA->getPrefectureCode()));
        if ($cityItem) {
            $cityName = $cityItem->getCityName();
        }

        if ($applyIBA instanceof \Application\Entity\ApplyIBAOrg) {
            $totalPrice = $this->iBAService->calculatePrice($applyIBA);
            $applyIBA = $applyIBA->toArray('Y/m/d H:i:s');
        }

        $objOrg = $em->getRepository('Application\Entity\Organization')->find($this->org_id);
        $orgName = '';
        if(!empty($objOrg)){
            $orgName = $objOrg->getOrgNameKanji();
        }
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true)
                ->setTemplate('iba/mail-template/notice-confirmed.phtml')
                ->setVariables(array(
                    'applyIBA' => $applyIBA,
                    'totalPrice' => $totalPrice,
                    'cityName' => $cityName,
                    'orgNameKanji' => $orgName
        ));

        $mailBody = $this->getServiceLocator()
                ->get('viewrenderer')
                ->render($viewModel);
        $listMailTo = array($applyIBA['mailAddress1']);
        $listMailCc = array();
        filter_var($applyIBA['mailAddress2'], FILTER_VALIDATE_EMAIL) ? $listMailCc[] = $applyIBA['mailAddress2'] : '';
        filter_var($applyIBA['mailAddress3'], FILTER_VALIDATE_EMAIL) ? $listMailCc[] = $applyIBA['mailAddress3'] : '';
        if (empty($listMailTo)) {
            return;
        }
        $ses = \Dantai\Aws\AwsSesClient::getInstance();
        $ses->send($this->translate('subject-mail-notice-comfirmed-apply-IBA'), $mailBody, $listMailTo, $listMailCc);
    }

    public function policyAction() {
        $request = $this->getRequest();
        $form = new PolicyForm($this->getServiceLocator());
        $viewModel = new ViewModel();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $token = uniqid();
                $data = $form->getData();
//                 $data['token'] = $token; 
                \Dantai\PublicSession::setData(self::IBAPolicySessionKey . $token, $data);
                return $this->redirect()->toRoute('i-b-a/default', array(
                            'controller' => 'iba',
                            'action' => 'add',
                            'token' => $token
                ));
            }
        }
        $resultAPI = $this->iBAService->getAPIOrg();
        if(!empty($resultAPI->kekka) && (intval($resultAPI->kekka) == 1)){
            return $viewModel->setVariables(array(
                    'form' => $form,
                    'noORG' => $this->translate('ORG_NOT_VALID')
            ));
        }
        $dataMapping = $this->mappingDataAPIToEntity($resultAPI, '', 'policy');
        if (!empty($dataMapping)) {
            $form->setHydrator(new \Zend\Stdlib\Hydrator\ObjectProperty());
            $form->bind($dataMapping);
        }
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('id', 'iba-policy');
        $page->setAction('');
        return $viewModel->setVariables(array(
                    'form' => $form,
        ));
    }

   

    //@TODO: need refactor
    public function mappingDataAPIToEntity($apiData, $dataPolicy, $screen = '') {
        if (empty($apiData) || $apiData->kekka !== '10') {
            return false;
        }
        $data = new stdClass();
        $zipcode = $apiData->zipcode;
        $zipcode1 = $zipcode ? substr($zipcode, 0, 3) : null;
        $zipcode2 = $zipcode ? substr($zipcode, - 4) : null;

        $data->organizationNo = $apiData->dantaino;
        $data->orgNameKanji = $apiData->dantaimei;
        $data->pICName = $apiData->sekininsha_sei . $apiData->sekininsha_mei;
        $data->zipCode1 = $zipcode1;
        $data->zipCode2 = $zipcode2;
        $data->mailName1 = (count($dataPolicy) > 0 && !empty($dataPolicy)) ? $dataPolicy['firtNameKanji'] . $dataPolicy['lastNameKanji'] : '';
        $data->address1 = $apiData->addr1;
        $data->mailAddress1 = (count($dataPolicy) > 0 && !empty($dataPolicy)) ? $dataPolicy['mailAddress'] : '';
        $data->telNo = $apiData->tel;
        $data->fax = $apiData->fax;
        if ($screen == 'policy') {
            $data->mailAddress = $apiData->email;
            $data->confirmEmail = $apiData->email;
            $userInfo = $this->dantaiService->getCurrentUser();
            $data->firtNameKanji = $userInfo['firstName'];
            $data->lastNameKanji = $userInfo['lastName'];
        }
        return $data;
    }

    public function saveRegister($data, $id = 0, $status = '') {

        $data = $this->removeDataNull($data);
        $token = array_key_exists('token', $data) ? $data['token'] : '';
        $objectManager = $this->serviceLocator->get('Doctrine\ORM\EntityManager');
        $hydrator = new DoctrineObject($objectManager, 'Application\Entity\ApplyIBAOrg');

        $em = $this->getEntityManager();
        $org = $em->getRepository('Application\Entity\Organization')->find(intval($this->org_id));

        if (!empty($data['rankNo'])) {
            $data['rankNo'] = intval($data['rankNo']);
        }

        $data = $this->trimArray($data);

        PrivateSession::setData('iba-confirm-action',(isset($data['status']) && $data['status'] === self::IBA_STATUS_CONFIRMED) ? true : false);

        if ($id > 0) {
            $IBA = $em->getRepository('Application\Entity\ApplyIBAOrg')->find(intval($id));
            // Set session flag to log activities
            $createFlag = PrivateSession::getData('create-activity-log-flag');
            if ($createFlag == null)
            {
                PrivateSession::setData('create-activity-log-flag', false);
                PrivateSession::setData('iba-confirm-page-title', $data['iba-confirm-page-title']);
            }
        } else {
            $IBA = new ApplyIBAOrg();
            $dataPolicy = \Dantai\PublicSession::getData(self::IBAPolicySessionKey . $token);
            if (!empty($dataPolicy)) {
                $data = array_merge($data, (array) $dataPolicy);
            }
            // Set session flag to log activities
            PrivateSession::setData('create-activity-log-flag', true);
            \Dantai\PublicSession::clear(self::IBAPolicySessionKey . $token);
        }
        $data = array_map(function($v) {
            if ('' == $v) {
                return null;
            }
            return $v;
        }, $data);
        $IBA = $hydrator->hydrate($data, $IBA);
        if (!empty($org)) {
            $IBA->setOrganization($org);
            $IBA->setOrganizationNo($org->getOrganizationNo());
        }
        $em->persist($IBA);
        $em->flush();

        return $IBA->getId();
    }

    public function trimArray($data) {
        $result = array_map(function (&$value) {
            return trim($value);
        }, $data);

        return $result;
    }

    public function isRegisterTestDateAction($date = '') {
        $testDate = date_create($this->params()->fromPost('testDate'));
        if (!empty($date)) {
            $idIBA = $date;
        } else {
            $idIBA = $this->params()->fromPost('id');
        }
        $em = $this->getEntityManager();
        $applyIBAOrg = $em->getRepository('\Application\Entity\ApplyIBAOrg');
        $isTestDateIBA = $applyIBAOrg->isExistTestDateApplyIBAOrg($this->org_id, self::IBA_STATUS_DRAFT, $testDate, $idIBA);
        $jsonModel = \Dantai\Utility\JsonModelHelper::getInstance();
        if (!$isTestDateIBA) {
            $jsonModel->setFail();
        } else {
            $jsonModel->setSuccess();
        }
        return new \Zend\View\Model\JsonModel($jsonModel->toArray());
    }

    public function objectExistsIBA($id = '') {
        $em = $this->getEntityManager();
        $repository = $em->getRepository('Application\Entity\ApplyIBAOrg');
        $validator = new \DoctrineModule\Validator\ObjectExists(array(
            'object_repository' => $repository,
            'fields' => array(
                'id',
                'isDelete',
                'organizationId'
            )
        ));

        $userInfo = \Dantai\PrivateSession::getData('userIdentity');

        return $validator->isValid(array(
                    'id' => $id,
                    'isDelete' => '0',
                    'organizationId' => $userInfo['organizationId']
        ));
    }

    public function removeDataNull($data) {
        if (!empty($data)) {
            if (array_key_exists('purpose', $data)) {
                if ($data['purpose'] !== self::PURPOSE_OTHER) {
                    $data['purposeOther'] = '';
                }
            }

            if (array_key_exists('optionApply', $data)) {
                if ($data['optionApply'] === self::OPTIONAPPLY_0) {
                    $data['optionMenu'] = '';
                    $data['questionNo'] = '';
                    $data['rankNo'] = '';
                }
            }
        }
        return $data;
    }

    protected function setBreadCumbs($id = '', $text = '') {
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('id', $id);
        $page->setLabel($text);
    }

    private function translate($message) {
        return $this->getServiceLocator()->get('MvcTranslator')->translate($message);
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function mailAction() {
        $em = $this->getEntityManager();

        $IBARecord = $em->getRepository('Application\Entity\ApplyIBAOrg')->find('298');
        $IBAItem = $IBARecord->toArray('Y/m/d H:i:s');
        $totalPrice = $this->iBAService->calculatePrice($IBARecord);

        $cityItem = $em->getRepository('Application\Entity\City')->findOneBy(array('cityCode' => $IBARecord->getPrefectureCode()));
        if ($cityItem) {
            $cityName = $cityItem->getCityName();
        }

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true)
                ->setTemplate('iba/mail-template/' . 'request-apply.phtml')
//                ->setTemplate('iba/mail-template/' . 'request-update-apply.phtml')
//                ->setTemplate('iba/mail-template/' . 'notice-confirmed.phtml')
                ->setVariables(array(
                    'applyIBA' => $IBAItem,
                    'orgName' => '外五',
                    'totalPrice' => $totalPrice,
                    'cityName' => $cityName
        ));
        return $viewModel;
    }
    /**
     * @author minhbn1<minhbn1@fsoft.com.vn>
     * Show policy only
     * @return ViewModel
     */
     public function onlyPolicyAction() {
        $request = $this->getRequest();
        $viewModel = new ViewModel();
        return $viewModel;
    }
}
