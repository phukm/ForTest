<?php
namespace Satellite;

use Dantai\PrivateSession;
use Satellite\Constants;

class TestPaymentInfomationUITest extends \Dantai\Test\AbstractHttpControllerTestCase
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
                'id'              => 2,
                'kai'             => 2,
                'year'            => 2018,
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
    
    public function testTitlePage() {
        $this->loginFake();
        $this->dispatch('/payment-eiken-exam/payment-infomation');
        $this->assertResponseStatusCode(200);
        $this->assertQueryContentRegex('body', '/申し込み情報/');
    }
    public function testDecriptionPage() {
        $this->loginFake();
        $this->dispatch('/payment-eiken-exam/payment-infomation');
        $this->assertResponseStatusCode(200);
        $this->assertQueryContentRegex('body', '/以下は申し込み済み級の一覧です。/');
    }
    public function testTitlePaymentStatusInlistData() {
        $this->loginFake();
        $this->dispatch('/payment-eiken-exam/payment-infomation');
        $this->assertResponseStatusCode(200);
        $this->assertQueryContentRegex('body', '/支払状況/');
    }
    public function testTitleACtionInlistData() {
        $this->loginFake();
        $this->dispatch('/payment-eiken-exam/payment-infomation');
        $this->assertResponseStatusCode(200);
        $this->assertQueryContentRegex('body', '/操作/');
    }
}
