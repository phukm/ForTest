<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace InvitationSetting;

use InvitationMnt\Service\SettingService;

class AddInvitationUITest extends \Dantai\Test\AbstractHttpControllerTestCase
{   
    public function testShowCorrectAddInvitationScreen() 
    {
        $this->login();
        $this->dispatch('/invitation/setting/add');
        $this->assertQueryContentContains('body', '受験案内状登録');
    }
    
    public function testWhenShowFormThenDisplayField() 
    {
        $this->login();
        $this->dispatch('/invitation/setting/add');
        $this->assertQueryContentContains('body', '年度');
        $this->assertQueryContentContains('body', '受験案内状の選択');
        $this->assertQueryContentContains('body', '団体名');
        $this->assertQueryContentContains('body', '氏名');
        $this->assertQueryContentContains('body', '発行日');
        $this->assertQueryContentContains('body', '実施会場の選択');
        $this->assertQueryContentContains('body', '申込可能級の選択');
        $this->assertQueryContentContains('body', '一次試験実施曜日');
        $this->assertQueryContentContains('body', '一次試験会場');
        $this->assertQueryContentContains('body', '申込期限');
        $this->assertQueryContentContains('body', '支払形式');
        $this->assertQueryContentContains('body', '申込級確認');
        $this->assertQueryContentContains('body', '支払方法');
        $this->assertQueryContentContains('body', '支払対象コンビニ');
        $this->assertQueryContentContains('body', '先生からのメッセ');
        $this->assertQueryContentContains('body', 'ージの掲載');
        $this->assertQueryContentContains('body', '生徒向けメッセージ');
        $this->assertQueryContentContains('body', '保護者向けメッセージ');
        $this->assertQueryContentContains('body', 'ダブル受験希望時の対応');
    }
}