<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Satellite;

use Satellite\Service\PaymentEikenExamService;
use Dantai\Utility\CharsetConverter;

class PaymentEikenExamApiServiceTest extends \Dantai\Test\AbstractHttpControllerTestCase
{

    public function getTelNoByMock($organizationId, $organizationNo, $telNoOrg)
    {
        $organization = new \Application\Entity\Organization();
        $organization->setId($organizationId);
        $organization->setTelNo($telNoOrg);
        $organization->setOrganizationNo($organizationNo);
        $orgMock = $this->getMockBuilder('Application\Entity\Repository\OrganizationRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $orgMock->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($organization));
        $paymentExamService = new PaymentEikenExamService($this->getApplicationServiceLocator());
        $paymentExamService->setOrgRepository($orgMock);
        $telNo = $paymentExamService->generateTelNo($organizationNo);

        return $telNo;
    }

    public function testReturnTelNoWhenPutParamToFunctionGenerateOrderId()
    {
        $organizationId = 1000;
        $organizationNo = 90015600;
        $telNoOrg = '456001234999';
        $telNoPayment = $this->getTelNoByMock($organizationId, $organizationNo, $telNoOrg);
        $pattern = '/' . $telNoOrg . '[0-9]{1}/';
        $this->assertRegExp($pattern, $telNoPayment);
    }

    public function testReturnOrderIdWhenPutOrgNoToFunctionGenerateOrderId()
    {
        $paymentExamService = new PaymentEikenExamService($this->getApplicationServiceLocator());
        $organizationNo = '90010100';
        $pattern = '/' . date('Ymd') . $organizationNo . '[0-9]{6}/';
        $orderId = $paymentExamService->generateOrderId($organizationNo);
        $this->assertRegExp($pattern, $orderId);
    }

    public function testReturnCorrectParamApiWhenPutParamToFunctionGetParameterOfApiCredit()
    {
        $organizationId = 1000;
        $organizationNo = 90015600;
        $telNoOrg = '456001234999';
        $params = array(
            'cardMonth'     => '10',
            'cardYear'      => '2016',
            'cardFirstName' => '山田ズン',
            'cardLastName'  => '山田ズン',
            'cardNumber'    => '4123450131003312',
            'cardCvv'       => '815',
            'kyu'           => array(
                //1 => array('price' => 8400, 'name' => '1級'),
                2 => array('price' => 6900, 'name' => '準1級'),
                3 => array('price' => 5000, 'name' => '2級'),
            ),
        );
        $paymentExamService = new PaymentEikenExamService($this->getApplicationServiceLocator());
        $orderId = $paymentExamService->generateOrderId($organizationNo);
        $telNo = $this->getTelNoByMock($organizationId, $organizationNo, $telNoOrg);
        $charsetConverter = new CharsetConverter();
        $parameter = $paymentExamService->getParameterOfApiCredit($params, $orderId, $telNo);
        $totalParameterExpect = 11 + count($params['kyu']) * 6;
        $this->assertEquals($totalParameterExpect, count($parameter));
        $this->assertEquals($telNo, $parameter['telNo']);
        $this->assertEquals($charsetConverter->utf8ToShiftJis($params['cardFirstName']), $parameter['kanjiName1_1']);
        $this->assertEquals($charsetConverter->utf8ToShiftJis($params['cardLastName']), $parameter['kanjiName1_2']);
        $this->assertEquals('0' . $params['cardCvv'], $parameter['CVV2']);
        $this->assertEquals($params['cardYear'] . $params['cardMonth'], $parameter['cardExpdate']);
        $i = 1;
        foreach ($params['kyu'] as $eikenLevel) {
            $eikenLevelName = $charsetConverter->utf8ToShiftJis($eikenLevel['name']);
            $this->assertEquals($eikenLevelName, $parameter['itemName' . $i]);
            $this->assertEquals($eikenLevel['price'], $parameter['unitPrice' . $i]);
            $i++;
        }
    }
    
    public function testReturnFalseWhenPutWrongParamPupilIdToFunctionCreateNewPayment()
    {
        $pupilId = 0;
        $eikenScheduleId = 2;
        $eikenLevels = array();
        $orderId = date('Ymd') . '90015600';
        $telNo = '4560012349991';
        $paymentExamService = new PaymentEikenExamService($this->getApplicationServiceLocator());
        $statusPayment = $paymentExamService->createNewPayment($pupilId, $eikenScheduleId, $eikenLevels, $orderId, $telNo);
        $this->assertFalse($statusPayment);
    }
    
    public function testReturnFalseWhenPutWrongParamEikenScheduelToFunctionCreateNewPayment()
    {
        $pupilId = 4;
        $eikenScheduleId = 0;
        $eikenLevels = array(
            2 => array('price' => 6900, 'name' => '準1級'),
            3 => array('price' => 5000, 'name' => '2級'),
        );
        $orderId = date('Ymd') . '900156004235';
        $telNo = '4560012349991';
        $paymentExamService = new PaymentEikenExamService($this->getApplicationServiceLocator());
        $statusPayment = $paymentExamService->createNewPayment($pupilId, $eikenScheduleId, $eikenLevels, $orderId, $telNo);
        $this->assertFalse($statusPayment);
    }
}
