<?php
/*
 * @Author Vu Hung Tai (taivh)
 */
namespace Eiken;
use Eiken\Service\ExemptionService;
use stdClass;

class ExemptionFunctionServiceTest extends \Dantai\Test\AbstractHttpControllerTestCase
{    
    public function fakeDataForTest()
    {
        $dataSession = new stdClass();
        $dataSession->eikenArray = array();
        $dataSession->eikenArray[0] = new stdClass();
        $dataSession->eikenArray[0]->kekka = '10';
        $dataSession->eikenArray[0]->password = '';
        $dataSession->eikenArray[0]->jukenchino = '';
        $dataSession->eikenArray[0]->nendo = '';
        $dataSession->eikenArray[0]->kai = '';
        $dataSession->eikenArray[0]->eikenid = '';
        $dataSession->eikenArray[0]->kyucd = 1;
        $dataSession->eikenArray[0]->dantaino = '9696969';
        $dataSession->eikenArray[0]->createdt = '';
        $dataSession->eikenArray[0]->updatedt = '';
        $dataSession->eikenArray[0]->seibetsu = 1;
        $dataSession->eikenArray[0]->kojinno = '';
        $dataSession->eikenArray[0]->shimei_kana = 'EXEMPTIONTEST';
        $dataSession->eikenArray[0]->shimei = '';
        $dataSession->eikenArray[0]->shokugyono = 1;
        $dataSession->eikenArray[0]->gakkouno = 1;
        $dataSession->eikenArray[0]->gakunenno = '';
        $dataSession->eikenArray[0]->birthdt = '';
        $dataSession->eikenArray[0]->nenrei = '';
        return $dataSession;
    }

    public function testCallExemptionDataFromApi()
    {
        $expectName = 'EXEMPTIONTEST';
        $exemptionService = new ExemptionService();
        $exemptionService->setServiceLocator($this->getApplicationServiceLocator());
        $uketukeClientMock = $this->getMockBuilder('\Dantai\Api\UkestukeClient')->getMock(); 
        $exemptionData = $this->fakeDataForTest();
        $uketukeClientMock->expects($this->any())
                ->method('callEir2a04')
                ->will($this->returnValue($exemptionData));
        $exemptionService->setExemptionApiClient($uketukeClientMock);
        $config = $this->getApplicationServiceLocator()->get('Config')['orgmnt_config']['api'];
        $result = $exemptionService->getExemptionFromAPI('9696969',$config);
        
        $this->assertEquals($result->eikenArray[0]->shimei_kana, $expectName);
    }

    public function testMappingDataFromApi()
    {
        $exemptionService = new ExemptionService();
        $exemptionService->setServiceLocator($this->getApplicationServiceLocator());
        $uketukeClientMock = $this->getMockBuilder('\Dantai\Api\UkestukeClient')->getMock();
        $exemptionData = $this->fakeDataForTest();
        $uketukeClientMock->expects($this->any())
            ->method('callEir2a04')
            ->will($this->returnValue($exemptionData));
        $exemptionService->setExemptionApiClient($uketukeClientMock);
        $config = $this->getApplicationServiceLocator()->get('Config')['orgmnt_config']['api'];
        $result = $exemptionService->getExemptionFromAPI('9696969',$config);
        $result = $exemptionService->mappingDataFromApi($result->eikenArray);
        $this->assertEquals($result[0]->kyucd, '1級');
        $this->assertEquals($result[0]->seibetsu, '男');
        $this->assertEquals($result[0]->shokugyono, '学生生徒');
        $this->assertEquals($result[0]->gakkouno, '大学');
    }

    public function testCreateExportExcelData()
    {
        $exemptionService = new ExemptionService();
        $exemptionService->setServiceLocator($this->getApplicationServiceLocator());
        $uketukeClientMock = $this->getMockBuilder('\Dantai\Api\UkestukeClient')->getMock();
        $exemptionData = $this->fakeDataForTest();
        $uketukeClientMock->expects($this->any())
            ->method('callEir2a04')
            ->will($this->returnValue($exemptionData));
        $exemptionService->setExemptionApiClient($uketukeClientMock);
        $result = $exemptionService->getExportExcelDataExemptionList('9696969','','');
        $headerApi = $this->getApplicationServiceLocator()->get('Config')['headerExcelExport']['listOfExemptionList']['api'];
        $this->assertEquals(array_keys($result[1]), array_values($headerApi));
    }
}
