<?php
namespace Satellite\Eiken;

use Dantai\PrivateSession;
use Satellite\Constants;

class TestSiteExemptionTest extends \Dantai\Test\AbstractHttpControllerTestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->setApplicationConfig(
            include APP_DIR . '/config/satellite.config.php'
        );
    }

    private $organizationNo = '55D71F4';// Decimal to Hexadecimal Converter 90010100
    private $authenKey = '12345678';

    public function login()
    {
        $this->dispatch('/login', \Zend\Http\Request::METHOD_POST, array(
                'organizationNo' => $this->organizationNo,
                'authenKey'      => $this->authenKey)
        );
        $this->assertRedirectTo('/');
    }

    public function loginFake()
    {
        PrivateSession::setData('satellite', $this->getUserIdentity());
        PrivateSession::setData(Constants::SESSION_APPLYEIKEN, $this->getDataExemptionSession());
    }

    
    public function isPaymentByCreditFlag()
    {
        $this->loginFake();
        $user = $this->getUserIdentity();
        $personalPayment = !empty($user['personalPayment']) ? json_decode($user['personalPayment']) : null;
        if ($user['paymentType'] == Constants::PAYMENT_TYPE && is_array($personalPayment) && (int)current($personalPayment) == Constants::PERSONAL_PAYMENT) {
            return true;
        }

        return false;
    }

    public function getUserIdentity()
    {
        $deadlineForm = new \DateTime();
        $deadlineForm->setDate(date('Y') - 1, date('m'), 10);
        $satelliteDeadline = new \DateTime();
        $satelliteDeadline->setDate(date('Y') + 1, date('m'), 30);
        $combiniDeadline = new \DateTime();
        $combiniDeadline->setDate(date('Y') + 1, date('m'), 30);
        $creditDeadline = new \DateTime();
        $creditDeadline->setDate(date('Y') + 1, date('m'), 30);
        $loginSession = Array
        (
            'organizationNo'     => 90010100,
            'organizationId'     => 1,
            'paymentType'        => 0,
            'personalPayment'    => '["0","1"]',
            'hallType'           => 1,
            'beneficiary'        => null,
            'organizationPayment' => 1,
            'listEikenLevel'     => '["1","2","3","4","5","6","7"]',
            'eikenScheduleId'    => 2,
            'hallTypeExamDay'    => 4,
            'pupilId'            => 4,
            'deadline'           => Array
            (
                'id'           => 2,
                'kai'          => 2,
                'year'         => 2018,
                'deadlineForm'    => $deadlineForm,
                'deadlineTo'      => $satelliteDeadline,
                'combiniDeadline' => $combiniDeadline,
                'creditDeadline'  => $creditDeadline,
            ),
            'doubleEiken'        => 3,
            'paymentInformation' => 1,
            'firstNameKanji' => 'firstNameKanji',
            'lastNameKanji' => 'lastNameKanji',
        );

        return $loginSession;
    }
    
    public function getDataExemptionSession(){
        $dataExemptionSession = array(
            "exemption"=>"1",
            "chooseKyu"=>array(0=>"2"),
            "hallType"=>"1",
            "txtFirstNameKanji"=>"は必須",
            "txtLastNameKanji"=>"項目です",
            "txtFirstNameKana"=>"カナ",
            "txtLastNameKana"=>"カナ",
            "rdSex"=>"1",
            "ddlYear"=>"2015",
            "ddlMonth"=>"8",
            "ddlDay"=>"5",
            "txtPostalCode1"=>"060",
            "txtPostalCode2"=>"0042",
            "ddlCity"=>"1",
            "txtDistrict"=>"札幌市中央区",
            "txtTown"=>"大通西（１～１９丁目）",
            "txtPhoneNo1"=>"1234",
            "txtPhoneNo2"=>"435",
            "txtPhoneNo3"=>"342",
            "txtEmail"=>"asds@fpt.com",
            "ddlJobName"=>"3",
            "kyu" => array(
                '2' => array(
                    "price" => "3200",
                    "priceName" => "3,200円",
                    "examDate" => "4月24日（日）",
                    "name" => "準1級",
                    "hallType" => "1",
                )
            )
        );
        return $dataExemptionSession;
    }

    public function testTestSiteExemptionUiTestTitle() {
        $this->loginFake();
        $this->dispatch('/eiken/test-site-exemption');
        $this->assertResponseStatusCode(200);
        $this->assertQueryContentContains('body', '英検申し込み');
    }
    
    public function testTestSiteExemptionUiTestLabel() {
        $this->loginFake();
        $this->dispatch('/eiken/test-site-exemption');
        $this->assertResponseStatusCode(200);
        
        $this->assertQueryContentContains('table td', '一免情報登録');
        $this->assertQueryContentContains('table td', '一次試験免除');
        $this->assertQueryContentContains('table td', '一次試験合格年度回');
        $this->assertQueryContentContains('table td', '一次試験合格時 受験地名');
        $this->assertQueryContentContains('#test-side-exemption td', '一次試験合格時個人番号');
        
        $this->assertQueryContentContains('table td', '受験地選択');
        $this->assertQueryContentContains('table td', '受験希望地域(一次試験受験)');
        $this->assertQueryContentContains('table td', '希望受験地(一次試験受験)');
        $this->assertQueryContentContains('table td', '受験希望地域(二次試験受験)');
        $this->assertQueryContentContains('table td', '希望受験地 (二次試験受験)');
    }
    
    public function testTestSiteExemptionUiTestButton() {
        $this->loginFake();
        $this->dispatch('/eiken/test-site-exemption');
        $this->assertResponseStatusCode(200);
        $this->assertQueryContentContains('.box-footer', '戻る');
        $this->assertQueryContentContains('.box-footer', 'キャンセル');
        $this->assertQueryContentContains('.box-footer', '次へ');
    }
}
