<?php

namespace Eiken;

class PaymentStatusUITest extends \Dantai\Test\AbstractHttpControllerTestCase {
    
    public function testTitlePageWidthYear() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016');
        $this->assertQueryContentRegex('body', '/支払情報 - 2016年度第/');
    }
    public function testTitlePageWidthYearAndKai() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016&kai=1');
        $this->assertQueryContentRegex('body', '/支払情報 - 2016年度第1回/');
    }
    public function testSummaryTableTitleApplyEikenRegister() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016&kai=1');
        $this->assertQueryContentRegex('body', '/登録者数/');
    }
    public function testSummaryTableTitleApplyEikenHadPayment() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016&kai=1');
        $this->assertQueryContentRegex('body', '/支払者数/');
    }
    public function testSummaryTableTitleCyu1() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016&kai=1');
        $this->assertQueryContentRegex('body', '/1級/');
    }
    public function testSummaryTableTitlePreCyu1() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016&kai=1');
        $this->assertQueryContentRegex('body', '/準1級/');
    }
    public function testSummaryTableTitleCyu2() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016&kai=1');
        $this->assertQueryContentRegex('body', '/2級/');
    }
    public function testSummaryTableTitlePreCyu2() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016&kai=1');
        $this->assertQueryContentRegex('body', '/準2級/');
    }
    public function testSummaryTableTitleCyu3() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016&kai=1');
        $this->assertQueryContentRegex('body', '/3級/');
    }
    public function testSummaryTableTitleCyu4() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016&kai=1');
        $this->assertQueryContentRegex('body', '/4級/');
    }
    public function testSummaryTableTitleCyu5() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016&kai=1');
        $this->assertQueryContentRegex('body', '/5級/');
    }
    public function testSummaryTableTitleTotal() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016&kai=1');
        $this->assertQueryContentRegex('body', '/合計/');
    }
    public function testSummaryTableValueCyu1() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=9999');
        $this->assertQueryContentRegex('body .tblLv', '/0/');
    }
    public function testSummaryTableValueTotal() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=9999');
        $this->assertQueryContentRegex('body .tblLv', '/0/');
    }
    public function testTitleInTableListWidthNameKanji() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016');
        $this->assertQueryContentRegex('body', '/氏名（漢字）/');
    }
    public function testTitleInTableListWidthGrade() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016');
        $this->assertQueryContentRegex('body', '/学年/');
    }
    public function testTitleInTableListWidthClass() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016');
        $this->assertQueryContentRegex('body', '/クラス/');
    }
    public function testTitleInTableListWidthExamGrade() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016');
        $this->assertQueryContentRegex('body', '/受験級/');
    }
    public function testTitleInTableListWidthTestSite() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016');
        $this->assertQueryContentRegex('body', '/実施会場/');
    }
    public function testTitleInTableListWidthPaymentStatus() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016');
        $this->assertQueryContentRegex('body', '/支払状況/');
    }
    public function testTitleInTableListWidthPaymentDate() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016');
        $this->assertQueryContentRegex('body', '/支払日/');
    }
    public function testTitleInTableListWidthPaymentMethod() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016');
        $this->assertQueryContentRegex('body', '/支払方法/');
    }
    public function testTitleInTableListWidthApplyStatus() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016');
        $this->assertQueryContentRegex('body', '/登録状況/');
    }
    public function testTitleInTableListWidthApplyDate() {
        $this->login();
        $this->dispatch('/eiken/payment/paymentstatus?year=2016');
        $this->assertQueryContentRegex('body', '/登録日/');
    }

}
