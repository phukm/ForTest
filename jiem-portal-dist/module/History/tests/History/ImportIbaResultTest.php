<?php
namespace History;
use History\Service\MappingIbaResultService;
use Dantai\PrivateSession;
use stdClass;

class ImportIbaResultTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    
    protected $expectKekka = 10;

    private $data = '{"eikenArray":[{"password":"246663","kekka":"10","nendo":"2015","uketsukeno":"275049","eikenid":"15810043963","gakunen":"3","jisshiid":"990310000","kakuteiseq":"1","jisshikanrino":"201599903100000101","groupno":"99903100","torikomino":"20150004734","testsyubetsu":"B","testsetno":"01","listeningumu":"1","id_alphabet":"  ","id_number":"            ","seibetsu":"2","shimeiroma":"EIKEN SHINO              ","shimeikana":"ｴｲｹﾝ ｼﾉ            ","kojinzokusei":"2","shimeikanji":null,"class1":"02","syussekino":"15","birthdate":"H090727","score_total":"1119","score_read":"555","score_listen":"564","old_score_total":null,"old_score_read":null,"old_score_listen":null,"rank_total":"31","rank_read":"24","rank_listen":"30","jukensyasu":"32","quessu_goi":"20","quessu_kosei":"0","quessu_dokkai":"15","quessu_listen":"30","quessu_total":"65","seitosu_goi":"5","seitosu_kosei":null,"seitosu_dokkai":"9","seitosu_listen":"9","seitosu_total":"23","seitoritu_goi":"25","seitoritu_kosei":null,"seitoritu_dokkai":"60","seitoritu_listen":"30","seitoritu_total":"35.399999999999999","eikenkyu":"5","toeic":"   ","toefl":"   ","toeic_bridge":"   ","avescore_total":"1167","avescore_read":"571","avescore_listen":"597","old_avescore_total":null,"old_avescore_read":null,"old_avescore_listen":null,"aveseitoritu_goi":"40.799999999999997","aveseitoritu_kosei":null,"aveseitoritu_dokkai":"60.399999999999999","aveseitoritu_listen":"52.399999999999999","aveseitoritu_total":"50.700000000000003","testdate":"2015/05/20","shoridate":"2015/05/25","sinkyukbn":"1","syukeitargetflg":"1","eikenkyulv_total":"３級レベルの力があります。","eikenkyulv_read":"準２級レベルまであと一歩です。","eikenkyulv_listen":"準２級レベルまであと一歩です。","seiseki_jun":"02","eikenlevel":"01","junihyoji":"02","junihyojiseigen":null,"hyodaihenko":"01","hyodai":null,"eikenidhyoji":"01","createdate":"2015/05/25 14:05:54","updatedate":"2015/05/25 14:05:54","answer01":"01","answer02":"04","answer03":"03","answer04":"02","answer05":"01","answer06":"04","answer07":"02","answer08":"01","answer09":"02","answer10":"01","answer11":"02","answer12":"02","answer13":"04","answer14":"03","answer15":"04","answer16":"04","answer17":"01","answer18":"02","answer19":"02","answer20":"02","answer21":"02","answer22":"03","answer23":"02","answer24":"01","answer25":"01","answer26":"03","answer27":"03","answer28":"01","answer29":"01","answer30":"03","answer31":"02","answer32":"01","answer33":"03","answer34":"02","answer35":"03","answer36":"01","answer37":"04","answer38":"03","answer39":"02","answer40":"02","answer41":"03","answer42":"04","answer43":"01","answer44":"01","answer45":"01","answer46":"03","answer47":"04","answer48":"02","answer49":"03","answer50":"01","answer51":"02","answer52":"01","answer53":"  ","answer54":"04","answer55":"02","answer56":"03","answer57":"03","answer58":"04","answer59":"03","answer60":"01","answer61":"01","answer62":"04","answer63":"03","answer64":"01","answer65":"03","answer66":"  ","answer67":"  ","answer68":"  ","answer69":"  ","answer70":"  ","answer71":"  ","answer72":"  ","answer73":"  ","answer74":"  ","answer75":"  ","answer76":"  ","answer77":"  ","answer78":"  ","answer79":"  ","answer80":"  ","seigojudge01":"O","seigojudge02":"E","seigojudge03":"E","seigojudge04":"O","seigojudge05":"O","seigojudge06":"E","seigojudge07":"E","seigojudge08":"E","seigojudge09":"O","seigojudge10":"E","seigojudge11":"E","seigojudge12":"E","seigojudge13":"E","seigojudge14":"E","seigojudge15":"E","seigojudge16":"E","seigojudge17":"E","seigojudge18":"E","seigojudge19":"E","seigojudge20":"O","seigojudge21":"O","seigojudge22":"O","seigojudge23":"O","seigojudge24":"E","seigojudge25":"O","seigojudge26":"O","seigojudge27":"O","seigojudge28":"O","seigojudge29":"O","seigojudge30":"E","seigojudge31":"E","seigojudge32":"E","seigojudge33":"E","seigojudge34":"E","seigojudge35":"O","seigojudge36":"E","seigojudge37":"E","seigojudge38":"E","seigojudge39":"E","seigojudge40":"O","seigojudge41":"E","seigojudge42":"O","seigojudge43":"O","seigojudge44":"E","seigojudge45":"E","seigojudge46":"O","seigojudge47":"E","seigojudge48":"E","seigojudge49":"E","seigojudge50":"O","seigojudge51":"E","seigojudge52":"O","seigojudge53":"N","seigojudge54":"E","seigojudge55":"O","seigojudge56":"E","seigojudge57":"E","seigojudge58":"E","seigojudge59":"E","seigojudge60":"E","seigojudge61":"O","seigojudge62":"E","seigojudge63":"E","seigojudge64":"E","seigojudge65":"O","seigojudge66":" ","seigojudge67":" ","seigojudge68":" ","seigojudge69":" ","seigojudge70":" ","seigojudge71":" ","seigojudge72":" ","seigojudge73":" ","seigojudge74":" ","seigojudge75":" ","seigojudge76":" ","seigojudge77":" ","seigojudge78":" ","seigojudge79":" ","seigojudge80":" "}]}';

    public function testCorrectKekkaWhenGetIbaDataFromApi()
    {
        $this->login();
        $mappingIbaResult = new MappingIbaResultService($this->getApplicationServiceLocator());
        $uketukeClientMock = $this->getMockBuilder('\Dantai\Api\UkestukeClient')->getMock();
        $ibaApiResult = \Zend\Json\Json::decode('{"eikenArray": [{"password":"123456", "kekka":"10", "nendo":"2015", "dantaino":"10566000", "jisshiId":"666666", "examType" : "01"}]}');
        $uketukeClientMock->expects($this->any())        
                ->method('callEir2c02')        
                ->will($this->returnValue($ibaApiResult));
        $mappingIbaResult->setUketukeClient($uketukeClientMock);
        $result = $mappingIbaResult->getIBAExamResult("10566000", "666666");
        $this->assertEquals($result->eikenArray[0]->kekka, $this->expectKekka);
    }
    
    private function setSessionData()
    {
        PrivateSession::setData('jisshiId', 656565);
        PrivateSession::setData('examType', '01');
        PrivateSession::setData('examDate', "2015-12-26 00:00:00");
        PrivateSession::setData('yearNo', date('Y'));
        PrivateSession::setData('kaiNo', 1);
    }

    public function mockMappingIbaService(){
        $mappingIbaServiceMock = $this->getMockBuilder('\History\Service\MappingIbaResultService')
            ->disableOriginalConstructor()
            ->getMock();
        $ibaApiResult = \Zend\Json\Json::decode($this->data);
        $mappingIbaServiceMock->expects($this->any())
            ->method('getIBAExamResult')
            ->will($this->returnValue($ibaApiResult));

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('History\Service\MappingIbaResultService', $mappingIbaServiceMock);
    }

    public function testUiShowCorrectTitleOfConfirmImportIbaScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->mockMappingIbaService();
        $this->dispatch('/history/iba/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/英検IBA取込結果確認/');
    }
    public function testUiShowCorrectDescriptionOfConfirmImportIbaScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->mockMappingIbaService();
        $this->dispatch('/history/iba/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/英検IBAの取込結果一覧となります。/');
    }
    public function testUiShowCorrectSchoolyearOnTableOfConfirmImportIbaScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->mockMappingIbaService();
        $this->dispatch('/history/iba/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/学年/');
    }
    public function testUiShowCorrectClassOnTableOfConfirmImportIbaScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->mockMappingIbaService();
        $this->dispatch('/history/iba/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/クラス/');
    }
    public function testUiShowCorrectAttendanceNumberOnTableOfConfirmImportIbaScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->mockMappingIbaService();
        $this->dispatch('/history/iba/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/番号/');
    }
    public function testUiShowCorrectNameKanaOnTableOfConfirmImportIbaScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->mockMappingIbaService();
        $this->dispatch('/history/iba/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/氏名（カナ）/');
    }
    public function testUiShowCorrectTestTypeOnTableOfConfirmImportIbaScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->mockMappingIbaService();
        $this->dispatch('/history/iba/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/テスト種別/');
    }
    public function testUiShowCorrectScoreTotalOnTableOfConfirmImportIbaScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->mockMappingIbaService();
        $this->dispatch('/history/iba/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/総合/');
    }
    public function testUiShowCorrectScoreReadOnTableOfConfirmImportIbaScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->mockMappingIbaService();
        $this->dispatch('/history/iba/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/リーディング/');
    }
    public function testUiShowCorrectEikenLevelRateOnTableOfConfirmImportIbaScreen()
    {
        $this->login();
        $this->setSessionData();
        $this->mockMappingIbaService();
        $this->dispatch('/history/iba/confirm-exam-result');
        $this->assertQueryContentRegex('body', '/英検級レベル判定/');
    }
}