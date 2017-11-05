<?php

namespace History\Service;

use Application\Entity\EikenTestResult;
use History\HistoryConst;
use History\Service\ServiceInterface\EikenHistoryServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Dantai\PrivateSession;
use Dantai\PublicSession;
use History\Form\SearchInquiryEikenForm;
use Zend\Json\Json;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class EikenHistoryService implements EikenHistoryServiceInterface, ServiceLocatorAwareInterface {

    use \Application\Controller\ControllerAwareTrait;

use ServiceLocatorAwareTrait;

    private $id_org;
    protected $em;
    protected $organizationNo;

    public function __construct() {
        $user = PrivateSession::getData('userIdentity');
        $this->id_org = $user['organizationId'];
        $this->organizationNo = $user['organizationNo'];
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function getListInquiryEiken($sortOrder, $sortKey, $searchVisible, $page, $limit, $offset, $post, $searchCriteria, $routeMatch, $request, $params, $flashMessenger, $dantaiService, $redirect, $messages) {
        $yearNo = PrivateSession::getData('yearNo');
        $kaiNo = PrivateSession::getData('kaiNo');
        if (empty($yearNo) && empty($kaiNo)) {
            return $redirect->toRoute('history/default', array(
                        'controller' => 'eiken',
                        'action' => 'exam-result'
            ));
        }
        $form = new SearchInquiryEikenForm();
        $em = $this->getEntityManager();
        if ($post && $searchCriteria['token']) {
            return $redirect->toUrl('/history/eiken/pupil-achievement/search/' . $searchCriteria['token']);
        }
        $objSchoolyear = $this->getSchoolYearCode($this->organizationNo, $yearNo, $kaiNo);
        $listOrgSchoolYear = array();
        if (isset($objSchoolyear)) {
            $listOrgSchoolYear[''] = '';
            foreach ($objSchoolyear as $key => $value) {
                $listOrgSchoolYear[$value['schoolYearName']] = $value['schoolYearName'];
            }
        }

        $dataAttrForm = array(
            'value' => '',
            'selected' => true,
            'escape' => false
        );
        if ($searchCriteria['orgSchoolYear']) {
            $dataAttrForm['value'] = $searchCriteria['orgSchoolYear'];
        }

        $form->get('orgSchoolYear')
             ->setValueOptions($listOrgSchoolYear)
             ->setAttributes($dataAttrForm);

        $objClass = $this->getClassCode($this->organizationNo, $yearNo, $kaiNo);
        $listClass = array();
        // change key and value ListClass
        if (isset($objClass)) {
            $listClass[''] = '';
            foreach ($objClass as $key => $value) {
                $listClass[$key] = $value['className'];
            }
        }
        if ($searchCriteria['classj'] != NULL) {
            $dataAttrForm['value'] = $searchCriteria['classj'];
            $searchCriteria['classj'] = $listClass[$searchCriteria['classj']];
        }
        $form->get('classj')
             ->setValueOptions($listClass)
             ->setAttributes($dataAttrForm);

        $name = '';
        if ($searchCriteria['name'] != NULL) {
            $name = $searchCriteria['name'];
        }
        $form->get('name')->setAttributes(array(
            'value' => $name
        ));

        if (isset($searchName)) {
            $searchName = $this->remove_special_characters(trim($searchName));
        }
        $config = $this->getServiceLocator()->get('config');
        $classification = $config['School_Code'];
        $paginator = $em->getRepository('Application\Entity\EikenTestResult')->getDataInquiryEiken($this->organizationNo, $searchCriteria);

        return array(
            'pupilAchievementList' => $paginator->getAllItems(),
            'inquiryeiken' => $paginator->getItems($offset, $limit),
            'form' => $form,
            'page' => $page,
            'sessionYear' => $yearNo,
            'sessionKai' => $kaiNo,
            'paginator' => $paginator,
            'numPerPage' => $limit,
            'param' => isset($searchCriteria['token']) ? $searchCriteria['token'] : '',
            'sortOrder' => $sortOrder,
            'sortKey' => $sortKey,
            'searchVisible' => $searchVisible,
            'classification' => $classification,
            'noRecordExcel'  => $messages,
            'roleLimit' => PublicSession::isDisableDownloadButtonRole() || PublicSession::isViewerRole(),
        );
    }
    public function remove_special_characters($string) {
        $string = str_replace(array(
            "'",
            '"'
                ), array(
            "",
            ""
                ), $string);
        $string = trim($string);

        return $string;
    }

    /**
     * Service function that call uketsuke API : /step-eir/EIR2B01
     *
     * @param string $organizationNo
     *            (dantaino)
     * @param string $year
     *            (nendo)
     * @param string $term
     *            (kai)
     */
    public function getEikenExamResult($organizationNo, $year, $term) {
        $config = $this->getServiceLocator()->get('Config')['eiken_config']['api'];
        // api parameters
        $params = array(
            "dantaino" => $organizationNo,
            "nendo" => $year,
            "kai" => $term
        );
        $result = \Dantai\Api\UkestukeClient::getInstance()->callEir2b01($config, $params);

        return $result;
    }

    /**
     *
     * @param mix $getEikenExamResult
     *            ( /step-eir/EIR2B01 output )
     */
    public function saveEikenExamResult($getEikenExamResult, $organizationNo, $organizationId, $eikenScheduleId, $year, $kai) {
        $em = $this->getEntityManager();
        $data = (array) $getEikenExamResult->eikenArray;
        $em->getConnection()->beginTransaction();
        try {
            $list_EikenScore = $em->getRepository('Application\Entity\EikenTestResult')->getListIdEikenTestResult($kai, $year, $organizationNo);
            $em->getRepository('Application\Entity\EikenScore')->deleteEikenScore($list_EikenScore);
            $eikenTestResultArray = array();
            // create data from API
            foreach ($data as $item) {
                // check null birthday
                if ($item->birthday == null) {
                    $item->birthday = null;
                }
                $eikenTestResultArray[] = $this->mappingDataFromUkestuke($item);
            }
            // insert into DB
            $this->insertOnDuplicateUpdateMultiple($eikenTestResultArray);
            $this->updateTempValueAfterImport($organizationNo, $year, $kai);

            $em->flush();
            if ($organizationId != '' && $eikenScheduleId != '') {
                $em->getRepository('Application\Entity\ApplyEikenOrg')->updateStatusAndTotalImporting($organizationId, $eikenScheduleId, count($data));
            }
            $em->getConnection()->commit();
            /**
             * @author minhbn1
             * add org To queue
             */
            $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
            $dantaiService->addOrgToQueue($this->id_org, $year);
            //
        } catch (Exception $e) {
            $em->getConnection()->rollback();
        }
    }

    /**
     * AnNV6 UC11
     * Get Year and Kai of UC9 from param and set into session
     */
    public function setSessionKaiAndYear($params, $redirect) {
        $isset_post = $params->fromRoute();
        $year = date('Y');
        $kai = 0;
        if (isset($isset_post)) {
            $year = $isset_post['year'];
            $kai = $isset_post['kai'];
        }
        //set yearNo, kaiNo into session
        PrivateSession::setData('yearNo', $year);
        PrivateSession::setData('kaiNo', $kai);

        return $redirect->toRoute('history/default', array(
                    'controller' => 'eiken',
                    'action' => 'pupil-achievement'
        ));
    }

    public function getHistoryPupilEiken($redirect, $params, $page, $limit, $offset, $routeMatch, $searchCriteria,$messages) {
        $em = $this->getEntityManager();
        $type = ($searchCriteria['type'] != null) ? $searchCriteria['type'] : '';
        $schoolyear = ($searchCriteria['schoolYear'] != null) ? $searchCriteria['schoolYear'] : '';
        $class = ($searchCriteria['className'] != null) ? $searchCriteria['className'] : '';
        $number = ($searchCriteria['pupilNumber'] != null) ? $searchCriteria['pupilNumber'] : '';
        $name = ($searchCriteria['name'] != null) ? $searchCriteria['name'] : '';
        $config = $this->getServiceLocator()->get('config');
        $classification = $config['OrganizationClass'];
        $eikenTotal = $config['IBAEikenLevelTotal'];
        $eikenReadAndListen = $config['IBAEikenLevelReadListen'];
        $paginator = $em->getRepository('Application\Entity\EikenTestResult')->getHistoryPupilEiken($searchCriteria, $this->organizationNo);
        $currentKai = $em->getRepository('Application\Entity\EikenSchedule')->getCurrentEikenSchedule();

        return array(
            'historyeiken' => $paginator->getItems($offset, $limit),
            'page' => $page,
            'paginator' => $paginator,
            'numPerPage' => $limit,
            'type' => $type,
            'schoolyear' => $schoolyear,
            'class' => $class,
            'number' => $number,
            'name' => $name,
            'param' => isset($searchCriteria['token']) ? $searchCriteria['token'] : '',
            'eikenTotal' => $eikenTotal,
            'eikenReadAndListen' => $eikenReadAndListen,
            'classification' => $classification,
            'noRecordExcel' => $messages,
            'currentKai' => $currentKai,
        );
    }

    public function getHistoryPupilEikenExport($searchCriteria) {
        $em = $this->getEntityManager();
        $paginator = $em->getRepository('Application\Entity\EikenTestResult')->getHistoryPupilEiken($searchCriteria, $this->organizationNo);
        $historyPupilEiken = $paginator->getAllItems();
        if(empty($historyPupilEiken)){
            return $historyPupilEiken;
        }
        $header = $this->getServiceLocator()->get('Config')['headerExcelExport']['listOfEikenHistoryPupil'];

        $exportExcelMapper = new ExportExcelMapper($historyPupilEiken, $header, $this->getServiceLocator());
        $dataExport = $exportExcelMapper->convertToExport();
        
        return $dataExport;
    }

    /**
     * AnNV6 UC11
     * Get list of SchoolYearCode from table EikenTestResult
     */
    public function getSchoolYearCode($orgNo, $yearNo, $kaiNo) {
        $em = $this->getEntityManager();
        $objSchoolyear = $em->getRepository('Application\Entity\EikenTestResult')->getSchoolYearCode($orgNo, $yearNo, $kaiNo);

        return $objSchoolyear;
    }

    /**
     * AnNV6 UC11
     * Get list of ClassCode from table EikenTestResult
     */
    public function getClassCode($orgNo, $yearNo, $kaiNo) {
        $em = $this->getEntityManager();
        $objClass = $em->getRepository('Application\Entity\EikenTestResult')->getClassCode($orgNo, $yearNo, $kaiNo);

        return $objClass;
    }

    /**
     *
     * @return unknown
     */
    public function getListKai($yearId = '') {
        $em = $this->getEntityManager();
        //$yearId = $this->params()->fromQuery('year');
        $listkai = $em->getRepository('Application\Entity\EikenSchedule')->getKaiByYear($yearId);
        if (count($listkai) == 0) {
            $listkai[''] = '';
        }

        return $listkai;
    }

    /**
     *
     * @return multitype:number multitype:number  unknown
     */
    public function getInfoYearKai() {
        $currentYear = (int) date("Y");
        $currentDate = date(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT);
        $curKai = null;
        $curYearByKai = null;
        $pastKai = null;
        $pastYear = null;
        //List kai with current year value
        $em = $this->getEntityManager();
        $dataCurrentYear = $em->getRepository('Application\Entity\EikenSchedule')->getInfoStudentCurrentYear($this->id_org, $currentYear);

        if ($dataCurrentYear) {
            foreach ($dataCurrentYear as $key => $value) {
                if (!empty($value['day1stTestResult']) && $value['day1stTestResult']->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) <= $currentDate) {
                    $pastKai = $value['kai'];
                    $pastYear = $value['year'];
                    break;
                }
            }
        }

        //TODO comment for pending
//         if($curKai != null && $curYearByKai != null){
//             if($curKai == 1){
//                 $pastKai = 3;
//                 $pastYear = $curYearByKai - 1;
//             }elseif($curKai == 2 || $curKai == 3){
//                 $pastKai = $curKai - 1;
//                 $pastYear = $curYearByKai;
//             }
//         }


        $listKai = $em->getRepository('Application\Entity\EikenSchedule')->getKaiByYearDESC($currentYear);
        if (count($listKai) == 0) {
            $listKai[''] = '';
        }

        //TODO comment for pending
//         $listKai = $em->getRepository('Application\Entity\EikenSchedule')->getAllKai();
//         if (count($listKai) == 0) {
//             $listKai[''] = '';
//         }
        //List year with current year value
        $listYear[''] = '';
//        for ($i = $pastYear; $i >= 2010; $i--) {
        for ($i = $currentYear; $i >= 2010; $i--) {
            $listYear[$i] = $i;
        }
        //TODO comment for pending
//         $listYear = array();
//         $years = $em->getRepository('Application\Entity\EikenSchedule')->getAllYear();
//         foreach ($years as $key => $value){
//             $listYear[$value['year']] = $value['year'];
//         }

        return array('pastKai' => $pastKai, 'pastYear' => $pastYear, 'currentYear' => $currentYear, 'currentKai' => $curKai, 'listYear' => $listYear, 'listKai' => $listKai);
    }

    /**
     *
     * @return multitype:number multitype:number  string
     */
    public function getListYear() {
        $currentYear = (int) date("Y");
        //List year with current year value
        $listYear = array();
        for ($i = $currentYear + 2; $i >= 2010; $i--) {
            $listYear[$i] = $i;
        }

        return array('currentYear' => $currentYear, 'listYear' => $listYear);
    }

    /**
     * DucNA17
     * @param int $year
     * @param int $kai
     */
    public function getEikenTestResult($year, $kai, $orgNo) {
        $em = $this->getEntityManager();
        $listEikenTestResult = $em->getRepository('Application\Entity\EikenTestResult')->getEikenTestResult($year, $kai, $orgNo);
        $returnEikenTestResul = $this->convertEikenTestResult($listEikenTestResult);

        return $returnEikenTestResul;
    }

    //ducna17
    public function convertEikenTestResult($listEikenTestResult) {
        $return = array();
        if (!empty($listEikenTestResult)) {
            foreach ($listEikenTestResult as $key => $item) {
                $item['nameKanji'] = str_replace(' ', '', $item['nameKanji']);
                $listIbaTestResult[$key]['nameKanji'] = str_replace(' ', '', $item['nameKanji']);
                $keyStr = '';
                if (!empty($item['birthday']) && !empty($item['nameKanji'])) {
                    $keyStr .= $item['birthday']->format('Y-m-d');
                    $keyStr .= $item['nameKanji'];
                    $return[$keyStr][] = $item;
                }
            }
        }

        return $return;
    }

    /**
     * DucNA17
     * @param array $eikenTestResult
     */
    public function getListPupil($orgId, $year, $typeExam = null) {
        $em = $this->getEntityManager();
        $returnPupil = array();
        $listPupil = $em->getRepository('Application\Entity\Pupil')->getPupilData($orgId, $year, $typeExam);
        if (!empty($listPupil)) {
            //convert to type list data for use autocomplete
            $listPupil = $this->convertDataAutoComplete($listPupil, $typeExam);
        }

        return $listPupil;
    }

    //ducna17
    public function convertDataAutoComplete($listPupil, $typeExam = null) {
        $name = array('first' => 'firstNameKanji', 'last' => 'lastNameKanji');
        if ($typeExam == 'IBA') {
            $name = array('first' => 'firstNameKana', 'last' => 'lastNameKana');
        }

        foreach ($listPupil as $key => $item) {
            $listPupil[$key]['value'] = $item[$name['first']] . $item[$name['last']];
            $listPupil[$key]['label'] = $item[$name['first']] . $item[$name['last']];
        }

        return $listPupil;
    }

    //ducna17
    public function convertPupilManager($listPupil, $typeExam = null) {
        $return = array();
        if (!empty($listPupil)) {
            foreach ($listPupil as $key => $item) {
                $keyStr = '';
                if ($typeExam == 'IBA') {
                    if (!empty($item['birthday']) && !empty($item['firstNameKana']) && !empty($item['lastNameKana'])) {
                        $keyStr .= $item['birthday']->format('Y-m-d');
                        $keyStr .= $item['firstNameKana'];
                        $keyStr .= $item['lastNameKana'];
                        if (!empty($return[$keyStr])) {
                            //exist;
                            array_push($return[$keyStr], $item);
                        } else {
                            // no exist
                            $return[$keyStr][] = $item;
                        }
                    }
                } else {
                    if (!empty($item['birthday']) && !empty($item['firstNameKanji']) && !empty($item['lastNameKanji'])) {
                        $keyStr .= $item['birthday']->format('Y-m-d');
                        $keyStr .= $item['firstNameKanji'];
                        $keyStr .= $item['lastNameKanji'];
                        if (!empty($return[$keyStr])) {
                            //exist;
                            array_push($return[$keyStr], $item);
                        } else {
                            // no exist
                            $return[$keyStr][] = $item;
                        }
                    }
                }
            }
        }

        return $return;
    }

    /**
     * DucNA17
     * @param array $eikenTestResult
     */
    public function mappingData($eikenTestResult, $pupilManager, $applyEikenId = 0, $examName = 'EIKEN') {
        $mappingError = array();
        $mappingSuccess = array();
        $mappingCoincident = array();
        if (!empty($eikenTestResult)) {
            foreach ($eikenTestResult as $key => $item) {
                if (sizeof($item) > 1) {
                    if (!empty($pupilManager[$key])) {
                        $mappingCoincident[$key] = $item;
                    } else {
                        $mappingError[$key] = $item;
                    }
                } elseif (sizeof($item) == 1) {
                    if (!empty($pupilManager[$key])) {
                        //duplicate
                        if (sizeof($pupilManager[$key]) > 1) {
                            $mappingCoincident[$key] = $item;
                        } else {
                            //success
                            $mappingSuccess[$key] = $item;
                        }
                    } else {
                        // error
                        $mappingError[$key] = $item;
                    }
                }
            }
            //update Status mapping in ApplyEikenOrg
            //TODO need to double check again for this case
            //             if ($applyEikenId) {
            //                 if ($examName == 'EIKEN') {
            //                     $ApplyEikenOrg = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenOrg')->find($applyEikenId);
            //                     if (!empty($ApplyEikenOrg)) {
            //                         $ApplyEikenOrg->setStatusMapping(1);
            //                         $this->getEntityManager()->persist($ApplyEikenOrg);
            //                         $this->getEntityManager()->flush();
            //                     }
            //                 } else {
            //                     $ApplyIbaOrg = $this->getEntityManager()->getRepository('Application\Entity\ApplyIBAOrg')->find($applyEikenId);
            //                     if (!empty($ApplyIbaOrg)) {
            //                         $ApplyIbaOrg->setStatusMapping(1);
            //                         $this->getEntityManager()->persist($ApplyIbaOrg);
            //                         $this->getEntityManager()->flush();
            //                     }
            //                 }
            //             }
            //update and insert with list success
            if (!empty($mappingSuccess)) {
                $listItemSuccess = array();
                foreach ($mappingSuccess as $key => $itemsSuccess) {
                    foreach ($itemsSuccess as $itemSuccess) {
                        $listItemSuccess[$pupilManager[$key][0]['id']] = $itemSuccess['id'];
                        $this->updateEikenTestResult($itemSuccess['id'], $pupilManager[$key][0]['id']);
                    }
                }
                PrivateSession::setData('listItemSuccess', $listItemSuccess);
            }
        }

        return array(
            'mappingError' => $mappingError,
            'mappingSuccess' => $mappingSuccess,
            'mappingCoincident' => $mappingCoincident//duplicate
        );
    }

    /**
     * DucnaNA17
     * @param string $orgNo
     * @param int $year
     * @param int $kai
     * @param int $page
     * @param int $statusMapping
     * @return array $mappingResturn;
     */
    public function getDataMappingByStatus($orgNo, $year, $kai, $page, $statusMapping) {
        //TODO
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $mappingResturn = array();
        $em = $this->getEntityManager();
        $paginator = $em->getRepository('Application\Entity\EikenTestResult')->getEikenTestResultByOrgNo($orgNo, $year, $kai, $statusMapping);
        $result = $paginator->getItems($offset, $limit);
        if (!empty($result)) {
            $mappingResturn = $this->convertEikenTestResult($result);
        }

        return array('data' => $mappingResturn, 'paginator' => $paginator);
    }

    /**
     * DucNA17
     * @param string $orgNo
     * @param int $year
     * @param int $kai
     * @param int $page
     * @return array(
     * 'mappingError'        => $mappingError,
     * 'mappingSuccess'      => $mappingSuccess,
     * 'mappingCoincident'   => $mappingCoincident,//duplicate
     * 'pupilSuccess'   => $pupilSuccess,
     * 'totalMappingSuccess' => sizeof($mappingSuccess),
     * 'totalMappingError'   => sizeof($mappingError) + sizeof($mappingCoincident)
     * );
     */
    public function getDataMapping($orgNo, $year, $kai, $page, $mappingStatus = null) {
        $mappingError = array();
        $mappingSuccess = array();
        $mappingCoincident = array();
        $pupilSuccess = array();
        $pupilError = array();
        $data = array('paginator' => null,
            'mappingSuccess' => null,
            'mappingError' => null
        );
        $em = $this->getEntityManager();
        if ($mappingStatus == 1) {
            $mappingSuccess = $this->getDataMappingByStatus($orgNo, $year, $kai, $page, 1);
            $data['mappingSuccess'] = $mappingSuccess['data'];
        } elseif ($mappingStatus == 0) {
            $mappingError = $this->getDataMappingByStatus($orgNo, $year, $kai, $page, 0);
            $data['mappingError'] = $mappingError['data'];
        }
        if (!empty($mappingError['data'])) {
            $listIdPupilError = array();
            $data['paginator'] = $mappingError['paginator'];
            foreach ($mappingError['data'] as $itemsError) {
                foreach ($itemsError as $itemsError) {
                    if (!empty($itemsError['tempPupilId'])) {
                        $listIdPupilError[] = $itemsError['tempPupilId'];
                    }
                }
            }
            //get list pupil error (duplicate)
            $data['pupilError'] = $em->getRepository('Application\Entity\Pupil')->getListPupilDataById($listIdPupilError);
        }
        //$mappingCoincident = $this->getDataMappingByStatus($orgNo, $year, $kai, $page, 2);
        if (!empty($mappingSuccess['data'])) {
            $listIdPupilSuccess = array();
            $data['paginator'] = $mappingSuccess['paginator'];
            foreach ($mappingSuccess['data'] as $itemsSuccess) {
                foreach ($itemsSuccess as $itemSuccess) {
                    $listIdPupilSuccess[] = $itemSuccess['pupilId'];
                }
            }
            //get list pupil success
            $data['pupilSuccess'] = $em->getRepository('Application\Entity\Pupil')->getListPupilDataById($listIdPupilSuccess);
        }

        return $data;
    }

    public function updateMappingStatus($mappingData, $pupilManager, $typeExam = null) {
        $em = $this->getEntityManager();
        $i = 0;
        if (sizeof($mappingData['mappingError']) > 0) {
            $idMappingError = array();
            foreach ($mappingData['mappingError'] as $items) {
                foreach ($items as $item) {
                    array_push($idMappingError, $item['id']);
                    if (empty($typeExam)) {
                        //ANHNT
                        $updatePupilId = $em->getReference('Application\Entity\EikenTestResult', array(
                            'id' => $item['id']
                        ));
                        $updatePupilId->setMappingStatus(0);
                        $i++;
                    } else {
                        //ANHNT
                        $updatePupilId = $em->getReference('Application\Entity\IBATestResult', array(
                            'id' => $item['id']
                        ));
                        $updatePupilId->setMappingStatus(0);
                        $i++;
                    }
                    if ($i == 1000) {
                        $em->flush();
                        $em->clear();
                        $i = 0;
                    }
                }
            }
            $em->flush();
            $em->clear();
            $i = 0;
        }
        if (sizeof($mappingData['mappingSuccess']) > 0) {
            $idMappingSuccess = array();
            foreach ($mappingData['mappingSuccess'] as $key => $items) {
                $pupilId = $pupilManager[$key][0]['id'];
                //update PupilId by Id of EikenTestResult
                foreach ($items as $item) {
                    array_push($idMappingSuccess, $item['id']);
                    //update pupilID to EikenExamResult
                    if (empty($typeExam)) {
                        //update Pupil
                        //$em->getRepository('Application\Entity\EikenTestResult')->updatePupilId($item['id'], $pupilId);
                        //ANHNT
                        $updatePupilId = $em->getReference('Application\Entity\EikenTestResult', array(
                            'id' => $item['id']
                        ));
                        $updatePupilId->setTempPupilId($pupilId);
                        $updatePupilId->setPupilId($pupilId);
                        $updatePupilId->setMappingStatus(1);
                        $i++;
                    } else {
                        //update Pupil
                        //$em->getRepository('Application\Entity\IBATestResult')->updatePupilId($item['id'], $pupilId);
                        //ANHNT
                        $updatePupilId = $em->getReference('Application\Entity\IBATestResult', array(
                            'id' => $item['id']
                        ));
                        $updatePupilId->setTempPupilId($pupilId);
                        $updatePupilId->setPupilId($pupilId);
                        $updatePupilId->setMappingStatus(1);
                        $i++;
                    }
                    if ($i == 1000) {
                        $em->flush();
                        $em->clear();
                        $i = 0;
                    }
                }
            }
            $em->flush();
            $em->clear();
            $i = 0;
        }
        if (sizeof($mappingData['mappingCoincident']) > 0) {
            $idMappingCoincident = array();
            foreach ($mappingData['mappingCoincident'] as $key => $items) {
                $pupilId = $pupilManager[$key][0]['id'];
                foreach ($items as $item) {
                    array_push($idMappingCoincident, $item['id']);
                    //update pupilID to EikenExamResult
                    if (empty($typeExam)) {
                        //update TempPupil
                        //$em->getRepository('Application\Entity\EikenTestResult')->updateTempPupilId($item['id'], $pupilId);
                        //ANHNT
                        $updatePupilId = $em->getReference('Application\Entity\EikenTestResult', array(
                            'id' => $item['id']
                        ));
                        $updatePupilId->setTempPupilId($pupilId);
                        $updatePupilId->setMappingStatus(2);
                        $i++;
                    } else {
                        //update TempPupil
                        //$em->getRepository('Application\Entity\IBATestResult')->updateTempPupilId($item['id'], $pupilId);
                        //ANHNT
                        $updatePupilId = $em->getReference('Application\Entity\IBATestResult', array(
                            'id' => $item['id']
                        ));
                        $updatePupilId->setTempPupilId($pupilId);
                        $updatePupilId->setMappingStatus(2);
                        $i++;
                    }
                    if ($i == 1000) {
                        $em->flush();
                        $em->clear();
                        $i = 0;
                    }
                }
            }
            $em->flush();
            $em->clear();
            $i = 0;
        }

//         if (empty($typeExam)) {
//             if (!empty($idMappingError)) {
//                 $em->getRepository('Application\Entity\EikenTestResult')->updateMappingStatus($idMappingError, 0);
//             }
// //             if (!empty($idMappingSuccess)) {
// //                 $em->getRepository('Application\Entity\EikenTestResult')->updateMappingStatus($idMappingSuccess, 1);
// //             }
// //             if (!empty($idMappingCoincident)) {
// //                 $em->getRepository('Application\Entity\EikenTestResult')->updateMappingStatus($idMappingCoincident, 2);
// //             }
//         } else {
//             if (!empty($idMappingError)) {
//                 $em->getRepository('Application\Entity\IBATestResult')->updateMappingStatus($idMappingError, 0);
//             }
//             if (!empty($idMappingSuccess)) {
//                 $em->getRepository('Application\Entity\IBATestResult')->updateMappingStatus($idMappingSuccess, 1);
//             }
//             if (!empty($idMappingCoincident)) {
//                 $em->getRepository('Application\Entity\IBATestResult')->updateMappingStatus($idMappingCoincident, 2);
//             }
//         }
    }

    /**
     *
     * @param unknown $item
     * @return multitype:number \DateTime NULL
     */
    private function mappingDataFromUkestuke($item = array()) {
        $config = $this->getServiceLocator()->get('config');
        $listMappingLevel = $config['MappingLevel'];
        $eikenLevel = $listMappingLevel[$item->kyucd];
        $isPass = 0;
        if ($eikenLevel == '4級' || $eikenLevel == '5級') {
            if ($item->ichijigouhiflg == 1) {
                $isPass = 1;
            }
        } else {
            if ($item->ichimenflg == 1) {
                if ($item->nijigouhiflg == 1) {
                    $isPass = 1;
                }
            } else {
                if ($item->ichijigouhiflg == 1 && $item->nijigouhiflg == 1) {
                    $isPass = 1;
                }
            }
        }
        $attendFlag = 0;
        //Add field attendFlag
        if ($item->kyucd >= 6) {
            $attendFlag = ($item->ichijigouhiflg != '') ? 1 : 0;
        } else {
            if ($item->ichimenflg === 1) {
                $attendFlag = ($item->nijigouhiflg != '') ? 1 : 0; // TaiVH - Fix 6/11/2015 - bug F1GJIEM-3309 
            } else {
                $attendFlag = ($item->nijigouhiflg != '' || $item->ichijigouhiflg != '') ? 1 : 0;
            }
        }
        $examDate = $this->getExamDateOfEikenByLevelAndYearAndKai($this->checkDataBeforeTrim($item->kyucd), $this->checkDataBeforeTrim($item->nendo), $this->checkDataBeforeTrim($item->kai));

        $array = array(
            "ResultFlag" => $this->checkDataBeforeTrim($item->kekka),
            "Year" => $this->checkDataBeforeTrim($item->nendo),
            "Kai" => $this->checkDataBeforeTrim($item->kai),
            "EikenId" => $this->checkDataBeforeTrim($item->eikenid),
            "UketsukeNo" => $this->checkDataBeforeTrim($item->uketsukeno),
            "EikenLevelId" => $this->checkDataBeforeTrim($item->kyucd),
            "HallClassification" => $this->checkDataBeforeTrim($item->kaijokbn),
            "ExecutionDayOfTheWeek" => $this->checkDataBeforeTrim($item->jishiyoubi),
            "ExamineeNumber" => $this->checkDataBeforeTrim($item->jukenno),
            "PupilName" => str_replace(array(' ', ' ', '　'), array('', '', ''), $this->checkDataBeforeTrim($item->simei)),
            "SchoolNumber" => $this->checkDataBeforeTrim($item->gakkouno),
            "SchoolYearCode" => $this->checkDataBeforeTrim($item->gakunenno),
            "ClassCode" => $this->checkDataBeforeTrim($item->kumi),
            "OneExemptionFlag" => $this->checkDataBeforeTrim($item->ichimenflg),
            "OrganizationNo" => $this->checkDataBeforeTrim($item->dantaino),
            "FisrtScore1" => $this->checkDataBeforeTrim($item->ichiji1),
            "FisrtScore2" => $this->checkDataBeforeTrim($item->ichiji2),
            "FisrtScore3" => $this->checkDataBeforeTrim($item->ichiji3),
            "FisrtScore4" => $this->checkDataBeforeTrim($item->ichiji4),
            "FisrtScore5" => $this->checkDataBeforeTrim($item->ichiji5),
            "FisrtScore6" => $this->checkDataBeforeTrim($item->ichiji6),
            "FisrtScore7" => $this->checkDataBeforeTrim($item->ichiji7),
            "FisrtScore8" => $this->checkDataBeforeTrim($item->ichiji8),
            "TotalPrimaryScore" => $this->checkDataBeforeTrim($item->ichijikei),
            "PrimaryPassFailFlag" => $this->checkDataBeforeTrim($item->ichijigouhiflg),
            "PrimaryFailureLevel" => $this->checkDataBeforeTrim($item->ichijilevel),
            "SecondScore1" => $this->checkDataBeforeTrim($item->niji1),
            "SecondScore2" => $this->checkDataBeforeTrim($item->niji2),
            "SecondScore3" => $this->checkDataBeforeTrim($item->niji3),
            "SecondScore4" => $this->checkDataBeforeTrim($item->niji4),
            "SecondScore5" => $this->checkDataBeforeTrim($item->niji5),
            "SecondScore6" => $this->checkDataBeforeTrim($item->niji6),
            "SecondScore7" => $this->checkDataBeforeTrim($item->niji7),
            "SecondScore8" => $this->checkDataBeforeTrim($item->niji8),
            "TotalSecondScore" => $this->checkDataBeforeTrim($item->nijikei),
            "SecondPassFailFlag" => $this->checkDataBeforeTrim($item->nijigouhiflg),
            "SecondUnacceptableLevel" => $this->checkDataBeforeTrim($item->nijilevel),
            "SecondExamHall" => $this->checkDataBeforeTrim($item->nijikaijo),
            "SecondSetTimeHour" => $this->checkDataBeforeTrim($item->nijijikan_ji),
            "SecondSetTimeMinute" => $this->checkDataBeforeTrim($item->nijijikan_hun),
            "FirstMailSendFlag" => $this->checkDataBeforeTrim($item->ichijimailflg),
            "SecondMailSendFlag" => $this->checkDataBeforeTrim($item->nijimailflg),
            "InsertDate" => new \DateTime(date('Y-m-d H:i:s', strtotime($item->createdt))),
            "UpdateDate" => new \DateTime(date('Y-m-d H:i:s', strtotime($item->updatedt))),
            "IssueDate" => $this->checkDataBeforeTrim($item->hakkodt),
            "DeliveryClassification" => $this->checkDataBeforeTrim($item->nouhinkbn),
            "SemiClassification" => $this->checkDataBeforeTrim($item->junhonkbn),
            "DomesticInternationalClassification" => $this->checkDataBeforeTrim($item->kokunaigaikbn),
            "ShippingClassification" => $this->checkDataBeforeTrim($item->hassokbn),
            "DeedClassification" => $this->checkDataBeforeTrim($item->syousyokbn),
            "DisplayClass" => $this->checkDataBeforeTrim($item->hyojikyu),
            "ExamLocation" => $this->checkDataBeforeTrim($item->jyukenchi),
            "NameKanji" => str_replace(array(' ', ' ', '　'), array('', '', ''), $this->checkDataBeforeTrim($item->simei)),
            "TempNameKanji" => str_replace(array(' ', ' ', '　'), array('', '', ''), $this->checkDataBeforeTrim($item->simei)),
            "NameRomanji" => str_replace(array(' ', ' ', '　'), array('', '', ''), $this->checkDataBeforeTrim($item->simei_romaji)),
            "NameRomanjiWithPrefix" => $this->checkDataBeforeTrim($item->simei_romaji_m),
            "NameKana" => str_replace(array(' ', ' ', '　'), array('', '', ''), $this->checkDataBeforeTrim($item->simei_kana)),
            "ZipCode" => $this->checkDataBeforeTrim($item->yubinbangou),
            "Address1" => $this->checkDataBeforeTrim($item->jyusyo1),
            "Address2" => $this->checkDataBeforeTrim($item->jyusyo2),
            "Address3" => $this->checkDataBeforeTrim($item->jyusyo3),
            "Address4" => $this->checkDataBeforeTrim($item->jyusyo4),
            "Address5" => $this->checkDataBeforeTrim($item->jyusyo5),
            "UrgentNotification" => $this->checkDataBeforeTrim($item->kokuchi),
            "BatchNumber" => $this->checkDataBeforeTrim($item->batchnum),
            "SeriNumber" => $this->checkDataBeforeTrim($item->seirinum),
            "SchoolClassification" => $this->checkDataBeforeTrim($item->gakkokbn),
            "ClassForDisplay" => $this->checkDataBeforeTrim($item->hyojikumi),
            "Sex" => $this->checkDataBeforeTrim($item->sei),
            "BarCodeStatus" => $this->checkDataBeforeTrim($item->bcdumu),
            "Barcode" => $this->checkDataBeforeTrim($item->bcd),
            "OrganizationName" => $this->checkDataBeforeTrim($item->dantaimei),
            "Password" => $this->checkDataBeforeTrim($item->password),
            "Note1" => $this->checkDataBeforeTrim($item->chui1),
            "Note2" => $this->checkDataBeforeTrim($item->chui2),
            "Note3" => $this->checkDataBeforeTrim($item->chui3),
            "ExamResults" => $this->checkDataBeforeTrim($item->shikenkbn),
            "FirstExamResultsFlag" => $this->checkDataBeforeTrim($item->ichijikekka),
            "FirstExamResultsFlagForDisplay" => $this->checkDataBeforeTrim($item->ichijikekka_hyoji),
            "FirstExamResultsPerfectScore" => $this->checkDataBeforeTrim($item->ichijikekka_manten),
            "FirstExamResultsPassPoint" => $this->checkDataBeforeTrim($item->ichijikekka_gokaku),
            "FirstExamResultsFailPoint" => $this->checkDataBeforeTrim($item->ichijikekka_fugokakua),
            "FirstExamResultsAveragePass" => $this->checkDataBeforeTrim($item->ichijikekka_gokakuheikin),
            "FirstExamResultsExamAverage" => $this->checkDataBeforeTrim($item->ichijikekka_jukensyaheikin),
            "FirstAdviceSentence1" => $this->checkDataBeforeTrim($item->ichijiadvice1),
            "FirstAdviceSentence2" => $this->checkDataBeforeTrim($item->ichijiadvice2),
            "FirstAdviceSentence3" => $this->checkDataBeforeTrim($item->ichijiadvice3),
            "FirstAdviceSentence4" => $this->checkDataBeforeTrim($item->ichijiadvice4),
            "FirstAdviceSentence5" => $this->checkDataBeforeTrim($item->ichijiadvice5),
            "FirstAdviceSentence6" => $this->checkDataBeforeTrim($item->ichijiadvice6),
            "CorrectAnswer" => $this->checkDataBeforeTrim($item->seikai),
            "Correction" => $this->checkDataBeforeTrim($item->seigo),
            "Explanation1" => $this->checkDataBeforeTrim($item->setumei1),
            "Explanation2" => $this->checkDataBeforeTrim($item->setumei2),
            "CrowdedFlag" => $this->checkDataBeforeTrim($item->manninflg),
            "CrowdedSentence" => $this->checkDataBeforeTrim($item->manninbunsyo),
            "SecondHallClassification" => $this->checkDataBeforeTrim($item->niji_kaijo_kbn),
            "HallNumber" => $this->checkDataBeforeTrim($item->kaijonum),
            "HallName" => $this->checkDataBeforeTrim($item->kaijomei),
            "SecondZipCode" => $this->checkDataBeforeTrim($item->niji_yubin_no),
            "SecondAddress" => $this->checkDataBeforeTrim($item->niji_jusyo),
            "TrafficRoute1" => $this->checkDataBeforeTrim($item->keiro1),
            "TrafficRoute2" => $this->checkDataBeforeTrim($item->keiro2),
            "TrafficRoute3" => $this->checkDataBeforeTrim($item->keiro3),
            "MapCode" => $this->checkDataBeforeTrim($item->chizu),
            "MeetingTime" => $this->checkDataBeforeTrim($item->syugojikan),
            "MeetingTimeDisplay" => $this->checkDataBeforeTrim($item->syugojikan_hyoji),
            "MeetingTimeColorFlag" => $this->checkDataBeforeTrim($item->syugojikan_flg),
            "PhotoAttachEsitence" => $this->checkDataBeforeTrim($item->syasin_tempu),
            "SemiHallApplicationDisplay" => $this->checkDataBeforeTrim($item->junkaijo_hyoji),
            "BaggageOutputClassification" => $this->checkDataBeforeTrim($item->keikohin),
            "Comment" => $this->checkDataBeforeTrim($item->seiseki_comment),
            "CommunicationField" => $this->checkDataBeforeTrim($item->info),
            "FirstFailureFourFiveClass" => $this->checkDataBeforeTrim($item->ichijihugokaku),
            "VocabularyFieldScore" => $this->checkDataBeforeTrim($item->tokuten_1),
            "VocabularyScore" => $this->checkDataBeforeTrim($item->haiten_1),
            "VocabularyPercentCorrectAnswers" => $this->checkDataBeforeTrim($item->seitouritsu_1),
            "VocabularyOverallAverage" => $this->checkDataBeforeTrim($item->heikin_1),
            "VocabularyPassAverage" => $this->checkDataBeforeTrim($item->goukakuheikin_1),
            "ReadingFieldScore" => $this->checkDataBeforeTrim($item->tokuten_2),
            "ReadingScore" => $this->checkDataBeforeTrim($item->haiten_2),
            "ReadingPercentCorrectAnswers" => $this->checkDataBeforeTrim($item->seitouritsu_2),
            "ReadingOverallAverage" => $this->checkDataBeforeTrim($item->heikin_2),
            "ReadingPassAverage" => $this->checkDataBeforeTrim($item->goukakuheikin_2),
            "ListeningFieldScore" => $this->checkDataBeforeTrim($item->tokuten_3),
            "ListeningScore" => $this->checkDataBeforeTrim($item->haiten_3),
            "ListeningPercentCorrectAnswers" => $this->checkDataBeforeTrim($item->seitouritsu_3),
            "ListeningOverallAverage" => $this->checkDataBeforeTrim($item->heikin_3),
            "ListeningPassAverage" => $this->checkDataBeforeTrim($item->goukakuheikin_3),
            "CompositionFieldScore" => $this->checkDataBeforeTrim($item->tokuten_4),
            "CompositionScore" => $this->checkDataBeforeTrim($item->haiten_4),
            "CompositionPercentCorrectAnswers" => $this->checkDataBeforeTrim($item->seitouritsu_4),
            "CompositionOverallAverage" => $this->checkDataBeforeTrim($item->heikin_4),
            "CompositionPassAverage" => $this->checkDataBeforeTrim($item->goukakuheikin_4),
            "ResultScoreAccordingField1" => $this->checkDataBeforeTrim($item->bunyatokuten_1),
            "ResultScoreAccordingField2" => $this->checkDataBeforeTrim($item->bunyatokuten_2),
            "ResultScoreAccordingField3" => $this->checkDataBeforeTrim($item->bunyatokuten_3),
            "ResultScoreAccordingField4" => $this->checkDataBeforeTrim($item->bunyatokuten_4),
            "ResultPerfectScoreAccordingField1" => $this->checkDataBeforeTrim($item->manten_1),
            "ResultPerfectScoreAccordingField2" => $this->checkDataBeforeTrim($item->manten_2),
            "ResultPerfectScoreAccordingField3" => $this->checkDataBeforeTrim($item->manten_3),
            "ResultPerfectScoreAccordingField4" => $this->checkDataBeforeTrim($item->manten_4),
            "LargeQuestionCorrectAnswer1" => $this->checkDataBeforeTrim($item->daimon_1),
            "LargeQuestionCorrectAnswer2" => $this->checkDataBeforeTrim($item->daimon_2),
            "LargeQuestionCorrectAnswer3" => $this->checkDataBeforeTrim($item->daimon_3),
            "LargeQuestionCorrectAnswer4" => $this->checkDataBeforeTrim($item->daimon_4),
            "LargeQuestionProblemResult1" => $this->checkDataBeforeTrim($item->mondaisu_1),
            "LargeQuestionProblemResult2" => $this->checkDataBeforeTrim($item->mondaisu_2),
            "LargeQuestionProblemResult3" => $this->checkDataBeforeTrim($item->mondaisu_3),
            "LargeQuestionProblemResult4" => $this->checkDataBeforeTrim($item->mondaisu_4),
            "StydyAdvice1" => $this->checkDataBeforeTrim($item->advice_1),
            "StydyAdvice2" => $this->checkDataBeforeTrim($item->advice_2),
            "StydyAdvice3" => $this->checkDataBeforeTrim($item->advice_3),
            "StydyAdvice4" => $this->checkDataBeforeTrim($item->advice_4),
            "NoticeCode1" => $this->checkDataBeforeTrim($item->oshirase_1),
            "NoticeCode2" => $this->checkDataBeforeTrim($item->oshirase_2),
            "StudyRealityGraph1" => $this->checkDataBeforeTrim($item->graph_1),
            "StudyRealityGraph2" => $this->checkDataBeforeTrim($item->graph_2),
            "FirstPassMerit1" => $this->checkDataBeforeTrim($item->merit_1),
            "FirstPassMerit2" => $this->checkDataBeforeTrim($item->merit_2),
            "FirstPassMerit3" => $this->checkDataBeforeTrim($item->merit_3),
            "FirstPassMerit4" => $this->checkDataBeforeTrim($item->merit_4),
            "FirstPassMerit5" => $this->checkDataBeforeTrim($item->merit_5),
            "FirstPassMerit6" => $this->checkDataBeforeTrim($item->merit_6),
            "FirstPassMerit7" => $this->checkDataBeforeTrim($item->merit_7),
            "FirstPassMerit8" => $this->checkDataBeforeTrim($item->merit_8),
            "FirstPassMerit9" => $this->checkDataBeforeTrim($item->merit_9),
            "FirstPassMerit10" => $this->checkDataBeforeTrim($item->merit_10),
            "FirstPassMerit11" => $this->checkDataBeforeTrim($item->merit_11),
            "FirstPassMerit12" => $this->checkDataBeforeTrim($item->merit_12),
            "FirstPassMerit13" => $this->checkDataBeforeTrim($item->merit_13),
            "FirstPassMerit14" => $this->checkDataBeforeTrim($item->merit_14),
            "FirstPassMerit15" => $this->checkDataBeforeTrim($item->merit_15),
            "CanDoList1" => $this->checkDataBeforeTrim($item->cando_1),
            "CertificateNumber" => $this->checkDataBeforeTrim($item->syousyonum),
            "CertificationDate" => isset($examDate) ? new \DateTime(date('Y-m-d H:i:s', strtotime($examDate))) : null,
            "SortArea" => $this->checkDataBeforeTrim($item->sort),
            "SelfOrganizationsDeliveryFlag" => $this->checkDataBeforeTrim($item->dantai_chokuso),
            "SecondIssueYear" => $this->checkDataBeforeTrim($item->niji_hakkodt),
            "SecondDeliveryClassification" => $this->checkDataBeforeTrim($item->niji_nouhinkbn),
            "SecondSemiClassification" => $this->checkDataBeforeTrim($item->niji_junhonkbn),
            "SecondExecutionDayOfTheWeek" => $this->checkDataBeforeTrim($item->niji_jishiyoubi),
            "SecondDomesticInternationalClassification" => $this->checkDataBeforeTrim($item->niji_kokunaigaikbn),
            "SecondShippingClassification" => $this->checkDataBeforeTrim($item->niji_hassokbn),
            "SecondDeedExistenceClassification" => $this->checkDataBeforeTrim($item->niji_syousyokbn),
            "SecondExaminationAreas" => $this->checkDataBeforeTrim($item->niji_jyukenchi),
            "SecondEmergencyNotice" => $this->checkDataBeforeTrim($item->niji_kokuchi),
            "SecondBatchNumber" => $this->checkDataBeforeTrim($item->niji_batchnum),
            "SecondSeriNumber" => $this->checkDataBeforeTrim($item->niji_seirinum),
            "SecondBarCodeStatus" => $this->checkDataBeforeTrim($item->niji_bcdumu),
            "SecondBarCode" => $this->checkDataBeforeTrim($item->niji_bcd),
            "SecondNote1" => $this->checkDataBeforeTrim($item->niji_chui1),
            "SecondNote2" => $this->checkDataBeforeTrim($item->niji_chui2),
            "SecondNote3" => $this->checkDataBeforeTrim($item->niji_chui3),
            "SecondExamClassification" => $this->checkDataBeforeTrim($item->niji_kbn),
            "SecondExamResultsFlag" => $this->checkDataBeforeTrim($item->nijikekka),
            "SecondExamResultsFlagForDisplay" => $this->checkDataBeforeTrim($item->nijikekka_hyoji),
            "SecondExamResultsPerfectScore" => $this->checkDataBeforeTrim($item->nijikekka_manten),
            "SecondExamResultsPassPoint" => $this->checkDataBeforeTrim($item->nijikekka_gokaku),
            "SecondtExamResultsFailPoint" => $this->checkDataBeforeTrim($item->nijikekka_fugokakua),
            "SecondAdviceSentence1" => $this->checkDataBeforeTrim($item->nijiadvice1),
            "SecondAdviceSentence2" => $this->checkDataBeforeTrim($item->nijiadvice2),
            "SecondAdviceSentence3" => $this->checkDataBeforeTrim($item->nijiadvice3),
            "SecondAdviceSentence4" => $this->checkDataBeforeTrim($item->nijiadvice4),
            "SecondAdviceSentence5" => $this->checkDataBeforeTrim($item->nijiadvice5),
            "SecondAdviceSentence6" => $this->checkDataBeforeTrim($item->nijiadvice6),
            "ScoreAccordingField1" => $this->checkDataBeforeTrim($item->nijitokuten_1),
            "ScoreAccordingField2" => $this->checkDataBeforeTrim($item->nijitokuten_2),
            "ScoreAccordingField3" => $this->checkDataBeforeTrim($item->nijitokuten_3),
            "ScoreAccordingField4" => $this->checkDataBeforeTrim($item->nijitokuten_4),
            "ScoreAccordingField5" => $this->checkDataBeforeTrim($item->nijitokuten_5),
            "ScoringAccordingField1" => $this->checkDataBeforeTrim($item->nijihaiten_1),
            "ScoringAccordingField2" => $this->checkDataBeforeTrim($item->nijihaiten_2),
            "ScoringAccordingField3" => $this->checkDataBeforeTrim($item->nijihaiten_3),
            "ScoringAccordingField4" => $this->checkDataBeforeTrim($item->nijihaiten_4),
            "ScoringAccordingField5" => $this->checkDataBeforeTrim($item->nijihaiten_5),
            "SecondPassMerit1" => $this->checkDataBeforeTrim($item->nijimerit_1),
            "SecondPassMerit2" => $this->checkDataBeforeTrim($item->nijimerit_2),
            "SecondPassMerit3" => $this->checkDataBeforeTrim($item->nijimerit_3),
            "SecondPassMerit4" => $this->checkDataBeforeTrim($item->nijimerit_4),
            "SecondPassMerit5" => $this->checkDataBeforeTrim($item->nijimerit_5),
            "SecondPassMerit6" => $this->checkDataBeforeTrim($item->nijimerit_6),
            "SecondPassMerit7" => $this->checkDataBeforeTrim($item->nijimerit_7),
            "SecondPassMerit8" => $this->checkDataBeforeTrim($item->nijimerit_8),
            "SecondPassMerit9" => $this->checkDataBeforeTrim($item->nijimerit_9),
            "SecondPassMerit10" => $this->checkDataBeforeTrim($item->nijimerit_10),
            "SecondPassMerit11" => $this->checkDataBeforeTrim($item->nijimerit_11),
            "SecondPassMerit12" => $this->checkDataBeforeTrim($item->nijimerit_12),
            "SecondPassMerit13" => $this->checkDataBeforeTrim($item->nijimerit_13),
            "SecondPassMerit14" => $this->checkDataBeforeTrim($item->nijimerit_14),
            "SecondPassMerit15" => $this->checkDataBeforeTrim($item->nijimerit_15),
            "CanDoList2" => $this->checkDataBeforeTrim($item->cando_2),
            "Notice" => $this->checkDataBeforeTrim($item->niji_oshirase),
            "SecondCertificateNumber" => $this->checkDataBeforeTrim($item->niji_syousyonum),
            "SecondCertificationDate" => isset($item->niji_ninteibi) ? new \DateTime(date('Y-m-d H:i:s', strtotime($item->niji_ninteibi))) : null,
            "SecondSortArea" => $this->checkDataBeforeTrim($item->niji_sort),
            "SecondselfOrganizationDeliveryFlag" => $this->checkDataBeforeTrim($item->niji_dantai_chokuso),
            "PasswordNumber" => $this->checkDataBeforeTrim($item->pin_number),
            "Birthday" => new \DateTime(date('Y-m-d H:i:s', strtotime($item->birthday))),
            "FirsrtScoreTwoSkillRL" => $this->checkDataBeforeTrim($item->cse_total_1_rl),
            "FirstSoreThreeSkillRLW" => $this->checkDataBeforeTrim($item->cse_total_1_rlw),
            "SecondScoreThreeSkillRLS" => $this->checkDataBeforeTrim($item->cse_total_2_rls),
            "SecondScoreFourSkillRLWS" => $this->checkDataBeforeTrim($item->cse_total_2_rlws),
            "CSEScoreReading" => $this->checkDataBeforeTrim($item->cse_reading),
            "CSEScoreListening" => $this->checkDataBeforeTrim($item->cse_listening),
            "CSEScoreWriting" => $this->checkDataBeforeTrim($item->cse_writing),
            "CSEScoreSpeaking" => $this->checkDataBeforeTrim($item->cse_speaking),
            "EikenBand1" => $this->checkDataBeforeTrim($item->eikenband_1),
            "EikenBand2" => $this->checkDataBeforeTrim($item->eikenband_2),
            "CSEScoreMessage1" => $this->checkDataBeforeTrim($item->cse_msg_1),
            "CSEScoreMessage2" => $this->checkDataBeforeTrim($item->cse_msg_2),
            "EikenCSETotal" => $item->cse_reading + $item->cse_listening + $item->cse_writing + $item->cse_speaking,
            "SchoolYearName" => $this->checkDataBeforeTrim($item->gakunenno),
            "ClassName" => $this->checkDataBeforeTrim($item->kumi),
            "IsPass" => $isPass,
            "AttendFlag" => $attendFlag
        );

        return $array;
    }

    /**
     * This function to get personal achievement
     * @param $id
     * @param $organizationNo
     * @return bool|null|object
     */
    public function getPersonalAchievementEiken($id, $organizationNo)
    {
        $id = (int) $id;
        if ($id < 1)
            return false;
        /** @var EikenTestResult $result */
        $result = $this->getEntityManager()->getRepository('Application\Entity\EikenTestResult')->findOneBy(array('id' => $id, 'organizationNo' => $organizationNo));
        if (empty($result))
            return false;
        if($result->getYear() == 2016){
            return $this->getPersonalAchievementEiken2016($result);
        }else if($result->getYear() > 2016){
            return $this->getPersonalAchievementEiken2017($result);
        }else{
            return $this->getPersonalAchievementEikenLessThan2016($result);
        }
    }

    /**
     * This function to get personal achievement for result of kai from 2015 to the past
     * @param $eikenTestResult
     * @return bool|null|object
     */
    public function getPersonalAchievementEiken2016($eikenTestResult) {
        // this delimiter using for split mondaisu_x & daimon_x.
        $tenDelimiter = HistoryConst::TEN_KEY;
        $monDelimiter = HistoryConst::MONDAI_KEY;
        $eikenLevel = $this->getEntityManager()
            ->getRepository('Application\Entity\EikenLevel')
            ->find($eikenTestResult->getEikenLevelId());
        $eikenTestResult->getLevelName = $eikenLevel->getLevelName();

        $eikenTestResult->mondaisu_1 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionProblemResult1());
        $eikenTestResult->mondaisu_2 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionProblemResult2());
        $eikenTestResult->mondaisu_3 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionProblemResult3());
        $isExitMondai4 = $eikenTestResult->getEikenLevelId() == 1 || $eikenTestResult->getEikenLevelId() == 2 || $eikenTestResult->getEikenLevelId() == 3;
        $eikenTestResult->mondaisu_4 = $isExitMondai4
            ? $this->convert3byteToArray($eikenTestResult->getLargeQuestionProblemResult4(), $tenDelimiter)
            : $this->convert3byteToArray($eikenTestResult->getLargeQuestionProblemResult4());

        $Daimon_1 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionCorrectAnswer1());
        $Daimon_2 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionCorrectAnswer2());
        $Daimon_3 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionCorrectAnswer3());
        $Daimon_4 = $isExitMondai4
            ? $this->convert3byteToArray($eikenTestResult->getLargeQuestionCorrectAnswer4(), $tenDelimiter)
            : $this->convert3byteToArray($eikenTestResult->getLargeQuestionCorrectAnswer4());

        $path1 = $this->mapDaimonMondaisu($Daimon_1, $eikenTestResult->mondaisu_1);
        $path2 = $this->mapDaimonMondaisu($Daimon_2, $eikenTestResult->mondaisu_2);
        $path3 = $this->mapDaimonMondaisu($Daimon_3, $eikenTestResult->mondaisu_3);
        $path4 = $isExitMondai4
            ? $this->mapDaimonMondaisu($Daimon_4, $eikenTestResult->mondaisu_4, $tenDelimiter, $tenDelimiter)
            : $this->mapDaimonMondaisu($Daimon_4, $eikenTestResult->mondaisu_4);

        $eikenTestResult->totalMondai = array();
        if ($path1->value && $path2->value && $path3->value && ($path4->value || !$isExitMondai4)) {
            if (!$isExitMondai4) {
                $eikenTestResult->valueMondai = array_merge($path1->value, array_merge($path2->value, $path3->value));
                $eikenTestResult->totalMondai = array_merge($path1->total, array_merge($path2->total, $path3->total));
            } else {
                $eikenTestResult->valueMondai = array_merge($path1->value, array_merge($path2->value, array_merge($path3->value, $path4->value)));
                $eikenTestResult->totalMondai = array_merge($path1->total, array_merge($path2->total, array_merge($path3->total, $path4->total)));
            }
        }
        $eikenTestResult->active = '';
        $eikenTestResult->display = '';
        //Exits tab2
        if ($eikenTestResult->getOneExemptionFlag() != 0) {
            $eikenTestResult->active = 'in active';
        }
        preg_match_all('/(\d{1}\-\d{1}|\d{1})/', $eikenTestResult->getCorrectAnswer(), $CorrectAnswer);
        $eikenTestResult->getAnswerNo = $CorrectAnswer[0];
        $eikenTestResult->getTrueFalse = empty($eikenTestResult->getCorrection()) ? array() : str_split(str_replace(' ', '', $eikenTestResult->getCorrection()));
        $eikenTestResult->caseId = $eikenTestResult->getEikenLevelId();
        //Level : 1級
        if (($eikenTestResult->getEikenLevelId() == 1) && count($eikenTestResult->totalMondai) > 5) {
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[4] + $eikenTestResult->totalMondai[5] + (isset($eikenTestResult->totalMondai[6]) ? $eikenTestResult->totalMondai[6] : 0);
        } //Level : 準1級
        else if (($eikenTestResult->getEikenLevelId() == 2) && count($eikenTestResult->totalMondai) > 5) {
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[4] + $eikenTestResult->totalMondai[5];
        } //Level: ２級
        else if ($eikenTestResult->getEikenLevelId() == 3 && count($eikenTestResult->totalMondai) > 8) {
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[4];
        } //Level: 準２級
        else if ($eikenTestResult->getEikenLevelId() == 4 && count($eikenTestResult->totalMondai) > 7) {
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2] + $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[4];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[5] + $eikenTestResult->totalMondai[6] + $eikenTestResult->totalMondai[7];
        } //Level: 3級,4級
        else if (($eikenTestResult->getEikenLevelId() == 5 || $eikenTestResult->getEikenLevelId() == 6) && count($eikenTestResult->totalMondai) > 6) {
            $eikenTestResult->caseId = $eikenTestResult->getEikenLevelId();
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2] + $eikenTestResult->totalMondai[3];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[4] + $eikenTestResult->totalMondai[5] + $eikenTestResult->totalMondai[6];
        } //Level: 5級
        else if ($eikenTestResult->getEikenLevelId() == 7 && count($eikenTestResult->totalMondai) > 5) {
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[4] + $eikenTestResult->totalMondai[5];
        }
        if ($eikenTestResult->getOneExemptionFlag() == 0 && count($eikenTestResult->getAnswerNo) != count($eikenTestResult->getTrueFalse)) {
            return false;
        }
        //Exits tab1 not exits tab2
        if ($eikenTestResult->getOneExemptionFlag() == 0 && $eikenTestResult->getEikenLevelId() < 8) {
            $eikenTestResult->display = 'style="display: none"';
            //Fake data local false.
            if (empty($eikenTestResult->part1) || empty($eikenTestResult->part2)) {
                return false;
            }
        }

        return $eikenTestResult;
    }
    public function getPersonalAchievementEiken2017($eikenTestResult) {
        // this delimiter using for split mondaisu_x & daimon_x.
        $tenDelimiter = HistoryConst::TEN_KEY;
        $monDelimiter = HistoryConst::MONDAI_KEY;
        $eikenLevel = $this->getEntityManager()
            ->getRepository('Application\Entity\EikenLevel')
            ->find($eikenTestResult->getEikenLevelId());
        $eikenTestResult->getLevelName = $eikenLevel->getLevelName();

        $eikenTestResult->mondaisu_1 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionProblemResult1());
        $eikenTestResult->mondaisu_2 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionProblemResult2());
        $eikenTestResult->mondaisu_3 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionProblemResult3());
        $isExitMondai4 = $eikenTestResult->getEikenLevelId() == 1 || $eikenTestResult->getEikenLevelId() == 2 || $eikenTestResult->getEikenLevelId() == 3 || $eikenTestResult->getEikenLevelId() == 4 || $eikenTestResult->getEikenLevelId() == 5;
        $eikenTestResult->mondaisu_4 = $isExitMondai4
            ? $this->convert3byteToArray($eikenTestResult->getLargeQuestionProblemResult4(), $tenDelimiter)
            : $this->convert3byteToArray($eikenTestResult->getLargeQuestionProblemResult4());

        $Daimon_1 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionCorrectAnswer1());
        $Daimon_2 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionCorrectAnswer2());
        $Daimon_3 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionCorrectAnswer3());
        $Daimon_4 = $isExitMondai4
            ? $this->convert3byteToArray($eikenTestResult->getLargeQuestionCorrectAnswer4(), $tenDelimiter)
            : $this->convert3byteToArray($eikenTestResult->getLargeQuestionCorrectAnswer4());

        $path1 = $this->mapDaimonMondaisu($Daimon_1, $eikenTestResult->mondaisu_1);
        $path2 = $this->mapDaimonMondaisu($Daimon_2, $eikenTestResult->mondaisu_2);

        $path3 = $this->mapDaimonMondaisu($Daimon_3, $eikenTestResult->mondaisu_3);
        $path4 = $isExitMondai4
            ? $this->mapDaimonMondaisu($Daimon_4, $eikenTestResult->mondaisu_4, $tenDelimiter, $tenDelimiter)
            : $this->mapDaimonMondaisu($Daimon_4, $eikenTestResult->mondaisu_4);
        $eikenTestResult->totalMondai = array();
        if ($path1->value && $path2->value && $path3->value && ($path4->value || !$isExitMondai4)) {
            if (!$isExitMondai4) {
                $eikenTestResult->valueMondai = array_merge($path1->value, array_merge($path2->value, $path3->value));
                $eikenTestResult->totalMondai = array_merge($path1->total, array_merge($path2->total, $path3->total));
            } else {
                $eikenTestResult->valueMondai = array_merge($path1->value, array_merge($path2->value, array_merge($path3->value, $path4->value)));
                $eikenTestResult->totalMondai = array_merge($path1->total, array_merge($path2->total, array_merge($path3->total, $path4->total)));
            }
        }
        $eikenTestResult->active = '';
        $eikenTestResult->display = '';
        //Exits tab2
        if ($eikenTestResult->getOneExemptionFlag() != 0) {
            $eikenTestResult->active = 'in active';
        }
        preg_match_all('/(\d{1}\-\d{1}|\d{1})/', $eikenTestResult->getCorrectAnswer(), $CorrectAnswer);
        $eikenTestResult->getAnswerNo = $CorrectAnswer[0];
        $eikenTestResult->getTrueFalse = empty($eikenTestResult->getCorrection()) ? array() : str_split(str_replace(' ', '', $eikenTestResult->getCorrection()));
        $eikenTestResult->caseId = $eikenTestResult->getEikenLevelId();
        //Level : 1級
        if (($eikenTestResult->getEikenLevelId() == 1) && count($eikenTestResult->totalMondai) > 5) {
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[4] + $eikenTestResult->totalMondai[5] + (isset($eikenTestResult->totalMondai[6]) ? $eikenTestResult->totalMondai[6] : 0);
        } //Level : 準1級
        else if (($eikenTestResult->getEikenLevelId() == 2) && count($eikenTestResult->totalMondai) > 5) {
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[4] + $eikenTestResult->totalMondai[5];
        } //Level: ２級
        else if ($eikenTestResult->getEikenLevelId() == 3 && count($eikenTestResult->totalMondai) > 8) {
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[4];
        } //Level: 準２級
        else if ($eikenTestResult->getEikenLevelId() == 4 && count($eikenTestResult->totalMondai) > 6) {
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2] + $eikenTestResult->totalMondai[3];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[4] + $eikenTestResult->totalMondai[5] + $eikenTestResult->totalMondai[6];
        } //Level: 3級
        else if($eikenTestResult->getEikenLevelId() == 5 && count($eikenTestResult->totalMondai) > 5){
            $eikenTestResult->caseId = $eikenTestResult->getEikenLevelId();
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[4] + $eikenTestResult->totalMondai[5];
        }
        //Level: 4級
        else if ($eikenTestResult->getEikenLevelId() == 6 && count($eikenTestResult->totalMondai) > 6) {
            $eikenTestResult->caseId = $eikenTestResult->getEikenLevelId();
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2] + $eikenTestResult->totalMondai[3];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[4] + $eikenTestResult->totalMondai[5] + $eikenTestResult->totalMondai[6];
        } //Level: 5級
        else if ($eikenTestResult->getEikenLevelId() == 7 && count($eikenTestResult->totalMondai) > 5) {
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[4] + $eikenTestResult->totalMondai[5];
        }
        if ($eikenTestResult->getOneExemptionFlag() == 0 && count($eikenTestResult->getAnswerNo) != count($eikenTestResult->getTrueFalse)) {
            return false;
        }
        //Exits tab1 not exits tab2
        if ($eikenTestResult->getOneExemptionFlag() == 0 && $eikenTestResult->getEikenLevelId() < 8) {
            $eikenTestResult->display = 'style="display: none"';
            //Fake data local false.
            if (empty($eikenTestResult->part1) || empty($eikenTestResult->part2)) {
                return false;
            }
        }

        return $eikenTestResult;
    }

    /**
     * This function to get personal achievement for result of kai from 2016 to now
     * @param $eikenTestResult
     * @return bool|null|object
     */
    public function getPersonalAchievementEikenLessThan2016($eikenTestResult) {
        $eikenLevel = $this->getEntityManager()->getRepository('Application\Entity\EikenLevel')->find($eikenTestResult->getEikenLevelId());
        $eikenTestResult->getLevelName = $eikenLevel->getLevelName();
        $eikenTestResult->mondaisu_1 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionProblemResult1());
        $eikenTestResult->mondaisu_2 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionProblemResult2());
        $eikenTestResult->mondaisu_3 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionProblemResult3());
        $eikenTestResult->mondaisu_4 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionProblemResult4());
        $Daimon_1 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionCorrectAnswer1());
        $Daimon_2 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionCorrectAnswer2());
        $Daimon_3 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionCorrectAnswer3());
        $Daimon_4 = $this->convert3byteToArray($eikenTestResult->getLargeQuestionCorrectAnswer4());
        $path1 = $this->mapDaimonMondaisu($Daimon_1, $eikenTestResult->mondaisu_1);
        $path2 = $this->mapDaimonMondaisu($Daimon_2, $eikenTestResult->mondaisu_2);
        $path3 = $this->mapDaimonMondaisu($Daimon_3, $eikenTestResult->mondaisu_3);
        $path4 = $this->mapDaimonMondaisu($Daimon_4, $eikenTestResult->mondaisu_4);
        $eikenTestResult->totalMondai = array();
        if ($path1->value && $path2->value && $path3->value && $path4->value) {
            if ($eikenTestResult->getEikenLevelId() == 1 || $eikenTestResult->getEikenLevelId() == 2) {
                $eikenTestResult->valueMondai = array_merge($path1->value, array_merge($path2->value, $path3->value));
                $eikenTestResult->totalMondai = array_merge($path1->total, array_merge($path2->total, $path3->total));
            } else {
                $eikenTestResult->valueMondai = array_merge($path1->value, array_merge($path2->value, array_merge($path3->value, $path4->value)));
                $eikenTestResult->totalMondai = array_merge($path1->total, array_merge($path2->total, array_merge($path3->total, $path4->total)));
            }
        }
        $eikenTestResult->active = '';
        $eikenTestResult->display = '';
        //Exits tab2
        if ($eikenTestResult->getOneExemptionFlag() != 0) {
            $eikenTestResult->active = 'in active';
        }
        preg_match_all('/(\d{1}\-\d{1}|\d{1})/', $eikenTestResult->getCorrectAnswer(), $CorrectAnswer);
        $eikenTestResult->getAnswerNo = $CorrectAnswer[0];
        $eikenTestResult->getTrueFalse = empty($eikenTestResult->getCorrection()) ? array() : str_split(str_replace(' ', '', $eikenTestResult->getCorrection()));
        $eikenTestResult->caseId = $eikenTestResult->getEikenLevelId();
        //Level : 1級
        if (($eikenTestResult->getEikenLevelId() == 1) && count($eikenTestResult->totalMondai) > 5) {
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[4] + $eikenTestResult->totalMondai[5] + (isset($eikenTestResult->totalMondai[6]) ? $eikenTestResult->totalMondai[6] : 0);
        } //Level : 準1級
        else if (($eikenTestResult->getEikenLevelId() == 2) && count($eikenTestResult->totalMondai) > 5) {
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[4] + $eikenTestResult->totalMondai[5];
        } //Level: ２級
        else if ($eikenTestResult->getEikenLevelId() == 3 && count($eikenTestResult->totalMondai) > 5) {
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2] + $eikenTestResult->totalMondai[5];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[4];
        } //Level: 準２級
        else if ($eikenTestResult->getEikenLevelId() == 4 && count($eikenTestResult->totalMondai) > 6) {
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2] + $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[7];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[4] + $eikenTestResult->totalMondai[5] + $eikenTestResult->totalMondai[6];
        } //Level: 3級,4級
        else if (($eikenTestResult->getEikenLevelId() == 5 || $eikenTestResult->getEikenLevelId() == 6) && count($eikenTestResult->totalMondai) > 6) {
            $eikenTestResult->caseId = 56;
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[2] + $eikenTestResult->totalMondai[6];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[4] + $eikenTestResult->totalMondai[5];
        } //Level: 5級
        else if ($eikenTestResult->getEikenLevelId() == 7 && count($eikenTestResult->totalMondai) > 5) {
            $eikenTestResult->part1 = $eikenTestResult->totalMondai[0] + $eikenTestResult->totalMondai[1] + $eikenTestResult->totalMondai[5];
            $eikenTestResult->part2 = $eikenTestResult->totalMondai[2] + $eikenTestResult->totalMondai[3] + $eikenTestResult->totalMondai[4];
        }
        if ($eikenTestResult->getOneExemptionFlag() == 0 && count($eikenTestResult->getAnswerNo) != count($eikenTestResult->getTrueFalse)) {
            return false;
        }
        //Exits tab1 not exits tab2
        if ($eikenTestResult->getOneExemptionFlag() == 0 && $eikenTestResult->getEikenLevelId() < 8) {
            $eikenTestResult->display = 'style="display: none"';
            //Fake data local false.
            if (empty($eikenTestResult->part1) || empty($eikenTestResult->part2) || $eikenTestResult->caseId == 5 || $eikenTestResult->caseId == 6) {
                return false;
            }
        }

        return $eikenTestResult;
    }

    public function mapDaimonMondaisu($Daimon, $mondaisu, $delimiter1 = HistoryConst::MONDAI_KEY, $delimiter2 = HistoryConst::MONDAI_KEY) {
        $value = array();
        $total = array();
        foreach ($mondaisu as $key => $val) {
            if (!empty($val)) {
                $value[] = $Daimon[$key] . $delimiter1.'/' . $val . $delimiter2;
                $total[] = $val;
            }
        }
        $result['total'] = $total;
        $result['value'] = $value;

        return (object) $result;
    }

    /**
     * Convert 3 byte string with delimiter.
     * @param $str
     * @param $delimiter
     * @return array
     */
    public function convert3byteToArray($str, $delimiter = HistoryConst::MONDAI_KEY) {
        return explode($delimiter, str_replace(array('　', ' ', '０', '１', '２', '３', '４', '５', '６', '７', '８', '９'), array('', '', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'), trim($str)));
    }

    /**
     *
     * @param unknown $action
     * @param unknown $organizationNo
     * @param unknown $year
     * @param unknown $term
     */
    public function setDataToSave($organizationNo, $organizationId, $year, $term) {
        $config = $this->getServiceLocator()->get('Config')['eiken_config']['api'];
        // api parameters
        $params = array(
            "dantaino" => $organizationNo,
            "nendo" => $year,
            "kai" => $term
        );
        $em = $this->getEntityManager();
        $eikenScheduleId = $em->getRepository('Application\Entity\EikenSchedule')->getIdByYearKai($year, $term);
        $result = \Dantai\Api\UkestukeClient::getInstance()->callEir2b01($config, $params);
        $this->saveEikenExamResult($result, $organizationNo, $organizationId, $eikenScheduleId, $year, $term);
    }

    /**
     * TaiVH
     * R2-UC10
     * @param unknown $orgNo
     * @param unknown $year
     * @param unknown $kai
     * @param unknown $mappingStatus
     */
    public function getEikenTestResultByOrgNo($orgNo, $year, $kai, $mappingStatus, $offset = 0, $limit = 10) {
        $em = $this->getEntityManager();
        $paginator = $em->getRepository('Application\Entity\EikenTestResult')->getEikenTestResultByOrgNo($orgNo, $year, $kai, $mappingStatus);
        $result = $paginator->getItems($offset, $limit);
        $result = $this->convertEikenTestResult($result);

        return array('data' => $result, 'paginator' => $paginator);
    }

    /**
     * get Status mapping by applyEikenOrgId (examId)
     * @author FPT-DuongTD
     */
    public function getStatusMappingByExamId($applyEikenOrgId = 0) {
        $examById = $this->getApplyEikenOrgById($applyEikenOrgId);
        if ($examById) {
            return $examById->getStatusMapping() ? 1 : 0;
        }

        return 0;
    }

    /**
     * DuongTD
     * reset mapping status, import status, total import .... if there is no data response from Ukestuke
     * this case just occurs with faked data but need to be prevent
     */
    public function resetExamStatus($applyEikenOrgId = 0) {
        $examById = $this->getApplyEikenOrgById($applyEikenOrgId);
        if ($examById) {
            $examById->setStatusMapping(0);
            $examById->setStatusImporting(0);
            $examById->setTotalImport(0);
            try {
                $this->getEntityManager()->persist($examById);
                $this->getEntityManager()->flush();

                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    /**
     * @author FPT-DuongTD
     * get applyEikenOrg by Id
     */
    public function getApplyEikenOrgById($applyEikenOrgId = 0) {
        return $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenOrg')->find($applyEikenOrgId);
    }

    /**
     * DucNa17
     */
    public function saveMappingEiken($keepSessionData) {
        $em = $this->getEntityManager();
        $listItemSuccess = PrivateSession::getData('listItemSuccess');
        $batch = 500;
        if (!empty($listItemSuccess)) {
            $countArr = count($listItemSuccess);
            $listPupilId = array_keys($listItemSuccess);
            $step = ceil($countArr / $batch);
            for ($j = 0; $j < $step; $j++) {
                $nx = 0;
                if ($j == $step - 1) {
                    $nx = $countArr;
                } else {
                    $nx = ($j + 1) * $batch;
                }
                $arrTemp = array_slice($listItemSuccess, $j * $batch, $nx);
                $em->getRepository('Application\Entity\EikenTestResult')->updateTempData($arrTemp);
                $lstEikenScore = $em->getRepository('Application\Entity\EikenTestResult')->getListDataByEikenScore($arrTemp);
                $lstEikenTestResult = $em->getRepository('Application\Entity\EikenTestResult')->getListDataById($arrTemp);
                foreach ($arrTemp as $keys => $eikenTestResultId) {
                    $objEikenScore = $this->objArraySearch($lstEikenScore, $eikenTestResultId, false);
                    $objEikenTestResult = $this->objArraySearch($lstEikenTestResult, $eikenTestResultId, true);
                    $this->insertEikenScoreByEikenTestResult($listPupilId[$keys], $objEikenScore, $objEikenTestResult);
                }
                $em->flush();
                $em->clear();
            }
            if($lstEikenTestResult) {
                $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
                $dantaiService->addOrgToQueue($this->id_org, array_values($lstEikenTestResult)[0]->getYear());
            }
            PrivateSession::clear('listItemSuccess');
        }
        if (!empty($keepSessionData)) {
            $countArr = count($keepSessionData);
            $listEikenTestResultId = array_keys($keepSessionData);
            $step = ceil($countArr / $batch);
            for ($j = 0; $j < $step; $j++) {
                $nx = 0;
                if ($j == $step - 1) {
                    $nx = $countArr;
                } else {
                    $nx = ($j + 1) * $batch;
                }
                $arrTemp = array_slice($keepSessionData, $j * $batch, $nx);
                $arrKeys = array_slice($listEikenTestResultId, $j * $batch, $nx);
                $em->getRepository('Application\Entity\EikenTestResult')->updateTempData($arrKeys);
                $lstEikenScore = $em->getRepository('Application\Entity\EikenTestResult')->getListDataByEikenScore($arrKeys);
                $lstEikenTestResult = $em->getRepository('Application\Entity\EikenTestResult')->getListDataById($arrKeys);
                foreach ($arrTemp as $eikenTestResultId => $mappingItem) {
                    $objEikenScore = $this->objArraySearch($lstEikenScore, $eikenTestResultId, false);
                    $objEikenTestResult = $this->objArraySearch($lstEikenTestResult, $eikenTestResultId, true);
                    $this->insertEikenScoreByEikenTestResult($mappingItem['pupilId'], $objEikenScore, $objEikenTestResult);
                }
                $em->flush();
                $em->clear();
            }
            if($lstEikenTestResult) {
                $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
                $dantaiService->addOrgToQueue($this->id_org, array_values($lstEikenTestResult)[0]->getYear());
            }
        }
        $applyEikenId = PrivateSession::getData('applyEikenId');
        if ($applyEikenId) {
            $ApplyEikenOrg = $em->getRepository('Application\Entity\ApplyEikenOrg')->find($applyEikenId);
            if (!empty($ApplyEikenOrg)) {
                $ApplyEikenOrg->setStatusMapping(1);
                $em->persist($ApplyEikenOrg);
                $em->flush();
            }
        }
    }

    /**
     * Ducna17
     * update temp EikenTestResult and insert temp EikenScore when user change textbox to mapping
     * @param int $pupilId
     * @param int $eikenTestResultId
     */
    public function updateEikenTestResult($eikenTestResultId, $pupilId) {
        if ($pupilId > 0 && $eikenTestResultId > 0) {
            $em = $this->getEntityManager();
            //get info of Pupil by Pupil
            //$infoPupil = $em->getRepository('Application\Entity\Pupil')->find($pupilId);
            //ANHNT
            $infoPupil = $em->getReference('Application\Entity\Pupil', array('id' => $pupilId));

            //update EikenExamResult
            //$eikenTestResultById = $em->getRepository('Application\Entity\EikenTestResult')->find(array('id' => $eikenTestResultId));
            //ANHNT
            $eikenTestResultById = $em->getReference('Application\Entity\EikenTestResult', array('id' => $eikenTestResultId));
            if (!empty($infoPupil->getOrgSchoolYearId())) {
                //$orgSchoolYear = $em->getRepository('Application\Entity\OrgSchoolYear')->findOneBy(array('id' => $infoPupil->getOrgSchoolYearId()));
                //ANHNT
                $orgSchoolYear = $em->getReference('Application\Entity\OrgSchoolYear', array('id' => $infoPupil->getOrgSchoolYearId()));

                $schoolYearName = $orgSchoolYear->getDisplayName();
                $eikenTestResultById->setTempOrgSchoolYearId($infoPupil->getOrgSchoolYearId());
                $eikenTestResultById->setTempSchoolYearName($schoolYearName);

                $schoolYearMapping = $em->getRepository('Application\Entity\SchoolYearMapping')->findOneBy(array(
                    'schoolYearId' => $orgSchoolYear->getSchoolYearId(),
                    'orgCode' => $eikenTestResultById->getSchoolClassification()
                ));

//                 $schoolYearMapping = $em->getReference('Application\Entity\SchoolYearMapping', array(
//                     'schoolYearId' => $orgSchoolYear->getSchoolYearId(),
//                     'orgCode' => $eikenTestResultById->getSchoolClassification()
//                 ));
                if (!empty($schoolYearMapping)) {
                    $dantailSchoolYear = $schoolYearMapping->getSchoolYearCode();
                    if ($dantailSchoolYear !== null) {
                        $eikenTestResultById->setTempDantaiSchoolYearCode($dantailSchoolYear);
                    }
                }
            }
            if (!empty($infoPupil->getClassId())) {
                //$className = $em->getRepository('Application\Entity\ClassJ')->findOneBy(array('id' => $infoPupil->getClassId()))->getClassName();
                //ANHNT
                $className = $em->getReference('Application\Entity\ClassJ', array('id' => $infoPupil->getClassId()))->getClassName();
                $eikenTestResultById->setTempClassId($infoPupil->getClassId());
                $eikenTestResultById->setTempClassName($className);
            }
            $nameKanjiOfPupil = '';
            if (!empty($infoPupil->getFirstNameKanji())) {
                $nameKanjiOfPupil .= $infoPupil->getFirstNameKanji();
            }
            if (!empty($infoPupil->getLastNameKanji())) {
                $nameKanjiOfPupil .= $infoPupil->getLastNameKanji();
            }
            if ($nameKanjiOfPupil !== '') {
                //update vao TempNameKanji
                $eikenTestResultById->setPreTempNameKanji($nameKanjiOfPupil);
            }
            $pupilNo = $infoPupil->getNumber();
            $eikenTestResultById->setTempPupilId($pupilId);
            $eikenTestResultById->setTempPupilNo($pupilNo);
            try {
                $em->persist($eikenTestResultById);
//                 $em->flush();
            } catch (Exception $e) {
                throw $e;
            }
        }
    }
    public static function objArraySearch($array,$value,$flag)
    {
        foreach($array as $arrayInf) {
           if($flag){ if($arrayInf->getId()== $value) {
                return $arrayInf;
            }}else{
                if($arrayInf->getEikenTestResultId()== $value) {
                    return $arrayInf;
                }
            }
        }
        return null;
    }
    /**
     * DucNA17
     * @param int $eikenTestResultId
     * get record of EikenTestResult by id input to EikenScore
     */
    public function insertEikenScoreByEikenTestResult($pupilId,$eikenScore,$eikenTestResult) {
        $em = $this->getEntityManager();
        $flag = false;
        if (empty($eikenScore)) {
            $eikenScore = new \Application\Entity\EikenScore();
            $flag = true;
        }
        $eikenLevelId = $eikenTestResult->getEikenLevelId();
        $eikenScore->setYear($eikenTestResult->getYear());
        $eikenScore->setKai($eikenTestResult->getKai());
        $eikenScore->setReadingScore($eikenTestResult->getCSEScoreReading());
        $eikenScore->setListeningScore($eikenTestResult->getCSEScoreListening());
        $eikenScore->setCSEScoreWriting($eikenTestResult->getCSEScoreWriting());
        $eikenScore->setCSEScoreSpeaking($eikenTestResult->getCSEScoreSpeaking());
        $total = $eikenTestResult->getCSEScoreReading() + $eikenTestResult->getCSEScoreListening() + $eikenTestResult->getCSEScoreWriting() + $eikenTestResult->getCSEScoreSpeaking();
        $eikenScore->setEikenCSETotal($total);
        $eikenScore->setPrimaryPassFailFlag($eikenTestResult->getPrimaryPassFailFlag());
        $eikenScore->setSecondPassFailFlag($eikenTestResult->getSecondPassFailFlag());
        $eikenScore->setEikenTestResultId($eikenTestResult->getId());
        $eikenScore->setPupil($em->getReference('Application\Entity\Pupil', $pupilId));
        $eikenScore->setEikenLevel($em->getReference('\Application\Entity\EikenLevel', $eikenLevelId));
        $eikenScore->setPassFailFlag($eikenTestResult->getIsPass());
        $eikenScore->setCertificationDate($eikenTestResult->getCertificationDate());
        if ($flag == true) {
            $em->persist($eikenScore);
        }
        
    }

    public function getListSchoolYear($orgNo, $key, $year) {
        $em = $this->getEntityManager();
        $listSchoolYear = $em->getRepository('Application\Entity\EikenTestResult')->getListEikenExamHistory($orgNo, $key, $year);

        return $listSchoolYear->getAllItems();
    }

    public function getOrgSchoolYear($orgId) {
        $em = $this->getEntityManager();
        $listSchoolYear = $em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($orgId);
        return $listSchoolYear;
    }

    public function getOrgClass($orgId) {
        $em = $this->getEntityManager();
        $listClass = $em->getRepository('Application\Entity\ClassJ')->getListOrgClass($orgId);
        return $listClass;
    }

    private function checkDataBeforeTrim($data) {
        if ($data != '') {
            return trim($data);
        } else {
            return $data;
        }
    }

     public function setEikenIBALevel($data, $searchYear)
    {
        $pupilIdList = array();
        $list = array();
        foreach ($data as $item) {
            if ($item['pupilId'] != '') {
                array_push($pupilIdList, $item['pupilId']);
            }
        }
        $em = $this->getEntityManager();
        if ($pupilIdList != array()) {
            $list['eiken'] = $em->getRepository('Application\Entity\EikenTestResult')->getEikenLevelByPupilId($pupilIdList, $this->organizationNo, $searchYear);
            $list['iba'] = $em->getRepository('Application\Entity\IBATestResult')->getIBALevelByPupilId($pupilIdList, $this->organizationNo, $searchYear);
        }

        return $list;
    }    
    /**
     * Ducna17 - MinhTN6
     * Get ExamDate
     * @param int $eikenLevelId
     * @param int $year
     * @param int $kai
     */
    public function getExamDateOfEikenByLevelAndYearAndKai($eikenLevelId, $year, $kai) {
        $em = $this->getEntityManager();
        /* @var $eikenSchedule \Application\Entity\EikenSchedule */
        $eikenSchedule = $em->getRepository('Application\Entity\EikenSchedule')->findOneBy(array(
            'year' => $year,
            'kai' => $kai
        ));

        if (in_array($eikenLevelId, array(1, 2, 3, 4, 5))) {

            $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
            $dateRule = $dantaiService->getDateRound2EachKyu($eikenSchedule->getId());

            // 1級 -> 3級
            $eikenDate = 0;
            if(isset($dateRule[$eikenLevelId])){
                $eikenDate = $eikenSchedule->getRound2Day2ExamDate() != Null ? $eikenSchedule->getRound2Day2ExamDate()->format('Y-m-d H:i:s') : 0;
                if($dateRule[$eikenLevelId] === 1){
                    $eikenDate = $eikenSchedule->getRound2Day1ExamDate() != Null ? $eikenSchedule->getRound2Day1ExamDate()->format('Y-m-d H:i:s') : 0;
                }
            }
        } else {
            //4級 -> 5級
            if ($eikenSchedule->getSunDate() == Null && $eikenSchedule->getFriDate() == Null && $eikenSchedule->getSatDate() == Null) {
                $eikenDate = 0;
            } else {
                $sunDate = $eikenSchedule->getSunDate() != Null ? $eikenSchedule->getSunDate()->format('Y-m-d H:i:s') : 0;
                $friDate = $eikenSchedule->getFriDate() != Null ? $eikenSchedule->getFriDate()->format('Y-m-d H:i:s') : 0;
                $satDate = $eikenSchedule->getSatDate() != Null ? $eikenSchedule->getSatDate()->format('Y-m-d H:i:s') : 0;

                $arrWday = array();
                if ($sunDate > 0)
                    $arrWday[] = $sunDate;
                if ($friDate > 0)
                    $arrWday[] = $friDate;
                if ($satDate > 0)
                    $arrWday[] = $satDate;

                $eikenDate = max($arrWday);
            }
        }
        return $eikenDate;
    }
    public function getAjaxListClass($year, $schoolYear)
    {
        $em = $this->getEntityManager();
        $listClass = array();
        if (isset($schoolYear) && $schoolYear) {
            $objClass = $em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYearAndYear($year, $schoolYear, $this->id_org);
            if (!empty($objClass)) {
                foreach ($objClass as $key => $value) {
                    $listClass['classj'][$key] = $value;
                }
            }
        } else {
            $listClass['classj'] = '';
        }
        return $listClass;
    }

    public function listclass($searchYear = false, $searchOrgSchoolYear = false, $flag = false)
    {
        $em = $this->getEntityManager();
        $listclass = array();
        if (!empty($searchOrgSchoolYear) && $flag == false) {
            $objClass = $em->getRepository('Application\Entity\ClassJ')->getHistoryClassBySchoolYearAndYearAjax($searchYear, $searchOrgSchoolYear, $this->id_org);
        }
        if ($flag == true) {
            $objClass = $em->getRepository('Application\Entity\ClassJ')->getListClass($this->id_org);
        }
        if (!empty($objClass)) {
            $listclass[''] = '';
            foreach ($objClass as $key => $value) {
                $listclass[$value['className']] = $value['className'];
            }
        }
        return $listclass;
    }

    public function listshoolyear()
    {
        $em = $this->getEntityManager();
        $yearschool = array();
        $objSchoolyear = $em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->id_org);
        if (isset($objSchoolyear) && $objSchoolyear) {
            $yearschool[''] = '';
            foreach ($objSchoolyear as $key => $value) {
                $yearschool[$value['displayName']] = $value['displayName'];
            }
        }
        return $yearschool;
    }

    public function year()
    {
        $listYear = array();
        $y = (int) 2009;
        $listYear[''] = '';
        for ($i = (int) date('Y'); $i > $y; $i --) {
            $listYear[$i] = $i;
        }
        return $listYear;
    }

    /**
     * @author AnhNT56
     * @param unknown $applyEikenId
     * @param unknown $examName
     */
    public function mappingData1($eikenTestResult, $applyEikenId = 0, $year, $kai, $examName = 'EIKEN') {
        //get list  pupil
        $em = $this->getEntityManager();
        $listPupil = $this->getListPupil($this->id_org, $year);
        $pupilManager = array();
        $i = 0;
        if (!empty($listPupil)) {
            foreach ($listPupil as $key => $item) {
                $keyStr = '';
                if (!empty($item['birthday']) && !empty($item['firstNameKanji']) && !empty($item['lastNameKanji'])) {
                    $keyStr .= $item['birthday']->format('Y-m-d');
                    $keyStr .= $item['firstNameKanji'];
                    $keyStr .= $item['lastNameKanji'];
                    if (!empty($pupilManager[$keyStr])) {
                        //exist;
                        array_push($pupilManager[$keyStr], $item);
                    } else {
                        // no exist
                        $pupilManager[$keyStr][] = $item;
                    }
                }
            }
        }
        //get eiken test result
        //$eikenTestResult = $this->getEikenTestResult($year, $kai,$this->organizationNo);
        //mapping data
        $mappingError = array();
        $mappingSuccess = array();
        $mappingCoincident = array();
        if (!empty($eikenTestResult)) {
            foreach ($eikenTestResult as $key => $item) {
                if (sizeof($item) > 1) {
                    if (!empty($pupilManager[$key])) {
                        $mappingCoincident[$key] = $item;
                    } else {
                        $mappingError[$key] = $item;
                    }
                } elseif (sizeof($item) == 1) {
                    if (!empty($pupilManager[$key])) {
                        //duplicate
                        if (sizeof($pupilManager[$key]) > 1) {
                            $mappingCoincident[$key] = $item;
                        } else {
                            //success
                            $mappingSuccess[$key] = $item;
                        }
                    } else {
                        // error
                        $mappingError[$key] = $item;
                    }
                }
            }
            if (!empty($mappingSuccess)) {
                $listItemSuccess = array();
                foreach ($mappingSuccess as $key => $itemsSuccess) {
                    foreach ($itemsSuccess as $itemSuccess) {
                        $listItemSuccess[$pupilManager[$key][0]['id']] = $itemSuccess['id'];
                        $this->updateEikenTestResult($itemSuccess['id'], $pupilManager[$key][0]['id']);
                        $i++;
                        if ($i == 300) {
                            $em->flush();
                            $em->clear();
                            $i = 0;
                        }
                    }
                }
                $em->flush();
                $em->clear();
                $i = 0;
                PrivateSession::setData('listItemSuccess', $listItemSuccess);
            }
            //             $mappingData = array(
            //                 'mappingError' => $mappingError,
            //                 'mappingSuccess' => $mappingSuccess,
            //                 'mappingCoincident' => $mappingCoincident//duplicate
            //             );
        }

        //mapping status
        if (sizeof($mappingError) > 0) {
            foreach ($mappingError as $items) {
                foreach ($items as $item) {
                    //ANHNT
                    $updatePupilId = $em->getReference('Application\Entity\EikenTestResult', array(
                        'id' => $item['id']
                    ));
                    $updatePupilId->setMappingStatus(0);
                    $i++;
                }
            }
            $em->flush();
            $em->clear();
        }
        if (sizeof($mappingSuccess) > 0) {
            foreach ($mappingSuccess as $key => $items) {
                $pupilId = $pupilManager[$key][0]['id'];
                foreach ($items as $item) {
                    $updatePupilId = $em->getReference('Application\Entity\EikenTestResult', array(
                        'id' => $item['id']
                    ));
                    $updatePupilId->setTempPupilId($pupilId);
                    $updatePupilId->setPupilId($pupilId);
                    $updatePupilId->setMappingStatus(1);
                }
            }
            $em->flush();
            $em->clear();
        }
        if (sizeof($mappingCoincident > 0)) {
            foreach ($mappingCoincident as $key => $items) {
                $pupilId = $pupilManager[$key][0]['id'];
                foreach ($items as $item) {
                    $updatePupilId = $em->getReference('Application\Entity\EikenTestResult', array(
                        'id' => $item['id']
                    ));
                    $updatePupilId->setTempPupilId($pupilId);
                    $updatePupilId->setMappingStatus(2);
                    $i++;
                }
            }
            $em->flush();
            $em->clear();
        }
    }
    public function getListClass($orgNo , $type)
    {
        $em = $this->getEntityManager();
        $listclass = array();
        $objClass = $em->getRepository('Application\Entity\EikenTestResult')->getListGradeClass($orgNo,$type);
        if (!empty($objClass)) {
            $listclass[''] = '';
            foreach ($objClass as $key => $value) {
                $listclass[$value[$type]] = $value[$type];
            }
        }
        return $listclass;
    }
    //get List Percent for Progess Bar
    public function getListPercentPB($cSEScoreWidthId,$cSEScoreByKyu,$lim,$score){
        $result = array();
        $config = $this->getServiceLocator()->get('config');
        $mappingLevel = $config['MappingLevel'];
        $avg = $cSEScoreByKyu['cseBand1'];
        $max = intval($avg + $lim);
        $min = intval($avg - $lim);
        if($score < $min){
            $result['score'] = 0.5;
        }else{
            $score = $this->getPercent($max ,$min , $score);
            $point = $score;
            if($score > 100){
                $point = 99.5;
            }
            if($score == 100){
                $point = 98.95;
            }
            if($score == 0){
                $point = 1;
            }
            if($score < 0){
                $point = 0.5;
            }
            if($score > 98 && $score < 100){
                $point = $score - 1;
            }
            
            if($score > 0 && $score < 2){
                $point = $score + 1;
            }
            $result['score'] = $point;
        }
        if ($cSEScoreWidthId) {
            foreach ($cSEScoreWidthId as $key => $value) {
                    if ($value['cseBand1'] > $min && $value['cseBand1'] < $max) {
                        $percent = $this->getPercent($max, $min, $value['cseBand1']);
                        $result[$value['eikenLevelId']] = array(
                            'levelName' => $mappingLevel[$value['eikenLevelId']],
                            'percent' => $percent,
                            'point' => $value['cseBand1'],
                        );
                }
            }
        }
        return $result;
    }
    public function getPercent($max , $min ,$x){
        return ($x - $min)/(($max - $min) / 100);
    }

    public function getEikenMasterData($year, $kai,$isInland,$dateOfWeek){
        $em = $this->getEntityManager();
        $listMasterData = $em->getRepository('Application\Entity\EikenResultMasterData')->getEikenMasterData($year, $kai,$isInland,$dateOfWeek);
        return $listMasterData;
    }
    public function getEikenMasterDataByKyu($year, $kai,$isInland,$dateOfWeek,$kyu){
        $em = $this->getEntityManager();
        $masterData = $em->getRepository('Application\Entity\EikenResultMasterData')->getEikenMasterDataByKyu($year, $kai,$isInland,$dateOfWeek,$kyu);
        return $masterData;
    }
    
    //get List Percent for Progess Bar
    public function getListPercentPBR2($cSEScoreWidthId,$cSEScoreByKyu,$lim,$score){
        $result = array();
        $config = $this->getServiceLocator()->get('config');
        $mappingLevel = $config['MappingLevel'];
        $avg = $cSEScoreByKyu['cseBand2'];
        $max = intval($avg + $lim);
        $min = intval($avg - $lim);
        if($score < $min){
            $result['score'] = 0.5;
        }else{
            $score = $this->getPercent($max ,$min , $score);
            $point = $score;
            if($score > 100){
                $point = 99.5;
            }
            if($score == 100){
                $point = 98.95;
            }
            if($score == 0){
                $point = 1;
            }
            if($score < 0){
                $point = 0.5;
            }
            if($score > 98 && $score < 100){
                $point = $score - 1;
            }
            
            if($score > 0 && $score < 2){
                $point = $score + 1;
            }
            $result['score'] = $point;
        }
        if ($cSEScoreWidthId) {
            foreach ($cSEScoreWidthId as $key => $value) {
                    if ($value['cseBand2'] > $min && $value['cseBand2'] < $max) {
                        $percent = $this->getPercent($max, $min, $value['cseBand2']);
                        $result[$value['eikenLevelId']] = array(
                            'levelName' => $mappingLevel[$value['eikenLevelId']],
                            'percent' => $percent,
                            'point' => $value['cseBand2'],
                        );
                }
            }
        }
        return $result;
    }

    public function getDetailPupil($pupil,$orgid){
        $em = $this->getEntityManager();
        return $em->getRepository('Application\Entity\Pupil')->getPupilDetail($pupil,$orgid);
    }

    public function getEikenTestResultRepo(){
        return $this->getEntityManager()->getRepository('Application\Entity\EikenTestResult');
    }

    public function insertOnDuplicateUpdateMultiple($listEikenTestResult){
        return $this->getEikenTestResultRepo()->insertOnDuplicateUpdateMultiple($listEikenTestResult);
    }

    public function updateTempValueAfterImport($orgNo, $year, $kai){
        return $this->getEikenTestResultRepo()->updateTempValueAfterImport($orgNo, $year, $kai);
    }
}
