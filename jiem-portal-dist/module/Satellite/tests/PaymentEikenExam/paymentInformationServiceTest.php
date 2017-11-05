<?php
/*
 * @Author Huy Manh(manhnh5)
 */
namespace Satellite;

use Application\Entity\EikenSchedule;
use Dantai\PrivateSession;
use Satellite\Service\PaymentEikenExamService;

class PaymentInformationServiceTest extends \Dantai\Test\AbstractHttpControllerTestCase
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

    public function testFunctionGetPaymentInformation()
    {
        $this->loginFake();
        $user = PrivateSession::getData(Constants::SESSION_SATELLITE);
        $paymentExamService = new PaymentEikenExamService($this->getApplicationServiceLocator());
        $paymentInfo = $paymentExamService->getPaymentInformation(PrivateSession::getData(Constants::SESSION_SATELLITE));
        $applyEikenLevel = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenLevel')->findBy(array('pupilId' => $user['pupilId'], 'eikenScheduleId' => $user['eikenScheduleId'], 'paymentStatus' => Constants::PAYMENT_STATUS_SUCCESS, 'isDelete' => 0), array('eikenLevelId' => 'ASC'));
        foreach ($applyEikenLevel as $eikenLevelId => $val) {
            $this->assertEquals($val['priceName'], $paymentInfo[$eikenLevelId]['priceName']);
            $this->assertEquals($val['price'], $paymentInfo[$eikenLevelId]['price']);
            $this->assertEquals($val['name'], $paymentInfo[$eikenLevelId]['name']);
            $this->assertEquals($val['examDate'], $paymentInfo[$eikenLevelId]['examDate']);
        }
    }

    public function testGetTotalKyuPaymentInformation()
    {
        $this->loginFake();
        $paymentExamService = new PaymentEikenExamService($this->getApplicationServiceLocator());
        $user = PrivateSession::getData(Constants::SESSION_SATELLITE);
        $eikenSchedule = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->findOneBy(array('year' => 2015, 'kai' => 3));
        $this->assertNotNull($eikenSchedule);
        $totalKyu = $paymentExamService->paymentInformationStatus($user['pupilId'], $eikenSchedule->getId());
        $applyEikenLevel = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenLevel')->findBy(array('pupilId' => $user['pupilId'], 'eikenScheduleId' => $eikenSchedule->getId(), 'paymentStatus' => Constants::PAYMENT_STATUS_SUCCESS, 'isDelete' => 0), array('eikenLevelId' => 'ASC'));
        $this->assertEquals($totalKyu, count($applyEikenLevel));
    }

    public function getUserIdentity()
    {
        /** @var EikenSchedule $eikenSchedule */
        $eikenSchedule = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->findOneBy(array('year' => 2015, 'kai' => 3));
        $this->assertNotNull($eikenSchedule);
        $applyEikenLevel = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenLevel')->findBy(array('pupilId' => 4, 'eikenScheduleId' => $eikenSchedule->getId(), 'paymentStatus' => Constants::PAYMENT_STATUS_SUCCESS, 'isDelete' => 0), array('eikenLevelId' => 'ASC'));
        $loginSession = Array
        (
            'paymentType'        => 0,
            'organizationNo'     => 10080300,
            'personalPayment'    => '["0","1"]',
            'hallType'           => 1,
            'beneficiary'        => null,
            'organizationPayment' => 1,
            'listEikenLevel'     => '["1","2","3","4","5","6","7"]',
            'eikenScheduleId'    => $eikenSchedule->getId(),
            'hallTypeExamDay'    => 4,
            'pupilId'            => 4,
            'deadline'           => Array
            (
                'id'              => 2,
                'kai'             => 3,
                'year'            => date('Y') + 1,
                'deadlineForm'    => $eikenSchedule->getDeadlineFrom(),
                'deadlineTo'      => $eikenSchedule->getSatelliteSiteDeadline(),
                'combiniDeadline' => $eikenSchedule->getCombiniDeadline(),
                'creditDeadline'  => $eikenSchedule->getCreditCardDeadline(),
            ),
            'doubleEiken'        => 3,
            'paymentInformation' => count($applyEikenLevel),
            'firstNameKanji' => 'firstNameKanji',
            'lastNameKanji' => 'lastNameKanji',
        );

        return $loginSession;
    }

}
