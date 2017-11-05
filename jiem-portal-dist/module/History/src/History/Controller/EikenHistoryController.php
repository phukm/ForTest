<?php

namespace History\Controller;

use Aws\History;
use History\HistoryConst;
use History\Service\MappingEikenResultService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use History\Form\Eiken\ExamHistoryListForm;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use History\Service\ServiceInterface\EikenHistoryServiceInterface;
use Dantai\PrivateSession;
use Dantai\PublicSession;
use Zend\Session\Container;
use History\Helper\PaginationHelper;
use History\Form\SearchInquiryEikenForm;
use Dantai\Utility\PHPExcel;
use Dantai\Utility\CharsetConverter;
use Application\Entity\Organization;
use History\Service\ExportExcelMapper;

class EikenHistoryController extends AbstractActionController
{

    use \Application\Controller\ControllerAwareTrait;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var \History\Service\EikenHistoryService
     */
    protected $eikenHistoryService;
    protected $orgId;
    protected $orgNo;
    protected $year;
    protected $kai;
    protected $roleId;

    const MEMORY_LIMIT = '512M';
    /**
     * @var MappingEikenResultService
     */
    protected $mappingEikenResultService;

    public function __construct(DantaiServiceInterface $dantaiService, EikenHistoryServiceInterface $eikenHistoryService)
    {
        $this->eikenHistoryService = $eikenHistoryService;
        $this->dantaiService = $dantaiService;
        $user = PrivateSession::getData('userIdentity');
        $this->orgId = $user['organizationId'];
        $this->orgNo = $user['organizationNo'];
        $this->roleId = $user['roleId'];
        //TODO
        $this->year = $this->params()->fromPost('year', '');
        $this->kai = $this->params()->fromPost('kai', '');
    }

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
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
            'notCheckbox'                                        => $translate->translate('notCheckbox')
        );

        return json_encode($messages);
    }

    /**
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function examResultAction()
    {
        $viewModel = new ViewModel();
        $flashMessages = $this->flashMessenger()->getMessages();
        $messages = '';
        if ($flashMessages) {
            $messages = $flashMessages[0]['noRecordExcel'];
        }
        //Get info year kai default
        $pastKai = null;
        $pastYear = null;
        $listYear = array();
        $currentYear = null;
        $currentKai = null;
        $listKai = array();
        $infoYearKai = $this->eikenHistoryService->getInfoYearKai();
        if ($infoYearKai) {
            $pastKai = $infoYearKai['pastKai'];
            $pastYear = $infoYearKai['pastYear'];
            $listYear = $infoYearKai['listYear'];
            $currentYear = $infoYearKai['currentYear'];
            $currentKai = $infoYearKai['currentKai'];
            $listKai = $infoYearKai['listKai'];
        }
        $searchCriteria = $this->dantaiService->getSearchCriteria($this->getEvent(), array(
            'examType' => '',
            'year' => '',
            'kai' => '',
            'startDate' => '',
            'endDate' => '',
            'sortKey' => '',
            'sortOrder' => 'asc',
            'searchVisible' => 0,
            'token' => ''
        ));
        $sortOrder = !empty($searchCriteria['sortOrder']) ? $searchCriteria['sortOrder'] : 'asc';
        $sortKey = !empty($searchCriteria['sortKey']) ? $searchCriteria['sortKey'] : '';
        if ($this->isPost() && $searchCriteria['token']) {
            return $this->redirect()->toUrl('/history/eiken/exam-result/search/' . $searchCriteria['token']);
        }
        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $em = $this->getEntityManager();

        //Get info from session
        $user = PrivateSession::getData('userIdentity');
        $organizationId = $user['organizationId'];
        $paginator = $em->getRepository('Application\Entity\EikenSchedule')->getListEikenExamResult($organizationId, $searchCriteria);

        $dataEiken = array();
        $dataEikenConfirm = $em->getRepository('Application\Entity\EikenTestResult')->getConfirmStatus($this->orgNo);
        foreach ($dataEikenConfirm as $key => $value){
            $dataEiken[$value['totalMap']] = $value;
        }

        $dataIba = array();
        $dataIBAConfirm = $em->getRepository('Application\Entity\IBATestResult')->getConfirmStatus($this->orgNo);
        foreach ($dataIBAConfirm as $key => $value){
            $dataIba[$value['totalMap']] = $value;
        }
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $viewModel->setVariables(array(
            'dataEiken'   => $dataEiken,
            'dataIba'     => $dataIba,
            'noRecord'    => $translator->translate('MSG13'),
            'msg15'       => $translator->translate('MSG15New'),
            'msg16'       => $translator->translate('MSG16New'),
            'msg24'       => $translator->translate('MSG24'),
            'data'        => (!empty($searchCriteria)) ? $searchCriteria : '',
            'listYear'    => $listYear,
            'currentYear' => $currentYear,
            'currentKai'  => $currentKai,
            'listKai'     => $listKai,
            'listExam'    => $paginator->getItems(($page - 1) * $limit, $limit),
            'page'        => $page,
            'paginator'   => $paginator,
            'param'       => isset($searchCriteria['token']) ? $searchCriteria['token'] : '',
            'pageLimit'   => $limit,
            'sortOrder'   => $sortOrder,
            'sortKey'     => $sortKey,
            'searchVisible' => isset($searchCriteria['searchVisible']) ? $searchCriteria['searchVisible'] : 0,
            'pastYear' => $infoYearKai['pastYear'],
            'pastKai' => $infoYearKai['pastKai'],
            'noRecordExcel' => $messages,
            'roleLimit' => PublicSession::isDisableDownloadButtonRole() || PublicSession::isViewerRole(),
            'translator' => $this->getTranslation()
        ));

        return $viewModel;
    }

    public function checkKekkaValueAction()
    {
        $year = $this->params()->fromQuery('year');
        $kai = $this->params()->fromQuery('kai');
        $examId = $this->params()->fromQuery('examId');
        //set yearNo, kaiNo into sesstion
        PrivateSession::setData('yearNo', $year);
        PrivateSession::setData('kaiNo', $kai);
        PrivateSession::setData('examId', $examId);
        //Get info from session
        $user = PrivateSession::getData('userIdentity');
        $organizationId = $user['organizationId'];
        $em = $this->getEntityManager();
        $organizationNo = $em->getRepository('Application\Entity\Organization')->findOneBy(array(
            'id' => $organizationId));
        // call uketsuke api $organizationNo,$year ,$term
        $result = $this->mappingEikenResultService->getEikenExamResult($organizationNo->getOrganizationNo(), $year, $kai);
        if (isset($result->kekka)) {
            $kekka = $result->kekka;
            $this->mappingEikenResultService->resetExamStatus($examId);
        } else {
            if (count($result) == 1 && $result->eikenArray[0]->eikenid == '') {
                $kekka = '02';
            } else {
                $kekka = $result->eikenArray[0]->kekka;
            }
        }
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($kekka));
        return $response;
    }

    public function confirmExamResultAction()
    {

        $page = $this->params('page') != null ? $this->params('page') : 1;
        //Get info from session
        $organizationId = $this->orgId;
        $em = $this->getEntityManager();
        $organizationNo = $em->getRepository('Application\Entity\Organization')->findOneBy(array(
            'id' => $organizationId));
        $year = PrivateSession::getData('yearNo');
        $kai = PrivateSession::getData('kaiNo');
        $examId = PrivateSession::getData('examId');
        $result = $this->mappingEikenResultService->getEikenExamResult($organizationNo->getOrganizationNo(), $year, $kai);
        $noResult = false;
        if (count($result) == 1 && $result->eikenArray[0]->eikenid == '') {
            $noResult = true;
        }
        if (!isset($result->kekka)) {
            //Sort array with 3 condition
            $resultSort = $result->eikenArray;
            usort($resultSort, function ($a, $b) {
                $r = strcmp(trim($a->gakkokbn), trim($b->gakkokbn));
                if ($r !== 0)
                    return $r;
                $r = strcmp(trim($a->gakunenno), trim($b->gakunenno));
                if ($r !== 0)
                    return $r;
                $r = strcmp(trim($a->kumi), trim($b->kumi));
                if ($r !== 0)
                    return $r;

                return strcmp(trim($a->simei), trim($b->simei));
            });
            $config = $this->getServiceLocator()->get('config');
            $listMappingOrgClass = $config['School_Code'];
            $listMappingLevel = $config['MappingLevel'];

            $translator = $this->getServiceLocator()->get('MVCTranslator');
            $jsMessages = array(
                'SHOW_POPUP_MSG_CONFIRM_EXAM_RESULT' => $translator->translate('SHOW_POPUP_MSG_CONFIRM_EXAM_RESULT'),
            );
            $viewModel = new ViewModel();
            $viewModel->setVariables(array(
                'data' => $resultSort,
                'listMappingOrgClass' => $listMappingOrgClass,
                'listMappingLevel' => $listMappingLevel,
                'page' => $page,
                'yearKaiValue' => ($year != '') ? json_encode(array('year' => $year, 'kai' => $kai)) : '',
                'statusMapping' => $this->eikenHistoryService->getStatusMappingByExamId($examId),
                'examId' => PrivateSession::getData('examId'),
                'yearNo' => $year,
                'kaiNo' => $kai,
                'noRecord' => $translator->translate('MSG13'),
                'noResult' => $noResult,
                'jsMessages' => json_encode($jsMessages)
            ));

            //         // Dynamic breadcrumb by kai and year
            //         $navigation = $this->getServiceLocator()->get('navigation');
            //         $page = $navigation->findBy('id', 'confirm-exam-result');
            //         $page->setLabel($year.'年度第'.$kai.'回');
            return $viewModel;
        }
        return $this->redirect()->toRoute('history/default', array('controller' => 'eiken', 'action' => 'exam-result'));
    }

    /**
     *  DucNA17 UC10
     * @param int $year
     * @param int $kai
     * @return \Zend\View\Model\ViewModel
     */
    public function mappingResultAction()
    {
        $data = array();
        $year = $this->params()->fromRoute('year', date('Y'));
        $kai = $this->params()->fromRoute('kai', 0);
        $data['page'] = $this->params()->fromRoute('page', 1);

        if (empty($year) || empty($kai))
            return $this->redirect()->toUrl('/error/index');

        //$eikenTestResult = $this->eikenHistoryService->getEikenTestResult($year, $kai,$this->orgNo);//check process mapping

        $dataMapping = $this->eikenHistoryService->getDataMapping($this->orgNo, $year, $kai, $data['page']);
        $listPupil = $this->eikenHistoryService->getListPupil($this->orgId, $year);

        $config = $this->getServiceLocator()->get('config');
        $data['listMappingOrgClass'] = $config['OrganizationClass'];
        $data['listMappingLevel'] = $config['MappingLevel'];
        if (!empty($dataMapping)) {
            //if (!empty($eikenTestResult)) {//check process mapping
            $data['jsonListPupil'] = Json::encode($listPupil);
            // convert all pupil has key is 'birthdar'.'nameKanji'
            $pupilManager = $this->eikenHistoryService->convertPupilManager($listPupil);

            //$mappingData = $this->eikenHistoryService->mappingData($eikenTestResult, $pupilManager);//check process mapping

            $keepSession = PrivateSession::getData('tempMappingEiken');
            if (!empty($keepSession)) {
                $data['keepSession'] = $keepSession;
            }
            //update status mapping
            //$this->eikenHistoryService->updateMappingStatus($mappingData, $pupilManager);//check process mapping

            $data['listPupil'] = $pupilManager;
            $data['mappingData'] = $dataMapping;
            $viewModel = new ViewModel($data);
            return $viewModel;
        }

        return $this->redirect()->toUrl('/error/index');
    }

    public function pupilAchievementAction()
    {
        $yearNo = PrivateSession::getData('yearNo');
        $kaiNo = PrivateSession::getData('kaiNo');
        $searchCriteria = $this->dantaiService->getSearchCriteria($this->getEvent(), array(
            'year' => $yearNo,
            'kai' => $kaiNo,
            'orgSchoolYear' => $this->params()->fromPost('orgSchoolYear'),
            'classj' => $this->params()->fromPost('classj'),
            'name' => $this->params()->fromPost('name'),
            'sortOrder' => '',
            'sortKey' => '',
            'searchVisible' => 0,
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

        if ($this->isPost() && $searchCriteria['token']) {
            return $this->redirect()->toUrl('/history/eiken/pupil-achievement/search/' . $searchCriteria['token']);
        }

        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;

        // messages when export excel not data
        $flashMessages = $this->flashMessenger()->getMessages();
        $messages = '';
        if ($flashMessages) {
            $messages = $flashMessages[0]['noRecordExcel'];
        }
        $pupilAchievement = $this->eikenHistoryService->getListInquiryEiken($sortOrder, $sortKey, $searchVisible, $page, $limit, $offset, $this->isPost(), $searchCriteria, $routeMatch, $this->getRequest(), $this->params(), $this->flashMessenger(), $this->dantaiService, $this->redirect(), $messages);

        // export excel
        $isExportExcel = $this->params()->fromRoute('isExportExcel');
        if ($isExportExcel) {
            return $this->exportPupilAchievementExcel($pupilAchievement['pupilAchievementList']);
        }

        return $pupilAchievement;
    }

    public function personalAchievementAction()
    {
        $id = $this->params()->fromRoute('id', '');
        // Get eiken test result.
        $items = $this->eikenHistoryService->getPersonalAchievementEiken($id, $this->orgNo);
        if ($items) {

            $isUsingOldTemplate = $items->getYear() < 2016;

            $translator = $this->getServiceLocator()->get('MVCTranslator');
            $Skill2 = $translator->translate('Skill2');
            $Description2 = $translator->translate('Description2');
            $PupilScoreTotalScore = $translator->translate('PupilScoreTotalScore');
            $TitleTableEiken = $translator->translate('TitleTableEiken');
            $Skill = $translator->translate('SkillEng');
            $Purpose = $items->getYear() == 2016 ? $translator->translate('Purpose') : $translator->translate('Purpose2017');

            if($isUsingOldTemplate) {
                $Skill = $translator->translate('Skill');
                $Purpose = $translator->translate('Purpose2015');
            }

            $items->getSkill2 = $Skill2[3];
            if ($items->getEikenLevelId() == 1) {
                $items->getSkill2 = $Skill2[1];
            }
            if ($items->getEikenLevelId() == 2) {
                $items->getSkill2 = $Skill2[2];
            }
            if ($items->getEikenLevelId() == 6 || $items->getEikenLevelId() == 7) {
                $items->getSkill2 = $Skill2[4];
            }

            $items->getDescription2 = $Description2;
            $items->getPupilScoreTotalScore = $PupilScoreTotalScore;
            $items->getSkill = $Skill;
            $items->getPurpose = $Purpose;
            $items->getTitleTableEiken = $TitleTableEiken;
            $items->titleEiken = sprintf($translator->translate('TitleEiken'), $items->getYear(), $items->getKai());

            $this->setEikenDetailBreadCumbs($items->titleEiken);

            $items->backAction = $this->getRequest()->getServer('HTTP_REFERER');

            if($isUsingOldTemplate){
                $viewModel = new ViewModel(array(
                                               'items' => $items,
                                           ));
                $viewModel->setTemplate('history/eiken-history/level2015/personal-achievement.phtml');
            }else {
                $config = $this->getServiceLocator()->get('config');
                $limProgressBar = HistoryConst::LIM_PROGES_BAR_R1_12367;
                $limProgressBarRound2 = HistoryConst::LIM_PROGES_BAR_R2_345367;
                if ($items->getEikenLevelId() == 4 || $items->getEikenLevelId() == 5) {
                    $limProgressBar = HistoryConst::LIM_PROGES_BAR_R1_45;
                }
                if ($items->getEikenLevelId() == 1 || $items->getEikenLevelId() == 2) {
                    $limProgressBarRound2 = HistoryConst::LIM_PROGES_BAR_R2_12;
                }
                $isInLand = HistoryConst::HAS_INLAND;
                if($items->getExamLocation() == HistoryConst::CODE_OUT_LAND){
                    $isInLand = HistoryConst::HAS_OUT_INLAND;
                }
                $cSEScoreByKyu = $this->eikenHistoryService->getEikenMasterDataByKyu($items->getYear(), $items->getKai(),$isInLand,$items->getExecutionDayOfTheWeek() ? $items->getExecutionDayOfTheWeek() : 3,$items->getEikenLevelId());
                $cSEScoreWidthId = $this->eikenHistoryService->getEikenMasterData($items->getYear(), $items->getKai(),$isInLand,$items->getExecutionDayOfTheWeek() ? $items->getExecutionDayOfTheWeek() : 3);
                if($items->getYear() >= 2017){
                    $limProgressBar = HistoryConst::LIM_PROGES_BAR_R1_12367;
                    $totalScoreBySkill = (intval($items->getEikenlevelId() < 6 ? $items->getFirstSoreThreeSkillRLW() : $items->getFirsrtScoreTwoSkillRL()));
                }else{
                    $totalScoreBySkill = (intval($items->getEikenlevelId() < 4 ? $items->getFirstSoreThreeSkillRLW() : $items->getFirsrtScoreTwoSkillRL()));
                }
                $progressBar = $this->eikenHistoryService->getListPercentPB($cSEScoreWidthId,$cSEScoreByKyu,$limProgressBar,$totalScoreBySkill);
                $isInLand = HistoryConst::HAS_INLAND;
                if($items->getSecondExaminationAreas() == HistoryConst::CODE_OUT_LAND){
                    $isInLand = HistoryConst::HAS_OUT_INLAND;
                }
                $cSEScoreByKyuR2 = $this->eikenHistoryService->getEikenMasterDataByKyu($items->getYear(), $items->getKai(),$isInLand,$items->getSecondExecutionDayOfTheWeek() ? $items->getSecondExecutionDayOfTheWeek() : 3,$items->getEikenLevelId());
                $cSEScoreWidthId = $this->eikenHistoryService->getEikenMasterData($items->getYear(), $items->getKai(),$isInLand,$items->getSecondExecutionDayOfTheWeek() ? $items->getSecondExecutionDayOfTheWeek() : 3);
                $progressBarRound2 = $this->eikenHistoryService->getListPercentPBR2($cSEScoreWidthId,$cSEScoreByKyuR2,$limProgressBarRound2,$items->getCSEScoreSpeaking());


                $viewModel = new ViewModel(array(
                   'items'           => $items,
                   'limProgressBar'  => $limProgressBar,
                   'progressBar'     => $progressBar,
                   'cSEScoreByKyu'   => $cSEScoreByKyu,
                   'cSEScoreByKyuR2'   => $cSEScoreByKyuR2,
                   'limProgressBarRound2'  => $limProgressBarRound2,
                   'progressBarRound2'     => $progressBarRound2
                ));
                if($items->getYear() >= 2017){
                    $viewModel->setTemplate('history/eiken-history/level2017/personal-achievement.phtml');
                } else if($items->getYear() == 2016){
                    $viewModel->setTemplate('history/eiken-history/level2016/personal-achievement.phtml');
                } else {
                    $viewModel->setTemplate('history/eiken-history/level2015/personal-achievement.phtml');
                }
            }
            return $viewModel;
        }

        return $this->redirect()->toUrl('/error/index');
    }

    public function getDataByYearAndKaiAction()
    {
        return $this->eikenHistoryService->setSessionKaiAndYear($this->params(), $this->redirect());
    }

    public function getKaiAction()
    {
        $yearId = $this->params()->fromQuery('year');
        //call list kai by year
        $listkai = $this->eikenHistoryService->getListKai($yearId);
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($listkai));

        return $response;
    }

    /**
     * @author NamTH7
     * @return \Zend\View\Model\ViewModel
     */
    public function examHistoryListAction()
    {
        $em = $this->getEntityManager();
        $organizationId = $this->orgId;
        $organizationNo = $this->orgNo;
        $sortOrder = !empty($searchCriteria['sortOrder']) ? $searchCriteria['sortOrder'] : 'asc';
        $sortKey = !empty($searchCriteria['sortKey']) ? $searchCriteria['sortKey'] : '';
        $searchCriteria = $this->dantaiService->getSearchCriteria($this->getEvent(), array(
            'year' => '',
            'orgSchoolYear' => '',
            'classj' => '',
            'name' => '',
            'searchName' => '',
            'searchVisible' => 0,
            'token' => ''
        ));
        if ($this->isPost() && $searchCriteria['token']) {
            return $this->redirect()->toUrl('/history/eiken/exam-history-list/search/' . $searchCriteria['token']);
        }
        $searchYear = $searchCriteria['year'];

        $viewModel = new ViewModel();
        $form = new ExamHistoryListForm();
        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;

        $listclass = $this->eikenHistoryService->getListClass($organizationNo,HistoryConst::GROUP_BY_CLASS_NAME);
        $yearschool = $this->eikenHistoryService->getListClass($organizationNo,HistoryConst::GROUP_BY_SCHOOL_YEAR_NAME);

        $form->get("year")
            ->setValueOptions($this->eikenHistoryService->year())
            ->setAttributes(array(
                'value' => $searchCriteria['year'],
                'selected' => true,
                'escape' => false
        ));
        $form->get("orgSchoolYear")
            ->setValueOptions($yearschool);
        $form->get("name")->setAttributes(array(
            'value' => $searchCriteria['name']
        ));
        if($listclass){
            $form->get("classj")
            ->setValueOptions($listclass);
        }

        //Get info year kai default
        $pastKai = null;
        $pastYear = null;
        $listYear = array();
        $currentYear = null;
        $currentKai = null;
        $listKai = array();
        $infoYearKai = $this->eikenHistoryService->getInfoYearKai();
        if ($infoYearKai) {
            $pastKai = $infoYearKai['pastKai'];
            $pastYear = $infoYearKai['pastYear'];
            $listYear = $infoYearKai['listYear'];
            $currentYear = $infoYearKai['currentYear'];
            $currentKai = $infoYearKai['currentKai'];
            $listKai = $infoYearKai['listKai'];
        }

        $syArray = array();
        $listSchoolYear = $this->eikenHistoryService->getOrgSchoolYear($organizationId);
        if ($listSchoolYear) {
            foreach ($listSchoolYear as $k => $value) {
                $syArray[$value['id']] = $value['displayName'];
            }
        }

        $paginator = $em->getRepository('Application\Entity\EikenTestResult')->getListEikenExamHistory($organizationNo, $searchCriteria, $pastYear);

        // messages when export excel not data
        $flashMessages = $this->flashMessenger()->getMessages();
        $messages = '';
        if ($flashMessages) {
            $messages = $flashMessages[0]['noRecordExcel'];
        }

        // export excel
        $isExportExcel = $this->params()->fromRoute('isExportExcel');
        if ($isExportExcel) {
            return $this->exportExamHistoryListExcel($paginator->getAllItems(), $searchYear);
        }

        $data = $paginator->getItems($offset, $limit);
        $level = $this->eikenHistoryService->setEikenIBALevel($data, $searchYear);

        $classArray = array();
        $listClass = $this->eikenHistoryService->getOrgClass($organizationId);
        if ($listClass) {
            foreach ($listClass as $k => $value) {
                $classArray[$value['id']] = $value['className'];
            }
        }

        $config = $this->getServiceLocator()->get('config');
        $listMapppingEikenLevel = $config['MappingLevel'];
        $listMapppingIBALevel = $config['MappingIBALevelTotal'];
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $viewModel->setVariables(array(
            'noRecord' => $translator->translate('MSG13'),
            'listYear' => $listYear,
            'currentYear' => $currentYear,
            'pastYear' => $pastYear,
            'listSchoolYear' => $syArray,
            'listClass' => $classArray,
            'listMapppingEikenLevel' => $listMapppingEikenLevel,
            'listMapppingIBALevel' => $listMapppingIBALevel,
            'data' => $searchCriteria,
            'listHistory' => $data,
            'page' => $page,
            'paginator' => $paginator,
            'param' => isset($searchCriteria['token']) ? $searchCriteria['token'] : '',
            'pageLimit' => $limit,
            'sortOrder' => $sortOrder,
            'sortKey' => $sortKey,
            'searchVisible' => isset($searchCriteria['searchVisible']) ? $searchCriteria['searchVisible'] : 0,
            'eikenLevelList' => isset($level['eiken']) ? $level['eiken'] : '',
            'ibaLevelList' => isset($level['iba']) ? $level['iba'] : '',
            'noRecordExcel' => $messages,
            'form' => $form,
            'roleLimit' => PublicSession::isDisableDownloadButtonRole(),
        ));
        return $viewModel;
    }

    public function eikenHistoryPupilAction()
    {
        $routeMatch = $this->getEvent()
                ->getRouteMatch()
                ->getParam('controller') . '_' . $this->getEvent()
                ->getRouteMatch()
                ->getParam('action');

        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $messages = $this->flashMessenger()->getMessages();
        if ($messages) {
            $messages = $messages[0]['MsgNoRecordExcel'];
        }

        $searchCriteria = $this->dantaiService->getSearchCriteria($this->getEvent(), array(
            'type' => PrivateSession::getData('eikenType'),
            'id' => PrivateSession::getData('eikenId'),
            'pupilId' => PrivateSession::getData('eikenPupilId'),
            'schoolYear' => PrivateSession::getData('eikenSchoolYear'),
            'className' => PrivateSession::getData('eikenClassName'),
            'pupilNumber' => PrivateSession::getData('eikenPupilNumber'),
            'name' => PrivateSession::getData('eikenName')
        ));
        return $this->eikenHistoryService->getHistoryPupilEiken($this->redirect(), $this->params(), $page, $limit, $offset, $routeMatch, $searchCriteria, $messages);
    }

    public function exportEikenHistoryPupilAction()
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $searchCriteria = $this->dantaiService->getSearchCriteria($this->getEvent(), array(
            'type' => PrivateSession::getData('eikenType'),
            'id' => PrivateSession::getData('eikenId'),
            'pupilId' => PrivateSession::getData('eikenPupilId'),
            'schoolYear' => PrivateSession::getData('eikenSchoolYear'),
            'className' => PrivateSession::getData('eikenClassName'),
            'pupilNumber' => PrivateSession::getData('eikenPupilNumber'),
            'name' => PrivateSession::getData('eikenName')
        ));

        $dataExport = $this->eikenHistoryService->getHistoryPupilEikenExport($searchCriteria);
        if (!$dataExport) {
            $messages = array(
                'MsgNoRecordExcel' => $translator->translate('MsgNoRecordExcel')
            );
            $this->flashMessenger()->addMessage($messages);
            return $this->redirect()->toRoute('history/default', array(
                    'controller' => 'eiken',
                    'action' => 'eiken-history-pupil'
                    )
            );
        }
        $objFileName = new CharsetConverter();
        $filename = sprintf($translator->translate('Tl5'),$searchCriteria['schoolYear'],$searchCriteria['className'],$searchCriteria['pupilNumber'],$searchCriteria['name']);
        $filename = $objFileName->utf8ToShiftJis($filename);
        $template = 'Eiken-history-pupil-template';
        $startIndex = 1;
        $objExcel = new PHPExcel();
        $objExcel->export($dataExport, $filename, $template, $startIndex);
        return $this->getResponse();

    }

    public function getDataEikenAction()
    {
        $data = $this->params()->fromQuery();

        if ($data) {
            $type = $data['type'];
            $id = $data['id'];
            $pupilId = $data['pupilId'];
            $schoolyear = $data['schoolYear'];
            $class = $data['className'];
            $number = $data['pupilNumber'];
            $name = $data['name'];
        }
        PrivateSession::setData('eikenType', $type);
        PrivateSession::setData('eikenId', $id);
        PrivateSession::setData('eikenPupilId', $pupilId);
        PrivateSession::setData('eikenSchoolYear', $schoolyear);
        PrivateSession::setData('eikenClassName', $class);
        PrivateSession::setData('eikenPupilNumber', $number);
        PrivateSession::setData('eikenName', $name);

        return $this->redirect()->toRoute('history/default', array(
                'controller' => 'eiken',
                'action' => 'eiken-history-pupil'
        ));
    }

    public function saveEikenExamResultAction()
    {
        //Get info from session
        $year = PrivateSession::getData('yearNo');
        $kai = PrivateSession::getData('kaiNo');
        $this->mappingEikenResultService->setDataToSave($this->orgNo, $this->orgId, $year, $kai);
        return $this->redirect()->toRoute(null, array(
                'module' => 'history',
                'controller' => 'eiken',
                'action' => 'exam-result'
        ));
    }

    public function saveEikenExamResultOnlyAction()
    {
        //Get info from session
        $year = PrivateSession::getData('yearNo');
        $kai = PrivateSession::getData('kaiNo');
        $this->eikenHistoryService->setDataToSave($this->orgNo, $this->orgId, $year, $kai);

        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode('OK'));

        return $response;
    }

    public function mappingEikenExamResultAction()
    {
        //Get info from session
        $user = PrivateSession::getData('userIdentity');
        $organizationId = $user['organizationId'];
        $em = $this->getEntityManager();
        $organizationNo = $em->getRepository('Application\Entity\Organization')->findOneBy(array(
            'id' => $organizationId));
        $year = PrivateSession::getData('yearNo');
        $kai = PrivateSession::getData('kaiNo');
        $this->eikenHistoryService->setDataToSave($organizationNo->getOrganizationNo(), $organizationId, $year, $kai);
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode('OK'));

        return $response;
    }

    //DucNA17
    public function saveMapingEikenAction()
    {
        $keepSessionData = PrivateSession::getData('tempMappingEiken');
        //save and update
        $this->eikenHistoryService->saveMappingEiken($keepSessionData);
        PrivateSession::clear('tempMappingEiken');
        return $this->getResponse()->setContent(true);
    }

    //DucNA17
    public function findEikenAction()
    {
        $eikenId = (int) $this->params()->fromPost('eikenId', '');
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
        if ($eikenId > 0 && $pupilId > 0) {
            $data = PrivateSession::getData('tempMappingEiken');
            $data[$eikenId] = array('pupilId' => $pupilId,
                'birthday' => $birthday,
                'nameKanji' => $nameKanji,
                'nameKana' => $nameKana,
                'number' => $number,
                'orgSchoolYearId' => $orgSchoolYearId,
                'orgSchoolYearName' => $orgSchoolYearName,
                'className' => $className,
                'classId' => $classId);

            PrivateSession::setData('tempMappingEiken', $data);
            //update temp EikenTestResult and insert temp EikenScore
            $this->eikenHistoryService->updateEikenTestResult($eikenId, $pupilId);

            $result = true;
        }

        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($result));

        return $response;
    }

    /**
     * TaiVH support UC10
     * DucNA change
     * @return \Zend\View\Model\ViewModel
     */
    public function mappingErrorAction()
    {
        // hand code
        $year = $this->params()->fromRoute('year', date('Y'));
        $kai = $this->params()->fromRoute('kai', 1);
        $page = $this->params()->fromRoute('page', 1);
        $orgNo = $this->orgNo;
        $mappingStatus = 0; // ---- Get MappingStatus = 0|2
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $data = array();
        //$results =  $this->eikenHistoryService->getEikenTestResultByOrgNo($orgNo, $year, $kai, $mappingStatus, $offset, $limit);

        $dataMapping = $this->eikenHistoryService->getDataMapping($this->orgNo, $year, $kai, $page, 0);
        //$eikenTestResult = $results['data'];
        if (!empty($dataMapping)) {
            $listPupil = $this->eikenHistoryService->getListPupil($this->orgId, $year);
            $data['jsonListPupil'] = Json::encode($listPupil);
            $pupilManager = $this->eikenHistoryService->convertPupilManager($listPupil);


            $keepSession = PrivateSession::getData('tempMappingEiken');
            if (!empty($keepSession)) {
                $data['keepSession'] = $keepSession;
            }
            $data['listPupil'] = $pupilManager;
            $data['mappingData'] = $dataMapping;
            $data['paginator'] = $dataMapping['paginator'];

            $config = $this->getServiceLocator()->get('config');
            $data['listMappingOrgClass'] = $config['School_Code'];
            $data['listMappingLevel'] = $config['MappingLevel'];
        }

        $data['year'] = $year;
        $data['kai'] = $kai;
        $data['page'] = $page;
        $data['numPerPage'] = $limit;
        $viewModel = new ViewModel($data);
        return $viewModel;
    }

    /**
     * @author TaiVH - DucNA
     * UC10 - List Success mapping items
     * @return \Zend\View\Model\ViewModel
     */
    public function mappingSuccessAction()
    {
        // hand code
        $year = $this->params()->fromRoute('year', date('Y'));
        $kai = $this->params()->fromRoute('kai', 0);
        $page = $this->params()->fromRoute('page', 1);
        $orgNo = $this->orgNo;
        $mappingStatus = 1;
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $data = array();
        //$results =  $this->eikenHistoryService->getEikenTestResultByOrgNo($orgNo, $year, $kai, $mappingStatus, $offset, $limit);

        $dataMapping = $this->eikenHistoryService->getDataMapping($this->orgNo, $year, $kai, $page, 1);

        if (!empty($dataMapping)) {
            $listPupil = $this->eikenHistoryService->getListPupil($this->orgId, $year);

            $data['jsonListPupil'] = Json::encode($listPupil);
            $pupilManager = $this->eikenHistoryService->convertPupilManager($listPupil);


            $keepSession = PrivateSession::getData('tempMappingEiken');
            if (!empty($keepSession)) {
                $data['keepSession'] = $keepSession;
            }
            $data['listPupil'] = $pupilManager;
            $data['mappingData'] = $dataMapping;
            $data['paginator'] = $dataMapping['paginator'];

            $config = $this->getServiceLocator()->get('config');
            $data['listMappingOrgClass'] = $config['School_Code'];
            $data['listMappingLevel'] = $config['MappingLevel'];
        }

        $data['year'] = $year;
        $data['kai'] = $kai;
        $data['page'] = $page;
        $data['numPerPage'] = $limit;

        $viewModel = new ViewModel($data);
        return $viewModel;
    }

    /** DucNA - DuongTD
     * Mapping data via ajax if needed and show progress bar
     * @return \Zend\View\Model\ViewModel
     */
    public function mappingDataAction()
    {
        $year = $this->params()->fromPost('yearNo', date('Y'));
        $kai = $this->params()->fromPost('kaiNo', 0);
        $applyEikenId = $this->params()->fromPost('applyEikenId', 0);
        $hasImportData = $this->params()->fromPost('hasImportData', 0);
        PrivateSession::setData('applyEikenId', $applyEikenId);
        if($hasImportData == 1){
            $this->mappingEikenResultService->setDataToSave($this->orgNo, $this->orgId, $year, $kai);
        }
        $result = $this->mappingEikenResultService->mappingDataEikenResult($year, $kai);
        $response = array(
            'mappingStatus' => $result['status']
        );
        return $this->getResponse()->setContent(Json::encode($response));
    }

    public function confirmStatusAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();

            $pupil = $this->mappingEikenResultService->checkPupilByYear((int)$data['year']);
            if (!$pupil) {
                return $this->getResponse()->setContent(Json::encode(HistoryConst::SAVE_TO_DATABASE_FAIL));
            }
            $result = $this->mappingEikenResultService->updateConfirmStatus($data['listIdOld']);
            if ($result == HistoryConst::SAVE_TO_DATABASE_SUCCESS) {
                return $this->getResponse()->setContent(Json::encode(HistoryConst::SAVE_TO_DATABASE_SUCCESS));
            }

            return $this->getResponse()->setContent(Json::encode(HistoryConst::SAVE_TO_DATABASE_FAIL));
        }

        return $this->getResponse()->setContent(Json::encode(HistoryConst::SAVE_TO_DATABASE_FAIL));
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    /**
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function loadSchoolYearAction()
    {
        $yearId = $this->params()->fromQuery('year');
        $orgNo = $this->orgNo;
        $key['searchYear'] = (int) $yearId;
        $pastYear = '';
        $listSchoolYear = $this->removeDuplicateSchoolYearName($this->eikenHistoryService->getListSchoolYear($orgNo, $key, $pastYear));
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($listSchoolYear));
        return $response;
    }

    public function loadClassAction()
    {
        $yearId = $this->params()->fromQuery('year');
        $schoolYear = $this->params()->fromQuery('schoolYear');
        $orgNo = $this->orgNo;
        $key['searchYear'] = (int) $yearId;
        $key['schoolYearName'] = $schoolYear;
        $pastYear = '';
        $listClass = $this->removeDuplicateClassName($this->eikenHistoryService->getListSchoolYear($orgNo, $key, $pastYear));
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($listClass));
        return $response;
    }

    //ducna17
    public function clearSessionAction()
    {
        $listItemSuccess = PrivateSession::clear('tempMappingEiken');
        $keepSession = PrivateSession::clear('listItemSuccess');
        return $this->getResponse()->setContent(true);
    }

    protected function setEikenDetailBreadCumbs($TitleEiken)
    {
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('id', 'personal_achievement');
        $page->setLabel($TitleEiken);
    }

    private function removeDuplicateSchoolYearName($listSchoolYear)
    {
        $listSchool = array();
        if (count($listSchoolYear) > 0) {
            foreach ($listSchoolYear as $school) {
                if ($school['schoolYearName'] != '') {
                    array_push($listSchool, $school['schoolYearName']);
                }
            }
            return array_unique($listSchool);
        }

        return $listSchool;
    }

    private function removeDuplicateClassName($listClassName)
    {
        $listClass = array();
        if (count($listClassName) > 0) {
            foreach ($listClassName as $class) {
                if ($class['className'] != '') {
                    array_push($listClass, $class['className']);
                }
            }
            return array_unique($listClass);
        }

        return $listClass;
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
            $currentDate = date(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT);
            $em = $this->getEntityManager();
            $currentEikenScheduleId ='';
            $currentKai = '';
            $year = $this->dantaiService->getCurrentYear();
            $getCurrentKaiByYear = $em->getRepository('Application\Entity\EikenSchedule')->getCurrentKaiByYear($year);

            foreach ($getCurrentKaiByYear as $key => $value) {
                if (! empty($value['day1stTestResult']) && $value['day1stTestResult']->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) <= $currentDate) {
                    $currentEikenScheduleId = $value['id'];
                    $currentKai = $value['kai'];
                    break;
                }
            }

            if($currentKai){
                $applyEiken = $em->getRepository('Application\Entity\ApplyEikenOrg')
                                 ->getApplyEikenOrgShowPopup($this->orgId,$currentEikenScheduleId);
                if($applyEiken){
                    $sessionExist = false;

                    $session = $em->getRepository('Application\Entity\Session')
                                    ->findBy(array(
                                            'id' => $applyEiken->getSession(),
                                        ));
                    if(!empty($session)){
                        $sessionExist = true;
                    }
                    if((!$applyEiken->getSession() && ($applyEiken->getStatusAutoImport()===HistoryConst::STATUS_AUTO_IMPORT_EIKEN_ROUND1_COMPLETE ||$applyEiken->getStatusAutoImport() === HistoryConst::STATUS_AUTO_IMPORT_EIKEN_ROUND2_COMPLETE))
                            || ($applyEiken->getStatusAutoImport() == HistoryConst::STATUS_AUTO_IMPORT_EIKEN_ROUND2_COMPLETE && $sessionExist === false)
                    )
                    {
                        $responseData->setSuccess();
                        $responseData->setMessages(['msg' => sprintf($translator->translate('SHOW_POPUP_MSG14'), $year, $currentKai)]);
                        $responseData->setData(array('year' => $year,'kai' => $currentKai));
                        $applyEiken->setSession(session_id());
                        $em->flush();
                        $em->clear();
                    }
                    else if($applyEiken->getStatusAutoImport()===HistoryConst::STATUS_AUTO_IMPORT_EIKEN_FAILURE){
                        $responseData->setSuccess();
                        $responseData->setMessages(['msg' => $translator->translate('SHOW_POPUP_MSG59')]);
                        $responseData->setData(array('year' => null));
                        $applyEiken->setStatusAutoImport(HistoryConst::STATUS_AUTO_IMPORT_NOT_RUN);
                    }

                }
            }
        }
        return $this->getResponse()->setContent($responseData->jsonSerialize());
    }

	/*
     * Author ChungDV
     * export data to file Excel Of Pupil Achievement
     */
    public function exportPupilAchievementExcel($pupilAchievementList)
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $messages = array('noRecordExcel' => $translator->translate('MsgNoRecordExcel'));

        if (!$pupilAchievementList) {
            $this->flashMessenger()->addMessage($messages);

            return $this->redirect()->toRoute('history/default', array('controller' => 'eiken',
                    'action' => 'pupil-achievement'
            ));
        }

        $yearNo = PrivateSession::getData('yearNo');
        $kaiNo = PrivateSession::getData('kaiNo');

        $objFileName = new CharsetConverter();
        $fileName = sprintf($translator->translate('fileNameExcelPupilAchievement'), $yearNo, $kaiNo, '.xlsx');
        $fileName = $objFileName->utf8ToShiftJis($fileName);

        $header = $this->getServiceLocator()->get('Config')['headerExcelExport']['listOfPupilAchievement'];
        $exportExcelMapper = new ExportExcelMapper($pupilAchievementList, $header, $this->getServiceLocator());
        $arrPupilAchievement = $exportExcelMapper->convertToExport();

        $export = new PHPExcel();
        $export->export($arrPupilAchievement, $fileName, 'default', 1);
        return $this->getResponse();
    }

    /*
     * Author ChungDV
     * export data to file Excel Of Exam History List
     */
    public function exportExamHistoryListExcel($examHistoryList, $searchYear)
    {
        ob_start();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $messages = array('noRecordExcel' => $translator->translate('MsgNoRecordExcel'));

        if (!$examHistoryList) {
            $this->flashMessenger()->addMessage($messages);

            return $this->redirect()->toRoute('history/default', array('controller' => 'eiken',
                    'action' => 'exam-history-list'
            ));
        }

        $yearNo = PrivateSession::getData('yearNo');
        $translator = $this->getServiceLocator()->get('MVCTranslator');

        $objFileName = new CharsetConverter();
        $fileName = sprintf($translator->translate('fileNameExcelExamHistoryList'), $searchYear, '.xlsx');
        $fileName = $objFileName->utf8ToShiftJis($fileName);

        $header = $this->getServiceLocator()->get('Config')['headerExcelExport']['listOfExamHistoryList'];
        $exportExcelMapper = new ExportExcelMapper($examHistoryList,$header,$this->getServiceLocator(), $searchYear);
        $arrExamHistoryList = $exportExcelMapper->convertToExport();

        $export = new PHPExcel();
        $export->export($arrExamHistoryList, $fileName, 'default', 1);
        return $this->getResponse();
    }

    /* @var $objectOrg \Application\Entity\Organization */

    public function exportEikenAction()
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $messages = array('noRecordExcel' => $translator->translate('MsgNoRecordExcel'));
        $em = $this->getEntityManager();
        $year = intval($this->params('year', 0));
        $kai = intval($this->params('kai', 0));
        $orgNo = '';
        $objectOrg = $em->getRepository('Application\Entity\Organization')->find($this->orgId);
        if ($objectOrg) {
            $orgNo = $objectOrg->getOrganizationNo();
        }
        if (empty($year) || empty($kai) || $orgNo == '') {
            $this->flashMessenger()->addMessage($messages);
            return $this->redirect()->toRoute('history/default', array('controller' => 'eiken',
                    'action' => 'exam-result'
                    )
            );
        }
        $nameFile = sprintf($translator->translate('Tl1'),$year,$kai);
        $nameFile = CharsetConverter::utf8ToShiftJis($nameFile);

        $eikenTestResult = $em->getRepository('Application\Entity\EikenTestResult');
        $data = $eikenTestResult->getListExamResult($orgNo, $year, $kai);

        if (!$data) {
            $this->flashMessenger()->addMessage($messages);
            return $this->redirect()->toRoute('history/default', array('controller' => 'eiken',
                    'action' => 'exam-result'
                    )
            );
        }

        $header = $this->getServiceLocator()->get('Config')['headerExcelExport']['listOfEikenExamResult'];
        $exportExcelMapper = new ExportExcelMapper($data, $header, $this->getServiceLocator());
        $dataMapping = $exportExcelMapper->convertToExport();


        if (!$dataMapping) {
            $this->flashMessenger()->addMessage($messages);
            return $this->redirect()->toRoute('history/default', array('controller' => 'eiken',
                    'action' => 'exam-result'
                    )
            );
        }
        $objExcel = new PHPExcel();
        $objExcel->export($dataMapping, $nameFile, 'Eiken-result-template', 1);
        return $this->getResponse();
    }
    /*
     * Author : Manhnh5
     * UC#502
     */
    public function ajaxGetListClassAction()
    {
        $app = $this->getServiceLocator()->get('Application');
        $data = $app->getRequest()->getPost();
        $year = $data['year'];
        $schoolYear = $data['schoolYearId'];
        if (empty($year) && empty($schoolYear)) {
            return $this->getResponse()->setContent(json_encode(array()));
        }
        $listClass = $this->eikenHistoryService->getAjaxListClass($year, $schoolYear);
        return $this->getResponse()->setContent(json_encode($listClass));
    }

    public function eikenMappingResultAction()
    {
        $year = $this->params()->fromRoute('year', 0);
        $kai = $this->params()->fromRoute('kai', 0);
        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $em = $this->getEntityManager();
        $currentEikenScheduleId = $em->getRepository('Application\Entity\EikenSchedule')->getIdByYearKai($year,$kai);
        $applyEikenAutoMapping = $em->getRepository('Application\Entity\ApplyEikenOrg')->getApplyEikenOrgShowPopup($this->orgId,$currentEikenScheduleId);
        if($applyEikenAutoMapping)
        {
            if($applyEikenAutoMapping->getStatusAutoImport() === HistoryConst::STATUS_AUTO_IMPORT_EIKEN_ROUND1_COMPLETE){
                $applyEikenAutoMapping->setStatusAutoImport(HistoryConst::STATUS_AUTO_IMPORT_EIKEN_ROUND1_CONFIRMED);
            }
            else if($applyEikenAutoMapping->getStatusAutoImport() == HistoryConst::STATUS_AUTO_IMPORT_EIKEN_ROUND2_COMPLETE){
                $applyEikenAutoMapping->setStatusAutoImport(HistoryConst::STATUS_AUTO_IMPORT_EIKEN_ROUND2_CONFIRMED);
            }
            $em->flush();
            $em->clear();
        }
        $checkSession = $this->getRequest()->getServer('HTTP_REFERER');
        if(strpos($checkSession, 'history/eiken/eiken-mapping-result') === false){
            PrivateSession::clear(HistoryConst::sessionSearchEiken);
        }
        $searchParams = PrivateSession::getData(HistoryConst::sessionSearchEiken);

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
        if (intval($year) < 2010 || intval($kai) < 1) {
            return $this->redirect()->toUrl('/history/eiken/exam-result');
        }
        $translate = $this->serviceLocator->get('MVCTranslator');
        $config = $this->serviceLocator->get('config')['MappingLevel'];
        $data = $this->mappingEikenResultService->getEikenResultsDetails($year, $kai, $schoolYearId, $classId, $nameKana, $mappingStatus);

        $totalConfirm = $this->mappingEikenResultService->getTotalMappingStatus($year, $kai);
        $arrayConfirm['unconfirm'] = isset($totalConfirm[HistoryConst::UNCONFIRM_STATUS]['total']) ? $totalConfirm[HistoryConst::UNCONFIRM_STATUS]['total'] : 0;
        $arrayConfirm['confirmed'] = isset($totalConfirm[HistoryConst::CONFIRMED_STATUS]['total']) ? $totalConfirm[HistoryConst::CONFIRMED_STATUS]['total'] : 0;

        $result = $data->getItems($offset, $limit);
        foreach ($result as $key => $val) {
            $result[$key]['eikenLevelName'] = $config[$val['eikenLevelId']];
            $result[$key]['birthday'] = empty($val['birthday']) ? '' : $val['birthday']->format(HistoryConst::FORMAT_DATE);
            $result[$key]['tempBirthday'] = empty($val['tempBirthday']) ? '' : $val['tempBirthday']->format(HistoryConst::FORMAT_DATE);
            $result[$key]['mappingStatus'] = isset($translate->translate('mappingStatus')[$val['mappingStatus']]) ? $translate->translate('mappingStatus')[$val['mappingStatus']] : '';
            $result[$key]['logicPassFail'] = $translate->translate('logicPassFail')[$this->mappingEikenResultService->logicPassFail($val['eikenLevelId'], $val['primaryPassFailFlag'], $val['oneExemptionFlag'], $val['secondPassFailFlag'])];
        }
        $schoolYear = $this->mappingEikenResultService->getListSchoolYear();
        if ($schoolYearId) {
            $class = $this->mappingEikenResultService->getListClassBySchoolYear($schoolYearId, $year);
        }
        $token = md5($this->orgNo . $year . $kai);
        $pupil = $this->mappingEikenResultService->checkPupilByYear($year);

        return new ViewModel(array('limit' => $limit,'pupilByYear' => ($pupil ? 1 : 0),'token' => $token, 'messages' => $this->getMessages(), 'result' => $result,'data' => $data, 'class' => $class, 'searchParams' => $searchParams, 'schoolYear' => $schoolYear, 'mappingStatus' => $translate->translate('mappingStatus'), 'arrayConfirm' => $arrayConfirm, 'year' => $year, 'kai' => $kai, 'page' => $page));

    }

    public function searchAction()
    {
        $app = $this->getServiceLocator()->get('Application');
        $data = $app->getRequest();
        if ($data->isPost()) {
            $data = $data->getPost();
            PrivateSession::setData(HistoryConst::sessionSearchEiken, $data);

            return $this->redirect()->toUrl('/history/eiken/eiken-mapping-result/year/' . $data['year'] . '/kai/' . $data['kai']);
        }

        return $this->redirect()->toUrl('/history/eiken/exam-result');
    }

    public function clearAction()
    {
        $data = PrivateSession::getData(HistoryConst::sessionSearchEiken);
        if ($data) {
            PrivateSession::clear(HistoryConst::sessionSearchEiken);
        }
        $year = $this->params()->fromRoute('year');
        $kai = $this->params()->fromRoute('kai');

        return $this->redirect()->toUrl('/history/eiken/eiken-mapping-result/year/' . $year . '/kai/' . $kai);
    }

    public function eikenConfirmResultAction()
    {
        /**
         * @var $result \Application\Entity\EikenTestResult
         */
        $id = $this->params()->fromRoute('id');
        $result = $this->mappingEikenResultService->getEikenTestResult($id);
        if (empty($result)) {
            return $this->redirect()->toUrl('/history/eiken/exam-result');
        }
        $currentPupil=array();
        if(!empty($result->getPupilId()))
        {
            $currentPupil = $this->eikenHistoryService->getDetailPupil($result->getPupilId(), $this->orgId);
        }
        $config = $this->serviceLocator->get('config')['MappingLevel'];
        $translate = $this->serviceLocator->get('MVCTranslator');
        $result->getBirthday = empty($result->getBirthday()) ? '' : $result->getBirthday()->format(HistoryConst::FORMAT_DATE);
        $result->getTempBirthday = empty($result->getTempBirthday()) ? '' : $result->getTempBirthday()->format(HistoryConst::FORMAT_DATE);
        $result->logicPassFail = $translate->translate('logicPassFail')[$this->mappingEikenResultService->logicPassFail($result->getEikenLevelId(), $result->getPrimaryPassFailFlag(), $result->getOneExemptionFlag(), $result->getSecondPassFailFlag())];
        $result->getEikenLevelName = $config[$result->getEikenLevelId()];
        $schoolYear = $this->mappingEikenResultService->getListSchoolYear();
        $token = md5($this->orgNo . $result->getYear() . $result->getKai());

        return new ViewModel(array(
                'result'     => $result,
                'currentPupil'=>$currentPupil,
                'messages'   => $this->getMessages(),
                'schoolYear' => $schoolYear,
                'token'      => $token,
                'id'         => $id,
                'year'       => $result->getYear(),
                'kai'        => $result->getKai())
        );
    }

    public function getStudentsAction()
    {
        $app = $this->getServiceLocator()->get('Application');
        $data = $app->getRequest();
        if ($data->isPost()) {
            $data = $data->getPost();

            $schoolYearId = isset($data['schoolYearId']) ? intval($data['schoolYearId']) : '';
            $classId = isset($data['classId']) ? trim($data['classId']) : '';
            $year = isset($data['year']) ? trim($data['year']) : '';
            $birthday = isset($data['birthday']) ? trim($data['birthday']) : '';
            $nameKana = isset($data['nameKana']) ? trim($data['nameKana']) : '';
            $nameKanji = isset($data['nameKanji']) ? trim($data['nameKanji']) : '';

            $items = $this->mappingEikenResultService->findPupilList($schoolYearId, $classId, $year, $birthday, $nameKana, $nameKanji);
        }
        $viewModel = new ViewModel();
        $viewModel->setTemplate('history/eiken-history/pupil-list-eiken-exam-result.phtml');
        $viewModel->setTerminal(true);
        $viewModel->setVariables(array('items' => empty($items) ? null : $items));

        return $viewModel;
    }

    public function callSaveNextPupilAction()
    {
        $app = $this->getServiceLocator()->get('Application');
        $data = $app->getRequest()->getPost();
        $pupilId = $data['pupilId'];
        $typeMapping = $data['typeMapping'];
        $eikenTestResultId = $data['eikenTestResultId'];
        $type = $data['type'];
        if ($typeMapping == 1 && $pupilId == 0) {
            $this->mappingEikenResultService->deleteMapping($eikenTestResultId);
        }
        else {
            $this->mappingEikenResultService->confirmMapping($eikenTestResultId, $pupilId);
        }

        return $this->getResponse()->setContent(json_encode(array('type' => $type)));
    }

    public function getTranslation() {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $messages = array(
            'msgReImport' => $translator->translate('msgReImport')
        );

        return json_encode($messages);
    }
}
