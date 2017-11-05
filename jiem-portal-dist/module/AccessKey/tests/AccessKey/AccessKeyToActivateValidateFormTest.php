<?php
class AccessKeyToActivateValidateFormTest extends \Dantai\Test\AbstractHttpControllerTestCase
{
    const LOGIN_SESSION_KEY = 'LoginSessionKey';
    private $params = array(
        'accessKey'      => '99999999999999999999'
    );

    private $msg01;
    private $msg17;
    private $msg18;
    public function createLoginInfor(){
        $data = array(
            'orgNo'=>'99930101',
            'userId'=>'username',
            'passWord'=>'passsword',
        );
        $privateSession = new \Dantai\PrivateSession();
        $privateSession->setData(self::LOGIN_SESSION_KEY, $data);
    }

    public function createMsg()
    {
        $this->msg01 = '/' . $this->getApplicationServiceLocator()->get('MVCTranslator')->translate('MSG1') . '/';
        $this->msg17 = '/' . $this->getApplicationServiceLocator()->get('MVCTranslator')->translate('MSG17_AccessKey') . '/';
        $this->msg18 = '/' . $this->getApplicationServiceLocator()->get('MVCTranslator')->translate('MSG18_AccessKey') . '/';
    }
    public function testWhenSubmitEmtpyFirstAccessKeyThenShowMessage1()
    {
        $this->createLoginInfor();
        $this->createMsg();
        $this->params['accessKey'] = '';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/activate', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg01);
    }

    public function testWhenSubmitHadValueAccessKeyThenNotShowMessage1()
    {
        $this->createLoginInfor();
        $this->createMsg();
        $data = $this->params;
        $this->dispatch('/access-key/access-key/activate', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertNotQueryContentRegex('body', $this->msg01);
    }
    public function testWhenSubmitAccessKeyNotExistsThenShowMessage17()
    {
        $this->createLoginInfor();
        $this->createMsg();
        $data = $this->params;
        $this->dispatch('/access-key/access-key/activate', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg17);
    }
}