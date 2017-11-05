<?php
namespace History;
use History\Service\MappingEikenResultService;
use History\HistoryConst;
use stdClass;

class ListMappingEikenResultServiceTest extends \Dantai\Test\AbstractHttpControllerTestCase{
    private $year = 2015;
    private $kai = 1;
    
    public function testGetMappingStatus()
    {
        $this->login();
        $mappingEikenResult = new MappingEikenResultService($this->getApplicationServiceLocator());
        $eikenTestResultMock = $this->getMockBuilder('Application\Entity\Repository\EikenTestResultRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $eikenResult = new \Application\Entity\EikenTestResult();
        $eikenResult->setMappingStatus(0);
        $eikenResult->setYear($this->year);
        $eikenResult->setKai($this->kai);
        
        $eikenTestResultMock->expects($this->any())
                ->method('getTotalMappingStatus')
                ->will($this->returnValue($eikenResult));
        $mappingEikenResult->getMappingStatus($eikenTestResultMock);
        
        $result = $mappingEikenResult->getTotalMappingStatus($this->year, $this->kai);
        
        $this->assertNotEquals(HistoryConst::CANNOT_FIND_DATA, $result);
    }
    
    public function testGetCorrectDataFromEikenTestResult()
    {
        $this->login();
        $mappingEikenResult = new MappingEikenResultService($this->getApplicationServiceLocator());
        $eikenTestResultMock = $this->getMockBuilder('Application\Entity\Repository\EikenTestResultRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $eikenResult = new \Application\Entity\EikenTestResult();
        $eikenResult->setId(99);
        $eikenResult->setYear($this->year);
        $eikenResult->setKai($this->kai);
        $eikenResult->setOrgSchoolYearId(1);
        $eikenResult->setClassId(1);
        $eikenResult->setNameKana('EIKENTESTNAME');
        $eikenResult->setMappingStatus(0);
        $expectNameKana = $eikenResult->getNameKana();

        $eikenTestResultMock->expects($this->any())
                ->method('getEikenResultsDetails')
                ->will($this->returnValue($eikenResult));

        $mappingEikenResult->getEikenResult($eikenTestResultMock);
        
        $result = $mappingEikenResult->getEikenResultsDetails($this->year, $this->kai, 1, 1, 'EIKENTESTNAME', 0);

        $expectResult = $result->getNameKana();

        $this->assertEquals($expectResult,$expectNameKana);
    }
}