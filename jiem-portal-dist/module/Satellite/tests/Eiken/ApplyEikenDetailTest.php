<?php
namespace Satellite\Eiken;

use Dantai\PrivateSession;
use Satellite\Constants;

class ApplyEikenDetailTest extends \Dantai\Test\AbstractHttpControllerTestCase
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
            'pupilId'  => 4,
            'deadline' => Array
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

    public function mock(){
        $eikenServiceMock = $this->getMockBuilder('\Satellite\Service\EikenService')
                ->disableOriginalConstructor()
                ->getMock();
        $applyEikenLevel = new \Application\Entity\ApplyEikenLevel();
        $applyEikenLevel->setHallType(0);
        $eikenServiceMock->expects($this->any())
                ->method('getApplyEikenLevel')
                ->will($this->returnValue($applyEikenLevel));
        $eikenServiceMock->expects($this->any())
                ->method('getExamDate')
                ->will($this->returnValue(new \Application\Entity\EikenSchedule()));
        $applyEikenPersonal = new \Application\Entity\ApplyEikenPersonalInfo();
        $applyEikenPersonal->setBirthday(new \DateTime());
        $eikenServiceMock->expects($this->any())
                ->method('getApplyEikenPersonalInfo')
                ->will($this->returnValue($applyEikenPersonal));
        
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Satellite\Service\EikenServiceInterface', $eikenServiceMock);
    }
    
    public function testApplyEikenDetailUiTestTitle() {
        $this->loginFake();
        $this->mock();
        $this->dispatch('/eiken/show/1');
        $this->assertResponseStatusCode(200);
        $this->assertQueryContentContains('body', '英検申し込み');
    }
//    
    public function testExamGradeTableUiTest() {
        $this->loginFake();
        $this->mock();
        $this->dispatch('/eiken/show');
        $this->assertResponseStatusCode(200);
        $this->assertQueryContentContains('#apply-eiken-detail', '受験級（金額)');
        $this->assertQueryContentContains('#apply-eiken-detail td', '級');
        $this->assertQueryContentContains('#apply-eiken-detail td', '受験料');
        $this->assertQueryContentContains('#apply-eiken-detail td', '試験日');
    }
    
    public function testApplyEikenDetailUiTestButton() {
        $this->loginFake();
        $this->mock();
        $this->dispatch('/eiken/show');
        $this->assertResponseStatusCode(200);
        $this->assertQueryContentContains('#apply-eiken-detail .box-footer', '戻る');

    }
}
