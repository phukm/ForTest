<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace History;
use \History\Service\MappingIbaResultService;

class MappingIbaResultServiceTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    
    public function translate($msgKey){
        return $this->getApplicationServiceLocator()->get('MVCTranslator')->translate($msgKey);
    }
    
    public function getEntityMock(){

        $repositoryMock = $this->getMock('\Doctrine\ORM\EntityManager', array('getRepository', 'getClassMetadata', 'persist', 'flush'), array(), '', false);

        $repositoryMock->expects($this->any())
                        ->method('getClassMetadata')
                        ->will($this->returnValue((object)array('name' => 'aClass')));
        $repositoryMock->expects($this->any())
                        ->method('persist')
                        ->will($this->returnValue(null));
        $repositoryMock->expects($this->any())
                        ->method('flush')
                        ->will($this->returnValue(null));
        

        $applyIbaOrgMock = $this->getEntityApplyEikenOrgMock();
        $eikenLevelMock = $this->getEntityEikenLevel();
        $repositoryMock->expects($this->any())
                ->method('getRepository')
                ->will(
                        $this->returnValueMap(
                                array(    
                                    array('Application\Entity\ApplyIBAOrg', $applyIbaOrgMock),
                                    array('Application\Entity\EikenLevel', $eikenLevelMock),
                                )
                        )
        );

        return $repositoryMock;
    }
    
    public function getEntityApplyEikenOrgMock(){
        $applyIbaOrg = new \Application\Entity\ApplyIBAOrg();
        $applyIbaOrg->setId(1);

        $applyIbaOrgMock = $this->getMockBuilder('Application\Entity\Repository\ApplyIBAOrgRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $applyIbaOrgMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($applyIbaOrg));
        
        return $applyIbaOrgMock;
    }
    
    public function getEntityPupilMock($resultPupil = array()) {
        if (!$resultPupil) {
            $birthdays = array('2015-02-03');
            for ($i = 0; $i < 50; $i++) {
                $birthday = $birthdays[array_rand($birthdays)];
                $resultPupil[$i] = array(
                    'id' => $i + 1,
                    'firstNameKanji' => '石土',
                    'lastNameKanji' => '石土' . $i,
                    'firstNameKana' => 'シカワ',
                    'lastNameKana' => 'ヒデオ',
                    'year' => 2015,
                    'className' => '1A',
                    'schoolYearId' => 1,
                    'schoolyearName' => 'Khoi1',
                    'gender' => '男',
                    'birthday' => new \DateTime($birthday),
                    'classId' => 1,
                    'number' => ($i + 1),
                    'orgSchoolYearId' => 1,
                    'orgSchoolYearName' => '小学1年生',
                    'className' => '小学一年生一',
                );
            }
        }
        $pupilMock = $this->getMockBuilder('Application\Entity\Repository\PupilRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $pupilMock->expects($this->any())
                ->method('getDataByOrgAndYearAndArraySearch')
                ->will($this->returnValue($resultPupil));
        return $pupilMock;
    }
    
    public function getEntityEikenLevel(){
        $eikenLevel = new \Application\Entity\EikenLevel();
        $eikenLevel->setId(3);
        $eikenLevel->setLevelName('2級');
        
        $eikenLevelMock = $this->getMockBuilder('Application\Entity\Repository\EikenLevelRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $eikenLevelMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($eikenLevel));
        return $eikenLevelMock;
    }   
    
    public function getEntityIbaTestResultMock($resultIba = array()) {
        if (!$resultIba) {
            $birthdays = array('2015-02-03');
            for ($i = 0; $i < 50; $i++) {
                $firstNameKana = 'シカワ';
                $lastNameKana = 'ヒデオ';
                $birthday = $birthdays[array_rand($birthdays)];
                $resultIba[$i] = array(
                    'id' => $i + 1,
                    'nameKanji' => '石土石土' . $i,
                    'tempNameKana' => $firstNameKana . $lastNameKana,
                    'nameKana' => $firstNameKana . $lastNameKana,
                    'birthday' => new \DateTime($birthday),
                    'pupilId' => $i + 1,
                    'organizationNo' => 1,
                    'schoolYear' => '小学1年生',
                    'classCode' => '小学一年生一',
                    'year' => 2015,
                    'testType' => 2,
                    'testSetNo' => '00',
                    'total' => '100',
                    'read' => '100',
                    'listen' => '100',
                    'eikenLevelTotal' => '準１級レベル以上の力があります。',
                    'ekenLevelRead' => '準１級レベルの実力があります。',
                    'eikenLevelListening' => '５級レベルまであと一歩です。',
                    'examDate' => new \DateTime('now'),
                    'isPass' => 1,
                );
            }
        }
        $ibaTestResultMock = $this->getMockBuilder('Application\Entity\Repository\IBATestResultRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $ibaTestResultMock->expects($this->any())
                ->method('getListIbaTestResult')
                ->will($this->returnValue($resultIba));
        
        $ibaTestResultMock->expects($this->any())
                ->method('updateMultipleRowsWithEachId')
                ->will($this->returnValue(5));
        
        return $ibaTestResultMock;
    }
    
    public function getEntityEikenScoreMock(){
        $eikenScoreMock = $this->getMockBuilder('Application\Entity\Repository\IBAScoreRepository')
                ->disableOriginalConstructor()
                ->getMock();
        
        $eikenScoreMock->expects($this->any())
                ->method('insertResultMappingToIbaScore')
                ->will($this->returnValue(5));
        
        return $eikenScoreMock;
    }
    
    public function testMappingSuccessEikenTestResultWhenPutParamToFunctionMappingDataIbaResult() {
        $emMock  = $this->getEntityMock();
        $this->login();
        $year = 2015;
        $jisshiId = '1234';
        $examType = '01';
        /* @var $mappingIbaService \History\Service\MappingIbaResultService */
        $mappingIbaService = new MappingIbaResultService($this->getApplicationServiceLocator(), $emMock);
        $pupilRepoMock = $this->getEntityPupilMock();
        $ibaScoreMock = $this->getEntityEikenScoreMock();
        $ibaTestResultRepoMock = $this->getEntityIbaTestResultMock();
        $mappingIbaService->setIbaScoreRepo($ibaScoreMock);
        $mappingIbaService->setPupilRepo($pupilRepoMock);
        $mappingIbaService->setIbaTestResultRepo($ibaTestResultRepoMock);
        $result = $mappingIbaService->mappingDataIbaResult($year, $jisshiId, $examType);
        $resultExpect = array('status' => 1, 'message' => '');
        $this->assertEquals($resultExpect, $result);
    }
    
    
    public function testReturnEmptyArrayMappingWhenPutParamToFunctionGetDataMapping(){
        $year = 2015;
        $this->login();
        $birthdays = array('2015-02-03');
        for ($i = 0; $i < 50; $i++) {
            $firstNameKana = 'シカワシカワ';
            $lastNameKana = 'ヒデオシカワ';
            $birthday = $birthdays[array_rand($birthdays)];
            $ibaTestResult[$i] = array(
                'id' => $i + 1,
                'nameKanji' => '石土石土' . $i,
                'tempNameKana' => $firstNameKana . $lastNameKana,
                'nameKana' => $firstNameKana . $lastNameKana,
                'birthday' => new \DateTime($birthday),
                'pupilId' => $i + 1,
                'organizationNo' => 1,
                'schoolYear' => '小学1年生',
                'classCode' => '小学一年生一',
                'year' => 2015,
                'testType' => 2,
                'testSetNo' => '00',
                'total' => '100',
                'read' => '100',
                'listen' => '100',
                'eikenLevelTotal' => '準１級レベル以上の力があります。',
                'ekenLevelRead' => '準１級レベルの実力があります。',
                'eikenLevelListening' => '５級レベルまであと一歩です。',
                'examDate' => new \DateTime('now'),
                'isPass' => 1,
            );
        }
        /* @var $mappingIbaService \History\Service\MappingIbaResultService */
        $mappingIbaService = new MappingIbaResultService($this->getApplicationServiceLocator());
        $pupilRepoMock = $this->getEntityPupilMock();
        $mappingIbaService->setPupilRepo($pupilRepoMock);
        $pupils = $mappingIbaService->getDataPupilResultForMapping($year);

        list($mapping, $newIbaScore) = $mappingIbaService->getDataMapping($ibaTestResult, $pupils);
        
        $this->assertEquals(array(), $mapping);
    }
    
    public function testReturnArrayMappingWhenPutParamToFunctionGetDataMapping(){
        $year = 2015;
        $this->login();
        $birthdays = array('2015-02-03');
        for ($i = 0; $i < 50; $i++) {
            $firstNameKana = 'シカワ';
            $lastNameKana = 'ヒデオ';
            $birthday = $birthdays[array_rand($birthdays)];
            $ibaTestResult[$i] = array(
                'id' => $i + 1,
                'nameKanji' => '石土石土' . $i,
                'tempNameKana' => $firstNameKana . $lastNameKana,
                'nameKana' => $firstNameKana . $lastNameKana,
                'birthday' => new \DateTime($birthday),
                'pupilId' => $i + 1,
                'organizationNo' => 1,
                'schoolYear' => '小学1年生',
                'classCode' => '小学一年生一',
                'year' => 2015,
                'testType' => 2,
                'testSetNo' => '00',
                'total' => '100',
                'read' => '100',
                'listen' => '100',
                'eikenLevelTotal' => '準１級レベル以上の力があります。',
                'ekenLevelRead' => '準１級レベルの実力があります。',
                'eikenLevelListening' => '５級レベルまであと一歩です。',
                'examDate' => new \DateTime('now'),
                'isPass' => 1,
            );
        }
        /* @var $mappingIbaService \History\Service\MappingIbaResultService */
        $mappingIbaService = new MappingIbaResultService($this->getApplicationServiceLocator());
        $pupilRepoMock = $this->getEntityPupilMock();
        $mappingIbaService->setPupilRepo($pupilRepoMock);
        $pupils = $mappingIbaService->getDataPupilResultForMapping($year);

        list($mapping, $newIbaScore) = $mappingIbaService->getDataMapping($ibaTestResult, $pupils);
        $this->assertGreaterThan(0, count($mapping));
        $this->assertEquals(9, count(reset($mapping)));
    }
}
