<?php

namespace History\Service;

use History\HistoryConst;
use History\Service\ServiceInterface\IbaHistoryServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Dantai\PrivateSession;
use Dantai\PublicSession;
use History\Form\SearchInquiryIBAForm;
use Dantai;
use Zend\Json\Json;
use Zend\View\Model\ViewModel;
use Dantai\Utility\MappingUtility;

class IbaHistoryService implements IbaHistoryServiceInterface, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    private $idOrg = 0;
    protected $em;
    protected $organizationNo;

    const NUMBER_QUESTION = 80;

    protected $organizationName;
    public $ibaTestResultRepo;
    
    public function __construct() {
        $user = PrivateSession::getData('userIdentity');
        $this->idOrg = $user['organizationId'];
        $this->organizationNo = $user['organizationNo'];
        $this->organizationName = $user['organizationName'];
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
     
    
    public function getListIbaMasterData($type=null)
    {
        $em = $this->getEntityManager();
        return $em->getRepository('Application\Entity\IbaScoreMasterData')->getListIbaScoreMasterData($type);       
    }

    public function getOrganizationNo() {
        $em = $this->getEntityManager();
        $organizationNo = $em->getRepository('Application\Entity\Organization')
                ->findOneBy(array(
                    'id' => $this->idOrg
                ))
                ->getOrganizationNo();

        return $organizationNo;
    }

    public function getListInquiryIBA($sortOrder, $sortKey, $searchVisible, $page, $limit, $offset, $post, $searchCriteria, $routeMatch, $request, $params, $flashMessenger, $dantaiService, $redirect, $messages) {
        $sessionExamDate = PrivateSession::getData('examdate');
        $sessionJisshiId = PrivateSession::getData('jisshiId');
        $sessionExamType = PrivateSession::getData('examType');
        if (empty($sessionJisshiId) || empty($sessionExamType)) {
            return $redirect->toRoute('history/default', array(
                        'controller' => 'eiken',
                        'action' => 'exam-result'
            ));
        }
        $examDate = date_format(date_create($sessionExamDate), "Y/m/d");
        $form = new SearchInquiryIBAForm();
        $em = $this->getEntityManager();
        if ($post && $searchCriteria['token']) {
            return $redirect->toUrl('/history/iba/pupil-achievement/search/' . $searchCriteria['token']);
        }
        $objSchoolyear = $this->getSchoolYearCode($this->organizationNo, $examDate, $sessionJisshiId, $sessionExamType);
        $listOrgSchoolYear = array();
        if (isset($objSchoolyear)) {
            $listOrgSchoolYear[''] = '';
            foreach ($objSchoolyear as $key => $value) {
                $listOrgSchoolYear[$value['schoolYearName']] = $value['schoolYearName'];
            }
        }
        $dataAttrFormOrgSchoolYear = array(
            'value' => '',
            'selected' => true,
            'escape' => false
        );
        if (isset($searchCriteria['orgSchoolYear'])) {
            $dataAttrFormOrgSchoolYear['value'] = $searchCriteria['orgSchoolYear'];
        }
        $form->get('orgSchoolYear')
                ->setValueOptions($listOrgSchoolYear)
                ->setAttributes($dataAttrFormOrgSchoolYear);
        $objClass = $this->getClassCode($this->organizationNo, $examDate,  $sessionJisshiId, $sessionExamType);
        $listClass = array();
        // change key value list class
        if (isset($objClass)) {
            $listClass[''] = '';
            foreach ($objClass as $key => $value) {
                $listClass[$key] = $value['className'];
            }
        }
        $dataAttrFormClassj = array(
            'value' => '',
            'selected' => true,
            'escape' => false
        );
        if ($searchCriteria['classj'] != null) {
            $dataAttrFormClassj['value'] = $searchCriteria['classj'];
            $searchCriteria['classj'] = $listClass[$dataAttrFormClassj['value']];
        }
        $form->get('classj')
                ->setValueOptions($listClass)
                ->setAttributes($dataAttrFormClassj);
        $dataAttrFormName = array(
            'value' => ''
        );
        if ($searchCriteria['name'] != NULL) {
            $dataAttrFormName['value'] = $searchCriteria['name'];
        }
        $form->get('name')->setAttributes($dataAttrFormName);
        if (isset($searchName)) {
            $searchName = $this->remove_special_characters(trim($searchName));
        }
        
//        update function for : #GNCCNCJDR5-761
        $ibaMasterData = $em->getRepository('Application\Entity\IbaScoreMasterData')->getListIbaScoreMasterData();
        
        $paginator = $em->getRepository('Application\Entity\IBATestResult')->getDataInquiryIBA($this->organizationNo, $searchCriteria);

        return array(
            'pupilAchievementList' => $paginator->getAllItems(),
            'inquiryiba' => $paginator->getItems($offset, $limit),
            'form' => $form,
            'page' => $page,
            'examDate' => $examDate,
            'paginator' => $paginator,
            'numPerPage' => $limit,
            'param' => isset($searchCriteria['token']) ? $searchCriteria['token'] : '',
            'ibaMasterData' => $ibaMasterData,
            'sortOrder' => $sortOrder,
            'sortKey' => $sortKey,
            'searchVisible' => $searchVisible,
            'noRecordExcel' => $messages,
            'roleLimit' => PublicSession::isDisableDownloadButtonRole(),
            'examType' => $sessionExamType,
        );
    }

    public function getHistoryPupilIBA($params, $searchCriteria, $page, $limit, $offset, $routeMatchs, $messages) {
        $em = $this->getEntityManager();
        $schoolyear = ($searchCriteria['schoolYear'] != null) ? $searchCriteria['schoolYear'] : '';
        $class = ($searchCriteria['className'] != null) ? $searchCriteria['className'] : '';
        $number = ($searchCriteria['pupilNumber'] != null) ? $searchCriteria['pupilNumber'] : '';
        $name = ($searchCriteria['name'] != null) ? $searchCriteria['name'] : '';
        //        update function for : #GNCCNCJDR5-761
        $ibaMasterData = $em->getRepository('Application\Entity\IbaScoreMasterData')->getListIbaScoreMasterData();
        $paginator = $em->getRepository('Application\Entity\IBATestResult')->getHistoryPupilIBA($this->organizationNo, $searchCriteria);
        return new ViewModel(array(
            'historyiba' => $paginator->getItems($offset, $limit),
            'page' => $page,
            'paginator' => $paginator,
            'numPerPage' => $limit,
            'schoolyear' => $schoolyear,
            'class' => $class,
            'number' => $number,
            'name' => $name,
            'param' => isset($searchCriteria['token']) ? $searchCriteria['token'] : '',
            'ibaMasterData' => $ibaMasterData,
            'noRecordExcel' => $messages
        ));
    }

    public function getListClassesBySchoolYear($params, $response) {
        $em = $this->getEntityManager();
        $string = array();
        $schoolyear = $params->fromPost('schoolyear', null);
        $string['classj'] = '';
        if (isset($schoolyear) && $schoolyear) {
            $objClass = $em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYear($schoolyear, $this->idOrg);
            if (!empty($objClass)) {
                foreach ($objClass as $key => $value) {
                    $string['classj'][$key] = $value;
                }
            }
        }
        return $response->setContent(Json::encode($string));
    }

    public function remove_special_characters($string) {
        $string = str_replace(array(
            "'",
            '"'
                ), array(
            "",
            ""
                ), $string);
        $string = trim($string);

        return $string;
    }

    /**
     * AnNV6 UC16
     * Get ExamDate of UC9 from param and set into session
     */
    public function setSessionExamDate($params, $redirect) {
        $isset_post = $params->fromRoute();
        $examDate = '';
        if (isset($isset_post)) {
            $examDate = $isset_post['examdate'];
            $jisshiId = $isset_post['jisshiId'];
            $examType = $isset_post['examType'];
        }
        PrivateSession::setData('examdate', $examDate);
        PrivateSession::setData('jisshiId', $jisshiId);
        PrivateSession::setData('examType', $examType);

        return $redirect->toRoute('history/default', array(
                    'controller' => 'iba',
                    'action' => 'pupil-achievement'
        ));
    }

    /**
     *
     * @author HienVH
     *         UC14: Service function that call uketsuke API : /step-eir/EIR2C02
     *
     * @param string $organizationNo
     *            (dantaino)
     * @param string $moshikomiid
     *            (moshikomiid)
     */
    public function getIBAExamResult($jisshiId, $examType) {
        $config = $this->serviceLocator->get('Config')['iba_config']['api'];
        // api parameters
        $params = array(
            "jisshiid" => $jisshiId,
            "examkbn" => $examType,
        );
        if (!$this->uketukeClient) {
            $this->setUketukeClient();
        }

        $result = \Dantai\Api\UkestukeClient::getInstance()->callEir2c02($config, $params);
        return $result;
    }

    public function getIBAExamResultId($ibaId, $organizationNo) {
        $ibaId = (int) $ibaId;
        if ($ibaId < 0) {
            return false;
        }
        $data = $this->getEntityManager()->getRepository('Application\Entity\IBATestResult')->findOneBy(array('id' => $ibaId, 'organizationNo' => $organizationNo));
        if (empty($data)) {
            return false;
        }
        // 英研究レベル検定（一次試験）” – star level
        $totalStar = $this->getIbaScoreStarNumber(HistoryConst::IBA_RESULT_TOTAL, $data->getTestType(), $data->getTotal());
        $readingStar = $this->getIbaScoreStarNumber(HistoryConst::IBA_RESULT_READING, $data->getTestType(), $data->getRead());
        $listeningStar = $this->getIbaScoreStarNumber(HistoryConst::IBA_RESULT_LISTENING, $data->getTestType(), $data->getListen());
        $data->getStarTotal = $totalStar['starNumber'] ? $totalStar['starNumber'] : 0;
        $data->getStarReading = $readingStar['starNumber'] ? $readingStar['starNumber'] : 0;
        $data->getStarListening = $listeningStar['starNumber'] ? $listeningStar['starNumber'] : 0;
        // あなたができること　- 英検Can-doリストから
        $readingCanDo = $this->getIbaScoreCanDo($readingStar['canDoName']);
        $listeningCanDo = $this->getIbaScoreCanDo($listeningStar['canDoName']);
        $data->getReadingCanDo = $readingCanDo ? $readingCanDo['reading'] : '';
        $data->getListeningCanDo = $listeningCanDo ? $listeningCanDo['listening'] : '';
        //分野別学習アドバイス - Advice
        $readingAdvice = $this->getIbaScoreAdvice($readingStar['adviceName']);
        $listeningAdvice = $this->getIbaScoreAdvice($listeningStar['adviceName']);
        $data->getVocAdvice = $readingAdvice ? $readingAdvice['vocab'] : '';
        $data->getReadingAdvice = $readingAdvice ? $readingAdvice['reading'] : '';
        $data->getListeningAdvice = $listeningAdvice ? $listeningAdvice['listening'] : '';
        //リーディング・リスニングテストの結果
        // $data->getAverageScoreTotal = ($data->getAverageScoreTotal() % 100 < 50 ? round($data->getAverageScoreTotal(), -2) + 100 : round($data->getAverageScoreTotal(), -2));
        $data->getExamDate = $data->getExamDate() ? $data->getExamDate()->format('Y/m/d') : '';
        $data->getOrganizationName = $this->organizationName;

        return $data;
    }

    /**
     * AnNV6 UC16
     * Get list of SchoolYearCode from table IBATestResult
     */
    public function getSchoolYearCode($organizationNo, $examDate, $jisshiId, $examType) {
        $em = $this->getEntityManager();
        $objSchoolyear = $em->getRepository('Application\Entity\IBATestResult')->getSchoolYearCode($organizationNo, $examDate, $jisshiId, $examType);

        return $objSchoolyear;
    }

    /**
     * AnNV6 UC16
     * Get list of ClassCode from table IBATestResult
     */
    public function getClassCode($organizationNo, $examDate, $jisshiId, $examType) {
        $em = $this->getEntityManager();
        $objClass = $em->getRepository('Application\Entity\IBATestResult')->getClassCode($organizationNo, $examDate, $jisshiId, $examType);

        return $objClass;
    }

    public function getListIbaTestResult($jisshiId, $examType) {
        $em = $this->getEntityManager();
        $listIbaTestResult = $em->getRepository('Application\Entity\IBATestResult')->getListIbaTestResult($jisshiId, $examType);
        $returnIbaTestResult = $this->convertIbaTestResult($listIbaTestResult);

        return $returnIbaTestResult;
    }

    /**
     * DucnaNA17
     * @param string $orgNo
     * @param int $year
     * @param int $kai
     * @param int $page
     * @param int $statusMapping
     * @return array $mappingResturn;
     */
    public function getDataMappingByStatus($orgNo, $year, $page, $statusMapping, $jisshiId, $examType) {
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $mappingResturn = array();
        $em = $this->getEntityManager();
        $paginator = $em->getRepository('Application\Entity\IBATestResult')->getIbaTestResultByOrgNo($orgNo, $year, $statusMapping, $jisshiId, $examType);
        $result = $paginator->getItems($offset, $limit);
        if (!empty($result)) {
            $mappingResturn = $this->convertIbaTestResult($result);
        }

        return array('data' => $mappingResturn, 'paginator' => $paginator);
    }

    /**
     * DucNA17
     * @param string $orgNo
     * @param int $year
     * @param int $kai
     * @param int $page
     * @return array(
     * 'mappingError'        => $mappingError,
     * 'mappingSuccess'      => $mappingSuccess,
     * 'mappingCoincident'   => $mappingCoincident,//duplicate
     * 'pupilSuccess'   => $pupilSuccess,
     * 'totalMappingSuccess' => sizeof($mappingSuccess),
     * 'totalMappingError'   => sizeof($mappingError) + sizeof($mappingCoincident)
     * );
     */
    public function getDataMapping($orgNo, $year, $page, $mappingStatus = null, $jisshiId = 0, $examType = '') {
        $mappingError = array();
        $mappingSuccess = array();
        $mappingCoincident = array();
        $pupilSuccess = array();
        $pupilError = array();
        $data = array('paginator' => null,
            'mappingSuccess' => null,
            'mappingError' => null
        );
        $em = $this->getEntityManager();
        if ($mappingStatus == 1) {
            $mappingSuccess = $this->getDataMappingByStatus($orgNo, $year, $page, 1, $jisshiId, $examType);
            $data['mappingSuccess'] = $mappingSuccess['data'];
        } elseif ($mappingStatus == 0) {
            $mappingError = $this->getDataMappingByStatus($orgNo, $year, $page, 0, $jisshiId, $examType);
            $data['mappingError'] = $mappingError['data'];
        }
        if (!empty($mappingError['data'])) {
            $listIdPupilError = array();
            $data['paginator'] = $mappingError['paginator'];
            foreach ($mappingError['data'] as $itemsError) {
                foreach ($itemsError as $itemsError) {
                    if (!empty($itemsError['tempPupilId'])) {
                        $listIdPupilError[] = $itemsError['tempPupilId'];
                    }
                }
            }
            //get list pupil error (duplicate)
            $data['pupilError'] = $em->getRepository('Application\Entity\Pupil')->getListPupilDataById($listIdPupilError);
        }
        if (!empty($mappingSuccess['data'])) {
            $listIdPupilSuccess = array();
            $data['paginator'] = $mappingSuccess['paginator'];
            foreach ($mappingSuccess['data'] as $itemsSuccess) {
                foreach ($itemsSuccess as $itemSuccess) {
                    $listIdPupilSuccess[] = $itemSuccess['pupilId'];
                }
            }
            //get list pupil success
            $data['pupilSuccess'] = $em->getRepository('Application\Entity\Pupil')->getListPupilDataById($listIdPupilSuccess);
        }

        return $data;
    }

    //ducna17
    public function convertIbaTestResult($listIbaTestResult) {
        
        $return = array();
        if (!empty($listIbaTestResult)) {
            foreach ($listIbaTestResult as $key => $item) {
                $item['nameKana'] = str_replace(' ', '', $item['nameKana']);
                $listIbaTestResult[$key]['nameKana'] = str_replace(' ', '', $item['nameKana']);
                $keyStr = '';
                if (!empty($item['birthday'])) {
                    $keyStr .= $item['birthday']->format('Y-m-d');
                }
                if(!empty($item['nameKana'])){
                    $keyStr .= $item['nameKana'];
                }
                if (!empty($return[$keyStr])) {
                    //exist;
                    array_push($return[$keyStr], $item);
                } else {
                    // no exist
                    $return[$keyStr][] = $item;
                }
            }
        }
        return $return;
    }

    /**
     * DucNA17
     * @param array $eikenTestResult
     */
    public function getListPupil($orgId, $year) {
        $em = $this->getEntityManager();
        $listPupil = $em->getRepository('Application\Entity\Pupil')->getPupilData($orgId, $year);
        if (!empty($listPupil)) {
            //convert to type list data use for autocomplete
            $listPupil = $this->convertDataAutoComplete($listPupil);
        }

        return $listPupil;
    }

    //ducna17
    public function convertDataAutoComplete($listPupil) {
        foreach ($listPupil as $key => $item) {
            $listPupil[$key]['value'] = $item['firstNameKanji'] . $item['lastNameKanji'];
            $listPupil[$key]['label'] = $item['firstNameKanji'] . $item['lastNameKanji'];
        }

        return $listPupil;
    }

    //ducna17
    public function convertPupilManager($listPupil) {
        $return = array();
        if (!empty($listPupil)) {
            foreach ($listPupil as $key => $item) {
                $keyStr = '';
                if (!empty($item['birthday']) && !empty($item['firstNameKanji']) && !empty($item['lastNameKanji'])) {
                    $keyStr .= $item['birthday']->format('Y-m-d');
                    $keyStr .= $item['firstNameKanji'];
                    $keyStr .= $item['lastNameKanji'];
                    if (!empty($return[$keyStr])) {
                        //exist;
                        array_push($return[$keyStr], $item);
                    } else {
                        // no exist
                        $return[$keyStr][] = $item;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * DucNA17
     * @param array $ibaTestResult
     */
    public function mappingData($ibaTestResult, $pupilManager, $applyIbaId = 0, $examName = null) {
        $mappingError = array();
        $mappingSuccess = array();
        $mappingCoincident = array();
        if (!empty($ibaTestResult)) {
            foreach ($ibaTestResult as $key => $item) {
                if (sizeof($item) > 1) {
                    if (!empty($pupilManager[$key])) {
                        $mappingCoincident[$key] = $item;
                    } else {
                        $mappingError[$key] = $item;
                    }
                } elseif (sizeof($item) == 1) {
                    if (!empty($pupilManager[$key])) {
                        //duplicate
                        if (sizeof($pupilManager[$key]) > 1) {
                            $mappingCoincident[$key] = $item;
                        } else {
                            //success
                            $mappingSuccess[$key] = $item;
                        }
                    } else {
                        // error
                        $mappingError[$key] = $item;
                    }
                }
            }
            if (!empty($mappingSuccess)) {
                $listItemSuccess = array();
                foreach ($mappingSuccess as $key => $itemsSuccess) {
                    foreach ($itemsSuccess as $itemSuccess) {
                        $listItemSuccess[$pupilManager[$key][0]['id']] = $itemSuccess['id'];
                        $this->updateIbaTestResult($itemSuccess['id'], $pupilManager[$key][0]['id']);
                    }
                }
                PrivateSession::setData('listItemSuccess', $listItemSuccess);
            }
        }

        return array(
            'mappingError' => $mappingError,
            'mappingSuccess' => $mappingSuccess,
            'mappingCoincident' => $mappingCoincident//duplicate
        );
    }

    /**
     * Ducna17
     * update temp EikenTestResult and insert temp EikenScore when user change textbox to mapping
     * @param int $pupilId
     * @param int $eikenTestResultId
     */
    public function updateIbaTestResult($ibaTestResultId, $pupilId) {
        if ($pupilId > 0 && $ibaTestResultId > 0) {
            $em = $this->getEntityManager();
            //get info of Pupil by Pupil
            $infoPupil = $em->getRepository('Application\Entity\Pupil')->find($pupilId);
            $pupilNo = $infoPupil->getNumber();
            //update IbaExamResult
            $ibaTestResultById = $em->getRepository('Application\Entity\IBATestResult')->find(array('id' => $ibaTestResultId));
            if ($infoPupil->getOrgSchoolYearId() !== null) {
                $schoolYearName = $em->getRepository('Application\Entity\OrgSchoolYear')->findOneBy(array('id' => $infoPupil->getOrgSchoolYearId()))->getDisplayName();
                $ibaTestResultById->setTempSchoolYearName($schoolYearName);
                $ibaTestResultById->setTempOrgSchoolYearId($infoPupil->getOrgSchoolYearId());
            }
            if ($infoPupil->getClassId() !== null) {
                $className = $em->getRepository('Application\Entity\ClassJ')->findOneBy(array('id' => $infoPupil->getClassId()))->getClassName();
                $ibaTestResultById->setTempClassId($infoPupil->getClassId());
                $ibaTestResultById->setTempClassName($className);
            }
            //clone nameKana update
            $nameKanaOfPupil = '';
            if (!empty($infoPupil->getFirstNameKana())) {
                $nameKanaOfPupil .= $infoPupil->getFirstNameKana();
            }
            if (!empty($infoPupil->getLastNameKana())) {
                $nameKanaOfPupil .= $infoPupil->getLastNameKana();
            }
            if ($nameKanaOfPupil !== '') {
                //update vao TempNameKanji
                $ibaTestResultById->setPreTempNameKana($nameKanaOfPupil);
            }
            $ibaTestResultById->setTempPupilId($pupilId);
            $ibaTestResultById->setTempPupilNo($pupilNo);
            try {
                $em->persist($ibaTestResultById);
                $em->flush();
            } catch (Exception $e) {
                throw $e;
            }
            //insert IbaScore
        }
    }

    /**
     * DucNA17
     * @param int $eikenTestResultId
     * get record of EikenTestResult by id input to EikenScore
     */
    public function insertIbaScoreByIbaTestResult($ibaTestResultId = 0, $pupilId ,$arrayMapping, $ibaLevelName) {
        $em = $this->getEntityManager();
        $ibaScore = $em->getRepository('Application\Entity\IBAScore')->findOneBy(array('ibaTestResultId' => $ibaTestResultId));
        if ($ibaScore === null) {
            $ibaScore = new \Application\Entity\IBAScore();
        }
        $ibaTestResult = $em->getRepository('Application\Entity\IBATestResult')->find($ibaTestResultId);
//        update function for : #GNCCNCJDR5-761
        /* @var $ibaTestResult \Application\Entity\IBATestResult */
        $eikenLevelName = isset($arrayMapping[$ibaLevelName]) ? $arrayMapping[$ibaLevelName] : $ibaLevelName;
        
        $eikenLevel = $em->getRepository('Application\Entity\EikenLevel')->findOneBy(array('levelName' => $eikenLevelName));
        if ($eikenLevel !== null) {
            $ibaScore->setIBALevel($eikenLevel);
        }
        $ibaScore->setExamDate($ibaTestResult->getExamDate());
        $ibaScore->setReadingScore($ibaTestResult->getRead());
        $ibaScore->setListeningScore($ibaTestResult->getListen());
        $ibaScore->setIBACSETotal($ibaTestResult->getTotal());
        $ibaScore->setIbaTestResultId($ibaTestResultId);
        $ibaScore->setPupil($em->getReference('Application\Entity\Pupil', $pupilId));
        try {
            $em->persist($ibaScore);
            $em->flush();
            /**
             * @author minhbn1
             * add org To queue
             */
            $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
            $dantaiService->addOrgToQueue($this->idOrg, $ibaTestResult->getYear());
            //
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * DucNa17
     */
    public function saveMappingIba($keepSessionData) {
        $em = $this->getEntityManager();
        $listItemSuccess = PrivateSession::getData('listItemSuccess');
        $config = $this->getServiceLocator()->get('config');
        $ibaMasterData = $em->getRepository('Application\Entity\IbaScoreMasterData')->getListIbaScoreMasterData();
        $ibaLevelName = MappingUtility::getKyuName($ibaMasterData, HistoryConst::IBA_RESULT_TOTAL, $ibaTestResult->getTestType(), $ibaTestResult->getTotal());
        $arrayMapping = $config['mappingKyuIbaFinal'];
        if (!empty($listItemSuccess)) {
            //update TempPupilId... => Pupil on table EikenTestResult
            $em->getRepository('Application\Entity\IBATestResult')->updateTempData($listItemSuccess);
            foreach ($listItemSuccess as $pupilId => $ibaTestResultId) {
                $this->insertIbaScoreByIbaTestResult($ibaTestResultId, $pupilId,$arrayMapping,$ibaLevelName);
            }

            PrivateSession::clear('listItemSuccess');
        }
        if (!empty($keepSessionData)) {
            foreach ($keepSessionData as $ibaTestResultId => $mappingItem) {
                $ibaTestResultIds[] = $ibaTestResultId;
                // insert to IBAScore
                $this->insertIbaScoreByIbaTestResult($ibaTestResultId, $mappingItem['pupilId'],$arrayMapping,$ibaLevelName);
            }
            //update TempPupilId... => Pupil on table EikenTestResult
            $em->getRepository('Application\Entity\IBATestResult')->updateTempData($ibaTestResultIds);
        }
        $applyIbaId = PrivateSession::getData('applyIbaId');
        if ($applyIbaId) {
            $ApplyIbaOrg = $this->getEntityManager()->getRepository('Application\Entity\ApplyIBAOrg')->find($applyIbaId);
            if (!empty($ApplyIbaOrg)) {
                $ApplyIbaOrg->setStatusMapping(1);
                $this->getEntityManager()->persist($ApplyIbaOrg);
                $this->getEntityManager()->flush();
            }
        }
    }

    private function checkDataBeforeTrim($data) {
        if ($data != '') {
            return trim($data);
        }
        return $data;
    }

    private function getIdByEikenLevelTotalName($list, $name) {
        foreach ($list as $eiken) {
            if ($eiken['levelName'] == $name) {
                return $eiken['id'];
                break;
            }
        }
    }
        
    public function setIbaTestResultRepo($ibaMock = Null){
        $this->ibaTestResultRepo = $ibaMock ? $ibaMock : $this->getEntityManager()->getRepository('Application\Entity\IBATestResult');
    }
    
    public function populateDataToExportListIba($jisshiId, $examType){
        if(!$this->ibaTestResultRepo){
            $this->setIbaTestResultRepo(); 
        }
        $data = $this->ibaTestResultRepo->getDataToExportByJisshiIdExamType($jisshiId, $examType);

        foreach($data as &$value){
            if(!empty($value['answerSerialize'])){
                $answerSerialize = json_decode($value['answerSerialize']);
                foreach ($answerSerialize as $key => $val) {
                    $value['answer' . ($key + 1)] = $val;
                }
            }
 
            if(!empty($value['accuraryJugdeSerialize'])){
                $accuraryJugdeSerialize = json_decode($value['accuraryJugdeSerialize']);
                foreach ($accuraryJugdeSerialize as $key => $val) {
                    $value['seigojudge' . ($key + 1)] = $val;
                }
            }
            
            $value['ibaEikenLevelTotal'] = $value['eikenLevelTotal'];
            $value['ibaEkenLevelRead'] = $value['ekenLevelRead'];
            $value['ibaEikenLevelListening'] = $value['eikenLevelListening'];
            $value['ibaEikenLevelKyu'] = $value['eikenLevelKyu'];
            $value['ibaCorrectAnswerPercentGrammar'] = $value['correctAnswerPercentGrammar'];
            $value['ibaCorrectAnswerPercentReading'] = $value['correctAnswerPercentReading'];
            $value['ibaCorrectAnswerPercentListening'] = $value['correctAnswerPercentListening'];
        }
        
        $config = $this->getServiceLocator()->get('Config');
        $header = $config['headerExcelExport']['listOfIBAExamResult'];
        $exportExcelMapper = new ExportExcelMapper($data, $header, $this->getServiceLocator());
        $arrIBAList = $exportExcelMapper->convertToExport();
        return $arrIBAList;
    }
    
    public function getHistoryPupilIbaExport($orgNo, $searchCriteria){
        if(!$this->ibaTestResultRepo){
            $this->setIbaTestResultRepo(); 
        }
        $dataExport = $this->ibaTestResultRepo->getHistoryPupilIBA($orgNo, $searchCriteria, true);
        if (!$dataExport) {
            return null;
        }
        $config = $this->getServiceLocator()->get('Config');
        $header = $config['headerExcelExport']['listOfIBAHistoryPupil'];
        $exportExcelMapper = new ExportExcelMapper($dataExport, $header, $this->getServiceLocator());
        $arrPupilAchievement = $exportExcelMapper->convertToExport();
        return $arrPupilAchievement;
    }

    public function getIbaScoreStarNumber($type, $testSet, $score){
        return $this->getEntityManager()->getRepository('Application\Entity\IbaScoreMasterData')->getIbaScoreStarNumber($type, $testSet, $score);
    }

    public function getIbaScoreCanDo($levelName){
        return $this->getEntityManager()->getRepository('Application\Entity\IbaCanDoAdvice')->getIbaScoreCanDo($levelName);
    }

    public function getIbaScoreAdvice($levelName){
        return $this->getEntityManager()->getRepository('Application\Entity\IbaCanDoAdvice')->getIbaScoreAdvice($levelName);
    }
}
