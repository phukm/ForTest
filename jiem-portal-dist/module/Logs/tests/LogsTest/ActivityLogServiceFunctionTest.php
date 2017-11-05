<?php

namespace Logs;
use Zend\I18n\View\Helper\CurrencyFormat;
class ActivityLogServiceFunctionTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    
    public function getDataExport()
    {
        $activityService = $this->getApplicationServiceLocator()->get('Logs\Service\ActivityServiceInterface');
        $date = new \DateTime('2015-12-30 10:15:30');
        $header = array(
            'a'=>'A',
            'b'=>'B',
            'c'=>'C',
            'insertAt'=>'insertAt'
        );
        $data = array(
            0 => array(
                  'a'=>'1',
                  'x'=>'7',
                  'c'=>'2',
                  'b'=>'3',
                  'insertAt'=>$date
            ),
            1 => array(
                  'a'=>'4',
                  'j'=>'4',
                  'b'=>'5',
                  'k'=>'5',
                  'c'=>'6'
            ),
        );
        
        return $activityService->convertToExport($data,$header);
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
        $this->assertEquals($data[1]['insertAt'], '2015/12/30 10:15');
        $this->assertEquals($data[2]['c'], '6');
    }
    public function testRedundancyDataExport(){
        $this->login();
        $data = $this->getDataExport();
        $this->assertEquals(array_key_exists('x',$data[1]), false);
        $this->assertEquals(array_key_exists('k',$data[2]), false);
    }
}
