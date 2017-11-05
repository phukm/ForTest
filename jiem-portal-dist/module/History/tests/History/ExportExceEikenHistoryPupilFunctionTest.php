<?php
use History\Service\ExportExcelMapper;

class ExportExcelListEikenHistoryPupilFunctionTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    private $header = array(
        'schoolYear' => '学年',
        'className' => 'クラス',
        'pupilNumber' => '番号',
        'nameKanji' => '氏名',
        'year' => '年度',
        'kai' => '回',
        'eikenlevelLevelName' => '受験級',
        'primaryPassFail' => '一次合否フラグ',
        'secondPassFail' => 'ニ次合否フラグ',
        'totalPrimaryScore' => '一次得点合計',
        'vocabularyFieldScore' => '語彙・熟語（文法）',
        'readingFieldScore' => '読解',
        'listeningFieldScore' => 'リスニング',
        'compositionFieldScore' => '作文',
        'totalSecondScore' => 'ニ次得点合計',
        'scoreAccordingField1' => '二次分野１',
        'scoreAccordingField2' => '二次分野2',
        'scoreAccordingField3' => '二次分野3',
        'scoreAccordingField4' => '二次分野4',
        'cseScore' => 'CSEスコア',
        'cSEScoreReading' => '読解',
        'cSEScoreListening' => 'リスニング',
        'cSEScoreWriting' => '作文',
        'cSEScoreSpeaking' => 'スピーキング',
        'eikenBand1' => '1次英検バンド',
        'eikenBand2' => '2次英検バンド',
    );
    private $data = array(
        0 =>array(
        'schoolYear' => '学年',
        'className' => 'クラス',
        'pupilNumber' => '番号',
        'nameKanji' => '氏名',
        'year' => 2015,
        'kai' => 1,
        'eikenlevelLevelName' => '受験級'
    ));
    public function getEikenTestResultMock() {
        $data = $this->data;
        $orgMock = $this->getMockBuilder('Application\Entity\Repository\EikenTestResultRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $orgMock->expects($this->any())
                ->method('getHistoryPupilEiken')
                ->will($data);
        return $orgMock;
    }
    public function checkExistRecordWithValue($data, $field = '', $value = ''){
        foreach ($data as $item){
            if($item[$field] == $value){
                return true;
            }
        }
        return false;
    }
    
    public function testWhenGetOneRecordThenGetCorrectYear() {
        $this->login();
        
        $exportExcilMapper = new ExportExcelMapper($this->data, $this->header, $this->getApplicationServiceLocator());
        $records = $exportExcilMapper->convertToExport();
        $isExist = $this->checkExistRecordWithValue($records, 'year', '2015');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectKai() {
        $this->login();
        
        $exportExcilMapper = new ExportExcelMapper($this->data, $this->header, $this->getApplicationServiceLocator());
        $records = $exportExcilMapper->convertToExport();
        $isExist = $this->checkExistRecordWithValue($records, 'kai', 1);
        $this->assertEquals($isExist,true);
    }
    
    public function testPrimaryPassFailFunctionWheneFirstExamResultsFlagForDisplayNotEmpty() {
        $this->login();
        
        $dataExport = array('firstExamResultsFlagForDisplay' => 'test');
        
        $exportExcilMapper = new ExportExcelMapper($this->data, $this->header, $this->getApplicationServiceLocator());
        $records = $exportExcilMapper->primaryPassFail($dataExport,'primaryPassFail');
        $this->assertEquals('test', $records['primaryPassFail']);
    }
    public function testPrimaryPassFailFunctionWheneOneExemptionFlagAndPrimaryPassFailFlagNotEmpty() {
        $this->login();
        
        $dataExport = array(
            'oneExemptionFlag' => 0,
            'primaryPassFailFlag' => 0,
            'primaryFailureLevel' => 'A'
            );
        
        $exportExcilMapper = new ExportExcelMapper($this->data, $this->header, $this->getApplicationServiceLocator());
        $records = $exportExcilMapper->primaryPassFail($dataExport,'primaryPassFail');
        $this->assertEquals('不合格', $records['primaryPassFail']);
    }
    public function testSecondPassFailFunctionWheneEikenLevelIdAndSecondPassFailFlagNotEmpty() {
        $this->login();
        
        $dataExport = array(
            'eikenLevelId' => 5,
            'secondPassFailFlag' => 1,
            'secondUnacceptableLevel' => 'A'
            );
        
        $exportExcilMapper = new ExportExcelMapper($this->data, $this->header, $this->getApplicationServiceLocator());
        $records = $exportExcilMapper->secondPassFail($dataExport,'secondPassFail');

        $this->assertEquals('合格', $records['secondPassFail']);
    }
    public function testTypeFieldScoreFunction() {
        $this->login();
        
        $dataExport = array(
            'vocabularyFieldScore' => '10',
            'vocabularyScore' => '20',
            'oneExemptionFlag' => 0
            );        
        $exportExcilMapper = new ExportExcelMapper($this->data, $this->header, $this->getApplicationServiceLocator());
        $records = $exportExcilMapper->typeFieldScore($dataExport,'vocabularyFieldScore','vocabularyFieldScoreOfListOfEikenHistoryPupil');

        $this->assertEquals('10/20', $records['vocabularyFieldScoreOfListOfEikenHistoryPupil']);
    }
     public function testScoreAccordingdataFunction() {
        $this->login();
        
        $dataExport = array(
            'scoreAccordingField1' => '99',
            'scoringAccordingField1' => '100'
            );
        
        $exportExcilMapper = new ExportExcelMapper($this->data, $this->header, $this->getApplicationServiceLocator());
        $records = $exportExcilMapper->scoreAccordingdata($dataExport,'scoreAccordingField1','scoreAccordingField1OfListOfEikenHistoryPupil');
        
        $this->assertEquals('99/100', $records['scoreAccordingField1OfListOfEikenHistoryPupil']);
    }
    public function testTotalPrimaryScoreFunction() {
        $this->login();
        
        $dataExport = array(
            'eikenLevelId' => 6,
            'totalPrimaryScore' => '77',
            'firstExamResultsPerfectScore' => '100',
            'oneExemptionFlag' => 0
            );
        
        $exportExcilMapper = new ExportExcelMapper($this->data, $this->header, $this->getApplicationServiceLocator());
        $records = $exportExcilMapper->totalPrimaryScoreOfListOfEikenHistoryPupil($dataExport,'totalPrimaryScore');
        
        $this->assertEquals('77/100', $records['totalPrimaryScore']);
    }
    public function testTotalSecondScoreFunction() {
        $this->login();
        
        $dataExport = array(
            'eikenLevelId' => 9,
            'totalSecondScore' => '2',
            'secondExamResultsPerfectScore' => '100'
            );
        
        $exportExcilMapper = new ExportExcelMapper($this->data, $this->header, $this->getApplicationServiceLocator());
        $records = $exportExcilMapper->totalSecondScoreOfListOfEikenHistoryPupil($dataExport,'totalSecondScore');        
        
        $this->assertEquals('2/100', $records['totalSecondScore']);
    }
    public function testCSEScoreFunction() {
        $this->login();
        
        $dataExport = array(
            'cSEScoreReading' => '10',
            'cSEScoreListening' => '20',
            'cSEScoreWriting' => '30',
            'cSEScoreSpeaking' => '40'
            );
        
        $exportExcilMapper = new ExportExcelMapper($this->data, $this->header, $this->getApplicationServiceLocator());
        $records = $exportExcilMapper->cseScore($dataExport,'cseScore');
        
        $this->assertEquals('100', $records['cseScore']);
    }
}