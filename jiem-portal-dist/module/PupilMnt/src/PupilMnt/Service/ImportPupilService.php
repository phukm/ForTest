<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PupilMnt\Service;

use PupilMnt\Service\ServiceInterface\ImportPupilServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Dantai\PrivateSession;
use Dantai\Utility\CharsetConverter;
use Dantai\Utility\CsvHelper;
use Dantai\Utility\DateHelper;
use stdClass;
use Zend\Json\Json;
use Zend\View\Model\ViewModel;
use PupilMnt\PupilConst;
use Dantai\Utility\PHPExcel;
use PupilMnt\SurnameConst;
use Application\ApplicationConst;

class ImportPupilService implements ImportPupilServiceInterface, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    private $em;
    private $organizationId;
    private $organizationName;
    private $dataCsv;
    private $dataExcel;
    private $eikenLevels;
    private $entityPupil;
    private $orgSchoolYearRepo;
    private $classjRepo;
    private $schoolYearRepo;
    private $listGradeMapping;

    public function __construct() {
        $user = PrivateSession::getData('userIdentity');
        $this->organizationId = $user['organizationId'];
        $this->organizationName = $user['organizationName'];
    }

    public function setOrgSchoolYearRepository($orgSchoolYearRepo = Null) {
        $this->orgSchoolYearRepo = $orgSchoolYearRepo ? $orgSchoolYearRepo : $this->getEntityManager()->getRepository('Application\Entity\OrgSchoolYear');
    }

    public function setClassJRepository($classjRepo = Null) {
        $this->classjRepo = $classjRepo ? $classjRepo : $this->getEntityManager()->getRepository('Application\Entity\ClassJ');
    }

    public function setSchoolYearRepository($schoolYearRepo = Null) {
        $this->schoolYearRepo = $schoolYearRepo ? $schoolYearRepo : $this->getEntityManager()->getRepository('Application\Entity\SchoolYear');
    }

    public function getListOrgSchoolYearOfOrg() {
        $result = array();
        $schoolYearIdsUsed = array();
        if (!$this->orgSchoolYearRepo) {
            $this->setOrgSchoolYearRepository();
        }
        $orgSchoolYears = $this->orgSchoolYearRepo->getOrgSchoolYearName($this->organizationId);
        if (!$orgSchoolYears) {
            return array($result, $schoolYearIdsUsed);
        }
        foreach ($orgSchoolYears as $value) {
            $key = trim($value['displayName']);
            $result[$key] = array(
                'orgSchoolYearId' => $value['orgSchoolYearId'],
                'orgSchoolYearName' => $value['displayName'],
                'schoolYearId' => $value['schoolYearId'],
                'schoolYearName' => $value['name'],
            );
            $schoolYearIdsUsed[] = $value['schoolYearId'];
        }
        return array($result, $schoolYearIdsUsed);
    }

    public function getListClassOfOrg() {
        $prefix = PupilConst::DELIMITER_VALUE;
        $result = array();
        if (!$this->classjRepo) {
            $this->setClassJRepository();
        }
        $classes = $this->classjRepo->getListClassByOrgAndGrade($this->organizationId);
        if (!$classes) {
            return $result;
        }
        foreach ($classes as $row) {
            $key = trim($row['displayName']) . $prefix . trim($row['name']) . $prefix . trim($row['className']) . $prefix . intval($row['year']);
            $result[$key] = trim($row['className']);
        }
        return $result;
    }

    public function getKeyOfFieldImport() {
        return array(
            'Year' => 0,
            'OrgSchoolYear' => 1,
            'Class' => 2,
            'PupilNumber' => 3,
            'FirstnameKanji' => 4,
            'LastnameKanji' => 5,
            'FirstnameKana' => 6,
            'LastnameKana' => 7,
            'Birthday' => 8,
            'Gender' => 9,
            'EinaviId' => 10,
            'EikenId' => 11,
            'EikenPassword' => 12,
            'EikenLevel' => 13,
            'EikenYear' => 14,
            'Kai' => 15,
            'EikenScoreReading' => 16,
            'EikenScoreListening' => 17,
            'EikenScoreWriting' => 18,
            'EikenScoreSpeaking' => 19,
            'IbaLevel' => 20,
            'IbaDate' => 21,
            'IbaScoreReading' => 22,
            'IbaScoreListening' => 23,
            'WordLevel' => 24,
            'GrammarLevel' => 25
        );
    }
    
    public function keyOfFileForSeperate() {
        return array(
            'Year' => 0,
            'OrgSchoolYear' => 1,
            'Class' => 2,
            'PupilNumber' => 3,
            'NameKanji' => 4,
            'FirstnameKana' => 5,
            'LastnameKana' => 6,
            'Birthday' => 7,
            'Gender' => 8,
            'EinaviId' => 9,
            'EikenId' => 10,
            'EikenPassword' => 11,
            'EikenLevel' => 12,
            'EikenYear' => 13,
            'Kai' => 14,
            'EikenScoreReading' => 15,
            'EikenScoreListening' => 16,
            'EikenScoreWriting' => 17,
            'EikenScoreSpeaking' => 18,
            'IbaLevel' => 19,
            'IbaDate' => 20,
            'IbaScoreReading' => 21,
            'IbaScoreListening' => 22,
            'WordLevel' => 23,
            'GrammarLevel' => 24
        );
    }

    public function validateFileImportPupil($fileImport) {
        $result = array('status' => 0, 'message' => '');
        $dataImport = array();
        if (!is_array($fileImport) || empty($fileImport["name"]) || (isset($fileImport['error']) && $fileImport['error'] == 4)) {
            $result['message'] = $this->translate('NotFileSelect');
            return array($result, $dataImport);
        }

        $fileImport["tmp_name"] = isset($fileImport["tmp_name"]) ? str_replace("\\", '/', $fileImport["tmp_name"]) : '';
        $extension = isset($fileImport["name"]) ? strtolower(pathinfo($fileImport["name"], PATHINFO_EXTENSION)) : '';
        if (empty($fileImport["tmp_name"]) || !in_array($extension, array('csv', 'xlsx', 'xls'))) {
            $result['message'] = $this->translate('MsgFileNotCSV_28');
            return array($result, $dataImport);
        }
        $dataImport = $this->getDataImportByFile($fileImport["tmp_name"], $extension);

        if (!isset($dataImport[0]) && count($dataImport[0]) != 26) {
            $result['message'] = $this->translate('MsgFileNotLikeTemplate_29_FBR5');
            return array($result, $dataImport);
        }
        $headerImport = $this->getDataHeaderImport();
        if (array_diff($headerImport, $dataImport[0]) !== array_diff($dataImport[0], $headerImport)) {
            $result['message'] = $this->translate('MsgFileNotLikeTemplate_29_FBR5');
            return array($result, $dataImport);
        }
        foreach ($dataImport as $lineImport) {
            if (count($lineImport) != 26) {
                $result['message'] = $this->translate('MsgFileNotLikeTemplate_29_FBR5');
                return array($result, $dataImport);
            }
        }
        if (!isset($dataImport[1])) {
            $result['message'] = $this->translate('MsgFileHasNotDataPupil');
            return array($result, $dataImport);
        }
        if ((count($dataImport) > 5001)) {

            $result['message'] = $this->translate('MsgFileHasDataPupilGreater1000_30');
            return array($result, $dataImport);
        }
        $result['status'] = 1;
        $result['message'] = 'Success';
        return array($result, $dataImport);
    }
    
    public function validateFileSeperatePupil($fileImport){
        $result = array('status' => 0, 'message' => '');
        $dataImport = array();
        if (!is_array($fileImport) || empty($fileImport["name"]) || (isset($fileImport['error']) && $fileImport['error'] == 4)) {
            $result['message'] = $this->translate('NotFileSelect');
            return array($result, $dataImport);
        }
        
        $fileImport["tmp_name"] = isset($fileImport["tmp_name"]) ? str_replace("\\", '/', $fileImport["tmp_name"]) : '';
        $extension = isset($fileImport["name"]) ? strtolower(pathinfo($fileImport["name"], PATHINFO_EXTENSION)) : '';
        if (empty($fileImport["tmp_name"]) || !in_array($extension, array('csv', 'xlsx', 'xls'))) {
            $result['message'] = $this->translate('MsgFileNotCSV_28');
            return array($result, $dataImport);
        }
        $dataImport = $this->getDataImportByFile($fileImport["tmp_name"], $extension);
        if (!isset($dataImport[0]) || count($dataImport[0]) != 25) {
            $result['message'] = $this->translate('MsgFileNotLikeTemplate_29_FBR5');
            return array($result, $dataImport);
        }
        $headerImport = $this->getDataHeaderSeperateImport();
        if (array_diff($headerImport, $dataImport[0]) !== array_diff($dataImport[0], $headerImport)) {
            $result['message'] = $this->translate('MsgFileNotLikeTemplate_29_FBR5');
            return array($result, $dataImport);
        }
        foreach ($dataImport as $lineImport) {
            if (count($lineImport) != 25) {
                $result['message'] = $this->translate('MsgFileNotLikeTemplate_29_FBR5');
                return array($result, $dataImport);
            }
        }
        if (!isset($dataImport[1])) {
            $result['message'] = $this->translate('MsgFileHasNotDataPupil');
            return array($result, $dataImport);
        }
        $result['status'] = 1;
        $result['message'] = PupilConst::PASS_ALL_FILE_VALIDATION;
        
        return array($result, $dataImport);
    }
    
    public function getDataHeaderSeperateImport() {
        $field = $this->keyOfFileForSeperate();
        $headerImport = array();
        foreach ($field as $key => $value) {
            $headerImport[] = $this->translate('Seperate' . $key);
        }

        return $headerImport;
    }

    public function getDataImportByFile($tmpFile, $extension) {
        if ($extension == 'csv') {
            if (!$this->dataCsv) {
                $this->setDataCsv($tmpFile);
            }
            $dataImport = CsvHelper::csvStrToArray($this->dataCsv);
        } else {
            if (!$this->dataExcel) {
                $this->setDataExcel($tmpFile);
            }
            $dataImport = $this->dataExcel;
        }
        return $dataImport;
    }

    public function setDataCsv($tmpFile, $dataCsv = null) {
        $this->dataCsv = $dataCsv ? $dataCsv : CharsetConverter::shiftJisToUtf8(@file_get_contents($tmpFile));
    }

    public function setDataExcel($tmpFile, $dataExcel = null) {
        $this->dataExcel = $dataExcel ? $dataExcel : PHPExcel::excelToArray($tmpFile);
    }

    public function getDataFromFileImport(array $dataFile) {
        $dataImport = array();
        if (!$dataFile) {
            return $dataImport;
        }
        $fields = $this->getKeyOfFieldImport();
        unset($dataFile[0]);
        $index = 0;
        foreach ($dataFile as $lineImport) {
            foreach ($fields as $keyField => $column) {
                $valueOfColumn = isset($lineImport[$column]) ? trim($lineImport[$column]) : '';
                if (in_array($keyField, array('SchoolYear', 'Class'))) {
                    $valueOfColumn = $this->convertKanaFullWidthToHalfWidth($this->convertCharFullWidthToHalfWidth($valueOfColumn));
                }
                $dataImport[$index][lcfirst($keyField)] = $valueOfColumn;
            }
            $index++;
        }

        return $dataImport;
    }

    public function getDataHeaderImport() {
        $field = $this->getKeyOfFieldImport();
        $headerImport = array();
        foreach ($field as $key => $value) {
            $headerImport[] = $this->translate('Import' . $key);
        }

        return $headerImport;
    }

    public function translate($messageKey) {
        $translator = $this->getServiceLocator()->get('MVCTranslator');

        return $translator->translate($messageKey);
    }

    public function isYearFormat($year) {
        if (preg_match('/^[0-9]{4}$/', $year) && intval($year) > 2009 && intval($year) <= (date('Y') + 2)) {
            return true;
        } else {
            return false;
        }
    }

    public function validateCommonMasterData($dataImport) {
        $i = 1;
        $errors = array();
        $orgSchoolYearsNew = array();
        list($listOrgSchoolYear, $schoolYearIdsUsed) = $this->getListOrgSchoolYearOfOrg();
        foreach ($dataImport as $keyImport => $row) {
            if (!$row['year']) {
                $errors[$i][$this->translate('year')] = $this->translate('MsgYearError1');
            }
            if (!$row['orgSchoolYear']) {
                $errors[$i][$this->translate('grade')] = $this->translate('MsgSchoolYearError1');
            }
            if (!$row['class']) {
                $errors[$i][$this->translate('class')] = $this->translate('MsgClassError1');
            }

            if ($row['year'] && !$this->isYearFormat($row['year'])) {
                $errors[$i][$this->translate('year')] = $this->translate('MsgYearError2');
            }

            if (!isset($errors[$i])) {
                $key = trim($row['orgSchoolYear']);
                if (!array_key_exists($key, $listOrgSchoolYear)) {
                    $orgSchoolYearsNew[$key] = $key;
                } else {
                    $dataImport[$keyImport]['schoolYear'] = $listOrgSchoolYear[$key]['schoolYearName'];
                }
            }
            $i++;
        }
        $schoolYearIdsUsed = isset($schoolYearIdsUsed) ? array_unique($schoolYearIdsUsed) : array();

        return array($errors, $orgSchoolYearsNew, $schoolYearIdsUsed, $dataImport);
    }

    public function getListMasterData($dataImport) {
        $masterData = array();
        $prefix = PupilConst::DELIMITER_VALUE;
        $classes = $this->getListClassOfOrg();
        foreach ($dataImport as $row) {
            $key = trim($row['orgSchoolYear']) . $prefix . trim($row['schoolYear']) . $prefix . trim($row['class']) . $prefix . intval($row['year']);
            if (!array_key_exists($key, $classes)) {
                $masterData[$key] = array(
                    'Year' => $row['year'],
                    'SchoolYear' => $row['schoolYear'],
                    'OrgSchoolYear' => $row['orgSchoolYear'],
                    'Class' => $row['class'],
                );
            }
        }
        return $masterData;
    }

    public function saveDataPupil($data, $pupilId = null) {
        $data = $this->getSchoolIdAndClassId($data);
        if (empty($data)) {
            return 0;
        }
        foreach ($data as $key => $items) {
            $pupil = $this->saveProfilePupil($items, $pupilId);
            $eikenScore = $this->saveResultEikenScore($items, $pupil);
            $ibaScore = $this->saveResultIbaScore($items, $pupil);
            $simpleMeasurementResult = $this->saveSimpleMeasurementResult($items, $pupil);
            if ($pupil) {
                $this->getEntityManager()->persist($pupil);
            }
            if ($eikenScore) {
                $this->getEntityManager()->persist($eikenScore);
            }
            if ($ibaScore) {
                $this->getEntityManager()->persist($ibaScore);
            }
            if ($simpleMeasurementResult) {
                $this->getEntityManager()->persist($simpleMeasurementResult);
            }
            if ($key % 500) {
                $this->getEntityManager()->flush();
                $this->getEntityManager()->clear();
            }
        }
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        return 1;
    }

    public function getSchoolIdAndClassId($data) {
        $prefix = PupilConst::DELIMITER_VALUE;
        $listClassName = $totalGrade = $listSchoolClass = array();
        $listMasterDataOfFile = array();
        foreach ($data as $key => $items) {
            $className = $this->strReplace($items['class']);
            $listClassName[$className] = $className;

            $keyMasterData = $items['orgSchoolYear'] . $prefix . $items['class'] . $prefix . $items['year'];
            $listMasterDataOfFile[$keyMasterData] = $keyMasterData;
        }
        if (!$listClassName) {
            return false;
        }
        $listId = $this->getEntityManager()->getRepository('Application\Entity\ClassJ')->getListSchoolAndClassId($listClassName, $this->organizationId);

        foreach ($listId as $item) {
            $listSchoolClass[$item['year']][$item['class']][$item['orgSchoolYear']] = array(
                'classId' => $item['id'],
                'orgSchoolYearId' => $item['orgSchoolYearId']
            );
            $keyMasterData = $item['orgSchoolYear'] . $prefix . $item['class'] . $prefix . $item['year'];
            $listMasterDataOfDb[$keyMasterData] = $keyMasterData;
        }
        $isCorrectData = true;
        foreach ($listMasterDataOfFile as $key => $value) {
            if (!array_key_exists($value, $listMasterDataOfDb)) {
                $isCorrectData = false;
                break;
            }
        }
        if (!$isCorrectData) {
            return false;
        }
        foreach ($data as $key => $items) {
            if (!empty($listSchoolClass[$items['year']][$items['class']][$items['orgSchoolYear']])) {
                $data[$key]['orgSchoolYearId'] = $listSchoolClass[$items['year']][$items['class']][$items['orgSchoolYear']]['orgSchoolYearId'];
                $data[$key]['classId'] = $listSchoolClass[$items['year']][$items['class']][$items['orgSchoolYear']]['classId'];
            }
        }

        return $data;
    }

    public function saveProfilePupil($data, $pupilId = null) {
        $result = new \Application\Entity\Pupil();
        if ($pupilId) {
            $result = $this->getEntityManager()->getReference('Application\Entity\Pupil', $pupilId);
        }
        $result->setNumber($this->strReplace($data['pupilNumber']));
        $result->setFirstNameKanji($this->strReplace($data['firstnameKanji']));
        $result->setLastNameKanji($this->strReplace($data['lastnameKanji']));
        $result->setFirstNameKana($this->strReplaceSpace($data['firstnameKana']));
        $result->setLastNameKana($this->strReplaceSpace($data['lastnameKana']));
        $result->setBirthday(!empty($data['birthday']) ? new \Datetime($data['birthday']) : null);
        $result->setGender(isset($data['gender']) && $data['gender'] !== '' ? ($data['gender'] == '女' ? 0 : 1) : -1);
        $result->setYear(isset($data['year']) ? $data['year'] : null);
        if (isset($data['orgSchoolYearId'])) {
            $result->setOrgSchoolYear($this->getEntityManager()->getReference('Application\Entity\OrgSchoolYear', $data['orgSchoolYearId']));
        }
        if (isset($data['classId'])) {
            $result->setClass($this->getEntityManager()->getReference('Application\Entity\ClassJ', $data['classId']));
        }
        $result->setEinaviId($this->strReplace($data['einaviId']));
        $result->setEikenId($this->strReplace($data['eikenId']));
        $result->setEikenPassword($this->strReplace($data['eikenPassword']));
        $result->setOrganization($this->getEntityManager()->getReference('Application\Entity\Organization', $this->organizationId ? $this->organizationId : 1));

        return $result;
    }

    public function saveResultIbaScore($data, $pupil = null, $ibaScoreId = null) {
        $result = new \Application\Entity\IBAScore();
        if ($ibaScoreId) {
            $result = $this->getEntityManager()->getReference('Application\Entity\IBAScore', $ibaScoreId);
        }
        $eikenLevelName = array_flip($this->getServiceLocator()->get('Config')['MappingLevel']);
        $eikenLevelId = !empty($eikenLevelName[$data['ibaLevel']]) ? $eikenLevelName[$data['ibaLevel']] : null;
        if ($pupil) {
            $result->setPupil($pupil);
        }
        if (empty($eikenLevelId)) {
            return;
        }
        $result->setStatus('Active'); // Active/Inactive
        $result->setExamDate(empty($data['ibaDate']) ? null : (new \Datetime($data['ibaDate'])));
        $result->setReadingScore(isset($data['ibaScoreReading']) ? (int) $data['ibaScoreReading'] : null);
        $result->setListeningScore(isset($data['ibaScoreListening']) ? (int) $data['ibaScoreListening'] : null);
        $result->setIBACSETotal((isset($data['ibaScoreReading']) ? (int) $data['ibaScoreReading'] : 0) + (isset($data['ibaScoreListening']) ? (int) $data['ibaScoreListening'] : 0));
        if ($eikenLevelId) {
            $result->setIBALevel($this->getEntityManager()->getReference('Application\Entity\EikenLevel', $eikenLevelId));
        }

        return $result;
    }

    public function saveResultEikenScore($data, $pupil = null, $eikenScoreId = null) {
        $eikenLevelName = array_flip($this->getServiceLocator()->get('Config')['MappingLevel']);
        $eikenLevelId = !empty($eikenLevelName[$data['eikenLevel']]) ? $eikenLevelName[$data['eikenLevel']] : null;
        $result = new \Application\Entity\EikenScore();
        if ($eikenScoreId) {
            $result = $this->getEntityManager()->getReference('Application\Entity\EikenScore', $eikenScoreId);
        }
        if ($pupil) {
            $result->setPupil($pupil);
        }
        if (empty($eikenLevelId)) {
            return;
        }
        $kai = isset($data['kai']) ? (int) $data['kai'] : null;
        $year = isset($data['eikenYear']) ? (int) $data['eikenYear'] : null;
        $pupilService = $this->getServiceLocator()->get('PupilMnt\Service\PupilServiceInterface');
        $examDate = $pupilService->getExamDateByYearAndKaiAndEikenLevel($year, $kai, $eikenLevelId);
        $eikenScoreReading = isset($data['eikenScoreReading']) ? (int) trim($data['eikenScoreReading']) : 0;
        $eikenScoreListening = isset($data['eikenScoreListening']) ? (int) trim($data['eikenScoreListening']) : 0;
        $eikenScoreWriting = isset($data['eikenScoreWriting']) ? (int) trim($data['eikenScoreWriting']) : 0;
        $eikenScoreSpeaking = isset($data['eikenScoreSpeaking']) ? (int) trim($data['eikenScoreSpeaking']) : 0;
        $result->setStatus('Active'); // Active/Inactive
        $result->setPassFailFlag(1);
        $result->setKai($kai);
        $result->setYear($year);
        $result->setCertificationDate($examDate);
        $result->setReadingScore($eikenScoreReading);
        $result->setListeningScore($eikenScoreListening);
        $result->setCSEScoreWriting($eikenScoreWriting);
        $result->setCSEScoreSpeaking($eikenScoreSpeaking);
        $result->setEikenLevel($this->getEntityManager()->getReference('Application\Entity\EikenLevel', $eikenLevelId));
        $result->setEikenCSETotal($eikenScoreReading + $eikenScoreListening + $eikenScoreWriting + $eikenScoreSpeaking);

        return $result;
    }

    public function saveSimpleMeasurementResult($data, $pupil = null, $simpleMeasurementResultId = null) {
        $result = new \Application\Entity\SimpleMeasurementResult();
        if ($simpleMeasurementResultId) {
            $result = $this->getEntityManager()->getReference('Application\Entity\SimpleMeasurementResult', $simpleMeasurementResultId);
        }
        if ($pupil) {
            $result->setPupil($pupil);
        }
        $eikenLevelName = array_flip($this->getServiceLocator()->get('Config')['MappingLevel']);
        if (!empty($eikenLevelName[$data['wordLevel']])) {
            $result->setResultVocabulary($this->getEntityManager()->getReference('Application\Entity\EikenLevel', $eikenLevelName[$data['wordLevel']]));
        }
        if (!empty($eikenLevelName[$data['grammarLevel']])) {
            $result->setResultGrammar($this->getEntityManager()->getReference('Application\Entity\EikenLevel', $eikenLevelName[$data['grammarLevel']]));
        }
        $result->setResultGrammarName($this->strReplace($data['grammarLevel']));
        $result->setResultVocabularyName($this->strReplace($data['wordLevel']));
        $result->setStatus('Active'); // Active/Inactive
        return $result;
    }

    public function strReplace($str) {
        return empty($str) ? null : str_replace(array('""', "'"), array('', ''), trim($str));
    }

    public function strReplaceSpace($str) {
        return empty($str) ? null : str_replace(array('""', "'", ' ', '　'), array('', '', '', ''), trim($str));
    }

    public function saveMasterData($data) {
        if ($data) {
            $orgSchoolYears = array();
            foreach ($data as $value) {
                $orgSchoolYears[$value['SchoolYear']] = $value['OrgSchoolYear'];
            }
            foreach ($orgSchoolYears as $schoolYear => $orgSchoolYear) {
                $newGrade = $this->saveGrade($schoolYear, $orgSchoolYear);
                if($newGrade){
                    $this->getEntityManager()->persist($newGrade);
                }
            }
            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear();

            $listOrgSchoolYear = $this->getEntityManager()
                    ->getRepository('Application\Entity\OrgSchoolYear')
                    ->ListSchoolYear($this->organizationId);
            foreach ($listOrgSchoolYear as $value) {
                $dataOrgSchoolYearDb[$value['displayName']] = $value['id'];
            }
            foreach ($data as $value) {
                $orgSchoolYearId = isset($dataOrgSchoolYearDb[$value['OrgSchoolYear']]) ? $dataOrgSchoolYearDb[$value['OrgSchoolYear']] : 0;
                $newClass = $this->saveClass($value['Year'], $orgSchoolYearId, $value['Class']);
                $this->getEntityManager()->persist($newClass);
            }
            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear();
        }
    }

    public function saveGrade($schoolYear, $orgSchoolYear) {
        
        $isnew = $this->getEntityManager()
                ->getRepository('Application\Entity\OrgSchoolYear')
                ->getGradeByDisplayName($this->organizationId,trim($orgSchoolYear));
        
        if ($isnew) {
            return array();
        }
        $result = new \Application\Entity\OrgSchoolYear();
        $objectOrg = $this->getEntityManager()->getReference('Application\Entity\Organization', $this->organizationId);
        $objectSchoolYear = $this->getEntityManager()->getRepository('Application\Entity\SchoolYear')->findOneBy(array('name' => $schoolYear));
        $result->setOrganization($objectOrg);
        $result->setSchoolYear($objectSchoolYear);
        $result->setDisplayName(trim($orgSchoolYear));
        $result->setOrdinal(1);
        $result->setIsDelete(0);

        return $result;
    }

    public function saveClass($year, $orgSchoolYearId, $class) {
        /* @var $result \Application\Entity\ClassJ */
        $result = new \Application\Entity\ClassJ();
        $objectOrg = $this->getEntityManager()->getReference('Application\Entity\Organization', $this->organizationId);
        $result->setOrganization($objectOrg);
        $result->setOrgSchoolYear($this->getEntityManager()->getReference('Application\Entity\OrgSchoolYear', $orgSchoolYearId));
        $result->setClassName(trim($class));
        $result->setYear(intval(trim($year)));
        $result->setNumberOfStudent(0);
        $result->setIsDelete(0);

        return $result;
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }

        return $this->em;
    }

    public function validateDataPupilImport($dataImport) {
        $prefix = PupilConst::DELIMITER_VALUE;
        $dataCheckDuplicateInFile = $this->getDataPupilNumberDuplicateInFile($dataImport);
        $dataCheckDuplicateInDb = $this->getDataPupilNumberInDb($this->organizationId);
        $index = 1;
        $isEmptyNameKanna = 0;
        $errors = array();
        foreach ($dataImport as $lineImport) {
            if(empty(trim($lineImport['firstnameKana'])) && empty(trim($lineImport['lastnameKana']))){
                $isEmptyNameKanna = $this->translate('emptyNameKanna');
            }
            $errors1 = $this->validatePupilNumber($lineImport['pupilNumber']);
            $errors2 = $this->validatePupilNameKanji($lineImport['firstnameKanji'], $lineImport['lastnameKanji']);
            $errors3 = $this->validatePupilNameKana($lineImport['firstnameKana'], $lineImport['lastnameKana']);
            $errors4 = $this->validatePupilBirthdayAndGender($lineImport['birthday'], $lineImport['gender']);
            $errors5 = $this->validatePupilEnaviIdAndEikenIdAndPassword($lineImport['einaviId'], $lineImport['eikenId'], $lineImport['eikenPassword']);
            $errors6 = $this->validatePupilEikenLevelAndYearAndKai($lineImport['eikenLevel'], $lineImport['eikenYear'], $lineImport['kai']);
            $errors7 = $this->validatePupilEikenScore($lineImport['eikenScoreReading'], $lineImport['eikenScoreListening'], $lineImport['eikenScoreWriting'], $lineImport['eikenScoreSpeaking']);
            $errors8 = $this->validatePupilIBA($lineImport['ibaLevel'], $lineImport['ibaDate']);
            $errors9 = $this->validatePupilIBAScore($lineImport['ibaScoreReading'], $lineImport['ibaScoreListening']);
            $errors10 = $this->validatePupilWordLevelAndGrammarLevel($lineImport['wordLevel'], $lineImport['grammarLevel']);
            $errors11 = array();
            if (!$errors1) {
                $keyCheckDuplicate = intval($lineImport['year']) . $prefix . $lineImport['orgSchoolYear'] . $prefix . $lineImport['class'] . $prefix . intval($lineImport['pupilNumber']);
                $errors11 = $this->validateDataPupilNumberDuplicate($keyCheckDuplicate, $dataCheckDuplicateInFile, $dataCheckDuplicateInDb);
            }
            $errorsOfLine = array_merge($errors1, $errors2, $errors3, $errors4, $errors5, $errors6, $errors7, $errors8, $errors9, $errors10, $errors11);
            if ($errorsOfLine) {
                $errors[$index] = $errorsOfLine;
            }
            $index++;
        }

        return array($errors,$isEmptyNameKanna);
    }

    public function validatePupilNumber($pupilNumber) {
        $error = array();
        if ($pupilNumber === '') {
            $error[$this->translate('ImportPupilNumber')] = $this->translate('MsgPupilNumberError1');
        } else {
            if (!$this->isNumber($pupilNumber)) {
                $error[$this->translate('ImportPupilNumber')] = $this->translate('MsgPupilNumberError2');
            }
        }

        return $error;
    }

    public function validatePupilNameKanji($firstName, $lastName) {
        $error = array();
        if (empty($firstName)) {
            $error[$this->translate('ImportFirstnameKanji')] = $this->translate('MsgFirstnameKanjiError1');
        }
        if (empty($lastName)) {
            $error[$this->translate('ImportLastnameKanji')] = $this->translate('MsgLastnameKanjiError1');
        }
        if (!empty($firstName) && !empty($lastName)) {
            $nameKanji = $firstName . $lastName;
            if (!$this->isFullSize($nameKanji) || mb_strlen($nameKanji, 'utf-8') > 20) {
                $error[$this->translate('ImportNameKanji')] = $this->translate('MsgNameKanjiError1');
            }else{
                if (!$this->isUseOldKanji($nameKanji)) {
                   $error[$this->translate('ImportNameKanji')] = $this->translate('MsgUseOldKanji');
                }
            }
        }

        return $error;
    }

    public function validatePupilNameKana($firstName, $lastName) {
        $error = array();

        if (!empty($firstName) && !$this->checkKatakana($firstName)) {
            $error[$this->translate('ImportFirstnameKana')] = $this->translate('MsgFirstnameKanaError2');
        }

        if (!empty($lastName) && !$this->checkKatakana($lastName)) {
            $error[$this->translate('ImportLastnameKana')] = $this->translate('MsgLastnameKanaError2');
        }

        return $error;
    }

    public function validatePupilBirthdayAndGender($birthday, $gender) {
        $error = array();

        if (!empty($birthday)) {
            $year = date('Y', strtotime($birthday));
            $intDate = date('Ymd', strtotime($birthday));
            if (!DateHelper::isDateFormatYmd($birthday)) {
                $error[$this->translate('ImportBirthday')] = $this->translate('MsgBirthdayError2');
            } else if ($year < 1916) {
                $error[$this->translate('ImportBirthday')] = $this->translate('MsgBirthdayError3');
            } else if ($intDate > date('Ymd')) {
                $error[$this->translate('ImportBirthday')] = $this->translate('MsgBirthdayError4');
            }
        }
        if (!empty($gender) && !in_array($gender, array('男', '女'))) {
            $error[$this->translate('ImportGender')] = $this->translate('MsgGenderError1');
        }

        return $error;
    }

    public function validatePupilEnaviIdAndEikenIdAndPassword($enaviId, $eikenId, $password) {
        $error = array();
        if ($enaviId !== '') {
            if (!preg_match('/^[0-9]*$/', $enaviId) || mb_strlen($enaviId, 'utf-8') != 10) {
                $error[$this->translate('ImportEinaviId')] = $this->translate('MsgEinaviIdError1');
            }
        }
        if ($eikenId !== '') {
            if (!preg_match('/^[0-9]*$/', $eikenId) || mb_strlen($eikenId, 'utf-8') != 11) {
                $error[$this->translate('ImportEikenId')] = $this->translate('MsgEikenIdError1');
            }
        }
        if ($password !== '') {
            if (!preg_match('/^[a-zA-Z0-9]*$/', $password) || mb_strlen($password, 'utf-8') > 6 || mb_strlen($password, 'utf-8') < 4) {
                $error[$this->translate('ImportEikenPassword')] = $this->translate('MsgEikenPasswordError1');
            }
        }

        return $error;
    }

    public function validatePupilEikenLevelAndYearAndKai($eikenLevel, $eikenYear, $kai) {
        $error = array();
        $arrEikenLevel = $this->getArrayEikenLevel();
        if ($eikenLevel !== '') {
            if (!in_array($eikenLevel, $arrEikenLevel)) {
                $error[$this->translate('ImportEikenLevel')] = $this->translate('MsgEikenLevelError1');
            }
            if ($eikenYear === '') {
                $error[$this->translate('ImportEikenYear')] = $this->translate('MsgEikenYearError1');
            } else {
                if (!$this->isYearFormat($eikenYear)) {
                    $error[$this->translate('ImportEikenYear')] = $this->translate('MsgEikenYearError2');
                }
            }
            if ($kai === '') {
                $error[$this->translate('ImportKai')] = $this->translate('MsgEikenKaiError1');
            } else {
                if ($kai > 3 || $kai < 1) {
                    $error[$this->translate('ImportKai')] = $this->translate('MsgEikenKaiError2');
                }
            }
        }

        return $error;
    }

    public function validatePupilEikenScore($reading, $listening, $writing, $speaking) {
        $error = array();
        if ($reading !== '') {
            if (!$this->isNumber($reading)) {
                $error[$this->translate('ImportEikenScoreReading')] = $this->translate('MsgEikenScoreReadingError1');
            } else if (!$this->isNumberScore($reading)) {
                $error[$this->translate('ImportEikenScoreReading')] = $this->translate('MsgEikenScoreReadingError2');
            }
        }
        if ($listening !== '') {
            if (!$this->isNumber($listening)) {
                $error[$this->translate('ImportEikenScoreListening')] = $this->translate('MsgEikenScoreListeningError1');
            } else if (!$this->isNumberScore($listening)) {
                $error[$this->translate('ImportEikenScoreListening')] = $this->translate('MsgEikenScoreListeningError2');
            }
        }
        if ($writing !== '') {
            if (!$this->isNumber($writing)) {
                $error[$this->translate('ImportEikenScoreWriting')] = $this->translate('MsgEikenScoreWritingError1');
            } else if (!$this->isNumberScore($writing)) {
                $error[$this->translate('ImportEikenScoreWriting')] = $this->translate('MsgEikenScoreWritingError2');
            }
        }
        if ($speaking !== '') {
            if (!$this->isNumber($speaking)) {
                $error[$this->translate('ImportEikenScoreSpeaking')] = $this->translate('MsgEikenScoreSpeakingError1');
            } else if (!$this->isNumberScore($speaking)) {
                $error[$this->translate('ImportEikenScoreSpeaking')] = $this->translate('MsgEikenScoreSpeakingError2');
            }
        }

        return $error;
    }

    public function validatePupilIBA($ibaLevel, $ibaDate) {
        $error = array();
        $arrEikenLevel = $this->getArrayEikenLevel();
        if ($ibaLevel !== '') {
            if (!in_array($ibaLevel, $arrEikenLevel)) {
                $error[$this->translate('ImportIbaLevel')] = $this->translate('MsgIBALevelError1');
            }
            if ($ibaDate === '') {
                $error[$this->translate('ImportIbaDate')] = $this->translate('MsgIBADateError1');
            } else {
                if (!DateHelper::isDateFormatYmd($ibaDate)) {
                    $error[$this->translate('ImportIbaDate')] = $this->translate('MsgIBADateError2');
                }
            }
        }

        return $error;
    }

    public function validatePupilIBAScore($reading, $listening) {
        $error = array();
        if ($reading !== '') {
            if (!$this->isNumber($reading)) {
                $error[$this->translate('ImportIbaScoreReading')] = $this->translate('MsgIBAScoreReadingError1');
            } else if (!$this->isNumberScore($reading)) {
                $error[$this->translate('ImportIbaScoreReading')] = $this->translate('MsgIBAScoreReadingError2');
            }
        }
        if ($listening !== '') {
            if (!$this->isNumber($listening)) {
                $error[$this->translate('ImportIbaScoreListening')] = $this->translate('MsgIBAScoreListeningError1');
            } else if (!$this->isNumberScore($listening)) {
                $error[$this->translate('ImportIbaScoreListening')] = $this->translate('MsgIBAScoreListeningError2');
            }
        }

        return $error;
    }

    public function validatePupilWordLevelAndGrammarLevel($wordLevel, $grammarLevel) {
        $error = array();
        $arrEikenLevel = $this->getArrayEikenLevel();
        if ($wordLevel !== '') {
            if (!in_array($wordLevel, $arrEikenLevel)) {
                $error[$this->translate('ImportWordLevel')] = $this->translate('MsgWordLevelError1');
            }
        }
        if ($grammarLevel !== '') {
            if (!in_array($grammarLevel, $arrEikenLevel)) {
                $error[$this->translate('ImportGrammarLevel')] = $this->translate('MsgGrammarLevelError1');
            }
        }

        return $error;
    }

    public function validateDataPupilNumberDuplicate($keyCheckDuplicate, $dataImport = false, $dataPupilDb = false) {
        $error = array();
        if ($dataImport) {
            if (array_key_exists($keyCheckDuplicate, $dataImport)) {
                $error[$this->translate('ImportDuplicatePupilNumber')] = $this->translate('MsgDuplicatePupilError1');
            }
        }
        if ($dataPupilDb) {
            if (array_key_exists($keyCheckDuplicate, $dataPupilDb)) {
                $error[$this->translate('ImportDuplicatePupilNumber')] = $this->translate('MsgDuplicatePupilError1');
            }
        }
        return $error;
    }

    public function getDataPupilNumberInDb($orgId) {
        $prefix = PupilConst::DELIMITER_VALUE;
        $pupilsOfOrg = $this->getEntityManager()
                ->getRepository('Application\Entity\Pupil')
                ->getListPupilOfClassByOrg($orgId);
        $dataPupil = array();
        if ($pupilsOfOrg) {
            foreach ($pupilsOfOrg as $value) {
                $year = intval($value['year']);
                $schoolyearName = $value['schoolyearName'];
                $className = $value['className'];
                $number = intval($value['number']);
                $key = $year . $prefix . $schoolyearName . $prefix . $className . $prefix . $number;
                $dataPupil[$key] = $number;
            }
        }
        return $dataPupil;
    }

    public function getDataPupilNumberDuplicateInFile($dataImport) {
        $prefix = PupilConst::DELIMITER_VALUE;
        $draffArray = array();
        $duplicateArray = array();
        foreach ($dataImport as $value) {
            if ($value['pupilNumber'] !== '') {
                $year = intval($value['year']);
                $schoolyearName = $value['orgSchoolYear'];
                $className = $value['class'];
                $number = intval($value['pupilNumber']);

                $key = $year . $prefix . $schoolyearName . $prefix . $className . $prefix . $number;
                if (isset($draffArray[$key])) {
                    $duplicateArray[$key] = $number;
                }
                $draffArray[$key] = $number;
            }
        }
        return $duplicateArray;
    }

    public function isNumber($value) {
        return preg_match('/^[0-9]*$/', $value);
    }

    public function isNumberScore($value) {
        return (intval($value) >= 0 && intval($value) <= 999);
    }

    public static function isFullSize($fullSize) {
        if (preg_match("/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])|[\x20-\x7E]/", $fullSize)) {
            return false;
        }

        return true;
    }
    function isUseOldKanji($namekanj) {
        if(strlen(utf8_decode($namekanj)) * 3 < strlen($namekanj)){
            return false;
        }
        return true;
    }
    
    public function checkKatakana($value)
    {
        return preg_match('/^[\x{30A0}-\x{30FF}\x{FF5F}-\x{FF9F}\s]*$/u', $value);
    }

    public function getArrayEikenLevel() {
        if ($this->eikenLevels) {
            return $this->eikenLevels;
        }
        $arrEikenLevel = array();
        $em = $this->getEntityManager();
        $eikenLevels = $em->getRepository('Application\Entity\EikenLevel')->ListEikenLevel();
        foreach ($eikenLevels as $eikenLevel) {
            $arrEikenLevel[$eikenLevel['id']] = $eikenLevel['levelName'];
        }
        $this->eikenLevels = $arrEikenLevel;
        return $arrEikenLevel;
    }

    public function isMatchString($content, $stringCompare) {
        $content = str_replace('||-||', 'prefix', $content);
        $content = str_replace('/', '', $content);

        $stringCompare = str_replace('||-||', 'prefix', $stringCompare);
        $stringCompare = str_replace('/', '', $stringCompare);

        $pattern = '/^' . $stringCompare . '/';

        if (preg_match($pattern, $content)) {
            return true;
        }
        return false;
    }
    public function getNewListKeyAfterCheck($listKeyDuplicate) {
        $prefix = '||-||';
        if(!$listKeyDuplicate){
            return array();
        }
        foreach ($listKeyDuplicate as $key => $value) {
            $arrayToCheck = $listKeyDuplicate;
            $dataKey = explode($prefix, $key);
            $keyEmptyNameAndBirthday = $dataKey[0] . $prefix . $dataKey[1] . $prefix . '' . $prefix . '';
            if (!empty($dataKey[2]) && empty($dataKey[3])) {
                unset($arrayToCheck[$key]);
                $stringToCheck = $dataKey[0] . '\|\|-\|\|' . $dataKey[1] . '\|\|-\|\|' . '' . '\|\|-\|\|' . '(.*)';
                if (preg_grep('/^' . $stringToCheck . '$/i', $arrayToCheck)) {
                    $listKeyDuplicate[$key] = $keyEmptyNameAndBirthday;
                }
            } else if (!empty($dataKey[3]) && empty($dataKey[2])) {
                unset($arrayToCheck[$key]);
                $stringToCheck = $dataKey[0] . '\|\|-\|\|' . $dataKey[1] . '\|\|-\|\|' . '(.*)' . '\|\|-\|\|' . '';
                if (preg_grep('/^' . $stringToCheck . '$/i', $arrayToCheck)) {
                    $listKeyDuplicate[$key] = $keyEmptyNameAndBirthday;
                }
            } else if (!empty($dataKey[3]) && !empty($dataKey[2])) {
                $keyEmtyName = $dataKey[0] . $prefix . $dataKey[1] . $prefix . '' . $prefix . $dataKey[3];
                $keyEmtyBirthday = $dataKey[0] . $prefix . $dataKey[1] . $prefix . $dataKey[2] . $prefix . '';
                if ((in_array($keyEmtyBirthday, $arrayToCheck) && in_array($keyEmtyName, $arrayToCheck)) || in_array($keyEmptyNameAndBirthday, $arrayToCheck)) {
                    $listKeyDuplicate[$key] = $keyEmptyNameAndBirthday;
                }
            }
        }
        return $listKeyDuplicate;
    }
    public function getListKeyCheckDuplicateInFile($dataImport) {
        $prefix = '||-||';
        $listKeyDuplicate = array();
        foreach ($dataImport as $key => $value) {
            $nameKanji = trim($value['firstnameKanji']) . trim($value['lastnameKanji']);
            $nameKana = trim($value['firstnameKana']) . trim($value['lastnameKana']);
            $nameKana = $this->convertKanaHalfWidthToFullWidth($nameKana);
            $birthday = $value['birthday'];
            $year = intval($value['year']);

            $currentKey = $year . $prefix . $nameKanji . $prefix . $nameKana . $prefix . $birthday;

            $keyCheckDuplicate1 = $year . $prefix . $nameKanji . $prefix . $nameKana . $prefix . $birthday;
            $keyCheckDuplicate2 = $year . $prefix . $nameKanji . $prefix . $nameKana . $prefix . '';
            $keyCheckDuplicate3 = $year . $prefix . $nameKanji . $prefix . '' . $prefix . $birthday;
            $keyCheckDuplicate4 = $year . $prefix . $nameKanji . $prefix . '' . $prefix . '';
            if (empty($nameKana) && empty($birthday)) {
                foreach ($listKeyDuplicate as $key => $value) {
                    $stringCompare = $year . $prefix . $nameKanji . $prefix;
                    if ($this->isMatchString($key, $stringCompare)) {
                        $listKeyDuplicate[$key] = $keyCheckDuplicate4;
                    }
                }
                $listKeyDuplicate[$currentKey] = $keyCheckDuplicate4;
            } else if (!empty($nameKana) && empty($birthday)) {
                if (array_key_exists($keyCheckDuplicate4, $listKeyDuplicate)) {
                    $listKeyDuplicate[$currentKey] = $keyCheckDuplicate4;
                } else {
                    foreach ($listKeyDuplicate as $key => $value) {
                        $stringCompare = $year . $prefix . $nameKanji . $prefix . $nameKana . $prefix;
                        if ($this->isMatchString($key, $stringCompare)) {
                            $listKeyDuplicate[$key] = $keyCheckDuplicate2;
                        }
                    }
                    $listKeyDuplicate[$currentKey] = $keyCheckDuplicate2;
                }
            } else if (empty($nameKana) && !empty($birthday)) {
                if (array_key_exists($keyCheckDuplicate4, $listKeyDuplicate)) {
                    $listKeyDuplicate[$currentKey] = $keyCheckDuplicate4;
                } else {
                    foreach ($listKeyDuplicate as $key => $value) {
                        $stringCompare = $year . $prefix . $nameKanji . $prefix . '(.*)' . $prefix . $birthday;
                        if ($this->isMatchString($key, $stringCompare)) {
                            $listKeyDuplicate[$key] = $keyCheckDuplicate3;
                        }
                    }
                    $listKeyDuplicate[$keyCheckDuplicate3] = $keyCheckDuplicate3;
                }
            } else {
                if (array_key_exists($keyCheckDuplicate4, $listKeyDuplicate)) {
                    $listKeyDuplicate[$currentKey] = $keyCheckDuplicate4;
                } else if (array_key_exists($keyCheckDuplicate2, $listKeyDuplicate)) {
                    $listKeyDuplicate[$currentKey] = $keyCheckDuplicate2;
                } else if (array_key_exists($keyCheckDuplicate3, $listKeyDuplicate)) {
                    $listKeyDuplicate[$currentKey] = $keyCheckDuplicate3;
                } else {
                    $listKeyDuplicate[$currentKey] = $keyCheckDuplicate1;
                }
            }
        }
        $listKeyDuplicate = $this->getNewListKeyAfterCheck($listKeyDuplicate);
        return $listKeyDuplicate;
    }

    public function getDataDuplicatePupilName($orgId, $dataImport) {
        $listKeyDuplicate = $this->getListKeyCheckDuplicateInFile($dataImport);
        $prefix = PupilConst::DELIMITER_VALUE;
        $draffArray = array();
        $dataDuplicate = array();
        $dataDetailInFile = array();
        $dataDetailInOrg = array();
        foreach ($dataImport as $key => $value) {
            $nameKanji = trim($value['firstnameKanji']) . trim($value['lastnameKanji']);
            $nameKana = trim($value['firstnameKana']) . trim($value['lastnameKana']);
            $year = intval($value['year']);
            $pupilDetails = array(
                'year' => $year,
                'orgSchoolYearName' => trim($value['orgSchoolYear']),
                'className' => trim($value['class']),
                'pupilNumber' => intval($value['pupilNumber']),
                'nameKanji' => $nameKanji,
                'nameKana' => $nameKana,
                'birthday' => $value['birthday'],
                'gender' => $value['gender']
            );
            $nameKanaToCompare = $this->convertKanaHalfWidthToFullWidth($nameKana);
            $keyCheckDuplicate = $year . $prefix . $nameKanji . $prefix . $nameKanaToCompare . $prefix . $value['birthday'];
            if(array_key_exists($keyCheckDuplicate, $listKeyDuplicate)){
                $keyCheckDuplicate = $listKeyDuplicate[$keyCheckDuplicate];
            }
            if (isset($draffArray[$keyCheckDuplicate])) {
                $dataDuplicate[$keyCheckDuplicate]['value'] = $keyCheckDuplicate;
                $dataDuplicate[$keyCheckDuplicate]['status'] = PupilConst::DUPLICATE_IN_FILE_IMPORT;

                if (empty($dataDetailInFile[$keyCheckDuplicate])) {
                    $dataDetailInFile[$keyCheckDuplicate][] = $draffArray[$keyCheckDuplicate];
                }
                $dataDetailInFile[$keyCheckDuplicate][] = $pupilDetails;
            }
            $draffArray[$keyCheckDuplicate] = $pupilDetails;
        }
        list($dataCheckInOrg, $dataDetailDb) = $this->getListPupilToCheckDuplicateNameInOrg($orgId);
        foreach ($draffArray as $key => $value) {
            if (array_key_exists($key, $dataCheckInOrg)) {
                $status = isset($dataDuplicate[$key]['status']) ? PupilConst::DUPLICATE_IN_FILE_IMPORT_AND_DATABASE : PupilConst::DUPLICATE_IN_DATABASE;
                $dataDuplicate[$key]['value'] = $key;
                $dataDuplicate[$key]['status'] = $status;
                $dataDetailInOrg[$key] = $dataDetailDb[$key];
                if ($status == PupilConst::DUPLICATE_IN_DATABASE) {
                    $dataDetailInFile[$key][] = $draffArray[$key];
                }
            }
        }
        return array($dataDuplicate, $dataDetailInFile, $dataDetailInOrg);
    }

    public function setEntityPupil($entityPupil = Null) {
        $this->entityPupil = $entityPupil ? $entityPupil : $this->getEntityManager()->getRepository('Application\Entity\Pupil');
    }

    public function getListPupilToCheckDuplicateNameInOrg($orgId) {
        $prefix = PupilConst::DELIMITER_VALUE;
        $dataPupilCheck = array();
        $dataPupilDetail = array();

        if (!$this->entityPupil) {
            $this->setEntityPupil();
        }
        $pupil = $this->entityPupil->getListPupilOfClassByOrg($orgId);
        if ($pupil) {
            foreach ($pupil as $value) {
                $pupilId = intval($value['id']);
                $year = intval($value['year']);
                $orgSchoolYearName = trim($value['schoolyearName']);
                $className = trim($value['className']);
                $pupilNumber = intval($value['number']);
                $nameKanji = trim($value['firstNameKanji']) . trim($value['lastNameKanji']);
                $nameKana = trim($value['firstNameKana']) . trim($value['lastNameKana']);
                $birthday = $value['birthday'] != Null ? $value['birthday']->format('Y/m/d') : '';
                $gender = $value['gender'] != -1 ? ($value['gender'] == 1 ? '男' : '女') : '';
                $pupilDetails = array(
                    'year' => $year,
                    'orgSchoolYearName' => $orgSchoolYearName,
                    'className' => $className,
                    'pupilNumber' => $pupilNumber,
                    'nameKanji' => $nameKanji,
                    'nameKana' => $nameKana,
                    'birthday' => $birthday,
                    'gender' => $gender
                );
                $nameKanaToCompare = $this->convertKanaHalfWidthToFullWidth($nameKana);
                $keyCheckDuplicate1 = $year . $prefix . $nameKanji . $prefix . $nameKanaToCompare . $prefix . $birthday;
                $dataPupilCheck[$keyCheckDuplicate1] = $keyCheckDuplicate1;
                $dataPupilDetail[$keyCheckDuplicate1][$pupilId] = $pupilDetails;

                $keyCheckDuplicate2 = $year . $prefix . $nameKanji . $prefix . $nameKanaToCompare . $prefix . '';
                $dataPupilCheck[$keyCheckDuplicate2] = $keyCheckDuplicate2;
                $dataPupilDetail[$keyCheckDuplicate2][$pupilId] = $pupilDetails;

                $keyCheckDuplicate3 = $year . $prefix . $nameKanji . $prefix . '' . $prefix . $birthday;
                $dataPupilCheck[$keyCheckDuplicate3] = $keyCheckDuplicate3;
                $dataPupilDetail[$keyCheckDuplicate3][$pupilId] = $pupilDetails;

                $keyCheckDuplicate4 = $year . $prefix . $nameKanji . $prefix . '' . $prefix . '';
                $dataPupilCheck[$keyCheckDuplicate4] = $keyCheckDuplicate4;
                $dataPupilDetail[$keyCheckDuplicate4][$pupilId] = $pupilDetails;
            }
        }
        return array($dataPupilCheck, $dataPupilDetail);
    }

    public function getHtmlOutPutOfTemplate($template, $params) {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true)
                ->setTemplate($template)
                ->setVariables($params);
        $htmlOutput = $this->getServiceLocator()->get('viewrenderer')->render($viewModel);
        return $htmlOutput;
    }

    /*
     * @author: MinhTN6
     * get html out put after check validate data import
     * @param array $dataImportFromFile (dataImport form file csv)
     * @param array $masterData (errors of master data, new master data)
     * @return string $htmlOutput
     */

    public function getResultAfterCheckDataImport($dataImportFromFile, $isCheckMapping = true, $isCheckCreateMasterData = true) {

        if ($isCheckMapping) {
            if (!$this->schoolYearRepo) {
                $this->setSchoolYearRepository();
            }
            list($errorsMasterData, $orgSchoolYearsNew, $schoolYearIdsUsed, $dataImport) = $this->validateCommonMasterData($dataImportFromFile);
            if (!$errorsMasterData && $orgSchoolYearsNew) {
                $listSchoolYearNotUse = $this->schoolYearRepo->getListSchoolYearNotUsed($schoolYearIdsUsed);
                $paramsTemplate = array(
                    'dataImport' => $dataImport,
                    'orgSchoolYearsNew' => $orgSchoolYearsNew,
                    'listSchoolYearNotUse' => $listSchoolYearNotUse,
                    'mappingData' => array(),
                );
                $template = '/pupil-mnt/import-pupil/mapping-schoolyear.phtml';
                $htmlOutput = $this->getHtmlOutPutOfTemplate($template, $paramsTemplate);
                return $htmlOutput;
            }
        } else {
            $dataImport = $dataImportFromFile;
        }
        if ($isCheckCreateMasterData && empty($errorsMasterData)) {
            $masterData = $this->getListMasterData($dataImport);
            if ($masterData) {
                $paramsTemplate = array(
                    'data' => $masterData,
                    'dataImport' => $dataImport
                );
                $template = '/pupil-mnt/import-pupil/create-master-data.phtml';
                $htmlOutput = $this->getHtmlOutPutOfTemplate($template, $paramsTemplate);
                return $htmlOutput;
            }
        }
        list($dataShow, $maxPage) = $this->getDataPagingOfDataImport($dataImport);
        $paramsTemplate = array(
            'dataImport' => $dataImport,
            'dataShow' => $dataShow,
            'maxPage' => $maxPage,
        );
        if (!empty($errorsMasterData)) {
            $paramsTemplate['errors'] = $errorsMasterData;
        } else {
            list($errorsDataPupil,$isEmptyNameKanna) = $this->validateDataPupilImport($dataImport);
            $paramsTemplate['errors'] = $errorsDataPupil;
            $paramsTemplate['isEmptyNameKanna'] = $isEmptyNameKanna;
        }

        $template = '/pupil-mnt/import-pupil/list.phtml';
        $htmlOutput = $this->getHtmlOutPutOfTemplate($template, $paramsTemplate);
        return $htmlOutput;
    }

    public function validateMappingSchoolYear($dataImport, $orgSchoolYearNames, $schoolYearNames) {
        $translator = $this->serviceLocator->get('MVCTranslator');
        list($listOrgSchoolYear, $schoolYearIdsUsed) = $this->getListOrgSchoolYearOfOrg();
        $listSchoolYearNotUse = $this->getEntityManager()->getRepository('Application\Entity\SchoolYear')->getListSchoolYearNotUsed($schoolYearIdsUsed);
        $listSchoolYearNameIsMapped = array();
        foreach ($listOrgSchoolYear as $value) {
            $listSchoolYearNameIsMapped[$value['schoolYearName']] = $value['orgSchoolYearName'];
        }
        foreach ($dataImport as $keyImport => $row) {
            $orgSchoolYear = trim($row['orgSchoolYear']);
            if (!array_key_exists($orgSchoolYear, $listOrgSchoolYear)) {
                $orgSchoolYearsNew[$orgSchoolYear] = $orgSchoolYear;
                if (!in_array($orgSchoolYear, $orgSchoolYearNames)) {
                    $orgSchoolYearNames[] = $orgSchoolYear;
                }
            } else {
                $dataImport[$keyImport]['schoolYear'] = $listOrgSchoolYear[$orgSchoolYear]['schoolYearName'];
            }
        }
        $gradeExits = array_map('strval', array_keys($listOrgSchoolYear));
        $mappingData = array();
        if (isset($orgSchoolYearNames)) {
            foreach ($orgSchoolYearNames as $key => $orgSchoolYearName) {
                if (empty($schoolYearNames[$key]) && array_key_exists($orgSchoolYearName, $orgSchoolYearsNew)) {
                    $errors[$orgSchoolYearName] = $translator->translate('schoolYearIsRequired');
                } else {
                    if (in_array($schoolYearNames[$key], $mappingData)) {
                        $errors[$orgSchoolYearName] = $translator->translate('twoGradeForOneSchoolYear');
                    } else if (in_array($orgSchoolYearName, $gradeExits)) {
                        $errors[$orgSchoolYearName] = sprintf($translator->translate('MsgOrgSchoolYearIsUsed'), $orgSchoolYearName);
                        unset($orgSchoolYearsNew[$orgSchoolYearName]);
                        $mappingData[$orgSchoolYearName] = $listOrgSchoolYear[$orgSchoolYearName]['schoolYearName'];
                    } else if (array_key_exists($schoolYearNames[$key], $listSchoolYearNameIsMapped)) {
                        $errors[$orgSchoolYearName] = sprintf($translator->translate('MsgSchoolYearIsUsed'), $schoolYearNames[$key]);
                    } else {
                        $mappingData[$orgSchoolYearName] = $schoolYearNames[$key];
                    }
                }
            }
        }

        return array(
            !empty($errors) ? $errors : array(),
            $mappingData,
            !empty($orgSchoolYearsNew) ? $orgSchoolYearsNew : array(),
            $listSchoolYearNotUse,
            $dataImport,
        );
    }

    public function getDataPagingOfDataImport($dataImport, $currentPage = 1) {
        if (intval($currentPage) < 1)
            $currentPage = 1;
        $rowPerPage = 20;
        $maxPage = ceil(count($dataImport) / $rowPerPage);
        $dataShow = array();
        foreach ($dataImport as $key => $value) {
            $begin = $rowPerPage * ($currentPage - 1);
            $end = $rowPerPage * $currentPage;
            if ($key >= $begin && $key < $end) {
                $dataShow[$key] = $value;
            }
        }
        return array($dataShow, $maxPage);
    }
    
    public function getCharacterKanaHalfwidth(){
        $listNumber = array('', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $listCharacterOne = array(
            'ｳﾞ', 'ｶﾞ', 'ｷﾞ', 'ｸﾞ',
            'ｹﾞ', 'ｺﾞ', 'ｻﾞ', 'ｼﾞ',
            'ｽﾞ', 'ｾﾞ', 'ｿﾞ', 'ﾀﾞ',
            'ﾁﾞ', 'ﾂﾞ', 'ﾃﾞ', 'ﾄﾞ',
            'ﾊﾞ', 'ﾋﾞ', 'ﾌﾞ', 'ﾍﾞ',
            'ﾎﾞ', 'ﾊﾟ', 'ﾋﾟ', 'ﾌﾟ', 'ﾍﾟ', 'ﾎﾟ');
        
        $listCharacterTwo = array(
            'ｱ', 'ｲ', 'ｳ', 'ｴ', 'ｵ',
            'ｶ', 'ｷ', 'ｸ', 'ｹ', 'ｺ',
            'ｻ', 'ｼ', 'ｽ', 'ｾ', 'ｿ',
            'ﾀ', 'ﾁ', 'ﾂ', 'ﾃ', 'ﾄ',
            'ﾅ', 'ﾆ', 'ﾇ', 'ﾈ', 'ﾉ',
            'ﾊ', 'ﾋ', 'ﾌ', 'ﾍ', 'ﾎ',
            'ﾏ', 'ﾐ', 'ﾑ', 'ﾒ', 'ﾓ',
            'ﾔ', 'ﾕ', 'ﾖ', 'ﾗ', 'ﾘ',
            'ﾙ', 'ﾚ', 'ﾛ', 'ﾜ', 'ｦ',
            'ﾝ', 'ｧ', 'ｨ', 'ｩ', 'ｪ',
            'ｫ', 'ヵ', 'ヶ', 'ｬ', 'ｭ',
            'ｮ', 'ｯ', '､', '｡', 'ｰ',
            '｢', '｣', 'ﾞ', 'ﾟ',
            ',', '.', '-','"',
            '−','―','－','-','‐');
        
        return array($listNumber, $listCharacterOne, $listCharacterTwo);
    }
    
    public function getCharacterKanaFullwidth(){
        $listNumber = array('　', '０', '１', '２', '３', '４', '５', '６', '７', '８', '９');
        $listCharacterOne = array(
            'ヴ', 'ガ', 'ギ', 'グ',
            'ゲ', 'ゴ', 'ザ', 'ジ',
            'ズ', 'ゼ', 'ゾ', 'ダ',
            'ヂ', 'ヅ', 'デ', 'ド',
            'バ', 'ビ', 'ブ', 'ベ',
            'ボ', 'パ', 'ピ', 'プ', 'ペ', 'ポ');
        
        $listCharacterTwo = array(
            'ア', 'イ', 'ウ', 'エ', 'オ',
            'カ', 'キ', 'ク', 'ケ', 'コ',
            'サ', 'シ', 'ス', 'セ', 'ソ',
            'タ', 'チ', 'ツ', 'テ', 'ト',
            'ナ', 'ニ', 'ヌ', 'ネ', 'ノ',
            'ハ', 'ヒ', 'フ', 'ヘ', 'ホ',
            'マ', 'ミ', 'ム', 'メ', 'モ',
            'ヤ', 'ユ', 'ヨ', 'ラ', 'リ',
            'ル', 'レ', 'ロ', 'ワ', 'ヲ',
            'ン', 'ァ', 'ィ', 'ゥ', 'ェ',
            'ォ', 'ヶ', 'ヶ', 'ャ', 'ュ',
            'ョ', 'ッ', '、', '。', 'ー',
            '「', '」', '”', '',
            '、', '。', 'ー','”',
            'ー','ー','ー','ー','ー');
        
        return array($listNumber, $listCharacterOne, $listCharacterTwo);
    }
    
    public function convertKanaHalfWidthToFullWidth($str){
        if($str === ''){
            return $str;
        }
        list($numberHw, $characterOneHw, $characterTwoHw) = $this->getCharacterKanaHalfwidth();
        list($numberFw, $characterOneFw, $characterTwoFw) = $this->getCharacterKanaFullwidth();
        $str = str_replace($numberHw, $numberFw, trim($str));
        $result = str_replace($characterOneHw, $characterOneFw, $str);

        return str_replace($characterTwoHw, $characterTwoFw, $result);
    }

    function convertKanaFullWidthToHalfWidth($str)
    {
        if($str === ''){
            return $str;
        }
        list($numberHw, $characterOneHw, $characterTwoHw) = $this->getCharacterKanaHalfwidth();
        list($numberFw, $characterOneFw, $characterTwoFw) = $this->getCharacterKanaFullwidth();
        $str = str_replace($numberFw, $numberHw, trim($str));
        $result = str_replace($characterOneFw, $characterOneHw, $str);

        return str_replace($characterTwoFw, $characterTwoHw, $result);
    }

    public function getlistGrade() {
        $em = $this->getEntityManager();
        if (!$this->schoolYearRepo) {
            $this->setSchoolYearRepository();
        }
        $data = $this->schoolYearRepo->getListUniversalGrade();
        $result = array();
        if ($data) {
            foreach ($data as $row) {
                if (!array_key_exists($row['name'], $result)) {
                    $result[$row['name']] = '';
                }
                if ($row['organizationId'] == $this->organizationId) {
                    $result[$row['name']] = $row['displayName'];
                }
            }
        }

        return $result;
    }

    public function setListGrade($data) {
        $this->listGradeMapping = $data;
    }

    public function isValidMappingGrade($masterData) {
        /*
         * key array $universalGrade is : schoolYearName , and value is : orgSchoolYearName
         */
        $universalGrade = $this->getlistGrade();
        if (!empty($this->listGradeMapping)) {
            $universalGrade = $this->listGradeMapping;
        }
        $gradeWithUni = array_flip($universalGrade);
        $flag = 1;
        if (!empty($masterData) && !empty($universalGrade)) {
            foreach ($masterData as $row) {
                if ((array_key_exists($row['SchoolYear'], $universalGrade) && $universalGrade[$row['SchoolYear']] != $row['OrgSchoolYear'] && $universalGrade[$row['SchoolYear']] != '') || (array_key_exists($row['OrgSchoolYear'], $gradeWithUni) && $gradeWithUni[$row['OrgSchoolYear']] != $row['SchoolYear'])) {
                    $flag = 0;
                }
            }
        }
        return $flag;
    }
    
    public function exportTemplateToCsv($response)
    {
        $arrFieldJapan = $this->getDataHeaderSeperateImport();
        $header = $arrFieldJapan;
        $csv = CsvHelper::arrayToStrCsv(array($arrFieldJapan));
        $csv = trim(mb_convert_encoding($csv, 'SJIS', 'UTF-8'));
        $filenames = $this->translate('fileName');
        $filenames = CharsetConverter::utf8ToShiftJis($filenames);
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'application/csv, charset=Shift_JIS');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"$filenames.csv\"");
        $headers->addHeaderLine('Accept-Ranges', 'bytes');
        $headers->addHeaderLine('Content-Length', strlen($csv));
        $headers->addHeaderLine('Content-Transfer-Encoding: Shift_JIS');
        $response->setHeaders($headers);
        $response->setContent($csv);
        return $response;
    }
    
    public function exportTemplateToExcel2003($response)
    {
        $data = array();
        $objFileName = new CharsetConverter();
        $filenames = $this->translate('fileName') . '.xls';
        $filenames = $objFileName->utf8ToShiftJis($filenames);
        
        array_unshift($data, $this->getDataHeaderSeperateImport());

        $phpExcel = new PHPExcel();
        $phpExcel->export($data, $filenames, 'default', 1, '', 'xls');
        return $response;
    }
    
     //$name is array of fullname
    function seperateFullname($name){
        $surname = new SurnameConst();
        $surnameList = $surname->getSurnameList();
        unset($name[0]);
        $newDataArray = array();
        foreach ($name as $key=>$value){
            $i=3;
            while ($i>=0){
                $compareName = substr($value[4], 0, $i*3);
                if ($i==0) {
                    $firstname = $value[4];
                    $lastname = '';
                }
                else if (!empty(array_keys($surnameList, $compareName)) && $i<strlen($value[4])/3) 
                {                    
                    $firstname = $compareName;
                    $lastname = substr($value[4], $i*3);
                    break;
                }
                $i--;
            }
            array_splice($value, 4, 1, array($firstname, $lastname));
            array_push($newDataArray, $value); 
        }
            
        return $newDataArray;
    }
    
    public function isNotShowMSGGradeClass($data) {
        $result = true;
        $resultGrade = true;
        $resultClass = true;
        /*@var $dantaiService \Application\Service\DantaiService */
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        $arrGrade = array();
        $arrClass = array();
        $years = array();
        $gradeObj = $this->getEntityManager()->getRepository('Application\Entity\OrgSchoolYear')->findBy(array(
                    'organizationId' => $this->organizationId,
                    'isDelete' => 0
                    ));
        $arrGradeDB = array();
        if($gradeObj){
            /*@var $row \Application\Entity\OrgSchoolYear*/
            foreach ($gradeObj as $row){
                array_push($arrGradeDB, $row->getDisplayName()); 
            }
        }
        
        $arrClassDB = $this->getAllClassOfOrgByYear($years);
        
        if($data){
            foreach ($data as $row){
                if($row['OrgSchoolYear']){
                    if(!in_array($row['OrgSchoolYear'], $arrGradeDB) 
                        && !in_array($row['OrgSchoolYear'], $arrGrade)){
                        array_push($arrGrade, $row['OrgSchoolYear']);
                    }
                }
                if($row['Class']){
                    if (!in_array($row['Year'] . $row['OrgSchoolYear'] . ApplicationConst::DELIMITER_VALUE . $row['Class'], $arrClassDB) 
                        && !in_array($row['Year'] . $row['OrgSchoolYear'] . ApplicationConst::DELIMITER_VALUE . $row['Class'], $arrClass)) {
                        array_push($arrClass, $row['Year'] . $row['OrgSchoolYear'] . ApplicationConst::DELIMITER_VALUE . $row['Class']);
                    }
                    if(!in_array($row['Year'], $years)){
                        array_push($years, $row['Year']);
                    }
                }
            }
        }
        
        $flagUpload = false;
        if(empty($arrGrade) && !empty($arrClass)){
            $flagUpload = true;
        }
        $resultGrade = $dantaiService->isAlphanumericCharacter($arrGradeDB, \Application\ApplicationConst::GRADE_TYPE,$arrGrade,$flagUpload);
        $resultClass = $dantaiService->isAlphanumericCharacter($arrClassDB, \Application\ApplicationConst::CLASS_TYPE ,$arrClass);

        if($resultGrade === false ||$resultClass === false){
            $result = false;
        }
        
        return $result;
    }
    
    public function getAllClassOfOrgByYear($years)
    {
        
       $arrResult = array();
        $dataClass = $this->getEntityManager()->getRepository('Application\Entity\ClassJ')->listClassByYear($this->organizationId,$years);
        if($dataClass){
            foreach ($dataClass as $row){
                 array_push($arrResult, $row['year'].$row['orgSchoolYearName'].  \Application\ApplicationConst::DELIMITER_VALUE.$row['className']);
            }
        }
        return $arrResult;
    }
    
    public function convertCharHalfWidthToFullWidth($str){
        if($str === ''){
            return $str;
        }
        list($alphaHalf, $listSpecialHalf) = $this->getCharHafSize();
        list($alphaFull, $listSpecialFull) = $this->getCharFullSize();
        $str = str_replace($alphaHalf, $alphaFull, trim($str));

        return str_replace($listSpecialHalf, $listSpecialFull, $str);
    }
    public function convertCharFullWidthToHalfWidth($str){
        if($str === ''){
            return $str;
        }
        list($alphaHalf, $listSpecialHalf) = $this->getCharHafSize();
        list($alphaFull, $listSpecialFull) = $this->getCharFullSize();
        $str = str_replace($alphaFull, $alphaHalf, trim($str));

        return str_replace($listSpecialFull, $listSpecialHalf, $str);
    }
    public function getCharHafSize() {
        $alphaHalf = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "k", "q", "r", "s", "t", "v", "w", "s", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "K", "Q", "R", "S", "T", "V", "W", "S", "X", "Y", "Z");
        $listSpecialHalf = array('`','~','!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '=', '+', '[', '{', ']', '}', ';', ':', "'", '"', '\\', '|', ',', '<', '.', '>', '/', '?');
        return array($alphaHalf, $listSpecialHalf);
    }

    public function getCharFullSize() {
        $alphaFull = array("ａ", "ｂ", "ｃ", "ｄ", "ｅ", "ｆ", "ｇ", "ｈ", "ｉ", "ｊ", "ｋ", "ｌ", "ｍ", "ｎ", "ｏ", "ｐ", "ｋ", "ｑ", "ｒ", "ｓ", "ｔ", "ｖ", "ｗ", "ｓ", "ｘ", "ｙ", "ｚ", "Ａ", "Ｂ", "Ｃ", "Ｄ", "Ｅ", "Ｆ", "Ｇ", "Ｈ", "Ｉ", "Ｊ", "Ｋ", "Ｌ", "Ｍ", "Ｎ", "Ｏ", "Ｐ", "Ｋ", "Ｑ", "Ｒ", "Ｓ", "Ｔ", "Ｖ", "Ｗ", "Ｓ", "Ｘ", "Ｙ", "Ｚ");
        $listSpecialFull = array('｀','～','！','＠','＃','＄','％','＾','＆','＊','（','）','－','＿','＝','＋','［','｛','］','｝','；','：',"’",'”','￥￥','｜',',','＜','．','＞','／','？');
        return array($alphaFull, $listSpecialFull);
    }
}
