<?php
/*
 * @Author Huy Manh(manhnh5)
 */
namespace Satellite;

use Dantai\PrivateSession;
use Satellite\Service\PaymentEikenExamService;
use Satellite\Constants;

class PaymentEikenExamFunctionSeviceTest extends \Dantai\Test\AbstractHttpControllerTestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->setApplicationConfig(
            include APP_DIR . '/config/satellite.config.php'
        );
    }

    public function loginFake()
    {
        PrivateSession::setData(Constants::SESSION_SATELLITE, $this->getUserIdentity());
    }

    public function testCheckResultEnablePaymentByCreditFlag()
    {
        $this->assertTrue($this->isPaymentByCreditFlag());
    }

    public function testResultFunctionMappingKyu()
    {
        $eikenLevelPrice = Array
        (
            1 => Array
            (
                'price' => 8400,
                'name'  => '1級'
            ),
            2 => Array
            (
                'price' => 6900,
                'name'  => '準1級'
            ),
            3 => Array
            (
                'price' => 5000,
                'name'  => '2級'
            ),
            4 => Array
            (
                'price' => 4500,
                'name'  => '準2級'
            ),
            5 => Array
            (
                'price' => 2800,
                'name'  => '3級'
            ),
            6 => Array
            (
                'price' => 2100,
                'name'  => '4級'
            ),
            7 => Array
            (
                'price' => 2000,
                'name'  => '5級'
            )
        );
        $examDate = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->findOneBy(array('year' => 2015, 'kai' => 2));
        $this->assertNotNull($examDate);
        $listEikenLevel = '["1", "4", "5", "6", "7"]';
        $hallTypeExamDay = 4;
        $paymentExamService = new PaymentEikenExamService($this->getApplicationServiceLocator());
        $actual = $paymentExamService->mappingKyu($eikenLevelPrice, $examDate, $listEikenLevel, $hallTypeExamDay);
        $expected = Array
        (
            1 => Array
            (
                'priceName' => '8,400円',
                'price'     => 8400,
                'name'      => '1級',
                'examDate'  => '10月11日（日）'
            ),
            4 => Array
            (
                'priceName' => '4,500円',
                'price'     => 4500,
                'name'      => '準2級',
                'examDate'  => '10月30日（金）、10月31日（土）'
            ),
            5 => Array
            (
                'priceName' => '2,800円',
                'price'     => 2800,
                'name'      => '3級',
                'examDate'  => '10月30日（金）、10月31日（土）'
            ),
            6 => Array
            (
                'priceName' => '2,100円',
                'price'     => 2100,
                'name'      => '4級',
                'examDate'  => '10月30日（金）、10月31日（土）'
            ),
            7 => Array
            (
                'priceName' => '2,000円',
                'price'     => 2000,
                'name'      => '5級',
                'examDate'  => '10月30日（金）、10月31日（土）'
            )
        );
        foreach ($expected as $key => $val) {
            $this->assertNotNull($actual[$key]['price']);
            $this->assertEquals($actual[$key]['price'], $val['price']);
        }
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
                'deadlineForm'    => new \DateTime(2015 - 10 - 4),
                'deadlineTo'      => new \DateTime(2015 - 11 - 30),
                'combiniDeadline' => new \DateTime(2015 - 11 - 30),
                'creditDeadline'  => new \DateTime(2015 - 11 - 30),
            ),
            'doubleEiken'        => 3,
            'paymentInformation' => 1,
            'firstNameKanji' => 'firstNameKanji',
            'lastNameKanji' => 'lastNameKanji',
        );

        return $loginSession;
    }

}
