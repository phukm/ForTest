<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace InvitationSetting;

use InvitationMnt\Service\SettingService;

class ValidateIssueDateTest extends \Dantai\Test\AbstractHttpControllerTestCase
{   
    public function getService()
    {
        return $this->getApplicationServiceLocator()->get('InvitationMnt\Service\SettingServiceInterface');
    }
    
    public function getTranslator()
    {
        return $this->getApplicationServiceLocator()->get('MVCTranslator');
    }
    
    public function testMessageWhenWrongDateFormat()
    {
        $this->login();
        $wrongFormatDate = '1232123';
        $appEndDate = '2016/04/02';
        $msg = $this->getService()->validateIssueDate($wrongFormatDate, $appEndDate);
        $this->assertEquals($msg, $this->getTranslator()->translate('MSG011'));
    }
    
    public function testMessageWhenIssueDateGreaterThanApplicationEndDate()
    {
        $this->login();
        $issueDate = '2016/04/20';
        $appEndDate = '2016/04/02';
        $msg = $this->getService()->validateIssueDate($issueDate, $appEndDate);
        $this->assertEquals($msg, $this->getTranslator()->translate('R4_MSG11'));
    }
}