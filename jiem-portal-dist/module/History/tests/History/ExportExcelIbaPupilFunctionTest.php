<?php 
class ExportExcelIbaPupilFunctionTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    public function getIBATestResultMock(){
        $ibaResultArr = array();
        for($i = 0; $i < 10; $i++){
            $ibaResult = array();
            $ibaResult['executeId'] = '1000001';
            $ibaResult['fixationSEQ'] = '3';
            $ibaResult['mappingStatus'] = '0';
            $ibaResult['year'] = '2014';
            $ibaResult['executeManagerNo'] = '201599904400000001';
            $ibaResult['examDate'] = '2015/06/08';
            $ibaResult['examType'] = '0'.  rand(0,1);
            $ibaResult['eikenKyu'] = '200';
            $ibaResult['toeic'] = '200';
            $ibaResult['toefl'] = '200';
            $ibaResult['totalFlag'] = '2';
            $ibaResult['eikenKyu'] = '200';
            $ibaResult['eikenKyu'] = '200';
            $ibaResult['eikenKyu'] = '200';
            $ibaResult['pupilId'] = 10;
            $ibaResult['eikenLevelId'] = $i+1;
            $ibaResult['eikenScheduleId'] = $i+1;
            $ibaResult['read'] = $i+50;
            $ibaResult['listen'] = $i+50;
            $ibaResult['total'] = $i+50;
            $ibaResult['resultDocOutput'] = $i+1;
            $ibaResult['eikenLevelKyu'] = $i+1;
            $ibaResult['rankDisplay'] = 0 . $i+1;
            $ibaResult['eikenIdDisplay'] = 0 . $i+1;
            $ibaResult['titleUpdate'] = 0 . $i+1;
            $ibaResult['eikenLevelTotal'] = 0 . $i+1;
            $ibaResult['ekenLevelRead'] = 0 . $i+1;
            $ibaResult['eikenLevelListening'] = 0 . $i+1;
            $ibaResult['correctAnswerPercentGrammar'] = 0 . $i+1;
            $ibaResult['correctAnswerPercentReading'] = 0 . $i+1;
            $ibaResult['correctAnswerPercentListening'] = 0 . $i+1;
            $ibaResult['examDate'] = new DateTime('now');
            $ibaResult['processDate'] = new DateTime('now');
            $ibaResult['examDate'] = new DateTime('now');
            $ibaResult['answerSerialize'] = '["09","08","03","03","03","03","03","03","03","03","03","02","03","04","04","04","01","02","03","02","04","03","02","02","04","01","04","04","02","01","02","01","01","01","01","04","01","03","01","01","01","02","03","02","04","01","03","01","03","01","03","04","03","03","01","04","03","03","04","04","03","04","03","02","02","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  "]';
            $ibaResult['accuraryJugdeSerialize'] = '["03","03","03","01","05","03","06","07","03","02","01","02","03","04","04","04","01","02","03","02","04","03","02","02","04","01","04","04","02","01","02","01","01","01","01","04","01","03","01","01","01","02","03","02","04","01","03","01","03","01","03","04","03","03","01","04","03","03","04","04","03","04","03","02","02","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  "]';
            array_push($ibaResultArr, $ibaResult);
        }
        
        
        $ibaResultMock = $this->getMockBuilder('Application\Entity\Repository\IBATestResultRepository')
              ->disableOriginalConstructor()
              ->getMock();
        
        $ibaResultMock->expects($this->any())
                ->method('getHistoryPupilIBA')
                ->will($this->returnValue($ibaResultArr));
        return $ibaResultMock;
    }
    
    public function updateEikenTestResultByOne($field = '', $value = '') {
        $ibaTestResultInfo = array(
            'organizationNo' => '10566000',
            'moshikomiId' => '99904400'
        );

        $ibaTestResultInfo[$field] = $value;

        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($this->getEntityManager(), 'Application\Entity\IBATestResult');
        $ibaTestResult =  $this->getEntityManager()->getRepository('Application\Entity\IBATestResult')->find('9');
        
        $this->getEntityManager()->persist($hydrator->hydrate($ibaTestResultInfo, $ibaTestResult));
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
    }
    
    public function checkExistRecordWithValue($data, $field = '', $value = ''){
        foreach ($data as $item){
            if($item[$field] == $value){
                return true;
            }
        }
        return false;
    }
    

    public function testWhenGetOneRecordThenGetCorrectTestSet() {
        $this->login();
      
        $searchCriteria = array('id'=>9,'pupilId'=>'');

        $ibaOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $ibaOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        
        $records = $ibaOrgService->getHistoryPupilIbaExport('009900440', $searchCriteria);
        $isExist = $this->checkExistRecordWithValue($records, 'testSet', '');
        $this->assertTrue($isExist);
    }
    public function testWhenGetOneRecordThenGetCorrectIBACSETotal() {
        $this->login();
      
        $searchCriteria = array('id'=>9,'pupilId'=>'');

        $ibaOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $ibaOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $ibaOrgService->getHistoryPupilIbaExport('009900440', $searchCriteria);
        $isExist = $this->checkExistRecordWithValue($records, 'total', '50');
        $this->assertTrue($isExist);
    }
    public function testWhenGetOneRecordThenGetCorrectIBACSERead() {
        $this->login();
      
        $searchCriteria = array('id'=>9,'pupilId'=>'');

        $ibaOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $ibaOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $ibaOrgService->getHistoryPupilIbaExport('009900440', $searchCriteria);
        $isExist = $this->checkExistRecordWithValue($records, 'read', '50');
        $this->assertTrue($isExist);
    }
    public function testWhenGetOneRecordThenGetCorrectIBACSEListen() {
        $this->login();
      
        $searchCriteria = array('id'=>9,'pupilId'=>'');

        $ibaOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $ibaOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $ibaOrgService->getHistoryPupilIbaExport('009900440', $searchCriteria);
        $isExist = $this->checkExistRecordWithValue($records, 'listen', '50');
        $this->assertTrue($isExist);
    }
    public function testWhenGetOneRecordThenGetCorrectGrammar() {
         $this->login();
      
        $searchCriteria = array('id'=>9,'pupilId'=>'');

        $ibaOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $ibaOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $ibaOrgService->getHistoryPupilIbaExport('009900440', $searchCriteria);
        $isExist = $this->checkExistRecordWithValue($records, 'correctAnswerPercentGrammar', '1%');
        $this->assertTrue($isExist);
    }
    public function testWhenGetOneRecordThenGetCorrectReadGrammar() {
        $this->login();
      
        $searchCriteria = array('id'=>9,'pupilId'=>'');

        $ibaOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $ibaOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $ibaOrgService->getHistoryPupilIbaExport('009900440', $searchCriteria);
        $isExist = $this->checkExistRecordWithValue($records, 'correctAnswerPercentReading', '1%');
        $this->assertTrue($isExist);
    }
    public function testWhenGetOneRecordThenGetCorrectListenGrammar() {
        $this->login();
      
        $searchCriteria = array('id'=>9,'pupilId'=>'');

        $ibaOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $ibaOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $ibaOrgService->getHistoryPupilIbaExport('009900440', $searchCriteria);
        $isExist = $this->checkExistRecordWithValue($records, 'correctAnswerPercentListening', '1%');
        $this->assertTrue($isExist);
    }
    public function testWhenGetOneRecordThenGetCorrectEikenLevelTotal() {
                 $this->login();
      
        $searchCriteria = array('id'=>9,'pupilId'=>'');

        $ibaOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $ibaOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $ibaOrgService->getHistoryPupilIbaExport('009900440', $searchCriteria);
        $isExist = $this->checkExistRecordWithValue($records, 'eikenLevelTotal', '');
        $this->assertTrue($isExist);
    }
    public function testWhenGetOneRecordThenGetCorrectEkenLevelRead() {
         $this->login();
      
        $searchCriteria = array('id'=>9,'pupilId'=>'');

        $ibaOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $ibaOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $ibaOrgService->getHistoryPupilIbaExport('009900440', $searchCriteria);
        $isExist = $this->checkExistRecordWithValue($records, 'ekenLevelRead', '');
        $this->assertTrue($isExist);
    }
    public function testWhenGetOneRecordThenGetCorrectEkenLevelListen() {
        $this->login();
      
        $searchCriteria = array('id'=>9,'pupilId'=>'');

        $ibaOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $ibaOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $ibaOrgService->getHistoryPupilIbaExport('009900440', $searchCriteria);
        $isExist = $this->checkExistRecordWithValue($records, 'eikenLevelListening', '');
        $this->assertTrue($isExist);
    }
}

