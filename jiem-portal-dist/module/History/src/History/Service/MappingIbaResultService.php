<?php
namespace History\Service;

use Application\Entity\IBATestResult;
use Application\Entity\Repository\ApplyIBAOrgRepository;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Dantai\Api\UkestukeClient;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Dantai\PrivateSession;
use History\HistoryConst;
use Dantai\Utility\MappingUtility;

class MappingIbaResultService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    const NumberQuestion = 80;

    protected $userIdentity;
    protected $organizationId = 0;
    protected $organizationNo;
    
    protected $ibaTestResultRepo;
    protected $pupilRepo;
    protected $ibaScoreRepo;
    
    protected $serviceLocator;
    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    
    private $uketukeClient;


    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager, $entityManger = Null)
    {
        $this->setServiceLocator($serviceManager);
        $this->serviceLocator = $this->getServiceLocator();
        $this->setEntityManager($entityManger);
        $user = PrivateSession::getData('userIdentity');
        $this->userIdentity = $user;
        $this->organizationId = $user['organizationId'];
        $this->organizationNo = $user['organizationNo'];
    }
    
    public function setEntityManager($entityManager){
        $this->em = $entityManager ? $entityManager : $this->serviceLocator->get('doctrine.entitymanager.orm_default');
    }
    
    public function setIbaScoreRepo($ibaScoreRepo = Null){
        $this->ibaScoreRepo = $ibaScoreRepo ? $ibaScoreRepo : $this->em->getRepository('Application\Entity\IBAScore');
    }
    
    public function setIbaTestResultRepo($ibaTestResultRepo = Null){
        $this->ibaTestResultRepo = $ibaTestResultRepo ? $ibaTestResultRepo : $this->em->getRepository('Application\Entity\IBATestResult');
    }
    
    public function setPupilRepo($pupilRepo = Null){
        $this->pupilRepo = $pupilRepo ? $pupilRepo : $this->em->getRepository('Application\Entity\Pupil');
    }
    
    public function setUketukeClient($client = '')
    {        
        if ($client) {
            $this->uketukeClient = $client;     
        } else {            
            $this->uketukeClient = UkestukeClient::getInstance();
        }   
    }
    
    public function setDataToSave($organizationNo, $organizationId, $jisshiId, $examType, $applyId = false){
        $response = array(
            'status' => HistoryConst::IMPORT_FAILED, 'message' => 'Failed',
        );
        try{
            $result = $this->getIBAExamResult($jisshiId, $examType);
            if (isset($result->kekka)) {
                $kekka = $result->kekka;
            } else {
                if (count($result) == 1 && $result->eikenArray[0]->eikenid == '') {
                    $kekka = '02';
                } else {
                    $kekka = $result->eikenArray[0]->kekka;
                }
            }
            
            if ($kekka == '10') {
                $response = $this->saveIBAExamResult($organizationNo, $organizationId, $jisshiId, $examType, $result, $applyId);
            } else if ($kekka == '02') {
                $response['status'] = HistoryConst::IMPORT_EMPTY_DATA;
                $response['message'] = 'Empty Data';
            }
        } catch (\Exception $ex) {
            $response = array(
                'status' => HistoryConst::IMPORT_FAILED,
                'message' => $ex->getMessage(),
            );
        }

        return $response;
    }
    
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

        $result = $this->uketukeClient->callEir2c02($config, $params);
        return $result;
    }
    
    private $importStatus = '';
    public function setImportStatus($mock = '')
    {
        if($mock){
            $this->importStatus = $mock;
        }else{
            $this->importStatus = $this->em->getRepository('Application\Entity\ApplyIBAOrg');
        }
    }

    public function getEikenScheduleRepo()
    {
        return $this->em->getRepository('Application\Entity\EikenSchedule');

    }
    
    public function saveIBAExamResult($orgNo, $orgId, $jisshiId, $examType, $getIBAExamResult, $applyId) {
        $response = array('status'=> HistoryConst::IMPORT_SUCCESS, 'message' => 'Success');
        $data = (array) $getIBAExamResult->eikenArray;
        
        if($applyId){
            if(!$this->importStatus){
                $this->setImportStatus();
            }
            $this->importStatus
                    ->changeStatusUpdateTotalImport($applyId, count($data), HistoryConst::IMPORTING_STATUS);
        }

        $this->em->getConnection()->beginTransaction();
        try {
            $list_IBAScore = $this->em->getRepository('Application\Entity\IBATestResult')->getListIdIbaTestResult($jisshiId, $examType);
            $this->em->getRepository('Application\Entity\IBAScore')->deleteIBAScore($list_IBAScore);
            
            $eikenLevelTotalNoList = array(
                '1' => array(
                    'id' => 1,
                    'levelName' => '準1級以上'
                ),
                '2' => array(
                    'id' => 2,
                    'levelName' => '準1級'
                ),
                '3' => array(
                    'id' => 3,
                    'levelName' => '2級'
                ),
                '4' => array(
                    'id' => 4,
                    'levelName' => '準2級'
                ),
                '5' => array(
                    'id' => 5,
                    'levelName' => '3級'
                ),
                '6' => array(
                    'id' => 6,
                    'levelName' => '4級'
                ),
                '7' => array(
                    'id' => 7,
                    'levelName' => '5級'
                ),
                '8' => array(
                    'id' => 8,
                    'levelName' => '5級受験'
                ),
            );
            //        start update function for : #GNCCNCJDR5-761
            $ibaMasterData = $this->em->getRepository('Application\Entity\IbaScoreMasterData')->getListIbaScoreMasterData(HistoryConst::IBA_RESULT_TOTAL);

            foreach ($data as $item) {
                $ibaTestResultArray[] = $this->mappingDataFromUkestuke($item, $eikenLevelTotalNoList,$ibaMasterData,$orgNo);   
            }
            foreach (array_chunk($ibaTestResultArray, HistoryConst::BATCH_UPDATE_IBA_TEST_RESULT) as $ibaTestResultBatch) {
                $this->insertOnDuplicateIbaTestResult($ibaTestResultBatch);
            }
            
            $this->updateTempValueAfterImport($orgNo, $jisshiId, $examType);
            
            $this->em->getRepository('Application\Entity\ApplyIBAOrg')
                    ->changeStatusUpdateTotalImport($applyId, count($data), HistoryConst::IMPORTED_STATUS);
            $isImported = $this->em->getRepository('Application\Entity\IBATestResult')->findBy(array('jisshiId' => $jisshiId, 'examType' => $examType, 'isDelete' => 0));
            if($isImported){
                $this->em->getRepository('Application\Entity\ApplyIBAOrg')
                    ->updateStatusMapping($applyId, HistoryConst::STATUS_WAITTING_MAP);
            }

            // update ibaScore for mapped ibaTestResult
            $ibaScoreData = $this->createIbaScoreForMappedResult($jisshiId, $examType);
            if($ibaScoreData){
                $this->insertResultMappingToIbaScore($ibaScoreData);
            }
            $this->em->flush();
            $this->em->getConnection()->commit();

             /**
             * @author minhbn1
             * add org To queue
             */
            $dantaiService = $this->serviceLocator->get('Application\Service\DantaiServiceInterface');
            $dantaiService->addOrgToQueue($this->organizationId, array_values($ibaTestResultArray)[0]['Year']);

        } catch (\Exception $ex) {
            $this->em->getConnection()->rollback();
            $response['status'] = HistoryConst::IMPORT_FAILED;
            $response['message'] = $ex->getMessage();
        }
        return $response;
    }
    
    private function mappingDataFromUkestuke($item = array(), $eikenLevelTotalNoList,$ibaMasterData = array(),$orgNo) {

        $ibaLevelName = MappingUtility::getKyuName($ibaMasterData, HistoryConst::IBA_RESULT_TOTAL, $this->checkDataBeforeTrim($item->testsyubetsu), $this->checkDataBeforeTrim($item->score_total) ? $this->checkDataBeforeTrim($item->score_total) : 0);

        $eikenLevelTotalNo = $this->getIdByEikenLevelTotalName($eikenLevelTotalNoList, $ibaLevelName);
//        end update function for : #GNCCNCJDR5-761
        
        // get data for :answerSerialize and accuraryJugdeSerialize
        $answerArray = array();
        $judgeArray = array();
        for ($i = 1; $i <= self::NumberQuestion; $i++) {
            $indexAnswer = "answer" . sprintf("%'.02d", $i);
            $indexJudge = "seigojudge" . sprintf("%'.02d", $i);
            $answerArray[] = $item->$indexAnswer;
            $judgeArray[] = $item->$indexJudge;
        }
        $answerSerialize = json_encode($answerArray);
        $accuraryJugdeSerialize = json_encode($judgeArray);
        
        $birthDate = \Dantai\Utility\DateHelper::convertJapaneseDatetoMysqlFormat($item->birthdate);
        $formatData = array(
            "ExecuteId" => $this->checkDataBeforeTrim($item->jisshiid),
            "FixationSEQ" => $this->checkDataBeforeTrim($item->kakuteiseq),
            "Year" => $this->checkDataBeforeTrim($item->nendo),
            "ExecuteManagerNo" => $this->checkDataBeforeTrim($item->jisshikanrino),
            "GroupNo" => $this->checkDataBeforeTrim($item->groupno),
            "AcquisitionNo" => $this->checkDataBeforeTrim($item->torikomino),
            "UketsukeNo" => $this->checkDataBeforeTrim($item->uketsukeno),
            "TestType" => $this->checkDataBeforeTrim($item->testsyubetsu),
            "TestSetNo" => $this->checkDataBeforeTrim($item->testsetno),
            "ExistenceListening" => $this->checkDataBeforeTrim($item->listeningumu),
            "IdAlphabet" => $this->checkDataBeforeTrim($item->id_alphabet),
            "IdNumber" => $this->checkDataBeforeTrim($item->id_number),
            "Gender" => $this->checkDataBeforeTrim($item->seibetsu),
            "NameRomanji" => $this->checkDataBeforeTrim($item->shimeiroma),
            "NameKana" => str_replace(array(' ', ' ', '　'), array('', '', ''), mb_convert_kana($this->checkDataBeforeTrim($item->shimeikana, "KVa"))),
            "TempNameKana" => str_replace(array(' ', ' ', '　'), array('', '', ''), mb_convert_kana($this->checkDataBeforeTrim($item->shimeikana, "KVa"))),
            "IndividualAttibute" => $this->checkDataBeforeTrim($item->kojinzokusei),
            "NameKanji" => str_replace(array(' ', ' ', '　'), array('', '', ''), $this->checkDataBeforeTrim($item->shimeikanji)),
            "TempNameKanji" => str_replace(array(' ', ' ', '　'), array('', '', ''), $this->checkDataBeforeTrim($item->shimeikanji)),
            "SchoolYear" => (int)$this->checkDataBeforeTrim($item->gakunen),
            "ClassCode" => $this->checkDataBeforeTrim($item->class1),
            "AttendanceNo" => $this->checkDataBeforeTrim($item->syussekino),
            "PupilNo" => $this->checkDataBeforeTrim($item->syussekino),
            "Birthday" => $birthDate ? new \DateTime(\Dantai\Utility\DateHelper::convertJapaneseDatetoMysqlFormat($item->birthdate)) : null,
            "IBACSETotal" => $this->checkDataBeforeTrim($item->score_total),
            "IBACSERead" => $this->checkDataBeforeTrim($item->score_read),
            "IBACSEListen" => $this->checkDataBeforeTrim($item->score_listen),
            "OldScoreTotal" => $this->checkDataBeforeTrim($item->old_score_total),
            "OldScoreReading" => $this->checkDataBeforeTrim($item->old_score_read),
            "OldScoreListening" => $this->checkDataBeforeTrim($item->old_score_listen),
            "RankTotal" => $this->checkDataBeforeTrim($item->rank_total),
            "RankReading" => $this->checkDataBeforeTrim($item->rank_read),
            "RankListening" => $this->checkDataBeforeTrim($item->rank_listen),
            "ExamNumber" => $this->checkDataBeforeTrim($item->jukensyasu),
            "QuestionNumberGrammar" => $this->checkDataBeforeTrim($item->quessu_goi),
            "QuestionNumberStructure" => $this->checkDataBeforeTrim($item->quessu_kosei),
            "QuestionNumberReading" => $this->checkDataBeforeTrim($item->quessu_dokkai),
            "QuestionNumberListening" => $this->checkDataBeforeTrim($item->quessu_listen),
            "QuestionNumberTotal" => $this->checkDataBeforeTrim($item->quessu_total),
            "CorrectAnswerNumberGrammar" => $this->checkDataBeforeTrim($item->seitosu_goi),
            "CorrectAnswerNumberStructure" => $this->checkDataBeforeTrim($item->seitosu_kosei),
            "CorrectAnswerNumberReading" => $this->checkDataBeforeTrim($item->seitosu_dokkai),
            "CorrectAnswerNumberListening" => $this->checkDataBeforeTrim($item->seitosu_listen),
            "CorrectAnswerNumberTotal" => $this->checkDataBeforeTrim($item->seitosu_total),
            "CorrectAnswerPercentGrammar" => $this->checkDataBeforeTrim($item->seitoritu_goi),
            "CorrectAnswerPercentStructure" => $this->checkDataBeforeTrim($item->seitoritu_kosei),
            "CorrectAnswerPercentReading" => $this->checkDataBeforeTrim($item->seitoritu_dokkai),
            "CorrectAnswerPercentListening" => $this->checkDataBeforeTrim($item->seitoritu_listen),
            "CorrectAnswerPercentTotal" => $this->checkDataBeforeTrim($item->seitoritu_total),
            "EikenKyu" => $this->checkDataBeforeTrim($item->eikenkyu),
            "Toeic" => $this->checkDataBeforeTrim($item->toeic),
            "Toefl" => $this->checkDataBeforeTrim($item->toefl),
            "ToeicBridge" => $this->checkDataBeforeTrim($item->toeic_bridge),
            "AverageScoreTotal" => $this->checkDataBeforeTrim($item->avescore_total),
            "AverageScoreReading" => $this->checkDataBeforeTrim($item->avescore_read),
            "AverageScoreListening" => $this->checkDataBeforeTrim($item->avescore_listen),
            "OldAverageScoreTotal" => $this->checkDataBeforeTrim($item->old_avescore_total),
            "OldAverageScoreReading" => $this->checkDataBeforeTrim($item->old_avescore_read),
            "OldAverageScoreListening" => $this->checkDataBeforeTrim($item->old_avescore_listen),
            "AvgCorrectPercentGrammar" => $this->checkDataBeforeTrim($item->aveseitoritu_goi),
            "AvgCorrectPercentStructure" => $this->checkDataBeforeTrim($item->aveseitoritu_kosei),
            "AvgCorrectPercentReading" => $this->checkDataBeforeTrim($item->aveseitoritu_dokkai),
            "AvgCorrectPercentListening" => $this->checkDataBeforeTrim($item->aveseitoritu_listen),
            "AvgCorrectPercentTotal" => $this->checkDataBeforeTrim($item->aveseitoritu_total),
            //             "AnswerSerialize" => $this->checkDataBeforeTrim($item->answer01),
            //             "AccuraryJugdeSerialize" => $this->checkDataBeforeTrim($item->seigojudge01),
            "EikenId" => $this->checkDataBeforeTrim($item->eikenid),
            "Password" => $this->checkDataBeforeTrim($item->password),
            "ExamDate" => new \DateTime(date('Y-m-d H:i:s', strtotime($item->testdate))),
            "ProcessDate" => new \DateTime(date('Y-m-d H:i:s', strtotime($item->shoridate))),
            "NewOldClassification" => $this->checkDataBeforeTrim($item->sinkyukbn),
            "TotalFlag" => $this->checkDataBeforeTrim($item->syukeitargetflg),
            "EikenLevelTotal" => $this->checkDataBeforeTrim($item->eikenkyulv_total),
            "EikenLevelTotalNo" => isset($eikenLevelTotalNo) ? (int)$eikenLevelTotalNo : 0,
            "EkenLevelRead" => $this->checkDataBeforeTrim($item->eikenkyulv_read),
            "EikenLevelListening" => $this->checkDataBeforeTrim($item->eikenkyulv_listen),
            "ResultDocOutput" => $this->checkDataBeforeTrim($item->seiseki_jun),
            "EikenLevelId" => (int) $this->checkDataBeforeTrim($item->eikenlevel),
            "RankDisplay" => $this->checkDataBeforeTrim($item->junihyoji),
            "RankDisplayLimit" => $this->checkDataBeforeTrim($item->junihyojiseigen),
            "TitleUpdate" => $this->checkDataBeforeTrim($item->hyodaihenko),
            "Title" => $this->checkDataBeforeTrim($item->hyodai),
            "EikenIdDisplay" => $this->checkDataBeforeTrim($item->eikenidhyoji),
            "CreateDate" => new \DateTime(date('Y-m-d H:i:s', strtotime($item->createdate))),
            "UpdateDate" => new \DateTime(date('Y-m-d H:i:s', strtotime($item->updatedate))),
            "SchoolYearName" => $this->checkDataBeforeTrim($item->gakunen),
            "ClassName" => $this->checkDataBeforeTrim($item->class1),
            "ExamType" => $item->examkbn,
            "SetName" => $item->groupnamekj,
            "JisshiId" => $item->jisshiid,
            "AnswerSerialize" => $answerSerialize,
            "AccuraryJugdeSerialize" => $accuraryJugdeSerialize,
            "OrganizationNo" => $orgNo
        );
        if (empty($formatData['NameKana'])) {
            $formatData['NameKana'] = 'サィアザゴェ';
        }
        if (empty($formatData['NameKanji'])) {
            $formatData['NameKanji'] = '';
        }

        return $formatData;
    }
    
    private function checkDataBeforeTrim($data) {
        if ($data != '') {
            return trim($data);
        } else {
            return $data;
        }
    }
    
    private function getIdByEikenLevelTotalName($list, $name) {
        foreach ($list as $eiken) {
            if ($eiken['levelName'] == $name) {
                return $eiken['id'];
                break;
            }
        }
    }
    
    public function getDataPupilResultForMapping($year) {
        $prefix = HistoryConst::DELIMITER_VALUE;
        if(!$this->pupilRepo){
            $this->setPupilRepo();
        }
        $listPupil = $this->pupilRepo->getDataByOrgAndYearAndArraySearch($this->organizationId, $year);
        /*@var $importPupilService \PupilMnt\Service\ImportPupilService */
        $importPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');  
        $pupils = array();
        if ($listPupil) {
            foreach ($listPupil as $value) {
                $birthday = !empty($value['birthday']) ? $value['birthday']->format('Y-m-d') : '';
                $nameKana = trim($value['firstNameKana']) . trim($value['lastNameKana']);
                $nameKana = $importPupilService->convertKanaHalfWidthToFullWidth($nameKana);
                $keyMapping = $nameKana . $prefix . $birthday;
                $pupils[$keyMapping][] = $value;
            }
        }
        return $pupils;
    }
    
    public function getDataMapping($ibaTestResults, $pupils) {
        /* @var $importPupilService \PupilMnt\Service\ImportPupilService */
        $importPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $prefix = HistoryConst::DELIMITER_VALUE;
        $mappingSuccess = array();
        $newIBAScore = array();
        foreach ($ibaTestResults as $value) {
            $nameKana = trim($value['nameKana']);
            $nameKana = $importPupilService->convertKanaHalfWidthToFullWidth($nameKana);
            $birthday = !empty($value['birthday']) ? $value['birthday']->format('Y-m-d') : '';

            $arrKeyMapping = array(
                $nameKana . $prefix . $birthday,
                $nameKana . $prefix . '',
            );
            foreach ($arrKeyMapping as $keyMapping) {
                if (array_key_exists($keyMapping, $pupils)) {
                    $pupil = $pupils[$keyMapping][0];
                    $ibaTestResultId = $value['id'];
                    $mappingSuccess[$ibaTestResultId] = array(
                        'orgSchoolYearId' => $pupil['orgSchoolYearId'],
                        'orgSchoolYearName' => $pupil['orgSchoolYearName'],
                        'classId' => $pupil['classId'],
                        'className' => $pupil['className'],
                        'tempNameKanji' => trim($pupil['firstNameKanji']) . trim($pupil['lastNameKanji']),
                        'nameKana' => trim($pupil['firstNameKana']) . trim($pupil['lastNameKana']),
                        'pupilId' => $pupil['id'],
                        'pupilNumber' => $pupil['number'],
                        'birthday' => !empty($pupil['birthday']) ? $pupil['birthday']->format('Y-m-d 00:00:00') : Null,
                    );
                    $newIBAScore[$ibaTestResultId] = $this->convertDataToInsertToIBAScore($value, $pupil);
                    break;
                }
            }
        }
        return array($mappingSuccess, $newIBAScore);
    }
    
    public function convertDataToInsertToIBAScore($ibaTestResult, $pupil){
        $eikenLevelId = isset($ibaTestResult['eikenLevelTotalNo']) ? $ibaTestResult['eikenLevelTotalNo'] : 0;
        $eikenLevelId = $eikenLevelId == 8 ? 7 : $eikenLevelId;
        
        $ibaScore = array(
            'PupilId' => $pupil['id'],
            'EikenLevelId' => intval($eikenLevelId),
            'Year' => $ibaTestResult['year'],
            'ExamDate' => $ibaTestResult['examDate'] != Null ? (is_object($ibaTestResult['examDate']) ? $ibaTestResult['examDate']->format('Y-m-d 00:00:00') : $ibaTestResult['examDate']) : Null,
            'ReadingScore' => $ibaTestResult['read'],
            'ListeningScore' => $ibaTestResult['listen'],
            'IBACSETotal' => $ibaTestResult['total'],
            'PassFailFlag' => intval($ibaTestResult['isPass']),
            'IbaTestResultId' => $ibaTestResult['id'],
            'Status' => 'Active'
        );
        return $ibaScore;
    }
    
    public function mappingDataIbaResult($year, $jisshiId, $examType) {
        if(empty($year) || empty($jisshiId) || empty($examType)){
            return array('status' => 0, 'message' => 'Empty Year, jisshiId or examType');
        }
        $response = array('status' => 1, 'message' => '');
        $this->updateStatusApplyIbaOrg($jisshiId, $examType, HistoryConst::STATUS_MAPPING);
        
        if (!$this->ibaTestResultRepo) {
            $this->setIbaTestResultRepo();
        }  
        $ibaTestResults = $this->ibaTestResultRepo->getListIbaTestResult($jisshiId, $examType, $isMapped = false);
        $pupils = $this->getDataPupilResultForMapping($year);
        list($mappingSuccess, $ibaScores) = $this->getDataMapping($ibaTestResults, $pupils);
        try{
            if ($mappingSuccess) {
                $this->updateIbaTestResult($mappingSuccess);
            }
            if($ibaScores){
                $this->insertResultMappingToIbaScore($ibaScores);
            }
            if(!empty($ibaTestResults)){
                $this->updateIsMappedWidthIds($ibaTestResults);
            }
            $this->updateStatusApplyIbaOrg($jisshiId, $examType, HistoryConst::STATUS_MAPPED);
            $this->updateStatusAutoImportOfRound($jisshiId, $examType);
        } catch (\Exception $ex) {
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
        }
        return $response;
    }
    
    public function updateIbaTestResult($ibaTestResults) {
        $batch = HistoryConst::BATCH_UPDATE_IBA_TEST_RESULT;
        if (!$this->ibaTestResultRepo) {
            $this->setIbaTestResultRepo();
        }
        if (count($ibaTestResults) <= $batch) {
            $result = $this->ibaTestResultRepo->updateMultipleRowsWithEachId($ibaTestResults);
            return $result;
        }

        for ($i = 0; $i < count($ibaTestResults); $i = $i + $batch) {
            $ibaResultsSlice = array_slice($ibaTestResults, $i, $batch, true);
            if ($ibaResultsSlice) {
                $result = $this->ibaTestResultRepo->updateMultipleRowsWithEachId($ibaTestResults);
            }
        }
        return true;
    }
    
    public function insertResultMappingToIbaScore($ibaScores){
        $batch = HistoryConst::BATCH_UPDATE_IBA_TEST_RESULT;
        if(!$this->ibaScoreRepo){
            $this->setIbaScoreRepo();
        }
        if (count($ibaScores) <= $batch) {
            $result = $this->ibaScoreRepo->insertMultipleRows($ibaScores);
        } else {
            for ($i = 0; $i < count($ibaScores); $i = $i + $batch) {
                $ibaScoresSlice = array_slice($ibaScores, $i, $batch);
                if ($ibaScoresSlice) {
                    $result = $this->ibaScoreRepo->insertMultipleRows($ibaScoresSlice);
                }
            }
        }

        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        $dantaiService->addOrgToQueue($this->organizationId, array_values($ibaScores)[0]['Year']);
        
        return $result;
    }
    
    public function updateStatusApplyIbaOrg($jisshiId, $examType, $status){
        $applyIbaId = PrivateSession::getData('applyIbaId');
        if(!$applyIbaId){
            $applyIbaOrg = $this->em->getRepository('Application\Entity\ApplyIBAOrg')->findOneBy(array(
                'jisshiId' => $jisshiId,
                'examType' => $examType,
            ));
        }else {
            $applyIbaOrg = $this->em->getRepository('Application\Entity\ApplyIBAOrg')->find($applyIbaId);
        }
        if (!empty($applyIbaOrg)) {
            $applyIbaOrg->setStatusMapping($status);
            $this->em->persist($applyIbaOrg);
            $this->em->flush();
        }
        return true;
    }
    
    public function updateStatusAutoImportOfRound($jisshiId, $examType) {
        /*@var $applyIbaOrg \Application\Entity\ApplyIBAOrg*/
        $applyIbaOrg = $this->em->getRepository('Application\Entity\ApplyIBAOrg')->findOneBy(array(
            'jisshiId' => $jisshiId,
            'examType' => $examType
        ));
        if (!empty($applyIbaOrg)) {
            $applyIbaOrg->setStatusAutoImport(HistoryConst::IBA_CONFIRMED);
            $this->em->persist($applyIbaOrg);
            $this->em->flush();
            return true;
        }
        return false;
    }

    public function updateConfirmStatus($ids)
    {
        try {
            foreach ($ids as $item) {
                $ibaResult = $this->em->getRepository('Application\Entity\IBATestResult')->findOneBy(array('id' => $item, 'organizationNo' => $this->organizationNo));
                if (!$ibaResult) {
                    return HistoryConst::SAVE_TO_DATABASE_FAIL;
                }
                $ibaResult->setMappingStatus(HistoryConst::CONFIRMED_STATUS);
                $this->em->persist($ibaResult);
            }
            $this->em->flush();

            return HistoryConst::SAVE_TO_DATABASE_SUCCESS;
        }
        catch (Exception $e) {
            return HistoryConst::SAVE_TO_DATABASE_FAIL;
        }
    }
    
    private $applyIBA = '';
    public function getApplyIBAMock($mock = '')
    {
        if($mock){
            $this->applyIBA = $mock;
        }else{
            $this->applyIBA = $this->em->getRepository('Application\Entity\ApplyIBAOrg');
        }
    }
    
    public function getIBAApply($jisshiId, $examType)
    {
        if(!$this->applyIBA){
            $this->getApplyIBAMock();
        }
        $ibaApply = $this->applyIBA->findOneBy(array('organizationId' => $this->organizationId, 'jisshiId' => $jisshiId, 'examType' => $examType));

        if(!$ibaApply){
            return HistoryConst::CANNOT_FIND_DATA;
        }
        return $ibaApply;
    }
    
    private $ibaResult = '';
    public function getIBAResult($mock = '')
    {
        if($mock){
            $this->ibaResult = $mock;
        }else{
            $this->ibaResult = $this->em->getRepository('Application\Entity\IBATestResult');
        }
    }
    
    public function getListMappingIBAResult($year, $jisshiId, $examType, $schoolYearId = '', $classId = '', $nameKana = '', $status = '')
    {
        if(!$this->ibaResult){
            $this->getIBAResult();
        }
        $data = $this->ibaResult->getIBAResultList($this->organizationNo, $year, $jisshiId, $examType, $schoolYearId, $classId, $nameKana, $status);
        
        if(!$data){
            return HistoryConst::CANNOT_FIND_DATA;
        }
        return $data;
    }
    
    private $status = '';
    public function getMappingStatus($mock = '')
    {
        if($mock){
            $this->status = $mock;
        }else{
            $this->status = $this->em->getRepository('Application\Entity\IBATestResult');
        }
    }
    
    public function countMappingStatus($jisshiId, $examType)
    {
        if(!$this->status){
            $this->getMappingStatus();
        }
        $mappingStatus = $this->status->getTotalMappingStatus($jisshiId, $examType, $this->organizationNo);
        
        if(!$mappingStatus){
            return HistoryConst::CANNOT_FIND_DATA;
        }
        return $mappingStatus;
    }
    

    public function getListSchoolYear()
    {
        return $this->em->getRepository('Application\Entity\OrgSchoolYear')->listSchoolYearName($this->organizationId);
    }
    
    public function getListClassBySchoolYear($schoolYearId, $year)
    {
        return $this->em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYear($schoolYearId, $year, $this->organizationId);
    }
    
    public function checkPupilByYear($year)
    {
        return $this->em->getRepository('Application\Entity\Pupil')->findBy(array('organizationId' => $this->organizationId, 'year' => $year));
    }
    
    public function deleteMapping($ibaTestResultId){
        $response = array('status' => 1, 'message' => 'Success');
        if (!$this->ibaScoreRepo) {
            $this->setIbaScoreRepo();
        }

        /* @var $ibaTestResult \Application\Entity\IBATestResult */
        $ibaTestResult = $this->em->getRepository('Application\Entity\IBATestResult')->findOneBy(array('id' => $ibaTestResultId, 'isDelete' => 0));

        $mappingDelete[$ibaTestResultId] = array(
            'orgSchoolYearId' => Null,
            'orgSchoolYearName' => empty($ibaTestResult) ? null : $ibaTestResult->getSchoolYear(),
            'tempSchoolYearName' => null,
            'classId' => Null,
            'className' => empty($ibaTestResult) ? null : $ibaTestResult->getClassCode(),
            'tempClassName' => null,
            'tempNameKanji' => $ibaTestResult->getNameKanji(),
            'nameKana' => $ibaTestResult->getNameKana(),
            'pupilId' => Null,
            'pupilNumber' => Null,
            'birthday' => Null,
            'isDeleteMapping' => 1
        );

        try{
            $this->updateIbaTestResult($mappingDelete);
            $ibaScoreRemove = $this->ibaScoreRepo->findOneBy(
                array('ibaTestResultId' => $ibaTestResultId)
            );
            if ($ibaScoreRemove) {
                $this->em->remove($ibaScoreRemove);
                $this->em->flush();
            }
            $this->updateConfirmStatus(array($ibaTestResultId));
            return $response;
        } catch (\Exception $ex) {
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
        }
        return $response;
    }
    
    public function confirmMapping($ibaTestResultId, $pupilId){
        $response = array(
            'status' => 1, 'message' => 'Successs'
        );
        if (intval($ibaTestResultId) <= 0) {
            $response['status'] = 0;
            $response['message'] = 'Emtpy IbaTestResult OR Pupil';
            return $response;
        }
        if(intval($pupilId) == 0){
            try {
                $this->updateConfirmStatus(array($ibaTestResultId));
            } catch (\Exception $ex) {
                $response['status'] = 0;
                $response['message'] = $ex->getMessage();
            }
            return $response;
        }
        if(!$this->ibaTestResultRepo){
            $this->setIbaTestResultRepo();
        }
        if(!$this->pupilRepo){
            $this->setPupilRepo();
        }
        if(!$this->ibaScoreRepo){
            $this->setIbaScoreRepo();
        }
        /*@var $eikenTestResult \Application\Entity\EikenTestResult*/
        $ibaTestResult = $this->ibaTestResultRepo->find($ibaTestResultId);
        /*@var $pupil \Application\Entity\Pupil*/
        $pupil = $this->pupilRepo->find($pupilId);
        if(!$ibaTestResult || !$pupil){
            $response['status'] = 0;
            $response['message'] = 'Emtpy IbaTestResult OR Pupil';
            return $response;
        }
        $mappingSuccess[$ibaTestResultId] = array(
            'orgSchoolYearId' => $pupil->getOrgSchoolYearId(),
            'orgSchoolYearName' => $pupil->getOrgSchoolYear()->getDisplayName(),
            'classId' => $pupil->getClassId(),
            'className' => $pupil->getClass()->getClassName(),
            'tempNameKanji' => $pupil->getFirstNameKanji() . $pupil->getLastNameKanji(),
            'nameKana' => $pupil->getFirstNameKana() . $pupil->getLastNameKana(),
            'pupilId' => $pupilId,
            'pupilNumber' => $pupil->getNumber(),
            'birthday' => $pupil->getBirthday() != Null ? $pupil->getBirthday()->format('Y-m-d 00:00:00') : Null,
        );
        $ibaTestResultArray = $ibaTestResult->toArray(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT);
        $pupilArray = $pupil->toArray(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT);
        $ibaScores[$ibaTestResultId] = $this->convertDataToInsertToIBAScore($ibaTestResultArray, $pupilArray);
        try {
            $ibaScoreRemove = $this->ibaScoreRepo->findOneBy(
                    array('ibaTestResultId' => $ibaTestResultId, 'pupilId' => $pupilId)
            );
            if ($ibaScoreRemove) {
                $this->em->remove($ibaScoreRemove);
                $this->em->flush();
            }
            $this->updateIbaTestResult($mappingSuccess);
            $this->insertResultMappingToIbaScore($ibaScores);
            $this->updateConfirmStatus(array($ibaTestResultId));
        } catch (\Exception $ex) {
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
        }
        return $response;

    }

    public function updateOneIBAHeader($orgNo, $jisshiId, $examType){
        try {
            $config = $this->serviceLocator->get('Config')['iba_config']['api'];
            $result = $this->uketukeClient->callEir2c03($config,array(
                'dantaino'  => $orgNo,
            ));
            if (isset($result->kekka)) {
                $kekka = $result->kekka;
            } else {
                if (!isset($result->eikenArray)) {
                    $kekka = '99'; // error
                } elseif (count($result->eikenArray) == 1 && $result->eikenArray[0]->jisshiid == '') {
                    $kekka = '02'; // empty data
                } else {
                    $kekka = $result->eikenArray[0]->kekka;
                }
            }

            if ($kekka == '10') {
                $arrayData = !empty($result->eikenArray) ? json_decode(json_encode($result->eikenArray), true) : array();
                $ibaHeaderData = null;
                foreach ($arrayData as $item) {
                    if($item['jisshiid'] == $jisshiId && $item['examkbn'] == $examType){
                        $ibaHeaderData = $item;
                        break;
                    }
                }
                if(empty($ibaHeaderData)){
                    return false;
                }
                $ibaOrgRepo = $this->getIbaOrgRepo();
                $ibaOrgRepo->updateFlagNewDataIBAHeader($orgNo, $this->userIdentity['userId'], array($ibaHeaderData));
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function getParamsSearch($searchCriteria = array())
    {
        return $searchCriteria;
    }
    
    public function getIBATestResult($id)
    {
        if (empty($id)) {
            return false;
        }
        return $this->em->getRepository('Application\Entity\IBATestResult')->findOneBy(array('id' => $id, 'isDelete' => 0, 'organizationNo' => $this->organizationNo));
    }

    /**
     * @return ApplyIBAOrgRepository
     */
    public function getIbaOrgRepo(){
        return isset($this->applyIbaOrgRepo) ? $this->applyIbaOrgRepo : $this->em->getRepository('\Application\Entity\ApplyIBAOrg');
    }
    
    public function isNoNameKanna($year)
    {      
        $pupilList = $this->em->getRepository('Application\Entity\Pupil')->getListEmptyNameKana($this->organizationId, $year);        
        return ($pupilList->count()>0) ? TRUE : FALSE;
    }
    
        
    public function updateIsMappedWidthIds($ibaTestResults) {
        if (!$this->ibaTestResultRepo) {
            $this->setIbaTestResultRepo();
        }
        if ($ibaTestResults) {
            $ids = array();
            foreach ($ibaTestResults as $row) {
                if (!in_array($row['id'], $ids)) {
                    array_push($ids, $row['id']);
                }
            }

            $this->ibaTestResultRepo->updateIsMappedWidthIds($ids);
            return true;
        }

        return false;
    }
    
    public function insertOnDuplicateIbaTestResult($data){
        if(empty($this->ibaTestResultRepo)){
            $this->setIbaTestResultRepo();
        }
        return $this->ibaTestResultRepo->insertOnDuplicateUpdateMultiple($data);
    }
    
    public function updateTempValueAfterImport($orgNo, $jisshiId, $examType){
        if(empty($this->ibaTestResultRepo)){
            $this->setIbaTestResultRepo();
        }
        return $this->ibaTestResultRepo->updateTempValueAfterImport($orgNo, $jisshiId, $examType);
    }

    public function createIbaScoreForMappedResult($jisshiId, $examType){
        // get list ibaTestResult which has pupilId
        $ibaTestResults = $this->ibaTestResultRepo->getListIbaTestResult($jisshiId, $examType, $isMapped = true);
        $ibaScoreArray = array();

        foreach ($ibaTestResults as $result) {
            $pupil = array(
                'id' => $result['pupilId'],
            );
            $ibaScoreArray[$result['id']] = $this->convertDataToInsertToIBAScore($result, $pupil);
        }

        return $ibaScoreArray;
    }

}