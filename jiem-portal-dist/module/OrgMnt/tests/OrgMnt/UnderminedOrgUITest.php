<?php

class UnderminedOrgUITest extends \Dantai\Test\AbstractHttpControllerTestCase {
    public function testWhenShowFormThenDisplayCorrectBreadcum() 
    {
        $this->login();
        $this->dispatch('/org/org/undetermined');
        $this->assertQueryContentContains('body', '申込履歴・試験結果');
        $this->assertQueryContentContains('body', '団体検索');
        $this->assertQueryContentContains('body', '未確定団体検索');
    }
    public function testWhenShowFormThenDisplayCorrectSearchBoxTitle() 
    {
        $this->login();
        $this->dispatch('/org/org/undetermined');
        $this->assertQueryContentContains('#frm-undetermined .search-header', '検索条件');
    }
    public function testWhenShowFormThenDisplayCorrectYearTitle() 
    {
        $this->login();
        $this->dispatch('/org/org/undetermined');
        $this->assertQueryContentContains('#search-box .r1 .lblYear', '年度');
    }
    public function testWhenShowFormThenDisplayCorrectKaiTitle() 
    {
        $this->login();
        $this->dispatch('/org/org/undetermined');
        $this->assertQueryContentContains('#search-box .r1 .lblKai', '回');
    }
    public function testWhenShowFormThenDisplayCorrectStatusTitle() 
    {
        $this->login();
        $this->dispatch('/org/org/undetermined');
        $this->assertQueryContentContains('#search-box .r1 .lblStatus', 'ステータス');
    }
    public function testWhenShowFormThenDisplayCorrectOrganizationNoTitle() 
    {
        $this->login();
        $this->dispatch('/org/org/undetermined');
        $this->assertQueryContentContains('#search-box .r2 .lblOrgNo', '団体番号');
    }
    public function testWhenShowFormThenDisplayCorrectOrganizationNameTitle() 
    {
        $this->login();
        $this->dispatch('/org/org/undetermined');
        $this->assertQueryContentContains('#search-box .r2 .lblOrgName', '団体名');
    }
    public function testWhenShowFormThenDisplayCorrectNameButtonClear() 
    {
        $this->login();
        $this->dispatch('/org/org/undetermined');
        $this->assertQueryContentContains('#search-box .box-footer a.btn-small-100', 'クリア');
    }
    public function testWhenShowFormThenDisplayCorrectNameButtonSearch() 
    {
        $this->login();
        $this->dispatch('/org/org/undetermined');
        $this->assertQueryContentContains('#search-box .box-footer a.btn-small-180', '検索');
    }
    public function testWhenShowFormThenDisplayCorrectListBoxTitle() 
    {
        $this->login();
        $this->dispatch('/org/org/undetermined');
        $this->assertQueryContentContains('.search-form15 .box-header', '未確定団体検索');
    }
    public function testWhenShowFormThenDisplayCorrectNameButtonExport() 
    {
        $this->login();
        $this->dispatch('/org/org/undetermined');
        $this->assertQueryContentContains('body', 'ダウンロード');
    }
    public function testWhenShowFormThenDisplayCorrectHeaderTable() 
    {
        $this->login();
        $this->dispatch('/org/org/undetermined');
        $this->assertQueryContentContains('.box-body table thead', '団体番号');
        $this->assertQueryContentContains('.box-body table thead', '団体名（漢字）');
        $this->assertQueryContentContains('.box-body table thead', 'メールアドレス');
        $this->assertQueryContentContains('.box-body table thead', '電話番号');
        $this->assertQueryContentContains('.box-body table thead', 'ステータス');
        $this->assertQueryContentContains('.box-body table thead', '本会場申込人数');
        $this->assertQueryContentContains('.box-body table thead', '準会場申込人数');
        $this->assertQueryContentContains('.box-body table thead', '支払済人数');
    }
      
}