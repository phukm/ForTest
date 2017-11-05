<?php

namespace OrgMnt\Controller;


use OrgMnt\Service\ServiceInterface\OrgServiceInterface;
use Dantai\Utility\PHPExcel;
use Application\Service\CommonService;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use OrgMnt\Form\OrgSearchForm;
use OrgMnt\Form\PaymentRefundStatusForm;
use OrgMnt\Form\UnderminedOrgForm;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Filter\Null;
use Zend\Json\Json;
use Zend\Validator\File\Count;
use Dantai\Utility\CharsetConverter;
use Zend\Validator\Explode;
use Doctrine\ORM\Query\AST\Functions\TrimFunction;
use Zend\Filter\HtmlEntities;
use Dantai\PrivateSession;
use Zend\Session\Container;
use Dantai\Api\EinaviClient;
use Dantai;
use Dantai\Api\UkestukeClient;
use Application\Entity\Application\Entity;
use Dantai\Utility\DateHelper;


class OrgController extends AbstractActionController {

    use \Application\Controller\ControllerAwareTrait;
    const POST_PER_PAGE = 20;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var OrgServiceInterface
     */
    protected $orgService;
    protected $year;

    public function __construct(DantaiServiceInterface $dantaiService, OrgServiceInterface $orgService, EntityManager $entityManager) {
        $this->dantaiService = $dantaiService;
        $this->orgService = $orgService;
        $this->em = $entityManager;
        $this->year = $this->dantaiService->getCurrentYear();
    }

    public function indexAction() {
        $container = new Container('APIOrg');
        $roleId = $this->dantaiService->getCurrentUser()['roleId'];
        // khai bao
        $viewModel = new ViewModel();
        $em = $this->getEntityManager();
        $form = new OrgSearchForm($this->getServiceLocator());
        $isset_post = $this->params()->fromPost();
        $isset_value = false;

        $orgNo = '';
        $orgNameKanji = '';
        $orgNameKana = '';
        $orgDateFrom = '';
        $orgDateTo = '';
        $orgExamType = '';
        $refundStatus = '';
        $publicFunding = '';
        $paymentBill = '';

        $search = array();
        if (!empty($isset_post)) {
            foreach ($isset_post as $key => $value) {
                if (!empty($value) || $value == 0) {
                    $isset_value = true;
                }
            }
        }
        $page = $this->params()->fromRoute("page", 1);
        $limit = self::POST_PER_PAGE;
        $offset = ($page < 0) ? 0 : ($page - 1) * $limit;

        // get value from input or session
        $routeMatch = $this->getEvent()
                        ->getRouteMatch()
                        ->getParam('controller') . '_' . $this->getEvent()
                        ->getRouteMatch()
                        ->getParam('action');

        if ($isset_value != false) {
            // get value form
            $orgNo = $this->removeSpecialCharacters($this->params()->fromPost('txtOrgNumber'));
            $orgNo = strip_tags(trim($orgNo));

            $orgNameKanji = $this->removeSpecialCharacters($this->params()->fromPost('txtOrgName1'));
            $orgNameKanji = strip_tags(trim($orgNameKanji));

            $orgNameKana = $this->removeSpecialCharacters($this->params()->fromPost('txtOrgName2'));
            $orgNameKana = strip_tags(trim($orgNameKana));

            $orgDateFrom = $this->removeSpecialCharacters($this->params()->fromPost('datetime1'));
            $orgDateFrom = strip_tags(trim($orgDateFrom));
            
            $refundStatus = $this->removeSpecialCharacters($this->params()->fromPost('refundStatus'));
            $refundStatus = strip_tags(trim($refundStatus));
            
            $publicFunding = $this->removeSpecialCharacters($this->params()->fromPost('publicFunding'));
            $publicFunding = strip_tags(trim($publicFunding));
            
            $paymentBill = $this->removeSpecialCharacters($this->params()->fromPost('paymentBill'));
            $paymentBill = strip_tags(trim($paymentBill));

            if (!empty($orgDateFrom)) {
                $orgDateFrom = date('Y-m-d', strtotime($orgDateFrom));
            }

            $orgDateTo = $this->removeSpecialCharacters($this->params()->fromPost('datetime2'));
            $orgDateTo = strip_tags(trim($orgDateTo));

            if (!empty($orgDateTo)) {
                $orgDateTo = date('Y-m-d', strtotime($orgDateTo));
            }

            $orgExamType = $this->removeSpecialCharacters($this->params()->fromPost('txteet'));
            $orgExamType = strip_tags(trim($orgExamType));

            $search = array('orgNo' => $orgNo,
                'orgNameKanji' => $orgNameKanji,
                'orgNameKana' => $orgNameKana,
                'orgDatelineForm' => $orgDateFrom,
                'orgDatelineTo' => $orgDateTo,
                'orgExamType' => $orgExamType,
                'publicFunding' => $publicFunding,
                'paymentBill' => $paymentBill,
                'refundStatus' => $refundStatus);

            $this->dantaiService->setSearchKeywordToSession($routeMatch, $search);
        } else {
            $isset_value = true;
            $search = $this->dantaiService->getSearchKeywordFromSession($routeMatch);

            if (!empty($search)) {
                $orgNo = $search['orgNo'];
                $orgNameKanji = $search['orgNameKanji'];
                $orgNameKana = $search['orgNameKana'];
                $orgDateFrom = $search['orgDatelineForm'];
                $orgDateTo = $search['orgDatelineTo'];
                $orgExamType = $search['orgExamType'];
                $refundStatus = $search['refundStatus'];
                $publicFunding = $search['publicFunding'];
                $paymentBill = $search['paymentBill'];
            }
        }

        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsMessages = array('MSGdatefomat' => $translator->translate('MSGdatefomat'),
            'MSGdatecompare' => $translator->translate('MSGdatecompare'));
        $eikenSchedule = $this->dantaiService->getCurrentEikenSchedule();

        $paginator = $em->getRepository('Application\Entity\Organization')->searchOrganization($orgNo, $orgNameKanji, $orgNameKana, $orgExamType, $orgDateFrom, $orgDateTo, $refundStatus, $publicFunding, $paymentBill, $eikenSchedule->id);

        // set filter call back
        $SetOrgDateLineForm = str_replace("-", "/", $orgDateFrom);
        $SetOrgDateLineTo = str_replace("-", "/", $orgDateTo);
        $form->get("txtOrgNumber")->setValue($orgNo);
        $form->get("txtOrgName1")->setValue($orgNameKanji);
        $form->get("txtOrgName2")->setValue($orgNameKana);
        $form->get("datetime1")->setValue($SetOrgDateLineForm);
        $form->get("datetime2")->setValue($SetOrgDateLineTo);
        $form->get("txteet")->setValue($orgExamType);
        $form->get("refundStatus")->setValue($refundStatus);
        $form->get("publicFunding")->setValue($publicFunding);
        $form->get("paymentBill")->setValue($paymentBill);

        $jsonMessage = \Dantai\Utility\JsonModelHelper::getInstance();
        $jsonMessage->setFail();
        $jsonMessage->setData($jsMessages);

        return $viewModel->setVariables(array(
                    "form" => $form,
                    "items" => $paginator->getItems($offset, $limit, false),
                    "paginator" => $paginator,
                    "page" => $page,
                    "jsMessages" => $jsonMessage,
                    "numPerPage" => $limit,
                    'searchVisible' => 1,
                    'advancedSearchVisible' => $refundStatus,
                    'roleId'=>$roleId
        ));
    }
    
    public function showAction() {
        $viewModel = new ViewModel();
        $em = $this->getEntityManager();

        // get id org
        $orgno = "";
        $id = "";
        $id = $this->params('id', 0);
        $repository = $em->getRepository('Application\Entity\Organization');
        $validator = new \DoctrineModule\Validator\ObjectExists(array(
            'object_repository' => $repository,
            'fields' => array(
                'id'
            )
        ));
        $check_id = $validator->isValid($id);
        if ($check_id != true) {
            return $this->redirect()->toRoute('org-mnt/default', array(
                        'controller' => 'org',
                        'action' => 'index'
            ));
        }

        // check org human
        $userCurent = $this->dantaiService->getCurrentUser();
        $roleCurent = $userCurent['roleId'];
        $iduserCurent = $userCurent['id'];
        if (Dantai\PublicSession::isOrgAdminOrOrgUser()) {
            $urlIdOrg = $em->getRepository('Application\Entity\Organization')
                    ->find($id)
                    ->getid();
            $dataIdOrg = $em->getRepository('Application\Entity\User')
                    ->find($iduserCurent)
                    ->getorganizationid();

            if ($urlIdOrg != $dataIdOrg) {
                return $this->redirect()->toRoute("home");
            }
        }

        $object_org = $em->getRepository('Application\Entity\Organization')->find($id);
        $orgno = $object_org->getOrganizationNo();

        $container = new Container('APIOrg');
        $result = $container->{$id};

        $result = $result['objectstring'];

        $data = array(
            'id' => $id,
            'orgno' => $orgno
        );

        $jsonMessage = \Dantai\Utility\JsonModelHelper::getInstance();
        $jsonMessage->setFail();
        $jsonMessage->setData($data);
        
        return $viewModel->setVariables(array(
                    'item' => $object_org,
                    'result' => $result,
                    "jsMessages" => $jsonMessage,
                    'roleid' => $roleCurent,
                    'id' => $id
        ));
    }

    public function getAPIAction() {
        $config = $this->getServiceLocator()->get('Config')['orgmnt_config']['api'];
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $msg = "";
        $isset_post = $this->params()->fromPost();
        $id = intval($isset_post['id']);
        $orgno = $isset_post['orgno'];
        $kyotenkoukbn = "";
        $sikaku = "";
        $bunruino = "";
        $kekka = "";

        try {
            $result = \Dantai\Api\UkestukeClient::getInstance()->callEir2a01($config, array(
                // 'dantaino' => $object_org->getOrganizationNo()
                'dantaino' => $orgno
            ));
        } catch (Exception $e) {
            // Caught exception regarding to:
            // Connection, Argument, RuntimeException
            // Do record to systemlog
            $msg = $translator->translate('MSG45');
            throw $e;
        }
        $kekka = 0;
        $kekka = intval($result->kekka);
        if ($kekka == 0) {
            $msg = $translator->translate('MSGsytemerro');
        } elseif ($kekka == 1) {
            $msg = $translator->translate('MSGnoorg');
        } elseif ($kekka == 10) {
            $container = new Container('APIOrg');
            $container->{$id} = array(
                "objectstring" => $result
            );

            // kyotenkoukbn
            $kyotenkoukbn = $result->kyotenkoukbn;

            switch ($kyotenkoukbn) {
                case '0':
                    $result->kyotenkoukbn = '通常校（デフォルト）';
                    break;
                case '1':
                    $result->kyotenkoukbn = '拠点校';
                    break;
                case '2':
                    $result->kyotenkoukbn = '協力校';
                    break;
            }

            // sikaku
            $sikaku = $result->sikaku;

            if ($sikaku == 1) {
                $result->sikaku = '登録済み';
            } else {
                $result->sikaku = '未登録';
            }

            // bunruino
            $bunruino = $result->bunruino;

            switch ($bunruino) {
                case '00':
                    $result->bunruino = '小学校';
                    break;
                case '01':
                    $result->bunruino = '中学校';
                    break;
                case '05':
                    $result->bunruino = '高校';
                    break;
                case '10':
                    $result->bunruino = '短大';
                    break;
                case '15':
                    $result->bunruino = '大学';
                    break;
                case '16':
                    $result->bunruino = '大学生協';
                    break;
                case '20':
                    $result->bunruino = '高専';
                    break;
                case '22':
                    $result->bunruino = '官公庁・公営企業体';
                    break;
                case '25':
                    $result->bunruino = '企業団体';
                    break;
                case '30':
                    $result->bunruino = '専修各種';
                    break;
                case '31':
                    $result->bunruino = 'YMCA';
                    break;
                case '35':
                    $result->bunruino = '旺文社LL';
                    break;
                case '36':
                    $result->bunruino = '登録予備校';
                    break;
                case '40':
                    $result->bunruino = '特殊学校';
                    break;
                case '41':
                    $result->bunruino = '朝鮮韓国学校';
                    break;
                case '45':
                    $result->bunruino = '準会場登録塾・未登録塾';
                    break;
                case '46':
                    $result->bunruino = '公文事務局';
                    break;
                case '50':
                    $result->bunruino = '専修学校高等課程';
                    break;
                case '51':
                    $result->bunruino = '盲学校';
                    break;
                case '52':
                    $result->bunruino = '聾学校';
                    break;
                case '53':
                    $result->bunruino = '養護学校（肢体）';
                    break;
                case '54':
                    $result->bunruino = '養護学校';
                    break;
                case '55':
                    $result->bunruino = '養護学校';
                    break;
            }
        } else {
            $msg = $translator->translate('MSG45');
        }
        return $this->getResponse()->setContent($msg);
    }

    public function transformAction() {
        $chosenOrganizationId = $this->params('id', 0);
        $this->dantaiService->changeOrganizationId($chosenOrganizationId);
        return $this->redirect()->toRoute('home-page/default', array(
                    'controller' => 'homepage'
        ));
    }

    /**
     * @author anhnt56
     */
    public function undeterminedAction() {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $form = new UnderminedOrgForm($this->getServiceLocator());
        $em = $this->getEntityManager();
        $viewModel = new ViewModel();

        $flashMessages = $this->flashMessenger()->getMessages();
        $messages = '';
        if ($flashMessages) {
            $messages = $flashMessages[0]['MsgNoRecordExcel'];
        }

        $kai = $em->getRepository('Application\Entity\EikenSchedule')->getKaiByYearDESC($this->year);
        $arrKai[''] = '';
        foreach($kai as $value)
        {
            $arrKai[$value['kai']]=$value['kai'];
        }
        $form->get('kai')->setValueOptions($arrKai);
        $page = $this->params()->fromRoute('page', 1);
        $limit = self::POST_PER_PAGE;
        $offset = ($page < 0) ? 0 : ($page - 1) * $limit;
        
        /* define searching params */
        $year = $this->year;
        $kai = 0;
        $status = '';
        $organizationNo = '';
        $organizationName = '';
        $searchCriteria = $this->dantaiService->getSearchCriteria($this->getEvent(), $this->params()
            ->fromPost());
        if ($this->isPost() && $searchCriteria['token']) {
            return $this->redirect()->toUrl('/org/org/undetermined/search/' . $searchCriteria['token']);
        }
        
        if($searchCriteria)
        {
            $year = isset($searchCriteria['year'])?$searchCriteria['year']:$this->year;
            $kai = isset($searchCriteria['kai'])?$searchCriteria['kai']:'';
            $status = isset($searchCriteria['status'])?$searchCriteria['status']:'';
            $organizationNo = isset($searchCriteria['organizationNo'])?$searchCriteria['organizationNo']:'';
            $organizationName = isset($searchCriteria['organizationName'])?$searchCriteria['organizationName']:'';
            $form->get('year')->setAttribute('value',$year);
            $form->get('kai')->setAttribute('value',$kai);
            $form->get('status')->setAttribute('value',$status);
            $form->get('organizationNo')->setAttribute('value',$organizationNo);
            $form->get('organizationName')->setAttribute('value',$organizationName);
        }
        
        $paginator = $em->getRepository('Application\Entity\Organization')->getListOrgNotConfirmApplyEiken($year, $kai, $organizationNo, $organizationName, $status);
        $items = $paginator->getItems($offset, $limit);
        $totalDantais = count($items);
        for ($i = 0; $i < $totalDantais; $i++) {
            if ($items[$i]['Status'] == 'DRAFT') {
                $items[$i]['Status'] = $translator->translate('UndiminedOrgStatusDraft');
                continue;
            }
            $items[$i]['Status'] = $translator->translate('UndiminedOrgStatusSubmited');
        }

        return $viewModel->setVariables(array(
                                            'items'            => $items,
                                            'paginator'        => $paginator,
                                            'page'             => $page,
                                            'numPerPage'       => $limit,
                                            'form'             => $form,
                                            'param'            => isset($searchCriteria['token']) ? $searchCriteria['token'] : '',
                                            'year'             => $year,
                                            'kai'              => $kai,
                                            'status'           => $status,
                                            'organizationNo'   => $organizationNo,
                                            'organizationName' => $organizationName,
                                            'noRecordExcel'    => $messages,
        ));
    }

    /**
     *
     * @return array|object
     */
    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    // remove character :  '' AND ""
    public function removeSpecialCharacters($string) {
        $string = str_replace(array(
            "'",
            '"'
                ), array(
            "",
            ""
                ), $string);
        return $string;
    }
    
    public function getOrgEmailByAPI($orgno){
        $config = $this->getServiceLocator()->get('Config')['orgmnt_config']['api'];
        
        try {
            $result = \Dantai\Api\UkestukeClient::getInstance()->callEir2a01($config, array(
                'dantaino' => $orgno
            ));
        } catch (Exception $e) {
            // Caught exception regarding to:
            // Connection, Argument, RuntimeException
            // Do record to systemlog
            return null;
        }
        $kekka = 0;
        $kekka = intval($result->kekka);
        if ($kekka == 0) {
            return null;
        } elseif ($kekka == 1) {
            return null;
        } elseif ($kekka == 10) {
            return array('Email' => $result->email, 'PhoneNumber' => $result->tel);
        }
        return null;
    }   
    
    /**
     * @author anhnt56
     */
    public function paymentRefundStatusAction() {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $form = new PaymentRefundStatusForm($this->getServiceLocator());
        $em = $this->getEntityManager();
        $viewModel = new ViewModel();
        $config = $this->getServiceLocator()->get('config');
        $listRefundOption = $config['refundStatusOption'];

        $page = $this->params()->fromRoute('page', 1);
        $limit = self::POST_PER_PAGE;
        $offset = ($page < 0) ? 0 : ($page - 1) * $limit;
        
        /* define searching params */
        $year = '';
        $kai = '';
        $statusRefund = '';
        $organizationNo = '';
        $organizationName = '';
        $searchCriteria = $this->dantaiService->getSearchCriteria($this->getEvent(), $this->params()
            ->fromPost());
        if ($this->isPost() && $searchCriteria['token']) {
            return $this->redirect()->toUrl('/org/org/paymentRefundStatus/search/' . $searchCriteria['token']);
        }
        
        if($searchCriteria)
        {
            $year = isset($searchCriteria['year'])?$searchCriteria['year']:'';
            $kai = isset($searchCriteria['kai'])?$searchCriteria['kai']:'';
            $statusRefund = isset($searchCriteria['statusRefund'])?$searchCriteria['statusRefund']:'';
            $organizationNo = isset($searchCriteria['organizationNo'])?$searchCriteria['organizationNo']:'';
            $organizationName = isset($searchCriteria['organizationName'])?$searchCriteria['organizationName']:'';
            $form->get('year')->setAttribute('value',$year);
            $form->get('kai')->setAttribute('value',$kai);
            $form->get('statusRefund')->setAttribute('value',$statusRefund);
            $form->get('organizationNo')->setAttribute('value',$organizationNo);
            $form->get('organizationName')->setAttribute('value',$organizationName);
        }
         
        $paginator = $em->getRepository('Application\Entity\ApplyEikenOrg')->searchApplyEikenWithRefund($organizationNo, $organizationName, $year, $kai, $statusRefund);        
       
        $items = $paginator->getItems($offset, $limit, false);
            
        return $viewModel->setVariables(array(
                    'items' => $items,
                    'paginator' => $paginator,
                    'page' => $page,
                    'numPerPage' => $limit,
                    'form' => $form,
                    'param' => isset($searchCriteria['token']) ? $searchCriteria['token'] : '',
                    'listRefundOption' => $listRefundOption
        ));
    }

    /**
     * @return array|\Zend\View\Model\JsonModel
     */
    public function ajaxSetSemiMainVenueAction() {
        $orgId = $this->params()->fromPost('orgId','');
        $check = $this->params()->fromPost('check','false');
        if(!$this->getRequest()->isXmlHttpRequest()){
            return $this->notFoundAction();
        }

        /** @var JsonModel $jsonModel */
        $jsonModel = $this->orgService->setSemiMainVenue($orgId, $check);

        return new \Zend\View\Model\JsonModel($jsonModel->toArray());
    }

    public function exportApplyEikenAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();
        if ($request->isPost()) {
            $params = $request->getPost();
            $type = isset($params['exportType']) ? $params['exportType'] : 1;

            $currentEiken = $this->dantaiService->getEndEikenSchedule();
            if ($type == 2) {
                $filename = $this->translate('paidAndRegisteredFormat') . "_" . date(DateHelper::DATE_FORMAT_EXPORT_EXCEL) . '.xls';
                $template = 'undetermined_moushikomi';
                $exportData = $this->orgService->createExportData($currentEiken->id);
            } else {
                $filename = $this->translate('paidOnlyFormat') . "_" . date(DateHelper::DATE_FORMAT_EXPORT_EXCEL) . '.xls';
                $template = 'undetermined_keihi';
                $exportData = $this->orgService->createPaidExportData($currentEiken->id);
            }

            if (empty($exportData)) {
                $messages = array(
                    'MsgNoRecordExcel' => $this->translate('MsgNoRecordExcel'),
                );
                $this->flashMessenger()->addMessage($messages);
                return $this->redirect()->toRoute('org-mnt/default', array(
                                                                       'controller' => 'org',
                                                                       'action'     => 'undetermined',
                                                                   )
                );
            }

            $response = $this->orgService->exportToExcel($exportData, $response, $filename, $template, $type);
        }
        return $response;
    }
    
    public function policyGradeClassAction()
    {
        $viewModel = new ViewModel();
        $this->setBreadCumbs('dantaiBreadCumb', '');
        
        return $viewModel;
    }
    protected function setBreadCumbs($id = '', $text = '') {
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('id', $id);
        $page->setLabel($text);
    }

    public function translate($key){
        return $this->getServiceLocator()->get('MVCTranslator')->translate($key);
    }
}
