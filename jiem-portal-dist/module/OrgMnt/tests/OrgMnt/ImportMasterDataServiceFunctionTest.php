<?php
class ImportMasterDataServiceMockupData {

    const STANDAR = 1;
    const _EMPTY = 2;
    const DUPLICATE = 3;   // Duplicate [DantaiNo]
    const BLANK = 4;
    const DUPLICATE_BLANK = 34;

    public function getMockStandarFormData() {
        return array(
            'data' => $this->_prepareStandarFromData(),
            'rules' => $this->_prepareStandarFormDataRules()
        );
    }

    public function getMockEmptyFormData() {
        return array(
            'data' => $this->_prepareEmptyFromData(),
            'rules' => $this->_prepareStandarFormDataRules()
        );
    }

    public function getMockStandarFileData($numberStandar = 1) {
        return array(
            'data' => $this->_prepareFileData(array(
                'type' => self::STANDAR,
                'numberStandar' => $numberStandar,
            )),
            'inputFileName' => 'fileImport',
            'rules' => $this->_prepareStandarFileDataRules()
        );
    }

    public function getMockEmptyFileData() {
        return array(
            'data' => $this->_prepareFileData(array('type' => self::_EMPTY,)),
            'inputFileName' => 'fileImport',
            'rules' => $this->_prepareStandarFileDataRules()
        );
    }

    public function getMockDuplicateFileData($numberStandar = 10, $numberDuplicate = 10) {
        return array(
            'data' => $this->_prepareFileData(array(
                'type' => self::DUPLICATE,
                'numberStandar' => $numberStandar,
                'numberDuplicate' => $numberDuplicate,
            )),
            'inputFileName' => 'fileImport',
            'rules' => $this->_prepareStandarFileDataRules()
        );
    }

    public function getMockDupliateAndBlankFileData($numberStandar = 10, $numberDuplicate = 10) {
        return array(
            'data' => $this->_prepareFileData(array(
                'type' => self::DUPLICATE_BLANK,
                'numberStandar' => $numberStandar,
                'numberDuplicate' => $numberDuplicate,
            )),
            'inputFileName' => 'fileImport',
            'rules' => $this->_prepareStandarFileDataRules()
        );
    }

    public function getMockEmptyFileName() {
        $unitTestData = $this->_prepareStandarFileData(50);
        
        return array(
            'data' => array(
                'fileImport' => array(
                    'name' => '',
                    'type' => '',
                    'tmp_name' => '',
                    'error' => 0,
                    'size' => 0,
                    'unitTestData' => $unitTestData,
                )
            ),
            'inputFileName' => 'fileImport',
            'rules' => $this->_prepareStandarFileDataRules()
        );
    }

    private function _prepareFileData($options = array()) {
        $unitTestData = '';
        if (!empty($options['type'])) {
            switch ($options['type']) {
                case self::STANDAR:
                    $unitTestData = $this->_prepareStandarFileData($options['numberStandar']);
                    break;
                case self::_EMPTY:
                    $unitTestData = '         ';
                    break;
                case self::DUPLICATE:
                    $unitTestData = $this->_prepareStandarFileData($options['numberStandar']) .
                            $this->_prepareDulicateFileData($options['numberDuplicate']);
                    break;
                case self::DUPLICATE_BLANK:
                    $unitTestData = $this->_prepareStandarFileData($options['numberStandar']) .
                            $this->_prepareDulicateFileData($options['numberDuplicate']) .
                            $this->_prepareBlankFileData();
                    break;
            }
        }

        return array(
            'fileImport' => array(
                'name' => 'TestData.csv',
                'type' => 'application/vnd.ms-excel',
                'tmp_name' => 'C:\xampp\tmp\phpA824.tmp',
                'error' => 0,
                'size' => 6837,
                'unitTestData' => $unitTestData,
            )
        );
    }

    private function _prepareStandarFromData() {
        return array(
            'ddbYear' => '2018',
            'ddlKai' => '2',
        );
    }

    private function _prepareEmptyFromData() {
        return array(
            'ddbYear' => '',
            'ddlKai' => '',
        );
    }

    private function _prepareStandarFormDataRules() {
        return array(
            'required' => array(
                'ddbYear' => array('field' => 'ddbYear', 'label' => 'Year'),
                'ddlKai' => array('field' => 'ddlKai', 'label' => 'Kai'),
            )
        );
    }

    private function _prepareStandarFileData($number) {
        $data = '';
        for ($i = 0; $i < $number; $i ++) {
            $data .= "" . rand(20000000, 99999999) . ",1,,101,,柏中学校,ｶｼﾜﾁﾕｳ,佐藤,百合恵,N" . rand(20000, 99999) . ",983271,,640912,北海道,札幌市中央区南２１条西５－１,,011-521-3351,011-531-3549,1,101,0,729326 \n";
        }
        return $data;
    }

    private function _prepareDulicateFileData($numberDuplicate = 10) {
        $data = '';
        for ($i = 0; $i < $numberDuplicate; $i++) {
            $data .= "" . (19999999 - $i) . ",1,,101,,柏中学校,ｶｼﾜﾁﾕｳ,佐藤,百合恵,N" . rand(20000, 99999) . ",983271,,640912,北海道,札幌市中央区南２１条西５－１,,011-521-3351,011-531-3549,1,101,0,729326 \n";
            $data .= "" . (19999999 - $i) . ",1,,101,,柏中学校,ｶｼﾜﾁﾕｳ,佐藤,百合恵,N" . rand(20000, 99999) . ",983271,,640912,北海道,札幌市中央区南２１条西５－１,,011-521-3351,011-531-3549,1,101,0,729326 \n";
        }
        return $data;
    }

    private function _prepareBlankFileData() {
        $data = '';
        for ($i = 0; $i < 20; $i++) {
            if ($i % 2 == 0) {
                $data .= ",1,,101,,柏中学校,ｶｼﾜﾁﾕｳ,佐藤,百合恵,N" . rand(20000, 99999) . ",983271,,640912,北海道,札幌市中央区南２１条西５－１,,011-521-3351,011-531-3549,1,101,0,729326 \n";
                $data .= "" . (19999999 - $i) . ",1,,101,,柏中学校,ｶｼﾜﾁﾕｳ,佐藤,百合恵,,983271,,640912,北海道,札幌市中央区南２１条西５－１,,011-521-3351,011-531-3549,1,101,0,729326 \n";
            } else {
                $data .= ",1,,101,,柏中学校,ｶｼﾜﾁﾕｳ,佐藤,百合恵,,983271,,640912,北海道,札幌市中央区南２１条西５－１,,011-521-3351,011-531-3549,1,101,0,729326 \n";
                $data .= "" . (19999999 - $i) . ",1,,101,,柏中学校,ｶｼﾜﾁﾕｳ,佐藤,百合恵,,983271,,640912,北海道,札幌市中央区南２１条西５－１,,011-521-3351,011-531-3549,1,101,0,729326 \n";
            }
        }
        return $data;
    }

    private function _prepareStandarFileDataRules($options = array()) {
        return array(
            'accepted' => '.CSV',
            'isEmpty' => !empty($options['isEmpty']) ? $options['isEmpty'] : false,
            'hasHeader' => !empty($options['hasHeader']) ? $options['hasHeader'] : false,
            'numberOfColumn' => 22,
        );
    }

}

class ImportMasterDataServiceFunctionTest extends \Dantai\Test\AbstractHttpControllerTestCase {

    private $dataImport = array();
    private $mockup;

    private function getMockupData() {
        if (empty($this->mockup)) {
            $this->mockup = new ImportMasterDataServiceMockupData();
        }

        return $this->mockup;
    }

    public function init() {
        $this->login();
        $this->dispatch('/org/importmasterdata/index');
    }

    public function setdataImport() {
        $data = array();
        for ($i = 0; $i < 50000; $i ++) {
            $data[$i] = array(
                "organizationNo" => (99999999 - $i),
                "organizationCode" => 01,
                "flagRegister" => "",
                "examLocation" => 0101,
                "undefined1" => "",
                "orgNameKanji" => '北野中学校',
                "orgNameKana" => 'ｷﾀﾉﾁﾕｳ',
                "department" => '',
                "officerName" => '夏見　千鶴子',
                "accessKey" => 'code' . (99999999 - $i),
                "email" => 256849,
                "zipCode" => '',
                "town" => 0040862,
                "address1" => '北海道',
                "address2" => '札幌市清田区北野二条３－７－３０',
                "telNo" => '',
                "fax" => '011-882-0754',
                "cityCode" => '011-882-2897',
                "stateCode" => 01,
                "schoolDivision" => 110,
                "passcode" => 0
            );
        }
        $this->dataImport = $data;
    }

    public function getOrganizationMock() {
        $dataOrg[0]['organizationNo'] = "99999998";
        $orgMock = $this->getMockBuilder('Application\Entity\Repository\OrganizationRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $orgMock->expects($this->any())
                ->method('getDantaiMasterData')
                ->will($this->returnValue($dataOrg));
        return $orgMock;
    }

    public function getAccessKeyMock() {
        $accessKey[0]['organizationNo'] = "99999998";
        $accessKeyMock = $this->getMockBuilder('Application\Entity\Repository\AccessKeyRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $accessKeyMock->expects($this->any())
                ->method('getAccessKeyMasterData')
                ->will($this->returnValue($accessKey));
        return $accessKeyMock;
    }

    public function getCityMock() {
        $city[0]['cityCode'] = "15";
        $city[0]['cityId'] = 15;
        $cityMock = $this->getMockBuilder('Application\Entity\Repository\AccessKeyRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $cityMock->expects($this->any())
                ->method('getCityMasterData')
                ->will($this->returnValue($city));
        return $cityMock;
    }

    public function getEntityMock() {
        $repositoryMock = $this->getMock('\Doctrine\ORM\EntityManager', array('getRepository', 'getReference', 'getClassMetadata', 'persist', 'flush', 'clear'), array(), '', false);

        $repositoryMock->expects($this->any())
                ->method('getClassMetadata')
                ->will($this->returnValue((object) array('name' => 'aClass')));
        $repositoryMock->expects($this->any())
                ->method('persist')
                ->will($this->returnValue(null));
        $repositoryMock->expects($this->any())
                ->method('flush')
                ->will($this->returnValue(null));
        $repositoryMock->expects($this->any())
                ->method('getReference')
                ->will($this->returnValue(null));
        $repositoryMock->expects($this->any())
                ->method('clear')
                ->will($this->returnValue(null));

        return $repositoryMock;
    }

    public function testFunctionSplitMasterData() {

        $this->login();
        $this->dispatch('/org/importmasterdata/index');
        if (!$this->dataImport) {
            $this->setdataImport();
        }

        /* @var $importMasterData \OrgMnt\Service\ImportMasterDataService */
        $importMasterData = $this->getApplicationServiceLocator()->get('OrgMnt\Service\ImportMasterDataServiceInterface');
        $importMasterData->setOrganizationRepository($this->getOrganizationMock());
        $importMasterData->setAccessRepository($this->getAccessKeyMock());
        $importMasterData->setEntityManager($this->getEntityMock());
        $importMasterData->setCityRepository($this->getCityMock());
        list($dantaiInsert, $dantaiUpdate, $accessKeyInsert, $accessKeyUpdate) = $importMasterData->splitMasterData($this->dataImport, 2016, 1);

        $this->assertEquals(array_key_exists('99999998', $dantaiUpdate), true);
        $this->assertEquals(array_key_exists('99999998', $accessKeyUpdate), true);

        $this->assertEquals(array_key_exists('99999998', $dantaiInsert), false);
        $this->assertEquals(array_key_exists('99999998', $accessKeyInsert), false);
    }

    public function testValidateWithStandarData() {
        $this->init();

        $mockup = $this->getMockupData();
        $importService = $this->getApplicationServiceLocator()
                ->get('OrgMnt\Service\ImportMasterDataServiceInterface');

        $validateImportMasterData = $importService->validateImportMasterData(array(
            'formDataSubmited' => $mockup->getMockStandarFormData(),
            'fileDataSubmited' => $mockup->getMockStandarFileData(10),
        ));

        $this->assertEquals($validateImportMasterData['messageId'], 'VIMD_MSG10');
    }

    // Test validate import master data with empty form data submited.
    public function testValidateWithEmptyFormData() {
        $this->init();

        $mockup = $this->getMockupData();
        $importService = $this->getApplicationServiceLocator()
                ->get('OrgMnt\Service\ImportMasterDataServiceInterface');

        $validateImportMasterData = $importService->validateImportMasterData(array(
            'formDataSubmited' => $mockup->getMockEmptyFormData(),
            'fileDataSubmited' => $mockup->getMockStandarFileData(10),
        ));

        $this->assertEquals($validateImportMasterData['messageId'], 'VIMD_MSG11');
    }

    // Test validate import master data with empty file submited.
    public function testValidateWithEmptyFile() {
        $this->init();

        $mockup = $this->getMockupData();
        $importService = $this->getApplicationServiceLocator()
                ->get('OrgMnt\Service\ImportMasterDataServiceInterface');

        $validateImportMasterData = $importService->validateImportMasterData(array(
            'formDataSubmited' => $mockup->getMockStandarFormData(),
            'fileDataSubmited' => $mockup->getMockEmptyFileData(),
        ));

        $this->assertEquals($validateImportMasterData['messageId'], 'VIMD_MSG6');
    }

    // Test validate import master data with duplicates DantaiNo or AccessKey
    public function testValidateWithDuplicatedFileContent() {
        $this->init();

        $mockup = $this->getMockupData();
        $importService = $this->getApplicationServiceLocator()
                ->get('OrgMnt\Service\ImportMasterDataServiceInterface');

        $validateImportMasterData = $importService->validateImportMasterData(array(
            'formDataSubmited' => $mockup->getMockStandarFormData(),
            'fileDataSubmited' => $mockup->getMockDuplicateFileData(),
        ));

        $this->assertEquals($validateImportMasterData['messageId'], 'VIMD_MSG8');
    }

    // Test validate import master data with duplicates (DantaiNo or AccessKey) and Blank data
    public function testValidateWithDuplicateAndEmptyData() {
        $this->init();

        $mockup = $this->getMockupData();
        $importService = $this->getApplicationServiceLocator()
                ->get('OrgMnt\Service\ImportMasterDataServiceInterface');

        $validateImportMasterData = $importService->validateImportMasterData(array(
            'formDataSubmited' => $mockup->getMockStandarFormData(),
            'fileDataSubmited' => $mockup->getMockDupliateAndBlankFileData(),
        ));

        $this->assertEquals($validateImportMasterData['messageId'], 'VIMD_MSG9');
    }

    // Test validate with empty file name
    public function testValidateWithEmptyFileName() {
        $this->init();

        $mockup = $this->getMockupData();
        $importService = $this->getApplicationServiceLocator()
                ->get('OrgMnt\Service\ImportMasterDataServiceInterface');

        $validateImportMasterData = $importService->validateImportMasterData(array(
            'formDataSubmited' => $mockup->getMockStandarFormData(),
            'fileDataSubmited' => $mockup->getMockEmptyFileName(),
        ));

        $this->assertEquals($validateImportMasterData['messageId'], 'VIMD_MSG4');
    }

}
