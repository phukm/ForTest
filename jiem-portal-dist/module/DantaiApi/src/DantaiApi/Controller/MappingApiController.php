<?php

namespace DantaiApi\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use DantaiApi\Service\ServiceInterface\MappingApiServiceInterface;
use Doctrine\ORM\EntityManager;
use Zend\Json\Json;
use Zend\Json\Encoder;
use Zend\Json\Decoder;

class MappingApiController extends AbstractRestfulController {

    /**
     *
     * @var MappingApiServiceInterface
     */
    protected $mappingApiService;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(DantaiServiceInterface $dantaiService, MappingApiServiceInterface $mappingApiService) {
        $this->dantaiService = $dantaiService;
        $this->mappingApiService = $mappingApiService;
    }

    public function getList() {
        $results = array(
            'code' => 0
        );
        return new JsonModel(array(
            'data' => $results
        ));
    }

    public function get($id) {
        $results = array(
            'api' => 'get Action get'
        );

        return new JsonModel(array(
            'data' => $results
        ));
    }

    public function create($data) {
        $result = false;
        try {
            if ($data) {
                $data = json_decode($data['eikendata'], true);
                $type = $data['type'];
                if ($type == 'EIKEN') {
                    $result = $this->mappingApiService->processAutoMappingEikenResult($data);
                } else if ($type == 'IBA') {
                    $result = $this->mappingApiService->processAutoMappingIBAResult($data);
                } else {
                    $result = false;
                }
            }
            $this->writelog($data, $type, $result);

            return new JsonModel(array(
                                     'status' => true,
                                 ));
        } catch (\Exception $ex) {
            return new JsonModel(array(
                                     'status' => false,
                                 ));
        }
    }

    protected function writelog($data,$type,$result) {
        $stream = @fopen(DATA_PATH . '/AutoMapping.txt', 'a', false);
        if ($stream) {
            $writer = new \Zend\Log\Writer\Stream(DATA_PATH . '/AutoMapping.txt');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info(
                    $type.' : ' . \Zend\Json\Json::encode($data)
                    . '. Response:' . $result
            );
        }
    }

    public function update($id, $data) {
        
    }

    public function delete($id) {
        
    }

}
