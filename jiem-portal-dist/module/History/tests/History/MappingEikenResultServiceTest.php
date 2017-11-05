<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace History;
use \History\Service\MappingEikenResultService;

class MappingEikenResultServiceTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    
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
        
        $eikenScheduleMock = $this->getEntityEikenScheduleMock();
        $applyEikenOrgMock = $this->getEntityApplyEikenOrgMock();
        $orgSchoolYearMock = $this->getEntityOrgSchoolYearMock();
        $schoolYearMappingMock = $this->getEntitySchoolYearMappingMock();
        $repositoryMock->expects($this->any())
                ->method('getRepository')
                ->will(
                        $this->returnValueMap(
                                array(
                                    array('Application\Entity\EikenSchedule', $eikenScheduleMock),
                                    array('Application\Entity\ApplyEikenOrg', $applyEikenOrgMock),
                                    array('Application\Entity\OrgSchoolYear', $orgSchoolYearMock),
                                    array('Application\Entity\SchoolYearMapping', $schoolYearMappingMock),
                                )
                        )
        );

        return $repositoryMock;
    }
    
    public function getEntityOrgSchoolYearMock(){
        $listOrgSchoolYear = array();

        $orgSchoolYearMock = $this->getMockBuilder('Application\Entity\Repository\OrgSchoolYearRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $orgSchoolYearMock->expects($this->any())
                ->method('ListSchoolYear')
                ->will($this->returnValue($listOrgSchoolYear));
        
        return $orgSchoolYearMock;
    }
    
    public function getEntitySchoolYearMappingMock(){
        $listSchoolYear = array();

        $schoolYearMappingMock = $this->getMockBuilder('Application\Entity\Repository\SchoolYearMappingRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $schoolYearMappingMock->expects($this->any())
                ->method('getAllDataByArraySchoolYearIds')
                ->will($this->returnValue($listSchoolYear));
        
        return $schoolYearMappingMock;
    }
    
    public function getEntityApplyEikenOrgMock(){
        $applyEikenOrg = new \Application\Entity\ApplyEikenOrg();
        $applyEikenOrg->setId(1);

        $applyEikenOrgMock = $this->getMockBuilder('Application\Entity\Repository\ApplyEikenOrgRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $applyEikenOrgMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($applyEikenOrg));
        
        return $applyEikenOrgMock;
    }
    
    public function getEntityEikenScheduleMock(){
        $eikenSchedule = new \Application\Entity\EikenSchedule();
        $eikenSchedule->setId(2);
        $eikenSchedule->setYear(2015);
        $eikenSchedule->setKai(2);
        $eikenScheduleMock = $this->getMockBuilder('Application\Entity\Repository\EikenScheduleRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $eikenScheduleMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($eikenSchedule));
        
        return $eikenScheduleMock;
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
    
    public function getEntityEikenTestResultMock($resultEiken = array()) {
        if (!$resultEiken) {
            $birthdays = array('2015-02-03');
            for ($i = 0; $i < 50; $i++) {
                $firstNameKana = 'シカワ';
                $lastNameKana = 'ヒデオ';
                $birthday = $birthdays[array_rand($birthdays)];
                $resultEiken[$i] = array(
                    'id' => $i + 1,
                    'nameKanji' => '石土石土' . $i,
                    'tempNameKanji' => '石土石土' . $i,
                    'nameKana' => $firstNameKana . $lastNameKana,
                    'birthday' => new \DateTime($birthday),
                    'pupilId' => $i + 1,
                    'schoolNumber' => 1,
                    'schoolYearCode' => '小学1年生',
                    'classCode' => '小学一年生一',
                    'year' => 2015,
                    'kai' => 2,
                    'schoolClassification' => '00',
                    'totalPrimaryScore' => 888,
                    'firstExamResultsPerfectScore' => 888,
                    'oneExemptionFlag' => 1,
                    'eikenLevelId' => 5,
                    'vocabularyScore' => 5,
                    'secondExamResultsPerfectScore' => 888,
                    'readingScore' => 888,
                    'listeningScore' => 888,
                    'compositionScore' => 888,
                    'primaryPassFailFlag' => 1,
                    'primaryFailureLevel' => '',
                    'totalSecondScore' => 888,
                    'secondPassFailFlag' => 1,
                    'secondUnacceptableLevel' => '',
                    'scoringAccordingField1' => 10,
                    'scoringAccordingField2' => 10,
                    'scoringAccordingField3' => 10,
                    'scoringAccordingField4' => 10,
                    'eikenLevelId' => 1,
                    'year' => 2015,
                    'kai' => 2,
                    'cSEScoreReading' => 111,
                    'cSEScoreListening' => 112,
                    'cSEScoreWriting' => 113,
                    'cSEScoreSpeaking' => 114,
                    'primaryPassFailFlag' => 0,
                    'secondPassFailFlag' => 1,
                    'isPass' => 1,
                    'certificationDate' => new \DateTime('now'),
                );
            }
        }
        $eikenTestResultMock = $this->getMockBuilder('Application\Entity\Repository\EikenTestResultRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $eikenTestResultMock->expects($this->any())
                ->method('getEikenTestResult')
                ->will($this->returnValue($resultEiken));
        
        $eikenTestResultMock->expects($this->any())
                ->method('updateMultipleRowsWithEachId')
                ->will($this->returnValue(5));
        
        return $eikenTestResultMock;
    }
    
    public function getEntityEikenScoreMock(){
        $eikenScoreMock = $this->getMockBuilder('Application\Entity\Repository\EikenScoreRepository')
                ->disableOriginalConstructor()
                ->getMock();
        
        $eikenScoreMock->expects($this->any())
                ->method('insertResultMappingToEikenScore')
                ->will($this->returnValue(5));
        
        return $eikenScoreMock;
    }
    
    
    public function testMappingSuccessEikenTestResultWhenPutParamToFunctionMappingDataEikenResult() {
        $emMock  = $this->getEntityMock();
        $this->login();
        $year = 2016;
        $kai = 3;
        /* @var $mappingEikenService \History\Service\MappingEikenResultService */
        $mappingEikenService = new MappingEikenResultService($this->getApplicationServiceLocator(), $emMock);
        $pupilRepoMock = $this->getEntityPupilMock();
        $eikenTestResultRepoMock = $this->getEntityEikenTestResultMock();
        $eikenScoreMock = $this->getEntityEikenScoreMock();
        $mappingEikenService->setPupilRepo($pupilRepoMock);
        $mappingEikenService->setEikenTestResultRepo($eikenTestResultRepoMock);
        $mappingEikenService->setEikenScoreRepo($eikenScoreMock);
        $result = $mappingEikenService->mappingDataEikenResult($year, $kai);
        $resultExpect = array('status' => 1, 'message' => '');
        $this->assertEquals($resultExpect, $result);
    }
    
    public function testReturnEmptyArrayMappingWhenPutParamToFunctionGetDataMapping(){
        $year = 2015;
        $this->login();
        $birthdays = array('2015-02-03');
        for ($i = 0; $i < 100; $i++) {
            $firstNameKana = 'シカワカワ';
            $lastNameKana = 'ヒデオカワ';
            $birthday = $birthdays[array_rand($birthdays)];
            $eikenTestResults[$i] = array(
                'id' => $i + 1,
                'nameKanji' => '石土石土' . $i,
                'tempNameKanji' => '石土石土' . $i,
                'nameKana' => $firstNameKana . $lastNameKana,
                'birthday' => new \DateTime($birthday),
                'pupilId' => $i + 1,
                'schoolNumber' => 1,
                'schoolYearCode' => '小学1年生',
                'classCode' => '小学一年生一',
                'year' => 2015,
                'kai' => 2,
                'schoolClassification' => '00',
                'totalPrimaryScore' => 888,
                'firstExamResultsPerfectScore' => 888,
                'oneExemptionFlag' => 1,
                'eikenLevelId' => 5,
                'vocabularyScore' => 5,
                'secondExamResultsPerfectScore' => 888,
                'readingScore' => 888,
                'listeningScore' => 888,
                'compositionScore' => 888,
                'primaryPassFailFlag' => 1,
                'primaryFailureLevel' => '',
                'totalSecondScore' => 888,
                'secondPassFailFlag' => 1,
                'secondUnacceptableLevel' => '',
                'scoringAccordingField1' => 10,
                'scoringAccordingField2' => 10,
                'scoringAccordingField3' => 10,
                'scoringAccordingField4' => 10,
                'eikenLevelId' => 1,
                'year' => 2015,
                'kai' => 2,
                'cSEScoreReading' => 111,
                'cSEScoreListening' => 112,
                'cSEScoreWriting' => 113,
                'cSEScoreSpeaking' => 114,
                'primaryPassFailFlag' => 0,
                'secondPassFailFlag' => 1,
                'isPass' => 1,
                'certificationDate' => new \DateTime('now'),
            );
        }
        /* @var $mappingEikenService \History\Service\MappingEikenResultService */
        $mappingEikenService = new MappingEikenResultService($this->getApplicationServiceLocator());
        $pupilRepoMock = $this->getEntityPupilMock();
        $mappingEikenService->setPupilRepo($pupilRepoMock);
        $pupils = $mappingEikenService->getDataPupilResultForMapping($year);
        
        $schoolYearCodes = $mappingEikenService->getDataSchoolYearCode();
        
        list($mapping, $newEikenScore) = $mappingEikenService->getDataMapping($eikenTestResults, $pupils, $schoolYearCodes);
        
        $this->assertEquals(array(), $mapping);
    }
    
    public function testReturnArrayMappingWhenPutParamToFunctionGetDataMapping(){
        $year = 2015;
        $this->login();
        $birthdays = array('2015-02-03');
        for ($i = 0; $i < 100; $i++) {
            $firstNameKana = 'シカワ';
            $lastNameKana = 'ヒデオ';
            $birthday = $birthdays[array_rand($birthdays)];
            $eikenTestResults[$i] = array(
                'id' => $i + 1,
                'nameKanji' => '石土石土' . $i,
                'tempNameKanji' => '石土石土' . $i,
                'nameKana' => $firstNameKana . $lastNameKana,
                'birthday' => new \DateTime($birthday),
                'pupilId' => $i + 1,
                'schoolNumber' => 1,
                'schoolYearCode' => '小学1年生',
                'classCode' => '小学一年生一',
                'year' => 2015,
                'kai' => 2,
                'schoolClassification' => '00',
                'totalPrimaryScore' => 888,
                'firstExamResultsPerfectScore' => 888,
                'oneExemptionFlag' => 1,
                'eikenLevelId' => 5,
                'vocabularyScore' => 5,
                'secondExamResultsPerfectScore' => 888,
                'readingScore' => 888,
                'listeningScore' => 888,
                'compositionScore' => 888,
                'primaryPassFailFlag' => 1,
                'primaryFailureLevel' => '',
                'totalSecondScore' => 888,
                'secondPassFailFlag' => 1,
                'secondUnacceptableLevel' => '',
                'scoringAccordingField1' => 10,
                'scoringAccordingField2' => 10,
                'scoringAccordingField3' => 10,
                'scoringAccordingField4' => 10,
                'eikenLevelId' => 1,
                'year' => 2015,
                'kai' => 2,
                'cSEScoreReading' => 111,
                'cSEScoreListening' => 112,
                'cSEScoreWriting' => 113,
                'cSEScoreSpeaking' => 114,
                'primaryPassFailFlag' => 0,
                'secondPassFailFlag' => 1,
                'isPass' => 1,
                'certificationDate' => new \DateTime('now'),
            );
        }
        /* @var $mappingEikenService \History\Service\MappingEikenResultService */
        $mappingEikenService = new MappingEikenResultService($this->getApplicationServiceLocator());
        $pupilRepoMock = $this->getEntityPupilMock();
        $mappingEikenService->setPupilRepo($pupilRepoMock);
        $pupils = $mappingEikenService->getDataPupilResultForMapping($year);
        
        $schoolYearCodes = $mappingEikenService->getDataSchoolYearCode();
        
        list($mapping, $newEikenScore) = $mappingEikenService->getDataMapping($eikenTestResults, $pupils, $schoolYearCodes);
        
        $this->assertGreaterThan(0, count($mapping));
        $this->assertEquals(10, count(reset($mapping)));
    }

}
    