<?php

namespace GoalSettingControllerTest;

use GoalSetting\Service\GraduationGoalSettingService;

class GraduationGoalSettingTest extends \Dantai\Test\AbstractHttpControllerTestCase {

    public function testWhenAccessPageGraduationIndexThenShowCorrectBreadCrumb() {
        $this->login();
        $this->dispatch('/goalsetting/eikenscheduleinquiry/index');
        $this->assertQueryContentRegex('ul.breadcrumb', '/目標設定・学習計画/');
        $this->assertQueryContentRegex('ul.breadcrumb', '/学習計画/');
    }
    
    public function testGetlistCity() {
        $service = $this->getApplicationServiceLocator()->get('GoalSetting\Service\GraduationGoalSettingServiceInterface');
        $listcity = $service->listCity();        
        $this->assertTrue(count($listcity) > 0);
    }
  
}
