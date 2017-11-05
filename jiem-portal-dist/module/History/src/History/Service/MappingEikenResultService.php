<?php
namespace History\Service;

use Application\Entity\EikenTestResult;
use Symfony\Component\Config\Definition\Exception\Exception;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Dantai\Api\UkestukeClient;
use Dantai\PrivateSession;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use History\HistoryConst;
use Application\Entity\Repository\PupilRepository;

class MappingEikenResultService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    protected $serviceLocator;
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;
    protected $organizationId;
    protected $organizationNo;
    protected $eikenTestResultRepo;
    protected $eikenScoreRepo;
    protected $pupilRepo;


    private $uketukeClient;
    private $eikenList;
    private $delEikenScore;
    private $delEikenResult;
    private $updateImportStatus;
    private $isImport;
    private $updateMappingStatus;

    /**
     * @return \Application\Entity\Repository\PupilRepository
     */
    public function getPupilRepository(){
        return $this->em->getRepository('Application\Eitity\Pupil');
    }

    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager, $entityManger = Null)
    {
        $this->setServiceLocator($serviceManager);
        $this->serviceLocator = $this->getServiceLocator();
        $this->setEntityManager($entityManger);
        $user = PrivateSession::getData('userIdentity');
        $this->organizationId = $user['organizationId'];
        $this->organizationNo = $user['organizationNo'];
    }

    public function setEntityManager($entityManager){
        $this->em = $entityManager ? $entityManager : $this->serviceLocator->get('doctrine.entitymanager.orm_default');
    }

    public function setEikenScoreRepo($eikenScoreRepo = Null){
        $this->eikenScoreRepo = $eikenScoreRepo ? $eikenScoreRepo : $this->em->getRepository('Application\Entity\EikenScore');
    }

    public function setEikenTestResultRepo($eikenTestResultRepo = Null){
        $this->eikenTestResultRepo = $eikenTestResultRepo ? $eikenTestResultRepo : $this->em->getRepository('Application\Entity\EikenTestResult');
    }

    public function setPupilRepo($pupilRepo = Null){
        $this->pupilRepo = $pupilRepo ? $pupilRepo : $this->em->getRepository('Application\Entity\Pupil');
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
                $nameKanji = trim($value['firstNameKanji']) . trim($value['lastNameKanji']);
                $nameKana = trim($value['firstNameKana']) . trim($value['lastNameKana']);
                $nameKana = $importPupilService->convertKanaHalfWidthToFullWidth($nameKana);
                $keyMapping = $nameKanji . $prefix . $nameKana . $prefix . $birthday;
                $pupils[$keyMapping][] = $value;
            }
        }
        return $pupils;
    }

    public function getDataSchoolYearCode(){
        $schoolYearCodes = array();
        $schoolYearIds = array();
        $listOrgSchoolYear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->organizationId);
        foreach($listOrgSchoolYear as $value){
            $schoolYearIds[] = $value['schoolYearId'];
        }
        $schoolYearIds = array_unique($schoolYearIds);
        $listSchoolYearCode = $this->em->getRepository('Application\Entity\SchoolYearMapping')->getAllDataByArraySchoolYearIds($schoolYearIds);
        if($listSchoolYearCode){
            foreach($listSchoolYearCode as $value){
                $key = $value['schoolYearId']. '_' . $value['orgCode'];
                $schoolYearCodes[$key] = $value['schoolYearCode'];
            }
        }
        return $schoolYearCodes;
    }

    public function getDataMapping($eikenTestResults, $pupils, $schoolYearCodes) {
        /* @var $importPupilService \PupilMnt\Service\ImportPupilService */
        $importPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $prefix = HistoryConst::DELIMITER_VALUE;
        $mappingSuccess = array();
        $newEikenScore = array();
        foreach ($eikenTestResults as $value) {
            $nameKanji = trim($value['tempNameKanji']);
            $nameKana = trim($value['nameKana']);
            $nameKana = $importPupilService->convertKanaHalfWidthToFullWidth($nameKana);
            $birthday = !empty($value['birthday']) ? $value['birthday']->format('Y-m-d') : '';

            $arrKeyMapping = array(
                $nameKanji . $prefix . $nameKana . $prefix . $birthday,
                $nameKanji . $prefix . $nameKana . $prefix . '',
                $nameKanji . $prefix . '' . $prefix . $birthday,
                $nameKanji . $prefix . '' . $prefix . '',
            );
            
            foreach($arrKeyMapping as $keyMapping){
                if (array_key_exists($keyMapping, $pupils)) {
                    $pupil = $pupils[$keyMapping][0];
                    $keySchoolYearCode = $pupil['orgSchoolYearId'] . '_' . $value['schoolClassification'];
                    $schoolYearCode = isset($schoolYearCodes[$keySchoolYearCode]) ? $schoolYearCodes[$keySchoolYearCode] : 0;
                    $eikenTestResultId = $value['id'];
                    $mappingSuccess[$eikenTestResultId] = array(
                        'orgSchoolYearId' => $pupil['orgSchoolYearId'],
                        'orgSchoolYearName' => $pupil['orgSchoolYearName'],
                        'orgSchoolYearCode' => $schoolYearCode,
                        'classId' => $pupil['classId'],
                        'className' => $pupil['className'],
                        'nameKanji' => trim($pupil['firstNameKanji']) . trim($pupil['lastNameKanji']),
                        'nameKana' => trim($pupil['firstNameKana']) . trim($pupil['lastNameKana']),
                        'pupilId' => $pupil['id'],
                        'pupilNumber' => $pupil['number'],
                        'birthday' => !empty($pupil['birthday']) ? $pupil['birthday']->format('Y-m-d 00:00:00') : Null,
                    );
                    $newEikenScore[$eikenTestResultId] = $this->convertDataToInsertToEikenScore($value, $pupil);
                    break;
                }
            }
        }
        return array($mappingSuccess, $newEikenScore);
    }
    
    public function convertDataToInsertToEikenScore($eikenTestResult, $pupil){
        $total = intval($eikenTestResult['cSEScoreReading']) + intval($eikenTestResult['cSEScoreListening']) + intval($eikenTestResult['cSEScoreWriting']) + intval($eikenTestResult['cSEScoreSpeaking']);
        $eikenScore = array(
            'EikenLevelId' => $eikenTestResult['eikenLevelId'],
            'Year' => $eikenTestResult['year'],
            'Kai' => $eikenTestResult['kai'],
            'ReadingScore' => $eikenTestResult['cSEScoreReading'],
            'ListeningScore' => $eikenTestResult['cSEScoreListening'],
            'CSEScoreWriting' => $eikenTestResult['cSEScoreWriting'],
            'CSEScoreSpeaking' => $eikenTestResult['cSEScoreSpeaking'],
            'EikenCSETotal' => $total,
            'PrimaryPassFailFlag' => $eikenTestResult['primaryPassFailFlag'],
            'SecondPassFailFlag' => $eikenTestResult['secondPassFailFlag'],
            'EikenTestResultId' => $eikenTestResult['id'],
            'PupilId' => $pupil['id'],
            'PassFailFlag' => intval($eikenTestResult['isPass']),
            'CertificationDate' => $eikenTestResult['certificationDate'] != Null ? (is_object($eikenTestResult['certificationDate']) ? $eikenTestResult['certificationDate']->format('Y-m-d 00:00:00') : $eikenTestResult['certificationDate']) : NULL ,
            'Status' => 'Active'
        );
        return $eikenScore;
    }

    public function mappingDataEikenResult($year, $kai) {
        if(empty($year) || empty($kai)){
            return array('status' => 0, 'message' => 'Empty Year Or Kai');
        }
        $response = array('status' => 1, 'message' => '');
        $this->updateStatusApplyEikenOrg($year, $kai, HistoryConst::STATUS_MAPPING);

        if (!$this->eikenTestResultRepo) {
            $this->setEikenTestResultRepo();
        }
        $eikenTestResults = $this->eikenTestResultRepo->getEikenTestResult($year, $kai, $this->organizationNo, $isMapped = false);

        $pupils = $this->getDataPupilResultForMapping($year);
        $schoolYearCodes = $this->getDataSchoolYearCode();

        list($mappingSuccess, $eikenScores) = $this->getDataMapping($eikenTestResults, $pupils, $schoolYearCodes, $year);
        try{
            if ($mappingSuccess) {
                $this->updateEikenTestResult($mappingSuccess);
            }
            if($eikenScores){
                $this->insertResultMappingToEikenScore($eikenScores);
            }
            if(!empty($eikenTestResults)){
                $this->updateIsMappedWidthIds($eikenTestResults);
            }
            $this->updateStatusApplyEikenOrg($year, $kai, HistoryConst::STATUS_MAPPED);
            $this->updateStatusAutoImportOfRound($year, $kai);
        } catch (\Exception $ex) {
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
        }
        return $response;
    }

    public function updateEikenTestResult($eikenTestResults) {
        $batch = HistoryConst::BATCH_UPDATE_EIKEN_TEST_RESULT;
        if (!$this->eikenTestResultRepo) {
            $this->setEikenTestResultRepo();
        }
        if (count($eikenTestResults) <= $batch) {
            $result = $this->eikenTestResultRepo->updateMultipleRowsWithEachId($eikenTestResults);
            return $result;
        }

        for ($i = 0; $i < count($eikenTestResults); $i = $i + $batch) {
            $eikenResultsSlice = array_slice($eikenTestResults, $i, $batch, true);
            if ($eikenResultsSlice) {
                $result = $this->eikenTestResultRepo->updateMultipleRowsWithEachId($eikenResultsSlice);
            }
        }
        return true;
    }

    public function insertResultMappingToEikenScore($eikenScores) {
        $batch = HistoryConst::BATCH_UPDATE_EIKEN_TEST_RESULT;
        if (!$this->eikenScoreRepo) {
            $this->setEikenScoreRepo();
        }
        if (count($eikenScores) <= $batch) {
            $result = $this->eikenScoreRepo->insertMultipleRows($eikenScores);
        } else {
            for ($i = 0; $i < count($eikenScores); $i = $i + $batch) {
                $eikenScoresSlice = array_slice($eikenScores, $i, $batch);
                if ($eikenScoresSlice) {
                    $result = $this->eikenScoreRepo->insertMultipleRows($eikenScoresSlice);
                }
            }
        }

        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        $dantaiService->addOrgToQueue($this->organizationId, array_values($eikenScores)[0]['Year']);

        return $result;
    }

    public function updateStatusApplyEikenOrg($year, $kai, $status){
        $applyEikenId = PrivateSession::getData('applyEikenId');
        if(!$applyEikenId){
            $eikenSchedule = $this->em->getRepository('Application\Entity\EikenSchedule')->findOneBy(array(
                'year' => $year,
                'kai' => $kai,
            ));
            $applyEikenOrg = $this->em->getRepository('Application\Entity\ApplyEikenOrg')->findOneBy(array(
                'eikenScheduleId' => $eikenSchedule->getId(),
                'organizationId' => $this->organizationId,
            ));
            if (!empty($applyEikenOrg)) {
                $applyEikenOrg->setStatusMapping($status);
                $this->em->persist($applyEikenOrg);
                $this->em->flush();
            }
            return true;
        }
        $applyEikenOrg = $this->em->getRepository('Application\Entity\ApplyEikenOrg')->find($applyEikenId);
        if (!empty($applyEikenOrg)) {
            $applyEikenOrg->setStatusMapping($status);
            $this->em->persist($applyEikenOrg);
            $this->em->flush();
        }
        return true;
    }

    public function setUketukeClient($client = '')
    {
        if ($client) {
            $this->uketukeClient = $client;
        } else {
            $this->uketukeClient = UkestukeClient::getInstance();
        }
    }


    public function getEikenExamResult($organizationNo, $year, $term) {
        $config = $this->serviceLocator->get('Config')['eiken_config']['api'];
        // api parameters
        $params = array(
            "dantaino" => $organizationNo,
            "nendo" => $year,
            "kai" => $term
        );
        if (!$this->uketukeClient) {
            $this->setUketukeClient();
        }

        $result = $this->uketukeClient->callEir2b01($config, $params);
        return $result;
    }

    public function resetExamStatus($applyEikenOrgId = 0) {
        $examById = $this->getApplyEikenOrgById($applyEikenOrgId);
        if ($examById) {
            $examById->setStatusMapping(0);
            $examById->setStatusImporting(0);
            $examById->setTotalImport(0);
            try {
                $this->em->persist($examById);
                $this->em->flush();

                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    public function getApplyEikenOrgById($applyEikenOrgId = 0) {
        if($applyEikenOrgId){
            return $this->em->getRepository('Application\Entity\ApplyEikenOrg')->find($applyEikenOrgId);
        }else{
            return 0;
        }
    }

    public function getStatusMappingByExamId($applyEikenOrgId = 0) {
        $examById = $this->getApplyEikenOrgById($applyEikenOrgId);
        if ($examById) {
            return $examById->getStatusMapping() ? 1 : 0;
        }
        return 0;
    }

    public function setDataToSave($organizationNo, $organizationId, $year, $term) {
        $response = array(
            'status' => HistoryConst::IMPORT_FAILED, 'message' => 'Failed',
        );
        try {
            $config = $this->getServiceLocator()->get('Config')['eiken_config']['api'];
            // api parameters
            $params = array(
                "dantaino" => $organizationNo,
                "nendo" => $year,
                "kai" => $term
            );
            if (!$this->uketukeClient) {
                $this->setUketukeClient();
            }
            $eikenScheduleId = $this->em->getRepository('Application\Entity\EikenSchedule')->getIdByYearKai($year, $term);
            $result = $this->uketukeClient->callEir2b01($config, $params);

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
                $response = $this->saveEikenExamResult($result, $organizationNo, $organizationId, $eikenScheduleId, $year, $term);
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
    
    /*
     * Set mock
     */
    public function setEikenList($mock = '')
    {
        if ($mock) {
            $this->eikenList = $mock;
        } else {
            $this->eikenList = $this->em->getRepository('Application\Entity\EikenTestResult');
        }
    }
    public function deleteEikenScore($mock = '')
    {
        if ($mock) {
            $this->delEikenScore = $mock;
        } else {
            $this->delEikenScore = $this->em->getRepository('Application\Entity\EikenScore');
        }
    }
    public function deleteEikenResult($mock = '')
    {
        if ($mock) {
            $this->delEikenResult = $mock;
        } else {
            $this->delEikenResult = $this->em->getRepository('Application\Entity\EikenTestResult');
        }
    }
    public function updateImportStatus($mock = '')
    {
        if ($mock) {
            $this->updateImportStatus = $mock;
        } else {
            $this->updateImportStatus = $this->em->getRepository('Application\Entity\ApplyEikenOrg');
        }
    }
    public function checkImportStatus($mock = '')
    {
        if ($mock) {
            $this->isImport = $mock;
        } else {
            $this->isImport = $this->em->getRepository('Application\Entity\EikenTestResult');
        }
    }
    public function updateMappingStatus($mock = '')
    {
        if ($mock) {
            $this->updateMappingStatus = $mock;
        } else {
            $this->updateMappingStatus = $this->em->getRepository('Application\Entity\ApplyEikenOrg');
        }
    }
    /*
     * End set mock
     */
    public function saveEikenExamResult($getEikenExamResult, $organizationNo, $organizationId, $eikenScheduleId, $year, $kai) {
        $response = array('status'=> HistoryConst::IMPORT_SUCCESS, 'message' => 'Success');
        $data = (array) $getEikenExamResult->eikenArray;
        
        $this->em->getConnection()->beginTransaction();
        
        if ($organizationId != '' && $eikenScheduleId != '') {
            if (!$this->updateImportStatus) {
                $this->updateImportStatus();
            }
            $this->updateImportStatus
                ->updateStatusAndTotalImporting($organizationId, $eikenScheduleId, count($data), HistoryConst::IMPORTING_STATUS);
        }

        try {
            if (!$this->eikenList) {
                $this->setEikenList();
            }
            $list_EikenScore = $this->eikenList->getListIdEikenTestResult($kai, $year, $organizationNo);

            if (!$this->delEikenScore) {
                $this->deleteEikenScore();
            }
            $this->delEikenScore->deleteEikenScore($list_EikenScore);

            if (!$this->delEikenResult) {
                $this->deleteEikenResult();
            }
            $listOrgSchoolYear = $this->getListSchoolYearMappingByOrgNo($organizationNo);
            $eikenTestResultArray = array();
            // create data from API
            foreach ($data as $item) {
                // check null birthday
                if ($item->birthday == null) {
                    $item->birthday = null;
                }
                $eikenTestResultArray[] = $this->mappingDataFromUkestuke($item, $listOrgSchoolYear);
            }
            // insert into DB
            foreach (array_chunk($eikenTestResultArray, HistoryConst::BATCH_UPDATE_EIKEN_TEST_RESULT) as $subArray) {
                $this->insertOnDuplicateUpdateMultiple($subArray);
            }
            $this->updateTempValueAfterImport($organizationNo, $year, $kai);


            if ($organizationId != '' && $eikenScheduleId != '') {
                if (!$this->updateImportStatus) {
                    $this->updateImportStatus();
                }
                $this->updateImportStatus
                    ->updateStatusAndTotalImporting($organizationId, $eikenScheduleId, count($data), HistoryConst::IMPORTED_STATUS);

                if (!$this->isImport) {
                    $this->checkImportStatus();
                }
                $isImported = $this->isImport->findBy(array('organizationNo' => $organizationNo, 'year' => $year, 'kai' => $kai, 'isDelete' => 0));

                if($isImported){
                    if (!$this->updateMappingStatus) {
                        $this->updateMappingStatus();
                    }
                    $this->updateMappingStatus
                        ->updateStatusMapping($organizationId, $eikenScheduleId, HistoryConst::STATUS_WAITTING_MAP);
                }
            }

            // update eikenScore for mapped EikenTestResult
            $eikenScoreData = $this->createEikenScoreForMappedResult($year, $kai, $this->organizationNo);
            if($eikenScoreData){
                $this->insertResultMappingToEikenScore($eikenScoreData);
            }
            $this->em->flush();
            $this->em->getConnection()->commit();

            /**
             * @author minhbn1
             * add org To queue
             */
            $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
            $dantaiService->addOrgToQueue($this->organizationId, $year);
            //
        } catch (\Exception $ex) {
            $this->em->getConnection()->rollback();
            $response['status'] = HistoryConst::IMPORT_FAILED;
            $response['message'] = $ex->getMessage();
        }
        return $response;
    }
    
    public function updateConfirmStatus($ids)
    {
        try {
            foreach ($ids as $item) {
                $eikenResult = $this->em->getRepository('Application\Entity\EikenTestResult')->findOneBy(array('id' => $item, 'organizationNo' => $this->organizationNo));
                if (!$eikenResult) {
                    return HistoryConst::SAVE_TO_DATABASE_FAIL;
                }
                $eikenResult->setMappingStatus(HistoryConst::CONFIRMED_STATUS);
                $this->em->persist($eikenResult);
            }
            $this->em->flush();

            return HistoryConst::SAVE_TO_DATABASE_SUCCESS;
        }
        catch (Exception $e) {
            return HistoryConst::SAVE_TO_DATABASE_FAIL;
        }
    }

    private function mappingDataFromUkestuke($item = array(), $listOrgSchoolYear = array()) {
        $config = $this->getServiceLocator()->get('config');
        $listMappingLevel = $config['MappingLevel'];
        $eikenLevel = $listMappingLevel[$item->kyucd];
        $isPass = 0;
        if ($eikenLevel == '4級' || $eikenLevel == '5級') {
            if ($item->ichijigouhiflg == 1) {
                $isPass = 1;
            }
        } else {
            if ($item->ichimenflg == 1) {
                if ($item->nijigouhiflg == 1) {
                    $isPass = 1;
                }
            } else {
                if ($item->ichijigouhiflg == 1 && $item->nijigouhiflg == 1) {
                    $isPass = 1;
                }
            }
        }
        //Add field attendFlag
        if ($item->kyucd >= 6) {
            $attendFlag = ($item->ichijigouhiflg != '') ? 1 : 0;
        } else {
            if ($item->ichimenflg === 1) {
                $attendFlag = ($item->nijigouhiflg != '') ? 1 : 0; // TaiVH - Fix 6/11/2015 - bug F1GJIEM-3309
            } else {
                $attendFlag = ($item->nijigouhiflg != '' || $item->ichijigouhiflg != '') ? 1 : 0;
            }
        }
        $examDate = $this->getExamDateOfEikenByLevelAndYearAndKai($this->checkDataBeforeTrim($item->kyucd), $this->checkDataBeforeTrim($item->nendo), $this->checkDataBeforeTrim($item->kai));

        $isExitSchoolYearCode = array_search($item->gakunenno, array_column($listOrgSchoolYear, 'schoolYearCode'));
        $status = $isExitSchoolYearCode === false ? 'other' : 'DRAFT';
        $array = array(
            "ResultFlag" => $this->checkDataBeforeTrim($item->kekka),
            "Year" => $this->checkDataBeforeTrim($item->nendo),
            "Kai" => $this->checkDataBeforeTrim($item->kai),
            "EikenId" => $this->checkDataBeforeTrim($item->eikenid),
            "UketsukeNo" => $this->checkDataBeforeTrim($item->uketsukeno),
            "EikenLevelId" => $this->checkDataBeforeTrim($item->kyucd),
            "HallClassification" => $this->checkDataBeforeTrim($item->kaijokbn),
            "ExecutionDayOfTheWeek" => $this->checkDataBeforeTrim($item->jishiyoubi),
            "ExamineeNumber" => $this->checkDataBeforeTrim($item->jukenno),
            "PupilName" => str_replace(array(' ', ' ', '　'), array('', '', ''), $this->checkDataBeforeTrim($item->simei)),
            "SchoolNumber" => $this->checkDataBeforeTrim($item->gakkouno),
            "SchoolYearCode" => $this->checkDataBeforeTrim($item->gakunenno),
            "ClassCode" => $this->checkDataBeforeTrim($item->kumi),
            "OneExemptionFlag" => $this->checkDataBeforeTrim($item->ichimenflg),
            "OrganizationNo" => $this->checkDataBeforeTrim($item->dantaino),
            "FisrtScore1" => $this->checkDataBeforeTrim($item->ichiji1),
            "FisrtScore2" => $this->checkDataBeforeTrim($item->ichiji2),
            "FisrtScore3" => $this->checkDataBeforeTrim($item->ichiji3),
            "FisrtScore4" => $this->checkDataBeforeTrim($item->ichiji4),
            "FisrtScore5" => $this->checkDataBeforeTrim($item->ichiji5),
            "FisrtScore6" => $this->checkDataBeforeTrim($item->ichiji6),
            "FisrtScore7" => $this->checkDataBeforeTrim($item->ichiji7),
            "FisrtScore8" => $this->checkDataBeforeTrim($item->ichiji8),
            "TotalPrimaryScore" => $this->checkDataBeforeTrim($item->ichijikei),
            "PrimaryPassFailFlag" => $this->checkDataBeforeTrim($item->ichijigouhiflg),
            "PrimaryFailureLevel" => $this->checkDataBeforeTrim($item->ichijilevel),
            "SecondScore1" => $this->checkDataBeforeTrim($item->niji1),
            "SecondScore2" => $this->checkDataBeforeTrim($item->niji2),
            "SecondScore3" => $this->checkDataBeforeTrim($item->niji3),
            "SecondScore4" => $this->checkDataBeforeTrim($item->niji4),
            "SecondScore5" => $this->checkDataBeforeTrim($item->niji5),
            "SecondScore6" => $this->checkDataBeforeTrim($item->niji6),
            "SecondScore7" => $this->checkDataBeforeTrim($item->niji7),
            "SecondScore8" => $this->checkDataBeforeTrim($item->niji8),
            "TotalSecondScore" => $this->checkDataBeforeTrim($item->nijikei),
            "SecondPassFailFlag" => $this->checkDataBeforeTrim($item->nijigouhiflg),
            "SecondUnacceptableLevel" => $this->checkDataBeforeTrim($item->nijilevel),
            "SecondExamHall" => $this->checkDataBeforeTrim($item->nijikaijo),
            "SecondSetTimeHour" => $this->checkDataBeforeTrim($item->nijijikan_ji),
            "SecondSetTimeMinute" => $this->checkDataBeforeTrim($item->nijijikan_hun),
            "FirstMailSendFlag" => $this->checkDataBeforeTrim($item->ichijimailflg),
            "SecondMailSendFlag" => $this->checkDataBeforeTrim($item->nijimailflg),
            "InsertDate" => new \DateTime(date('Y-m-d H:i:s', strtotime($item->createdt))),
            "UpdateDate" => new \DateTime(date('Y-m-d H:i:s', strtotime($item->updatedt))),
            "IssueDate" => $this->checkDataBeforeTrim($item->hakkodt),
            "DeliveryClassification" => $this->checkDataBeforeTrim($item->nouhinkbn),
            "SemiClassification" => $this->checkDataBeforeTrim($item->junhonkbn),
            "DomesticInternationalClassification" => $this->checkDataBeforeTrim($item->kokunaigaikbn),
            "ShippingClassification" => $this->checkDataBeforeTrim($item->hassokbn),
            "DeedClassification" => $this->checkDataBeforeTrim($item->syousyokbn),
            "DisplayClass" => $this->checkDataBeforeTrim($item->hyojikyu),
            "ExamLocation" => $this->checkDataBeforeTrim($item->jyukenchi),
            "NameKanji" => str_replace(array(' ', ' ', '　'), array('', '', ''), $this->checkDataBeforeTrim($item->simei_kanji)),
            "TempNameKanji" => str_replace(array(' ', ' ', '　'), array('', '', ''), $this->checkDataBeforeTrim($item->simei)),
            "NameRomanji" => $this->checkDataBeforeTrim($item->simei_romaji),
            "NameRomanjiWithPrefix" => $this->checkDataBeforeTrim($item->simei_romaji_m),
            "NameKana" => str_replace(array(' ', ' ', '　'), array('', '', ''), $this->checkDataBeforeTrim($item->simei_kana)),
            "ZipCode" => $this->checkDataBeforeTrim($item->yubinbangou),
            "Address1" => $this->checkDataBeforeTrim($item->jyusyo1),
            "Address2" => $this->checkDataBeforeTrim($item->jyusyo2),
            "Address3" => $this->checkDataBeforeTrim($item->jyusyo3),
            "Address4" => $this->checkDataBeforeTrim($item->jyusyo4),
            "Address5" => $this->checkDataBeforeTrim($item->jyusyo5),
            "UrgentNotification" => $this->checkDataBeforeTrim($item->kokuchi),
            "BatchNumber" => $this->checkDataBeforeTrim($item->batchnum),
            "SeriNumber" => $this->checkDataBeforeTrim($item->seirinum),
            "SchoolClassification" => $this->checkDataBeforeTrim($item->gakkokbn),
            "ClassForDisplay" => $this->checkDataBeforeTrim($item->hyojikumi),
            "Sex" => $this->checkDataBeforeTrim($item->sei),
            "BarCodeStatus" => $this->checkDataBeforeTrim($item->bcdumu),
            "Barcode" => $this->checkDataBeforeTrim($item->bcd),
            "OrganizationName" => $this->checkDataBeforeTrim($item->dantaimei),
            "Password" => $this->checkDataBeforeTrim($item->password),
            "Note1" => $this->checkDataBeforeTrim($item->chui1),
            "Note2" => $this->checkDataBeforeTrim($item->chui2),
            "Note3" => $this->checkDataBeforeTrim($item->chui3),
            "ExamResults" => $this->checkDataBeforeTrim($item->shikenkbn),
            "FirstExamResultsFlag" => $this->checkDataBeforeTrim($item->ichijikekka),
            "FirstExamResultsFlagForDisplay" => $this->checkDataBeforeTrim($item->ichijikekka_hyoji),
            "FirstExamResultsPerfectScore" => $this->checkDataBeforeTrim($item->ichijikekka_manten),
            "FirstExamResultsPassPoint" => $this->checkDataBeforeTrim($item->ichijikekka_gokaku),
            "FirstExamResultsFailPoint" => $this->checkDataBeforeTrim($item->ichijikekka_fugokakua),
            "FirstExamResultsAveragePass" => $this->checkDataBeforeTrim($item->ichijikekka_gokakuheikin),
            "FirstExamResultsExamAverage" => $this->checkDataBeforeTrim($item->ichijikekka_jukensyaheikin),
            "FirstAdviceSentence1" => $this->checkDataBeforeTrim($item->ichijiadvice1),
            "FirstAdviceSentence2" => $this->checkDataBeforeTrim($item->ichijiadvice2),
            "FirstAdviceSentence3" => $this->checkDataBeforeTrim($item->ichijiadvice3),
            "FirstAdviceSentence4" => $this->checkDataBeforeTrim($item->ichijiadvice4),
            "FirstAdviceSentence5" => $this->checkDataBeforeTrim($item->ichijiadvice5),
            "FirstAdviceSentence6" => $this->checkDataBeforeTrim($item->ichijiadvice6),
            "CorrectAnswer" => $this->checkDataBeforeTrim($item->seikai),
            "Correction" => $this->checkDataBeforeTrim($item->seigo),
            "Explanation1" => $this->checkDataBeforeTrim($item->setumei1),
            "Explanation2" => $this->checkDataBeforeTrim($item->setumei2),
            "CrowdedFlag" => $this->checkDataBeforeTrim($item->manninflg),
            "CrowdedSentence" => $this->checkDataBeforeTrim($item->manninbunsyo),
            "SecondHallClassification" => $this->checkDataBeforeTrim($item->niji_kaijo_kbn),
            "HallNumber" => $this->checkDataBeforeTrim($item->kaijonum),
            "HallName" => $this->checkDataBeforeTrim($item->kaijomei),
            "SecondZipCode" => $this->checkDataBeforeTrim($item->niji_yubin_no),
            "SecondAddress" => $this->checkDataBeforeTrim($item->niji_jusyo),
            "TrafficRoute1" => $this->checkDataBeforeTrim($item->keiro1),
            "TrafficRoute2" => $this->checkDataBeforeTrim($item->keiro2),
            "TrafficRoute3" => $this->checkDataBeforeTrim($item->keiro3),
            "MapCode" => $this->checkDataBeforeTrim($item->chizu),
            "MeetingTime" => $this->checkDataBeforeTrim($item->syugojikan),
            "MeetingTimeDisplay" => $this->checkDataBeforeTrim($item->syugojikan_hyoji),
            "MeetingTimeColorFlag" => $this->checkDataBeforeTrim($item->syugojikan_flg),
            "PhotoAttachEsitence" => $this->checkDataBeforeTrim($item->syasin_tempu),
            "SemiHallApplicationDisplay" => $this->checkDataBeforeTrim($item->junkaijo_hyoji),
            "BaggageOutputClassification" => $this->checkDataBeforeTrim($item->keikohin),
            "Comment" => $this->checkDataBeforeTrim($item->seiseki_comment),
            "CommunicationField" => $this->checkDataBeforeTrim($item->info),
            "FirstFailureFourFiveClass" => $this->checkDataBeforeTrim($item->ichijihugokaku),
            "VocabularyFieldScore" => $this->checkDataBeforeTrim($item->tokuten_1),
            "VocabularyScore" => $this->checkDataBeforeTrim($item->haiten_1),
            "VocabularyPercentCorrectAnswers" => $this->checkDataBeforeTrim($item->seitouritsu_1),
            "VocabularyOverallAverage" => $this->checkDataBeforeTrim($item->heikin_1),
            "VocabularyPassAverage" => $this->checkDataBeforeTrim($item->goukakuheikin_1),
            "ReadingFieldScore" => $this->checkDataBeforeTrim($item->tokuten_2),
            "ReadingScore" => $this->checkDataBeforeTrim($item->haiten_2),
            "ReadingPercentCorrectAnswers" => $this->checkDataBeforeTrim($item->seitouritsu_2),
            "ReadingOverallAverage" => $this->checkDataBeforeTrim($item->heikin_2),
            "ReadingPassAverage" => $this->checkDataBeforeTrim($item->goukakuheikin_2),
            "ListeningFieldScore" => $this->checkDataBeforeTrim($item->tokuten_3),
            "ListeningScore" => $this->checkDataBeforeTrim($item->haiten_3),
            "ListeningPercentCorrectAnswers" => $this->checkDataBeforeTrim($item->seitouritsu_3),
            "ListeningOverallAverage" => $this->checkDataBeforeTrim($item->heikin_3),
            "ListeningPassAverage" => $this->checkDataBeforeTrim($item->goukakuheikin_3),
            "CompositionFieldScore" => $this->checkDataBeforeTrim($item->tokuten_4),
            "CompositionScore" => $this->checkDataBeforeTrim($item->haiten_4),
            "CompositionPercentCorrectAnswers" => $this->checkDataBeforeTrim($item->seitouritsu_4),
            "CompositionOverallAverage" => $this->checkDataBeforeTrim($item->heikin_4),
            "CompositionPassAverage" => $this->checkDataBeforeTrim($item->goukakuheikin_4),
            "ResultScoreAccordingField1" => $this->checkDataBeforeTrim($item->bunyatokuten_1),
            "ResultScoreAccordingField2" => $this->checkDataBeforeTrim($item->bunyatokuten_2),
            "ResultScoreAccordingField3" => $this->checkDataBeforeTrim($item->bunyatokuten_3),
            "ResultScoreAccordingField4" => $this->checkDataBeforeTrim($item->bunyatokuten_4),
            "ResultPerfectScoreAccordingField1" => $this->checkDataBeforeTrim($item->manten_1),
            "ResultPerfectScoreAccordingField2" => $this->checkDataBeforeTrim($item->manten_2),
            "ResultPerfectScoreAccordingField3" => $this->checkDataBeforeTrim($item->manten_3),
            "ResultPerfectScoreAccordingField4" => $this->checkDataBeforeTrim($item->manten_4),
            "LargeQuestionCorrectAnswer1" => $this->checkDataBeforeTrim($item->daimon_1),
            "LargeQuestionCorrectAnswer2" => $this->checkDataBeforeTrim($item->daimon_2),
            "LargeQuestionCorrectAnswer3" => $this->checkDataBeforeTrim($item->daimon_3),
            "LargeQuestionCorrectAnswer4" => $this->checkDataBeforeTrim($item->daimon_4),
            "LargeQuestionProblemResult1" => $this->checkDataBeforeTrim($item->mondaisu_1),
            "LargeQuestionProblemResult2" => $this->checkDataBeforeTrim($item->mondaisu_2),
            "LargeQuestionProblemResult3" => $this->checkDataBeforeTrim($item->mondaisu_3),
            "LargeQuestionProblemResult4" => $this->checkDataBeforeTrim($item->mondaisu_4),
            "StydyAdvice1" => $this->checkDataBeforeTrim($item->advice_1),
            "StydyAdvice2" => $this->checkDataBeforeTrim($item->advice_2),
            "StydyAdvice3" => $this->checkDataBeforeTrim($item->advice_3),
            "StydyAdvice4" => $this->checkDataBeforeTrim($item->advice_4),
            "NoticeCode1" => $this->checkDataBeforeTrim($item->oshirase_1),
            "NoticeCode2" => $this->checkDataBeforeTrim($item->oshirase_2),
            "StudyRealityGraph1" => $this->checkDataBeforeTrim($item->graph_1),
            "StudyRealityGraph2" => $this->checkDataBeforeTrim($item->graph_2),
            "FirstPassMerit1" => $this->checkDataBeforeTrim($item->merit_1),
            "FirstPassMerit2" => $this->checkDataBeforeTrim($item->merit_2),
            "FirstPassMerit3" => $this->checkDataBeforeTrim($item->merit_3),
            "FirstPassMerit4" => $this->checkDataBeforeTrim($item->merit_4),
            "FirstPassMerit5" => $this->checkDataBeforeTrim($item->merit_5),
            "FirstPassMerit6" => $this->checkDataBeforeTrim($item->merit_6),
            "FirstPassMerit7" => $this->checkDataBeforeTrim($item->merit_7),
            "FirstPassMerit8" => $this->checkDataBeforeTrim($item->merit_8),
            "FirstPassMerit9" => $this->checkDataBeforeTrim($item->merit_9),
            "FirstPassMerit10" => $this->checkDataBeforeTrim($item->merit_10),
            "FirstPassMerit11" => $this->checkDataBeforeTrim($item->merit_11),
            "FirstPassMerit12" => $this->checkDataBeforeTrim($item->merit_12),
            "FirstPassMerit13" => $this->checkDataBeforeTrim($item->merit_13),
            "FirstPassMerit14" => $this->checkDataBeforeTrim($item->merit_14),
            "FirstPassMerit15" => $this->checkDataBeforeTrim($item->merit_15),
            "CanDoList1" => $this->checkDataBeforeTrim($item->cando_1),
            "CertificateNumber" => $this->checkDataBeforeTrim($item->syousyonum),
            "CertificationDate" => isset($examDate) ? new \DateTime(date('Y-m-d H:i:s', strtotime($examDate))) : null,
            "SortArea" => $this->checkDataBeforeTrim($item->sort),
            "SelfOrganizationsDeliveryFlag" => $this->checkDataBeforeTrim($item->dantai_chokuso),
            "SecondIssueYear" => $this->checkDataBeforeTrim($item->niji_hakkodt),
            "SecondDeliveryClassification" => $this->checkDataBeforeTrim($item->niji_nouhinkbn),
            "SecondSemiClassification" => $this->checkDataBeforeTrim($item->niji_junhonkbn),
            "SecondExecutionDayOfTheWeek" => $this->checkDataBeforeTrim($item->niji_jishiyoubi),
            "SecondDomesticInternationalClassification" => $this->checkDataBeforeTrim($item->niji_kokunaigaikbn),
            "SecondShippingClassification" => $this->checkDataBeforeTrim($item->niji_hassokbn),
            "SecondDeedExistenceClassification" => $this->checkDataBeforeTrim($item->niji_syousyokbn),
            "SecondExaminationAreas" => $this->checkDataBeforeTrim($item->niji_jyukenchi),
            "SecondEmergencyNotice" => $this->checkDataBeforeTrim($item->niji_kokuchi),
            "SecondBatchNumber" => $this->checkDataBeforeTrim($item->niji_batchnum),
            "SecondSeriNumber" => $this->checkDataBeforeTrim($item->niji_seirinum),
            "SecondBarCodeStatus" => $this->checkDataBeforeTrim($item->niji_bcdumu),
            "SecondBarCode" => $this->checkDataBeforeTrim($item->niji_bcd),
            "SecondNote1" => $this->checkDataBeforeTrim($item->niji_chui1),
            "SecondNote2" => $this->checkDataBeforeTrim($item->niji_chui2),
            "SecondNote3" => $this->checkDataBeforeTrim($item->niji_chui3),
            "SecondExamClassification" => $this->checkDataBeforeTrim($item->niji_kbn),
            "SecondExamResultsFlag" => $this->checkDataBeforeTrim($item->nijikekka),
            "SecondExamResultsFlagForDisplay" => $this->checkDataBeforeTrim($item->nijikekka_hyoji),
            "SecondExamResultsPerfectScore" => $this->checkDataBeforeTrim($item->nijikekka_manten),
            "SecondExamResultsPassPoint" => $this->checkDataBeforeTrim($item->nijikekka_gokaku),
            "SecondtExamResultsFailPoint" => $this->checkDataBeforeTrim($item->nijikekka_fugokakua),
            "SecondAdviceSentence1" => $this->checkDataBeforeTrim($item->nijiadvice1),
            "SecondAdviceSentence2" => $this->checkDataBeforeTrim($item->nijiadvice2),
            "SecondAdviceSentence3" => $this->checkDataBeforeTrim($item->nijiadvice3),
            "SecondAdviceSentence4" => $this->checkDataBeforeTrim($item->nijiadvice4),
            "SecondAdviceSentence5" => $this->checkDataBeforeTrim($item->nijiadvice5),
            "SecondAdviceSentence6" => $this->checkDataBeforeTrim($item->nijiadvice6),
            "ScoreAccordingField1" => $this->checkDataBeforeTrim($item->nijitokuten_1),
            "ScoreAccordingField2" => $this->checkDataBeforeTrim($item->nijitokuten_2),
            "ScoreAccordingField3" => $this->checkDataBeforeTrim($item->nijitokuten_3),
            "ScoreAccordingField4" => $this->checkDataBeforeTrim($item->nijitokuten_4),
            "ScoreAccordingField5" => $this->checkDataBeforeTrim($item->nijitokuten_5),
            "ScoringAccordingField1" => $this->checkDataBeforeTrim($item->nijihaiten_1),
            "ScoringAccordingField2" => $this->checkDataBeforeTrim($item->nijihaiten_2),
            "ScoringAccordingField3" => $this->checkDataBeforeTrim($item->nijihaiten_3),
            "ScoringAccordingField4" => $this->checkDataBeforeTrim($item->nijihaiten_4),
            "ScoringAccordingField5" => $this->checkDataBeforeTrim($item->nijihaiten_5),
            "SecondPassMerit1" => $this->checkDataBeforeTrim($item->nijimerit_1),
            "SecondPassMerit2" => $this->checkDataBeforeTrim($item->nijimerit_2),
            "SecondPassMerit3" => $this->checkDataBeforeTrim($item->nijimerit_3),
            "SecondPassMerit4" => $this->checkDataBeforeTrim($item->nijimerit_4),
            "SecondPassMerit5" => $this->checkDataBeforeTrim($item->nijimerit_5),
            "SecondPassMerit6" => $this->checkDataBeforeTrim($item->nijimerit_6),
            "SecondPassMerit7" => $this->checkDataBeforeTrim($item->nijimerit_7),
            "SecondPassMerit8" => $this->checkDataBeforeTrim($item->nijimerit_8),
            "SecondPassMerit9" => $this->checkDataBeforeTrim($item->nijimerit_9),
            "SecondPassMerit10" => $this->checkDataBeforeTrim($item->nijimerit_10),
            "SecondPassMerit11" => $this->checkDataBeforeTrim($item->nijimerit_11),
            "SecondPassMerit12" => $this->checkDataBeforeTrim($item->nijimerit_12),
            "SecondPassMerit13" => $this->checkDataBeforeTrim($item->nijimerit_13),
            "SecondPassMerit14" => $this->checkDataBeforeTrim($item->nijimerit_14),
            "SecondPassMerit15" => $this->checkDataBeforeTrim($item->nijimerit_15),
            "CanDoList2" => $this->checkDataBeforeTrim($item->cando_2),
            "Notice" => $this->checkDataBeforeTrim($item->niji_oshirase),
            "SecondCertificateNumber" => $this->checkDataBeforeTrim($item->niji_syousyonum),
            "SecondCertificationDate" => isset($item->niji_ninteibi) ? new \DateTime(date('Y-m-d H:i:s', strtotime($item->niji_ninteibi))) : null,
            "SecondSortArea" => $this->checkDataBeforeTrim($item->niji_sort),
            "SecondselfOrganizationDeliveryFlag" => $this->checkDataBeforeTrim($item->niji_dantai_chokuso),
            "PasswordNumber" => $this->checkDataBeforeTrim($item->pin_number),
            "Birthday" => new \DateTime(date('Y-m-d H:i:s', strtotime($item->birthday))),
            "FirsrtScoreTwoSkillRL" => $this->checkDataBeforeTrim($item->cse_total_1_rl),
            "FirstSoreThreeSkillRLW" => $this->checkDataBeforeTrim($item->cse_total_1_rlw),
            "SecondScoreThreeSkillRLS" => $this->checkDataBeforeTrim($item->cse_total_2_rls),
            "SecondScoreFourSkillRLWS" => $this->checkDataBeforeTrim($item->cse_total_2_rlws),
            "CSEScoreReading" => is_numeric($item->cse_reading) ? $this->checkDataBeforeTrim($item->cse_reading) : NULL,
            "CSEScoreListening" => is_numeric($item->cse_listening) ? $this->checkDataBeforeTrim($item->cse_listening) : NULL,
            "CSEScoreWriting" => is_numeric($item->cse_writing) ? $this->checkDataBeforeTrim($item->cse_writing) : NULL,
            "CSEScoreSpeaking" => is_numeric($item->cse_speaking) ? $this->checkDataBeforeTrim($item->cse_speaking) : NULL,
            "EikenBand1" => $this->checkDataBeforeTrim($item->eikenband_1),
            "EikenBand2" => $this->checkDataBeforeTrim($item->eikenband_2),
            "CSEScoreMessage1" => $this->checkDataBeforeTrim($item->cse_msg_1),
            "CSEScoreMessage2" => $this->checkDataBeforeTrim($item->cse_msg_2),
            "EikenCSETotal" => $item->cse_reading + $item->cse_listening + $item->cse_writing + $item->cse_speaking,
            "SchoolYearName" => $this->checkDataBeforeTrim($item->gakunenno),
            "PreSchoolYearName" => $this->checkDataBeforeTrim($item->gakunenno),
            "ClassName" => $this->checkDataBeforeTrim($item->kumi),
            "IsPass" => $isPass,
            "AttendFlag" => $attendFlag,
            "Status" => $status,
        );

        return $array;
    }

    public function getExamDateOfEikenByLevelAndYearAndKai($eikenLevelId, $year, $kai) {
        /* @var $eikenSchedule \Application\Entity\EikenSchedule */
        $eikenSchedule = $this->em->getRepository('Application\Entity\EikenSchedule')->findOneBy(array(
            'year' => $year,
            'kai' => $kai
        ));

        if (in_array($eikenLevelId, array(1, 2, 3, 4, 5))) {

            $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
            $dateRule = $dantaiService->getDateRound2EachKyu($eikenSchedule->getId());

            // 1級 -> 3級
            $eikenDate = 0;
            if(isset($dateRule[$eikenLevelId])){
                $eikenDate = $eikenSchedule->getRound2Day2ExamDate() != Null ? $eikenSchedule->getRound2Day2ExamDate()->format('Y-m-d H:i:s') : 0;
                if($dateRule[$eikenLevelId] === 1){
                    $eikenDate = $eikenSchedule->getRound2Day1ExamDate() != Null ? $eikenSchedule->getRound2Day1ExamDate()->format('Y-m-d H:i:s') : 0;
                }
            }
        } else {
            //4級 -> 5級
            if ($eikenSchedule->getSunDate() == Null && $eikenSchedule->getFriDate() == Null && $eikenSchedule->getSatDate() == Null) {
                $eikenDate = 0;
            } else {
                $sunDate = $eikenSchedule->getSunDate() != Null ? $eikenSchedule->getSunDate()->format('Y-m-d H:i:s') : 0;
                $friDate = $eikenSchedule->getFriDate() != Null ? $eikenSchedule->getFriDate()->format('Y-m-d H:i:s') : 0;
                $satDate = $eikenSchedule->getSatDate() != Null ? $eikenSchedule->getSatDate()->format('Y-m-d H:i:s') : 0;

                $arrWday = array();
                if ($sunDate > 0)
                    $arrWday[] = $sunDate;
                if ($friDate > 0)
                    $arrWday[] = $friDate;
                if ($satDate > 0)
                    $arrWday[] = $satDate;

                $eikenDate = max($arrWday);
            }
        }
        return $eikenDate;
    }

    public function checkDataBeforeTrim($data)
    {
        return ($data != '') ? trim($data) : $data;
    }

    private $eikenResult = '';
    public function getEikenResult($mock = '')
    {
        if($mock){
            $this->eikenResult = $mock;
        }else{
            $this->eikenResult = $this->em->getRepository('Application\Entity\EikenTestResult');
        }
    }
    
    public function getEikenResultsDetails($year, $kai, $orgSchoolYearId = '', $classId = '', $nameKana = '', $mappingStatus = '')
    {
        if(!$this->eikenResult){
            $this->getEikenResult();
        }
        $result = $this->eikenResult->getEikenResultsDetails($year, $kai, $this->organizationNo, $orgSchoolYearId, $classId, $nameKana, $mappingStatus);
        if(!$result){
            return HistoryConst::CANNOT_FIND_DATA;
        }
        return $result;
    }
    
    private $status = '';
    public function getMappingStatus($mock = '')
    {
        if($mock){
            $this->status = $mock;
        }else{
            $this->status = $this->em->getRepository('Application\Entity\EikenTestResult');
        }
    }

    public function getTotalMappingStatus($year, $kai)
    {
        if(!$this->status){
            $this->getMappingStatus();
        }
        $mappingStatus = $this->status->getTotalMappingStatus($year, $kai, $this->organizationNo);
        if(!$mappingStatus){
            return HistoryConst::CANNOT_FIND_DATA;
        }
        return $mappingStatus;
    }

    public function logicPassFail($eikenLevel, $ichijigouhiflg, $ichimenflg, $nijigouhiflg)
    {
        if ($eikenLevel > 5 && $ichijigouhiflg == 1) {
            return 1;
        }
        if ($eikenLevel < 6) {
            if ($ichimenflg == 1 && $nijigouhiflg == 1) {
                return 1;
            }
            if ($ichijigouhiflg == 1 && $nijigouhiflg == 1) {
                return 1;
            }
        }

        return 0;
    }

    public function getListClassBySchoolYear($schoolYearId, $year)
    {
        return $this->em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYear($schoolYearId, $year, $this->organizationId);
    }

    public function getListSchoolYear()
    {
        return $this->em->getRepository('Application\Entity\OrgSchoolYear')->listSchoolYearName($this->organizationId);
    }
    
    public function checkPupilByYear($year)
    {
        return $this->em->getRepository('Application\Entity\Pupil')->findBy(array('organizationId' => $this->organizationId, 'year' => $year,'isDelete' => 0));
    }

    public function getEikenTestResult($id)
    {
        if (empty($id)) {
            return false;
        }

        return $this->em->getRepository('Application\Entity\EikenTestResult')->findOneBy(array('id' => $id, 'isDelete' => 0, 'organizationNo' => $this->organizationNo));
    }

    public function findPupilList($schoolYearId, $classId, $year, $birthday, $nameKana, $nameKanji)
    {
        return $this->em->getRepository('Application\Entity\Pupil')->findPupilList($this->organizationId,$schoolYearId, $classId, $year, $birthday, $nameKana, $nameKanji);
    }
    
    public function deleteMapping($eikenTestResultId){
        $response = array('status' => 1, 'message' => 'Success');
        if (!$this->eikenScoreRepo) {
            $this->setEikenScoreRepo();
        }

        /*@var $eikenTestResult Application\Entity\EikenTestResult\ */
        $eikenTestResult = $this->em->getRepository('Application\Entity\EikenTestResult')->findOneBy(array('id' => $eikenTestResultId, 'isDelete' => 0));
        if (!empty($eikenTestResult)) {
            // remapping schoolYearName by schoolYearCode
            $listOrgSchoolYear = $this->getListSchoolYearMappingByOrgNo($eikenTestResult->getOrganizationNo());
            $isExitSchoolYearCode = array_search($eikenTestResult->getSchoolYearCode(), array_column($listOrgSchoolYear, 'schoolYearCode'));
            

            $mappingDelete[$eikenTestResultId] = array(
                'orgSchoolYearId'      => null,
                'orgSchoolYearName'    => $eikenTestResult->getSchoolYearCode(),
                'orgSchoolYearCode'    => null,
                'classId'              => null,
                'className'            => $eikenTestResult->getClassCode(),
                'nameKanji'            => $eikenTestResult->getPupilName(),
                'nameKana'             => null,
                'pupilId'              => null,
                'pupilNumber'          => null,
                'birthday'             => null,
                'isDeleteMapping'      => 1,
                'isExitSchoolYearCode' => $isExitSchoolYearCode,
            );
        } else {
            $mappingDelete[$eikenTestResultId] = array(
                'orgSchoolYearId'      => null,
                'orgSchoolYearName'    => null,
                'orgSchoolYearCode'    => null,
                'classId'              => null,
                'className'            => null,
                'nameKanji'            => null,
                'nameKana'             => null,
                'pupilId'              => null,
                'pupilNumber'          => null,
                'birthday'             => null,
                'isDeleteMapping'      => 1,
                'isExitSchoolYearCode' => false,
            );
        }

        try{
            $this->updateEikenTestResult($mappingDelete);
            $eikenScoreRemove = $this->eikenScoreRepo->findOneBy(
                array('eikenTestResultId' => $eikenTestResultId)
            );
            if ($eikenScoreRemove) {
                $this->em->remove($eikenScoreRemove);
                $this->em->flush();
            }
            $this->updateConfirmStatus(array($eikenTestResultId));
            return $response;
        } catch (\Exception $ex) {
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
        }
        return $response;
    }

    public function confirmMapping($eikenTestResultId, $pupilId){
        $response = array(
            'status' => 1, 'message' => 'Successs'
        );
        if (intval($eikenTestResultId) <= 0) {
            $response['status'] = 0;
            $response['message'] = 'Emtpy EikenTestResult';
            return $response;
        }
        if(intval($pupilId) == 0){
            try {
                $this->updateConfirmStatus(array($eikenTestResultId));
            } catch (\Exception $ex) {
                $response['status'] = 0;
                $response['message'] = $ex->getMessage();
            }
            return $response;
        }
        
        if(!$this->eikenTestResultRepo){
            $this->setEikenTestResultRepo();
        }
        if(!$this->pupilRepo){
            $this->setPupilRepo();
        }
        if(!$this->eikenScoreRepo){
            $this->setEikenScoreRepo();
        }
        /*@var $eikenTestResult \Application\Entity\EikenTestResult*/
        $eikenTestResult = $this->eikenTestResultRepo->find($eikenTestResultId);
        /*@var $pupil \Application\Entity\Pupil*/
        $pupil = $this->pupilRepo->find($pupilId);
        if(!$eikenTestResult || !$pupil){
            $response['status'] = 0;
            $response['message'] = 'Emtpy EikenTestResult OR Pupil';
            return $response;
        }
        $schoolYearMapping = $this->em->getRepository('Application\Entity\SchoolYearMapping')->findOneBy(array(
            'schoolYearId' => $pupil->getOrgSchoolYear()->getSchoolYearId(),
            'orgCode' => $eikenTestResult->getSchoolClassification()
        ));
        if ($schoolYearMapping) {
            $schoolYearCode = $schoolYearMapping->getSchoolYearCode();
        }
        $mappingSuccess[$eikenTestResultId] = array(
            'orgSchoolYearId' => $pupil->getOrgSchoolYearId(),
            'orgSchoolYearName' => $pupil->getOrgSchoolYear()->getDisplayName(),
            'orgSchoolYearCode' => !empty($schoolYearCode) ? $schoolYearCode : '',
            'classId' => $pupil->getClassId(),
            'className' => $pupil->getClass()->getClassName(),
            'nameKanji' => $pupil->getFirstNameKanji() . $pupil->getLastNameKanji(),
            'nameKana' => $pupil->getFirstNameKana() . $pupil->getLastNameKana(),
            'pupilId' => $pupilId,
            'pupilNumber' => $pupil->getNumber(),
            'birthday' => $pupil->getBirthday() != Null ? $pupil->getBirthday()->format('Y-m-d 00:00:00') : Null,
        );
        $eikenTestResultArray = $eikenTestResult->toArray(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT);
        $pupilArray = $pupil->toArray(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT);
        $eikenScores[$eikenTestResultId] = $this->convertDataToInsertToEikenScore($eikenTestResultArray, $pupilArray);
        try {
            $eikenScoreRemove = $this->eikenScoreRepo->findOneBy(
                    array('eikenTestResultId' => $eikenTestResultId, 'pupilId' => $pupilId)
            );
            if ($eikenScoreRemove) {
                $this->em->remove($eikenScoreRemove);
                $this->em->flush();
            }
            $this->updateEikenTestResult($mappingSuccess);
            $this->insertResultMappingToEikenScore($eikenScores);
            $this->updateConfirmStatus(array($eikenTestResultId));
        } catch (\Exception $ex) {
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
        }
        return $response;
    }
    
    public function updateStatusAutoImportOfRound($year, $kai){
        $eikenScheduleRepos = $this->em->getRepository('Application\Entity\EikenSchedule');
        /*@var $eikenSchedule \Application\Entity\EikenSchedule*/
        $eikenSchedule = $eikenScheduleRepos->findOneBy(array(
            'year' => intval($year),
            'kai' => intval($kai),
        ));
        if($eikenSchedule && $eikenSchedule->getDay1stTestResult() != Null && $eikenSchedule->getDay2ndTestResult() != Null){
            $dayTestResultOne = $eikenSchedule->getDay1stTestResult()->format('Ymd');
            $dayTestResultTwo = $eikenSchedule->getDay2ndTestResult()->format('Ymd');
            $dayNow = date('Ymd');
            $status = HistoryConst::ROUND_TWO_CONFIRMED;
            if($dayNow >= $dayTestResultOne && $dayNow < $dayTestResultTwo){
                $status = HistoryConst::ROUND_ONE_CONFIRMED;
            }
            /*@var $applyEikenOrg \Application\Entity\ApplyEikenOrg*/
            $applyEikenOrg = $this->em->getRepository('Application\Entity\ApplyEikenOrg')->findOneBy(array(
                'eikenScheduleId' => $eikenSchedule->getId(),
                'organizationId' => $this->organizationId,
            ));
            if (!empty($applyEikenOrg)) {
                $applyEikenOrg->setStatusAutoImport($status);
                $this->em->persist($applyEikenOrg);
                $this->em->flush();
            }
            return true;
        }
        return false;
    }

    public function getListSchoolYearMappingByOrgNo($orgNo)
    {
        return $this->em->getRepository('Application\Entity\SchoolYearMapping')->getListOrgSchoolYearNameByOrgNo($orgNo);
    }

    public function getEikenTestResultRepo(){
        return $this->em->getRepository('Application\Entity\EikenTestResult');
    }

    public function insertOnDuplicateUpdateMultiple($listEikenTestResult){
        return $this->getEikenTestResultRepo()->insertOnDuplicateUpdateMultiple($listEikenTestResult);
    }

    public function updateTempValueAfterImport($orgNo, $year, $kai){
        return $this->getEikenTestResultRepo()->updateTempValueAfterImport($orgNo, $year, $kai);
    }

    public function createEikenScoreForMappedResult($year, $kai, $organizationNo){
        // get list EikenTestResult which has pupilId
        $eikenTestResults = $this->getEikenTestResultRepo()->getEikenTestResult($year, $kai, $organizationNo, $isMapped = true);
        $eikenScoreArray = array();

        foreach ($eikenTestResults as $result) {
            $pupil = array(
                'id' => $result['pupilId'],
            );
            $eikenScoreArray[$result['id']] = $this->convertDataToInsertToEikenScore($result, $pupil);
        }

        return $eikenScoreArray;
    }
    
    public function updateIsMappedWidthIds($eikenTestResults) {
        if (!$this->eikenTestResultRepo) {
            $this->setEikenTestResultRepo();
        }
        if ($eikenTestResults) {
            $ids = array();
            foreach ($eikenTestResults as $row) {
                if (!in_array($row['id'], $ids)) {
                    array_push($ids, $row['id']);
                }
            }

            $this->eikenTestResultRepo->updateIsMappedWidthIds($ids);

            return true;
        }

        return false;
    }

}