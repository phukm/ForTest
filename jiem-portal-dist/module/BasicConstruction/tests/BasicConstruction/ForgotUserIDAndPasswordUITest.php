<?php
namespace BasicConstruction;

class ForgotUserIDAndPasswordUITest extends \Dantai\Test\AbstractHttpControllerTestCase {
    private $params = array(
        'radioOption' => '',
        'txtEmail' => ''
    );
    
    private $forgotParams = array(
        'txtPassword' => '',
        'txtConfirmPassword' => ''
    );
    
    private $msg1;
    private $msg7;
    
    private $orgNo = 90010100;
    private $password = '123456';
    private $userId = 'USER001';

    public function createMsg(){
        $this->msg1 = '/'.$this->getApplicationServiceLocator()->get('MVCTranslator')->translate('MSG1-forgot-password-mandatory').'/';
        $this->msg7 = '/'.$this->getApplicationServiceLocator()->get('MVCTranslator')->translate('MSG7-forgot-password-email-field').'/';
    }
    
    public function login() {
        $this->dispatch('/login', \Zend\Http\Request::METHOD_POST, array(
            'orgNo' => $this->orgNo,
            'password' => $this->password,
            'userId' => $this->userId,
        ));
        $this->assertRedirectTo('/org/org/index');
    }
    
    public function testAccessToLoginFormThenShowCorrectLink(){
        $this->dispatch('/login');
        $this->assertQueryContentRegex('.forgot-password', '/' . $this->getApplicationServiceLocator()->get('MVCTranslator')->translate('login-form-forgot-link') . '/');
        
    }
    
    public function testAccessToForgotUserFormThenShowCorrectTitle(){
        $this->dispatch('/user/forgot');
        $this->assertQueryContentRegex('.title', '/' . $this->getApplicationServiceLocator()->get('MVCTranslator')->translate('forgot-form-title') . '/');
    }
    
    public function testAccessToForgotUserFormThenShowCorrectEmailTitle(){
        $this->dispatch('/user/forgot');
        $this->assertQueryContentRegex('.email-title', '/' . $this->getApplicationServiceLocator()->get('MVCTranslator')->translate('forgot-form-email-title') . '/');
    }
    
    public function testInputNullOptionThenShowMsg1(){
        $this->createMsg();
        $data = $this->params;
        $data['radioOption'] = '';
        $this->dispatch('/user/forgot', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg1);
    }
    
    public function testInputNullEmailThenShowMsg1(){
        $this->createMsg();
        $data = $this->params;
        $data['txtEmail'] = '';
        $this->dispatch('/user/forgot', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg1);
    }
    
    public function testInputDoesntExistEmailThenShowMsg7(){
        $this->createMsg();
        $data = $this->params;

        $data['txtEmail'] = md5('testemail0112015') . '@gamil.com';
                
        $this->dispatch('/user/forgot', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg7);
    }
}

