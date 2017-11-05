<?php
namespace Satellite\Eiken;
use Dantai\PrivateSession;
use Dantai\Test\AbstractHttpControllerTestCase;
use Satellite\Constants;
use Satellite\Service\EikenService;
use Zend\Di\ServiceLocator;

class EikenServiceTest extends AbstractHttpControllerTestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->setApplicationConfig(
            include APP_DIR . '/config/satellite.config.php'
        );
    }

    private $examGrade = array(
        '1級' => 1,
        '準1級' => 2,
        '2級' => 3,
        '準2級' => 4,
        '3級' => 5,
        '4級' => 6,
        '5級' => 7
    );
    
    public function testFunctionValidateTestSideExemptionSuccess() {
        $data = array(
            'examGrade1' => $this->examGrade['1級'],
            'exemption1' => 0,
            'passedKai1' => 4,
            'passedExamPlace1' => '室蘭市周辺',
            'personalId1' => '1234567',
            'firstTestCity1' => '北海道',
            'firstExamPlace1' => '室蘭市周辺',
            'secondTestCity1' => '北海道',
            'secondExamPlace1' => '室蘭市周辺'
        );
        
        $data2 = array(
            'examGrade2' => $this->examGrade['準1級'],
            'exemption2' => 0,
            'passedKai2' => 4,
            'passedExamPlace2' => '室蘭市周辺',
            'personalId2' => '1234567',
            'firstTestCity2' => '北海道',
            'firstExamPlace2' => '室蘭市周辺',
            'secondTestCity2' => '北海道',
            'secondExamPlace2' => '室蘭市周辺'
        );
        
        $eikenService = new EikenService();
        $eikenService->setServiceLocator($this->getApplicationServiceLocator());
        
      // examption 1
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(true, empty($result));
        $data['exemption1'] = 1;
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(true, empty($result));
        
        // examption 2
        $data = array_merge($data, $data2);
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(true, empty($result));
        $data['exemption2'] = 1;
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(true, empty($result));
        
    }
    
    public function testFunctionValidateTestSideExemptionRequireWhenExemptionIsNo() {
        $data = array(
            'examGrade1' => $this->examGrade['1級'],
            'exemption1' => 0,
            'firstTestCity1' => '北海道',
            'firstExamPlace1' => '室蘭市周辺',
            'secondTestCity1' => '北海道',
            'secondExamPlace1' => '室蘭市周辺'
        );
        
        $data2 = array(
            'examGrade1' => $this->examGrade['準1級'],
            'exemption2' => 0,
            'firstTestCity2' => '北海道',
            'firstExamPlace2' => '室蘭市周辺',
            'secondTestCity2' => '北海道',
            'secondExamPlace2' => '室蘭市周辺'
        );
        
        $eikenService = new EikenService();
        $eikenService->setServiceLocator($this->getApplicationServiceLocator());
        
        // examption 1
        $data['firstTestCity1'] = null;
        $result = $eikenService->validateTestSideExemption($data);

        $this->assertEquals(false, empty($result));        
        $data['firstTestCity1'] = '';
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));
        
        $data['firstTestCity1'] = '北海道';
        $data['firstExamPlace1'] = null;
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));
        $data['firstExamPlace1'] = '';
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));
        $data['examGrade1'] = null;
        
        
        $data = array_merge($data, $data2);
        // examption 2
        $data['firstTestCity2'] = null;
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));        
        $data['firstTestCity2'] = '';
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));
        
        $data['firstTestCity2'] = '北海道';
        $data['firstExamPlace2'] = null;
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));
        $data['firstExamPlace2'] = '';
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));
    }
    
    public function testFunctionValidateTestSideExemptionRequireWhenExemptionIsYes() {
        $data = array(
            'examGrade1' => $this->examGrade['1級'],
            'exemption1' => 1,
            'secondTestCity1' => '北海道',
            'secondExamPlace1' => '室蘭市周辺',
        );
        
        $data2 = array(
            'examGrade2' => $this->examGrade['準1級'],
            'exemption2' => 1,
            'secondTestCity2' => '北海道',
            'secondExamPlace2' => '室蘭市周辺',
        );
        
        $eikenService = new EikenService();
        $eikenService->setServiceLocator($this->getApplicationServiceLocator());
        
        // examption 1
        $data['secondTestCity1'] = null;
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));        
        $data['secondTestCity1'] = '';
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));
        
        $data['secondTestCity1'] = '北海道';
        $data['secondExamPlace1'] = null;
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));
        $data['secondExamPlace1'] = '';
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));
        $data['examGrade1'] = $this->examGrade['4級'];
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(true, empty($result));
        
        
        $data = array_merge($data, $data2);
        // examption 2
        $data['secondTestCity2'] = null;
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));        
        $data['secondTestCity2'] = '';
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));
        
        $data['secondTestCity2'] = '北海道';
        $data['secondExamPlace2'] = null;
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));
        $data['secondExamPlace2'] = '';
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));
    }
    
    
    public function testFunctionValidateTestSideExemptionWhenExemptionIsYesAndPersonalIdNotDigit() {
        $data = array(
            'examGrade1' => $this->examGrade['1級'],
            'exemption1' => 1,
            'personalId1' => 'abc',
            'secondTestCity1' => '北海道',
            'secondExamPlace1' => '室蘭市周辺'
        );
        
        $data2 = array(
            'examGrade2' => $this->examGrade['準1級'],
            'exemption2' => 1,
            'personalId2' => 'abc',
            'secondTestCity2' => '北海道',
            'secondExamPlace2' => '室蘭市周辺',
        );
        
        $eikenService = new EikenService();
        $eikenService->setServiceLocator($this->getApplicationServiceLocator());
        
        // examption 1
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));
        
        $data = array_merge($data, $data2);
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));      
    }
    
    public function testFunctionValidateTestSideExemptionWhenExemptionIsYesAndPersonalIdTooLong() {
        $data = array(
            'examGrade1' => $this->examGrade['1級'],
            'exemption1' => 1,
            'personalId1' => '12345678', // 7 digit
            'secondTestCity1' => '北海道',
            'secondExamPlace1' => '室蘭市周辺'
        );
        
        $data2 = array(
            'examGrade2' => $this->examGrade['準1級'],
            'exemption2' => 1,
            'personalId2' => '12345678', // 7 digit
            'secondTestCity2' => '北海道',
            'secondExamPlace2' => '室蘭市周辺',
        );
        
        $eikenService = new EikenService();
        $eikenService->setServiceLocator($this->getApplicationServiceLocator());
        
        // exemption 1
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));
        
        $data = array_merge($data, $data2);
        $result = $eikenService->validateTestSideExemption($data);
        $this->assertEquals(false, empty($result));
        
    }

    public function createTempData($testSite)
    {
        PrivateSession::setData(Constants::LIST_KYU_PRICE,array(2 => array('name' => '準１級', 'price' => ''), 3 => array('name' => '２級', 'price' => '')));
        return array(
            'testSite' => $testSite,
            'isSupportCredit' => 0,
            'isSupportConbini' => 0,
            'listPaid' => array(2,3),
            'eikenId' => '12345',
            'eikenPassword' => 'Passw0rd',
            'deadline' => '2017/04/08',
            'combiniDeadline' => '2017/04/08',
            'creditDeadline' => '2017/04/08',
            'chooseKyu' => array(),
            'kyuInfo' => PrivateSession::getData(Constants::LIST_KYU_PRICE)
        );
    }

    public function translate($key) {
        return $this->getApplicationServiceLocator()->get('MVCTranslator')->translate($key);
    }

    public function testFunctionCreateMessageForApplyEikenWhenStandardSiteAndCollective()
    {
        $data = $this->createTempData(0);
        $eikenService = new EikenService();
        $eikenService->setServiceLocator($this->getApplicationServiceLocator());
        $returnData = $eikenService->createResponseMessage($data);

        $expectedMsg = $this->translate('R4_MSG28');
        $this->assertEquals($expectedMsg, $returnData['message']);
    }

    public function testFunctionCreateMessageForApplyEikenWhenStandardSiteAndIndividualAndNotPaidBefore()
    {
        $data = $this->createTempData(0);
        $data['isSupportConbini'] = 1;
        $data['listPaid'] = array();
        $data['chooseKyu'] = array(2,3);
        $eikenService = new EikenService();
        $eikenService->setServiceLocator($this->getApplicationServiceLocator());
        $returnData = $eikenService->createResponseMessage($data);
        $expectedMsg = sprintf($this->translate('msgStandardOnlyCombiniOrCredit'), date('Y/m/d',strtotime($returnData['combiniDeadline'])));
        $this->assertEquals($expectedMsg, $returnData['message']);
    }

    public function testFunctionCreateMessageForApplyEikenWhenStandardSiteAndIndividualAndPaidAllBefore()
    {
        $data = $this->createTempData(0);
        $eikenService = new EikenService();
        $data['isSupportConbini'] = 1;
        $eikenService->setServiceLocator($this->getApplicationServiceLocator());
        $returnData = $eikenService->createResponseMessage($data);

        $expectedMsg1 = $this->translate('R4_MSG28');
        $this->assertEquals($expectedMsg1, $returnData['message']);
        $nameKyu1 = $data['kyuInfo'][$data['listPaid'][0]]['name'];
        $nameKyu2 = $data['kyuInfo'][$data['listPaid'][1]]['name'];
        $expectedMsg2 = sprintf($this->translate('msgInformPaidBoth'), $nameKyu1, $nameKyu2);
        $this->assertEquals($expectedMsg2, $returnData['message2']);

        $data['listPaid'] = array(2);
        $returnData = $eikenService->createResponseMessage($data);
        $expectedMsg1 = $this->translate('R4_MSG28');
        $this->assertEquals($expectedMsg1, $returnData['message']);
        $expectedMsg2 = sprintf($this->translate('msgInformPaidOneKyu'), $nameKyu1);
        $this->assertEquals($expectedMsg2, $returnData['message2']);
    }

    public function testFunctionCreateMessageForApplyEikenWhenStandardSiteAndIndividualAndPaid1Of2()
    {
        $data = $this->createTempData(0);
        $data['isSupportConbini'] = 1;
        $data['listPaid'] = array(2);
        $data['chooseKyu'] = array(3);
        $eikenService = new EikenService();
        $eikenService->setServiceLocator($this->getApplicationServiceLocator());
        $returnData = $eikenService->createResponseMessage($data);

        $expectedMsg1 = $this->translate('R4_MSG28');
        $this->assertEquals($expectedMsg1, $returnData['message']);
        $nameKyu1 = $data['kyuInfo'][$data['listPaid'][0]]['name'];
        $nameKyu2 = $data['kyuInfo'][$data['chooseKyu'][0]]['name'];
        $expectedMsg2 = sprintf($this->translate('msgInformPaidOneKyuOf2Kyu'), $nameKyu1, $nameKyu2);
        $this->assertEquals($expectedMsg2, $returnData['message2']);
    }

    public function testFunctionCreateMessageForApplyEikenWhenMainSiteAndCollective()
    {
        $data = $this->createTempData(1);
        $eikenService = new EikenService();
        $eikenService->setServiceLocator($this->getApplicationServiceLocator());
        $returnData = $eikenService->createResponseMessage($data);

        $expectedMsg = sprintf($this->translate('R4_MSG32'), $data['eikenId'], $data['eikenPassword']);
        $this->assertEquals($expectedMsg, $returnData['message']);
    }

    public function testFunctionCreateMessageForApplyEikenWhenMainSiteAndIndividualAndNotPaidBefore()
    {
        $data = $this->createTempData(1);
        $data['listPaid'] = array();
        $data['chooseKyu'] = array(2,3);
        $eikenService = new EikenService();
        $data['isSupportConbini'] = 1;
        $eikenService->setServiceLocator($this->getApplicationServiceLocator());
        $returnData = $eikenService->createResponseMessage($data);

        $expectedMsg = sprintf($this->translate('msgMainOnlyCombiniOrCredit'), $data['eikenId'], $data['eikenPassword'],  date('Y/m/d',strtotime($returnData['combiniDeadline'])));
        $this->assertEquals($expectedMsg, $returnData['message']);
    }

    public function testFunctionCreateMessageForApplyEikenWhenMainSiteAndIndividualAndPaidAllBefore()
    {
        $data = $this->createTempData(1);
        $eikenService = new EikenService();
        $data['isSupportConbini'] = 1;
        $eikenService->setServiceLocator($this->getApplicationServiceLocator());
        $returnData = $eikenService->createResponseMessage($data);

        $expectedMsg1 = sprintf($this->translate('R4_MSG32'), $data['eikenId'], $data['eikenPassword']);
        $this->assertEquals($expectedMsg1, $returnData['message']);
        $nameKyu1 = $data['kyuInfo'][$data['listPaid'][0]]['name'];
        $nameKyu2 = $data['kyuInfo'][$data['listPaid'][1]]['name'];
        $expectedMsg2 = sprintf($this->translate('msgInformPaidBoth'), $nameKyu1, $nameKyu2);
        $this->assertEquals($expectedMsg2, $returnData['message2']);

        $data['listPaid'] = array(2);
        $returnData = $eikenService->createResponseMessage($data);
        $expectedMsg1 = sprintf($this->translate('R4_MSG32'), $data['eikenId'], $data['eikenPassword']);
        $this->assertEquals($expectedMsg1, $returnData['message']);
        $expectedMsg2 = sprintf($this->translate('msgInformPaidOneKyu'), $nameKyu1);
        $this->assertEquals($expectedMsg2, $returnData['message2']);
    }

    public function testFunctionCreateMessageForApplyEikenWhenMainSiteAndIndividualAndPaid1Of2()
    {
        $data = $this->createTempData(1);
        $data['isSupportConbini'] = 1;
        $data['listPaid'] = array(2);
        $data['chooseKyu'] = array(3);
        $eikenService = new EikenService();
        $eikenService->setServiceLocator($this->getApplicationServiceLocator());
        $returnData = $eikenService->createResponseMessage($data);

        $expectedMsg1 = sprintf($this->translate('R4_MSG32'), $data['eikenId'], $data['eikenPassword']);
        $this->assertEquals($expectedMsg1, $returnData['message']);
        $nameKyu1 = $data['kyuInfo'][$data['listPaid'][0]]['name'];
        $nameKyu2 = $data['kyuInfo'][$data['chooseKyu'][0]]['name'];
        $expectedMsg2 = sprintf($this->translate('msgInformPaidOneKyuOf2Kyu'), $nameKyu1, $nameKyu2);
        $this->assertEquals($expectedMsg2, $returnData['message2']);
    }
    
}
