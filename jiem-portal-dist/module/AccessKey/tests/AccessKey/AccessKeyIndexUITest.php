<?php

class AccessKeyIndexUITest extends \Dantai\Test\AbstractHttpControllerTestCase {
    public function testWhenShowFormThenDisplayCorrectExecuteIdTitle() 
    {
        $this->dispatch('/access-key/access-key/index');
        $this->assertQueryContentRegex('.index-access-key .box-header b.title', '/アクセスキー入力/');
    }
    public function testWhenShowFormThenDisplayCorrectExecuteIdDescription() 
    {
        $this->dispatch('/access-key/access-key/index');
        $this->assertQueryContentRegex('.index-access-key p.desc-title', '/管理者を登録するために、団体番号とアクセスキーを入力してください。/');
    }
    public function testWhenShowFormThenDisplayCorrectOrganizationNoTitle() 
    {
        $this->dispatch('/access-key/access-key/index');
        $this->assertQueryContentRegex('body', '/団体番号/');
    }
    public function testWhenShowFormThenDisplayCorrectAccessKeyTitle() 
    {
        $this->dispatch('/access-key/access-key/index');
        $this->assertQueryContentRegex('body', '/アクセスキー/');
    }
    public function testWhenShowFormThenDisplayCorrectNameButtonNext() 
    {
        $this->dispatch('/access-key/access-key/index');
        $this->assertQueryContentRegex('body', '/次へ/');
    }
      
}