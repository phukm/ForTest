<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/BasicConstruction for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace IBAControllerTest;

class ApplyIBAPolicyTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    public function getAPIMock()
    {
        $api = $this->getMockBuilder('\Dantai\Api\UkestukeClient')
                ->disableOriginalConstructor()
                ->getMock();

        $api->expects($this->any())
                ->method('callEir2a01')
                ->will($this->returnValue(0));
        return $api;
    }
    
    public function testWhenAccessPagePolicyThenShowCorrectBreadCrumb() {
        $this->login();
        $ibaService = $this->getApplicationServiceLocator()->get('IBA\Service\IBAServiceInterface');
        $ibaService->setUketukeClient($this->getAPIMock());
        $this->dispatch('/iba/iba/policy');
        $this->assertQueryContentRegex('body', '/各種検定申込/');
        $this->assertQueryContentRegex('body', '/英検IBA申込/');
        $this->assertQueryContentRegex('body', '/申込規約確認/');
    }

    public function testWhenSubmitAllEmptyDataThenRedirectToPolicyScreen() {
        $params = array(
            'firtNameKanji' => '',
            'lastNameKanji' => '',
            'mailAddress' => '',
            'confirmEmail' => '',
            'phoneNumber' => '',
        );
        $this->dispatch('/iba/iba/policy', \Zend\Http\Request::METHOD_POST, $params);
        $this->assertModuleName('IBA');
        $this->assertControllerName('iba\controller\iba');
        $this->assertActionName('policy');
    }

    public function testWhenSubmitCorrectDataThenRedirectToRegisterScreen() {
        $this->login();
        $ibaService = $this->getApplicationServiceLocator()->get('IBA\Service\IBAServiceInterface');
        $ibaService->setUketukeClient($this->getAPIMock());
        $params = array(
            'firtNameKanji' => '外五',
            'lastNameKanji' => '土八',
            'mailAddress' => 'vuvanphuc.1990@gmail.com',
            'confirmEmail' => 'vuvanphuc.1990@gmail.com'
        );
        $this->dispatch('/iba/iba/policy', \Zend\Http\Request::METHOD_POST, $params);
        $this->assertModuleName('IBA');
        $this->assertControllerName('iba\controller\iba');
        $this->assertRedirectRegex('/\/iba\/iba\/add\?token\=[a-z0-9]/');
    }

//    //FN_2.2 - [FN_3]
    public function testWhenSubmitEmtpyFisrtAndLastNameThenShowMessage1() {
        $ibaService = $this->getApplicationServiceLocator()->get('IBA\Service\IBAServiceInterface');
        $ibaService->setUketukeClient($this->getAPIMock());
        $this->login();
        $params = array(
            'firtNameKanji' => '',
            'lastNameKanji' => '',
            'mailAddress' => '',
            'confirmEmail' => ''
        );
        $this->dispatch('/iba/iba/policy', \Zend\Http\Request::METHOD_POST, $params);
        $this->assertQueryContentRegex('body', '/必須入力項目です。/');
    }

    //FN_2.2 - [FN_4]
    public function testWhenSubmitEmptyEmailAddThenShowMessage1() {
        $ibaService = $this->getApplicationServiceLocator()->get('IBA\Service\IBAServiceInterface');
        $ibaService->setUketukeClient($this->getAPIMock());
        $this->login();
        $params = array(
            'mailAddress' => ''
        );
        $this->dispatch('/iba/iba/policy', \Zend\Http\Request::METHOD_POST, $params);
        $this->assertQueryContentRegex('body', '/必須入力項目です/');
    }

    public function testWhenSubmitEmailAddThenValidateFormatEmailShowMessage8() {
        $ibaService = $this->getApplicationServiceLocator()->get('IBA\Service\IBAServiceInterface');
        $ibaService->setUketukeClient($this->getAPIMock());
        $this->login();
        $params = array(
            'mailAddress' => '',
            'confirmEmail' => ''
        );
        $this->dispatch('/iba/iba/policy', \Zend\Http\Request::METHOD_POST, $params);
        //@TODO
//        $this->assertQueryContentRegex('body','/メールアドレスの形式が正しくありません。/');
    }

    //FN_2.2 - [FN_5]
    public function testWhenSubmitEmptyConfirmEmailThenShowMessage1() {
        $ibaService = $this->getApplicationServiceLocator()->get('IBA\Service\IBAServiceInterface');
        $ibaService->setUketukeClient($this->getAPIMock());
        $this->login();
        $params = array(
            'confirmEmail' => ''
        );
        $this->dispatch('/iba/iba/policy', \Zend\Http\Request::METHOD_POST, $params);
        $this->assertQueryContentRegex('body', '/必須入力項目です/');
    }

    public function testWhenSubmitConfirmEmailNotAsEmailAddThenShowMessage19() {
        $ibaService = $this->getApplicationServiceLocator()->get('IBA\Service\IBAServiceInterface');
        $ibaService->setUketukeClient($this->getAPIMock());
        $this->login();
        $params = array(
            'mailAddress' => 'a@gmail.com',
            'confirmEmail' => 'ab@gmail.com'
        );
        $this->dispatch('/iba/iba/policy', \Zend\Http\Request::METHOD_POST, $params);
        $this->assertQueryContentRegex('body', '/メールアドレスが一致しません/');
    }

    //FN_2.2 - [FN_6] -> remove
}
