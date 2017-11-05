<?php

class AccessKeyActivateUITest extends \Dantai\Test\AbstractHttpControllerTestCase {
    const LOGIN_SESSION_KEY = 'LoginSessionKey';
    public function createLoginInfor(){
        $data = array(
            'orgNo'=>'99930101',
            'userId'=>'username',
            'passWord'=>'passsword',
        );
        $privateSession = new \Dantai\PrivateSession();
        $privateSession->setData(self::LOGIN_SESSION_KEY, $data);
    }
    public function testWhenShowFormThenDisplayCorrectExecuteIdTitle() 
    {
        $this->createLoginInfor();
        $this->dispatch('/access-key/access-key/activate');
        $this->assertQueryContentRegex('body', '/ユーザIDの再有効化が必要です/');
    }
    public function testWhenShowFormThenDisplayCorrectExecuteIdDescription() 
    {
        $this->createLoginInfor();
        $this->dispatch('/access-key/access-key/activate');
        $this->assertQueryContentRegex('.index-access-key p.desc-title', '/ユーザIDは英検実施回毎に再有効化が必要です。以下に、アクセスキーを入力してください。/');
        $this->assertQueryContentRegex('.index-access-key p.desc-title', '/アクセスキーがわからない場合は貴団体の管理者様にお問い合わせください。/');
    }
    public function testWhenShowFormThenDisplayCorrectAccessKeyTitle() 
    {
        $this->createLoginInfor();
        $this->dispatch('/access-key/access-key/activate');
        $this->assertQueryContentRegex('body', '/アクセスキー/');
    }
    public function testWhenShowFormThenDisplayCorrectNameButtonCancel() 
    {
        $this->createLoginInfor();
        $this->dispatch('/access-key/access-key/activate');
        $this->assertQueryContentRegex('body', '/キャンセル/');
    }
    public function testWhenShowFormThenDisplayCorrectNameButtonSave() 
    {
        $this->createLoginInfor();
        $this->dispatch('/access-key/access-key/activate');
        $this->assertQueryContentRegex('body', '/再有効化する/');
    }
      
}