<?php

namespace DantaiApi\Service;

use DantaiApi\Service\ServiceInterface\MappingApiServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use History\Service\MappingEikenResultService;
use History\Service\MappingIbaResultService;
use History\HistoryConst;

class MappingApiService implements MappingApiServiceInterface, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    /**
     *
     * @var EntityManager
     */
    protected $em;
    protected $mappingEikenResultService;
    protected $mappingIbaResultService;

    public function processAutoMappingEikenResult($param) {
        $return = false;
        $config = $this->getServiceLocator()->get('config');
        $eikenStatus = $config['Eiken_StatusAutoImport'];
        $orgId = $param['orgId'];
        $orgNo = $param['orgNo'];
        $paramEiken = $param['paramEiken'];
        $applyEikenOrgId = $paramEiken['applyEikenOrgId'];
        $round = (int) $paramEiken['round'];
        $userIdentity = array(
            'organizationId' => $orgId,
            'organizationNo' => $orgNo,
            'organizationName' => '',
            'userId' => 'DantaiApi',
        );
        $objEikenOrg = $this->getEntityManager()->getRepository('\Application\Entity\ApplyEikenOrg')->find($applyEikenOrgId);
        \Dantai\PrivateSession::setData('userIdentity', $userIdentity);
        $this->mappingEikenResultService = new MappingEikenResultService($this->getServiceLocator());
        $importData = $this->mappingEikenResultService->setDataToSave($orgNo, $orgId, $paramEiken['year'], $paramEiken['kai']);
        if ($importData['status'] == HistoryConst::IMPORT_SUCCESS) {
            $result = $this->mappingEikenResultService->mappingDataEikenResult($paramEiken['year'], $paramEiken['kai']);
            if ($result && $result['status'] === HistoryConst::IMPORT_SUCCESS) {
                $round === 1 ? $objEikenOrg->setStatusAutoImport($eikenStatus['Round1Complete']) : $objEikenOrg->setStatusAutoImport($eikenStatus['Round2Complete']);
                $return = true;
            } else {
                $objEikenOrg->setStatusAutoImport($eikenStatus['Failure']);
            }
        } else if ($importData['status'] == HistoryConst::IMPORT_EMPTY_DATA) {
            $round === 1 ? $objEikenOrg->setStatusAutoImport($eikenStatus['NotRun']) : $objEikenOrg->setStatusAutoImport($eikenStatus['Round1Confirmed']);
        } else {
            $objEikenOrg->setStatusAutoImport($eikenStatus['Failure']);
        }
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
        return $return;
    }

    public function processAutoMappingIBAResult($param) {
        $return = false;
        $orgId = $param['orgId'];
        $orgNo = $param['orgNo'];
        $paramIBA = $param['paramIBA'];
        $ibaId = $paramIBA['ibaId'];
        $moshikomiId = $paramIBA['moshikomiId'];
        $year = isset($paramIBA['year']) ? $paramIBA['year'] : date('Y');
        $config = $this->getServiceLocator()->get('config');
        $ibaStatus = $config['IBA_StatusAutoImport'];
        $userIdentity = array(
            'organizationId' => $orgId,
            'organizationNo' => $orgNo,
            'userId' => 'DantaiApi',
        );
        $objIbaOrg = $this->getEntityManager()->getRepository('\Application\Entity\ApplyIBAOrg')->find($ibaId);
        \Dantai\PrivateSession::setData('userIdentity', $userIdentity);
        $this->mappingIbaResultService = new MappingIbaResultService($this->getServiceLocator());
        $importData = $this->mappingIbaResultService->setDataToSave($orgNo, $orgId, $moshikomiId, $ibaId);
        if ($importData['status'] == HistoryConst::IMPORT_SUCCESS) {
            $result = $this->mappingIbaResultService->mappingDataIbaResult($year, $moshikomiId);
            if ($result && $result['status'] === HistoryConst::IMPORT_SUCCESS) {
                $objIbaOrg->setStatusAutoImport($ibaStatus['Complete']);
                $return = true;
            } else {
                $objIbaOrg->setStatusAutoImport($ibaStatus['Failure']);
            }
        } else if ($importData['status'] === HistoryConst::IMPORT_EMPTY_DATA) {
            $objIbaOrg->setStatusAutoImport($ibaStatus['NotRun']);
        } else {
            $objIbaOrg->setStatusAutoImport($ibaStatus['Failure']);
        }
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
        return $return;
    }

    /**
     *
     * @return array|object
     */
    protected function getEntityManager() {
        if (!$this->em)
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        return $this->em;
    }

}
