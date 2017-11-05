<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AutoMappingFunctionTest
 *
 * @author UtHV
 */
class AutoMappingFunctionTest extends \Dantai\Test\AbstractHttpControllerTestCase {

    //put your code here
    public function testCheckKaiIsValidWhenAutoMappingRunning() {
        
    }

    public function getEikenScheduleMock($lstKai = array()) {
        $schedule = array();
        if ($lstKai) {
            for ($i = 0; $i < count($lstKai); $i++) {
                $item = $lstKai[$i];
                $objSchedule = new \Application\Entity\EikenSchedule();
                $objSchedule->setId($item[0]);
                $objSchedule->setDay1stTestResult(new \DateTime($item[1]));
                $objSchedule->setDay2ndTestResult(new \DateTime($item[2]));
                $objSchedule->setKai($item[3]);
                $objSchedule->setYear($item[4]);
                $schedule[] = $objSchedule;
            }
        }
        $scheduleMock = $this->getMockBuilder('Application\Entity\Repository\EikenScheduleRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $scheduleMock->expects($this->any())
                ->method('getListEikenScheduleByTestDateResult')
                ->will($this->returnValue($schedule));
        return $scheduleMock;
    }

    public function getApplyEikenOrgMock($dataOrg = array()) {
        $applyOrg = Null;
        if ($dataOrg) {
            $applyOrg = new \Application\Entity\ApplyEikenOrg();
            $applyOrg->setId($dataOrg['id']);
            $applyOrg->setStatusAutoImport(Null);
        }
        $orgMock = $this->getMockBuilder('Application\Entity\Repository\ApplyEikenOrgRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $orgMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($applyOrg));
        return $orgMock;
    }

    public function getApplyIBAOrgMock($dataOrg = array()) {
        $orgMock = $this->getMockBuilder('Application\Entity\Repository\ApplyIBAOrgRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $orgMock->expects($this->any())
                ->method('getIBAOrgByTestDate')
                ->will($this->returnValue($dataOrg));
        return $orgMock;
    }

    public function testCanImportEikenTestResult() {
        $UACService = $this->getApplicationServiceLocator()->get('BasicConstruction\Service\UACServiceInterface');
        $year = date('Y');
        $data = array(
            array(1, ($year - 1).'-09-01', ($year - 1).'-12-31', 3, $year - 1),
            array(2, ($year).'-01-01', ($year).'04-31', 1, $year),
            array(3, ($year).'-04-01', ($year).'-06-31', 2, $year),
            array(4, ($year).'-09-01', ($year).'5-12-31', 3, $year),
            array(5, ($year + 1).'-01-01', ($year + 1).'-12-31', 1, $year + 1)
        );
        $UACService->setApplyEikenOrgRepo( $this->getApplyEikenOrgMock(array('id' => 1)));
        $UACService->setEikenScheduleRepo( $this->getEikenScheduleMock($data));
        $result = $UACService->checkImportEikenTestResult(1,1,'https://jiem-portal');
        $this->assertTrue($result);
    }

    public function testCanImportEikenTestResultRound2() {
        $UACService = $this->getApplicationServiceLocator()->get('BasicConstruction\Service\UACServiceInterface');
        $year = date('Y');
        $data = array(
            array(1, ($year - 1).'-09-01', ($year - 1).'-12-31', 3, $year - 1),
            array(2, ($year).'-01-01', ($year).'04-31', 1, $year),
            array(3, ($year).'-04-01', ($year).'-06-31', 2, $year),
            array(4, ($year).'-09-01', ($year).'5-12-31', 3, $year),
            array(5, ($year + 1).'-01-01', ($year + 1).'-12-31', 1, $year + 1)
        );
        $UACService->setApplyEikenOrgRepo( $this->getApplyEikenOrgMock(array('id' => 1)));
        $UACService->setEikenScheduleRepo( $this->getEikenScheduleMock($data));
        $result = $UACService->checkImportEikenTestResult(1,1,'https://jiem-portal');
        $this->assertTrue($result);
    }

    public function testCanNotImportEikenTestResult() {
        $UACService = $this->getApplicationServiceLocator()->get('BasicConstruction\Service\UACServiceInterface');
        $data = array(
            array(1, '2014-09-01', '2014-12-01', 3, 2014),
            array(2, '2015-01-01', '2015-04-01', 1, 2015),
            array(3, '2015-04-01', '2015-06-01', 2, 2015),
            array(4, '2015-09-01', '2015-12-01', 3, 2015)
        );
        $UACService->setApplyEikenOrgRepo( $this->getApplyEikenOrgMock(array('id' => 1)));
        $UACService->setEikenScheduleRepo( $this->getEikenScheduleMock($data));
        $result = $UACService->checkImportEikenTestResult(1,1,'https://jiem-portal');
        $this->assertFalse($result);
    }

    public function testCanImportIBATestResult() {
        $UACService = $this->getApplicationServiceLocator()->get('BasicConstruction\Service\UACServiceInterface');
        $result = $UACService->checkImportIBATestResult(1,1,1,'https://jiem-portal');
        $this->assertTrue($result);
    }

    public function testCanNotImportIBATestResult() {
        $UACService = $this->getApplicationServiceLocator()->get('BasicConstruction\Service\UACServiceInterface');
        $result = $UACService->checkImportIBATestResult(0,1,1,'https://jiem-portal');
        $this->assertFalse($result);
    }

}
