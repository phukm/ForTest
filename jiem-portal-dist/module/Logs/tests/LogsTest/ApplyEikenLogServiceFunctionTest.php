<?php

namespace Logs;

class ApplyEikenLogServiceFunctionTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    
    public function getDataExport()
    {
        $applyEiken = $this->getApplicationServiceLocator()->get('Logs\Service\ApplyEikenServiceInterface');
        
        $header = array(
            'a'=>'A',
            'b'=>'B',
            'c'=>'C'
        );
        $data = array(
            0 => array(
                  'a'=>'1',
                  'x'=>'7',
                  'c'=>'2',
                  'b'=>'3'
            ),
            1 => array(
                  'a'=>'4',
                  'j'=>'4',
                  'b'=>'5',
                  'k'=>'5',
                  'c'=>'6'
            ),
        );
        
        return $applyEiken->convertToExport($data,$header);
    }
    public function getDataMapping()
    {
        $applyEiken = $this->getApplicationServiceLocator()->get('Logs\Service\ApplyEikenServiceInterface');
        $date = new \DateTime('2015-12-30 10:15:30');
        $dataMainDetail = json_encode(array('2級'=>'1→2'));
        $standardDetail = json_encode(array('3級'=>'3→4'));
        $refundDetail = '受験料総額を一旦支払った後、英検より準会場経費または本会場運営費を送金を受ける→準会場経費または本会場運営費の支払は不要';
        $data = array(
            0 => array(
                  'organizationNo'=>'999',
                  'organizationName'=>'organizationName',
                  'userId'=>'userId',
                  'insertAt'=>$date,
                  'action'=>'create',
                  'mainDetail'=>$dataMainDetail,
                  'standardDetail'=>$standardDetail,
                  'refundDetail'=>$refundDetail,
            )
        );
        
        return $applyEiken->mappingDataForExport($data);
    }
    public function testHeaderDataExport(){
        $this->login();
        $data = $this->getDataExport();
        $this->assertEquals($data[0]['a'], 'A');
        $this->assertEquals($data[0]['b'], 'B');
        $this->assertEquals($data[0]['c'], 'C');
    }
    public function testDataExport(){
        $this->login();
        $data = $this->getDataExport();
        $this->assertEquals($data[1]['a'], '1');
        $this->assertEquals($data[1]['c'], '2');
        $this->assertEquals($data[2]['c'], '6');
    }
    public function testRedundancyDataExport(){
        $this->login();
        $data = $this->getDataExport();
        $this->assertEquals(array_key_exists('x',$data[1]), false);
        $this->assertEquals(array_key_exists('k',$data[2]), false);
    }
    public function testMappingData(){
        $this->login();
        $data = $this->getDataMapping();
        $this->assertEquals($data[0]["organizationNo"], '999');
        $this->assertEquals($data[0]["organizationName"], 'organizationName');
        $this->assertEquals($data[0]["userIdAndDateTime"], '2015/12/30 10:15');
        $this->assertEquals($data[0]["userId"], 'userId');
        $this->assertEquals($data[0]["action"], '登録');
    }
    
    public function getCurrentScheduleMock($data = array())
    {
        $current = $this->getMockBuilder('Application\Entity\Repository\EikenScheduleRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $current->expects($this->any())
                ->method('getCurrentKaiByYear')
                ->will($this->returnValue($data));
        return $current;
    }
    
    public function getNextScheduleMock($data = array())
    {
        $next = $this->getMockBuilder('Application\Entity\Repository\EikenScheduleRepository')
                ->disableOriginalConstructor()
                ->getMock();

        $next->expects($this->any())
                ->method('getDeadlineFromOfNextKai')
                ->will($this->returnValue($data));
        return $next;
    }
    
    public function testGetAndConvertDate()
    {
        $this->login();
        $applyEikenLog = $this->getApplicationServiceLocator()->get('Logs\Service\ApplyEikenServiceInterface');
        
        $currentSchedule = array(
            0 => array(
                'id' => 1,
                'year' => date('Y'),
                'kai' => 1,
                'deadlineFrom' => date_create(date('Y-m-d'))
            )
        );
        
        $nextSchedule = array(
            'deadlineFrom' => date_create(date('Y-m-d'))    
        );
        
        $applyEikenLog->getCurrentSchedule($this->getCurrentScheduleMock($currentSchedule));
        $applyEikenLog->getNextSchedule($this->getNextScheduleMock($nextSchedule));
        
        $result = $applyEikenLog->getCurrentAndNextEikenSchedule();
        
        $this->assertEquals($result[0], $currentSchedule[0]['deadlineFrom']->format('Y/m/d'));
        $this->assertEquals($result[1], date(\Dantai\Utility\DateHelper::DATE_FORMAT_DEFAULT, strtotime($nextSchedule['deadlineFrom']->format(\Dantai\Utility\DateHelper::DATE_FORMAT_DEFAULT) . '-1 days')));
        $this->assertEquals($result[2], $currentSchedule[0]['deadlineFrom']->format('Y-m-d'));
        $this->assertEquals($result[3], date(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT, strtotime($nextSchedule['deadlineFrom']->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) . '-1 days')));
    }
}
