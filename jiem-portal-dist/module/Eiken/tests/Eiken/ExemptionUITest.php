<?php

namespace Eiken;

class ExemptionUITest extends \Dantai\Test\AbstractHttpControllerTestCase {

    protected $fakeData = array(
        'year' => 2015,
        'kai' => 15123222588,
        'eikenid' => 1,
        'name' => '朝日翼'
    );

    public function testWhenShowFormThenDisplayCorrectExecuteTitle() {
        $this->login();
        $data = $this->fakeData;
        $this->dispatch('/eiken/exemption/list', \Zend\Http\Request::METHOD_GET, $data);
        $this->assertQueryContentRegex('body', '/検索条件/');
    }

    public function testWhenShowFormThenDisplayCorrectExecuteLabel() {
        $this->login();
        $data = $this->fakeData;
        $this->dispatch('/eiken/exemption/list', \Zend\Http\Request::METHOD_GET, $data);

        $this->dispatch('/eiken/exemption/list');
        $this->assertQueryContentRegex('body', '/年度/');
        $this->assertQueryContentRegex('body', '/回/');
        $this->assertQueryContentRegex('body', '/英検ID/');
        $this->assertQueryContentRegex('body', '/氏名/');
    }

    public function testWhenShowFormThenDisplayCorrectExecuteBoxHeader() {
        $this->login();
        $data = $this->fakeData;
        $this->dispatch('/eiken/exemption/list', \Zend\Http\Request::METHOD_GET, $data);

        $this->dispatch('/eiken/exemption/list');
        $this->assertQueryContentRegex('body', '/一免者情報/');
    }

    public function testWhenShowFormThenDisplayCorrectExecuteHeaderTable() {
        $this->login();
        $data = $this->fakeData;
        $this->dispatch('/eiken/exemption/list', \Zend\Http\Request::METHOD_GET, $data);

        $this->dispatch('/eiken/exemption/list');
        $this->assertQueryContentRegex('body', '/年度/');
        $this->assertQueryContentRegex('body', '/回/');
        $this->assertQueryContentRegex('body', '/英検ID/');
        $this->assertQueryContentRegex('body', '/パスワード/');
        $this->assertQueryContentRegex('body', '/受験級/');
        $this->assertQueryContentRegex('body', '/受験地番号/');
        $this->assertQueryContentRegex('body', '/個人番号/');
        $this->assertQueryContentRegex('body', '/氏名（カナ）/');
        $this->assertQueryContentRegex('body', '/氏名（漢字）/');
        $this->assertQueryContentRegex('body', '/性別/');
        $this->assertQueryContentRegex('body', '/生年月日/');
    }
}
