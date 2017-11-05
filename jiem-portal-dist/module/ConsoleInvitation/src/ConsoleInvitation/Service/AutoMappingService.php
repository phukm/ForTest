<?php

/**
 * @description this function process business for Auto Mapping Eiken and IBA
 */

namespace ConsoleInvitation\Service;

use Application\Entity\Repository\ApplyIBAOrgRepository;
use ConsoleInvitation\ConsoleInvitationConst;
use Dantai\Api\UkestukeClient;
use Dantai\PrivateSession;
use Dantai\Utility\DateHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class AutoMappingService implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;


    protected $sl;
    protected $em;
    protected $jsonClient;
    protected $applyIbaOrgRepo;
    protected $apiConfig;
    protected $ibaStatus;
    protected $eikenStatus;
    protected $mappingIbaResultService;
    protected $mappingEikenResultService;

    const PERIOD_TIME = '-1 year';

    // constructor
    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        $this->setServiceLocator($serviceManager);
        $this->sl = $this->getServiceLocator();
        if ($this->sl) {
            $this->em = $this->getEntityManager();
        }
        $this->jsonClient = UkestukeClient::getInstance();
        $this->apiConfig = $this->getServiceLocator()->get('Config')['iba_config']['api'];
        $this->ibaStatus = $this->getServiceLocator()->get('config')['IBA_StatusAutoImport'];
        $this->eikenStatus = $this->getServiceLocator()->get('config')['Eiken_StatusAutoImport'];
        $this->mappingIbaResultService = $this->getServiceLocator()->get('History\Service\MappingIbaResultService');
        $this->mappingEikenResultService = $this->getServiceLocator()->get('History\Service\MappingEikenResultServiceFactory');
    }

    /**
     * @return array|object
     */
    public function getEntityManager() {
        return isset($this->em) ? $this->em : $this->sl->get('doctrine.entitymanager.orm_default');
    }

    /**
     * @return ApplyIBAOrgRepository
     */
    public function getIbaOrgRepo(){
        return isset($this->applyIbaOrgRepo) ? $this->applyIbaOrgRepo : $this->getEntityManager()->getRepository('\Application\Entity\ApplyIBAOrg');
    }

    public function processAutoMappingIBA($orgId, $orgNo) {
        if (empty($orgNo) || empty($orgId)) {
            return false;
        }

        $result = $this->checkHeaderIBAFromUketsuke($orgId, $orgNo);

        if ($result) {
            return $this->importIBATestResult($orgId, $orgNo);
        }

        return false;
    }

    public function processAutoMappingEiken($params) {
        if (empty($params)) return false;

        $orgId = $params['orgId'];
        $orgNo = $params['orgNo'];
        $paramEiken = $params['paramEiken'];
        $applyEikenOrgId = $paramEiken['applyEikenOrgId'];
        $year = $paramEiken['year'];
        $kai = $paramEiken['kai'];
        $round = (int)$paramEiken['round'];

        $result = $this->importEikenTestResult($orgNo, $orgId, $applyEikenOrgId, $year, $kai, $round);
        if ($result) {
            return true;
        }

        return false;
    }

    public function checkHeaderIBAFromUketsuke($orgId, $orgNo){
        echo 'Process check header IBA...' . PHP_EOL;

        try {
            $result = $this->callEir2c03($orgNo);
            if (isset($result->kekka)) {
                $kekka = $result->kekka;
            } else {
                if (!isset($result->eikenArray)) {
                    $kekka = '99'; // error
                } elseif (count($result->eikenArray) == 1 && $result->eikenArray[0]->jisshiid == '') {
                    $kekka = '02'; // empty data
                } else {
                    $kekka = $result->eikenArray[0]->kekka;
                }
            }

            if ($kekka == '10') {
                echo 'Save and update header' . PHP_EOL;
                $arrayData = !empty($result->eikenArray) ? $result->eikenArray : array();
                $this->saveAndUpdateIBAHeader($orgId, json_decode(json_encode($arrayData), true));

                echo 'Done process check header IBA.' . PHP_EOL;

                return true;
            } else {
                echo 'FAIL'. $kekka . ': ' . json_encode($result) . PHP_EOL;

                return false;
            }
        } catch (\Exception $e) {
            echo 'FAIL!'.PHP_EOL;
            return false;
        }
    }

    public function saveAndUpdateIBAHeader($orgId, $listIbaHeader){
        $ibaRepo = $this->getIbaOrgRepo();

        $listExistIBA = $ibaRepo->getListExistIBAOrg($listIbaHeader);
        $listNewIBA = $this->filterIBAHeaderNotIn($listIbaHeader, $listExistIBA);
        $listExistIBA = $this->filterIBAHeader($listIbaHeader, $listExistIBA);
        $userIdentity = PrivateSession::getData('userIdentity');
        echo 'insert new: '. count($listNewIBA) . PHP_EOL;
        $ibaRepo->insertNewIBAHeader($orgId, $userIdentity['organizationNo'], $userIdentity['userId'], $listNewIBA);
        echo 'update: '. count($listExistIBA) . PHP_EOL;
        $ibaRepo->updateFlagNewDataIBAHeader($userIdentity['organizationNo'], $userIdentity['userId'], $listExistIBA);
    }

    public function importIBATestResult($orgId, $orgNo){
        echo 'Process import IBA...' . PHP_EOL;
        $return = false;
        $ibaRepo = $this->getIbaOrgRepo();
        $testDate = date(DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT, strtotime(AutoMappingService::PERIOD_TIME));
        $ibaHeaders = $ibaRepo->getListIBAHasNewData($orgId, $testDate);
        if(empty($ibaHeaders)){
            echo 'No IBA header to import' . PHP_EOL;
            return $return;
        }
        echo 'Import from Uketuke' . PHP_EOL;
        // import IBA result into DB.
        foreach ($ibaHeaders as $key => $ibaHeader) {
            $this->changeStatusAutoMappingIBA($ibaHeader['id'], $this->ibaStatus['Running']);
            $response = $this->processImportIBA($orgId,$orgNo, $ibaHeader['jisshiId'], $ibaHeader['examType'], $ibaHeader['id']);
            $ibaHeaders[$key] = array_merge($ibaHeader, $response);
        }
        echo 'Done import from Uketuke' . PHP_EOL;

        echo 'Mapping IBATestResult' . PHP_EOL;
        // process mapping IBA result.
        foreach ($ibaHeaders as $ibaHeader) {
            $year = $ibaHeader['year'];
            $jisshiId = $ibaHeader['jisshiId'];
            $examType = $ibaHeader['examType'];
            if ($ibaHeader['status'] == ConsoleInvitationConst::IMPORT_SUCCESS) {
                $result = $this->mappingIbaResultService->mappingDataIbaResult($year, $jisshiId, $examType);
                if ($result && $result['status'] === ConsoleInvitationConst::STATUS_MAPPED) {
                    $this->changeStatusAutoMappingIBA($ibaHeader['id'], $this->ibaStatus['Complete']);
                    $return = true;
                } else {
                    $this->changeStatusAutoMappingIBA($ibaHeader['id'], $this->ibaStatus['Failure\'']);
                }
            } else if ($ibaHeader['status'] === ConsoleInvitationConst::IMPORT_EMPTY_DATA) {
                $this->changeStatusAutoMappingIBA($ibaHeader['id'], $this->ibaStatus['NotRun']);
            } else {
                $this->changeStatusAutoMappingIBA($ibaHeader['id'], $this->ibaStatus['Failure']);
            }
            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear();
        }
        echo 'Done mapping IBATestResult' . PHP_EOL;
        echo 'Done process import IBA.' . PHP_EOL;
        return $return;
    }

    public function importEikenTestResult($orgNo, $orgId, $eikenOrgId, $year, $kai, $round) {
        echo 'Process import Eiken...' . PHP_EOL;
        $return = false;
        echo 'Import from Uketsuke' . PHP_EOL;
        $importData = $this->mappingEikenResultService->setDataToSave($orgNo, $orgId, $year, $kai);
        if ($importData['status'] == ConsoleInvitationConst::IMPORT_SUCCESS) {
            echo 'Done import from Uketsuke' . PHP_EOL;
            echo 'Mapping EikenTestResult' . PHP_EOL;
            $result = $this->mappingEikenResultService->mappingDataEikenResult($year, $kai);
            if ($result && $result['status'] === ConsoleInvitationConst::STATUS_MAPPED) {
                $round === 1 ? $this->changeStatusAutoMappingEiken($eikenOrgId, $this->eikenStatus['Round1Complete']) : $this->changeStatusAutoMappingEiken($eikenOrgId, $this->eikenStatus['Round2Complete']);
                $return = true;
                echo 'Done mapping EikenTestResult' . PHP_EOL;
            } else {
                $this->changeStatusAutoMappingEiken($eikenOrgId, $this->eikenStatus['Failure']);
                echo 'Error mapping EikenTestResult' . PHP_EOL;
            }
        } else if ($importData['status'] == ConsoleInvitationConst::IMPORT_EMPTY_DATA) {
            echo 'Import Empty from Uketsuke' . PHP_EOL;
            $round === 1 ? $this->changeStatusAutoMappingEiken($eikenOrgId, $this->eikenStatus['NotRun']) : $this->changeStatusAutoMappingEiken($eikenOrgId, $this->eikenStatus['Round1Confirmed']);
        } else {
            echo 'Error importing from Uketsuke' . PHP_EOL;
            $this->changeStatusAutoMappingEiken($eikenOrgId, $this->eikenStatus['Failure']);
        }
        echo 'Done process import Eiken' . PHP_EOL;

        return $return;
    }

    public function processImportIBA($orgId, $orgNo, $jisshiId, $examType, $ibaId){
        $response = array(
            'status' => ConsoleInvitationConst::IMPORT_FAILED, 'message' => 'Failed',
        );
        try{
            $result = $this->mappingIbaResultService->getIBAExamResult($jisshiId, $examType);
            // use when ip worker can't call api of uketsuke
            //$result = $this->callEir2c02($jisshiId, $examType);
            if (isset($result->kekka)) {
                $kekka = $result->kekka;
            } else {
                if (count($result) == 1 && $result->eikenArray[0]->eikenid == '') {
                    $kekka = '02';
                } else {
                    $kekka = $result->eikenArray[0]->kekka;
                }
            }

            if ($kekka == '10') {
                $response = $this->mappingIbaResultService->saveIBAExamResult($orgNo, $orgId, $jisshiId, $examType, $result, $ibaId);
            } else if ($kekka == '02') {
                $response['status'] = ConsoleInvitationConst::IMPORT_EMPTY_DATA;
                $response['message'] = 'Empty Data';
            }
        } catch (\Exception $ex) {
            $response = array(
                'status' => ConsoleInvitationConst::IMPORT_FAILED,
                'message' => $ex->getMessage(),
            );
        }

        return $response;
    }

    public function changeStatusAutoMappingIBA($id, $status) {
        $em = $this->getEntityManager();
        $objIba = $em->getRepository('\Application\Entity\ApplyIBAOrg')->find($id);
        if ($objIba) {
            $objIba->setStatusAutoImport($status);
            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear();

            return true;
        }
        return false;
    }

    public function changeStatusAutoMappingEiken($id, $status) {
        $em = $this->getEntityManager();
        $objEikenOrg = $em->getRepository('\Application\Entity\ApplyEikenOrg')->find($id);
        if ($objEikenOrg) {
            $objEikenOrg->setStatusAutoImport($status);
            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear();

            return true;
        }
        return false;
    }

    public function callEir2c03($orgNo){
        return $this->jsonClient->callEir2c03($this->apiConfig, array(
            'dantaino' => $orgNo,
        ));

        // use this comment when worker can't call api of uketsuke
        //$host = PrivateSession::getData('userIdentity')['host'];
        //$env = getenv('APP_ENV') ?  : 'production';
        //$config = array(
        //    'protocol' => '',
        //    'end_point' => $host,
        //    'sslverifypeer' => ($env == 'production'),
        //    'timeout' => 0,
        //);
        //$params = array(
        //    'orgNo' => $orgNo,
        //);
        //$result = $this->jsonClient->callDantaiApiEir2c03($config, $params);
    }

    //public function callEir2c02($jisshiId, $examType){
    //    echo $jisshiId . ':' . $examType . PHP_EOL;
    //    $env = getenv('APP_ENV') ?  : 'production';
    //    $host = PrivateSession::getData('userIdentity')['host'];
    //    $config = array(
    //        'protocol' => '',
    //        'end_point' => $host,
    //        'sslverifypeer' => ($env == 'production'),
    //        'timeout' => 0,
    //    );
    //    $params = array(
    //        'jisshiid' => $jisshiId,
    //        'examkbn'  => $examType,
    //    );
    //    $result = $this->jsonClient->callDantaiApiEir2c02($config, $params);
    //    return $result;
    //}

    public function callDantaiApiMappingEiken($params){
        $env = getenv('APP_ENV') ?  : 'production';
        $host = PrivateSession::getData('userIdentity')['host'];
        $config = array(
            'protocol' => '',
            'end_point' => $host,
            'sslverifypeer' => ($env == 'production'),
            'timeout' => 0,
        );
        $result = $this->jsonClient->callDantaiApiMappingEiken($config, $params);
        return $result;
    }

    public function filterIBAHeaderNotIn($listIbaHeader, $filterList){
        $result = array_filter($listIbaHeader, function($item) use ($filterList) {
            $value = array('jisshiid' => $item['jisshiid'], 'examkbn' => $item['examkbn']);
            if (!in_array($value, $filterList)) {
                return $item;
            }
        });

        return array_values($result);
    }

    public function filterIBAHeader($listIbaHeader, $filterList){
        $result = array_filter($listIbaHeader, function($item) use ($filterList) {
            $value = array('jisshiid' => $item['jisshiid'], 'examkbn' => $item['examkbn']);
            if (in_array($value, $filterList)) {
                return $item;
            }
        });

        return array_values($result);
    }
}
