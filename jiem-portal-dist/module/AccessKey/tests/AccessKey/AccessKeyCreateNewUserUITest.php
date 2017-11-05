<?php
use AccessKey\AccessKeyConst;
use Dantai\PrivateSession;
class AccessKeyCreateNewUserUITest extends \Dantai\Test\AbstractHttpControllerTestCase {
    public function setPrivateSession()
    {
        $data = array(
            'organizationNo'=>123456,
            'accessKey'=>123456
        );
        $privateSession = new PrivateSession();
        $privateSession->setData(AccessKeyConst::SESSION_ACCESS_KEY, $data);
    }
    
    
    public function testWhenShowFormThenDisplayCorrectExecuteIdTitle() 
    {
        $this->setPrivateSession();
        $this->dispatch('/access-key/access-key/add');
        $this->assertQueryContentRegex('.row-1 .col-sm-3 strong', '/ユーザID/');
    }
    
    public function testWhenShowFormThenDisplayCorrectNameKanjiTitle() 
    {
        $this->setPrivateSession();
        $this->dispatch('/access-key/access-key/add');
        $this->assertQueryContentRegex('.row-2 .col-sm-3 strong', '/管理者氏名（漢字）/');
    }
    
    public function testWhenShowFormThenDisplayCorrectEmailTitle() 
    {
        $this->setPrivateSession();
        $this->dispatch('/access-key/access-key/add');
        $this->assertQueryContentRegex('.row-3 .col-sm-3 strong', '/メールアドレス/');
    }
    
    
    public function testWhenShowFormThenDisplayCorrectConfirmEmailTitle() 
    {
        $this->setPrivateSession();
        $this->dispatch('/access-key/access-key/add');
        $this->assertQueryContentRegex('.row-5 .col-sm-3 strong', '/メールアドレス（確認）/');
    }
    
    public function testWhenShowFormThenDisplayCorrectNoteTitle() 
    {
        $this->setPrivateSession();
        $this->dispatch('/access-key/access-key/add');
        $this->assertQueryContentRegex('.row-6 .col-sm-3', '/は入力必須項目です。/');
    }
    
    public function testWhenShowFormThenDisplayCorrectAgreeTitle() 
    {
        $this->setPrivateSession();
        $this->dispatch('/access-key/access-key/add');
        $this->assertQueryContentRegex('.row-7 .policy-checkbox', '/利用規約に同意する/');
    }
    
}