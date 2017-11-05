<?php

namespace GoalSettingControllerTest;

use GoalSetting\Service\EikenScheduleInquiryService;

class EikenScheduleInquiry extends \Dantai\Test\AbstractHttpControllerTestCase {

    public function testWhenAccessPageEikenScheduleIndexThenShowCorrectBreadCrumb() {
        $this->login();
        $this->dispatch('/goalsetting/eikenscheduleinquiry/index');
        $this->assertQueryContentRegex('ul.breadcrumb', '/目標設定・学習計画/');
        $this->assertQueryContentRegex('ul.breadcrumb', '/学習計画/');
    }

//    public function testCorrectLabel() {
//        $this->login();
//        $this->dispatch('/goalsetting/eikenscheduleinquiry/index');
//        $this->assertQueryContentRegex('span.text-eiken', '/英検/');
//        $this->assertQueryContentRegex('span.text-eiken-iba', '/英検IBA/');
//        $this->assertQueryContentRegex('table.tbl-calendar-control', '/英検申込期間/');
//        $this->assertQueryContentRegex('table.tbl-calendar-control', '/一次試験実施/');
//        $this->assertQueryContentRegex('table.tbl-calendar-control', '/一次試験結果発表/');
//        $this->assertQueryContentRegex('table.tbl-calendar-control', '/二次試験実施/');
//        $this->assertQueryContentRegex('table.tbl-calendar-control', '/二次試験結果発表/');
//        $this->assertQueryContentRegex('table.tbl-calendar-control', '/実施日/');        
//    }

//    public function testGetHodidaysFromDB() {
//        $holidays = EikenScheduleInquiryService::getHolidays();
//        $this->assertTrue(count($holidays) > 0);
//    }
    
//    public function testGetSchedulesFromDB() {
//        $schedules = EikenScheduleInquiryService::getEikenSchedules();
//        $this->assertTrue(count($schedules) > 0);
//    }

}
