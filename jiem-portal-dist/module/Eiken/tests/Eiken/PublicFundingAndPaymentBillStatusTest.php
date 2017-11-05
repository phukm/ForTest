<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Eiken;

use Eiken\Service\ApplyEikenOrgService;
use Application\Entity\PaymentMethod;
use Eiken\EikenConst;

class PublicFundingAndPaymentBillStatusTest extends \Dantai\Test\AbstractHttpControllerTestCase
{
    public function testSavePublicFundingStatusIntoSessionSuccess()
    {
        $this->login();
        $data = array('fundingStatus' => '0');
        $this->dispatch('/eiken/eikenorg/funding', \Zend\Http\Request::METHOD_POST, $data);
        $finalResult = $this->getResponse()->getContent();
        $this->assertEquals($finalResult, EikenConst::SAVE_SUCCESS_FUNDINGSTATUS_INTO_SESSION);
    }
    
    public function testSavePaymentBillStatusIntoSessionSuccess()
    {
        $this->login();
        $data = array('paymentStatus' => '0');
        $this->dispatch('/eiken/eikenorg/payment', \Zend\Http\Request::METHOD_POST, $data);
        $finalResult = $this->getResponse()->getContent();
        $this->assertEquals($finalResult, EikenConst::SAVE_SUCCESS_FUNDINGSTATUS_INTO_SESSION);
    }
    
    public function getPaymentMethodMock($dataPayment = array())
    {
        $paymentMock = $this->getMockBuilder('Application\Entity\Repository\PaymentMethodRepository')
                ->disableOriginalConstructor()
                ->getMock();
        
        $paymentMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($dataPayment));
        return $paymentMock;
    }
    
    public function testGetSuccessRecordInPaymentMethod()
    {
        $this->login();
        $paymentObject = new \Application\Entity\PaymentMethod();
        $paymentObject->setId(1);
        $applyEikenService = $this->getApplicationServiceLocator()->get('Eiken\Service\ApplyEikenOrgServiceInterface');
        $applyEikenService->setPaymentMethodRepository($this->getPaymentMethodMock($paymentObject));
        $result = $applyEikenService->getPaymentMethodExistValue();
        $this->assertEquals($result, EikenConst::EXIST);
    }
    
    public function getOrganizationMock($dataOrg = array())
    {
        $orgMock = $this->getMockBuilder('Application\Entity\Repository\OrganizationRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $orgMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($dataOrg));
        return $orgMock;
    }
    
    public function getEikenScheduleMock($dataSchedule = array())
    {
        $scheduleMock = $this->getMockBuilder('Application\Entity\Repository\EikenScheduleRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $scheduleMock->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($dataSchedule));
        return $scheduleMock;
    }
    
    public function getEntityMock()
    {
        $repositoryMock = $this->getMock('\Doctrine\ORM\EntityManager', array('getRepository', 'getReference', 'getClassMetadata', 'persist', 'flush','clear'), array(), '', false);
        
        $repositoryMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue((object)array('name' => 'aClass')));
        $repositoryMock->expects($this->any())
                        ->method('persist')
                        ->will($this->returnValue(null));
        $repositoryMock->expects($this->any())
                        ->method('flush')
                        ->will($this->returnValue(null));
        $repositoryMock->expects($this->any())
                        ->method('getReference')
                        ->will($this->returnValue(null));
        $repositoryMock->expects($this->any())
                        ->method('clear')
                        ->will($this->returnValue(null));
        
        return $repositoryMock;
    }
    
    public function testSaveDataIntoDatabaseSuccess()
    {
        $this->login();
        $orgObject = new \Application\Entity\Organization();
        $orgObject->setId(1);
        $scheduleObject = new \Application\Entity\EikenSchedule();
        $scheduleObject->setId(1);
        $applyEikenService = $this->getApplicationServiceLocator()->get('Eiken\Service\ApplyEikenOrgServiceInterface');
        $applyEikenService->setOrgRepository($this->getOrganizationMock($orgObject));
        $applyEikenService->setEikenScheduleRepository($this->getEikenScheduleMock($scheduleObject));
        $result = $applyEikenService->createPaymentMethod(1, 1, $this->getEntityMock());
        $this->assertEquals($result, EikenConst::SAVE_DATA_INTO_DATABASE_SUCCESS);
    }
}
