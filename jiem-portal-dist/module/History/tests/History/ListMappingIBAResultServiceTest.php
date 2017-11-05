<?php
namespace History;
use History\Service\MappingIbaResultService;
use History\HistoryConst;
use stdClass;

class ListMappingIBAResultServiceTest extends \Dantai\Test\AbstractHttpControllerTestCase{
    private $year = 2015;
    private $jisshiId = '6969696';
    private $examType = '01';
    public function testGetCorrectApplyIbaOrgByJisshiIdExamType()
    {
        $this->login();
        $mappingIBAResult = new MappingIbaResultService($this->getApplicationServiceLocator());
        $applyIBAOrgMock = $this->getMockBuilder('Application\Entity\Repository\ApplyIBAOrgRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $expectTestDate = '2015/12/22';
        $applyIBA = new \Application\Entity\ApplyIBAOrg();
        $applyIBA->setTestDate(new \DateTime('2015/12/22'));
        $applyIBAOrgMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($applyIBA));
        $mappingIBAResult->getApplyIBAMock($applyIBAOrgMock);
        
        $result = $mappingIBAResult->getIBAApply($this->jisshiId, $this->examType);
        $this->assertEquals($result->getTestDate()->format('Y/m/d'), $expectTestDate);
    }
    
    public function testGetCorrectDataFromIbaTestResult()
    {
        $this->login();
        $mappingIBAResult = new MappingIbaResultService($this->getApplicationServiceLocator());
        $ibaTestResultMock = $this->getMockBuilder('Application\Entity\Repository\IBATestResultRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $ibaResult = new \Application\Entity\IBATestResult(); 
        $ibaResult->setYear($this->year);
        $ibaResult->setJisshiId($this->jisshiId);
        $ibaResult->setExamType($this->examType);
        $ibaResult->setOrgSchoolYearId(1);
        $ibaResult->setClassId(1);
        $ibaResult->setNameKana('NAMEKANATEST');
        $ibaResult->setMappingStatus(0);
        
        $expectNameKana = $ibaResult->getNameKana();
        
        $ibaTestResultMock->expects($this->any())
                ->method('getIBAResultList')
                ->will($this->returnValue($ibaResult));
        $mappingIBAResult->getIBAResult($ibaTestResultMock);
        
        $result = $mappingIBAResult->getListMappingIBAResult($this->year, $this->jisshiId, $this->examType, 1, 1, 'NAMEKANATEST', 0);

        $this->assertEquals($expectNameKana, $result->getNameKana());
    }
    
    public function testGetMappingStatus()
    {
        $this->login();
        $mappingIBAResult = new MappingIbaResultService($this->getApplicationServiceLocator());
        $ibaTestResultMock = $this->getMockBuilder('Application\Entity\Repository\IBATestResultRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $ibaResult = new \Application\Entity\IBATestResult();
        $ibaResult->setMappingStatus(0);
        $ibaResult->setJisshiId($this->jisshiId);
        $ibaResult->setExamType($this->examType);
        $ibaTestResultMock->expects($this->any())
                ->method('getTotalMappingStatus')
                ->will($this->returnValue($ibaResult));
        $mappingIBAResult->getMappingStatus($ibaTestResultMock);
        
        $result = $mappingIBAResult->countMappingStatus($this->jisshiId, $this->examType);
        
        $this->assertNotEquals(HistoryConst::CANNOT_FIND_DATA, $result);
    }
}