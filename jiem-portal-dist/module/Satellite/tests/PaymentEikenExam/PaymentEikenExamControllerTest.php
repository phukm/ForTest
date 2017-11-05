<?php
/*
 * @Author Huy Manh(manhnh5)
 */
namespace Satellite;

use Dantai\PrivateSession;
use Satellite\Service\PaymentEikenExamService;
use Satellite\Constants;

class PaymentEikenExamControllerTest extends \Dantai\Test\AbstractHttpControllerTestCase
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

    public function testCheckResultEnablePaymentByCreditFlag()
    {
        $this->assertTrue($this->isPaymentByCreditFlag());
    }

    public function testResultPaymentConfirmActionValidPost()
    {
        $this->loginFake();
        $postData = Array(
            'cardFirstName' => 'テス',
            'cardLastName'  => 'テス',
            'cardNumber'    => '1111111111111111',
            'cardMonth'     => 12,
            'cardYear'      => 18,
            'cardCvv'       => 111,
            'chooseKyu'     => Array(
                0 => 5,
                1 => 6
            )
        );
        $kyu = Array
        (
            5 => Array
            (
                'priceName' => '2,800円',
                'price'     => 2800,
                'name'      => '3級',
                'examDate'  => '10月30日 （金）、10月31日 （土）',
                'examDateRound2' => '一次試験 : 2月22日（日）'
            ),
            6 => Array
            (
                'priceName' => '1,600円',
                'price'     => 1600,
                'name'      => '4級',
                'examDate'  => '10月30日 （金）、10月31日 （土）',
                'examDateRound2' => '一次試験 : 2月22日（日）'
            )
        );
        $privateSession = new privateSession();
        $privateSession->setData(Constants::LIST_KYU_PRICE, $kyu);
        $this->dispatch('/payment-eiken-exam/payment-confirm', 'POST', $postData);
        $this->assertQueryContentRegex('div#credit', '/' . $postData['cardFirstName'] . $postData['cardLastName'] . '/');
        $this->assertQueryContentRegex('div#credit', '/' . $postData['cardNumber'] . '/');
        $this->assertQueryContentRegex('div#credit', '/' . $postData['cardMonth'] . '/');
        $this->assertQueryContentRegex('div#credit', '/' . $postData['cardYear'] . '/');
        $this->assertQueryContentRegex('div#credit', '/' . $postData['cardCvv'] . '/');
        $this->assertQueryContentRegex('div#credit', '/' . $postData['cardCvv'] . '/');
    }

    public function testPaymentConfirmActionValidPostFault()
    {
        $this->loginFake();
        $postData = Array(
            'cardFirstName' => '',
            'cardLastName'  => '',
            'cardNumber'    => '',
            'cardMonth'     => 12,
            'cardYear'      => 18,
            'cardCvv'       => 1111,
            'chooseKyu'     => Array(
                0 => 5,
                1 => 6
            )
        );
        $kyu = Array
        (
            5 => Array
            (
                'priceName' => '2,800円',
                'price'     => 2800,
                'name'      => '3級',
                'examDate'  => '10月30日 （金）、10月31日 （土）'
            ),
            6 => Array
            (
                'priceName' => '1,600円',
                'price'     => 1600,
                'name'      => '4級',
                'examDate'  => '10月30日 （金）、10月31日 （土）'
            )
        );
        $privateSession = new privateSession();
        $privateSession->setData(Constants::LIST_KYU_PRICE, $kyu);
        $this->dispatch('/payment-eiken-exam/payment-confirm', 'POST', $postData);
        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/payment-eiken-exam/pay-by-credit');
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

}
