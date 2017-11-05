<?php

namespace Logs;

use Dantai\Utility\DateHelper;

class ApplyEikenSearchForm extends \Dantai\Test\AbstractHttpControllerTestCase {
    public function createEikenServiceMock(){
        $eikenServiceMock = $this->getMockBuilder('\Logs\Service\ApplyEikenService')
            ->disableOriginalConstructor()
            ->getMock();

        $dateMock = new \DateTime();
        $formCurrentDeadlineFrom = $dateMock->format(\Dantai\Utility\DateHelper::DATE_FORMAT_DEFAULT);
        $sqlCurrentDeadlineFrom = $dateMock->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT);
        $formNextDeadlineFromMinusByOneDay = date(\Dantai\Utility\DateHelper::DATE_FORMAT_DEFAULT, strtotime($dateMock->format(\Dantai\Utility\DateHelper::DATE_FORMAT_DEFAULT) . '-1 days'));
        $sqlNextDeadlineFromMinusByOneDay = date(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT, strtotime($dateMock->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) . '-1 days'));

        $eikenServiceMock->expects($this->any())
            ->method('getCurrentAndNextEikenSchedule')
            ->will($this->returnValue(array($formCurrentDeadlineFrom, $formNextDeadlineFromMinusByOneDay, $sqlCurrentDeadlineFrom, $sqlNextDeadlineFromMinusByOneDay)));
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('Logs\Service\ApplyEikenServiceInterface', $eikenServiceMock);
    }

    // UI Search Form unit test
    public function testShowCorrectSearchFormTitleWhenAccessPageViewApplyEikenLogs() {
        $this->login();
        $this->createEikenServiceMock();
        $this->dispatch('/logs/apply-eiken/index');
        $this->assertQueryContentContains('body', '検索条件');
    }
    
    public function testShowCorrectLabelOnSearchFormWhenAccessPageViewApplyEikenyLogs(){
        $this->login();
        $this->createEikenServiceMock();
        $this->dispatch('/logs/apply-eiken/index');
        $this->assertQueryContentRegex('body', '/団体番号/');
        $this->assertQueryContentRegex('body', '/団体名/');
        $this->assertQueryContentRegex('body', '/操作/');
        $this->assertQueryContentRegex('body', '/から/');
        $this->assertQueryContentRegex('body', '/まで/');
        $this->assertQueryContentRegex('body', '/検索/');
        $this->assertQueryContentRegex('body', '/クリア/');
    }
    
    // UI List Table unit test
    public function testShowCorrectListTitleWhenAccessPageViewApplyEikenLogs(){
        $this->login();
        $this->createEikenServiceMock();
        $this->dispatch('/logs/apply-eiken/index');
        $this->assertQueryContentRegex('body', '/申込確定後の変更情報/');
    }
    
    public function testShowCorrectOrgNoColumnOnListTableWhenAccessPageViewApplyEikenLogs(){
        $this->login();
        $this->createEikenServiceMock();
        $this->dispatch('/logs/apply-eiken/index');
        $this->assertQueryContentRegex('body', '/団体番号/');
        $this->assertQueryContentRegex('body', '/団体名/');
        $this->assertQueryContentRegex('body', '/ユーザID/');
        $this->assertQueryContentRegex('body', '/日時/');
        $this->assertQueryContentRegex('body', '/操作/');
        $this->assertQueryContentRegex('body', '/詳細/');
    }
    
    public function testBreadCrumbPageViewApplyEikenLogs(){
        $this->login();
        $this->createEikenServiceMock();
        $this->dispatch('/logs/apply-eiken/index');
        $this->assertQueryContentRegex('body', '/申込確定後の変更情報/');
    }
}
