<?php
class ExportExcelListIBAFunctionTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    public function getIBATestResultMock(){
        $ibaResultArr = array();
        for($i = 0; $i < 10; $i++){
            $ibaResult = array();
            $ibaResult['executeId'] = '1000001';
            $ibaResult['fixationSEQ'] = '3';
            $ibaResult['year'] = '2014';
            $ibaResult['executeManagerNo'] = '201599904400000001';
            $ibaResult['eikenKyu'] = '200';
            $ibaResult['toeic'] = '200';
            $ibaResult['pupilNo'] = '1';
            $ibaResult['toefl'] = '200';
            $ibaResult['totalFlag'] = '2';
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
            $ibaResult['answerSerialize'] = '["09","08","03","03","03","03","03","03","03","03","03","02","03","04","04","04","01","02","03","02","04","03","02","02","04","01","04","04","02","01","02","01","01","01","01","04","01","03","01","01","01","02","03","02","04","01","03","01","03","01","03","04","03","03","01","04","03","03","04","04","03","04","03","02","02","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  "]';
            $ibaResult['accuraryJugdeSerialize'] = '["03","03","03","01","05","03","06","07","03","02","01","02","03","04","04","04","01","02","03","02","04","03","02","02","04","01","04","04","02","01","02","01","01","01","01","04","01","03","01","01","01","02","03","02","04","01","03","01","03","01","03","04","03","03","01","04","03","03","04","04","03","04","03","02","02","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  ","  "]';
            array_push($ibaResultArr, $ibaResult);
        }
        
        
        $ibaResultMock = $this->getMockBuilder('Application\Entity\Repository\IBATestResultRepository')
              ->disableOriginalConstructor()
              ->getMock();
        
        $ibaResultMock->expects($this->any())
                ->method('getDataToExportByJisshiIdExamType')
                ->will($this->returnValue($ibaResultArr));
        return $ibaResultMock;
    }
    
    public function checkExistRecordWithValue($data, $field = '', $value = ''){
        foreach ($data as $item){
            if($item[$field] == $value){
                return true;
            }
        }
        return false;
    }
    
    public function checkAnswerValueByIndex($data, $field = 'answerSerialize', $index, $value){
        foreach ($data as $item){
            $dbField = 'seigojudge' . ($index + 1);
            if($field == 'answerSerialize'){
                $dbField = 'answer' . ($index + 1);
            }
            if ($item[$dbField] == $value) {
                return true;
            }
        }
        return false;
    }
    
    public function testWhenGetOneRecordThenGetCorrectExecuteId() {
        $this->login();
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        $isExist = $this->checkExistRecordWithValue($records, 'executeId', '1000001');
        
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectFixationSeq() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');

        $isExist = $this->checkExistRecordWithValue($records, 'fixationSEQ', '3');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectYear() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');

        $isExist = $this->checkExistRecordWithValue($records, 'year', '2014');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectExecuteManagerNo() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');

        $isExist = $this->checkExistRecordWithValue($records, 'executeManagerNo', '201599904400000001');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectEienKyu() {
       $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkExistRecordWithValue($records, 'eikenKyu', '200');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectToeic() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');

        $isExist = $this->checkExistRecordWithValue($records, 'toeic', '200');
        $this->assertTrue($isExist);
    }
 
    public function testWhenGetOneRecordThenGetCorrectToefl() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');

        $isExist = $this->checkExistRecordWithValue($records, 'toefl', '200');
        $this->assertTrue($isExist);
    }
//    totalFlag
    public function testWhenGetOneRecordThenGetCorrectTotalFlag() {
         $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');

        $isExist = $this->checkExistRecordWithValue($records, 'totalFlag', '2');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAnswer1Value() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'answerSerialize', 0, '09');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAnswer2Value() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'answerSerialize', 1, '08');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAnswer3Value() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'answerSerialize', 2, '03');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAnswer4Value() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'answerSerialize', 3, '03');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAnswer5Value() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'answerSerialize', 4, '03');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAnswer6Value() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'answerSerialize', 5, '03');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAnswer7Value() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'answerSerialize', 6, '03');
        $this->assertTrue($isExist);
    }
        
    public function testWhenGetOneRecordThenGetCorrectAnswer8Value() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'answerSerialize', 7, '03');
        $this->assertTrue($isExist);
    }
        
    public function testWhenGetOneRecordThenGetCorrectAnswer9Value() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'answerSerialize', 8, '03');
        $this->assertTrue($isExist);
    }
        
    public function testWhenGetOneRecordThenGetCorrectAnswer10Value() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'answerSerialize', 9, '03');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAccuraryJugde1Value() {
         $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'accuraryJugdeSerialize', 0, '03');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAccuraryJugde2Value() {
         $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'accuraryJugdeSerialize', 1, '03');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAccuraryJugde3Value() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'accuraryJugdeSerialize', 2, '03');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAccuraryJugde4Value() {
       $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'accuraryJugdeSerialize', 3, '01');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAccuraryJugde5Value() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'accuraryJugdeSerialize', 4, '05');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAccuraryJugde6Value() {
       $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'accuraryJugdeSerialize', 5, '03');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAccuraryJugde7Value() {
         $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'accuraryJugdeSerialize', 6, '06');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAccuraryJugde8Value() {
         $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'accuraryJugdeSerialize', 7, '07');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAccuraryJugde9Value() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'accuraryJugdeSerialize', 8, '03');
        $this->assertTrue($isExist);
    }
    
    public function testWhenGetOneRecordThenGetCorrectAccuraryJugde10Value() {
        $this->login();
        
        /* @var $eikenOrgService \Eiken\Service\ApplyEikenOrgServiceInterface */
        $eikenOrgService = $this->getApplicationServiceLocator()->get('History\Service\IbaHistoryServiceInterface');
        $eikenOrgService->setIbaTestResultRepo($this->getIBATestResultMock());
        $records = $eikenOrgService->populateDataToExportListIba('99904400','01');
        
        $isExist = $this->checkAnswerValueByIndex($records, $field = 'accuraryJugdeSerialize', 9, '02');
        $this->assertTrue($isExist);
    }
}