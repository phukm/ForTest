<?php
namespace History\Controller;

use History\Service\MappingEikenResultService;
use History\Service\MappingIbaResultService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use Doctrine\ORM\EntityManager;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use History\Service\ServiceInterface\IbaHistoryServiceInterface;
use History\Service\ServiceInterface\EikenHistoryServiceInterface;
use Dantai\PrivateSession;
use History\HistoryConst;
use Dantai\Utility\PHPExcel;
use Dantai\Utility\CharsetConverter;
use History\Service\ExportExcelMapper;
use Dantai\Utility\MappingUtility;

class IbaHistoryController extends AbstractActionController
{
    use \Application\Controller\ControllerAwareTrait;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var \History\Service\IbaHistoryService
     */
    protected $ibaHistoryService;
    /**
     *
     * @var \History\Service\EikenHistoryService
     */
    protected $eikenHistoryService;
    /**
     *
     * @var EntityManager
     */
    protected $em;
    /**
     * @var MappingIbaResultService
     */
    protected $mappingIbaResultService;
    /**
     * @var MappingEikenResultService
     */
    protected $mappingEikenResultService;
    protected $roleId;
    public function __construct(DantaiServiceInterface $dantaiService, EikenHistoryServiceInterface $eikenHistoryService, IbaHistoryServiceInterface $ibaHistoryService, EntityManager $entityManager)
    {
        $this->ibaHistoryService = $ibaHistoryService;
        $this->eikenHistoryService = $eikenHistoryService;
        $this->dantaiService = $dantaiService;
        $user = PrivateSession::getData('userIdentity');
        $this->orgId = $user['organizationId'];
        $this->orgNo = $user['organizationNo'];
        $this->roleId = $user['roleId'];
        $this->em = $entityManager;
    }

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->mappingIbaResultService = $this->getServiceLocator()->get('History\Service\MappingIbaResultService');
        $this->mappingEikenResultService = new MappingEikenResultService($this->getServiceLocator());

        return parent::onDispatch($e);
    }
    
    public function getMessages()
    {
        $translate = $this->getServiceLocator()->get('MVCTranslator');
        $messages = array(
            'noResultIsFoundMSG12'                               => $translate->translate('noResultIsFoundMSG12'),
            'registerStudentlistFirstMSG57'                      => $translate->translate('registerStudentlistFirstMSG57'),
            'eikenResultConfirmSuccessMEG58'                     => $translate->translate('eikenResultConfirmSuccessMEG58'),
            'errorsHappenWhenRetrievingResultsContactAdminMEG59' => $translate->translate('errorsHappenWhenRetrievingResultsContactAdminMEG59'),
            'errorsHappenContactAdminMEG60'                      => $translate->translate('errorsHappenContactAdminMEG60'),
            'btnStudentList'                                     => $translate->translate('btnStudentList'),
            'notCheckbox'                                        => $translate->translate('notCheckbox'),
            'SHOW_POPUP_MSG_PUPIL_NO_NAME_KANNA'                 => $translate->translate('SHOW_POPUP_MSG_PUPIL_NO_NAME_KANNA')
            );

        return json_encode($messages);
    }

    public function indexAction()
    {
        return new ViewModel();
    }

    public function examResultAction()
    {
        return new ViewModel();
    }

    public function confirmResultAction()
    {
        $this->ibaHistoryService->getNews();
        $viewModel = new ViewModel();

        return $viewModel;
    }

    public function pupilAchievementAction()
    {
        $sessionExamDate = PrivateSession::getData('examdate');
        $sessionJisshiId = PrivateSession::getData('jisshiId');
        $sessionExamType = PrivateSession::getData('examType');
        $examDate = date_format(date_create($sessionExamDate), "Y/m/d");
        $searchCriteria = $this->dantaiService->getSearchCriteria($this->getEvent(), array(
            'examDate' => $examDate,
            'orgSchoolYear' => $this->params()->fromPost('orgSchoolYear'),
            'classj' => $this->params()->fromPost('classj'),
            'name' => $this->params()->fromPost('name'),
            'sortOrder' => '',
            'sortKey' => '',
            'searchVisible' => 0,
            'jisshiId' => $sessionJisshiId,
            'examType' => $sessionExamType,
            'token' => ''
        ));
        $sortOrder = !empty($searchCriteria['sortOrder']) ? $searchCriteria['sortOrder'] : 'asc';
        $sortKey = !empty($searchCriteria['sortKey']) ? $searchCriteria['sortKey'] : '';
        $searchVisible = isset($searchCriteria['searchVisible']) ? $searchCriteria['searchVisible'] : 0;
        $routeMatch = $this->getEvent()
                ->getRouteMatch()
                ->getParam('controller') . '_' . $this->getEvent()
                ->getRouteMatch()
                ->getParam('action');
        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;

        // messages when export excel not data
        $flashMessages = $this->flashMessenger()->getMessages();
        $messages = '';
        if ($flashMessages) {
            $messages = $flashMessages[0]['noRecordExcel'];
        }

        $pupilAchievement = $this->ibaHistoryService->getListInquiryIBA($sortOrder, $sortKey, $searchVisible, $page, $limit, $offset, $this->isPost(), $searchCriteria, $routeMatch, $this->getRequest(), $this->params(), $this->flashMessenger(), $this->dantaiService, $this->redirect(), $messages);

        // export excel
        $isExportExcel = $this->params()->fromRoute('isExportExcel');
        if ($isExportExcel) {
            return $this->exportIbaPupilAchievementExcel($pupilAchievement['pupilAchievementList']);
        }

        return $pupilAchievement;
    }

    public function ajaxGetClassesAction()
    {
        return $this->ibaHistoryService->getListClassesBySchoolYear($this->params(), $this->getResponse());
    }

    public function getDataByExamDateAction()
    {
        return $this->ibaHistoryService->setSessionExamDate($this->params(), $this->redirect());
    }

    /**
     *
     * @author KhoaNV4
     */
    public function confirmExamResultAction()
    {
        $sessionExamdate = PrivateSession::getData('examDate');
        if ($sessionExamdate != null) {
            $examDateFromSession = date_format(date_create($sessionExamdate), "Y/m/d");
        }
        $year = PrivateSession::getData('yearNo');
        $kai = PrivateSession::getData('kaiNo');
        $page = $this->params('page') != null ? $this->params('page') : 1;
        $limit = 20;
        $jisshiId = PrivateSession::getData('jisshiId');
        $examType = PrivateSession::getData('examType');
        if (empty($jisshiId) || empty($examType)) {
            return $this->redirect()->toRoute('history/default', array('controller' => 'eiken', 'action' => 'exam-result'));
        }
        $user = PrivateSession::getData('userIdentity');
        $organizationId = $user['organizationId'];
        $em = $this->getEntityManager();
        /* @var $applyIbaOrg \Application\Entity\ApplyIBAOrg */
        $applyIbaOrg = $em->getRepository('Application\Entity\ApplyIBAOrg')->findOneBy(array(
            'organizationId' => $organizationId,
            'jisshiId' => $jisshiId,
            'examType' => $examType
        ));
        if($applyIbaOrg){
            $examDate = $applyIbaOrg->getTestDate() != Null ? $applyIbaOrg->getTestDate()->format('Y/m/d') : $examDateFromSession;
        }else{
            $examDate = $sessionExamdate;
        }
//        update function for : #GNCCNCJDR5-761
        $ibaMasterData = $em->getRepository('Application\Entity\IbaScoreMasterData')->getListIbaScoreMasterData();
        $result = $this->mappingIbaResultService->getIBAExamResult($jisshiId, $examType);
        if (isset($result->kekka)) {
            return $this->redirect()->toRoute('history/default', array('controller' => 'eiken', 'action' => 'exam-result'));
        } else {
            // sort result by school year, class, name kana
            $resultSort = $result->eikenArray;
            usort($resultSort, function ($a, $b) {
                $r = strcmp(trim($a->gakunen), trim($b->gakunen));
                if ($r !== 0)
                    return $r;
                $r = strcmp(trim($a->class1), trim($b->class1));
                if ($r !== 0)
                    return $r;

                return strcmp(trim($a->shimeikana), trim($b->shimeikana));
            });
            $translator = $this->getServiceLocator()->get('MVCTranslator');
            $jsMessages = array(
                'SHOW_POPUP_MSG_CONFIRM_EXAM_RESULT' => $translator->translate('SHOW_POPUP_MSG_CONFIRM_EXAM_RESULT')
            );
            $viewModel = new ViewModel();
            $viewModel->setVariables(array(
                'result' => $resultSort,
                'page' => $page,
                'limit' => $limit,
                'noRecord' => $translator->translate('MSG13'),
                'jisshiId' => $jisshiId,
                'examType' => $examType,
                'examId' => PrivateSession::getData('examId'),
                'yearNo' => $year,
                'kaiNo' => $kai,
                'examDate' => $examDate,
                'jsMessages' => json_encode($jsMessages),
                'examType' => $examType,
                'ibaMasterData' => $ibaMasterData
            ));

            return $viewModel;
        }
        return $this->redirect()->toRoute('history/default', array('controller' => 'eiken', 'action' => 'exam-result'));
    }

    public function mappingDataAction()
    {
        $year = $this->params()->fromPost('yearNo', date('Y'));
        $jisshiId = $this->getRequest()->getPost('jisshiId');
        $examType = $this->getRequest()->getPost('examType');
        $applyIbaId = $this->params()->fromPost('applyEikenId', 0);
        $hasImportData = $this->params()->fromPost('hasImportData', 0);
        PrivateSession::setData('applyIbaId', $applyIbaId);
        if($hasImportData == 1){
            $response = $this->mappingIbaResultService->setDataToSave($this->orgNo, $this->orgId, $jisshiId, $examType, $applyIbaId);
            if($response['status'] == HistoryConst::IMPORT_SUCCESS){
                $this->mappingIbaResultService->updateOneIBAHeader($this->orgNo, $jisshiId, $examType);
            }
        }
        $result = $this->mappingIbaResultService->mappingDataIbaResult($year, $jisshiId, $examType);
        $response = array(
            'mappingStatus' => $result['status']
        );
        return $this->getResponse()->setContent(Json::encode($response));
    }

    public function detailAction()
    {
        $ibaId = $this->params()->fromRoute('id', '');
        $data = $this->ibaHistoryService->getIBAExamResultId($ibaId, $this->orgNo);
        if ($data) {
            $translator = $this->getServiceLocator()->get('MVCTranslator');
            $averageScoreTotal = $translator->translate('ScoreTotal');
            $data->getAverageScoreTotal = $averageScoreTotal[1][$data->getTestType()];
            return new ViewModel(array(
                'data' => $data,
                'backAction' => $this->getRequest()->getServer('HTTP_REFERER')
            ));
        }
        return $this->redirect()->toUrl('/error/index');
    }

    public function ibaHistoryPupilAction()
    {
        $routeMatch = $this->getEvent()
                ->getRouteMatch()
                ->getParam('controller') . '_' . $this->getEvent()
                ->getRouteMatch()
                ->getParam('action');
        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $searchCriteria = $this->dantaiService->getSearchCriteria($this->getEvent(), array(
            'id' => PrivateSession::getData('ibaId'),
            'pupilId' => PrivateSession::getData('ibaPupilId'),
            'schoolYear' => PrivateSession::getData('ibaSchoolYear'),
            'className' => PrivateSession::getData('ibaClassName'),
            'pupilNumber' => PrivateSession::getData('ibaPupilNumber'),
            'name' => PrivateSession::getData('ibaName')
        ));

        $messages = $this->flashMessenger()->getMessages();
        $messagesResult = '';
        if ($messages) {
            $messagesResult = $messages[0]['MsgNoRecordExcel'];
        }
        return $this->ibaHistoryService->getHistoryPupilIBA(
                $this->params(), $searchCriteria, $page, $limit, $offset, $routeMatch, $messagesResult);
    }


     public function exportIbaHistoryPupilAction() {
        $translator = $this->getServiceLocator()->get('MVCTranslator');

        $searchCriteria = $this->dantaiService->getSearchCriteria($this->getEvent(), array(
            'id' => PrivateSession::getData('ibaId'),
            'pupilId' => PrivateSession::getData('ibaPupilId'),
            'schoolYear' => PrivateSession::getData('ibaSchoolYear'),
            'className' => PrivateSession::getData('ibaClassName'),
            'pupilNumber' => PrivateSession::getData('ibaPupilNumber'),
            'name' => PrivateSession::getData('ibaName')
        ));
        $em = $this->getEntityManager();
        $objOrg = $em->getRepository('Application\Entity\Organization')->find($this->orgId);
        $orgNo = '';
        if ($objOrg) {
            $orgNo = $objOrg->getOrganizationNo();
        }
        
        $arrIBAHistory = $this->ibaHistoryService->getHistoryPupilIbaExport($orgNo, $searchCriteria);
        if(empty($arrIBAHistory)){
            $messages = array(
                'MsgNoRecordExcel' => $translator->translate('MsgNoRecordExcel')
            );
            $this->flashMessenger()->addMessage($messages);
            return $this->redirect()->toRoute('history/default', array(
                    'controller' => 'iba',
                    'action' => 'iba-history-pupil'
                    )
            );
        }
        $objNameFile = new CharsetConverter();
        $filename = sprintf($translator->translate('Tl7'),$searchCriteria['schoolYear'],$searchCriteria['className'],$searchCriteria['pupilNumber'],$searchCriteria['name']);
        $nameFile = $objNameFile->utf8ToShiftJis($filename);
        $template = 'IBA-history-pupil-template';
  
        $objExcel = new PHPExcel();
        $objExcel->export($arrIBAHistory, $nameFile, $template, 1);
        return $this->getResponse();
    }
    
    public function getDataIbaAction()
    {
        $data = $this->params()->fromQuery();
        
        if ($data) {
            $id = $data['id'];
            $pupilId = $data['pupilId'];
            $schoolyear = $data['schoolYear'];
            $class = rawurldecode($data['className']);
            $number = rawurldecode($data['pupilNumber']);
            $name = rawurldecode($data['name']);
        }
        PrivateSession::setData('ibaId', $id);
        PrivateSession::setData('ibaPupilId', $pupilId);
        PrivateSession::setData('ibaSchoolYear', $schoolyear);
        PrivateSession::setData('ibaClassName', $class);
        PrivateSession::setData('ibaPupilNumber', $number);
        PrivateSession::setData('ibaName', $name);

        return $this->redirect()->toRoute('history/default', array(
                'controller' => 'iba',
                'action' => 'iba-history-pupil'
        ));
    }

    public function checkKekkaValueAction()
    {
        $year = $this->params()->fromQuery('year');
        $kai = $this->params()->fromQuery('kai');
        $examId = $this->params()->fromQuery('examId');
        PrivateSession::setData('yearNo', $year);
        PrivateSession::setData('kaiNo', $kai);
        PrivateSession::setData('examId', $examId);
        $examdate = $this->params()->fromRoute('examdate');
        $jisshiId = $this->params()->fromQuery('jisshiId');
        $examType = $this->params()->fromQuery('examType');
        PrivateSession::setData('jisshiId', $jisshiId);
        PrivateSession::setData('examType', $examType);
        PrivateSession::setData('examDate', $examdate);
        $result = $this->mappingIbaResultService->getIBAExamResult($jisshiId, $examType);
        if (isset($result->kekka)) {
            $kekka = $result->kekka;
        }
        else {
            if (count($result) == 1 && $result->eikenArray[0]->eikenid == '') {
                $kekka = '02';
            }
            else {
                $kekka = $result->eikenArray[0]->kekka;
            }
        }
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($kekka));
        return $response;
    }

    public function saveExamResultAction()
    {
        $jisshiId = $this->getRequest()->getPost('jisshiId');
        $examType = $this->getRequest()->getPost('examType');
        $applyId = $this->getRequest()->getPost('applyId');
        $result = $this->mappingIbaResultService->getIBAExamResult($jisshiId, $examType);
        if (isset($result) && $result->eikenArray[0]->eikenid != null) {
            $response = $this->mappingIbaResultService->saveIBAExamResult($this->orgNo, $this->orgId, $jisshiId, $examType, $result, $applyId);
            if($response['status'] == HistoryConst::IMPORT_SUCCESS){
                $this->mappingIbaResultService->updateOneIBAHeader($this->orgNo, $jisshiId, $examType);
            }
        }
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode("success"));

        return $response;
    }

    public function saveIBAExamResultOnlyAction()
    {
        $jisshiId = $this->getRequest()->getPost('jisshiId');
        $examType = $this->getRequest()->getPost('examType');
        $result = $this->mappingIbaResultService->getIBAExamResult($jisshiId, $examType);
        if (isset($result) && $result->eikenArray[0]->eikenid != null) {
            $this->mappingIbaResultService->saveIBAExamResult($this->orgNo, $this->orgId, $jisshiId, $examType, $result, '');
        }
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode("success"));

        return $response;
    }

    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    //DucNA17
    public function findIbaAction()
    {
        $ibaId = (int) $this->params()->fromPost('ibaId', '');
        $pupilId = (int) $this->params()->fromPost('pupilId', '');
        $birthday = $this->params()->fromPost('birthday', '');
        $nameKanji = $this->params()->fromPost('nameKanji', '');
        $nameKana = $this->params()->fromPost('nameKana', '');
        $number = $this->params()->fromPost('number', '');
        $orgSchoolYearId = $this->params()->fromPost('orgSchoolYearId', '');
        $orgSchoolYearName = $this->params()->fromPost('orgSchoolYearName', '');
        $classId = $this->params()->fromPost('classId', '');
        $className = $this->params()->fromPost('className', '');
        $result = false;
        if ($ibaId > 0 && $pupilId > 0) {
            $data = PrivateSession::getData('tempMappingIba');
            $data[$ibaId] = array('pupilId' => $pupilId,
                'birthday' => $birthday,
                'nameKanji' => $nameKanji,
                'nameKana' => $nameKana,
                'number' => $number,
                'orgSchoolYearId' => $orgSchoolYearId,
                'orgSchoolYearName' => $orgSchoolYearName,
                'className' => $className,
                'classId' => $classId);
            PrivateSession::setData('tempMappingIba', $data);
            //update temp EikenTestResult and insert temp EikenScore
            $this->ibaHistoryService->updateIbaTestResult($ibaId, $pupilId);
            $result = true;
        }
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($result));

        return $response;
    }

    //DucNA17
    public function saveMapingIbaAction()
    {
        $keepSessionData = PrivateSession::getData('tempMappingIba');
        //save and update
        $this->ibaHistoryService->saveMappingIba($keepSessionData);
        PrivateSession::clear('tempMappingIba');
        PrivateSession::clear('listItemSuccess');

        return $this->getResponse()->setContent(true);
    }

    //ducna17
    public function clearSessionAction()
    {
        PrivateSession::clear('tempMappingIba');
        PrivateSession::clear('listItemSuccess');

        return $this->getResponse()->setContent(true);
    }

    /**
     * DucNA17
     */
    public function mappingErrorAction()
    {
        $limit = 20;
        $year = $this->params()->fromRoute('year', date('Y'));
        $page = $this->params()->fromRoute('page', 1);
        $jisshiId = $this->params()->fromRoute('jisshiId', 0);
        $examType = $this->params()->fromRoute('examType', 0);
        if (empty($year))
            return $this->redirect()->toUrl('/error/index');
        $data = array();
        $dataMapping = $this->ibaHistoryService->getDataMapping($this->orgNo, $year, $page, 0, $jisshiId, $examType);
        if (!empty($dataMapping)) {
            $config = $this->getServiceLocator()->get('config');
            $data['iBAEikenLevelTotal'] = $config['IBAEikenLevelTotal'];
            $data['iBAEikenLevelReadListen'] = $config['IBAEikenLevelReadListen'];
            //get list pupil
            $listPupil = $this->eikenHistoryService->getListPupil($this->orgId, $year, 'IBA');
            $data['jsonListPupil'] = Json::encode($listPupil);
            $pupilManager = $this->eikenHistoryService->convertPupilManager($listPupil, 'IBA');
            $keepSession = PrivateSession::getData('tempMappingIba');
            if (!empty($keepSession)) {
                $data['keepSession'] = $keepSession;
            }
            $data['listPupil'] = $pupilManager;
            $data['mappingData'] = $dataMapping;
            $data['paginator'] = $dataMapping['paginator'];
        }
        $data['jisshiId'] = $jisshiId;
        $data['examType'] = $examType;
        $data['year'] = $year;
        $data['page'] = $page;
        $data['numPerPage'] = $limit;
        $viewModel = new ViewModel($data);

        return $viewModel;
    }

    /**
     * @author DucNA
     * UC10 - List Success mapping items
     */
    public function mappingSuccessAction()
    {
        // hand code
        $limit = 20;
        $year = $this->params()->fromRoute('year', date('Y'));
        $page = $this->params()->fromRoute('page', 1);
        $jisshiId = $this->params()->fromRoute('jisshiId', 0);
        $examType = $this->params()->fromRoute('examType', 0);
        if (empty($year))
            return $this->redirect()->toUrl('/error/index');
        $data = array();
        $dataMapping = $this->ibaHistoryService->getDataMapping($this->orgNo, $year, $page, 1, $jisshiId, $examType);
        if (!empty($dataMapping)) {
            $config = $this->getServiceLocator()->get('config');
            $data['iBAEikenLevelTotal'] = $config['IBAEikenLevelTotal'];
            $data['iBAEikenLevelReadListen'] = $config['IBAEikenLevelReadListen'];
            //get list pupil
            $listPupil = $this->eikenHistoryService->getListPupil($this->orgId, $year, 'IBA');
            $data['jsonListPupil'] = Json::encode($listPupil);
            $pupilManager = $this->eikenHistoryService->convertPupilManager($listPupil, 'IBA');
            $keepSession = PrivateSession::getData('tempMappingIba');
            if (!empty($keepSession)) {
                $data['keepSession'] = $keepSession;
            }
            $data['listPupil'] = $pupilManager;
            $data['mappingData'] = $dataMapping;
            $data['paginator'] = $dataMapping['paginator'];
        }
        $data['jisshiId'] = $jisshiId;
        $data['examType'] = $examType;
        $data['year'] = $year;
        $data['page'] = $page;
        $data['numPerPage'] = $limit;
        $viewModel = new ViewModel($data);

        return $viewModel;
    }

    public function confirmStatusAction()
    {
        $request = $this->getRequest();
        if($request->isPost()){
            $data = $request->getPost();
            $result = $this->mappingIbaResultService->updateConfirmStatus($data['listIdOld']);
            if($result == HistoryConst::SAVE_TO_DATABASE_SUCCESS){
                return $this->getResponse()->setContent(Json::encode(HistoryConst::SAVE_TO_DATABASE_SUCCESS));
            }else{
                return $this->getResponse()->setContent(Json::encode(HistoryConst::SAVE_TO_DATABASE_FAIL));
            }
        }
        return $this->getResponse()->setContent(Json::encode(HistoryConst::SAVE_TO_DATABASE_FAIL));
    }
    
    public function ibaMappingResultAction()
    {
        $year = $this->params()->fromRoute('year', 0);
        $jisshiId = $this->params()->fromRoute('jisshiId', '');
        $examType = $this->params()->fromRoute('examType', '');
        $em = $this->getEntityManager();
        $applyIbaAutoMapping = $em->getRepository('Application\Entity\ApplyIBAOrg')
                ->findOneBy(array('organizationId' => $this->orgId, 'jisshiId' => $jisshiId, 'examType' => $examType));
        $flagNoNameKanna = $this->mappingIbaResultService->isNoNameKanna($year);
        if($applyIbaAutoMapping)
        {
            if($applyIbaAutoMapping->getStatusAutoImport() == HistoryConst::STATUS_AUTO_IMPORT_IBA_COMPLETE)
            {
                $applyIbaAutoMapping->setStatusAutoImport(HistoryConst::STATUS_AUTO_IMPORT_IBA_CONFIRMED);
                $em->flush();
                $em->clear();
            }
            else if ($this->getRequest()->getHeader('Referer') != false) {
                $redirectUrl = parse_url($this->getRequest()
                    ->getHeader('Referer')
                    ->getUri()); 
                if (!empty($redirectUrl) && (strpos($redirectUrl['path'], '/history/iba/confirm-exam-result')===false && strpos($redirectUrl['path'], '/history/eiken/exam-result')===false))
                    $flagNoNameKanna = FALSE;
                if (empty($redirectUrl)) $flagNoNameKanna = FALSE;
            }
        }
        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $checkSession = $this->getRequest()->getServer('HTTP_REFERER');
        if(strpos($checkSession, 'history/iba/iba-mapping-result') === false){
            PrivateSession::clear(HistoryConst::sessionSearchIBA);
        }
        
        $ibaApply = $this->mappingIbaResultService->getIBAApply($jisshiId, $examType);
        if($ibaApply === HistoryConst::CANNOT_FIND_DATA){
            return $this->redirect()->toUrl('/history/eiken/exam-result');
        }
        $testDate = $ibaApply->getTestDate();
        if($testDate){
            $testDate = $testDate->format('Y/m/d');
        }
        
        $searchParams = PrivateSession::getData(HistoryConst::sessionSearchIBA);
        $schoolYearId = '';
        $classId = '';
        $nameKana = '';
        $mappingStatus = '';
        $class = array();
        if ($searchParams) {
            $schoolYearId = $searchParams['schoolYearId'];
            $classId = $searchParams['classId'];
            $nameKana = $searchParams['nameKana'];
            $mappingStatus = $searchParams['mappingStatus'];
        }
        if(intval($year) < 2010 || !$jisshiId || !$examType){
            return $this->redirect()->toUrl('/history/eiken/exam-result');
        }
        $data = $this->mappingIbaResultService->getListMappingIBAResult($year, $jisshiId, $examType, $schoolYearId, $classId, $nameKana, $mappingStatus);
        $totalConfirm = $this->mappingIbaResultService->countMappingStatus($jisshiId, $examType);
        $arrayConfirm['unconfirm'] = isset($totalConfirm[HistoryConst::UNCONFIRM_STATUS]['total']) ? $totalConfirm[HistoryConst::UNCONFIRM_STATUS]['total'] : 0;
        $arrayConfirm['confirmed'] = isset($totalConfirm[HistoryConst::CONFIRMED_STATUS]['total']) ? $totalConfirm[HistoryConst::CONFIRMED_STATUS]['total'] : 0;
        $result = $data->getItems($offset, $limit);
        $translate = $this->serviceLocator->get('MVCTranslator');
        $configIBAEikenLvTotal = $this->serviceLocator->get('config')['IBAEikenLevelTotal'];
        
        //        update function for : #GNCCNCJDR5-761
        $ibaMasterData = $em->getRepository('Application\Entity\IbaScoreMasterData')->getListIbaScoreMasterData(HistoryConst::IBA_RESULT_TOTAL);
        foreach ($result as $key => $value){
            $result[$key]['birthday'] = empty($value['birthday']) ? '' : $value['birthday']->format(HistoryConst::FORMAT_DATE);
            $result[$key]['tempBirthday'] = empty($value['tempBirthday']) ? '' : $value['tempBirthday']->format(HistoryConst::FORMAT_DATE);
            //        update function for : #GNCCNCJDR5-761
            $result[$key]['eikenLevelTotal'] = MappingUtility::getKyuName($ibaMasterData, HistoryConst::IBA_RESULT_TOTAL, $value['testType'], $value['total']);
            $result[$key]['mappingStatus'] = ($value['mappingStatus'] == 0) 
                    ? $translate->translate('mappingStatus')[HistoryConst::UNCONFIRM_STATUS]
                        : $translate->translate('mappingStatus')[HistoryConst::CONFIRMED_STATUS] ;
        }
        
        $schoolYear = $this->mappingIbaResultService->getListSchoolYear();

        if ($schoolYearId) {
            $class = $this->mappingIbaResultService->getListClassBySchoolYear($schoolYearId, $year);
        }
        $token = md5($this->orgNo . $year . $jisshiId . $examType);

        $pupil = $this->mappingEikenResultService->checkPupilByYear($year);
        
        
        $warningMessage = sprintf($this->translate('SHOW_POPUP_MSG_PUPIL_NO_NAME_KANNA'),
                '<a id="nonameKana">ここ</a>');
        
        
        return new ViewModel(array(
            'pupilByYear' => ($pupil ? 1 : 0),
            'testDate' => $testDate, 
            'arrayConfirm' => $arrayConfirm, 
            'class' => $class, 
            'schoolYear' => $schoolYear, 
            'searchParams' => $searchParams,
            'year' => $year,
            'jisshiId' => $jisshiId,
            'examType' => $examType,
            'result' => $result,
            'page' => $page, 
            'data' => $data,
            'token' => $token,
            'mappingStatus' => $translate->translate('mappingStatus'),
            'messages' => $this->getMessages(),
            'flagNoNameKanna' => $flagNoNameKanna,
            'warningMessage' => $warningMessage
        ));
    } 
    
    public function searchAction()
    {
        $app = $this->getServiceLocator()->get('Application');
        $data = $app->getRequest();
        if ($data->isPost()) {
            $data = $data->getPost();
            PrivateSession::setData(HistoryConst::sessionSearchIBA, $data);

            return $this->redirect()->toUrl('/history/iba/iba-mapping-result/year/' . $data['year'] . '/jisshiId/' . $data['jisshiId']. '/examType/' . $data['examType']);
        }

        return $this->redirect()->toUrl('/history/eiken/exam-result');
    }
    
    public function clearAction()
    { 
        $data = PrivateSession::getData(HistoryConst::sessionSearchIBA);
        if ($data) {
            PrivateSession::clear(HistoryConst::sessionSearchIBA);
        }
        $year = $this->params()->fromRoute('year');
        $jisshiId = $this->params()->fromRoute('jisshiId');
        $examType = $this->params()->fromRoute('examType');

        return $this->redirect()->toUrl('/history/iba/iba-mapping-result/year/' . $year . '/jisshiId/' . $jisshiId. '/examType/' . $examType);
    }
    
    public function ajaxGetListClassAction()
    {
        $request = $this->getRequest();
        if($request->isPost()){
            $data = $request->getPost();
            $year = $data['year'];
            $schoolyear = $data['schoolYearId'];
            if(empty($year) && empty($schoolyear)){
                return $this->getResponse()->setContent(json_encode(array()));
            }
            $class = $this->mappingIbaResultService->getListClassBySchoolYear($schoolyear, $year);
            return $this->getResponse()->setContent(json_encode($class));
        }
    }

    public function ibaConfirmResultAction()
    {
        /**
         * @var $result \Application\Entity\IBATestResult
         */
        $id = $this->params()->fromRoute('id');
        $result = $this->mappingIbaResultService->getIBATestResult($id);
        if (empty($result)) {
            return $this->redirect()->toUrl('/history/eiken/exam-result');
        }
        $em = $this->getEntityManager();
        $currentPupil=array();
        if(!empty($result->getPupilId()))
        {
            $currentPupil = $this->eikenHistoryService->getDetailPupil($result->getPupilId(), $this->orgId);
        }
        $result->getBirthday = empty($result->getBirthday()) ? '' : $result->getBirthday()->format(HistoryConst::FORMAT_DATE);
        $result->getTempBirthday = empty($result->getTempBirthday()) ? '' : $result->getTempBirthday()->format(HistoryConst::FORMAT_DATE);
        $schoolYear = $this->mappingEikenResultService->getListSchoolYear();
        $token = md5($this->orgNo . $result->getYear() . $result->getJisshiId() . $result->getExamType());
//        update function for : #GNCCNCJDR5-761
        $ibaMasterData = $em->getRepository('Application\Entity\IbaScoreMasterData')->getListIbaScoreMasterData(HistoryConst::IBA_RESULT_TOTAL);
        $IBAEikenLevelTotalList = MappingUtility::getKyuName($ibaMasterData, HistoryConst::IBA_RESULT_TOTAL, $result->getTestType(), $result->getTotal());
        $result->getEikenLevelName = $IBAEikenLevelTotalList;
        return new ViewModel(array(
            'result' => $result,
            'currentPupil'=>$currentPupil,
            'messages' => $this->getMessages(),
            'schoolYear' => $schoolYear,
            'token' => $token,
            'id' => $id,
            'year' => $result->getYear(),
            'jisshiId' => $result->getJisshiId(),
            'examType' => $result->getExamType(),
            'IBAEikenLevelTotal' => isset($IBAEikenLevelTotalList) ? $IBAEikenLevelTotalList : ''
        ));
    }

    public function callSaveNextPupilAction()
    {
        $app = $this->getServiceLocator()->get('Application');
        $data = $app->getRequest()->getPost();
        $pupilId = $data['pupilId'];
        $ibaTestResultId = $data['ibaTestResultId'];
        $type = $data['type'];
        $typeMapping = $data['typeMapping'];
        if ($typeMapping == 1 && $pupilId == 0) {
            $this->mappingIbaResultService->deleteMapping($ibaTestResultId);
        }
        else {
            $this->mappingIbaResultService->confirmMapping($ibaTestResultId, $pupilId);
        }

        return $this->getResponse()->setContent(json_encode(array('type' => $type)));
    }
	/**
     * author AnhNT
     */
    public function showPopupComfirmAutoMappingAction(){
        $responseData = \Dantai\Utility\JsonModelHelper::getInstance();
        $responseData->setFail();
        if($this->roleId == 4 || $this->roleId == 5)
        {
            $translator = $this->getServiceLocator()->get('MVCTranslator');
            $em = $this->getEntityManager();
            $year = $this->dantaiService->getCurrentYear();
            $applyIBAList = $em->getRepository('Application\Entity\ApplyIBAOrg')->getApplyIBAOrgShowPopup($this->orgId,$year);
            $messageIBA = $translator->translate('SHOW_POPUP_MSG15').'<br>';

            if($applyIBAList){

                foreach ($applyIBAList as $applyIBA){
                    if(!$applyIBA->getSession() && $applyIBA->getStatusAutoImport() === HistoryConst::STATUS_AUTO_IMPORT_IBA_COMPLETE){
                        $testDate = $applyIBA->getTestDate();
                        $month = $testDate->format('n');
                        $day = $testDate->format('j');
                        $year = $testDate->format('Y');

                        if ($applyIBA->getExamType() == '01' || $applyIBA->getExamType() == '02')
                            $examType = HistoryConst::EXAM_TYPE_NAME_IBA;
                        else $examType = '';

                        $messageIBA .= '<br>' . $examType . '_' . $applyIBA->getSetName() . '_' . $year. '年' . $month . '月' . $day . '日';

                        $responseData->setSuccess();
                        $responseData->setMessages(array('msg' => $messageIBA));
                        $responseData->setData(array('year' => $year, 'moshikomiId' => $applyIBA->getMoshikomiId()));

                        $applyIBA->setSession(session_id());

                    }
                    else if($applyIBA->getStatusAutoImport() === HistoryConst::STATUS_AUTO_IMPORT_IBA_FAILURE){
                        $responseData->setSuccess();
                        $responseData->setMessages(array('msg' => $translator->translate('SHOW_POPUP_MSG59')));
                        $applyIBA->setStatusAutoImport(HistoryConst::STATUS_AUTO_IMPORT_NOT_RUN);
                    }
                }
                $em->flush();
//                $em->clear();
            }    
        }
        return $this->getResponse()->setContent($responseData->jsonSerialize());
    }	

    /*
     * Author ChungDV
     * export data to file Excel Of Iba Pupil Achievement
     */
     
    public function exportIbaPupilAchievementExcel($ibaPupilAchievement)
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $messages = array('noRecordExcel' => $translator->translate('MsgNoRecordExcel'));

        if (!$ibaPupilAchievement) {
            $this->flashMessenger()->addMessage($messages);

            return $this->redirect()->toRoute('history/default', array('controller' => 'iba',
                    'action' => 'pupil-achievement'
            ));
        }
               
        // get exam date from session
        $sessionExamDate = PrivateSession::getData('examdate');
        $examDate = date_format(date_create($sessionExamDate), \Dantai\Utility\DateHelper::DATE_FORMAT_EXPORT_EXCEL);

        $objFileName = new CharsetConverter();
        $fileName = sprintf($translator->translate('fileNameExcelIbaPupilAchievement'), $examDate, '.xlsx');
        $fileName = $objFileName->utf8ToShiftJis($fileName);
        
        $config = $this->getServiceLocator()->get('Config');
        $header = $config['headerExcelExport']['listOfIbaPupilAchievement'];
        $exportExcelMapper = new ExportExcelMapper($ibaPupilAchievement, $header, $this->getServiceLocator());
        $arrPupilAchievement = $exportExcelMapper->convertToExport();
        $objExcel = new PHPExcel();
        $objExcel->export($arrPupilAchievement, $fileName, 'default', 1);
        return $this->getResponse();
    }

    /* @var $objectOrg \Application\Entity\IBATestResult */
    public function exportibaAction()
    {
        $jisshiId = trim($this->params('jisshiId'));
        $examType = trim($this->params('examType'));
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $messages = array('noRecordExcel' => $translator->translate('MsgNoRecordExcel'));
        $em = $this->getEntityManager();
        $orgNo = '';
        $objectOrg = $em->getRepository('Application\Entity\Organization')->find($this->orgId);
        if ($objectOrg) {
            $orgNo = $objectOrg->getOrganizationNo();
        }

        if (!$jisshiId || !$examType) {
            $this->flashMessenger()->addMessage($messages);
            return $this->redirect()->toRoute('history/default', array('controller' => 'eiken',
                    'action' => 'exam-result'
                    )
            );
        }

        $orgNoIBA = '';
        $objectIBATestResult = $em->getRepository('Application\Entity\IBATestResult')->findOneBy(array('jisshiId' => $jisshiId, 'examType' => $examType, 'isDelete' => 0));
        if ($objectIBATestResult) {
            $orgNoIBA = $objectIBATestResult->getOrganizationNo();
        }

        if (empty($objectIBATestResult) || ($orgNo != $orgNoIBA)) {
            $this->flashMessenger()->addMessage($messages);
            return $this->redirect()->toRoute('history/default', array('controller' => 'eiken',
                    'action' => 'exam-result'
                    )
            );
        }

        $testResults = $this->getEntityManager()->getRepository('Application\Entity\ApplyIBAOrg')->findOneBy(array('jisshiId' => $jisshiId, 'examType' => $examType, 'isDelete' => 0));
        $testDate = ($testResults->getTestDate()) ? $testResults->getTestDate()->format('Ymd') : '';
        
        $filename = CharsetConverter::utf8ToShiftJis($translator->translate('export-IBA-filename') . $testDate . '.xlsx');

        $dataExport = $this->ibaHistoryService->populateDataToExportListIba($jisshiId, $examType);

        $objExcel = new PHPExcel();
        $objExcel->export($dataExport, $filename, 'IBA-result-template', 1);
        return $this->getResponse();
    }
    
    public function emptyNameKanaAction() {
        $page = $this->params()->fromRoute('page', 1);
        $year = $this->params()->fromQuery('year', $this->dantaiService->getCurrentYear());
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $paginator = $this->em->getRepository('Application\Entity\Pupil')->getListEmptyNameKana($this->orgId, $year);
        return new ViewModel(array(
            'listEmptyNameKana' => $paginator->getItems($offset, $limit, false),
            'paginator' => $paginator,
            'numPerPage' => $limit,
            'page' => $page,
            'year' => $year
        ));
    }
}
