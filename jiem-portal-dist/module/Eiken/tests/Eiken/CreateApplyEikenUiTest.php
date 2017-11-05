<?php
namespace Eiken;

class CreateApplyEikenUiTest extends \Dantai\Test\AbstractHttpControllerTestCase
{    
    public function testShowCorrectTitle()
    {
        $this->login();
        $this->dispatch('/eiken/eikenorg/create');
        if($this->getResponseStatusCode() == '200'){
            $this->assertQueryContentContains('body', '年度第');
        }else{
            $this->assertResponseStatusCode(302);
        }
    }
    
    public function testShowCorrectDescription()
    {
        $this->login();
        $this->dispatch('/eiken/eikenorg/create');
        if($this->getResponseStatusCode() == '200'){
            $this->assertQueryContentContains('body', '申込済人数：スマホまたはPCから申請された人数です');
        }else{
            $this->assertResponseStatusCode(302);
        }
    }
    
    public function testShowCorrectTableLabel()
    {
        $this->login();
        $this->dispatch('/eiken/eikenorg/create');
        if($this->getResponseStatusCode() == '200'){
            $this->assertQueryContentContains('body', '受験級');
        }else{
            $this->assertResponseStatusCode(302);
        }
    }
    
    public function testShowCorrectRefundStatusLabel()
    {
        $this->login();
        $this->dispatch('/eiken/eikenorg/create');
        if($this->getResponseStatusCode() == '200'){
            $this->assertQueryContentContains('body', '本会場運営費・準会場実施経費の取扱い');
        }else{
            $this->assertResponseStatusCode(302);
        }
    }
}