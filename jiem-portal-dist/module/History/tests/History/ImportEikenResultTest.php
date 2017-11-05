<?php
namespace History;
use History\Service\MappingEikenResultService;
use Dantai\PrivateSession;
use stdClass;

class ImportEikenResultTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    
    protected $expectKekka = 10;
    
    public function testCorrectKekkaWhenGetEikenDataFromApi()
    {
        $mappingEikenResult = new MappingEikenResultService($this->getApplicationServiceLocator());
        $uketukeClientMock = $this->getMockBuilder('\Dantai\Api\UkestukeClient')->getMock();   
        $eikenApiResult = \Zend\Json\Json::decode('{"info":null, "password":"123456", "sort":"110566000C5 012339882", "kekka":"10", "nendo":"2015", "kai":"1", "dantaino":"90010100"}');    
        $uketukeClientMock->expects($this->any())        
                ->method('callEir2b01')        
                ->will($this->returnValue($eikenApiResult));
        $mappingEikenResult->setUketukeClient($uketukeClientMock);
        $result = $mappingEikenResult->getEikenExamResult("10566000", 2015, 1);
        $this->assertEquals($result->kekka, $eikenApiResult->kekka);
    }
    
    public function testSaveResultToDatabaseSuccess()
    {
        $a = new stdClass;
        $a->eikenArray = array();
        $a->eikenArray[0] = new stdClass;
        $a->eikenArray[0]->info = null;
        $a->eikenArray[0]->password = "123456";
        $a->eikenArray[0]->sort = "110566000C5 012339882";
        $a->eikenArray[0]->kekka = "10";
        $a->eikenArray[0]->nendo = "2015";
        $a->eikenArray[0]->kai = "1";
        $a->eikenArray[0]->dantaino = "90010100";
        $a->eikenArray[0]->birthday = "1999/07/09";
        $a->eikenArray[0]->kyucd = "3";
        $a->eikenArray[0]->ichijigouhiflg = 1;
        $a->eikenArray[0]->ichimenflg = 0;
        $a->eikenArray[0]->nijigouhiflg = 1;
        $a->eikenArray[0]->eikenid = null;
        $a->eikenArray[0]->uketsukeno = null;
        $a->eikenArray[0]->kaijokbn = 1;
        $a->eikenArray[0]->jishiyoubi = null;
        $a->eikenArray[0]->jukenno = null;
        $a->eikenArray[0]->simei = null;
        $a->eikenArray[0]->gakkouno = null;
        $a->eikenArray[0]->gakunenno = 1;
        $a->eikenArray[0]->kumi = 1;
        $a->eikenArray[0]->ichiji1 = 2;
        $a->eikenArray[0]->ichiji2 = 2;
        $a->eikenArray[0]->ichiji3 = 2;
        $a->eikenArray[0]->ichiji4 = 2;
        $a->eikenArray[0]->ichiji5 = 2;
        $a->eikenArray[0]->ichiji6 = 2;
        $a->eikenArray[0]->ichiji7 = 2;
        $a->eikenArray[0]->ichiji8 = 2;
        $a->eikenArray[0]->ichijikei = 16;
        $a->eikenArray[0]->ichijilevel = null;
        $a->eikenArray[0]->niji1 = 3;
        $a->eikenArray[0]->niji2 = 3;
        $a->eikenArray[0]->niji3 = 3;
        $a->eikenArray[0]->niji4 = 3;
        $a->eikenArray[0]->niji5 = 3;
        $a->eikenArray[0]->niji6 = 3;
        $a->eikenArray[0]->niji7 = 3;
        $a->eikenArray[0]->niji8 = 3;
        $a->eikenArray[0]->nijikei = 24;
        $a->eikenArray[0]->nijilevel = null;
        $a->eikenArray[0]->nijikaijo = null;
        $a->eikenArray[0]->nijijikan_ji = null;
        $a->eikenArray[0]->nijijikan_hun = null;
        $a->eikenArray[0]->ichijimailflg = null;
        $a->eikenArray[0]->nijimailflg = null;
        $a->eikenArray[0]->createdt = null;
        $a->eikenArray[0]->updatedt = null;
        $a->eikenArray[0]->hakkodt = null;
        $a->eikenArray[0]->nouhinkbn = null;
        $a->eikenArray[0]->junhonkbn = null;
        $a->eikenArray[0]->kokunaigaikbn = null;
        $a->eikenArray[0]->hassokbn = null;
        $a->eikenArray[0]->syousyokbn = null;
        $a->eikenArray[0]->hyojikyu = null;
        $a->eikenArray[0]->jyukenchi = null;
        $a->eikenArray[0]->simei_kanji = null;
        $a->eikenArray[0]->simei_romaji = "EIKENRESULTTEST";
        $a->eikenArray[0]->simei_romaji_m = null;
        $a->eikenArray[0]->simei_kana = null;
        $a->eikenArray[0]->simei_kanji = null;
        $a->eikenArray[0]->yubinbangou = null;
        $a->eikenArray[0]->jyusyo1 = null;
        $a->eikenArray[0]->jyusyo2 = null;
        $a->eikenArray[0]->jyusyo3 = null;
        $a->eikenArray[0]->jyusyo4 = null;
        $a->eikenArray[0]->jyusyo5 = null;
        $a->eikenArray[0]->kokuchi = null;
        $a->eikenArray[0]->batchnum = null;
        $a->eikenArray[0]->seirinum = null;
        $a->eikenArray[0]->gakkokbn = null;
        $a->eikenArray[0]->hyojikumi = null;
        $a->eikenArray[0]->sei = null;
        $a->eikenArray[0]->bcdumu = null;
        $a->eikenArray[0]->bcd = null;
        $a->eikenArray[0]->dantaimei = null;
        $a->eikenArray[0]->chui1 = null;
        $a->eikenArray[0]->chui2 = null;
        $a->eikenArray[0]->chui3 = null;
        $a->eikenArray[0]->shikenkbn = null;
        $a->eikenArray[0]->ichijikekka = null;
        $a->eikenArray[0]->ichijikekka_hyoji = null;
        $a->eikenArray[0]->ichijikekka_manten = null;
        $a->eikenArray[0]->ichijikekka_gokaku = null;
        $a->eikenArray[0]->ichijikekka_fugokakua = null;
        $a->eikenArray[0]->ichijikekka_gokakuheikin = null;
        $a->eikenArray[0]->ichijikekka_jukensyaheikin = null;
        $a->eikenArray[0]->ichijiadvice1 = null;
        $a->eikenArray[0]->ichijiadvice2 = null;
        $a->eikenArray[0]->ichijiadvice3 = null;
        $a->eikenArray[0]->ichijiadvice4 = null;
        $a->eikenArray[0]->ichijiadvice5 = null;
        $a->eikenArray[0]->ichijiadvice6 = null;
        $a->eikenArray[0]->seikai = null;
        $a->eikenArray[0]->seigo = null;
        $a->eikenArray[0]->setumei1 = null;
        $a->eikenArray[0]->setumei2 = null;
        $a->eikenArray[0]->manninflg = null;
        $a->eikenArray[0]->manninbunsyo = null;
        $a->eikenArray[0]->niji_kaijo_kbn = null;
        $a->eikenArray[0]->kaijonum = null;
        $a->eikenArray[0]->kaijomei = null;
        $a->eikenArray[0]->niji_yubin_no = null;
        $a->eikenArray[0]->niji_jusyo = null;
        $a->eikenArray[0]->keiro1 = null;
        $a->eikenArray[0]->keiro2 = null;
        $a->eikenArray[0]->keiro3 = null;
        $a->eikenArray[0]->chizu = null;
        $a->eikenArray[0]->syugojikan = null;
        $a->eikenArray[0]->syugojikan_hyoji = null;
        $a->eikenArray[0]->syugojikan_flg = null;
        $a->eikenArray[0]->syasin_tempu = null;
        $a->eikenArray[0]->junkaijo_hyoji = null;
        $a->eikenArray[0]->keikohin = null;
        $a->eikenArray[0]->seiseki_comment = null;
        $a->eikenArray[0]->ichijihugokaku = null;
        $a->eikenArray[0]->tokuten_1 = null;
        $a->eikenArray[0]->haiten_1 = null;
        $a->eikenArray[0]->seitouritsu_1 = null;
        $a->eikenArray[0]->heikin_1 = null;
        $a->eikenArray[0]->goukakuheikin_1 = null;
        $a->eikenArray[0]->tokuten_2 = null;
        $a->eikenArray[0]->haiten_2 = null;
        $a->eikenArray[0]->seitouritsu_2 = null;
        $a->eikenArray[0]->heikin_2 = null;
        $a->eikenArray[0]->goukakuheikin_2 = null;
        $a->eikenArray[0]->tokuten_3 = null;
        $a->eikenArray[0]->haiten_3 = null;
        $a->eikenArray[0]->seitouritsu_3 = null;
        $a->eikenArray[0]->heikin_3 = null;
        $a->eikenArray[0]->goukakuheikin_3 = null;
        $a->eikenArray[0]->tokuten_4 = null;
        $a->eikenArray[0]->haiten_4 = null;
        $a->eikenArray[0]->seitouritsu_4 = null;
        $a->eikenArray[0]->heikin_4 = null;
        $a->eikenArray[0]->goukakuheikin_4 = null;
        $a->eikenArray[0]->bunyatokuten_1 = null;
        $a->eikenArray[0]->bunyatokuten_2 = null;
        $a->eikenArray[0]->bunyatokuten_3 = null;
        $a->eikenArray[0]->bunyatokuten_4 = null;
        $a->eikenArray[0]->manten_1 = null;
        $a->eikenArray[0]->manten_2 = null;
        $a->eikenArray[0]->manten_3 = null;
        $a->eikenArray[0]->manten_4 = null;
        $a->eikenArray[0]->daimon_1 = null;
        $a->eikenArray[0]->daimon_2 = null;
        $a->eikenArray[0]->daimon_3 = null;
        $a->eikenArray[0]->daimon_4 = null;
        $a->eikenArray[0]->mondaisu_1 = null;
        $a->eikenArray[0]->mondaisu_2 = null;
        $a->eikenArray[0]->mondaisu_3 = null;
        $a->eikenArray[0]->mondaisu_4 = null;
        $a->eikenArray[0]->advice_1 = null;
        $a->eikenArray[0]->advice_2 = null;
        $a->eikenArray[0]->advice_3 = null;
        $a->eikenArray[0]->advice_4 = null;
        $a->eikenArray[0]->oshirase_1 = null;
        $a->eikenArray[0]->oshirase_2 = null;
        $a->eikenArray[0]->graph_1 = null;
        $a->eikenArray[0]->graph_2 = null;
        $a->eikenArray[0]->merit_1 = null;
        $a->eikenArray[0]->merit_2 = null;
        $a->eikenArray[0]->merit_3 = null;
        $a->eikenArray[0]->merit_4 = null;
        $a->eikenArray[0]->merit_5 = null;
        $a->eikenArray[0]->merit_6 = null;
        $a->eikenArray[0]->merit_7 = null;
        $a->eikenArray[0]->merit_8 = null;
        $a->eikenArray[0]->merit_9 = null;
        $a->eikenArray[0]->merit_10 = null;
        $a->eikenArray[0]->merit_11 = null;
        $a->eikenArray[0]->merit_12 = null;
        $a->eikenArray[0]->merit_13 = null;
        $a->eikenArray[0]->merit_14 = null;
        $a->eikenArray[0]->merit_15 = null;
        $a->eikenArray[0]->cando_1 = null;
        $a->eikenArray[0]->syousyonum = null;
        $a->eikenArray[0]->sort = null;
        $a->eikenArray[0]->dantai_chokuso = null;
        $a->eikenArray[0]->niji_hakkodt = null;
        $a->eikenArray[0]->niji_nouhinkbn = null;
        $a->eikenArray[0]->niji_junhonkbn = null;
        $a->eikenArray[0]->niji_jishiyoubi = null;
        $a->eikenArray[0]->niji_kokunaigaikbn = null;
        $a->eikenArray[0]->niji_hassokbn = null;
        $a->eikenArray[0]->niji_syousyokbn = null;
        $a->eikenArray[0]->niji_jyukenchi = null;
        $a->eikenArray[0]->niji_kokuchi = null;
        $a->eikenArray[0]->niji_batchnum = null;
        $a->eikenArray[0]->niji_seirinum = null;
        $a->eikenArray[0]->niji_bcdumu = null;
        $a->eikenArray[0]->niji_bcd = null;
        $a->eikenArray[0]->niji_chui1 = null;
        $a->eikenArray[0]->niji_chui2 = null;
        $a->eikenArray[0]->niji_chui3 = null;
        $a->eikenArray[0]->niji_kbn = null;
        $a->eikenArray[0]->nijikekka = null;
        $a->eikenArray[0]->nijikekka_hyoji = null;
        $a->eikenArray[0]->nijikekka_manten = null;
        $a->eikenArray[0]->nijikekka_gokaku = null;
        $a->eikenArray[0]->nijikekka_fugokakua = null;
        $a->eikenArray[0]->nijiadvice1 = null;
        $a->eikenArray[0]->nijiadvice2 = null;
        $a->eikenArray[0]->nijiadvice3 = null;
        $a->eikenArray[0]->nijiadvice4 = null;
        $a->eikenArray[0]->nijiadvice5 = null;
        $a->eikenArray[0]->nijiadvice6 = null;
        $a->eikenArray[0]->nijitokuten_1 = null;
        $a->eikenArray[0]->nijitokuten_2 = null;
        $a->eikenArray[0]->nijitokuten_3 = null;
        $a->eikenArray[0]->nijitokuten_4 = null;
        $a->eikenArray[0]->nijitokuten_5 = null;
        $a->eikenArray[0]->nijihaiten_1 = null;
        $a->eikenArray[0]->nijihaiten_2 = null;
        $a->eikenArray[0]->nijihaiten_3 = null;
        $a->eikenArray[0]->nijihaiten_4 = null;
        $a->eikenArray[0]->nijihaiten_5 = null;
        $a->eikenArray[0]->nijimerit_1 = null;
        $a->eikenArray[0]->nijimerit_2 = null;
        $a->eikenArray[0]->nijimerit_3 = null;
        $a->eikenArray[0]->nijimerit_4 = null;
        $a->eikenArray[0]->nijimerit_5 = null;
        $a->eikenArray[0]->nijimerit_6 = null;
        $a->eikenArray[0]->nijimerit_7 = null;
        $a->eikenArray[0]->nijimerit_8 = null;
        $a->eikenArray[0]->nijimerit_9 = null;
        $a->eikenArray[0]->nijimerit_10 = null;
        $a->eikenArray[0]->nijimerit_11 = null;
        $a->eikenArray[0]->nijimerit_12 = null;
        $a->eikenArray[0]->nijimerit_13 = null;
        $a->eikenArray[0]->nijimerit_14 = null;
        $a->eikenArray[0]->nijimerit_15 = null;
        $a->eikenArray[0]->cando_2 = null;
        $a->eikenArray[0]->niji_oshirase = null;
        $a->eikenArray[0]->niji_syousyonum = null;
        $a->eikenArray[0]->niji_ninteibi = null;
        $a->eikenArray[0]->niji_sort = null;
        $a->eikenArray[0]->niji_dantai_chokuso = null;
        $a->eikenArray[0]->pin_number = null;
        $a->eikenArray[0]->cse_total_1_rl = null;
        $a->eikenArray[0]->cse_total_1_rlw = null;
        $a->eikenArray[0]->cse_total_2_rls = null;
        $a->eikenArray[0]->cse_total_2_rlws = null;
        $a->eikenArray[0]->cse_reading = null;
        $a->eikenArray[0]->cse_listening = null;
        $a->eikenArray[0]->cse_writing = null;
        $a->eikenArray[0]->cse_speaking = null;
        $a->eikenArray[0]->eikenband_1 = null;
        $a->eikenArray[0]->eikenband_2 = null;
        $a->eikenArray[0]->cse_msg_1 = null;
        $a->eikenArray[0]->cse_msg_2 = null;
        
        $expectResult = 1;
        
        $mappingEikenResult = new MappingEikenResultService($this->getApplicationServiceLocator());
        $eikenResultMock = $this->getMockBuilder('Application\Entity\Repository\EikenTestResultRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $eikenApplyMock = $this->getMockBuilder('Application\Entity\Repository\ApplyEikenOrgRepository')
                ->disableOriginalConstructor()
                ->getMock();
        $eikenScoreMock = $this->getMockBuilder('Application\Entity\Repository\EikenScoreRepository')
                ->disableOriginalConstructor()
                ->getMock();
        // Set mock for EikenList
        $listEikenId = array(
            '0' => array('id' => 1),
            '1' => array('id' => 2),
            '2' => array('id' => 3),
        );
        $eikenResultMock->expects($this->any())        
                ->method('getListIdEikenTestResult')        
                ->will($this->returnValue($listEikenId));
        $mappingEikenResult->setEikenList($eikenResultMock);
        
        // Set mock for delete Eiken Score
        $delScore = 1;
        $eikenScoreMock->expects($this->any())
                ->method('deleteEikenScore')
                ->will($this->returnValue($delScore));
        $mappingEikenResult->deleteEikenScore($eikenScoreMock);
        
        // Set mock for delete Eiken Result
        $delResults = 1;
        $eikenResultMock->expects($this->any())
                ->method('deleteEikenTestResult')
                ->will($this->returnValue($delResults));
        $mappingEikenResult->deleteEikenResult($eikenResultMock);
        
        // Set mock for update Import Status
        $updateImport = 1;
        $eikenApplyMock->expects($this->any())
                ->method('updateStatusAndTotalImporting')
                ->will($this->returnValue($updateImport));
        $mappingEikenResult->updateImportStatus($eikenApplyMock);
        
        // Set mock for check Import Status
        $checkImport = true;
        $eikenResultMock->expects($this->any())
                ->method('findBy')
                ->will($this->returnValue($checkImport));
        $mappingEikenResult->checkImportStatus($eikenResultMock);
        
        // Set mock for update Mapping Status
        $updateMapping = 1;
        $eikenApplyMock->expects($this->any())
                ->method('updateStatusMapping')
                ->will($this->returnValue($updateMapping));
        $mappingEikenResult->updateMappingStatus($eikenApplyMock);
        
        $result = $mappingEikenResult->saveEikenExamResult($a, "90010100", 18, 1, 2015, 1);
        $this->assertEquals($result['status'], $expectResult);
        $this->deleteTestResult();
    }
    
    public function deleteTestResult()
    {
        $em = $this->getEntityManager();
        $testResult =  $em->getRepository('Application\Entity\EikenTestResult')
                ->findOneBy(array('organizationNo' => '90010100', 'nameRomanji' => 'EIKENRESULTTEST'));
        $em->remove($testResult);
        $em->flush();
    }
    
    private function setSessionData()
    {
        PrivateSession::setData('examId', 1);
        PrivateSession::setData('yearNo', date('Y'));
        PrivateSession::setData('kaiNo', 1);
    }
    
    public function testUiShowCorrectTitleOfConfirmImportEikenScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->dispatch('/history/eiken/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/英検取込結果確認/');
        
    }
    public function testUiShowCorrectDescriptionOfConfirmImportEikenScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->dispatch('/history/eiken/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/英検の取込結果一覧となります。/');
    }
    public function testUiShowCorrectOrganizationClassOnTableOfConfirmImportEikenScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->dispatch('/history/eiken/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/学校区分/');
    }
    public function testUiShowCorrectSchoolyearOnTableOfConfirmImportEikenScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->dispatch('/history/eiken/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/学年/');
    }
    public function testUiShowCorrectClassOnTableOfConfirmImportEikenScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->dispatch('/history/eiken/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/クラス/');
    }
    public function testUiShowCorrectNameOnTableOfConfirmImportEikenScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->dispatch('/history/eiken/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/氏名（漢字）/');
    }
    public function testUiShowCorrectEikenLevelOnTableOfConfirmImportEikenScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->dispatch('/history/eiken/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/受験級/');
    }
    public function testUiShowCorrectFirstEikenTotalOnTableOfConfirmImportEikenScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->dispatch('/history/eiken/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/一次得点合計/');
    }
    public function testUiShowCorrectFirstPassFailOnTableOfConfirmImportEikenScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->dispatch('/history/eiken/confirm-exam-result');
      
        $this->assertQueryContentRegex('body', '/一次合否結果/');
    }
    public function testUiShowCorrectSecondEikenTotalOnTableOfConfirmImportEikenScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->dispatch('/history/eiken/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/ニ次得点合計/');
    }
    public function testUiShowCorrectSecondPassFailOnTableOfConfirmImportEikenScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->dispatch('/history/eiken/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/二次合否結果/');
    }
}