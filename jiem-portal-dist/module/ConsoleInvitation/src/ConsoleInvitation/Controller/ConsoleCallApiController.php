<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ConsoleInvitation\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Dantai\Api\UkestukeClient;
use Dantai\Utility\CharsetConverter;

class ConsoleCallApiController extends AbstractActionController
{

    public function sendOrderToEcontextAction()
    {
        $timeBegin = microtime(true);
        $config = $this->getServiceLocator()->get('Config')['creditcard_config'];
        $yesterday = new \DateTime('-1 days');
        echo '===============BEGIN GET ORDER==================' . date('[Y-m-d H:i:s]') . PHP_EOL;
        $getAllOrder = 0;
        list($msgLog, $dataOrder, $dataOrderCancel) = $this->processSendOrder($config, $yesterday, $getAllOrder);
        echo $msgLog;

        $timeEnd = microtime(true);
        echo $dataOrder ? 'Total Order Sent: ' . count($dataOrder) . PHP_EOL : '';
        echo $dataOrderCancel ? 'Total Order Cancel: ' . count($dataOrderCancel) . PHP_EOL : '';
        echo 'Time excute: ' . ($timeEnd - $timeBegin) . PHP_EOL;
        echo '===============END==================' . date('[Y-m-d H:i:s]') . PHP_EOL;
    }

    public function sendAllOrderToEcontextAction()
    {
        $timeBegin = microtime(true);
        $config = $this->getServiceLocator()->get('Config')['creditcard_config'];
        $yesterday = new \DateTime('-1 days');
        echo '===============BEGIN GET ALL ORDER==================' . date('[Y-m-d H:i:s]') . PHP_EOL;
        $getAllOrder = 1;
        list($msgLog, $dataOrder, $dataOrderCancel) = $this->processSendOrder($config, $yesterday, $getAllOrder);
        echo $msgLog;

        $timeEnd = microtime(true);
        echo ($dataOrder ? 'Total Order Sent: ' . count($dataOrder) . PHP_EOL : '');
        echo ($dataOrderCancel ? 'Total Order Cancel: ' . count($dataOrderCancel) . PHP_EOL : '') . PHP_EOL;
        echo 'Time excute: ' . ($timeEnd - $timeBegin) . PHP_EOL;
        echo '===============END==================' . date('[Y-m-d H:i:s]') . PHP_EOL;
    }

    public function processSendOrder(array $config, \DateTime $shipDate, $getAllOrder)
    {
        /* @var $currentEikenSchedule \Application\Entity\EikenSchedule */
        $currentEikenSchedule = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->getCurrentEikenSchedule();
        $eikenScheduleId = $currentEikenSchedule ? $currentEikenSchedule['id'] : 0;
        $response = $this->getDataDuplicatePaymentHaveToDelete($shipDate, $eikenScheduleId);
        echo $response['message'] . PHP_EOL;
        $listKeepPayment = isset($response['data']['listKeepPayment']) ? $response['data']['listKeepPayment'] : array();
        $listDeletePayment = isset($response['data']['listDeletePayment']) ? $response['data']['listDeletePayment'] : array();
        $listPupilDuplicate = isset($response['data']['listPupilDuplicate']) ? $response['data']['listPupilDuplicate'] : array();
        if ($listDeletePayment) {
            echo 'Delete Payment Duplicate by format PupilId-EikenLevelId-Price: ' . json_encode(array_keys($listPupilDuplicate)) . PHP_EOL;
            $this->setDeleteForListDuplicate($listDeletePayment);
        }
        $msgLog = '';
        list($msgLogSendOrder, $dataOrder) = $this->sendCreditCardOrder($shipDate, $config, $getAllOrder);
        list($msgLogCancelOrder, $dataOrderCancel) = $this->cancelCreditCardOrder($shipDate, $config, $getAllOrder);

        if ($msgLogSendOrder != '' || $msgLogCancelOrder !== '') {
            $msgLog .= $msgLogSendOrder;
            $msgLog .= $msgLogCancelOrder;
        } else {
            $msgLog = 'Empty Order' . PHP_EOL;
        }

        return array($msgLog, $dataOrder, $dataOrderCancel);
    }

    public function getDataDuplicatePaymentHaveToDelete($date, $eikenScheduleId)
    {
        $delimiter = '-';
        $response = array('status' => 0, 'message' => '');
        $datetime = $date ? $date->format('Y-m-d') : '';
        $listPupilDuplicate = $this->getEntityManager()->getRepository('Application\Entity\IssuingPayment')->getDataDuplicateCreditCardByDate($datetime, $eikenScheduleId);
        if (!$listPupilDuplicate) {
            $response['status'] = 1;
            $response['message'] = 'Do not exist duplicate for this date: ' . $datetime;
            return $response;
        }
        $listPupil = array();
        foreach ($listPupilDuplicate as $key => $value) {
            if ($value['totalPayment'] > 1 && $value['totalRetrieve'] > 1) {
                $key = $value['pupilId'] . $delimiter . $value['eikenLevelId'] . $delimiter . $value['price'];
                $listPupil[$key] = $value;
            }
        }
        if (!$listPupil) {
            $response['status'] = 1;
            $response['message'] = 'Do not exist duplicate info and duplicate retrieve for this date: ' . $datetime;
            return $response;
        }
        $arrDuplicateKey = array_keys($listPupil);
        $listDataPayment = $this->getEntityManager()->getRepository('Application\Entity\IssuingPayment')->getDataPaymentByListDuplicate($arrDuplicateKey, $eikenScheduleId);
        $listDeletePayment = array();
        $listKeepPayment = array();
        foreach ($listDataPayment as $key => $value) {
            $key = $value['pupilId'] . $delimiter . $value['eikenLevelId'] . $delimiter . $value['price'];
            if (isset($listKeepPayment[$key])) {
                $listDeletePayment[] = $value;
            } else {
                $listKeepPayment[$key] = $value;
            }
        }
        $response['status'] = 1;
        $response['data']['listKeepPayment'] = $listKeepPayment;
        $response['data']['listDeletePayment'] = $listDeletePayment;
        $response['data']['listPupilDuplicate'] = $listPupil;
        $response['message'] = 'Exist duplicate for this date: ' . $datetime;
        return $response;
    }

    public function setDeleteForListDuplicate($listDeletePayment)
    {
        if (!$listDeletePayment || !is_array($listDeletePayment)) {
            return false;
        }
        try {
            $listPaymentInfoIds = array();
            $listIssuingPaymentIds = array();
            $listRetrieveIds = array();
            foreach ($listDeletePayment as $key => $value) {
                $listPaymentInfoIds[] = $value['paymentInfoId'];
                $listIssuingPaymentIds[] = $value['issuingId'];
                $listRetrieveIds[] = $value['retrieveId'];
            }
            $this->getEntityManager()->getRepository('Application\Entity\IssuingPayment')->deleteDataByListIds(array_unique($listIssuingPaymentIds));
            $this->getEntityManager()->getRepository('Application\Entity\PaymentInfo')->deleteDataByListIds(array_unique($listPaymentInfoIds));
            $this->getEntityManager()->getRepository('Application\Entity\RetrieveBillingInfo')->deleteDataByListIds(array_unique($listRetrieveIds));
            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function sendCreditCardOrder($shipDate, $config, $getAllOrder)
    {
        $executeDate = $shipDate ? $shipDate->format('Y-m-d') : '';
        $retrieveBillingRepo = $this->getEntityManager()->getRepository('Application\Entity\RetrieveBillingInfo');
        $dataOrder = $retrieveBillingRepo->getDataOrderCreditInDateBySiteCode($executeDate, $config['site_code'], $getAllOrder);
        $msgLog = '';
        if ($dataOrder) {
            $msgLog .= 'Send Order At ' . $executeDate . ': ' . PHP_EOL;
            foreach ($dataOrder as $value) {
                $timeBeginCallApi = microtime(true);
                try {
                    $parameterApi = array(
                        'shopID' => $config['site_code'],
                        'shipDate' => $shipDate ? $shipDate->format('Y/m/d') : '',
                        'orderID' => $value['orderId']
                    );

                    $configApi = $config['api_send_order'];
                    $result = UkestukeClient::getInstance()->callEconRcvEnd($configApi, $parameterApi);
                    $result['message'] = CharsetConverter::shiftJisToUtf8($result['message']);
                    $response = implode(' ', $result);
                } catch (\Exception $ex) {
                    $response = 'Error: ' . $ex->getMessage();
                }
                $timeEndCallApi = microtime(true);
                $timeExcuteCallApi = 'Time excute: ' . ($timeEndCallApi - $timeBeginCallApi);
                $msgLog .= $timeExcuteCallApi . ' | REQUEST: ' . json_encode($parameterApi) . ' | REPONSE: ' . $response . PHP_EOL;
            }
        }
        return array($msgLog, $dataOrder);
    }

    public function cancelCreditCardOrder($shipDate, $config, $getAllOrder)
    {
        $executeDate = $shipDate ? $shipDate->format('Y-m-d') : '';
        $isDelete = 1;
        $retrieveBillingRepo = $this->getEntityManager()->getRepository('Application\Entity\RetrieveBillingInfo');
        $dataOrderCancel = $retrieveBillingRepo->getDataOrderCreditInDateBySiteCode($executeDate, $config['site_code'], $getAllOrder, $isDelete);
        $msgLog = '';
        if ($dataOrderCancel) {
            $msgLog .= 'Cancel Order At ' . $executeDate . ': ' . PHP_EOL;
            foreach ($dataOrderCancel as $value) {
                $timeBeginCallApi = microtime(true);
                try {
                    $parameterApi = array(
                        'shopID' => $config['site_code'],
                        'orderID' => $value['orderId'],
                        'chkCode' => $config['api_cancel_order']['chkCode'],
                        'ordAmount' => $value['ordAmount'],
                    );

                    $configApi = $config['api_cancel_order'];
                    $result = UkestukeClient::getInstance()->callEconRcvCancelOrder($configApi, $parameterApi);
                    $result['message'] = CharsetConverter::shiftJisToUtf8($result['message']);
                    $response = implode(' ', $result);
                } catch (\Exception $ex) {
                    $response = 'Error: ' . $ex->getMessage();
                }
                $timeEndCallApi = microtime(true);
                $timeExcuteCallApi = 'Time excute: ' . ($timeEndCallApi - $timeBeginCallApi);
                $msgLog .= $timeExcuteCallApi . ' | REQUEST: ' . json_encode($parameterApi) . ' | REPONSE: ' . $response . PHP_EOL;
            }
        }
        return array($msgLog, $dataOrderCancel);
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
}

