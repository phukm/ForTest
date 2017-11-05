<?php
namespace PupilMnt;

class CheckDuplicatePupilUITest extends \Dantai\Test\AbstractHttpControllerTestCase {
    
    private $dataImport =  array(
        '0' => array(
            'year'                => '2015',
            'schoolYear'          => 'schoolyear',
            'orgSchoolYear'       => 'grade',
            'class'               => 'class',
            'pupilNumber'         => '1234567890',
            'firstnameKanji'      => 'FirstNameTest3',
            'lastnameKanji'       => 'LastNameTest3',
            'firstnameKana'       => 'KanaFirst',
            'lastnameKana'        => 'KanaLast',
            'birthday'            => '1999/12/22',
            'gender'              => '',
            'einaviId'            => '',
            'eikenId'             => '',
            'eikenPassword'       => '',
            'eikenLevel'          => '',
            'eikenYear'           => '',
            'kai'                 => '',
            'eikenScoreReading'   => '',
            'eikenScoreListening' => '',
            'eikenScoreWriting'   => '',
            'eikenScoreSpeaking'  => '',
            'ibaLevel'            => '',
            'ibaDate'             => '',
            'ibaScoreReading'     => '',
            'ibaScoreListening'   => '',
            'wordLevel'           => '',
            'grammarLevel'        => ''
        ),
        '1' => array(
            'year'                => '2015',
            'schoolYear'          => 'schoolyear',
            'orgSchoolYear'       => 'grade',
            'class'               => 'class',
            'pupilNumber'         => '1234567890',
            'firstnameKanji'      => 'FirstNameTest3',
            'lastnameKanji'       => 'LastNameTest3',
            'firstnameKana'       => 'KanaFirst',
            'lastnameKana'        => 'KanaLast',
            'birthday'            => '1999/12/22',
            'gender'              => '',
            'einaviId'            => '',
            'eikenId'             => '',
            'eikenPassword'       => '',
            'eikenLevel'          => '',
            'eikenYear'           => '',
            'kai'                 => '',
            'eikenScoreReading'   => '',
            'eikenScoreListening' => '',
            'eikenScoreWriting'   => '',
            'eikenScoreSpeaking'  => '',
            'ibaLevel'            => '',
            'ibaDate'             => '',
            'ibaScoreReading'     => '',
            'ibaScoreListening'   => '',
            'wordLevel'           => '',
            'grammarLevel'        => ''
        )
    );
    
    public function testDisplayCorrectTitleOfDuplicateScreen()
    {
        $this->login();
        $data = $this->dataImport;
        $this->dispatch('/pupil/import-pupil/duplicate', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', '/重複生徒一覧/');
    }
    
    public function testDisplayCorrectDescriptionDuplicateScreen()
    {
        $this->login();
        $data = $this->dataImport;
        $this->dispatch('/pupil/import-pupil/duplicate', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', '/重複の可能性があるものを抽出した結果の一覧です/');
    }
    
    public function testDisplayCorrectTitleOfNameKanaInTableDuplicateScreen()
    {
        $this->login();
        $data = $this->dataImport;
        $this->dispatch('/pupil/import-pupil/duplicate', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', '/氏名/');
    }
    
    public function testDisplayCorrectTitleOfBirthdayInTableDuplicateScreen()
    {
        $this->login();
        $data = $this->dataImport;
        $this->dispatch('/pupil/import-pupil/duplicate', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', '/生年月日/');
    }
    
    public function testDisplayCorrectTitleOfStatusInTableDuplicateScreen()
    {
        $this->login();
        $data = $this->dataImport;
        $this->dispatch('/pupil/import-pupil/duplicate', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', '/備考/');
    }
    
    public function testDisplayCorrectTitleOfActionInTableDuplicateScreen()
    {
        $this->login();
        $data = $this->dataImport;
        $this->dispatch('/pupil/import-pupil/duplicate', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', '/操作/');
    }
    
    public function testDisplayCorrectCancelButtonLabelDuplicateScreen()
    {
        $this->login();
        $data = $this->dataImport;
        $this->dispatch('/pupil/import-pupil/duplicate', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', '/キャンセル/');
    }
    
    public function testDisplayCorrectSaveButtonLabelDuplicateScreen()
    {
        $this->login();
        $data = $this->dataImport;
        $this->dispatch('/pupil/import-pupil/duplicate', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', '/続ける/');
    }
}
    