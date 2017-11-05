<?php

namespace ConsoleInvitation\Controller;

use Dantai\Api\JsonClient;
use Zend\Mvc\Controller\AbstractActionController;
use ConsoleInvitation\Service\EikenService;
use Dantai\Utility\ValidateHelper;

class EikenController extends AbstractActionController {

    const MEMORY_LIMIT = '4096M';

    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }


    
    function dummyEikenAction() {
        $year = $this->params()->fromRoute('year');
        $kai = $this->params()->fromRoute('kai');
        
        if(empty($year) || empty($kai)){
            echo "Error: Please input year and kai greater than 0" . PHP_EOL;

            return false;
        }
        
        if (!ValidateHelper::isYear($year) || !ValidateHelper::isNumeric($kai))
        {
            echo "Error: Year and kai must be integer and greater than 0, year must be YYYY format" . PHP_EOL;

            return false;
        }

        $start = microtime(true);
        $eikenService = new EikenService($this->getServiceLocator());

        if (!empty($year) && !empty($kai)) {
            $eikenService->saveDummyEiken($year, $kai);
            // write Log
            $end = microtime(true);
            echo 'Create dummy apply eiken header kai '.$kai.' '.$year.' [' . date('d-m-Y H:i:s') . '] [Start At: ' . date('d-m-Y H:i:s', $start) . ' , End: ' . date('d-m-Y H:i:s', $end) . ' , Time spent: ' . ($end - $start) . ']' . PHP_EOL;
        }
    }

}
