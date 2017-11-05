<?php
namespace Satellite\Eiken;

use Dantai\PrivateSession;
use Zend\Mvc\Controller\AbstractActionController;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ApplyEikenUiTest extends \Dantai\Test\AbstractHttpControllerTestCase
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
    
    public function testCorrectApplyEikenTitle()
    {
        $this->loginFake();
        $this->dispatch('/eiken/apply-eiken');
        $this->assertResponseStatusCode(200);
        $this->assertQueryContentContains('body', '英検申し込み');
    }
    
    public function testCorrectApplyEikenDescription()
    {
        $this->loginFake();
        $this->dispatch('/eiken/apply-eiken');
        $this->assertResponseStatusCode(200);       
    }
    
    public function testCorrectApplyEikenTable()
    {
        $this->loginFake();
        $this->dispatch('/eiken/apply-eiken');
        $this->assertResponseStatusCode(200);
    }
    
    public function testCorrectApplyEikenHallTypeSelect()
    {
        $this->loginFake();
        $this->dispatch('/eiken/apply-eiken');
        $this->assertResponseStatusCode(200);
    }
}