<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/LeaningProgress for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ConsoleInvitation\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ConsoleLearningProgressController extends AbstractActionController {

    public function receiveLearningInfoFromEinaviAction() {
        echo date('[Y-m-d H:i:s e]'), ' Start receive learning info from einavi' . PHP_EOL;
        try {
            $dateExport = $this->params('date', '-1 day');
            $service = new \ConsoleInvitation\Service\LearningProgressService($this->getServiceLocator());
            $service->receiveLearningInfoFromEinavi($dateExport);
        } catch (\Exception $ex) {
            echo $ex->getMessage() . PHP_EOL;
        }
        echo date('[Y-m-d H:i:s e]'), ' End receive learning info from einavi' . PHP_EOL;
    }

    public function sendPersonalIdToEinaviAction() {
        echo date('[Y-m-d H:i:s e]'),' Start send personal to einavi' . PHP_EOL;
        try {
            $dateExport = $this->params('date', '-1 day');
            $service = new \ConsoleInvitation\Service\LearningProgressService($this->getServiceLocator());
            $service->sendPersonalIdToEinavi($dateExport);
        } catch (\Exception $ex) {
            echo $ex->getMessage() . PHP_EOL;
        }
        echo date('[Y-m-d H:i:s e]'),' End send personal to einavi' . PHP_EOL;
    }

    public function InquiryStudyGearAction() {
        echo '================Begin at ' . date('Y-m-d H:i:s') . '==========' . PHP_EOL;
        $timeBegin = microtime();
        try {
            $dateExport = $this->params('date', '-1 day');
            echo 'REQUEST: ' . $dateExport . PHP_EOL;
            $service = new \ConsoleInvitation\Service\LearningProgressService($this->getServiceLocator());
            $arrDt = explode(",", $dateExport);
            for ($i = 0; $i < count($arrDt); $i++) {

                $dateGet = new \DateTime($arrDt[$i]);
                $date = $dateGet->format('Y-m-d');
                $response = $service->processInquiryData($date);
                echo 'RESPONSE: ' . json_encode($response) . PHP_EOL;
            }
        } catch (\Exception $ex) {
            echo $ex->getMessage() . PHP_EOL;
        }
        $timeEnd = microtime();
        echo 'Time excute: '. $timeEnd - $timeBegin . PHP_EOL;
    }

    public function sendBatchPersonalIdToEinaviAction() {
        try {
            $dateExport = $this->params('date', '-1 day');
            $service = new \ConsoleInvitation\Service\LearningProgressService($this->getServiceLocator());
            $service->sendBatchPersonalIdToEinavi($dateExport);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }

    public function receiveBatchLearningInfoFromEinaviAction() {
        $dateExport = $this->params('date', '-1 day');
        $service = new \ConsoleInvitation\Service\LearningProgressService($this->getServiceLocator());
        $service->receiveBatchLearningInfoFromEinavi($dateExport);
    }

}
