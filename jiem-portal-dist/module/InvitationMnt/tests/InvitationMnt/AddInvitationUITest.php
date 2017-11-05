<?php

namespace InvitationMnt;

class AddInvitationUITest extends \Dantai\Test\AbstractHttpControllerTestCase {
    
    public function testShowCorrectAddInvitationScreen() 
    {
        $this->login();
        $this->dispatch('/invitation/setting/add');
        $this->assertQueryContentContains('body', '受験案内状登録');
    }
    
    public function testWhenShowFormThenDisplayRequireField() 
    {
        $this->login();
        $this->dispatch('/invitation/setting/add');
        $this->assertQueryContentContains('body', '年度');
        $this->assertQueryContentContains('body', '受験案内状の選択');
        $this->assertQueryContentContains('body', '実施会場の選択');
        $this->assertQueryContentContains('body', '申込可能級の選択');
    }
    
    public function testWhenShowFormThenDisplayPaymentMethodFields() {  
        $this->login();
        $this->dispatch('/invitation/setting/add');
        $this->assertQueryContentContains('body', '支払形式');
        $this->assertQueryContentContains('body', '申込級確認');
        $this->assertQueryContentContains('body', '支払方法');
        $this->assertQueryContentContains('body', '支払対象コンビニ');
    }
      
}