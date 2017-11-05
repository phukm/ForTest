<?php
namespace History;
use History\Service\MappingEikenResultService;
use stdClass;
use History\HistoryConst;

class ListMappingIBAResultTest extends \Dantai\Test\AbstractHttpControllerTestCase{
    
    public function testFakeIdAndPostToConfirmStatusAction()
    {
        $this->login();
        $expectResult = 1;
        
        $result = array();
        $result[0] = 589;
        $result[1] = 590;
        $result[2] = 591;
        $result[3] = 592;
        
//        $data = $this->dispatch('/history/iba/confirm-status', \Zend\Http\Request::METHOD_POST, $result);
    }
}