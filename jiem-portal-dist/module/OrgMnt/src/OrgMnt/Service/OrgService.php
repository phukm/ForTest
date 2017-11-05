<?php

namespace OrgMnt\Service;

use Application\Entity\ApplyEikenOrg;
use Application\Entity\ApplyEikenOrgDetails;
use Application\Entity\InvitationSetting;
use Application\Entity\SemiVenue;
use Application\Service\DantaiService;
use Dantai\DantaiConstants;
use Dantai\Utility\CharsetConverter;
use Dantai\Utility\PHPExcel;
use OrgMnt\Service\ServiceInterface\OrgServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Doctrine\ORM\EntityManager;

class OrgService implements OrgServiceInterface, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    private $formatTemplateKeihi = array(
        'C' => PHPExcel::EXCEL_NUMERIC,
        'D' => PHPExcel::EXCEL_NUMERIC,
        'E' => PHPExcel::EXCEL_NUMERIC,
        'F' => PHPExcel::EXCEL_NUMERIC,
        'G' => PHPExcel::EXCEL_NUMERIC,
        'H' => PHPExcel::EXCEL_NUMERIC,
        'I' => PHPExcel::EXCEL_NUMERIC,
        'J' => PHPExcel::EXCEL_NUMERIC,
        'K' => PHPExcel::EXCEL_NUMERIC,
        'L' => PHPExcel::EXCEL_NUMERIC,
        'M' => PHPExcel::EXCEL_NUMERIC,
        'N' => PHPExcel::EXCEL_NUMERIC,
        'O' => PHPExcel::EXCEL_CURRENCY,
    );

    private $formatTemplateMoushiKomi = array(
        'E'  => PHPExcel::EXCEL_NUMERIC,
        'I'  => PHPExcel::EXCEL_NUMERIC,
        'J'  => PHPExcel::EXCEL_NUMERIC,
        'K'  => PHPExcel::EXCEL_NUMERIC,
        'L'  => PHPExcel::EXCEL_NUMERIC,
        'M'  => PHPExcel::EXCEL_NUMERIC,
        'N'  => PHPExcel::EXCEL_NUMERIC,
        'O'  => PHPExcel::EXCEL_NUMERIC,
        'P'  => PHPExcel::EXCEL_NUMERIC,
        'Q'  => PHPExcel::EXCEL_NUMERIC,
        'R'  => PHPExcel::EXCEL_NUMERIC,
        'S'  => PHPExcel::EXCEL_NUMERIC,
        'T'  => PHPExcel::EXCEL_NUMERIC,
        'U'  => PHPExcel::EXCEL_NUMERIC,
        'V'  => PHPExcel::EXCEL_NUMERIC,
        'W'  => PHPExcel::EXCEL_NUMERIC,
        'X'  => PHPExcel::EXCEL_NUMERIC,
        'Y'  => PHPExcel::EXCEL_NUMERIC,
        'Z'  => PHPExcel::EXCEL_NUMERIC,
        'AA' => PHPExcel::EXCEL_NUMERIC,
        'AB' => PHPExcel::EXCEL_NUMERIC,
        'AC' => PHPExcel::EXCEL_NUMERIC,
        'AD' => PHPExcel::EXCEL_NUMERIC,
        'AE' => PHPExcel::EXCEL_NUMERIC,
        'AF' => PHPExcel::EXCEL_NUMERIC,
    );

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function getApplyEikenOrgDataExport($scheduleId) {
        $repository = $this->getEntityManager()
                ->getRepository('Application\Entity\ApplyEikenOrg');

        $data = $repository->getDataToExport($scheduleId);
        $listExceptOrg = !empty($data) ? array_column($data, 'organizationId') : null;
        if (!empty($data2 = $repository->getDataToExport2($scheduleId, $listExceptOrg))) {
            $data = array_merge($data, $data2);
        }

        $dataExport = array();

        if (!empty($data)) {
            foreach ($data as $key => &$item) {
                if ($organizationId = $item['organizationId']) {
                    if ($item['typeExamDate']) {
                        switch ($item['typeExamDate']) {
                            case 1:
                                $item['actualExamDate'] = $item['friDate']->format('m/d');
                                break;

                            case 2:
                                $item['actualExamDate'] = $item['satDate']->format('m/d');
                                break;

                            case 3:
                                $item['actualExamDate'] = $item['sunDate']->format('m/d');
                                break;

                            case 4:
                                if($item['actualExamDate'] == 1){
                                    $item['actualExamDate'] = $item['friDate']->format('m/d');
                                }elseif($item['actualExamDate'] == 2){
                                    $item['actualExamDate'] = $item['satDate']->format('m/d');
                                }else{
                                    $item['actualExamDate'] = $item['friDate']->format('m/d') . ', '
                                        . $item['satDate']->format('m/d');
                                }
                                break;
                        }
                    } else {
                        $item['actualExamDate'] = '';
                    }
                    unset($item['typeExamDate']);
                    if($item['locationType'] === 1){
                        $item['locationType'] = '合同';
                    }elseif($item['locationType'] === 0){
                        $item['locationType'] = '単独';
                    }else {
                        $item['locationType'] = '';
                    }
                    unset($item['friDate'], $item['satDate'], $item['sunDate']);

                    $dataExport[$organizationId] = array_slice($item, 0, count($item) - 3, true) + array(
                        'nameKanji' => $item['firtNameKanji'] . ' ' . $item['lastNameKanji'],
                        'telNo' => $item['telNo']
                    );
                }
            }
        }

        return $dataExport;
    }

    /**
     * Function to create data to export all paid apply Eiken of Organization.
     * @param $eikenScheduleId
     * @return array
     */
    public function createPaidExportData($eikenScheduleId) {
        $listPaidApplyEiken = $this->getArrayPaidApplyEiken($eikenScheduleId);
        $listOrg = array_unique(array_column($listPaidApplyEiken, 'organizationId'));
        $em = $this->getEntityManager();
        $listOrg = $em->getRepository('Application\Entity\Organization')->getListOrganizations($listOrg);
        return $this->mappingArrayPaidDataExport($listOrg, $listPaidApplyEiken);
    }

    /**
     * Function to create data to export all data registered and paid apply eiken.
     * @param $eikenScheduleId
     * @return array
     */
    public function createExportData($eikenScheduleId) {
        $listOrg = $this->getApplyEikenOrgDataExport($eikenScheduleId);
        $listRegistered = $this->getArrayRegisteredApplyEiken($eikenScheduleId);
        $listPaid = $this->getArrayPaidApplyEiken($eikenScheduleId);

        return $this->mapping3ArrayDataExport($listOrg, $listRegistered, $listPaid);
    }

    /**
     * Function get array registered number apply eiken of Orgs.
     * @param $eikenScheduleId
     * @return array
     */
    public function getArrayRegisteredApplyEiken($eikenScheduleId) {
        // get data for orgs who not yet create apply eiken
        $listRegisteredNotApplyEikenOrg = $this->getArrayRegisteredNotApplyEikenOrg($eikenScheduleId);

        // get data for orgs who submitted apply eiken
        $listRegisteredSubmittedStandardHall = $this->getArrayRegisteredStandardHall($eikenScheduleId, DantaiConstants::SUBMITTED);
        $listRegisteredSubmittedMainHall = $this->getArrayRegisteredApplyEikenMainHall($eikenScheduleId, DantaiConstants::SUBMITTED);

        // get data for orgs who created draft but not yet submit apply eiken
        $listRegisteredDraftStandardHall = $this->getArrayRegisteredStandardHall($eikenScheduleId, DantaiConstants::DRAFT);
        $listRegisteredDraftMainHall = $this->getArrayRegisteredApplyEikenMainHall($eikenScheduleId, DantaiConstants::DRAFT);

        // map column and data.
        $listRegisteredSubmitted = $this->mappingMainAndStandardHall($listRegisteredSubmittedMainHall, $listRegisteredSubmittedStandardHall);
        $listRegisteredDraft = $this->mappingMainAndStandardHall($listRegisteredDraftMainHall, $listRegisteredDraftStandardHall);

        $listRegisteredSubmitted = array_combine(array_column($listRegisteredSubmitted, 'organizationId'), $listRegisteredSubmitted);
        return $listRegisteredNotApplyEikenOrg + $listRegisteredDraft + $listRegisteredSubmitted;
    }

    public function mappingMainAndStandardHall($listMain, $listStandard) {
        $listRegistered = array_map(function($item1) use ($listMain) {
            $organizationId = $item1['organizationId'];
            $item2 = $this->createMainEmptyData('Registered');

            if (array_key_exists($organizationId, $listMain)) {
                $item2 = $listMain[$organizationId];
                unset($item2['organizationId']);
            }

            return array_merge($item2, $item1);
        }, $listStandard);

        $listRegistered = array_combine(array_column($listRegistered, 'organizationId'), $listRegistered);
        return $listRegistered;
    }

    public function createEmptyData($type) {
        return array_merge($this->createMainEmptyData($type), $this->createStandardEmptyData($type));
    }

    public function createStandardEmptyData($type) {
        return array(
            'standard' . $type . 'Level2' => 0,
            'standard' . $type . 'LevelPre2' => 0,
            'standard' . $type . 'Level3' => 0,
            'standard' . $type . 'Level4' => 0,
            'standard' . $type . 'Level5' => 0
        );
    }

    public function createMainEmptyData($type) {
        return array(
            'main' . $type . 'Level1' => 0,
            'main' . $type . 'LevelPre1' => 0,
            'main' . $type . 'Level2' => 0,
            'main' . $type . 'LevelPre2' => 0,
            'main' . $type . 'Level3' => 0,
            'main' . $type . 'Level4' => 0,
            'main' . $type . 'Level5' => 0
        );
    }

    /**
     * Function to mapping data to create array export data for template moushikomi
     * @param $listOrg
     * @param $listRegisteredApplyEiken
     * @param $listPaidApplyEiken
     * @return array
     */
    public function mapping3ArrayDataExport($listOrg, $listRegisteredApplyEiken, $listPaidApplyEiken) {
        $listMapped = array_map(function($item) use ($listRegisteredApplyEiken, $listPaidApplyEiken) {
            $infoOrg = $item;
            $organizationId = $item['organizationId'];
            $registeredApplyEiken = $this->createEmptyData('Registered');
            $paidApplyEiken = $this->createEmptyData('Paid');

            if (array_key_exists($organizationId, $listRegisteredApplyEiken)) {
                $registeredApplyEiken = $listRegisteredApplyEiken[$organizationId];
                unset($registeredApplyEiken['organizationId']);
            }

            if (array_key_exists($organizationId, $listPaidApplyEiken)) {
                $paidApplyEiken = $listPaidApplyEiken[$organizationId];
                unset($paidApplyEiken['organizationId']);
                unset($paidApplyEiken['totalPaidAmount']);
            }
            unset($infoOrg['organizationId']);

            return array_merge($infoOrg, $registeredApplyEiken, $paidApplyEiken);
        }, $listOrg);

        $listMapped = $this->array_orderby($listMapped, 'organizationNo');
        return $listMapped;
    }

    /**
     * Function to mapping data to create array export data for template paid apply eiken
     * @param $listOrg
     * @param $listPaidApplyEiken
     * @return array
     */
    public function mappingArrayPaidDataExport($listOrg, $listPaidApplyEiken) {
        $listMapped = array_map(function ($item) use ($listOrg) {
            if (array_key_exists($item['organizationId'], $listOrg)) {
                $applyOrgInfo = $listOrg[$item['organizationId']];
                $item['totalPaidAmount'] = number_format($item['totalPaidAmount'], 0, '', ',') . ' 円';
                unset($applyOrgInfo['organizationId']);
                unset($item['organizationId']);

                return array_merge($applyOrgInfo, $item);
            }
        }, $listPaidApplyEiken);

        $listMapped = $this->array_orderby($listMapped, 'organizationNo');
        return $listMapped;
    }

    public function array_orderby($array, $column, $type = SORT_ASC) {
        $dantaiService = $this->getServiceLocator()->get('\Application\Service\DantaiServiceInterface');
        $array = $dantaiService->array_orderby($array, $column, $type);
        return $array;
    }

    public function getArrayPaidApplyEiken($eikenScheduleId) {
        $em = $this->getEntityManager();
        return $em->getRepository('Application\Entity\ApplyEikenLevel')->getArrayPaidApplyEiken($eikenScheduleId);
    }

    public function getArrayRegisteredNotApplyEikenOrg($eikenScheduleId) {
        $em = $this->getEntityManager();
        return $em->getRepository('Application\Entity\ApplyEikenLevel')->getArrayRegisteredNotApplyEikenOrg($eikenScheduleId);
    }

    public function getArrayRegisteredApplyEikenMainHall($eikenScheduleId, $type = DantaiConstants::SUBMITTED) {
        $em = $this->getEntityManager();
        return $em->getRepository('Application\Entity\ApplyEikenLevel')->getArrayRegisteredApplyEikenMainHall($eikenScheduleId, $type);
    }

    public function getArrayRegisteredStandardHall($eikenScheduleId, $type = DantaiConstants::SUBMITTED) {
        $em = $this->getEntityManager();
        return $em->getRepository('Application\Entity\ApplyEikenOrg')->getArrayRegisteredStandardHall($eikenScheduleId, $type);
    }

    public function exportToExcel($data, $response, $filename, $template, $type = 1) {
        $objFileName = new CharsetConverter();
        $filename = $objFileName->utf8ToShiftJis($filename);
        $phpExcel = new PHPExcel();
        $templateFormat = $type == 1 ? $this->formatTemplateKeihi : $this->formatTemplateMoushiKomi;
        $phpExcel->export($data, $filename, $template, 4, '', 'xls', $templateFormat, true);
        return $response;
    }

    public function setSemiMainVenue($orgId, $check)
    {
        $em = $this->getEntityManager();
        /** @var DantaiService $dantaiService */
        $dantaiService = $this->getServiceLocator()->get('\Application\Service\DantaiServiceInterface');
        $eikenScheduleId = $dantaiService->getCurrentEikenSchedule()->id;
        $jsonModel = \Dantai\Utility\JsonModelHelper::getInstance();

        /** @var SemiVenue $semiVenue */
        $semiVenue = $em->getRepository('Application\Entity\SemiVenue')
            ->findOneBy(array(
                            'organizationId'  => $orgId,
                            'eikenScheduleId' => $eikenScheduleId,
                            'isDelete'        => 0,
                        ));
        if(empty($semiVenue)){
            $semiVenue = new SemiVenue();
            $semiVenue->setOrganizationId($orgId);
            $semiVenue->setEikenScheduleId($eikenScheduleId);
        }

        /** @var InvitationSetting $inv */
        $inv = $em->getRepository('Application\Entity\InvitationSetting')
            ->findOneBy(array(
                            'organizationId'  => $orgId,
                            'eikenScheduleId' => $dantaiService->getCurrentEikenSchedule()->id,
                        ));

        //remove Beneficiary value when uncheck semiMainVenue.
        if ($check == 'false') {
            $em->getRepository('Application\Entity\InvitationSetting')
                ->setBeneficiaryForInvitationSetting($orgId, $eikenScheduleId, null);
        }

        // return data.isShowMessage = true when letter generated before.
        $isGenerated = !empty($inv) && $inv->getStatus() == '1';
        if ($isGenerated) {
            $jsonModel->setData(array('isShowMessage' => 'true'));
            $jsonModel->clearMessage();
            $jsonModel->addMessage($this->getServiceLocator()->get('MvcTranslator')->translate('messageSemiMainVenue'));
        } else {
            $jsonModel->setData(array('isShowMessage' => 'false'));
        }

        if(!empty($inv) && $check == 'true'){
            $inv->setHallType(1);
            $em->flush();
        }

        $jsonModel->setSuccess();
        $semiVenue->setSemiMainVenue(($check == 'true')? 1 : 0);
        // case: check semi after gen letter.
        if($isGenerated && $semiVenue->getSemiMainVenueTemp() === null){
            $semiVenue->setSemiMainVenueTemp(0);
        }
        $em->persist($semiVenue);
        $em->flush();
        $em->clear();

        return $jsonModel;
    }
}
