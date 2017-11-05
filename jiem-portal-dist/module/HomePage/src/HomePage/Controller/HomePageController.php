<?php
namespace HomePage\Controller;

use Dantai\PublicSession;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Prophecy\Exception\Exception;
use Zend\Validator\File\Count;
use Doctrine\ORM\EntityManager;
use Application\Entity\Repository;
use HomePage\Service\ServiceInterface\HomeServiceInterface;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Dantai\PrivateSession;

class HomePageController extends AbstractActionController
{

    protected $em;

    protected $year;

    protected $orgId;

    protected $orgNo;
    
    protected $target;

    protected $targetLevel;

    protected $targetLastYear;

    protected $targetLevelLastYear;
    
    protected $limit;

    /*
     * get list year for DDL detail B and C
     * DucNA17
     * return array
     */
    protected $dllYear;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    public function getEntityManager()
    {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }
        return $this->em;
    }

    /**
     *
     * @var \HomePage\Service\HomeService
     */
    protected $homeService;

    public function __construct(DantaiServiceInterface $dantaiService, HomeServiceInterface $homeService)
    {
        $this->homeService = $homeService;
        $this->dantaiService = $dantaiService;
        $user = $this->dantaiService->getCurrentUser();
        $this->orgId = $user['organizationId'];
        $this->orgNo = $user['organizationNo'];
        $this->orgCode = $user['organizationCode'];
        $this->year = $this->dantaiService->getCurrentYear();
        $this->dllYear = $this->homeService->getDDLYear($this->year);
        $this->limit = 20;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()    
    {
        //---- BEGIN TAIVH  
        // Process data for homepage index
        $viewModel = new ViewModel();
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $year = $this->year;
        // Get News
        $news = $this->homeService->getNews();               
        
        // ------------ Get Box B
        $data = $em->getRepository('Application\Entity\EikenSchedule');

        $eikenSchedule = $data->getAvailableEikenScheduleByDate(date('Y'), date('Y-m-d H:i:s')); 
        $eikenScheduleInfo = $eikenSchedule;
        
        $currentEikenSchedule = $data->getCurrentEikenSchedule();

        $eikenKai = 0;
        $sumResApplyEiken = 0;
        $compDateBefo = 0;

        $date1 = date('Y-m-d', strtotime('-1 days'));
        //During registration
        if ($eikenScheduleInfo) {
            $eikenScheduleId = $eikenScheduleInfo['id'];
            $eikenKai = $eikenScheduleInfo['kai'];

            $dateCurrent = date('Y-m-d');
            $arrRes = $this->homeService->sumReExam($this->orgId, $eikenScheduleId, $dateCurrent, 'SUBMITTED');
            // CASE SUBMITTED
            if ($arrRes[1] == 1) {
                $sumResApplyEiken = $arrRes[0];
                $compDateBefo = '';
                $compDateBefo1 = $compDateBefo;
                $date1 = $arrRes[2] . 'まで';
                $date2 = '';
            } else {// Case current date - 1
                $sumResApplyEiken = $arrRes[0];
                $sumDate1 = $this->homeService->sumReExam($this->orgId, $eikenScheduleId, $date1, 'SUBMITTED');
                $compDateBefo = (int) $arrRes[0] - (int) $sumDate1[0];
                $date1 = date('n/j', strtotime('-1 days')) . 'まで';
                $date2 = date('n/j', strtotime('-2 days')) . '比';
                $compDateBefo1 = $compDateBefo;
                $compDateBefo = $this->homeService->getValueWithColor($compDateBefo, '人');
            }
        } else {//Expires registration deadline then get the previous value (Total number student SUBMIT or DRAFT for last Kai)
            $eikenScheduleInfo = $data->getEikenScheduleLastTime();
            $eikenScheduleId = $eikenScheduleInfo[0]['id'];
            $eikenKai = $eikenScheduleInfo[0]['kai'];
            $dateCurrent = $eikenScheduleInfo[0]['deadlineTo']->format('Y-m-d');
            $dateCurrent = date('Y-m-d', strtotime($dateCurrent. ' + 1 day'));

            $arrRes = $this->homeService->sumReExam($this->orgId, $eikenScheduleId, $dateCurrent, 'SUBMITTED');
            if ($arrRes[1] == 1) {
                $date1 = $arrRes[2] . 'まで';
                $date2 = '';
                $compDateBefo = '';
                $compDateBefo1 = $compDateBefo;
            } else {
                $date1 = date('n/j', strtotime('-1 days')) . 'まで';
                $date2 = date('n/j', strtotime('-2 days')) . '比';
                $compDateBefo = '0';
                $compDateBefo1 = $compDateBefo;
                $compDateBefo = $this->homeService->getValueWithColor($compDateBefo, '人');
            }
            if ($arrRes[0] <= 0) {
                $arrRes = $this->homeService->sumReExam($this->orgId, $eikenScheduleId, $dateCurrent, 'DRAFT');
                $date1 = date('n/j', strtotime('-1 days')) . 'まで';
                $date2 = date('n/j', strtotime('-2 days')) . '比';
                $compDateBefo = 0;
                $compDateBefo1 = $compDateBefo;
                $compDateBefo = $this->homeService->getValueWithColor($compDateBefo, '人');
            }
            $sumResApplyEiken = $arrRes[0];
        }

        $sumResApplyEiken = $this->homeService->getValueBigWithColor($compDateBefo1, $sumResApplyEiken, '人', 'fix-font-1');

        $btnPayment = '<a class="btn w-180 tooltip-home" alt=\'学年別・クラス別の申込・支払状況\' onclick="openUrl(' . '\'/eiken/payment/paymentstatus\'' . ',' . '\'pay\'' . ',' . $eikenKai . ',' . $year . ')">詳細を見る</a>';
        $date11 = date('n/j', strtotime('-1 days')) . '分';
        $date12 = date('n/j', strtotime('-2 days')) . '比';
        // ---- Output View
        $viewModel->setVariables(array(
            'date1' => $date1,
            'date2'=> $date2,
            'date11'=> $date11,
            'date12'=> $date12,
            'eikenKai'=> $eikenKai,
            'sumResApplyEiken'=> $sumResApplyEiken,
            'compDateBefo'=> $compDateBefo,
            'btnPayment'=> $btnPayment,
            'news'=> $news,
            'eikenScheduleId' => $eikenScheduleId
        ));

        // ------------- GetBox C
        $dataC = $this->homeService->getInfoBox3($this->orgId);
        $dataC2 = $this->homeService->getValueWithColor($dataC[1], '人');
        $dataC[0] = $this->homeService->getValueBigWithColor($dataC[1], $dataC[0], '人', 'fix-font-2');

        $viewModel->setVariables(array(
            'dataC1' => $dataC[0],
            'dataC2' => $dataC2            
        ));       
        //---- END TAIVH
        // -- NOTIFICATION - WITH TIME LINE
        $notifi = '';
        $btnEikenRes = '';
        $dateCurrent = date('Y-m-d');
        $dtEikenSchedule = $this->homeService->getEikenScheduleByCurrentTime();
        $year = $this->year;

        if ($dtEikenSchedule) {
            $dtES = $dtEikenSchedule[0]["deadlineTo"]->format('Y-m-d');
            $dtKai = $dtEikenSchedule[0]["kai"];
            list ($year, $month, $day, ) = split('[/.-]', $dtES);
            $notifi = $year . '年度 第' . $dtKai . '回検定申し込み受付中 ' . (int)$month . '月' . (int)$day . '日まで';
            // ---- set button
            $sumAppEiken = $this->homeService->sumReExam($this->orgId, $dtEikenSchedule[0]["id"], $dateCurrent, 'SUBMITTED');
            $sumAppEiken1 = $this->homeService->sumReExam($this->orgId, $dtEikenSchedule[0]["id"], $dateCurrent, 'DRAFT');
            
            /* fix error check exist apply eiken of dantai, show button click redirect to list, else redirect to policy page   */
            $applyEikenOrg = $this->homeService->getApplyEikenOrg($this->orgId, $dtEikenSchedule[0]["id"]);
            if ($applyEikenOrg) {
                $btnEikenRes = '<a class="btn btn-red  btn-180" onclick="openUrl(' . '\'/eiken/eikenorg\'' . ',' . '\'英検\'' . ',' . $dtKai . ',' . $year . ')"> <span class="awesome-sm chevron-circle-right-w"> </span>英検申し込み</a>';
            } else {
                $btnEikenRes = '<a class="btn btn-red  btn-180" href="/eiken/eikenorg/policy"> <span class="awesome-sm chevron-circle-right-w"> </span>英検申し込み</a>';
            }
        }

        /**
         * NghiaNM3
         * enable ApplyEiken after deadlineTo with role 1,2,3
         */
        if (PublicSession::isSysAdminOrServiceManagerOrOrgSupervisor()) {
            $dtES = $this->homeService->getCurrentEikenSchedule()['deadlineTo']->format('Y-m-d');
            $dtKai = $this->homeService->getCurrentEikenSchedule()['kai'];
            $applyEikenOrg = $this->homeService->getApplyEikenOrg($this->orgId, $this->homeService->getCurrentEikenSchedule()['id']);
            if ($applyEikenOrg) {
                $btnEikenRes = '<a class="btn btn-red  btn-180" onclick="openUrl(' . '\'/eiken/eikenorg\'' . ',' . '\'英検\'' . ',' . $dtKai . ',' . $year . ')"> <span class="awesome-sm chevron-circle-right-w"> </span>英検申し込み</a>';
            } else {
                $btnEikenRes = '<a class="btn btn-red  btn-180" href="/eiken/eikenorg/policy"> <span class="awesome-sm chevron-circle-right-w"> </span>英検申し込み</a>';
            }
        }

        
        // TODO thay nam tai chinh = nam server
        $year = date('Y');
        // ChungDV
        // message system notification home page
        $msgSystemNotification = $this->homeService->systemNotification($this->orgId, $year, $this->orgNo);
            
        $viewModel->setVariables(array(
            'notifi' => $notifi,
            'btnEikenRes' => $btnEikenRes,
            'msgSystemNotification' => ($msgSystemNotification) ? current($msgSystemNotification) : ''  
        ));
        
        // message apply notification home page
        
        $fiscalYear = $this->dantaiService->getCurrentYear();
        $msgApplyNotification = $this->homeService->applyNotification($this->orgId, $year, $this->orgNo, $fiscalYear);
        $viewModel->setVariables(array(
            'notifi' => $notifi,
            'btnEikenRes' => $btnEikenRes,
            'currentMsgApplyNotification' => ($msgApplyNotification) ? current($msgApplyNotification) : ''
        ));
        
        // -- TIMELINE  
        //--- BEGIN TAIVH   
        $lblEikenRes = '';
        if ($eikenSchedule) {
            $lblEikenRes = '第' . $eikenSchedule['kai'] . '回検定申し込み受付中';
        }
        $dataTimeline = $this->homeService->getDataTimeline();

        // In this case, only use the timeline
        $currYear = date('Y');
        $currMonth = date('m');
        $currDay = date('d');
        $isNotShowMSG = $this->dantaiService->isNotShowMSGGradeClass($currYear, $this->orgId);
        $isSpecialOrg = $this->dantaiService->isSpecialOrg($this->orgId, $eikenScheduleId);
        $isSemiMainVenue = $this->dantaiService->getSemiMainVenueOrigin($this->orgId, $eikenScheduleId);
        
        $round2Day1 = $currentEikenSchedule['round2Day1ExamDate'] 
                ? $currentEikenSchedule['round2Day1ExamDate']->format('Y') == $year 
                ? sprintf(str_replace('-', '%s',$currentEikenSchedule['round2Day1ExamDate']->format('n-j')),'月').'日' 
                : sprintf(str_replace('-', '%s',$currentEikenSchedule['round2Day1ExamDate']->format('Y-n-j')),'年','月').'日' 
                : ''; 
        $round2Day2 = $currentEikenSchedule['round2Day2ExamDate'] 
                ? $currentEikenSchedule['round2Day2ExamDate']->format('Y') == $year 
                ? sprintf(str_replace('-', '%s',$currentEikenSchedule['round2Day2ExamDate']->format('n-j')),'月').'日' 
                : sprintf(str_replace('-', '%s',$currentEikenSchedule['round2Day2ExamDate']->format('Y-n-j')),'年','月').'日' 
                : ''; 
        
        $comma = $this->homeService->translate('comma');
        $round2Day1 = (count($this->homeService->listKyuNameExamDate(1)) == 5 ? '' :implode($comma,$this->homeService->listKyuNameExamDate(1)).' :')
                .'<b>A日程</b> :'
                .$round2Day1
                .'（'.$this->dantaiService->changeDay($currentEikenSchedule['round2Day1ExamDate']->format('D')).'）';
        $round2Day2 = implode($comma,$this->homeService->listKyuNameExamDate(2))
                .' :<b>B日程</b> :'
                .$round2Day2.
                '（'.$this->dantaiService->changeDay($currentEikenSchedule['round2Day2ExamDate']->format('D')).'）';
        
        // ---- Output View
        $viewModel->setVariables(array(
            'data' => $dataTimeline,
            'currYear' => $currYear,
            'currMonth' => $currMonth,
            'currDay' => $currDay,
            'lblEikenRes' => $lblEikenRes,
            'isNotShowMSG' => $isNotShowMSG,
            'isSpecialOrg' => $isSpecialOrg,
            'isSemiMainVenue' => $isSemiMainVenue,
            'currentEikenSchedule' => $currentEikenSchedule,
            'friDateOrgCode' => $this->getServiceLocator()->get('Config')['OrganizationCode'][1],
            'satDateOrgCode' => $this->getServiceLocator()->get('Config')['OrganizationCode'][2],
            'sunDateOrgCode' => $this->getServiceLocator()->get('Config')['OrganizationCode'][3],
            'orgCode'        => $this->orgCode,
            'isDantaiA' => $this->dantaiService->isDantaiA(),
            'round2Day1'=>$round2Day1,
            'round2Day2'=>$round2Day2
            
        ));
        //--- END TAIVH
        $viewModel->setVariables(array(
            'organizationNo' => $this->orgNo,
        ));
        $listFileDownloadDB = $this->getEntityManager()->getRepository('Application\Entity\FileDownload')->getDataBySearch($this->orgNo);
        if($listFileDownloadDB){
            foreach($listFileDownloadDB as $value){
                $keyFile = $value['year'].'-'.$value['kai'].'-'.$value['type'];
                $listFileDownload[$keyFile] = $value;
            }
        }
        $viewModel->setVariables(array(
            'listFileDownload' => isset($listFileDownload) ? $listFileDownload : array()
        ));
        return $viewModel;
    }

    //AnhNT
    public function detailb2Action()
    {
        if (!PublicSession::isHighSchool()) {
            return $this->redirect()->toUrl('/');
        }
        $orgNo = $this->orgNo;
        $orgId = $this->orgId;
        $cityName = $this->homeService->getCityNameByOrgNo($orgNo);
        $year = $this->year - 1;
        $this->em = $this->getEntityManager();
        $objSchoolyear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($orgId);
        $listSchoolYear = array();
        if ($objSchoolyear) {
            foreach ($objSchoolyear as $value) {
                $listSchoolYear[$value['id']] = $value['displayName'];
            }
        }
        $search = $this->dantaiService->getSearchCriteria($this->getEvent(), array(
            'year' => $year + 1,
            'type' => 'Deem',
        ));
        
        if ($this->getRequest()->isPost() && $search['token']) {
            return $this->redirect()->toUrl('/homepage/homepage/detailb2/search/' . $search['token']);
        }
        $dataGraph = $this->homeService->getDataGraphBSv($orgId, $orgNo, $year, $this->orgCode);
        $dataJsonGraph = Json::encode($dataGraph);
        $dllYear = $this->dllYear;
        //hanrd code
        $dataTime = 'all';
        $returnMax = $this->homeService->getMaxRateAndTotal($dataGraph);
        $dataB = array(
            'orgNo' => $orgNo,
            'orgId' => $orgId,
            'time' => $dataTime,
            'year' => $year,
            'cityName' => $cityName
        );
        
        $lastOrgSchoolYear = end($objSchoolyear);
        $lastOrgSchoolYearId = $lastOrgSchoolYear ? $lastOrgSchoolYear['id'] : 0;
        
        list($goalResultSchoolYear, $goalResultClass, $graduationGoal, $class) = $this->homeService->getDataGoalResult($orgId, $search, $lastOrgSchoolYearId);
        $resultSchoolYear = isset($goalResultSchoolYear[$search['year']]) ? $goalResultSchoolYear[$search['year']] : array();
        $resultClass = isset($goalResultClass[$search['year']]) ? $goalResultClass[$search['year']] : array();
        $resultGoal = isset($graduationGoal[$search['year']]) ? $graduationGoal[$search['year']] : array();
        $listYear = $this->homeService->listYear();
        $dataJsonB = Json::encode($dataB);
        //
        $allClassOfOrg = $this->em->getRepository('Application\Entity\ClassJ')->getAllClassIdOfOrgByYear($orgId,$search['year']);
        //
        $viewModel = new ViewModel(array(
            'dataGraphJ' => $dataJsonGraph,
            'dataGraph' => $dataGraph,
            'orgNo' => $orgNo,
            'time' => $dataTime,
            'year' => $year,
            'ddlYear' => $dllYear,
            'cityName' => $cityName,
            'dataJsonB' => $dataJsonB,
            'arrayMax' => $returnMax,
            'listYear' => $listYear,
            'resultSchoolYear' => $resultSchoolYear,
            'search' => $search,
            'listSchoolYear' => $listSchoolYear,
            'resultGoal' => $resultGoal,
            'resultClass' => $resultClass,
            'class' => $class,
            'allClassOfOrg' => $allClassOfOrg,
        ));
        return $viewModel;
    }

    public function detailcAction()
    {
        $em = $this->getEntityManager();
        $orgNo = $this->orgNo;
        $page = 1;
        $year = $this->year;
        $dataGraph = $this->homeService->getDataDetailC($orgNo,$year);
        $dataJsonGraph = Json::encode($dataGraph['data']);
        $dataKai = 'all';
        
        $dataClass = $this->homeService->getDetailByYearAndTime($orgNo, $year, $dataKai);  
            
        $data = array();
        $dllYear = $this->dllYear;
        $schoolYearName = null;

        $schoolClassification = null;
        $schoolYearCode = null;
        
        $offset = ($page - 1) * $this->limit;
        foreach ($dataClass as $key => $item) {
            if ($dataClass[$key]["schoolYearCode"] !== '' && $dataClass[$key]["schoolYearCode"] !== null) {
                $schoolYearName = $dataClass[$key]["schoolYearName"];
                $schoolYearCode = $dataClass[$key]["schoolYearCode"];
                $schoolClassification = $dataClass[$key]["schoolClassification"];
                $sum = $dataClass[$key]["countElement"];
                $keyActive = $key;
                break;
            }
        }
        
        $paginator = $em->getRepository('Application\Entity\EikenTestResult')->
        getDataDetailTableC($orgNo, $year, $dataKai, $schoolYearCode, $schoolClassification, NULL);
     
        $config = $this->getServiceLocator()->get('config');
        $data['schoolCode'] = $config['School_Code'];
        $data['listMappingLevel'] = $config['MappingLevel'];
        
        $data['dataGraphJ'] = $dataJsonGraph;
        $data['dataGraph'] = $dataGraph['data'];
        $data['orgNo'] = $orgNo;
        $data['dataClass'] = $dataClass;
        $data['dataTableC'] = $paginator->getItems($offset, $this->limit);
        $data['paginator'] = $paginator;
        $data['numPerPage'] = $this->limit;
        if(!isset($keyActive)) $keyActive = 'other';
        $data['activeKey'] = $keyActive;
        $data['time'] = $dataKai;
        $data['year'] = $year;
        $data['page'] = $page;
        $data['maxPeople'] = $dataGraph['max'];
        $data['ddlYear'] = $dllYear;      
     
        $viewModel= new ViewModel($data);
        return $viewModel;
    }  

    /**
     * This action for call Ajax- get data when DDL Year/Kai of Detail C change
     * DucNA
     */
    public function ajaxDetailPeopleByTimeAction()
    {
        $em = $this->getEntityManager();
        $data = array();
        $kai = $this->params()->fromPost('time');
        $orgNo = $this->params()->fromPost('orgNo');
        $year = $this->params()->fromPost('year');
        $page = 1;
        $offset = ($page - 1) * $this->limit;
        $dataClass = $this->homeService->getDetailByYearAndTime($orgNo, $year, $kai);
        $data['dataClass'] = $dataClass;
        if (empty($page)) {
            $page = 1;
        }
        $data['page'] = $page;
        $schoolYearName = null;
        $schoolClassification = null;
        $schoolYearCode = null;

        if (! empty($dataClass)) {    
            foreach ($dataClass as $key => $item) {
                    $schoolYearName = $dataClass[$key]["schoolYearName"];
                    $schoolYearCode = $dataClass[$key]["schoolYearCode"];
                    $data['sumExam'] = $dataClass[$key]["countElement"];
                    $data['activeKey'] = $key;   
                    $schoolClassification = $dataClass[$key]["schoolClassification"];
                    if($dataClass[$key]["mappingStatus"] != 1)
                    {
                        $schoolYearCode = $dataClass[$key]["schoolYearCode"];
                        if(isset($dataClass[$key]["activeKey"]))
                            $data['activeKey'] = 'other';
                    }           
                    break;
            }
            if(!isset($data['activeKey'])){
                $data['activeKey'] = 'other';
            }
            
            $paginator = $em->getRepository('Application\Entity\EikenTestResult')->getDataDetailTableC($orgNo, $year, $kai, $schoolYearCode, $schoolClassification, NULL);
            $config = $this->getServiceLocator()->get('config');
            $data['schoolCode'] = $config['School_Code'];
            if(!isset($data['activeKey'])) $data['activeKey'] = 'other';
            $data['listMappingLevel'] = $config['MappingLevel'];
            $data['dataTableC'] = $paginator->getItems($offset, $this->limit);
            $data['paginator'] = $paginator;
            $data['numPerPage'] = $this->limit;
        }
        
        $template = '/home-page/home-page/ajax-detail-people-by-time.phtml';
        $params = $data;
        $htmlOutput = $this->homeService->getHtmlOutPutOfTemplate($template, $params);
        $response = array(
            'status' => 1,
            'content' => $htmlOutput
        );
        if(empty($dataClass)){
            $response['status'] =0;
        }
        return $this->getResponse()->setContent(Json::encode($response));
    }

    /**
     * This action for call Ajax - get data Exam Table of detail C
     * DucNA
     */
    public function ajaxDataDetailTableCAction()
    {
        $em = $this->getEntityManager();
        $kai = $this->params()->fromPost('time');
        $orgNo = $this->params()->fromPost('orgNo');
        $page = $this->params()->fromPost('page');
        $offset = ($page - 1) * $this->limit;
        $year = $this->params()->fromPost('year');
//        update function for : #GNCCNCJDM-304
        $schoolYearCode = $this->params()->fromPost('schoolYearCode');
        $schoolClassification = $this->params()->fromPost('schoolClassification');
//        update function for : #GNCCNCJDM-304
        $paginator = $em->getRepository('Application\Entity\EikenTestResult')->getDataDetailTableC($orgNo, $year, $kai, $schoolYearCode, $schoolClassification, NULL);
        
        $data['dataTableC'] = $paginator->getItems($offset, $this->limit);
        $data['paginator'] = $paginator;
        $data['numPerPage'] = $this->limit;
        $data['page'] = $page;
        $config = $this->getServiceLocator()->get('config');
        $data['listMappingLevel'] = $config['MappingLevel'];
        
        $template = '/home-page/home-page/ajax-data-detail-table-c.phtml';
        $params = $data;
        $htmlOutput = $this->homeService->getHtmlOutPutOfTemplate($template, $params);
        $response = array(
            'status' => 1,
            'content' => $htmlOutput
        );
        if(empty($data['dataTableC'])){
            $response['status'] =0;
        }
        return $this->getResponse()->setContent(Json::encode($response));
    }

    /**
     * This action for call Ajax when DDL of detail-B change
     * DucNA
     */
    public function ajaxDetailClassBAction()
    {
        $time = $this->params()->fromPost('time');
        $orgNo = $this->params()->fromPost('orgNo');
        $year = $this->params()->fromPost('year');
        $page = 1;
        $data = array();
        $dataClass = $this->homeService->getDetailClassBSv($orgNo, $year, $time);

        if (! empty($dataClass)) {
            $dataTableB = $this->homeService->getTableByClassBSv($dataClass['data'], $orgNo, $year, $time, $page);
            $data = array();
            $data['dataClass'] = $dataClass['data'];
            
            $data['numPerPage'] = $this->limit;
            $data['paginator'] = $dataTableB['paginator'];
            $data['dataTableB'] = $dataTableB['data'];
            if (! empty($dataClass['data'][0]['year'])) {
                $data['activeKey'] = $dataTableB['keyActive'];
            }      
    
            $config = $this->getServiceLocator()->get('config');
            $data['schoolCode'] = $config['School_Code'];
            $data['listMappingLevel'] = $config['MappingLevel'];
            $data['sumTotal'] = $dataClass['sumTotal'];
            $data['sumTotalReal'] = $dataClass['sumTotalReal'];
            $data['page'] = $page;
            $data['time'] = $time;
            $data['year'] = $year;
        }
        
        
        $template = '/home-page/home-page/ajax-detail-class-b.phtml';
        $params = $data;
        $htmlOutput = $this->homeService->getHtmlOutPutOfTemplate($template, $params);
        $response = array(
            'status' => 1,
            'content' => $htmlOutput
        );
        if(empty($dataClass)){
            $response['status'] =0;
        }
        return $this->getResponse()->setContent(Json::encode($response)); 
    }
    
    
    /**
     * This action for call Ajax - get data table of Exam at page detail B
     * DucNA
     */
    public function ajaxTableByClassBAction()
    {
        $time = $this->params()->fromPost('time');
        $orgNo = $this->params()->fromPost('orgNo');
        $year = $this->params()->fromPost('year');
        $sum = $this->params()->fromPost('sum');
        $page = $this->params()->fromPost('page');        
        $schoolYearName = $this->params()->fromPost('schoolYearName');
        
        $schoolClassification = $this->params()->fromPost('schoolClassification');
        $schoolYearCode = $this->params()->fromPost('schoolYearCode');
        $notMap = $this->params()->fromPost('notMap');
        
        $data = array();
        $dataTableB = $this->homeService->getTableByClassBSv(NULL, $orgNo, $year, $time, $page, $schoolYearCode,$schoolClassification, $schoolYearCode, $notMap);
        if (! empty($dataTableB)) {
            $data['dataTableB'] = $dataTableB['data'];
            $data['paginator'] = $dataTableB['paginator'];
            $data['sumExam'] = $sum;
            $data['numPerPage'] = $this->limit;
            // $data['activeKey'] = $dataTableB['keyActive'];
            $config = $this->getServiceLocator()->get('config');
            $data['listMappingLevel'] = $config['MappingLevel'];
            $data['page'] = $page;
            $data['time'] = $time;
            $data['year'] = $year;
        }

        $viewModel = new ViewModel($data);
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    /**
     * TaiVH
     */
    public function siteMapAction()
    {
        return new ViewModel();
    }
    
    /**
     * ChungDV
     */
    public function userManualAction()
    {
        return new ViewModel();
    }
    
    /**
     * ChungDV
     */
    public function policyAction()
    {
        return new ViewModel();
    }
    
    /**
     * ChungDV
     */
    public function privacyPolicyAction()
    {
        return new ViewModel();
    }
    
    /**
     * ThanhNX6
     */
    public function termsOfUseAction()
    {
        return new ViewModel();
    }
    
    /**
     * @author taivh
     */
    public function achieveGoalAction()
    {
        //This functionality is temporarily closed on production GNCCNCJDM-258
        return $this->redirect()->toUrl('/');
        
        $viewModel = new ViewModel();
        $orgNo = $this->orgNo;     
        $orgId = $this->orgId;
        $currentYear = $this->year;    
        $year = $currentYear;        
        $isGraduationGoal = 2; // Fix for get all
        $target = 0;
        $eikenLevelId = 0;
        $levelName = '';
        $orgSchoolYearId = 0;
        $numOfSchoolYear = 0;
        $schoolIndex = array();

        $kai = $this->homeService->getMaxKai($currentYear);

        $listOrgSchoolYear =  $this->homeService->listOrgSchoolYear($orgId);
        
        //--- Set index of OrgSchoolYearId. Used to calculate the location of the school year
        if( !empty($listOrgSchoolYear) )
        {            
            $orgSchoolYearId = $listOrgSchoolYear[0]['id'];
            $numOfSchoolYear = count($listOrgSchoolYear);
            
            for($i = 0; $i<$numOfSchoolYear;$i++)
            {
                $schoolIndex[$listOrgSchoolYear[$i]['id']] = $numOfSchoolYear - $i; 
            }
        }

        $lastOrgSchoolYear = $orgSchoolYearId;
        $orgSchoolYearId = $this->params()->fromRoute('orgscy', $orgSchoolYearId);

        //--- out put View
        $viewModel->setVariables(array(
            'orgscy' => $orgSchoolYearId,
            'listOrgSchoolYear' => $this->homeService->listOrgSchoolYear($orgId, 'ASC'),            
        ));

        $numOfSchoolYear = ( isset($schoolIndex[$orgSchoolYearId]) ) ? $schoolIndex[$orgSchoolYearId] : 0;
        
        //--- Get target and Level of year when select orgSchoolYearId
        $tg = $this->homeService->getTarget($orgId, $orgSchoolYearId, $currentYear, $isGraduationGoal);        
        if($tg)
        {            
            $eikenLevelId = $tg[0]["eikenLevelId"];
            $target = $tg[0]["targetPass"];
            $levelName = $tg[0]["levelName"];            
        }
        else //---- Get target and Level of Graduation year when select orgSchoolYearId
        {
            $tg = $this->homeService->getTarget($orgId, 0, $currentYear + count($listOrgSchoolYear) - $numOfSchoolYear, 1);
            if($tg)
            {
                $eikenLevelId = $tg[0]["eikenLevelId"];
                $target = $tg[0]["targetPass"];            
                $levelName = $tg[0]["levelName"];             
            }
        }        

        $dt  = $this->homeService->getPassRatingAndComparing($orgId, $orgNo, $year, $eikenLevelId, $target, $kai, $orgSchoolYearId, $numOfSchoolYear);        
           
        // ---- Output View
        // $passRate   : Pass rate this year
        // $compRate   : Comparing Rate with Kai before
        // $numPass    : Number of student pass this year
        // $numPassComp: Comparing number pass this year with Kai before
        // $passRate1  : Pass rate of Kai before 
        // $numPass1   : Number pass of Kai before
        // $passRateY1 : Pass rate last year
        // $numPassY1  : Number of student pass last year
        $viewModel->setVariables(array(
            'passRate' => $dt[0],
            'compRate' => $this->homeService->getValueWithColorHomeDetailA($dt[1], '%'),
            'numPass' => $dt[2],
            'numPassComp' => $this->homeService->getValueWithColorHomeDetailA($dt[3], '人'),
            'passRate1' => $dt[4],
            'numPass1' => $dt[5],
            'passRateY1' => $dt[6],
            'numPassY1' => $dt[7],
        ));
        
        // ------ Set infor display target
        if($levelName != '') $lblTarget = '卒業時 <br/>英検' . $levelName . '取得';
            else $lblTarget ='';
        $viewModel->setVariables(array(
            'lblTarget' => $lblTarget,
            'targetOrg' => $target.'<span> %</span>',
        ));
        
        // ---- DISPLAY HIGHT CHART
        // tblA1     : Total number pass student for current year - 2
        // jsonDataA1: Json data for hightchart
        // tblA2     : Pass rate for current year - 2
        // jsonDataA2: json data for hightchart
        
        $dataChart = $this->homeService->getHightChartForAchieveGoal($orgId, $orgNo, $year, $eikenLevelId, $target, $kai, $orgSchoolYearId, count($listOrgSchoolYear));        
        $viewModel->setVariables(array(
            'tblA1' => $dataChart[0],
            'jsonDataA1' => $dataChart[1],
            'tblA2' => $dataChart[2],
            'jsonDataA2' => $dataChart[3],
            'currentYear' => $currentYear,
        ));

        // ----- Process for table data  
        
        $page = $this->params()->fromRoute('page', 1);
        $year = $this->params()->fromRoute('year', $year);
        $kai = $this->params()->fromRoute('kai', 4);
        $kyu = $this->params()->fromRoute('kyu', 0);
        $key = $this->params()->fromRoute('key', 'col0');
        $ord = $this->params()->fromRoute('ord', 'a');  //d:desc; a:asc
             
        $limit = $this->limit;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $paginator =  $this->homeService->getListPassBySchoolYearId($orgId, $orgNo, $year, $kai, $kyu, $key, $ord);
        $listPupilPass = $paginator->getItems(($page - 1) * $limit, $limit);
        $listEikenLevel = $this->homeService->listEikenLevel();
        $viewModel->setVariables(array(
            'listEikenLevel' => $listEikenLevel,        
            'paginator' => $paginator,
            'page' => $page,
            'year' => $year,
            'kai' => $kai,        
            'kyu' => $kyu,
            'key' => $key,
            'ord' => $ord,
            'numPerPage' => $limit,        
            'listPupilPass' => $listPupilPass,
            'msg17' => $this->homeService->translate('MSG17')
        ));
      
        return $viewModel;
    }   

    /**
     * @author TaiVH
     * Export to Excel using for achieve-goal table data
     */

    public function exportPupilsToCsvAction(){
        
        $orgId = $this->orgId;
        $orgNo = $this->orgNo;
              
        $year = $this->params()->fromRoute('year', $this->year);
        $kai = $this->params()->fromRoute('kai', 4);
        $kyu = $this->params()->fromRoute('kyu', 0);
        $key = $this->params()->fromRoute('key', 'col0');
        $ord = $this->params()->fromRoute('ord', 'a');  //d:desc; a:asc
         
        $limit = 1048576;
     
        $paginator =  $this->homeService->getListPassBySchoolYearId($orgId, $orgNo, $year, $kai, $kyu, $key, $ord);
        $listPupilPass = $paginator->getItems(0, $limit);

        $filenames = "年度別達成度推移_".$year;
        if($kai != 4)$filenames = $filenames."_".$kai;
        if($kyu != 0)$filenames = $filenames."_".$kyu;
        $filenames = $filenames."_".date('Ymd') . '.xlsx';
        
        $objPHPExcel = \Dantai\Utility\PHPExcel::export($listPupilPass, $filenames, 'homepage-detaila');
        return $this->getResponse();
    }

    //Ducna17
    public function getExportListAttendPupilAction(){
        $typeDetail = null;
        $orgNo = $this->params()->fromQuery('orgNo');
        $kai = $this->params()->fromQuery('kai');
        $year = $this->params()->fromQuery('year');
        $schoolYearName = $this->params()->fromQuery('schoolyear');
        $schoolClassification = $this->params()->fromQuery('schoolClassification');
        $schoolYearCode = $this->params()->fromQuery('schoolYearCode');
        $notMap = $this->params()->fromQuery('notMap');
        
        PrivateSession::setData('is-detail-b-activity-flag', false);
        if(!empty($this->params()->fromQuery('typeDetail'))){
            $typeDetail = 'B';
            PrivateSession::setData('is-detail-b-activity-flag', true);
        }
        $config = $this->getServiceLocator()->get('config');           
        return $this->homeService->exportListAttendPupil($schoolYearName, $orgNo, $kai, $year, $this->getResponse(), $config['MappingLevel'], $typeDetail, $schoolClassification, $schoolYearCode, $notMap);
    }
    
    public function detailb1Action()
    {
        $orgNo = $this->orgNo;
        $orgId = $this->orgId;
        $cityName = $this->homeService->getCityNameByOrgNo($orgNo);
        $page = 1;
        $year = $this->year;
    
        $dataGraph = $this->homeService->getDataGraphBSv($orgId, $orgNo, $year);
        
        $dataJsonGraph = Json::encode($dataGraph);
        $dllYear = $this->dllYear;
        //TODO
        $dataTime = 'all';
        $dataClass = $this->homeService->getDetailClassBSv($orgNo, $year, $dataTime);
    
        $returnMax = $this->homeService->getMaxRateAndTotal($dataGraph);
    
        $dataB = array(
            'orgNo' => $orgNo,
            'orgId' => $orgId,
            'time' => $dataTime,
            'year' => $year,
            'cityName' => $cityName
        );
        $dataJsonB = Json::encode($dataB);
        if(!empty($dataClass['data'])){
            $data['dataClass'] = $dataClass['data'];
            $data['sumTotal'] = $dataClass['sumTotal'];
            $data['sumTotalReal'] = $dataClass['sumTotalReal'];
            $dataTableB = $this->homeService->getTableByClassBSv($dataClass['data'], $orgNo, $year, $dataTime, $page);
            if(!empty($dataTableB)){
                $data['dataTableB'] = $dataTableB['data'];
                $data['paginator'] = $dataTableB['paginator'];
            }
    
        }
        $config = $this->getServiceLocator()->get('config');
        $data['schoolCode'] = $config['School_Code'];
        $data['listMappingLevel'] = $config['MappingLevel'];
        $data['numPerPage'] = $this->limit;
        $data['page'] = $page;
    
        $data['dataGraphJ'] = $dataJsonGraph;
        $data['dataGraph'] = $dataGraph;
        $data['orgNo'] = $orgNo;
        $data['time'] = $dataTime;
        $data['year'] = $year;
        $data['ddlYear'] = $dllYear;
        $data['cityName'] = $cityName;
        $data['dataJsonB'] = $dataJsonB;
        $data['arrayMax'] = $returnMax;
    
        if (! empty($dataClass['data'][0]['year'])) {
            $data['activeKey'] = $dataTableB['keyActive'];
        }
        $viewModel = new ViewModel($data);
        return $viewModel;
    }

    public function downloadEikenIdAction()
    {
        $em = $this->getEntityManager();
        $listTypeStaticDownloadedFile = $this->getServiceLocator()->get('Config')['listTypeStaticDownloadedFile'];
        $orgNo = $this->orgNo;
        $type = '4s5s_credentials';
        $year = $this->params()->fromRoute('year');
        $kai = $this->params()->fromRoute('kai');

        if (!isset($listTypeStaticDownloadedFile[$type])) {
            $error = 'Do not exits this type';
            $response = $this->getResponse();
            $response->setContent($error);
            return $response;
        }

        /* @var $fileDownload \Application\Entity\FileDownload */
        $fileDownload = $em->getRepository('Application\Entity\FileDownload')->getOneDataBySearch($orgNo, $type, $year, $kai);
        if (!$fileDownload) {
            $error = 'ダウンロード可能なファイルがありません。';
            $response = $this->getResponse();
            $response->setContent($error);
            return $response;
        }
        $bucket = 'dantai' . getenv('APP_ENV');
        $keyObject = $listTypeStaticDownloadedFile[$type]['homeDir'] . $year . '/' . $kai . '/' . $fileDownload->getFilename();
        $contentType = 'application/pdf';
        $filename = $fileDownload->getFilename();

        $result = \Dantai\Aws\AwsS3Client::getInstance()->readObject($bucket, $keyObject);
        if ($result["status"] == 1) {
            $logPath = DATA_PATH . '/downloadEikenId'. date('Ymd') .'.txt';
            $stream = @fopen($logPath, 'a', false);
            if ($stream) {
                $writer = new \Zend\Log\Writer\Stream($logPath);
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $user = $this->dantaiService->getCurrentUser();
                $ipAddress = $this->dantaiService->getIpAddress();
                $arrLogInfo = array(
                    'IpAddress' => $ipAddress,
                    'UserId' => $user['userId'],
                    'DantaiNo' => $user['organizationNo'],
                    'Year' => $year,
                    'Kai' => $kai,
                    'Filename' => $filename,
                );
                $logger->info(\Zend\Json\Json::encode($arrLogInfo));
            }

            $resultObj = $result["content"];
            $filename = \Dantai\Utility\CharsetConverter::utf8ToShiftJis($filename);
            header('Content-type: ' . $contentType . ';charset=utf-8');
            header('Content-Length: ' . strlen($resultObj['Body']));
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            echo $resultObj["Body"];
        } else {
            $error = 'ダウンロード可能なファイルがありません。';
            $response = $this->getResponse();
            $response->setContent($error);
            return $response;
        }
    }
    public function translate($key)
    {
        return $this->getServiceLocator()->get('MVCTranslator')->translate($key);
    }
}