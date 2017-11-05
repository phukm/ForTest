<?php

namespace ConsoleInvitation\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Application\Entity\LearningHistory;
use Application\Entity\EinaviExam;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use phpseclib\Net\SFTP;
use phpseclib\Crypt\RSA;
/*
 * Service process bussiness logic transfer PersonalId to Enavi system
 * And Get data learning history and exam on Enavi insert to table EnaviExam and LearningHistory
 * Author: Uthv
 * Create: 16/09/2015
 */

class LearningProgressService implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    private $learningField = array(
        'WORD' => '単熟語',
        'GRAM' => '文法',
        'READ' => 'リーディング',
        'LIST' => 'リスニング',
        'EK' => '英検形式に慣れる'
    );
    private $kyuMapping = array(
        '10' => '1級',
        '15' => '準1級',
        '20' => '2級',
        '25' => '準2級',
        '30' => '3級',
        '40' => '4級',
        '50' => '5級'
    );
    
    private $typeSelectField = array(
        1, //inquiry learning history for Org and EikenLevel
        2, //inquiry learning history for Org and EikenLevel and SchoolYear
        3, //inquiry learning history for Org and EikenLevel and SchoolYear and Class
        4, //inquiry learning history for Org
        5, //inquiry learning history for Org and SchoolYear
        6, //inquiry learning history for Org and SchoolYear and Class
    );

    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        $this->setServiceLocator($serviceManager);
    }

    /*
     * Receive data learning info from Enavi system.
     * Author: Uthv
     * Create: 16/09/2015
     */

    public function receiveLearningInfoFromEinavi($dateExport = '-1 day') {
        // Proccess get file from Einavi System.
        $listFileLearningInfo = $this->getListFileFromEnaviFtp();
        if (empty($listFileLearningInfo)) {
            return;
        }
        // Process insert data from file to LearningHistory and EinaviExam.
        foreach ($listFileLearningInfo as $file) {
            $this->processLearningInfoFile($file);
            echo 'Process file ' . $file . PHP_EOL;
        }
        // Proccess inquiry data to InquiryStudyGear and InquiryMeasure       
        $dateGet = new \DateTime($dateExport);
        $date = $dateGet->format('Y-m-d');
        $this->processInquiryData($date);
    }

    public function receiveBatchLearningInfoFromEinavi($dateExport = '-1 day') {
        // Proccess get file from Einavi System.
        $listFileLearningInfo = $this->getListFileFromEnaviFtp();
        if (empty($listFileLearningInfo)) {
            return;
        }
        // Process insert data from file to LearningHistory and EinaviExam.
        foreach ($listFileLearningInfo as $file) {
            $this->processLearningInfoFile($file);
        }
        // Proccess inquiry data to InquiryStudyGear and InquiryMeasure
        $arrDt = explode(",", $dateExport);
        for ($i = 0; $i < count($arrDt); $i++) {

            $dateGet = new \DateTime($arrDt[$i]);
            $date = $dateGet->format('Y-m-d');
            $this->processInquiryData($date);
        }
    }

    /*
     * Process get list file data from enavi use SFTP
     *
     * Author: Uthv
     * Create: 16/09/2015
     */

    protected function getListFileFromEnaviFtp() {
        $configFtp = $this->getConfig()['einavi_studygear_ftp_config'];
        $key = $this->getConfig()['einavi_studygear_file_key'];
        $sftp = new SFTP($configFtp['host'], $configFtp['port'], $configFtp['timeout']);
        $rsa = new RSA();
        $priKey = file_get_contents($key);
        $rsa->loadKey($priKey);
        if (!$sftp->login($configFtp['username'], $rsa)) {
            return false;
        }

        $listFileLearningInfo = array();
        $outputPath = $this->getConfig()['einavi_studygear_output_path'];
        $listFile = $sftp->_list($outputPath);
        foreach ($listFile as $file) {
            $lenFileName = mb_strlen($file['filename'], 'UTF-8');
            if (1 != $file['type'] || $lenFileName <= 8) {
                continue;
            }
            if (strpos($file['filename'], '_study_history.csv') || strpos($file['filename'], '_measure_history.csv')) {
                $fileSavePath = $this->getConfig()['einavi_studygear_file_input_path'] . DIRECTORY_SEPARATOR . $file['filename'];
                $sftp->get($outputPath . $file['filename'], $fileSavePath);
                $listFileLearningInfo[] = $fileSavePath;
                $sftp->delete($outputPath . $file['filename']);
            }
        }
        return $listFileLearningInfo;
    }

    /*
     * Send file csv to Enavi system use SFTP.
     * Author: Uthv
     * Create: 16/09/2015
     */

    protected function sendFileToEnaviFtpServer($csvListFile) {
        $configFtp = $this->getConfig()['einavi_studygear_ftp_config'];
        $inputPath = $this->getConfig()['einavi_studygear_input_path'];
        $sftp = new SFTP($configFtp['host'], $configFtp['port'], $configFtp['timeout']);
        $key = $this->getConfig()['einavi_studygear_file_key'];
        $rsa = new RSA();
        $priKey = file_get_contents($key);
        $rsa->loadKey($priKey);
        if (!$sftp->login($configFtp['username'], $rsa)) {
            return false;
        }
        foreach ($csvListFile as $fileName => $filePath) {
            $isSuccess = $sftp->put($inputPath . $fileName, $filePath, SFTP::SOURCE_LOCAL_FILE);
            if ($isSuccess !== false) {
                unlink($filePath);
                echo 'send file ' . $filePath . ' to einavi '.PHP_EOL;
            }
        }
        //return $isSuccess;
    }

    /*
     * Read file csv data learning history for pupil on Enavi system.
     * Author: Uthv
     * Create: 16/09/2015
     */

    public function processLearningInfoFile($file) {
        $em = $this->getEntityManager();

        $isLearning = true;
        if (strpos($file, '_measure_history.csv')) {
            $isLearning = FALSE;
        }
        if (!strpos($file, '_study_history.csv') && $isLearning) {
            return;
        }

        $rawCsvStr = file_get_contents($file);
        $csvStr = \Dantai\Utility\CharsetConverter::shiftJisToUtf8($rawCsvStr);
        $data = \Dantai\Utility\CsvHelper::csvStrToArray($csvStr, ",");
        if (empty($data)) {
            return;
        }

        $listKyu = $em->getRepository('Application\Entity\EikenLevel')->findAll();
        $mapKyuName2KyuId = array();
        if ($listKyu) {
            foreach ($listKyu as $kyu) {
                $mapKyuName2KyuId[$kyu->getLevelName()] = $kyu->getId();
            }
        }

        if ($isLearning) {
            $index = 0;
            $header = array(
                'personal_id' => 0,
                'study_date' => 1,
                'eiken_grade' => 2,
                'learning_field' => 3,
                'plan_learning_time' => 4,
                'learning_time' => 5,
                'question_count' => 6,
                'correct_answer_count' => 7,
                'last_used_date' => 8
            ); // 3000036847,2015/09/30,40,WORD,5,3,30,4,"2015/09/30 19:10:38"
            // print_r($data);die;
            foreach ($data as $row) {
                $index ++;
                if (empty($row) || $row[$header['personal_id']] === NULL) {
                    continue;
                }
                $grade = $this->kyuMapping[$row[$header['eiken_grade']]];
                $eikenId = array_key_exists($grade, $mapKyuName2KyuId) ? $mapKyuName2KyuId[$grade] : null;

                $objLearning = new LearningHistory();
                $objLearning->setCorrectAnswerCount($row[$header['correct_answer_count']]);
                $objLearning->setEikenLevel($eikenId ? $em->getReference('\Application\Entity\EikenLevel', $eikenId) : null);
                $objLearning->setLastUsedDate(new \DateTime($row[$header['last_used_date']]));
                $objLearning->setLearningDate($this->genDateTime($row[$header['study_date']]));
                $objLearning->setLearningTime($row[$header['learning_time']]);
                // $type = $row[$header['learning_field']];
                $objLearning->setLearningType($this->learningField[$row[$header['learning_field']]]);
                $objLearning->setPersonalId($row[$header['personal_id']]);
                $objLearning->setPlanLearningTime($row[$header['plan_learning_time']]);
                $objLearning->setQuestionCount($row[$header['question_count']]);
                $em->persist($objLearning);
                if ($index == 20) {
                    $em->flush();
                    $em->clear();
                    $index = 0;
                }
            }

            $em->flush();
            $em->clear();
            //Update pupilId to table Learning History
            $em->getRepository('Application\Entity\EinaviExam')->updatePupilIdInLearningHistory();
        } else {
            $index = 0;
            $header = array(
                'personal_id' => 0,
                'study_date' => 1,
                'eiken_grade' => 2,
                'measure_kind' => 3,
                'measure_time' => 4,
                'score_max' => 5,
                'score_current' => 6,
                'pass_fail' => 7,
                'last_used_date' => 8
            );
            // 3000036846,2015/09/30,50,MOCK,"2013年度 第2回",50,12,fail,"2015/09/30 19:03:18"
            foreach ($data as $row) {

                $index ++;
                if (empty($row) || $row[$header['personal_id']] === NULL) {
                    continue;
                }
                $objExam = new EinaviExam();
                $grade = $this->kyuMapping[$row[$header['eiken_grade']]];
                $eikenId = array_key_exists($grade, $mapKyuName2KyuId) ? $mapKyuName2KyuId[$grade] : null;
                $objExam->setEikenLevel($eikenId ? $em->getReference('\Application\Entity\EikenLevel', $eikenId) : null);
                $objExam->setExamDate($this->genDateTime($row[$header['study_date']]));
                $objExam->setLastUsedDate(new \DateTime($row[$header['last_used_date']]));
                $mock = $row[$header['measure_kind']];
                if ($row[$header['measure_kind']] === 'MOCK') {
                    $mock = "模試";
                }
                $objExam->setMeasureKind($mock);
                $objExam->setMeasureTime($row[$header['measure_time']]);
                $objExam->setPassFail($row[$header['pass_fail']] != 'fail');
                $objExam->setPersonalId($row[$header['personal_id']]);
                $objExam->setScoreCurrent($row[$header['score_current']]);
                $objExam->setScoreMax($row[$header['score_max']]);
                $em->persist($objExam);
                if ($index == 20) {
                    $em->flush();
                    $em->clear();
                    $index = 0;
                }
            }
            $em->flush();
            $em->clear();
            //Update pupilId to table EinaviExam.
            $em->getRepository('Application\Entity\EinaviExam')->updatePupilIdInEinaviExam();
        }
    }
    

    /**
     * Uthv
     *
     * @param unknown $day*
     */
    public function processInquiryData($day) {
        $response = array();
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $hydrator = new DoctrineObject($em, "Application\Entity\InquiryStudyGear");
        
        $hydratorMeasure = new DoctrineObject($em, "Application\Entity\InquiryMeasure");
        
        foreach ($this->typeSelectField as $type) {
            $result = $em->getRepository('Application\Entity\InquiryStudyGear')->getListDataLearningHistory($day, $type, $this->learningField);
            if (!empty($result)) {
                foreach ($result as $item) {
                    $recordStudyGear = new \Application\Entity\InquiryStudyGear();
                    $recordStudyGear = $hydrator->hydrate($item, $recordStudyGear);
                    $em->persist($recordStudyGear);
                }
                $em->flush();
                $em->clear();
                $response['StudyGear'][$type]['type'] = $type;
                $response['StudyGear'][$type]['message'] = 'DATE: ' . $day . ' - TOTAL RESULT: ' . count($result);
            }
            
            $resultMeasure = $em->getRepository('Application\Entity\InquiryStudyGear')->getListDataEinaviExam($day, $type, $this->learningField);
            if (!empty($resultMeasure)) {
                foreach ($resultMeasure as $item) {
                    $recordMeasure = new \Application\Entity\InquiryMeasure();
                    $recordMeasure = $hydratorMeasure->hydrate($item, $recordMeasure);
                    $em->persist($recordMeasure);
                }
                $em->flush();
                $em->clear();
                $response['Measure'][$type]['type'] = $type;
                $response['Measure'][$type]['message'] = 'DATE: ' . $day . ' - TOTAL RESULT: ' . count($resultMeasure);
            }
        }     
        return $response;
    }

    public function getEikenId($name, $listEiken) {
        
    }

    public function convertNumber1byte($str) {
        return str_replace([
            '１',
            '２',
            '３',
            '４',
            '５',
            '６',
            '７',
            '８',
            '９',
            '０'
                ], [
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '0'
                ], $str);
    }

    /*
     * Get list data personalId for pupil transfer to Enavi system.
     * Author: Uthv
     * Create: 16/09/2015
     */

    public function sendPersonalIdToEinavi($dateExport = '-1 day') {
        $em = $this->getEntityManager();
        $dateGet = new \DateTime();
        $date = $dateGet->format('Ymd');
        $listFile = array();

        $lstPersonal = $em->getRepository('Application\Entity\EinaviInfo')->getListPersonalIdForPupil();
        if (empty($lstPersonal)) {
            return;
        }
        $csvData = array();
        $dt = new \DateTime($dateExport);
        $dEx = $dt->format('Y/m/d');
        foreach ($lstPersonal as $row) {
            $item = array();
            $item[0] = $row['organizationNo'];
            $item[1] = $row['personalId'];
            $item[2] = $dEx;

            array_push($csvData, $item);
        }
        $filePath = $this->getConfig()['einavi_studygear_file_output_path'] . DIRECTORY_SEPARATOR;
        $fileName = $date . '-0.csv'; // 枝番
        $strCsv = \Dantai\Utility\CsvHelper::arrayToStrCsv($csvData, ",");
        $strCsv = \Dantai\Utility\CharsetConverter::utf8ToShiftJis($strCsv);
        $strCsv = str_replace(\Dantai\Utility\CharsetConverter::utf8ToShiftJis('?'), \Dantai\Utility\CharsetConverter::utf8ToShiftJis('？'), $strCsv);

        file_put_contents($filePath . $fileName, $strCsv);
        $listFile[$fileName] = $filePath . $fileName;
        $this->sendFileToEnaviFtpServer($listFile);
    }

    public function sendBatchPersonalIdToEinavi($dateExport = '-1 day') {
        $em = $this->getEntityManager();
        $lstPersonal = $em->getRepository('Application\Entity\EinaviInfo')->getListPersonalIdForPupil();
        if (empty($lstPersonal)) {
            return;
        }
        $arrDt = explode(",", $dateExport);
        $dateGet = new \DateTime();
        $date = $dateGet->format('Ymd');
        $listFile = array();
        $csvData = array();
        for ($i = 0; $i < count($arrDt); $i++) {
            $dt = new \DateTime($arrDt[$i]);
            $dEx = $dt->format('Y/m/d');
            foreach ($lstPersonal as $row) {
                $item = array();
                $item[0] = $row['organizationNo'];
                $item[1] = $row['personalId'];
                $item[2] = $dEx;
                array_push($csvData, $item);
            }
        }
        $filePath = $this->getConfig()['einavi_studygear_file_output_path'] . DIRECTORY_SEPARATOR;
        $fileName = $date . '-0.csv'; // 枝番
        $strCsv = \Dantai\Utility\CsvHelper::arrayToStrCsv($csvData, ",");
        $strCsv = \Dantai\Utility\CharsetConverter::utf8ToShiftJis($strCsv);
        $strCsv = str_replace(\Dantai\Utility\CharsetConverter::utf8ToShiftJis('?'), \Dantai\Utility\CharsetConverter::utf8ToShiftJis('？'), $strCsv);

        file_put_contents($filePath . $fileName, $strCsv);
        $listFile[$fileName] = $filePath . $fileName;
        $this->sendFileToEnaviFtpServer($listFile);
    }

    public function getFormatDate($strDate) {
        if (preg_match('/[0-9]{8}/', $strDate)) {
            return 'Ymd';
        }
        if (preg_match('/[0-9]{4}\/[0-9]{2}\/[0-9]{2}/', $strDate)) {
            return 'Y/m/d';
        }
        if (preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}/', $strDate)) {
            return 'Y-m-d';
        }
        return 'Y/m/d';
    }

    public function genDateTime($strDate) {
        $dateFormat = $this->getFormatDate($strDate);
        if (!$dateFormat)
            return null;
        return \DateTime::createFromFormat($dateFormat, $strDate);
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function getConfig() {
        return $this->getServiceLocator()->get('Config')['ConsoleInvitation'];
    }

}
