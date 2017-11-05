<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace PupilMnt\Service;

use PupilMnt\Service\ServiceInterface\ExportPupilServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Dantai\PrivateSession;
use Dantai\Utility\CharsetConverter;
use Dantai\Utility\CsvHelper;
use Dantai\Utility\PHPExcel;

class ExportPupilService implements ExportPupilServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    
    private $em;
    
    private $organizationId;
    
    private $organizationName;

    public function __construct()
    {
        $user = PrivateSession::getData('userIdentity');
        $this->organizationId = $user['organizationId'];
        $this->organizationName = $user['organizationName'];
    }
    
    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }

        return $this->em;
    }
    
    public function getExportPupilData($search){
        $em = $this->getEntityManager();
        $dataPupils = $em->getRepository('Application\Entity\Pupil')->getAllPupilExportByOrgAndSearch($this->organizationId, $search);
        if(!$dataPupils){
            return false;
        }
        foreach($dataPupils as $value){
           $pupilIds[] = $value['id']; 
        }
        $eikenScores = array();
        $ibaScores = array();
        $last6year = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d"), date("Y") - 6));
        $last3year = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d"), date("Y") - 3));
        $dataEikenScores = $em->getRepository('Application\Entity\EikenScore')->getEikenScoreByPupilIdsAndDate($pupilIds, $last6year);
        $dataIBAScores = $em->getRepository('Application\Entity\IBAScore')->getIBAScoreByPupilIdsAndDate($pupilIds, $last3year); 
        foreach ($dataEikenScores as $value) {
            if(empty($eikenScores[$value['pupilId']])){
                $eikenScores[$value['pupilId']] = $value;
            }
        }
        foreach ($dataIBAScores as $value) {
            if(empty($ibaScores[$value['pupilId']])){
                $ibaScores[$value['pupilId']] = $value;
            }
        }
        $pupilExports = $this->mapArrayPupilExport($dataPupils, $eikenScores, $ibaScores);
        return $pupilExports;
    }
    
    public function mapArrayPupilExport($dataPupils, $eikenScores, $ibaScores){
        $pupilExport = array();
        $index = 0;
        /*@var $importPupilService \PupilMnt\Service\ImportPupilService*/
        $importPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $eikenLevels = $importPupilService->getArrayEikenLevel();
        foreach($dataPupils as $value){
            $pupilExport[$index]['year'] = $value['year'];
            $pupilExport[$index]['orgSchoolYear'] = trim($value['displayName']);
            $pupilExport[$index]['class'] = $value['className'];
            $pupilExport[$index]['pupilNumber'] = $value['number'];
            $pupilExport[$index]['firstnameKanji'] = str_replace('﨑髙', '？？', $value['firstNameKanji']);
            $pupilExport[$index]['lastnameKanji'] = str_replace('﨑髙', '？？', $value['lastNameKanji']);
            $pupilExport[$index]['firstnameKana'] = $value['firstNameKana'];
            $pupilExport[$index]['lastnameKana'] = $value['lastNameKana'];
            $pupilExport[$index]['birthday'] = $value['birthday'] != Null ? $value['birthday']->format('Y/m/d') : '';
            $pupilExport[$index]['gender'] = $value['gender'] != -1 ? (intval($value['gender']) == 1 ? '男' : '女') : '';
            $pupilExport[$index]['einaviId'] = $value['einaviId'];
            $pupilExport[$index]['eikenId'] = $value['eikenId'];
            $pupilExport[$index]['eikenPassword'] = $value['eikenPassword'];
            $pupilExport[$index]['eikenLevel'] = '';
            $pupilExport[$index]['eikenYear'] = '';
            $pupilExport[$index]['kai'] = '';
            $pupilExport[$index]['eikenScoreReading'] = '';
            $pupilExport[$index]['eikenScoreListening'] = '';
            $pupilExport[$index]['eikenScoreWriting'] = '';
            $pupilExport[$index]['eikenScoreSpeaking'] = '';
            if(isset($eikenScores[$value['id']])){
                $eikenLevelId = $eikenScores[$value['id']]['eikenLevelId'];
                $eikenLevelName = isset($eikenLevels[$eikenLevelId]) ? $eikenLevels[$eikenLevelId] : '';
                $pupilExport[$index]['eikenLevel'] = $eikenLevelName;
                $pupilExport[$index]['eikenYear'] = $eikenScores[$value['id']]['year'];
                $pupilExport[$index]['kai'] = $eikenScores[$value['id']]['kai'];
                $pupilExport[$index]['eikenScoreReading'] = trim($eikenScores[$value['id']]['readingScore']);
                $pupilExport[$index]['eikenScoreListening'] = trim($eikenScores[$value['id']]['listeningScore']);
                $pupilExport[$index]['eikenScoreWriting'] = trim($eikenScores[$value['id']]['cSEScoreWriting']);
                $pupilExport[$index]['eikenScoreSpeaking'] = trim($eikenScores[$value['id']]['cSEScoreSpeaking']);
            }
            $pupilExport[$index]['ibaLevel'] = '';
            $pupilExport[$index]['ibaDate'] = '';
            $pupilExport[$index]['ibaScoreReading'] = '';
            $pupilExport[$index]['ibaScoreListening'] = '';
            if(isset($ibaScores[$value['id']])){
                $eikenLevelId = $ibaScores[$value['id']]['eikenLevelId'];
                $ibaLevelName = isset($eikenLevels[$eikenLevelId]) ? $eikenLevels[$eikenLevelId] : '';
                $pupilExport[$index]['ibaLevel'] = $ibaLevelName;
                $pupilExport[$index]['ibaDate'] = $ibaScores[$value['id']]['examDate'] != Null ? $ibaScores[$value['id']]['examDate']->format('Y/m/d') : '';
                $pupilExport[$index]['ibaScoreReading'] = trim($ibaScores[$value['id']]['readingScore']);
                $pupilExport[$index]['ibaScoreListening'] = trim($ibaScores[$value['id']]['listeningScore']);
            }
            $pupilExport[$index]['wordLevel'] = trim($value['resultVocabularyName']);
            $pupilExport[$index]['grammarLevel'] = trim($value['resultGrammarName']);
            $index++;
        }
        return $pupilExport;
    }
    
    public function exportDataToCsv($response, $pupils)
    {
        /*@var $importPupilService \PupilMnt\Service\ImportPupilService*/
        $importPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        array_unshift($pupils, $importPupilService->getDataHeaderImport());
        $csv = CsvHelper::arrayToStrCsv($pupils);
        $csv = mb_convert_encoding($csv, 'SJIS', 'UTF-8');
        $filenames = "生徒名簿_" . $this->organizationName . "_" . date('Ymd');
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
    
    public function exportDataToExcel2003($response, $pupils)
    {
        $objFileName = new CharsetConverter();
        $filenames = "生徒名簿_" . $this->organizationName . "_" . date('Ymd') . '.xls';
        $filenames = $objFileName->utf8ToShiftJis($filenames);
        /*@var $importPupilService \PupilMnt\Service\ImportPupilService*/
        $importPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        array_unshift($pupils, $importPupilService->getDataHeaderImport());
        $phpExcel = new PHPExcel();
        $phpExcel->export($pupils, $filenames, 'default', 1, '', 'xls');
        return $response;
    }
    
    public function exportDataToExcel2007($response, $pupils)
    {
        $objFileName = new CharsetConverter();
        $filenames = "生徒名簿_" . $this->organizationName . "_" . date('Ymd') . '.xlsx';
        $filenames = $objFileName->utf8ToShiftJis($filenames);
        /*@var $importPupilService \PupilMnt\Service\ImportPupilService*/
        $importPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        array_unshift($pupils, $importPupilService->getDataHeaderImport());
        $phpExcel = new PHPExcel();
        $phpExcel->export($pupils, $filenames, 'default', 1);
        return $response;
    }
    
    public function exportTemplateToCsv($response)
    {
        /*@var $importPupilService \PupilMnt\Service\ImportPupilService*/
        $importPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        
        $arrFieldJapan = $importPupilService->getDataHeaderImport();
        $header = $arrFieldJapan;
        $csv = CsvHelper::arrayToStrCsv(array($arrFieldJapan));
        $csv = trim(mb_convert_encoding($csv, 'SJIS', 'UTF-8'));
        $filenames = "生徒名簿登録用テンプレート";
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
        $filenames = "生徒名簿登録用テンプレート" . '.xls';
        $filenames = $objFileName->utf8ToShiftJis($filenames);
        
        /*@var $importPupilService \PupilMnt\Service\ImportPupilService*/
        $importPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        array_unshift($data, $importPupilService->getDataHeaderImport());

        $phpExcel = new PHPExcel();
        $phpExcel->export($data, $filenames, 'default', 1, '', 'xls');
        return $response;
    }
    
    public function exportTemplateToExcel2007($response)
    {
        $data = array();
        $objFileName = new CharsetConverter();
        $filenames = "生徒名簿登録用テンプレート" . '.xlsx';
        $filenames = $objFileName->utf8ToShiftJis($filenames);
        
        /*@var $importPupilService \PupilMnt\Service\ImportPupilService*/
        $importPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        array_unshift($data, $importPupilService->getDataHeaderImport());
        
        $phpExcel = new PHPExcel();
        $phpExcel->export($data, $filenames, 'default', 1, '', 'xlsx');
        return $response;
    }
}
