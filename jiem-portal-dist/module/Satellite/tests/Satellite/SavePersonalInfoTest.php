<?php
namespace Satellite;

use Application\Entity\EikenLevel;
use Application\Entity\InvitationSetting;
use Dantai\PrivateSession;
use Satellite\Constants;
use Satellite\Service\EikenService;

class SavePersonalInfoTest extends \Dantai\Test\AbstractHttpControllerTestCase
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
    private $data = array(
        'chooseKyu' => array(0 => '3'),
        'txtPhoneNo1' => '',
        'txtPhoneNo2' => '',
        'txtPhoneNo3' => ''
    );

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
        PrivateSession::setData(Constants::SESSION_APPLYEIKEN, $this->getApplyEikenSession());
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

    public function getApplyEikenSession(){
        $dataExemptionSession = array(
            "exemption"=>"1",
            "chooseKyu"=>array(0=>"3"),
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
                '3' => array(
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

    public function translate($msgKey){
        return $this->getApplicationServiceLocator()->get('MVCTranslator')->translate($msgKey);
    }
    
    public function getInvitationMock($dataInv = array())
    {
        $invMock = $this->getMockBuilder('Application\Entity\Repository\InvitationSettingRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $invMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($dataInv));
        return $invMock;
    }
    
    public function getApplyMock($dataApply = array())
    {
        $applyMock = $this->getMockBuilder('Application\Entity\Repository\ApplyEikenLevelRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $applyMock->expects($this->any())
                ->method('findBy')
                ->will($this->returnValue($dataApply));
        return $applyMock;
    }

    public function mock(){
        $eikenServiceMock = $this->getMockBuilder('\Satellite\Service\EikenService')
            ->setMethods(array('getApplyEikenLevels'))
            ->getMock();
        $applyEikenLevel = new \Application\Entity\ApplyEikenLevel();
        $applyEikenLevel->setId(1);
        $applyEikenLevel->setHallType(0);
        $eikenLevel = new EikenLevel();
        $eikenLevel->setId(1);
        $applyEikenLevel->setEikenLevel($eikenLevel);
        $eikenServiceMock->expects($this->any())
            ->method('getApplyEikenLevels')
            ->will($this->returnValue($applyEikenLevel));

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Satellite\Service\EikenServiceInterface', $eikenServiceMock);
    }

    public function testShowMessageWhenCanNotDoubleEiken() {
        $this->loginFake();
        $this->mock();
        PrivateSession::setData(Constants::DATA_TEST_SITE_EXEMPTION, array());
        PrivateSession::setData(Constants::SESSION_SATELLITE, $this->getUserIdentity());
        /** @var EikenService $eikenService */
        $eikenService = $this->getApplicationServiceLocator()->get('Satellite\Service\EikenServiceInterface');
        $eikenService->setServiceLocator($this->getApplicationServiceLocator());
        $result = $eikenService->savePersonalInfo(1, 1, $this->getUserIdentity());
        
        $this->assertResponseStatusCode(200);
        $this->assertEquals($result['status'], 0);
        $this->assertEquals($result['message'], $this->translate('geKyuPaymentMSG56'));
    }
}