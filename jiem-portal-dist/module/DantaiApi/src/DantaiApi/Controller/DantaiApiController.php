<?php
namespace DantaiApi\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Application\Service\CommonService;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use DantaiApi\Service\ServiceInterface\DantaiApiServiceInterface;
use Dantai\Api\UkestukeClient;
use Doctrine\ORM\EntityManager;

class DantaiApiController extends AbstractRestfulController
{

    /**
     *
     * @var DantaiApiServiceInterface
     */
    protected $dantaiApiService;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    protected $jsonClient;

    /**
     *
     * @var EntityManager
     */
    protected $em;

    protected $id_org = 0;

    public function __construct(DantaiServiceInterface $dantaiService, DantaiApiServiceInterface $dantaiApiService)
    {
        $this->jsonClient = UkestukeClient::getInstance();
        $this->dantaiService = $dantaiService;
        $this->dantaiApiService = $dantaiApiService;
    }

    protected function getApiConfig(){
        return $this->getServiceLocator()->get('Config')['iba_config']['api'];
    }


    public function getList()
    {
        $results = array(
            'code' => 0
        );
        return new JsonModel(array(
            'data' => $results
        ));
    }

    public function get($id)
    {
        $results = array(
            'api' => 'get Action get'
        );

        return new JsonModel(array(
            'data' => $results
        ));
    }

    public function create($data)
    {
        $result = $this->dantaiApiService->gettingPaymentStatus($data);
        
        $stream = @fopen(DATA_PATH . '/UC24_GettingPaymentStatus.txt', 'a', false);
        if ($stream) {
            $writer = new \Zend\Log\Writer\Stream(DATA_PATH . '/UC24_GettingPaymentStatus.txt');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info(
                    '[UC24]Getting Payment Status : '. \Zend\Json\Json::encode($data) 
                    . '. Response:' . $result
                    );
        }

        $response = $this->getResponse();
        $response->setContent($result);
        return $response;
    }

    public function update($id, $data)
    {}

    public function delete($id)
    {}

    public function eir2c03Action()
    {
        $eikenData = $this->params()->fromPost('eikendata');
        try {
            $eikenData = json_decode($eikenData);
            $result = $this->jsonClient->callEir2c03($this->getApiConfig(), array(
                'dantaino' => $eikenData->orgNo,
            ));
            $response = $this->getResponse();
            $response->setContent(json_encode($result));

            return $response;
        } catch (\Exception $ex) {
            $response = $this->getResponse();
            $response->setContent(json_encode(array('status' => 'fail')));

            return $response;
        }
    }

    public function eir2c02Action()
    {
        $eikenData = $this->params()->fromPost('eikendata');
        try {
            $eikenData = json_decode($eikenData);
            $params = array(
                "jisshiid" => $eikenData->jisshiid,
                "examkbn"  => $eikenData->examkbn,
            );
            $result = $this->jsonClient->callEir2c02($this->getApiConfig(), $params);
            $response = $this->getResponse();
            $response->setContent(json_encode($result));

            return $response;
        } catch (\Exception $ex) {
            $response = $this->getResponse();
            $response->setContent(json_encode(array('status' => 'fail')));

            return $response;
        }


    }
}
