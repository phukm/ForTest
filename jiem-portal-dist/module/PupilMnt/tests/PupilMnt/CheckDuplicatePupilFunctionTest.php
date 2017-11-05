<?php
namespace PupilMnt;

class CheckDuplicatePupilFunctionTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    
    protected $dataImport =  array(
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
            'ưordLevel'           => '',
            'grammarLevel'        => ''
        )
    );
    
    protected $dataImportDuplicate = array(
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
    protected $dataImportDuplicateInfile = array(
        '0' => array(
            'year'                => '2015',
            'schoolYear'          => 'schoolyear',
            'orgSchoolYear'       => 'grade',
            'class'               => 'class',
            'pupilNumber'         => '1234567890',
            'firstnameKanji'      => 'FirstNameTest3xxxx',
            'lastnameKanji'       => 'LastNameTest3xxxxx',
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
            'firstnameKanji'      => 'FirstNameTest3xxxx',
            'lastnameKanji'       => 'LastNameTest3xxxxx',
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


    protected $createNewData = array(
        'firstNameKanji'=> 'FirstNameTest3',
        'lastNameKanji'=> 'LastNameTest3',
        'firstNameKana'=> 'KanaFirst',
        'lastNameKana'=> 'KanaLast',
        'birthYear'=> '1999',
        'birthMonth'=> '12',
        'birthDay'=> '22',
        'gender'=> '1',
        'year'=> '2015',
        'orgSchoolYear'=> '1',
        'classj'=> '1',
        'Number'=> '',
        'einaviId'=> '',
        'eikenId'=> '',
        'eikenPassword'=> '',
        'eikenLevel'=> '',
        'eikenYear'=> '',
        'kai'=> '',
        'eikenRead'=> '',
        'eikenListen'=> '',
        'eikenWrite'=> '',
        'eikenSpeak'=> '',
        'eikenTotal'=> '',
        'ibaLevel'=> '',
        'datetime'=> '',
        'ibaRead'=> '',
        'ibaListen'=> '',
        'ibaTotal'=> '',
        'resultVocabulary'=> '',
        'resultGrammar'=> ''
    );
    
    protected $detailDuplicateInfile = array(
        'keyDuplicate' => 'KanaFirstKanaLast||-||2010/10/10',
        'dataDetailInFile' => array(
            'KanaFirstKanaLast||-||2010/10/10' => array(
                '0' => array(
                    'year' => '2015',
                    'orgSchoolYearName' => 'grade',
                    'className' => 'class',
                    'pupilNumber' => '1234567890',
                    'nameKanji' => 'KanjiFirstKanjiLast',
                    'nameKana' => 'KanaFirstKanaLast',
                    'birthday' => '2010/10/10',
                    'gender' => '男'
                )
            )
        ),
        'dataDetailInDb' => array(),
        'status' => 1
    );
    protected $detailDuplicateLocal = array(
        'keyDuplicate' => 'KanaFirstKanaLast||-||2010/10/10',
        'dataDetailInFile' => array(),
        'dataDetailInDb' => array(
            'KanaFirstKanaLast||-||2010/10/10' => array(
                '0' => array(
                    'year' => '2015',
                    'orgSchoolYearName' => 'grade',
                    'className' => 'class',
                    'pupilNumber' => '1234567890',
                    'nameKanji' => 'KanjiFirstKanjiLast',
                    'nameKana' => 'KanaFirstKanaLast',
                    'birthday' => '2010/10/10',
                    'gender' => '男'
                )
            )
        ),
        'status' => 2
    );
    
    protected $detailDuplicateCreateNew = array(
        'firstNameKanji'=> 'FirstNameTest3',
        'lastNameKanji'=> 'LastNameTest3',
        'firstNameKana'=> 'KanaFirst',
        'lastNameKana'=> 'KanaLast',
        'birthYear'=> '1999',
        'birthMonth'=> '12',
        'birthDate'=> '22',
        'year' => '2015'
    );
    
    protected $detailDuplicateBoth = array(
        'keyDuplicate' => 'KanaFirstKanaLast||-||2010/10/10',
        'dataDetailInFile' => array(
            'KanaFirstKanaLast||-||2010/10/10' => array(
                '0' => array(
                    'year' => '2015',
                    'orgSchoolYearName' => 'grade',
                    'className' => 'class',
                    'pupilNumber' => '1234567890',
                    'nameKanji' => 'KanjiFirstKanjiLast',
                    'nameKana' => 'KanaFirstKanaLast',
                    'birthday' => '2010/10/10',
                    'gender' => '男'
                )
            )
        ),
        'dataDetailInDb' => array(
            'KanaFirstKanaLast||-||2010/10/10' => array(
                '0' => array(
                    'year' => '2015',
                    'orgSchoolYearName' => 'grade',
                    'className' => 'class',
                    'pupilNumber' => '1234567890',
                    'nameKanji' => 'KanjiFirstKanjiLast',
                    'nameKana' => 'KanaFirstKanaLast',
                    'birthday' => '2010/10/10',
                    'gender' => '男'
                )
            )
        ),
        'status' => 2
    );

    public function deleteTestPupil()
    {
        $this->login();
        $em = $this->getEntityManager();
        $pupil = $em->getRepository('Application\Entity\Pupil')->findOneBy(array(
            'firstNameKana' => 'KanaFirst', 
            'lastNameKana' => 'KanaLast'
        ));
        $testPupilId = 0;
        if($pupil){
            $testPupilId = $pupil->getId();
        }
        $eikenScore = $em->getRepository('Application\Entity\EikenScore')->findOneBy(array(
            'pupilId' => $testPupilId
        ));
        $ibaScore = $em->getRepository('Application\Entity\IBAScore')->findOneBy(array(
            'pupilId' => $testPupilId
        ));
        $simpleMeasurementResult = $em->getRepository('Application\Entity\SimpleMeasurementResult')->findOneBy(array(
            'pupilId' => $testPupilId
        ));
        $testPupil = $em->getRepository('Application\Entity\Pupil')->findOneBy(array(
            'id' => $testPupilId
        ));
        $em->remove($eikenScore);
        $em->remove($ibaScore);
        $em->remove($simpleMeasurementResult);
        $em->remove($testPupil);
        $em->flush();
    }
    
    public function testShowCorrectDuplicateStatusInDatabase()
    {
        $this->login();
        $this->dispatch('/pupil/pupil/save', \Zend\Http\Request::METHOD_POST, $this->createNewData);
        $this->dispatch('/pupil/import-pupil/duplicate', \Zend\Http\Request::METHOD_POST, $this->dataImport);
        $message = $this->translate('MsgDuplicatePupilNameInDb');
        $this->assertQueryContentRegex('body', '/' . $message . '/');
        $this->deleteTestPupil();
    }
    
    public function testShowCorrectDuplicateStatusInFile()
    {
        $this->login();
        $this->dispatch('/pupil/import-pupil/duplicate', \Zend\Http\Request::METHOD_POST, $this->dataImportDuplicateInfile);
        $message = $this->translate('MsgDuplicatePupilNameInFile');
        $this->assertQueryContentRegex('body', '/' . $message . '/');
    }
    
    public function testShowCorrectDuplicateStatusInBothDatabaseAndFile()
    {
        $this->login();
        $this->dispatch('/pupil/pupil/save', \Zend\Http\Request::METHOD_POST, $this->createNewData);
        $this->dispatch('/pupil/import-pupil/duplicate', \Zend\Http\Request::METHOD_POST, $this->dataImportDuplicate);
        $message = $this->translate('MsgDuplicatePupilNameInFileAndDb');
        $this->assertQueryContentRegex('body', '/' . $message . '/');
        $this->deleteTestPupil();
    }
    
    public function testShowCorrectDuplicatePupilInFile()
    {
        $this->login();
        $data = $this->detailDuplicateInfile;
        $datafile = isset($data['dataDetailInFile']) ? json_encode($data['dataDetailInFile'], true) : array();
        $datadb = isset($data['dataDetailInDb']) ? json_encode($data['dataDetailInDb'], true) : array();
        $postData = array(
            'keyDuplicate' => $data['keyDuplicate'],
            'dataDetailInFile' => $datafile,
            'dataDetailInDb' => $datadb,
            'status' => $data['status']
        );
        $this->dispatch('/pupil/import-pupil/detail-duplicate', \Zend\Http\Request::METHOD_POST, $postData);
        $this->assertQueryContentRegex('body', '/KanaFirstKanaLast/');
    }
    
    public function testShowCorrectDuplicatePupilInDatabase()
    {
        $this->login();
        $data = $this->detailDuplicateLocal;
        $datafile = isset($data['dataDetailInFile']) ? json_encode($data['dataDetailInFile'], true) : array();
        $datadb = isset($data['dataDetailInDb']) ? json_encode($data['dataDetailInDb'], true) : array();
        $postData = array(
            'keyDuplicate' => $data['keyDuplicate'],
            'dataDetailInFile' => $datafile,
            'dataDetailInDb' => $datadb,
            'status' => $data['status']
        );
        $this->dispatch('/pupil/import-pupil/detail-duplicate', \Zend\Http\Request::METHOD_POST, $postData);
        $this->assertQueryContentRegex('body', '/KanaFirstKanaLast/');
    }
    
    public function testShowCorrectDuplicatePupilInBothFileAndDatabase()
    {
        $this->login();
        $data = $this->detailDuplicateBoth;
        $datafile = isset($data['dataDetailInFile']) ? json_encode($data['dataDetailInFile'], true) : array();
        $datadb = isset($data['dataDetailInDb']) ? json_encode($data['dataDetailInDb'], true) : array();
        $postData = array(
            'keyDuplicate' => $data['keyDuplicate'],
            'dataDetailInFile' => $datafile,
            'dataDetailInDb' => $datadb,
            'status' => $data['status']
        );
        $this->dispatch('/pupil/import-pupil/detail-duplicate', \Zend\Http\Request::METHOD_POST, $postData);
        $this->assertQueryContentRegex('body', '/KanaFirstKanaLast/');
    }
    
    public function getdataPupilMock($pupil = array())
    {
        $pupilMock = $this->getMockBuilder('Application\Entity\Repository\PupilRepository')
                ->disableOriginalConstructor()
                ->getMock();
        
        $pupilMock->expects($this->any())
                ->method('checkDuplicatePupil')
                ->will($this->returnValue($pupil));
        return $pupilMock;
    }
    
    public function testShowCorrectDuplicateStatusInDatabaseWhenManuallyCreateNewPupil()
    {
        $this->login();
        $pupilService = $this->getApplicationServiceLocator()->get('PupilMnt\Service\PupilServiceInterface');
        $pupilService->setPupilRepository($this->getdataPupilMock($this->createNewData));
        $data = $pupilService->getAjaxCheckDuplicatePupil(true, $this->detailDuplicateCreateNew);
        $message = $this->translate('MsgDuplicatePupilNameInDb');
        $this->assertEquals($data['status'], 1);
    }
    
    public function translate($msgKey){
        return $this->getApplicationServiceLocator()->get('MVCTranslator')->translate($msgKey);
    }
}