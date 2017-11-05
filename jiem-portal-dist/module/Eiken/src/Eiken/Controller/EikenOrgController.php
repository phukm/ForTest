<?php

namespace Eiken\Controller;

use Application\Entity\InvitationSetting;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Eiken\Form\EikenOrg\CreateForm;
use Zend\View\Model\ViewModel;
use Eiken\Form\EikenOrg\EikenExamForm;
use Eiken\Form\EikenOrg\RegistrantInfoForm;
use Zend\Json\Json;
use Eiken\Service\ServiceInterface\ApplyEikenOrgServiceInterface;
use Zend\Session\Container as SessionContainer;
use Dantai\PrivateSession;
use Doctrine\ORM\EntityManager;
use Dantai\Utility\PHPExcel;
use Dantai\Utility\CharsetConverter;
use Dantai\Utility\DateHelper;
use Eiken\EikenConst;
use Dantai\PublicSession;

class EikenOrgController extends AbstractActionController {

    use \Application\Controller\ControllerAwareTrait;

    /**
     *
     * @var \Eiken\Service\ApplyEikenOrgService
     */
    protected $eikenOrgService;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var EntityManager
     */
    protected $em;
    protected $id_org = 0;
    protected $organizationNo;
    protected $organizationName;
    protected $userId;
    protected $roleId;

    public function __construct(DantaiServiceInterface $dantaiService, ApplyEikenOrgServiceInterface $eikenOrgService, EntityManager $entityManager) {
        $this->dantaiService = $dantaiService;
        $this->eikenOrgService = $eikenOrgService;
        $this->em = $entityManager;
        $user = PrivateSession::getData('userIdentity');
        $this->id_org = $user['organizationId'];
        $this->organizationNo = $user['organizationNo'];
        $this->userId = $user['userId'];
        $this->roleId = $user['roleId'];
        $this->organizationName = $user['organizationName'];
    }

    public function onDispatch(\Zend\Mvc\MvcEvent $e) {
        $routeMatch = $e->getRouteMatch();
        $action = $routeMatch->getParam('action');
        if (in_array($action, array(
                    'navigator',
                    'policy',
                    'standard-confirmation',
                    'create',
                    'confirmation'
                ))) {
            $eikenData = $this->eikenOrgService->getApplyEikenLevel();
            $isCreate = !empty($eikenData['applyEikenOrg']) ? 1 : 0;
            if (!$this->eikenOrgService->isValidTime($isCreate)) {
                return $this->redirect()->toRoute('eikenorg', array(
                            'action' => 'invalid'
                ));
            }
        }

        /**
         * @todo
         * Create new:
         * - Navigator -> policy -> create -> confirmation -> standard-confirmation
         * ==> Must check with each action:
         *      + Valid referer: stay as it is
         *      + Invalid referer: redirect to Navigator
         * Update exist:
         * - List exam -> detail -> update -> confirmation -> standard-confirmation
         * ==> Must check with: Navigator, policy
         *      + Redirect to List exam
         * ==> Must check with each of the others action:
         *      + Valid referer: stay as it is
         *      + Invalid referer: redirect to List exam
         * */
        $this->validateQuery($action);
        parent::onDispatch($e);
    }

    private function validateQuery($action) {
        $jiemSession = new SessionContainer('eikQueryValidation');
        // Case create new
        if (empty($this->eikenOrgService->getApplyEikenOrg())) {
            switch ($action) {
                case 'navigator':
                    return $this->redirect()->toRoute('eikenorg', array(
                                'action' => 'policy'
                    ));
                    break;
                case 'policy':
//                     if ($jiemSession->lastAction != $action && (empty($jiemSession->lastAction) || ($jiemSession->lastAction != 'navigator' && $jiemSession->lastAction != 'create'))) {
//                         $this->redirect()->toRoute('eikenorg', array(
//                             'action' => 'navigator'
//                         ));
//                     } else
                    $jiemSession->lastAction = 'policy';
                    break;
                case 'create':
                    $jiemPolicySession = new SessionContainer('jiemRegistration');
                    if ($jiemSession->lastAction != $action && (empty($jiemSession->lastAction) || ($jiemSession->lastAction != 'policy' && $jiemSession->lastAction != 'confirmation') || empty($jiemPolicySession->firstName))) {
                        return $this->redirect()->toRoute('eikenorg', array(
                                    'action' => 'policy'
                        ));
                    } else
                        $jiemSession->lastAction = 'create';
                    break;
                case 'confirmation':
                    if ($jiemSession->lastAction != $action && (empty($jiemSession->lastAction) || ($jiemSession->lastAction != 'create' && $jiemSession->lastAction != 'standard-confirmation'))) {
                        return $this->redirect()->toRoute('eikenorg', array(
                                    'action' => 'policy'
                        ));
                    } else
                        $jiemSession->lastAction = 'confirmation';
                    break;
                case 'standard-confirmation':
                    if ($jiemSession->lastAction != $action && (empty($jiemSession->lastAction) || ($jiemSession->lastAction != 'confirmation'))) {
                        return $this->redirect()->toRoute('eikenorg', array(
                                    'action' => 'policy'
                        ));
                    } else
                        $jiemSession->lastAction = 'standard-confirmation';
                    break;
                default:
                    break;
            }
        }
        else {
            switch ($action) {
                case 'index':
                    $jiemSession->lastAction = 'index';
                    break;
                case 'applyeikendetails':
                    if ($jiemSession->lastAction != $action && (empty($jiemSession->lastAction) || ($jiemSession->lastAction != 'index' && $jiemSession->lastAction != 'create'))) {
                        return $this->redirect()->toRoute('eikenorg', array(
                                    'action' => 'index'
                        ));
                    } else
                        $jiemSession->lastAction = 'applyeikendetails';
                    break;
                case 'create':
//                     if ($jiemSession->lastAction != $action && (empty($jiemSession->lastAction) || ($jiemSession->lastAction != 'applyeikendetails' && $jiemSession->lastAction != 'confirmation'))) {
//                         $this->redirect()->toRoute('eikenorg', array(
//                             'action' => 'index'
//                         ));
//                     } else
                    $jiemSession->lastAction = 'create';
                    break;
                case 'confirmation':
                    if ($jiemSession->lastAction != $action && (empty($jiemSession->lastAction) || ($jiemSession->lastAction != 'create' && $jiemSession->lastAction != 'standard-confirmation'))) {
                        return $this->redirect()->toRoute('eikenorg', array(
                                    'action' => 'index'
                        ));
                    } else
                        $jiemSession->lastAction = 'confirmation';
                    break;
                case 'standard-confirmation':
                    if ($jiemSession->lastAction != $action && (empty($jiemSession->lastAction) || ($jiemSession->lastAction != 'confirmation'))) {
                        return $this->redirect()->toRoute('eikenorg', array(
                                    'action' => 'index'
                        ));
                    } else
                        $jiemSession->lastAction = 'standard-confirmation';
                    break;
                case 'navigator':
                    return $this->redirect()->toRoute('eikenorg', array(
                                'action' => 'index'
                    ));
            }
        }
        return true;
    }

    public function indexAction() {
        $viewModel = new ViewModel();
        $form = new EikenExamForm();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $em = $this->getEntityManager();

        $config = $this->getServiceLocator()->get('config');

        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;

        $search = $this->dantaiService->getSearchCriteria($this->getEvent(), $this->params()->fromPost());

        if ($this->isPost() && $search['token']) {
            return $this->redirect()->toRoute(null, array('action' => 'index', 'search' => $search['token']));
        }
        $ddlYear = 0; // date('Y');
        $ddlKai = 0;
        $ddlExamName = NULL;
        $dtEndDate = NULL;
        $dtStartDate = NULL;

        $currentYear = (int) date("Y");

        // $search = $this->params()->fromPost();

        if (!empty($search)) {
            $ddlYear = DateHelper::getCurrentYear();
            if (isset($search['ddlYear']))
                $ddlYear = $search['ddlYear'];

            if (isset($search['ddlKai']))
                $ddlKai = $search['ddlKai'];

            if (isset($search['ddlExamName']))
                $ddlExamName = $search['ddlExamName'];

            if (isset($search['dtEndDate']))
                $dtEndDate = $search['dtEndDate'];

            if (isset($search['dtStartDate']))
                $dtStartDate = $search['dtStartDate'];
        }
        $listyear = array();
        $listyear[''] = '';
        for ($i = $currentYear + 2; $i >= 2010; $i --) {
            $listyear[$i] = $i;
        }
        // Fix bug F1GJIEM-1791
        $config['ExamName']['IBA'] = '英検' . $config['ExamName']['IBA'];
        $form->get("ddlYear")
                ->setValueOptions($listyear)
                ->setValue($ddlYear);
        $form->get("ddlExamName")
                ->setValueOptions($config['ExamName'])
                ->setValue($ddlExamName);

        $form->get("dtEndDate")->setValue($dtEndDate);
        $form->get("dtStartDate")->setValue($dtStartDate);
        $form->get("ddlKai")->setValue($ddlKai);
        $translator = $this->getServiceLocator()->get('MVCTranslator');

        /**
         * @var \Application\Service\ServiceInterface\DantaiServiceInterface
         */
        // $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
//         $crossMessages = $dantaiService->getCrossEditingMessage('Application\Entity\ApplyEikenLevel');
        //taivh cmt $crossMessages
        $jsMessages = array(
            'MSGdatefomat' => $translator->translate('MSGdatefomat'),
            'MSGdatecompare' => $translator->translate('MSGdatecompare')
        );

        $eikenschedulerepository = $em->getRepository('Application\Entity\EikenSchedule');
        if ($ddlExamName == 'IBA') {
            $ddlYear = '';
        }

        $paginator = $eikenschedulerepository->listEikenExam($this->id_org, $ddlExamName, $ddlYear, $ddlKai, $dtStartDate, $dtEndDate);
        $paginatorSet = $this->eikenOrgService->getCheckCurrentKaibyScheId($paginator->getItems($offset, $limit));
        $viewModel->setVariables(array(
            'paginator' => $paginator,
            'noRecord' => $translator->translate('MSG13'),
            'eikenschedule' => $paginatorSet,
            'form' => $form,
            'jsMessages' => json_encode($jsMessages),
            'page' => $page,
            'numPerPage' => $limit,
            'searchVisible' => isset($search['token']) ? 1 : 0,
            'param' => isset($search['token']) ? $search['token'] : '',
            'roleId' => $this->roleId
        ));
        return $viewModel;
    }

    public function showAction() {
        $config = $this->getServiceLocator()->get('Config')['eiken_config']['api'];

        $result = \Dantai\Api\UkestukeClient::getInstance()->callEir2a02($config, array(
            'dantaino' => 'ABCD1234'
        ));
    }

    public function newAction() {
        
    }

    public function editAction() {
        
    }

    /**
     * Eiken application form for create/edit
     */
    public function createAction() {
        $jiemSession = new SessionContainer('jiemRegistration');
        // get current invitation setting to check HallType (hoi truong chinh hoac hoi truong chuan)
        $em = $this->getEntityManager();
        $viewModel = new ViewModel();
        $form = new CreateForm($this->getServiceLocator());

        $config = $this->serviceLocator->get('config');
        $listRefundOption = $config['refundStatusOption'];

        $invSetting = $this->eikenOrgService->getInvitationSetting();
        if (isset($invSetting['paymentType']) && $invSetting['paymentType'] == 0) {
            unset($listRefundOption[1]);
            $form->get("refundStatus")->setValueOptions($listRefundOption);
        }
        $form->get("refundStatus")->setValueOptions($listRefundOption);

        $eikenData = $this->eikenOrgService->getApplyEikenLevel();
        if(!empty($eikenData['applyEikenOrg']['status']) && $eikenData['applyEikenOrg']['status'] == 'N/A'){
            return $this->redirect()->toRoute('eikenorg', array('action' => 'index'));
        }
        $detailEikenOrgDetail = array();
        if (!empty($eikenData['noOfExpectationStandard']['origin'])) {
            $detailEikenOrgDetail = $eikenData['noOfExpectationStandard']['origin'];
        }
        $orgCodeInBD = array('00', '01', '05', '20', '40', '41', '50', '51', '52', '53', '54', '55', '10', '15', '16', '22', '25', '30', '31', '35', '36', '45', '46','58','59');
        $orgCodeAllowThreeDay = array("00", "01", "05", "20", "40", "41", "50", "51", "52", "53", "54", "55","58","59");
        $orgBR72 = array("10", "15", "16", "20", "22", "25", "30", "31", "35", "36", "45", "46", "50");
//        $orgRejectLessThan10Standard is list org had show MSG when apply standard < 10 human
        $orgRejectLessThan10Standard = array('10', '15', '16', '20', '22', '25', '30', '31', '35', '36', '45', '46', '50');

        $orgCode = $this->eikenOrgService->getOrganizationCode();
        if (empty($orgCode)) {
            $detailEikenOrgDetail['isAllowThreeDay'] = false;
            $detailEikenOrgDetail['orgBR72'] = 1;
            $detailEikenOrgDetail['isRejectLessThan10Standard'] = false;
        } else {
            $detailEikenOrgDetail['isAllowThreeDay'] = in_array($orgCode, $orgCodeAllowThreeDay) || !in_array($orgCode, $orgCodeInBD);
            $detailEikenOrgDetail['isRejectLessThan10Standard'] = in_array($orgCode, $orgRejectLessThan10Standard);
            if (in_array($orgCode, $orgBR72)) {
                $detailEikenOrgDetail['orgBR72'] = 0;
            } else {
                $detailEikenOrgDetail['orgBR72'] = 1;
            }
        }
        $year = date('Y');
        $kaiNumber = $this->eikenOrgService->getKaiNumber();
        $eikenScheduleId = $this->eikenOrgService->getEikenScheduleId();
        $isCreate = $eikenData['applyEikenOrg'] ? 1 : 0;
        $detailEikenOrgDetail['isValidTime'] = $this->eikenOrgService->isValidTime($isCreate);
        $inviSetting = $this->eikenOrgService->getInviSettingByEikenScheduleIdAndOrg($eikenScheduleId, $this->id_org);

        $eikenLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')->getNumberStudentApplyEikenBySatellite($this->id_org, $eikenScheduleId);

        foreach ($eikenLevel as $value) {
            if ($value['hallType'] == 1 || $value['eikenLevelId'] == 1 || $value['eikenLevelId'] == 2) {
                $eikenData['publicSite'][$value['eikenLevelId']] = $value['number'];
            } else if ($value['hallType'] == 0) {
                $eikenData['groupSite'][$value['eikenLevelId']] = $value['number'];
            }
        }
        
        
        list($arrayRender,$arrayHeader,$standPupilOfKyu,$isOrgDiscount,$standPupilOfKyuTotal,$flgHadGradeDiscount) = $this->eikenOrgService->getDataSpecial($this->id_org, $eikenScheduleId);
        
        $totalPupilMainHall = $this->eikenOrgService->getTotalPupilMainHall($this->id_org, $eikenScheduleId);
        
        $isSemiMainVenue = $this->dantaiService->getSemiMainVenueOrigin($this->id_org, $eikenScheduleId);
        $MSGCreateGrade = $this->translate('MSGPleaseCreateGrade');
        $isDisableStandard = $isSemiMainVenue && !empty($inviSetting) && $inviSetting->getHallType() == 1;
        $viewModel->setVariables(array(
            'totalKyuPayment' => $eikenData['totalKyuPayment'],
            'hallType' => $eikenData['hallType'],
            'form' => $form,
            'applyEikenLevel' => $eikenData,
            'year' => $year,
            'eikenScheduleId' => $eikenScheduleId,
            'kaiNumber' => $kaiNumber,
            'jsMessages' => $this->getTranslation(),
            'eikenOrgDetailJS' => Json::encode($detailEikenOrgDetail),
            'eikenOrgDetail' => $detailEikenOrgDetail,
            'registrationInfor' => array(
                'firstName' => $jiemSession->firstName,
                'lastName' => $jiemSession->lastName,
                'emailAddress' => $jiemSession->emailAddress,
                'phoneNumber' => $jiemSession->phoneNumber,
                'cityId' => $jiemSession->cityId,
                'districtId' => $jiemSession->districtId,
                'confirmMailAddress' => $jiemSession->confirmMailAddress
            ),
            'hasRegisterd' => $eikenData['hasRegisterd'],
            'totalRegistereds' => $eikenData['totalRegistereds'],
            'inviSetting' => $inviSetting,
            'isInvitationSetting' => $this->eikenOrgService->isInvitationSettingValue(),
            'hadCreate' => $eikenData['applyEikenOrg'],
            'arrayRender' => $arrayRender ? json_encode($arrayRender) : '',
            'arrayHeader' => $arrayHeader,
            'standPupilOfKyu' => $standPupilOfKyu,
            'totalPupilMainHall' => $totalPupilMainHall,
            'isOrgDiscount' => $this->eikenOrgService->isSpecialOrg($this->id_org),
            'isSemiMainVenue' => $isSemiMainVenue,
            'standPupilOfKyuTotal' => $standPupilOfKyuTotal,
            'MSGCreateGrade' => $MSGCreateGrade,
            'flgHadGradeDiscount' => $flgHadGradeDiscount,
            'isDisableStandard'   => $isDisableStandard,
        ));
        if (empty($eikenData['applyEikenOrg'])) {
            $this->getIndexBreadCumbs(null, 'create');
        } else
            $this->getIndexBreadCumbs(null, 'edit');
        if (isset($detailEikenOrgDetail['statusRefund'])) {
            $form->get("refundStatus")->setValue($detailEikenOrgDetail['statusRefund']);
        }

        return $viewModel;
    }

    protected $mainDetail;

    /**
     * This actin for call Ajax
     */
    public function saveAction() {
        $status = 'create';
        $isExistApplyEiken = $this->eikenOrgService->checkExistApplyEiken($this->id_org);
        if ($isExistApplyEiken == EikenConst::EXIST) {
            $status = 'update';
        }
        PrivateSession::setData(EikenConst::APPLY_ACTION, $status);
        PrivateSession::setData(EikenConst::APPLY_DATA_FROM_POST, $this->params()->fromPost());

        $eikenOrgNo = $this->params()->fromPost('eikenOrgNo');
        $eikenData = $this->eikenOrgService->getApplyEikenLevel();
        $isCreate = $eikenData['applyEikenOrg'] ? 1 : 0;
        $isValidTime = $this->eikenOrgService->isValidTime($isCreate);
        $data = array(
            'isValidTime' => $isValidTime
        );
        if (!$isValidTime) {
            return $this->getResponse()->setContent(Json::encode($data));
        }
        $rfStatus = array(
            'rfStatus' => false
        );

        if ($this->params()->fromPost('refundStatus') === '') {
            return $this->getResponse()->setContent(Json::encode($rfStatus));
        }
        if ($status == 'update') {
            $config = $this->serviceLocator->get('config');
            $listRefund = $config['refundStatusOption'];
            $listRefundByvalue = array_flip($listRefund);
            $lastestLog = $this->eikenOrgService->getPreviousLog();
            $refundStatusFull = array();
            if (array_key_exists('refundDetail', $lastestLog)) {
                $refundStatusFull = $lastestLog['refundDetail'] ? explode('→', $lastestLog['refundDetail']) : array();
            }
            $oldstatus = '';
            if (count($refundStatusFull) > 0 && $refundStatusFull[count($refundStatusFull) - 1] != '') {
                $oldstatus = $refundStatusFull[count($refundStatusFull) - 1];
                PrivateSession::setData(EikenConst::APPLY_REFUND_STATUS_FROM_DATABASE, $listRefundByvalue[$oldstatus]);
            } else {
                PrivateSession::setData(EikenConst::APPLY_REFUND_STATUS_FROM_DATABASE, $this->eikenOrgService->getPastRefundStatus());
            }
        }

        // save data to EikenOrgDetail
        $this->eikenOrgService->saveInformationData($this->params()
                        ->fromPost());
        return $this->getResponse()->setContent(Json::encode($data));
    }

    public function saveStatusAction() {
        // Save public funding and payment bill status if exist
        $fundingStatus = PrivateSession::getData('fundingStatus');
        PrivateSession::clear('fundingStatus');
        $paymentStatus = PrivateSession::getData('paymentStatus');
        PrivateSession::clear('paymentStatus');

        $isExistPaymentMethod = $this->eikenOrgService->getPaymentMethodExistValue();
        if ($isExistPaymentMethod == EikenConst::NOT_EXIST) {
            if ($fundingStatus != null && $paymentStatus != null) {
                $result = $this->eikenOrgService->createPaymentMethod($fundingStatus, $paymentStatus, $this->getEntityManager());
            }
        }
        return $this->getResponse()->setContent(Json::encode($result));
    }

    public function updateAction() {
        
    }

    public function destroyAction() {
        
    }

    public function viewAction() {
        
    }

    public function checkPaymentTypeAction() {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $invitationSetting = $this->eikenOrgService->getInvitationSetting();
        $data = array();
        if ($invitationSetting) {
            $paymentType = $invitationSetting['paymentType'];
            $data = array(
                'paymentType' => $paymentType,
                'message' => $translator->translate('individualSelectedOnInvitationScreen')
            );
        }

        return $this->getResponse()->setContent(Json::encode($data));
    }

    /**
     * This actin for call Ajax
     */
    public function getOrgNameAction() {
        $eikenOrgNo = $this->params()->fromPost('eikenOrgNo');
        $orgInfor = $this->eikenOrgService->getOrganizationByNumber($eikenOrgNo);
        if (!empty($orgInfor)) {
            $data = array(
                'isEmpty' => false,
                'eikenOrgName' => $orgInfor[0]['orgNameKanji']
            );
        } else {
            $data = array(
                'isEmpty' => true,
                'eikenOrgName' => 'empty'
            );
        }
        return $this->getResponse()->setContent(Json::encode($data));
    }

    public function navigatorAction() {
        $viewModel = new ViewModel();
        $form = new CreateForm($this->getServiceLocator());
        $viewModel->setVariables(array(
            'form' => $form,
            'jsMessages' => $this->getTranslation()
        ));
        return $viewModel;
    }

    public function policyAction() {
        $viewModel = new ViewModel();
        $form = new CreateForm($this->getServiceLocator());
        // Get citiesList
        $form->get("cityId")->setValueOptions($this->eikenOrgService->getCityList());
        // If create new, must be get from session
        $jiemSession = new SessionContainer('jiemRegistration');
        if (isset($jiemSession->firstName) && $jiemSession->firstName != '' && isset($jiemSession->orgId) && $jiemSession->orgId == $this->eikenOrgService->getOrganizationId()) {
            $eikenOrg = array(
                'firtNameKanji' => $jiemSession->firstName,
                'lastNameKanji' => $jiemSession->lastName,
                'mailAddress' => $jiemSession->emailAddress,
                'cityId' => $jiemSession->cityId,
                'districtId' => $jiemSession->districtId,
                'confirmEmail' => $jiemSession->confirmMailAddress
            );
        } else {
            // clear session
            $jiemSession->getManager()->getStorage()->clear('jiemRegistration');
        }
        // If absolutely new - Must get fom API
        if (empty($eikenOrg)) {
            $eikenOrg = $this->eikenOrgService->getOrganizationInfoByApi();
            if (empty($eikenOrg))
                $apiError = 1;
        }
        if ($eikenOrg && isset($eikenOrg['cityId'])) {
            $cityId = $eikenOrg['cityId'];
            $form->get('cityId')->setValue($cityId);
            // Get coressponding district
            $form->get('districtId')->setValueOptions($this->eikenOrgService->getExamLocationList($cityId, 0));
            if ($eikenOrg['districtId'])
                $form->get('districtId')->setValue($eikenOrg['districtId']);
        }

        $viewModel->setVariables(array(
            'form' => $form,
            'jsMessages' => $this->getTranslation(),
            'eikenOrg' => $eikenOrg,
            'apiError' => isset($apiError) ? 1 : 0,
            'isExistApplyEiken' => $this->eikenOrgService->checkExistApplyEiken($this->id_org,true)
        ));
        return $viewModel;
    }

    public function standardConfirmationAction() {
        // check exist payment method
        $paymentMethod = $this->eikenOrgService->getPaymentMethodExistValue();

        $viewModel = new ViewModel();
        $form = new RegistrantInfoForm();
        //Generate tocken to prevent duplicate request
        $tocken = md5(time());
        // get organiz
        // $form->bind($eikenOrg);
        $currentDate = $this->eikenOrgService->genCurrentYearJapan();
        $viewModel->setVariables(array(
            'form' => $form,
            'jsMessages' => $this->getTranslation(),
            'userId' => $this->eikenOrgService->getUserId(),
            'generalInfo' => $this->eikenOrgService->standardConfirmation(),
            'tocken' => $tocken,
            'currentDate' => $currentDate,
            'isInvitationSetting' => $this->eikenOrgService->isInvitationSettingValue(),
            'paymentMethod' => $paymentMethod,
            'isExistApplyEiken' => $this->eikenOrgService->checkExistSubmittedApplyEiken($this->id_org)
        ));
        PrivateSession::setData('applyEikenTocken', $tocken);
        //$this->getIndexBreadCumbs($this->eikenOrgService->getKaiNumber(), 'confirmation');
        return $viewModel;
    }

    public function saveRegistrantAction() {
        // Send info to API
        $result = $this->eikenOrgService->sendPolicyInfoToApi($this->params());
        if ($result) {
            $jiemSession = new SessionContainer('jiemRegistration');
            // $this->session->ex = true;
            $jiemSession->firstName = $this->params()->fromPost('txtFirstName');
            $jiemSession->lastName = $this->params()->fromPost('txtLastName');
            $jiemSession->emailAddress = $this->params()->fromPost('txtEmailAddress');

            $jiemSession->cityId = $this->params()->fromPost('cityId');
            $jiemSession->districtId = $this->params()->fromPost('districtId');
            $jiemSession->confirmMailAddress = $this->params()->fromPost('txtConfirmEmailAddress');
            $jiemSession->orgId = $this->eikenOrgService->getOrganizationId();
            $isExit = $this->eikenOrgService->checkExistApplyEiken($this->id_org,true);
            if ($isExit == EikenConst::EXIST) {
                $this->eikenOrgService->updateInformationPolicy($jiemSession);
            }
        }

        return $this->getResponse()->setContent(Json::encode(array(
                            'isSuccess' => $result ? 1 : 0
        )));
    }

    public function confirmationAction() {
        $em = $this->getEntityManager();
        // check exist payment method
        $paymentMethod = $this->eikenOrgService->getPaymentMethodExistValue();

        $eikenData = $this->eikenOrgService->getRealExpectNoForConfirmation($this->eikenOrgService->getApplyEikenLevel());
        $detailEikenOrgDetail = array();
        if (!empty($eikenData['noOfExpectationStandard']['origin'])) {
            $detailEikenOrgDetail = $eikenData['noOfExpectationStandard']['origin'];
        }
        $detailEikenOrgDetails = $this->eikenOrgService->getApplyEikenOrgDetails(- 1);
        $semiMainVenue = $this->dantaiService->getSemiMainVenueOrigin($this->id_org, $this->eikenOrgService->getEikenScheduleId());
        $detailEikenOrgDetails = $semiMainVenue ? $this->eikenOrgService->convertPriceMainHall($detailEikenOrgDetails) : $detailEikenOrgDetails;

        // Get Refund Status
        $refundStatusCode = !empty($detailEikenOrgDetails['statusRefund']) ? $detailEikenOrgDetails['statusRefund'] : 0;
        if (count($detailEikenOrgDetails) > 1) {
            $refundStatusCode = !empty($detailEikenOrgDetails[0]['statusRefund']) ? $detailEikenOrgDetails[0]['statusRefund'] : 0;
        }

        $config = $this->getServiceLocator()->get('config');
        $listRefundOption = $config['refundStatusOption'];
        $refundStatus = array_key_exists($refundStatusCode, $listRefundOption) ? $listRefundOption[$refundStatusCode] : $listRefundOption[0];

        $detailFee = $this->eikenOrgService->getDetailFee($detailEikenOrgDetails);
        $detailPrice = $this->eikenOrgService->detailPriceForConfirmation();
        $viewModel = new ViewModel();
        $form = new CreateForm($this->getServiceLocator());
        $tocken = md5(time());

        $eikenLevel = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenLevel')->getNumberStudentApplyEikenBySatellite($this->id_org, $this->eikenOrgService->getEikenScheduleId());
        $inv = $this->eikenOrgService->getInvitationSetting();
        foreach ($eikenLevel as $value) {
            if ($value['hallType'] == 1 || $value['eikenLevelId'] == 1 || $value['eikenLevelId'] == 2) {
                $eikenData['publicSite'][$value['eikenLevelId']] = $value['number'];
            } else {
                $eikenData['groupSite'][$value['eikenLevelId']] = $value['number'];
            }
        }
        $semi = $this->eikenOrgService->getSemiMainVenueAndSettingCard($this->id_org);

        $totalPupilDisCount = $em->getRepository('Application\Entity\ApplyEikenOrgDetails')->getTotalPupilDiscount($this->id_org,$this->eikenOrgService->getEikenScheduleId());
        $isSpecialOrg = $this->eikenOrgService->isSpecialOrg($this->id_org);
        $schedeulrId = $this->eikenOrgService->getEikenScheduleId();
        $objeikenS = $em->getRepository('Application\Entity\EikenSchedule')->find($schedeulrId);
        $em->getRepository('Application\Entity\SpecialPrice')->getSpecialPriceAllGrade($this->id_org,$objeikenS->getYear(),$objeikenS->getKai());

        $viewModel->setVariables(array(
            'totalKyuPayment' => $eikenData['totalKyuPayment'],
            'hallType' => $eikenData['hallType'],
            'form' => $form,
            'refundStatus' => $refundStatus,
            'year' => date('Y'),
            'kaiNumber' => $this->eikenOrgService->getKaiNumber(),
            'eikenScheduleId' => $this->eikenOrgService->getEikenScheduleId(),
            'applyEikenLevel' => $eikenData,
            'eikenOrgDetailJS' => Json::encode($detailEikenOrgDetail),
            'eikenOrgDetail' => $detailEikenOrgDetail,
            'detailPrice' => $detailPrice,
            'detailFee' => $detailFee,
            'jsMessages' => $this->getTranslation(),
            'tocken' => $tocken,
            'isSentStandardHall' => $this->eikenOrgService->getIsSentStandardHall(),
            'definitionSpecial' => $this->eikenOrgService->getDefinition($detailPrice['mainHall']),
            'isInvitationSetting' => $this->eikenOrgService->isInvitationSettingValue(),
            'paymentMethod' => $paymentMethod,
            'semi' => $semi,
            'inv' => $inv,
            'settingFields' => $semi,
            'totalPupilDisCount' => isset($totalPupilDisCount[0]) ? $totalPupilDisCount[0] : '',
            'isSpecialOrg' => $isSpecialOrg
        ));
        PrivateSession::setData('applyEikenTocken', $tocken);

        return $viewModel;
    }

    public function applyeikendetailsAction() {
        $eikenScheduleId = $this->params()->fromRoute('id');
        if (!$eikenScheduleId) {
            return $this->redirect()->toRoute('eikenorg', array(
                        'action' => 'index'
            ));
        }
        $eikenData = $this->eikenOrgService->getRealExpectNoForConfirmation($this->eikenOrgService->getApplyEikenLevel($eikenScheduleId));
        $em = $this->getEntityManager();
        /*@var $applyEikenOrg \Application\Entity\ApplyEikenOrg*/
        $applyEikenOrg = $em->getRepository('Application\Entity\ApplyEikenOrg')->findOneBy(array(
            'eikenScheduleId' => $eikenScheduleId,
            'organizationId' => $this->id_org,
        ));
        if(!$applyEikenOrg || $applyEikenOrg->getStatus() == 'N/A'){
            return $this->redirect()->toRoute('eikenorg', array('action' => 'index'));
        }
        $detailEikenOrgDetail = array();
        if (!empty($eikenData['noOfExpectationStandard']['origin'])) {
            $detailEikenOrgDetail = $eikenData['noOfExpectationStandard']['origin'];
        }
        $detailEikenOrgDetails = $this->eikenOrgService->getApplyEikenOrgDetails(- 1);

        $refundStatusCode = !empty($detailEikenOrgDetails['statusRefund']) ? $detailEikenOrgDetails['statusRefund'] : 0;
        if (count($detailEikenOrgDetails) > 1) {
            $refundStatusCode = !empty($detailEikenOrgDetails[0]['statusRefund']) ? $detailEikenOrgDetails[0]['statusRefund'] : 0;
        }
        $config = $this->getServiceLocator()->get('config');
        $listRefundOption = $config['refundStatusOption'];
        $refundStatus = array_key_exists($refundStatusCode, $listRefundOption) ? $listRefundOption[$refundStatusCode] : $listRefundOption[0];
        $detailFee = $this->eikenOrgService->getDetailFee($detailEikenOrgDetails);
        $detailPrice = $this->eikenOrgService->detailPriceForConfirmation();
        $viewModel = new ViewModel();
        $form = new CreateForm($this->getServiceLocator());
        $year = $this->eikenOrgService->getYearOfEikenSchedule($eikenScheduleId);

        $eikenSchedule = $this->em->getRepository('Application\Entity\EikenSchedule')->find($eikenScheduleId);
        if ($eikenSchedule) {
            $deadlineTo = $eikenSchedule->getDeadlineTo()->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT);
            $currentDate = date(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT);
            $currentEikenSchedule = $this->em->getRepository('Application\Entity\EikenSchedule')->getCurrentEikenSchedule();
            if ($currentEikenSchedule && $eikenSchedule->getId() == $currentEikenSchedule['id'] && !($deadlineTo < $currentDate && PublicSession::isOrgAdminOrOrgUser())) {
                $isValidTime = true;
            } else {
                $isValidTime = false;
            }
        } else {
            $isValidTime = false;
        }

        $eikenLevel = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenLevel')->getNumberStudentApplyEikenBySatellite($this->id_org, $this->eikenOrgService->getEikenScheduleId());
        foreach ($eikenLevel as $value) {
            if ($value['hallType'] == 1 || $value['eikenLevelId'] == 1 || $value['eikenLevelId'] == 2) {
                $eikenData['publicSite'][$value['eikenLevelId']] = $value['number'];
            } else {
                $eikenData['groupSite'][$value['eikenLevelId']] = $value['number'];
            }
        }
        
        $totalPupilDisCount = $this->em->getRepository('Application\Entity\ApplyEikenOrgDetails')
                ->getTotalPupilDiscount($this->id_org,$this->eikenOrgService->getEikenScheduleId());
        $isSpecialOrg = $this->eikenOrgService->isSpecialOrg($this->id_org);
        
        $viewModel->setVariables(array(
            'totalKyuPayment' => $eikenData['totalKyuPayment'],
            'hallType' => $eikenData['hallType'],
            'form' => $form,
            'year' => !empty($year) ? $year : date('Y'),
            'kaiNumber' => $this->eikenOrgService->getKaiNumber($eikenScheduleId),
            'eikenScheduleId' => $this->eikenOrgService->getEikenScheduleId(),
            'applyEikenLevel' => $eikenData,
            'eikenOrgDetailJS' => Json::encode($detailEikenOrgDetail),
            'eikenOrgDetail' => $detailEikenOrgDetail,
            'detailPrice' => $detailPrice,
            'detailFee' => $detailFee,
            'jsMessages' => $this->getTranslation(),
            'isValidTime' => $isValidTime,
            'refundStatus' => $refundStatus,
            'isSpecialOrg' => $isSpecialOrg,
            'totalPupilDisCount' => isset($totalPupilDisCount[0]) ? $totalPupilDisCount[0] : array(),
        ));
        //$this->getIndexBreadCumbs($this->eikenOrgService->getKaiNumber(), 'detail', $eikenScheduleId);
        return $viewModel;
    }

    public function invalidAction() {
        $listCurrentKaiByYear = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->getCurrentKaiByYear(date('Y'));
        $currentTime = [];
        foreach ($listCurrentKaiByYear as $value) {
            if (!empty($value['deadlineFrom']) && $value['deadlineFrom'] <= (new \DateTime('now'))) {
                $currentTime = [
                    'year' => $value['year'],
                    'kai' => $value['kai'],
                ];
                break;
            }
        }
        $eikenData = $this->eikenOrgService->getApplyEikenLevel();
        $isCreate = $eikenData['applyEikenOrg'] ? 1 : 0;
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'dantaiStatus' => Json::encode(array(
                'isValidTime' => $this->eikenOrgService->isValidTime($isCreate),
                'isValidInvitationSetting' => $this->eikenOrgService->validInvitationSetting(),
            )),
            'currentTime' => $currentTime
        ));
        return $viewModel;
    }

    public function testAction() {

        $orderOrdAmount = $this->params()->fromQuery('data');
        if (empty($orderOrdAmount)) {
            die('empty orderId');
        }
        $orderOrdAmount = explode(',', $orderOrdAmount);
        $jsonData = array();
        foreach ($orderOrdAmount as $item) {
            $data = explode('-', $item);
            $jsonData[] = array(
                'id' => 1,
                'orderID' => $data[0],
                'shopID' => "s1",
                'payDate40' => '2015/05/30 00:00:00',
                'payBy' => "1",
                'cvsCode' => "900273",
                'kssspCode' => "000001",
                'inputID' => 1,
                'ordAmount' => $data[1]
            );
        }
        die(Json::encode(array(
                    'paymenData' => $jsonData
        )));
    }

    /**
     * Check valid apply eiken date
     *
     * @return JSON
     */
    public function checkValidApplyEikenAction() {
        // check exist apply eiken org
        $isPersonal = $this->params()->fromPost('isPersonal', 0);
        PrivateSession::setData('ApplyEikenOfPersonal', $isPersonal);
        $isRegistered = false;
        $applyEikenOrg = $this->eikenOrgService->getApplyEikenOrg();
        if (!empty($applyEikenOrg)) {
            $isRegistered = true;
        }
        $eikenData = $this->eikenOrgService->getApplyEikenLevel();
        $isCreate = $eikenData['applyEikenOrg'] ? 1 : 0;
        $data = array(
            'isValid' => $this->eikenOrgService->isValidTime($isCreate),
            'isRegistered' => $isRegistered
        );
        return $this->getResponse()->setContent(Json::encode($data));
    }

    public function getkaiAction() {
        $em = $this->getEntityManager();
        $yearId = $this->params()->fromQuery('year');
        $listkai = $em->getRepository('Application\Entity\EikenSchedule')->getKaiByYear($yearId);
        if (count($listkai) == 0) {
            $listkai[''] = '';
        }
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($listkai));
        return $response;
    }

    public function listeikenexamAction() {
        $viewModel = new ViewModel();
        $em = $this->getEntityManager();
        $form = new EikenExamForm();
        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $request = $this->params()->fromQuery();
        $examname = $request['ddlExamName'];
        $year = $request['ddlYear'];
        $kai = $request['ddlKai'];
        $startDate = $request['dtStartDate'];
        $endDate = $request['dtEndDate'];

        if (!empty($examname)) {
            $form->get("ddlExamName")->setAttributes(array(
                'value' => $examname
            ));
        } else {
            $form->get("ddlExamName")->setAttributes(array(
                'value' => ''
            ));
        }
        if (!empty($kai)) {
            $form->get("ddlKai")->setAttributes(array(
                'value' => $kai
            ));
        } else {
            $form->get("ddlKai")->setAttributes(array(
                'value' => ''
            ));
        }
        if (!empty($year)) {
            $form->get("ddlYear")->setAttributes(array(
                'value' => $year
            ));
        } else {
            $form->get("ddlYear")->setAttributes(array(
                'value' => ''
            ));
        }

        $eikenschedulerepository = $em->getRepository('Application\Entity\EikenSchedule');
        $data = $eikenschedulerepository->listEikenExam($this->id_org, $examname, $year, $kai, $startDate, $endDate, $offset, $limit);
        $viewModel->setVariables(array(
            'eikenschedule' => $data,
            'form' => $form
        ));
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    /**
     * Ajax
     * Submit to UiKeSuKe
     */
    function getCookieExport($flag = false) {
        $cookieExport = @$this->getRequest()
                        ->getHeaders()
                        ->get('Cookie')->cookieExport;
        if (isset($cookieExport) && $cookieExport && $cookieExport != '[]') {
            if ($flag) {
                $cookieExport = str_replace("[", "", $cookieExport);
                $cookieExport = str_replace("]", "", $cookieExport);
                $cookieExport = explode(",", $cookieExport);
            } else {
                $cookieExport = str_replace("[", "(", $cookieExport);
                $cookieExport = str_replace("]", ")", $cookieExport);
            }
        } else {
            $cookieExport = false;
        }
        return $cookieExport;
    }

    public function submitAction() {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $eikenData = $this->eikenOrgService->getApplyEikenLevel();
        $isCreate = $eikenData['applyEikenOrg'] ? 1 : 0;
        if (!$this->eikenOrgService->isValidTime($isCreate)) {
            $data = array(
                'isValid' => $this->eikenOrgService->isValidTime($isCreate)
            );

            return $this->getResponse()->setContent(Json::encode($data));
        }
        $tocken = $this->params()->fromPost('tocken', 0);
        if (empty($tocken) || $tocken != PrivateSession::getData('applyEikenTocken')) {
            $data = array(
                'inValidTocken' => 1
            );

            return $this->getResponse()->setContent(Json::encode($data));
        }

        // validate require field of student info before send to uketsuke.
        $numberError = $this->eikenOrgService
            ->countNumberApplyMainHallEmptyExemption($this->id_org, $this->eikenOrgService->getEikenScheduleId());
        if(count($numberError) > 0){
            $msg = '';
            foreach ($numberError as $row){
                if(empty($msg)){
                    $msg = $translator->translate('MSG_SOME_THING_ERROR_OF_INFO_STUDENT').'<br>'.sprintf($translator->translate('MSG_INFO_STUDENT'), $row['infoFirstNameKanji'].$row['infoLastNameKanji'], $row['displayName'],$row['className']);
                }else{
                    $msg.= '、 <br>'.sprintf($translator->translate('MSG_INFO_STUDENT'), $row['firstNameKanji'].$row['lastNameKanji'], $row['displayName'],$row['className']);
                }
            }
            $data = array(
                'isSuccess' => true,
                'resultFlag' => array(
                    'PupilListMainHallError' => 1,
                    'msg' => $msg
                )
            );
            return $this->getResponse()->setContent(Json::encode($data));
        }

        $logData = array(
            'action' => PrivateSession::getData(EikenConst::APPLY_ACTION),
            'params' => PrivateSession::getData(EikenConst::APPLY_DATA_FROM_POST),
            // Data from database
            'oldStatusRefund' => PrivateSession::getData(EikenConst::APPLY_REFUND_STATUS_FROM_DATABASE),
            'userId' => $this->userId
        );

        $data = array(
            'isSuccess' => true,
            'resultFlag' => $this->eikenOrgService->submitApplyEikenOrgToUkestuke($this->params()->fromPost('managerName', 0), $logData)
        );
        PrivateSession::clear('applyEikenTocken');

        return $this->getResponse()->setContent(Json::encode($data));
    }

    public function loadExamLocationAction() {
        $cityId = $this->params()->fromPost('cityId', 0);

        $examLocation = $this->eikenOrgService->getExamLocationList($cityId, 0);

        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariable('examLocation', $examLocation);
        return $view;
    }

    protected function getTranslation() {
        // $currentUserInfo = $this->eikenOrgService->getCurrentUserInfor();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
//         $crossMessages = $this->dantaiService->getCrossEditingMessage('Application\Entity\ApplyEikenLevel');

        $jsMessages = array(
            'MSG1' => $translator->translate('MSG1'),
            'MSG4' => $translator->translate('MSG4-eiken'),
            'MSG33' => $translator->translate('MSG33'),
            'MSG44' => $translator->translate('MSG44'),
            'MSG46' => $translator->translate('MSG46'),
            'MSG47' => $translator->translate('MSG47'),
            'MSG48' => $translator->translate('MSG48'),
            'MSG19_wrong_format' => $translator->translate('MSG19_wrong_format'),
            'MSG36' => $translator->translate('MSG36'),
            'MSG53' => $translator->translate('MSG53'),
            'MSG49' => $translator->translate('MSG49'),
            'MSG72' => $translator->translate('MSG72'),
            'MSG73' => $translator->translate('MSG73'),
            'MSG74' => $translator->translate('MSG74'),
            'MSG29' => $translator->translate('MSG29'),
            'SystemError' => $translator->translate('SystemError'),
            'MSG50' => $translator->translate('MSG50'),
            'MSG80' => $translator->translate('MSG80'),
            'FullWidthFont' => $translator->translate('FullWidthFont'),
            'StandardConfirmChk' => $translator->translate('StandardConfirmChk'),
            'SentPolicyUnsuccessfully' => $translator->translate('SentPolicyUnsuccessfully'),
            'GetOrgInfoError' => $translator->translate('GetOrgInfoError'),
            'HalfWidthFont' => $translator->translate('HalfWidthFont'),
//             'conflictWarning' => $crossMessages['conflictWarning'],
//             'conflictType' => $crossMessages['conflictType'],
            'SendMailError' => $translator->translate('SendMailError'),
            'MinimumCdSet' => $translator->translate('MinimumCdSet'),
            'SGHMSG48' => $translator->translate('SGHMSG48'),
            'msgNotAllowLessThan10Standard' => $translator->translate('msgNotAllowLessThan10Standard')
        );
        return Json::encode($jsMessages);
    }

    protected function getIndexBreadCumbs($kaiNumber = 0, $page = 'create', $id = 0) {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $navigation = $this->getServiceLocator()->get('navigation');
        switch ($page) {
            case 'create':
                $pageId = 'app_eik_org_create';
                $page = $navigation->findBy('id', $pageId);
                $page->setLabel('申込情報登録');
                break;
            case 'edit':
                $pageId = 'app_eik_org_create';
                $page = $navigation->findBy('id', $pageId);
                $page->setLabel('団体申込情報編集');
                break;
//             case 'confirmation':
//                 $pageId = 'app_eik_org_confirmation';
//                 break;
//             case 'detail':
//                 $pageId = 'app_eik_org_applyeikendetails';
//                 break;
        }
//         $page = $navigation->findBy('id', $pageId);
//         $year = date('Y');
//         $page->setLabel('英検申込–' . $year . '年第' . $kaiNumber . '回');
        //if($id){
        //$page->setParams(array('id' => 1));
        //}
    //
    }

    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function getEntityAuthenticateManager() {
        return $this->getServiceLocator()->get('doctrine.authenticationservice.orm_default');
    }

    public function fundingAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            PrivateSession::setData('fundingStatus', $data['fundingStatus']);
            return $this->getResponse()->setContent(Json::encode(EikenConst::SAVE_SUCCESS_FUNDINGSTATUS_INTO_SESSION));
        }
        return $this->redirect()->toRoute('eikenorg', array(
                    'action' => 'index'
        ));
    }

    public function paymentAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            PrivateSession::setData('paymentStatus', $data['paymentStatus']);
            return $this->getResponse()->setContent(Json::encode(EikenConst::SAVE_SUCCESS_FUNDINGSTATUS_INTO_SESSION));
        }
        return $this->redirect()->toRoute('eikenorg', array(
                    'action' => 'index'
        ));
    }

}
