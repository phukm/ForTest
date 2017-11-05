<?php

class AccessKeyValidateFormTest extends \Dantai\Test\AbstractHttpControllerTestCase
{
    private $params = array(
        'organizationNo' => '999999999999999999999999999',
        'accessKey'      => '1234234324432432453421323213'
    );

    private $msg01;
    private $msg17;
    public function createMsg()
    {
        $this->msg01 = '/' . $this->getApplicationServiceLocator()->get('MVCTranslator')->translate('MSG1') . '/';
        $this->msg17 = '/' . $this->getApplicationServiceLocator()->get('MVCTranslator')->translate('MSG17_AccessKey') . '/';
    }


    public function testWhenSubmitEmtpyFirstOrganizationNoThenShowMessage1()
    {
        $this->createMsg();
        $this->params['organizationNo'] = '';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/index', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg01);
    }

    public function testWhenSubmitHadValueOrganizationNoThenNotShowMessage1()
    {
        $this->createMsg();
        $data = $this->params;
        $this->dispatch('/access-key/access-key/index', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertNotQueryContentRegex('body', $this->msg01);
    }

    public function testWhenSubmitEmtpyFirstAccessKeyThenShowMessage1()
    {
        $this->createMsg();
        $this->params['accessKey'] = '';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/index', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg01);
    }

    public function testWhenSubmitHadValueAccessKeyThenNotShowMessage1()
    {
        $this->createMsg();
        $data = $this->params;
        $this->dispatch('/access-key/access-key/index', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertNotQueryContentRegex('body', $this->msg01);
    }
    public function testWhenSubmitOrgnoNotExistsThenShowMessage17()
    {
        $this->createMsg();
        $data = $this->params;
        $this->dispatch('/access-key/access-key/index', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg17);
    }

}