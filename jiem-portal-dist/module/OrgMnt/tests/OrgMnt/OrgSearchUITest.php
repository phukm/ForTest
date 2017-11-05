<?php

class OrgSearchUITest extends \Dantai\Test\AbstractHttpControllerTestCase {
    public function testWhenShowFormThenDisplayCorrectBreadcum() 
    {
        $this->login();
        $this->dispatch('/org/org/index');
        $this->assertQueryContentContains('body', '団体情報管理');
        $this->assertQueryContentContains('body', '団体検索');
    }
    public function testWhenShowFormThenDisplayCorrectSearchBoxTitle() 
    {
        $this->login();
        $this->dispatch('/org/org/index');
        $this->assertQueryContentContains('.frm-detail .box', '検索条件');
    }
    public function testWhenShowFormThenDisplayCorrectOrgTitle() 
    {
        $this->login();
        $this->dispatch('/org/org/index');
        $this->assertQueryContentContains('#search-box .r1 .title-txtOrgNumber', '団体番号');
    }
    public function testWhenShowFormThenDisplayCorrectKanjiTitle() 
    {
        $this->login();
        $this->dispatch('/org/org/index');
        $this->assertQueryContentContains('#search-box .r1 .title-org-name', '団体名（漢字）');
    }
    public function testWhenShowFormThenDisplayCorrectKanaTitle() 
    {
        $this->login();
        $this->dispatch('/org/org/index');
        $this->assertQueryContentContains('#search-box .r1 .title-teet', '団体名（カナ）');
    }
    public function testWhenShowFormThenDisplayCorrectExamTitle() 
    {
        $this->login();
        $this->dispatch('/org/org/index');
        $this->assertQueryContentContains('#search-box .r2 .title_ex', '検定名');
    }
    public function testWhenShowFormThenDisplayCorrectFromToDateTitle() 
    {
        $this->login();
        $this->dispatch('/org/org/index');
        $this->assertQueryContentContains('#search-box .r2 .title-date-1', '検定試験申込日');
    }
    
    public function testWhenShowFormThenDisplayCorrectNameButtonClear() 
    {
        $this->login();
        $this->dispatch('/org/org/index');
        $this->assertQueryContentContains('.box-footer a.btn-small-100', 'クリア');
    }
    public function testWhenShowFormThenDisplayCorrectNameButtonSearch() 
    {
        $this->login();
        $this->dispatch('/org/org/index');
        $this->assertQueryContentContains('.box-footer a.btn-small-180', '検索');
    }

    public function testWhenShowFormThenHideAdvancedSearch() 
    {
        $this->login();
        $this->dispatch('/org/org/index');
        $this->assertQueryContentContains('#advanced-search .hide', '本会場運営費・準会場実施経費の取扱い');
    }
    
    public function testWhenShowFormThenDisplayCorrectHeaderTable() 
    {
        $this->login();
        $this->dispatch('/org/org/index');
        $this->assertQueryContentContains('.box-body table thead', '団体番号');
        $this->assertQueryContentContains('.box-body table thead', '団体名（漢字）');
        $this->assertQueryContentContains('.box-body table thead', '団体名（カナ）');
        $this->assertQueryContentContains('.box-body table thead', '住所');
        $this->assertQueryContentContains('.box-body table thead', '操作');
    }
      
}