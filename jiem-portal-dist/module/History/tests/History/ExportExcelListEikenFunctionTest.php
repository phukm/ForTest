<?php 
use History\Service\ExportExcelMapper;

class ExportExcelListEikenFunctionTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    private function getData() {
        $date = \DateTime::createFromFormat('Y-m-d', '2015-06-08');
        return array(
            0 => array(
                'eikenId' => '',
                'organizationNo' => '10566000',
                'year' => 2014,
                'kai' => 1,
                'birthday' => $date,
                'primaryPassFailFlag' => 0,
                'oneExemptionFlag' => 0,
                'eikenLevelId' => '7',
                'hallClassification' => '2',
                'executionDayOfTheWeek' => '3',
                'oneExemptionFlag' => '1',
                'firstMailSendFlag' => '1',
                'schoolClassification' => '5',
                'sex' => '2',
                'barCodeStatus' => '1',
                'firstExamResultsFlag' => '3',
                'secondExecutionDayOfTheWeek' => '3',
                'secondBarCodeStatus' => '1',
            )
        );
    }

    public function getEikenTestResultMock() {
        $data = $this->getData();
        $orgMock = $this->getMockBuilder('Application\Entity\Repository\EikenTestResultRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $orgMock->expects($this->any())
                ->method('getListExamResult')
                ->will($this->returnValue($data));
        return $eikenMock;
    }
    
    public function checkExistRecordWithValue($data, $field = '', $value = ''){
        foreach ($data as $item){
            if($item[$field] == $value){
                return true;
            }
        }
        return false;
    }
    public function checkExistRecordEqValue($data, $dataResult){
        if($data && $dataResult){
            $numberRecordData = count($data);
            $numberRecordDataResult = count($dataResult);
            if(($numberRecordData + 1) == $numberRecordDataResult){
                 return true;
            }
        }
        return false;
    }
    public function checkExistRecordNumberHeader($header){
        if($header){
            $numberHeader = count($header);
            if($numberHeader == 249){
                 return true;
            }
        }
        return false;
    }
    public function checkExistRecordNumberField($dataResult){
        $flagReturn = true;
        foreach ($dataResult as $item){
            if(count($item) != 249){
                $flagReturn = false;
            }
        }
        return $flagReturn;
    }
    
    public function testWhenGetOneRecordThenGetCorrectYear() {
        $this->login();
       
        $data = $this->getData();
        $header = $this->getApplicationServiceLocator()->get('Config')['headerExcelExport']['listOfEikenExamResult'];
        
        $exportExcelMapper = new ExportExcelMapper($data, $header, $this->getApplicationServiceLocator());

        $records = $exportExcelMapper->convertToExport();
        
        $isExist = $this->checkExistRecordWithValue($records, 'year', '2014');
        $this->assertEquals($isExist,true);
    }
    public function testWhenGetOneRecordThenGetCorrectKai() {
        $this->login();
        
        $data = $this->getData();
        $header = $this->getApplicationServiceLocator()->get('Config')['headerExcelExport']['listOfEikenExamResult'];
        
        $exportExcelMapper = new ExportExcelMapper($data, $header, $this->getApplicationServiceLocator());
        $records = $exportExcelMapper->convertToExport();
        
        $isExist = $this->checkExistRecordWithValue($records, 'kai', 1);
        $this->assertEquals($isExist,true);
    }
    public function testWhenGetOneRecordThenGetCorrectOrganizationNo() {
        $this->login();
        
        $data = $this->getData();
        $header = $this->getApplicationServiceLocator()->get('Config')['headerExcelExport']['listOfEikenExamResult'];
        
        $exportExcelMapper = new ExportExcelMapper($data, $header, $this->getApplicationServiceLocator());
        $records = $exportExcelMapper->convertToExport();
        
        $isExist = $this->checkExistRecordWithValue($records, 'organizationNo', '10566000');
        $this->assertEquals($isExist,true);
    }
    public function testWhenGetOneRecordThenGetCorrectPrimaryPassFailFlag() {
        $this->login();
        
        $data = $this->getData();
        $header = $this->getApplicationServiceLocator()->get('Config')['headerExcelExport']['listOfEikenExamResult'];
        
        $exportExcelMapper = new ExportExcelMapper($data, $header, $this->getApplicationServiceLocator());
        $records = $exportExcelMapper->convertToExport();
        
        $isExist = $this->checkExistRecordWithValue($records, 'primaryPassFailFlag', '不合格');
        $this->assertEquals($isExist,true);
    }
    public function testWhenGetOneRecordThenGetCorrectTotalNumberRecordDataAndTotalNumberRecordExcel() {
        $this->login();
        
        $data = $this->getData();
        $header = $this->getApplicationServiceLocator()->get('Config')['headerExcelExport']['listOfEikenExamResult'];
        
        $exportExcelMapper = new ExportExcelMapper($data, $header, $this->getApplicationServiceLocator());
        $records = $exportExcelMapper->convertToExport();
        
        $isExist = $this->checkExistRecordEqValue($data ,$records);
        $this->assertEquals($isExist,true);
    }
    
    public function testWhenGetOneRecordThenGetCorrectEikenLevelId() {
        $this->login();
        
        $data = $this->getData();
        $header = $this->getApplicationServiceLocator()->get('Config')['headerExcelExport']['listOfEikenExamResult'];
        
        $exportExcelMapper = new ExportExcelMapper($data, $header, $this->getApplicationServiceLocator());
        $records = $exportExcelMapper->convertToExport();
        
        $isExist = $this->checkExistRecordWithValue($records, 'eikenLevelId', '5級');
        $this->assertEquals($isExist,true);
    }
    public function testWhenGetOneRecordThenGetCorrectHallClassification() {
        $this->login();
        
        $data = $this->getData();
        $header = $this->getApplicationServiceLocator()->get('Config')['headerExcelExport']['listOfEikenExamResult'];
        
        $exportExcelMapper = new ExportExcelMapper($data, $header, $this->getApplicationServiceLocator());
        $records = $exportExcelMapper->convertToExport();
        
        $isExist = $this->checkExistRecordWithValue($records, 'hallClassification', '本会場個人');
        $this->assertEquals($isExist,true);
    }
    public function testWhenGetOneRecordThenGetCorrectExecutionDayOfTheWeek() {
        $this->login();
        
        $data = $this->getData();
        $header = $this->getApplicationServiceLocator()->get('Config')['headerExcelExport']['listOfEikenExamResult'];
        
        $exportExcelMapper = new ExportExcelMapper($data, $header, $this->getApplicationServiceLocator());
        $records = $exportExcelMapper->convertToExport();
        
        $isExist = $this->checkExistRecordWithValue($records, 'executionDayOfTheWeek', '金曜');
        $this->assertEquals($isExist,true);
    }
     public function testWhenGetOneRecordThenGetCorrectOneExemptionFlag() {
        $this->login();
        
        $data = $this->getData();
        $header = $this->getApplicationServiceLocator()->get('Config')['headerExcelExport']['listOfEikenExamResult'];
        
        $exportExcelMapper = new ExportExcelMapper($data, $header, $this->getApplicationServiceLocator());
        $records = $exportExcelMapper->convertToExport();
        
        $isExist = $this->checkExistRecordWithValue($records, 'oneExemptionFlag', '一免');
        $this->assertEquals($isExist,true);
    }
}

