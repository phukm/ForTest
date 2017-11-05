<?php
namespace History\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Dantai\PrivateSession;
use History\HistoryConst;
use Dantai\Utility\MappingUtility;

class ExportExcelMapper implements ServiceLocatorAwareInterface {
    use ServiceLocatorAwareTrait;
    private $header;
    private $data;
    private $searchCriteria;
    private $organizationNo;
    private $level;
    private $eikenLevelId;
    private $hallClassification;
    private $dayOfTheWeek;
    private $oneExemptionFlag;
    private $passFailFlag;
    private $sex;
    private $barCodeStatus;
    private $firstExamResultsFlag;
    private $schoolCode;
    private $ibaMasterDataScore;

    public function __construct($data, $header, $serviceLocator, $searchCriteria = '') {
        $this->header = $header;
        $this->data = $data;
        $this->searchCriteria = $searchCriteria;
        $this->setServiceLocator($serviceLocator);
        $user = PrivateSession::getData('userIdentity');
        $this->organizationNo = $user['organizationNo'];
        $this->ibaMasterDataScore = array();
    }

    public function translate($text){
        $translate = $this->serviceLocator->get('MVCTranslator');
        return $translate->translate($text);
    }
    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        return $this->serviceLocator->get('doctrine.entitymanager.orm_default');
    }
    
    public function getIBAMasterDataScore($type = null) {
        return $this->getEntityManager()->getRepository('Application\Entity\IbaScoreMasterData')->getListIbaScoreMasterData($type);
    }
    
    public function convertToExport() {
        $data = $this->data;
        $header = $this->header;
        $dataExport = array();
        if ($data && $header) {
            $index = 0;
            foreach ($data as $row) {
                foreach ($header as $field => $titleHeader) {
                    $dataExport[$index][$field] = '';
                        $dataExport[$index][$field] = array_key_exists($field, $row) ? $row[$field] : '';
                        if (method_exists($this, $field)) {
                            $dataExport[$index] = array_merge($dataExport[$index],$this->$field($row,$field));
                        }
                }
                $index++;
            }
        }
        array_unshift($dataExport, $header);
        return $dataExport;
    }
//    start listOfPupilAchievement
    private function schoolCode($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
            
            // get school code
            $config = $this->serviceLocator->get('config');
            $classification = $config['School_Code'];
            // school code
            $orgClassification = array_key_exists('schoolClassification', $row) ? $row['schoolClassification'] : '';
            $data[$field] = array_key_exists('schoolClassification', $row) ? $row['schoolClassification'] : '';

            if (in_array($orgClassification, array_keys($classification)) && $orgClassification) {
                $data[$field] = $classification[$orgClassification];
            }
        }
        return $data;
    }
    
    private function numberPupil($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
            if($row['pupilId'] != '' && array_key_exists('pupilId', $row)){
                $data[$field] = array_key_exists('pupilNo', $row) ? $row['pupilNo'] : '';
            }
        }
        return $data;
    }
    
    private function nameKanjiOfListOfPupilAchievement($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
           if(array_key_exists('mappingStatus', $row) && array_key_exists('pupilName', $row) && array_key_exists('tempNameKanji', $row)){
                $data[$field] = $row['pupilName'];
                if($row['pupilId']){
                    $data[$field] = $row['tempNameKanji'];
                }
           }
        }
        return $data;
    }
    
    private function passFail1($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
           // pass fail 1
            if(array_key_exists('oneExemptionFlag', $row) && array_key_exists('primaryFailureLevel', $row)){
                if(!empty($row['firstExamResultsFlagForDisplay']) && $row['oneExemptionFlag'] == 1){
                    $data[$field] = $row['firstExamResultsFlagForDisplay'];
                }
                if ($row['oneExemptionFlag'] != 1) {
                    $data[$field] = '不合格';
                    if ($row['primaryPassFailFlag'] == 1) {
                        $data[$field] = '合格';
                    }
                }
            }
        }
        return $data;
    }
    
    private function eikenTotal1($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists('eikenLevelId', $row) && array_key_exists('totalPrimaryScore', $row) && array_key_exists('firstExamResultsPerfectScore', $row)){
                if ($row['eikenLevelId'] > 5) {
                    if ($row['totalPrimaryScore'] != null && $row['firstExamResultsPerfectScore'] != null) {
                        $data[$field] = $row['totalPrimaryScore'] . "/" . $row['firstExamResultsPerfectScore'];
                    }
                }elseif ($row['eikenLevelId'] < 6) {
                    if(array_key_exists('oneExemptionFlag', $row)){
                        if ($row['oneExemptionFlag'] != 1 && $row['totalPrimaryScore'] != null && $row['firstExamResultsPerfectScore'] != null) {
                            $data[$field] = $row['totalPrimaryScore'] . "/" . $row['firstExamResultsPerfectScore'];
                        }
                    }
                }
            }
        }
        return $data;
    }
    
    private function passFail2($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists('eikenLevelId', $row)){
                $data[$field] = '';
                if (array_key_exists('secondPassFailFlag', $row) && $row['secondPassFailFlag'] == 1) {
                    $data[$field] = '合格';
                }elseif (array_key_exists('secondPassFailFlag', $row) && $row['secondPassFailFlag'] === 0) {
                    $data[$field] = '不合格';
                }
            }
        }
        return $data;
    }
    
    private function eikenTotal2($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists('eikenLevelId', $row) && array_key_exists('totalSecondScore', $row) && array_key_exists('secondExamResultsPerfectScore', $row)){
                if ($row['eikenLevelId'] < 6) {
                    if ($row['totalSecondScore'] != null && $row['secondExamResultsPerfectScore'] != null) {
                        $data[$field] = $row['totalSecondScore'] . "/" . $row['secondExamResultsPerfectScore'];
                    }
                }
            }
        }
        return $data;
    }
       
    /* get special field in list IBA */
    private function createDate($data, $field){
        $result = '';
        if(!empty($data[$field])){
            $result = $data[$field]->format('Y/m/d H:i:s');
        }
        return array($field => $result);
    }
    
//    end listOfPupilAchievement
//    start listOfExamHistoryList
    
    private function pupilNo($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
                $data[$field] = $row[$field] != '' ? $row[$field] : '';
        }
        return $data;
    }
    

    private function setEikenIBALevel()
    {
        $pupilIdList = array();
        $list = array();
        $data = $this->data;
        $searchYear = $this->searchCriteria;
        foreach ($data as $item) {
            if ($item['pupilId'] != '') {
                array_push($pupilIdList, $item['pupilId']);
            }
        }
        $em = $this->getEntityManager();
        if ($pupilIdList != array()) {
            $list['eiken'] = $em->getRepository('Application\Entity\EikenTestResult')->getEikenLevelByPupilId($pupilIdList, $this->organizationNo, $searchYear);
            $list['iba'] = $em->getRepository('Application\Entity\IBATestResult')->getIBALevelByPupilId($pupilIdList, $this->organizationNo, $searchYear);
        }

        $this->level = $list;
    }
    
    private function level($row,$field){
        
        if(empty($this->level)){
            $this->setEikenIBALevel();
        }
        
        $level = $this->level;
        $eikenLevelList = isset($level['eiken']) ? $level['eiken'] : '';
        
        $config = $this->serviceLocator->get('config');
        $listMapppingEikenLevel = $config['MappingLevel'];
        
        $data[$field] = '';
        if (!empty($row)) {
            // level
             $data[$field] = (isset($row['eikenLevelId']) && $row['eikenLevelId'] != '') ? $listMapppingEikenLevel[$row['eikenLevelId']] : '';
            if (count($eikenLevelList) > 0 && $row['pupilId'] != '' && array_key_exists($row['pupilId'], $eikenLevelList)) {
                if ($eikenLevelList[$row['pupilId']]['eikenLevel'] != '') {
                    $data[$field] = $listMapppingEikenLevel[$eikenLevelList[$row['pupilId']]['eikenLevel']];
                }
            }
        }
        return $data;
    }
    
    private function ibaLevel($row,$field){
        
        if(empty($this->level)){
            $this->setEikenIBALevel();
        }
        
        $level = $this->level;
        $ibaLevelList = isset($level['iba']) ? $level['iba'] : '';
        
        $config = $this->serviceLocator->get('config');
        $listMapppingEikenLevel = $config['MappingIBALevelTotal'];
        
        $data[$field] = '';
        if (!empty($row)) {
            // IBA Level
                $data[$field] = (isset($row['ibaLevelId']) && $row['ibaLevelId'] != '') ? $listMapppingEikenLevel[$row['ibaLevelId']] : '';
                if (count($ibaLevelList) > 0 && $row['pupilId'] != '' && array_key_exists($row['pupilId'], $ibaLevelList)) {
                    if ($ibaLevelList[$row['pupilId']]['ibaLevel'] != '') {
                        $data[$field] = $listMapppingEikenLevel[$ibaLevelList[$row['pupilId']]['ibaLevel']];
                    }
                }
        }
        return $data;
    }
    //    end listOfExamHistoryList
//    start listOfEikenHistoryPupil
    public function primaryPassFail($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists('firstExamResultsFlagForDisplay', $row) && $row['firstExamResultsFlagForDisplay']){
                $data[$field] = $row['firstExamResultsFlagForDisplay'];
            }
            if (array_key_exists('oneExemptionFlag', $row) && $row['oneExemptionFlag'] != 1) {
                if (array_key_exists('primaryPassFailFlag', $row) && $row['primaryPassFailFlag'] == 1) {
                    $data[$field] = '合格';
                }elseif (array_key_exists('primaryPassFailFlag', $row) && $row['primaryPassFailFlag'] == 0) {
                    $data[$field] = '不合格';
                }
            }
        }
        return $data;
    }
    
    public function secondPassFail($row,$field) {
        $data[$field] = '';
        if (!empty($row)) {
            if (array_key_exists('eikenLevelId', $row)) {
                if (array_key_exists('secondPassFailFlag', $row) &&  $row['secondPassFailFlag'] == 1) {
                    $data[$field] = '合格';
                }
                if (array_key_exists('secondPassFailFlag', $row) &&  $row['secondPassFailFlag'] === 0) {
                    $data[$field] = '不合格';
                }
            }
        }
        return $data;
    }
    
    public function typeFieldScore($row,$field,$fieldFull) {
        $nameScore = str_replace('Field', '', $field);
        $data[$fieldFull] = '';
        if (!empty($row)) {
            if(array_key_exists('oneExemptionFlag', $row) && array_key_exists($field, $row) && array_key_exists($nameScore, $row)){
                if($row['oneExemptionFlag'] == 0 || $row['oneExemptionFlag'] == null){
                    if($row[$field] != null && $row[$nameScore] != null){
                        $data[$fieldFull] = $row[$field] . '/' . $row[$nameScore];
                    }
                }
            }
        }
        return $data;
    }
    
    private function vocabularyFieldScoreOfListOfEikenHistoryPupil($row,$field) {
        return $this->typeFieldScore($row,'vocabularyFieldScore',$field);
    }
    
    private function readingFieldScoreOfListOfEikenHistoryPupil($row,$field) {
        return $this->typeFieldScore($row,'readingFieldScore',$field);
    }
    
    private function listeningFieldScoreOfListOfEikenHistoryPupil($row,$field) {
        return $this->typeFieldScore($row,'listeningFieldScore',$field);
    }
    
    private function compositionFieldScoreOfListOfEikenHistoryPupil($row,$field) {
        return $this->typeFieldScore($row,'compositionFieldScore',$field);
    }
    
    public function scoreAccordingdata($row,$field,$fieldFull) {
        $nameFielding = str_replace('scoreAccordingField', 'scoringAccordingField', $field);
        $scoreAccordingField = array_key_exists($field,$row) ? str_replace(array(' ', ' ', '　'), array('', '', ''), $row[$field]) : '';
        $scoringAccordingField = array_key_exists($nameFielding,$row) ?  str_replace(array(' ', ' ', '　'), array('', '', ''), $row[$nameFielding]) : '';
        $data[$fieldFull] = '';
        if (!empty($row)) {
            if($scoreAccordingField && $scoringAccordingField){
                $data[$fieldFull] = $scoreAccordingField . '/' . $scoringAccordingField;
            }
        }

        return $data;
    }
    
    private function scoreAccordingField1OfListOfEikenHistoryPupil($row,$field) {
        return $this->scoreAccordingdata($row,'scoreAccordingField1',$field);
    }
    
    private function scoreAccordingField2OfListOfEikenHistoryPupil($row,$field) {
        return $this->scoreAccordingdata($row,'scoreAccordingField2',$field);
    }
    
    private function scoreAccordingField3OfListOfEikenHistoryPupil($row,$field) {
        return $this->scoreAccordingdata($row,'scoreAccordingField3',$field);
    }
    
    private function scoreAccordingField4OfListOfEikenHistoryPupil($row,$field) {
        return $this->scoreAccordingdata($row,'scoreAccordingField4',$field);
    }
    
    public function totalPrimaryScoreOfListOfEikenHistoryPupil($row,$field) {
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists('eikenLevelId', $row) && array_key_exists('totalPrimaryScore', $row) && array_key_exists('firstExamResultsPerfectScore', $row)){
                if ($row['eikenLevelId'] == 6 || $row['eikenLevelId'] == 7 || $row['oneExemptionFlag'] != 1) {
                    if ($row['totalPrimaryScore'] !== Null || $row['firstExamResultsPerfectScore'] !== Null) {
                        $data[$field] = $row['totalPrimaryScore'] . "/" . $row['firstExamResultsPerfectScore'];
                    }
                }
            }
        }
        return $data;
    }
    
    public function totalSecondScoreOfListOfEikenHistoryPupil($row,$field) {
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists('eikenLevelId', $row) && array_key_exists('totalSecondScore', $row) && array_key_exists('secondExamResultsPerfectScore', $row)){
                if ($row['eikenLevelId'] != 6 && $row['eikenLevelId'] != 7) {
                    if ($row['totalSecondScore'] !== Null || $row['secondExamResultsPerfectScore'] !== Null) {
                        $data[$field] = $row['totalSecondScore'] . "/" . $row['secondExamResultsPerfectScore'];
                    }
                }
            }
        }
        return $data;
    }
    
    public function cseScore($row,$field) {
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists('cSEScoreReading',$row) && array_key_exists('cSEScoreListening',$row) && array_key_exists('cSEScoreWriting',$row) && array_key_exists('cSEScoreSpeaking',$row)){
                if ($row['cSEScoreReading'] !== Null || $row['cSEScoreListening'] !== Null || $row['cSEScoreWriting'] !== Null || $row['cSEScoreSpeaking'] !== Null) {
                    $data[$field] = $row['cSEScoreReading'] + $row['cSEScoreListening'] + $row['cSEScoreWriting'] + $row['cSEScoreSpeaking'];
                }
            }
        }
        return $data;
    }
    //    end listOfEikenHistoryPupil
    //start listOfEikenExamResult
    
    private function getListEikenLevelId(){
        $em = $this->getEntityManager();
        $this->eikenLevelId = $em->getRepository('\Application\Entity\EikenLevel')->ListEikenLevel();
    }
    
    public function eikenLevelId($row,$field) {
        if(empty($this->eikenLevelId)){
            $this->getListEikenLevelId();
        }
        $eikenLevels = $this->eikenLevelId;
        
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists($field, $row)){
                $data[$field] = $row[$field];
                if(!empty($eikenLevels[$row[$field]])){
                   $data[$field] = $eikenLevels[$row[$field]]['levelName'];
                }
            }
        }
        return $data;
    }
    
    private function getListHallClassification(){
        $config = $this->serviceLocator->get('config');
        $hallClassification = $config['hallClassification'];
        $this->hallClassification = $hallClassification;
    }
    
    private function hallClassification($row,$field) {
        if(empty($this->hallClassification)){
            $this->getListHallClassification();
        }
        $hallClassification = $this->hallClassification;
        
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists($field, $row)){
                $data[$field] = $row[$field];
                if(!empty($hallClassification[$row[$field]])){
                    $data[$field] = $hallClassification[$row[$field]];
                }
            }
        }
        return $data;
    }
    
    private function getListDayOfTheWeek(){
        $config = $this->serviceLocator->get('config');
        $dayOfTheWeek = $config['dayOfTheWeek'];
        $this->dayOfTheWeek = $dayOfTheWeek;
    }
    
    private function dayOfTheWeek($row,$field) {
        if(empty($this->dayOfTheWeek)){
            $this->getListDayOfTheWeek();
        }
        $dayOfTheWeek = $this->dayOfTheWeek;
        
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists($field, $row)){
                $data[$field] = $row[$field];
                if(!empty($dayOfTheWeek[$row[$field]])){
                    $data[$field] = $dayOfTheWeek[$row[$field]];
                }
            }
        }
        return $data;
    }
    
    private function executionDayOfTheWeek($row,$field) {
        return $this->dayOfTheWeek($row,$field);
    }
    
    private function secondExecutionDayOfTheWeek($row,$field) {
        return $this->dayOfTheWeek($row,$field);
    }
    
    private function getListOneExemptionFlag(){
        $config = $this->serviceLocator->get('config');
        $oneExemptionFlag = $config['oneExemptionFlag'];
        $this->oneExemptionFlag = $oneExemptionFlag;
    }
    
    private function oneExemptionFlag($row,$field) {
        if(empty($this->oneExemptionFlag)){
            $this->getListOneExemptionFlag();
        }
        $oneExemptionFlag = $this->oneExemptionFlag;
        
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists($field, $row)){
                $data[$field] = $row[$field];
                if(!empty($oneExemptionFlag[$row[$field]])){
                    $data[$field] = $oneExemptionFlag[$row[$field]];
                }
            }
        }
        return $data;
    }
    
    private function getListPassFailFlag(){
        $config = $this->serviceLocator->get('config');
        $passFailFlag = $config['passFailFlag'];
        $this->passFailFlag = $passFailFlag;
    }
    
    private function getValuePassFailFlag($row,$field) {
        if(empty($this->passFailFlag)){
            $this->getListPassFailFlag();
        }
        $passFailFlag = $this->passFailFlag;
        
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists($field, $row)){
                $data[$field] = $row[$field];
                if(!empty($passFailFlag[$row[$field]])){
                    $data[$field] = $passFailFlag[$row[$field]];
                }
            }
        }
        return $data;
    }
    
    private function primaryPassFailFlag($row,$field) {
        return $this->getValuePassFailFlag($row,$field);
    }
    
    private function secondPassFailFlag($row,$field) {
        return $this->getValuePassFailFlag($row,$field);
    }
    
    private function getListClassification(){
        $config = $this->serviceLocator->get('config');
        $passFailFlag = $config['School_Code'];
        $this->schoolCode = $passFailFlag;
    }
    
    private function schoolClassification($row,$field) {
        if(empty($this->schoolCode)){
            $this->getListClassification();
        }
        $classification = $this->schoolCode;
        
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists($field, $row)){
                $data[$field] = $row[$field];
                if(!empty($classification[$row[$field]])){
                    $data[$field] = $classification[$row[$field]];
                }
            }
        }
        return $data;
    }
    
    private function getListSex(){
        $config = $this->serviceLocator->get('config');
        $sex = $config['sex'];
        $this->sex = $sex;
    }
    
    private function sex($row,$field) {
        if(empty($this->sex)){
            $this->getListSex();
        }
        $sex = $this->sex;
        
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists($field, $row)){
                $data[$field] = $row[$field];
                if(!empty($sex[$row[$field]])){
                    $data[$field] = $sex[$row[$field]];
                }
            }
        }
        return $data;
    }
    
    private function getListBarCodeStatus(){
        $config = $this->serviceLocator->get('config');
        $barCodeStatus = $config['barCodeStatus'];
        $this->barCodeStatus = $barCodeStatus;
    }
    
    private function getValueBarCodeStatus($row,$field) {
        if(empty($this->barCodeStatus)){
            $this->getListBarCodeStatus();
        }
        $barCodeStatus = $this->barCodeStatus;
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists($field, $row)){
                $data[$field] = $row[$field];
                if(!empty($barCodeStatus[$row[$field]])){
                    $data[$field] = $barCodeStatus[$row[$field]];
                }
            }
        }
        return $data;
    }
    
    private function barCodeStatus($row,$field) {
        return $this->getValueBarCodeStatus($row,$field);
    }
    
    private function secondBarCodeStatus($row,$field) {
        return $this->getValueBarCodeStatus($row,$field);
    }
    
    private function getListFirstExamResultsFlag(){
        $config = $this->serviceLocator->get('config');
        $firstExamResultsFlag = $config['firstExamResultsFlag'];
        $this->firstExamResultsFlag = $firstExamResultsFlag;
    }
    
    private function firstExamResultsFlag($row,$field) {
        if(empty($this->firstExamResultsFlag)){
            $this->getListFirstExamResultsFlag();
        }
        $firstExamResultsFlag = $this->firstExamResultsFlag;
        
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists($field, $row)){
                $data[$field] = $row[$field];
                if(!empty($firstExamResultsFlag[$row[$field]])){
                    $data[$field] = $firstExamResultsFlag[$row[$field]];
                }
            }
        }
        return $data;
    }
    
    private function dateFullFormatExcel($row,$field) {
        $data[$field] = array_key_exists($field, $row) ? !empty($row[$field]) ? $row[$field]->format('Y/m/d H:i:s') : $row[$field] : '';
        return $data;
    }
    
    private function insertDate($row,$field) {
         return $this->dateFullFormatExcel($row,$field);
    }
    
    private function updateDate($row,$field) {
         return $this->dateFullFormatExcel($row,$field);
    }
    
    private function dateFormatExcel($row,$field) {
        $data[$field] = array_key_exists($field, $row) ? !empty($row[$field]) ? $row[$field]->format('Y/m/d') : $row[$field] : '';
        return $data;
    }
    
    private function certificationDate($row,$field) {
         return $this->dateFormatExcel($row,$field);
    }
    
    private function secondCertificationDate($row,$field) {
         return $this->dateFormatExcel($row,$field);
    }
    
    private function birthday($row,$field) {
         return $this->dateFormatExcel($row,$field);
    }
    
    private function firstMailSendFlag($row,$field) {
        $data[$field] = array_key_exists($field, $row) ? $row[$field] == 1 ? '送信済み' : $row[$field] : '';
        return $data;
    }
    // end listOfEikenExamResult
  
//    private function birthday($data, $field){
//         $result = '';
//        if(!empty($data[$field])){
//            $result = $data[$field]->format('Y/m/d H:i:s');
//        }
//        return array($field => $result);
//    }
    
     private function processDate($data, $field){
        if(!empty($data)){
            return array($field => $data[$field]->format('Y/m/d H:i:s'));
        }
    }
    
    private function answerSerialize($data, $field){
        if(!empty($data)){
            $answerSerialize = json_decode($data);
            $result = array();
            foreach ($answerSerialize as $key => $val) {
                $result['answer' . ($key + 1)] = $val;
            }
            return $result;
        }
    }
    
    private function accuraryJugdeSerialize($data, $field){
        if(!empty($data)){
            $answerSerialize = json_decode($data);
            $result = array();
            foreach ($answerSerialize as $key => $val) {
                $result['answer' . ($key + 1)] = $val;
            }
            return $result;
        }
    }
    
    private function resultDocOutput($data, $field){
        $result = $this->translate('resultDocOutput-1');
        if($data[$field] == '02'){
            $result = $this->translate('resultDocOutput-2');
        }
        if($data[$field] == '03'){
            $result = $this->translate('resultDocOutput-3');
        }
        return array($field => $result);
    }
    
    private function eikenLevelKyu($data, $field){
        $result = $this->translate('eikenLevel-1');
        if($data[$field] == '02'){
            $result = $this->translate('eikenLevel-2');
        }
        return array($field => $result);
    }
    
    private function rankDisplay($data, $field){
        $result = $this->translate('rankDisplay-1');
        if($data[$field] == '02'){
            $result = $this->translate('rankDisplay-2');
        }
        if($data[$field] == '03'){
            $result = $this->translate('rankDisplay-3');
        }
        return array($field => $result);
    }
    
    private function eikenIdDisplay($data, $field){
        $result = $this->translate('eikenIdDisplay-1');
        if($data[$field] == '02'){
            $result = $this->translate('eikenIdDisplay-2');
        }
        return array($field => $result);
    }
    
    private function titleUpdate($data, $field){
        $result = $this->translate('titleUpdate-1');
        if($data[$field] == '02'){
            $result = $this->translate('titleUpdate-2');
        }
        return array($field => $result);
    }
    
    /* get special field in history pupil IBA */
    private function examDate($data, $field){
        if(!empty($data[$field])){
            return $this->dateFormatExcel($data,$field);
        }
    }
    
    private function testSet($row, $field){
        $row[$field] = '';
        if (!empty($row)) {
            if(array_key_exists('testType', $row) && !empty($row['testType']) && array_key_exists('testSetNo', $row) && !empty($row['testSetNo'])){
                $row[$field] = $row['testType'] . '-' . $row['testSetNo'];
            }
        }
        return array($field =>$row[$field]);
    }
    
    private function correctAnswerPercentGrammar($data, $field){
        $result = '';
        if (!empty($data[$field])) {
            $result = $data[$field].'%';
        }
        return array($field => $result);
    }
    
    private function correctAnswerPercentReading($data, $field){
        $result = '';
        if (!empty($data[$field])) {
            $result = $data[$field].'%';
        }
        return array($field => $result);
    }
    
    private function correctAnswerPercentListening($data, $field){
        $result = '';
        if (!empty($data[$field])) {
            $result = $data[$field].'%';
        }
        return array($field => $result);
    }
    
    private function number($row, $field){
        $data[$field] = '';
        if (!empty($row)) {
            if($row['pupilId'] !== '' && array_key_exists('pupilId', $row)){
                $data[$field] = (array_key_exists('pupilNo', $row) && $row['pupilNo'] !== '') ? $row['pupilNo'] : '';
            }
        }
        return array($field => $data[$field]);
        
    }
    private function eikenLevelTotal($data, $field){       
        $result = '';
        if (!empty($data)) {
//        update function for : #GNCCNCJDR5-761
            $testType = isset($data['testType']) ? $data['testType'] : '';
            $total = isset($data['total']) ? $data['total'] : '';
            if(empty($this->ibaMasterDataScore)){
                $this->ibaMasterDataScore = $this->getIBAMasterDataScore();
            }
            $result = MappingUtility::getKyuName($this->ibaMasterDataScore, HistoryConst::IBA_RESULT_TOTAL, $testType, $total);
        }
        return array($field => $result);
    }
    
    private function ekenLevelRead($data, $field){
        $result = '';
        if (!empty($data)) {
//        update function for : #GNCCNCJDR5-761
            $testType = isset($data['testType']) ? $data['testType'] : '';
            $total = isset($data['read']) ? $data['read'] : '';
            if(empty($this->ibaMasterDataScore)){
                $this->ibaMasterDataScore = $this->getIBAMasterDataScore();
            }
            $result = MappingUtility::getKyuName($this->ibaMasterDataScore, HistoryConst::IBA_RESULT_READING, $testType, $total);
        }
        return array($field => $result);
    }
    
    private function eikenLevelListening($data, $field){
        $result = '';
        if (!empty($data)) {
//        update function for : #GNCCNCJDR5-761
            $testType = isset($data['testType']) ? $data['testType'] : '';
            $total = isset($data['listen']) ? $data['listen'] : '';
            if(empty($this->ibaMasterDataScore)){
                $this->ibaMasterDataScore = $this->getIBAMasterDataScore();
            }
            $result = MappingUtility::getKyuName($this->ibaMasterDataScore, HistoryConst::IBA_RESULT_LISTENING, $testType, $total);
        }
        return array($field => $result);
    }
    private function mappingStatus($data, $field) {
        $result = '';
        if (!empty($data)) {
            $configData = $this->getServiceLocator()->get('Config')['confirmStatus'];
            if(isset($configData[$data[$field]])){
                $result = $configData[$data[$field]];
            }
            if(empty($result)){
                $result = $configData[0];
            }
            $result = array($field => $result);
        }
        return $result;
    }
    
    private function examType($data, $field){
        $result = $this->translate('');
        if($data[$field] == '01' || $data[$field] == '02'){
            $result = $this->translate('examType_IBA');
        }
        return array($field => $result);
    }
    
    private function scoreRound1($row, $field) {
        $data[$field] = '';
        if (!empty($row)) {
            if (array_key_exists('eikenLevelId', $row) && array_key_exists('cSEScoreReading', $row) && array_key_exists('cSEScoreListening', $row) && array_key_exists('cSEScoreWriting', $row)) {
                $data[$field] = (int) $row['cSEScoreReading'] + (int) $row['cSEScoreListening'];
                if ($row['eikenLevelId'] < 6) {
                    $data[$field] = (int) $row['cSEScoreReading'] + (int) $row['cSEScoreListening'] + (int) $row['cSEScoreWriting'];
                }
            }
        }
        return $data;
    }
    
    private function fullName($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
                $data[$field] = $row[$field] != '' ? $row[$field] : '';
        }
        return $data;
    }
    
    private function displayName($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
                $data[$field] = $row[$field] != '' ? $row[$field] : '';
        }
        return $data;
    }
    
    private function className($row,$field){
        $data[$field] = '';
        if (!empty($row) && isset($row[$field])) {
                $data[$field] = $row[$field] != '' ? $row[$field] : '';
        }
        return $data;
    }
    
    private function levelName($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
                $data[$field] = $row[$field] != '' ? $row[$field] : '';
        }
        return $data;
    }
    
    private function recommendEikenLevelId($row,$field){
        if(empty($this->eikenLevelId)){
            $this->getListEikenLevelId();
        }
        $eikenLevels = $this->eikenLevelId;
        $data[$field] = '';
        if (!empty($row)) {
            if(array_key_exists($field, $row)){
                $data[$field] = $row[$field];
                if(!empty($eikenLevels[$row[$field]])){
                   $data[$field] = $eikenLevels[$row[$field]]['levelName'];
                }
            }
        }
        return $data;
    }
    
    private function hallType($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
            if($row[$field] == '1'){ 
                $data[$field] = '本会場'; 
            }else if($row[$field] == '0'){ 
                $data[$field] = '準会場'; 
            }
        }
        return $data;
    }
    
    private function paymentStatus($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
            if($row[$field] == '1'){ 
                $data[$field] = '支払済'; 
            }else if($row[$field] == '0'){ 
                $data[$field] = '未支払'; 
            }
        }
        return $data;
    }
    
    private function paymentDate($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
            return array($field => $row[$field] ? $row[$field]->format('Y/m/d') : '');
        }
        return $data;
    }
    
    private function paymentBy($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
            if($row['paymentStatus'] == '1'){
                if($row[$field] == '1'){ 
                    $data[$field] = 'クレジット'; 
                }else{ 
                    $data[$field] = 'コンビ二'; 
                }
            }
        }
        return $data;
    }
    
    private function registerStatus($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
            if($row[$field] == '1'){ 
                $data[$field] = '登録済'; 
            }else if($row[$field] == '0'){ 
                $data[$field] = '未登録'; 
            }
        }
        return $data;
    }
    
    private function regDateOnSatellite($row,$field){
        $data[$field] = '';
        if (!empty($row)) {
            return array($field => $row[$field] ? $row[$field]->format('Y/m/d') : '');
        }
        return $data;
    }
    
}
