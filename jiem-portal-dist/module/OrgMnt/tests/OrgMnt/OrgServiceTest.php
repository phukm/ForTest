<?php

class OrgServiceTest extends \Dantai\Test\AbstractHttpControllerTestCase {

    public function testFunctionMappingMainAndStandardHall(){
        $standardHall = array(
            1 => array(
                'organizationId' => 1,
                'standardRegisteredLevel2' => 0,
                'standardRegisteredLevelPre2' => 10,
                'standardRegisteredLevel3' => 11,
                'standardRegisteredLevel4' => 0,
                'standardRegisteredLevel5' => 4
            ),
            2 => array(
                'organizationId' => '2',
                'standardRegisteredLevel2' => 1,
                'standardRegisteredLevelPre2' => 15,
                'standardRegisteredLevel3' => 6,
                'standardRegisteredLevel4' => 0,
                'standardRegisteredLevel5' => 4
            ),
        );

        $mainHall = array(
            1 => array(
                'organizationId' => 1,
                'mainRegisteredLevel1' => 0,
                'mainRegisteredLevelPre1' => 10,
                'mainRegisteredLevel2' => 11,
                'mainRegisteredLevelPre2' => 0,
                'mainRegisteredLevel3' => 4,
                'mainRegisteredLevel4' => 0,
                'mainRegisteredLevel5' => 4,

            ),
        );

        $orgService = new \OrgMnt\Service\OrgService();
        $orgService->setServiceLocator($this->getApplicationServiceLocator());
        $mergedArray = $orgService->mappingMainAndStandardHall($mainHall, $standardHall);
        // Test number records.
        $this->assertEquals(2, count($mergedArray));
        // Test number column when merge standard and main.
        $this->assertEquals(13, count($mergedArray[1]));
        // Test number column when there are standard but not have main hall.
        $this->assertEquals(13, count($mergedArray[2]));
        // Test default value when there are standard but not have main hall.
        $this->assertEquals(0, $mergedArray[2]['mainRegisteredLevel1']);
    }

    public function testFunctionMapping3ArrayDataExport(){
        $listOrg = array(
            1 => array(
                'organizationId' => 1,
                'organizationNo' => '99960501',
                'orgNameKanji' => 'テスト６０1',
                'status' => 'SUBMITTED',
                'actualExamDate' => '',
                'cd' => '12',
                'locationType' => '',
                'nameKanji' => '情報に 情報に',
                'telNo' => '0332666011',
            ),
            2 => array(
                'organizationId' => 2,
                'organizationNo' => '999605002',
                'orgNameKanji' => 'テスト６０2',
                'status' => 'DRAFT',
                'actualExamDate' => '',
                'cd' => '12',
                'locationType' => '',
                'nameKanji' => '情報に 情報に',
                'telNo' => '0332666011',
            ),
            3 => array(
                'organizationId' => 3,
                'organizationNo' => '99960503s',
                'orgNameKanji' => 'テスト６０3',
                'status' => 'DRAFT',
                'actualExamDate' => '',
                'cd' => '12',
                'locationType' => '',
                'nameKanji' => '情報に 情報に',
                'telNo' => '0332666011',
            ),
        );

        $listRegistered = array(
            1 => array(
                'organizationId' => 1,
                'mainRegisteredLevel1' => 0,
                'mainRegisteredLevelPre1' => 10,
                'mainRegisteredLevel2' => 11,
                'mainRegisteredLevelPre2' => 0,
                'mainRegisteredLevel3' => 4,
                'mainRegisteredLevel4' => 0,
                'mainRegisteredLevel5' => 4,
                'standardRegisteredLevel2' => 0,
                'standardRegisteredLevelPre2' => 10,
                'standardRegisteredLevel3' => 11,
                'standardRegisteredLevel4' => 0,
                'standardRegisteredLevel5' => 4
            ),
            2 => array(
                'organizationId' => 2,
                'mainRegisteredLevel1' => 0,
                'mainRegisteredLevelPre1' => 10,
                'mainRegisteredLevel2' => 11,
                'mainRegisteredLevelPre2' => 0,
                'mainRegisteredLevel3' => 4,
                'mainRegisteredLevel4' => 0,
                'mainRegisteredLevel5' => 4,
                'standardRegisteredLevel2' => 1,
                'standardRegisteredLevelPre2' => 15,
                'standardRegisteredLevel3' => 6,
                'standardRegisteredLevel4' => 0,
                'standardRegisteredLevel5' => 4
            ),
        );

        $listPaid = array(
            1 => array(
                'organizationId' => 1,
                'mainPaidLevel1' => 0,
                'mainPaidLevelPre1' => 10,
                'mainPaidLevel2' => 11,
                'mainPaidLevelPre2' => 0,
                'mainPaidLevel3' => 4,
                'mainPaidLevel4' => 0,
                'mainPaidLevel5' => 4,
                'standardPaidLevel2' => 0,
                'standardPaidLevelPre2' => 1,
                'standardPaidLevel3' => 3,
                'standardPaidLevel4' => 0,
                'standardPaidLevel5' => 2,
            ),
            3 => array(
                'organizationId' => 3,
                'mainPaidLevel1' => 0,
                'mainPaidLevelPre1' => 10,
                'mainPaidLevel2' => 11,
                'mainPaidLevelPre2' => 0,
                'mainPaidLevel3' => 4,
                'mainPaidLevel4' => 0,
                'mainPaidLevel5' => 4,
                'standardPaidLevel2' => 0,
                'standardPaidLevelPre2' => 1,
                'standardPaidLevel3' => 3,
                'standardPaidLevel4' => 0,
                'standardPaidLevel5' => 2,
            ),
        );

        $orgService = new \OrgMnt\Service\OrgService();
        $orgService->setServiceLocator($this->getApplicationServiceLocator());
        $mergedArray = $orgService->mapping3ArrayDataExport($listOrg,$listRegistered, $listPaid);
        // Test number records.
        $this->assertEquals(3, count($mergedArray));
        // Test number column when merge array.
        $this->assertEquals(32, count($mergedArray[0]));
        // Test number column when there aren't data of a array.
        $this->assertEquals(32, count($mergedArray[2]));
        // Test default value when there are registered but not have paid.
        $this->assertEquals(0, $mergedArray[1]['mainPaidLevel1']);
        // Test default value when there are paid but not have registered.
        $this->assertEquals(0, $mergedArray[2]['mainRegisteredLevel1']);
    }

    public function testFunctionMappingArrayPaidDataExport(){
        $listOrg = array(
            1 => array(
                'organizationId' => 1,
                'organizationNo' => '99960501',
                'orgNameKanji' => 'テスト６０1',
            ),
            2 => array(
                'organizationId' => 2,
                'organizationNo' => '999605002',
                'orgNameKanji' => 'テスト６０2',
            ),
            3 => array(
                'organizationId' => 3,
                'organizationNo' => '99960503s',
                'orgNameKanji' => 'テスト６０3',
            ),
        );

        $listPaid = array(
            1 => array(
                'organizationId' => 1,
                'mainPaidLevel1' => 0,
                'mainPaidLevelPre1' => 10,
                'mainPaidLevel2' => 11,
                'mainPaidLevelPre2' => 0,
                'mainPaidLevel3' => 4,
                'mainPaidLevel4' => 0,
                'mainPaidLevel5' => 4,
                'standardPaidLevel2' => 0,
                'standardPaidLevelPre2' => 1,
                'standardPaidLevel3' => 3,
                'standardPaidLevel4' => 0,
                'standardPaidLevel5' => 2,
                'totalPaidAmount' => 5000
            ),
            3 => array(
                'organizationId' => 3,
                'mainPaidLevel1' => 0,
                'mainPaidLevelPre1' => 10,
                'mainPaidLevel2' => 11,
                'mainPaidLevelPre2' => 0,
                'mainPaidLevel3' => 4,
                'mainPaidLevel4' => 0,
                'mainPaidLevel5' => 4,
                'standardPaidLevel2' => 0,
                'standardPaidLevelPre2' => 1,
                'standardPaidLevel3' => 3,
                'standardPaidLevel4' => 0,
                'standardPaidLevel5' => 2,
                'totalPaidAmount' => 6500
            ),
        );

        $orgService = new \OrgMnt\Service\OrgService();
        $orgService->setServiceLocator($this->getApplicationServiceLocator());
        $mergedArray = $orgService->mappingArrayPaidDataExport($listOrg, $listPaid);
        // Test number records.
        $this->assertEquals(2, count($mergedArray));
        // Test number column when merge listOrg with list Paid.
        $this->assertEquals(15, count($mergedArray[0]));
    }
}