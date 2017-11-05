<?php

namespace InvitationMnt\Service;

use InvitationMnt\Service\ServiceInterface\RecommendedServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class RecommendedService implements RecommendedServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    function setPupilRecommendLevel($data)
    {
        $recommend = $this->setRecommendLevel($data);
        $recommend = $recommend['data'];
        if (empty($recommend)) {
            return false;
        }
        $listPupilId = array_keys($recommend);
        $EikenSchedule = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->findOneBy(array('year' => $data['year'], 'kai' => $data['kai'], 'isDelete' => 0));
        $EikenScheduleId = (!empty($EikenSchedule)) ? $EikenSchedule->getId() : '';
        //List pupil Id in recommend Level
        $listPupilIdOld = $this->getEntityManager()->getRepository('Application\Entity\RecommendLevel')->duplicateRecommendLevel($EikenScheduleId, $listPupilId);
        $listPupilIdNew = array_flip($listPupilId);
        if (!empty($listPupilIdOld)) {
            foreach ($listPupilIdOld as $val) {
                $RecommendLevelId[$val['pupilId']] = $val['id'];
                unset($listPupilIdNew[$val['pupilId']]);
            }
        }
        if ($EikenScheduleId && $listPupilId && $recommend && $EikenScheduleId) {
            if ($listPupilIdNew) {
                $this->setData($listPupilIdNew, $recommend, $EikenScheduleId, 'insert',false);
            }
            if ($RecommendLevelId) {
                $this->setData($RecommendLevelId, $recommend, $EikenScheduleId, 'update',false);
            }
        }

        return true;
    }

    function setData($listPupilId, $recommend, $EikenScheduleId, $type = 'insert',$flg = true)
    {
        $em = $this->getEntityManager();
        foreach ($listPupilId as $pupilId => $RecommendLevelId) {
            if ($type == 'insert') {
                $inv = new \Application\Entity\RecommendLevel();
            } else {
                $inv = $em->getRepository('Application\Entity\RecommendLevel')->find($RecommendLevelId);
            }
            $inv->setPupil($em->getReference('Application\Entity\Pupil', $pupilId));
            if (!empty($recommend[$pupilId]['OrgSchoolYearId'])) {
                $inv->setOrgSchoolYear($em->getReference('Application\Entity\OrgSchoolYear', $recommend[$pupilId]['OrgSchoolYearId']));
            }
            if (!empty($EikenScheduleId)) {
                $inv->setEikenSchedule($em->getReference('Application\Entity\EikenSchedule', $EikenScheduleId));
            }
            if (!empty($recommend[$pupilId]['resultLevel']) && $recommend[$pupilId]['resultLevel'] != 0 && $recommend[$pupilId]['resultLevel'] < 8) {
                if($type == 'update' && $flg == true){
                    $inv->setEikenLevel($em->getReference('Application\Entity\EikenLevel', $recommend[$pupilId]['resultLevel']));
                }else if($type == 'update' && $flg == false && $inv->getIsManuallySet() == 0){
                    $inv->setEikenLevel($em->getReference('Application\Entity\EikenLevel', $recommend[$pupilId]['resultLevel']));
                }else if($type == 'insert'){
                    $inv->setEikenLevel($em->getReference('Application\Entity\EikenLevel', $recommend[$pupilId]['resultLevel']));
                }
            }
            if($flg == true && $type == 'insert'){
                $inv->setIsManuallySet(1);
            }else if($flg == true && $type == 'update'){
                $inv->setIsManuallySet(1);
            }
            $em->persist($inv);
        }
        $em->flush();
        $em->clear();
    }

    //function set level to IBA, EIKEN,SIMPLETESTRESULT,STANDARDLEVEL.
    function setRecommendLevel($data)
    {
        $recommend = $this->getResultRecommend($data);
        if (empty($recommend['data'])) {
            return false;
        }
        foreach ($recommend['data'] as $key => $val) {
            $Iba = array(
                'examDate'        => $val['examDateIBA'],
                'eikenLevelId'    => $val['IBA'],
                'IBAPassFailFlag' => $val['IBAPassFailFlag']
            );
            $Eiken = array(
                'certificationDate' => $val['examDateE1'],
                'eikenLevelId'      => $val['EikenTestResultLevel'],
                'EikenPassFailFlag' => $val['EikenPassFailFlag']
            );
            $Level = $this->getIbaEiken($Iba, $Eiken);
            if (empty($Level)) {
                $Level = ($val['resultVocabularyId'] > $val['resultGrammarId']) ? $val['resultVocabularyId'] : $val['resultGrammarId'];
                if (empty($Level)) {
                    $Level = $val['standardlevelId'];
                }
            }
            if(isset($recommend['data'][$key]['isManuallySet'])){
                if($recommend['data'][$key]['isManuallySet'] == 0){
                    $recommend['data'][$key]['resultLevel'] = $Level;
                }
            }else{
                $recommend['data'][$key]['resultLevel'] = $Level;
            }
        }
        return $recommend;
    }

    function getIbaEiken($Iba, $Eiken)
    {
        $dateIba = (empty($Iba['examDate'])) ? false : $Iba['examDate']->format('Y-m-d');
        $levelIBA = empty($dateIba) ? false : $Iba['eikenLevelId'];
        if (empty($Eiken['certificationDate'])) {
            $levelE = false;
            $dateE = false;
        } else {
            $levelE = empty($Eiken) ? false : $Eiken['eikenLevelId'];
            $dateE = empty($Eiken['certificationDate']) ? false : $Eiken['certificationDate']->format('Y-m-d');
        }
        if ($levelE == false) {
            if ($dateIba) {
                return $Iba['eikenLevelId'];
            } else {
                return false;
            }
        } else if ($dateIba <= $dateE) {
            if ($this->checkPass($Eiken)) {
                return ($levelE > 1) ? $levelE - 1 : $levelE;
            } else {
                return $levelE;
            }
        } else {
            if ($levelIBA && $this->checkPass($Eiken)) {
                if ($levelIBA < $levelE) {
                    return $levelIBA;
                } else {
                    return ($levelE > 1) ? $levelE - 1 : $levelE;
                }
            } else {
                return $levelE;
            }
        }
    }

    function checkPass($Eiken = false)
    {
        if ((int)$Eiken['EikenPassFailFlag'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    //Get result recommendLevel
    function getResultRecommend($data)
    {
        $result = array();
        //get list search
        $paginator = $this->getEntityManager()->getRepository('Application\Entity\RecommendLevel')->getResultRecommend($data['orgId'], $data['year'], $data['kai'], $data['OrgSchoolYearId'], $data['classId'], $data['EikenLevelId'], $data['name']);
        $result['paginator'] = $paginator;
        $result['vaildateStandardlevel'] = $this->vaildateStandardlevel($paginator->getAllItems());
        $recommendLevel = $paginator->getItems( $data['offset'], $data['limit'] );
        $listPupil = array_keys($recommendLevel);
        if (!empty($listPupil)) {
            $last6year = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d"), date("Y") - 6));
            $last3year = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d"), date("Y") - 3));
            $IBAScore = $this->mapScore($this->getEntityManager()->getRepository('Application\Entity\RecommendLevel')->getIBAScore($listPupil, $last3year));
            $EikenScore = $this->mapScore($this->getEntityManager()->getRepository('Application\Entity\RecommendLevel')->getEikenScore($listPupil, $last6year));
            $recommendLevel = $this->array_merge($recommendLevel, $this->array_merge($IBAScore, $EikenScore));

            foreach ($recommendLevel as $key => $val) {
                if (empty($recommendLevel[$key]['IBATestResultId'])) {
                    $recommendLevel[$key]['IBATestResultId'] = '';
                    $recommendLevel[$key]['IBA'] = '';
                    $recommendLevel[$key]['examDateIBA'] = '';
                    $recommendLevel[$key]['IBAPassFailFlag'] = '';
                }
                if (empty($recommendLevel[$key]['EikenTestResultId'])) {
                    $recommendLevel[$key]['EikenTestResultId'] = '';
                    $recommendLevel[$key]['examDateE1'] = '';
                    $recommendLevel[$key]['EikenPassFailFlag'] = '';
                    $recommendLevel[$key]['EikenTestResultLevel'] = '';
                }
            }
        }
        $result['data'] = $recommendLevel;

        return $result;
    }

    function mapScore($Score = array())
    {
        $ScoreNew = array();
        if (!empty($Score)) {
            foreach ($Score as $id => $data) {
                if (empty($ScoreNew[$data['PupilId']])) {
                    $ScoreNew[$data['PupilId']] = $data;
                }
            }
        }

        return $ScoreNew;
    }

    function array_merge($array1 = array(), $array2 = array())
    {
        foreach ($array2 as $key => $data) {
            foreach ($data as $field => $value) {
                $array1[$key][$field] = $value;
            }
        }

        return $array1;
    }

    function saveSimpleTestLevel($gm_result, $listId)
    {
        $em = $this->getEntityManager();
        $gm_result = $this->mappingLevel($gm_result); // Mapping level api to level local [{1級; 準1級; 2級; 準2級; 3級; 4級; 5級}]
        foreach ($gm_result as $key => $val) {
            unset($gm_result[$key]);
            $gm_result[$listId[$val->personal_id]['id']] = $val;
        }
        $listPupilId = array_keys($gm_result);
        $listPupilIdOld = $this->getEntityManager()->getRepository('Application\Entity\RecommendLevel')->getSimpleMeasurementResultId($listPupilId);
        try {
            $this->setdataSimple($gm_result, array_flip($listPupilId), $listPupilIdOld);
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }

    function setdataSimple($gm_result, $listPupilId, $listPupilIdOld)
    {
        //update
        $em = $this->getEntityManager();
        if (!empty($listPupilIdOld)) {
            foreach ($listPupilIdOld as $pupilId => $value) {
                unset($listPupilId[$pupilId]);
                $inv = $em->getReference('Application\Entity\SimpleMeasurementResult', $value['id']);
                $inv->setPupil($em->getReference('Application\Entity\Pupil', $pupilId));
                $inv->setResultDate(new \DateTime(date('Y-m-d H:i:s', strtotime($gm_result[$pupilId]->result_date))));
                $inv->setResultVocabulary($em->getReference('Application\Entity\EikenLevel', $gm_result[$pupilId]->result_vocabulary));
                $inv->setResultVocabularyName($gm_result[$pupilId]->result_vocabularyName);
                $inv->setResultGrammar($em->getReference('Application\Entity\EikenLevel', $gm_result[$pupilId]->result_grammar));
                $inv->setResultGrammarName($gm_result[$pupilId]->result_grammarName);
                $em->persist($inv);
            }
            $em->flush();
            $em->clear();
        }
        //insert
        if (!empty($listPupilId)) {
            foreach ($listPupilId as $pupilId => $value) {
                $inv = new \Application\Entity\SimpleMeasurementResult();
                $inv->setPupil($em->getReference('Application\Entity\Pupil', $pupilId));
                $inv->setResultDate(new \DateTime(date('Y-m-d H:i:s', strtotime($gm_result[$pupilId]->result_date))));
                $inv->setResultVocabulary($em->getReference('Application\Entity\EikenLevel', $gm_result[$pupilId]->result_vocabulary));
                $inv->setResultVocabularyName($gm_result[$pupilId]->result_vocabularyName);
                $inv->setResultGrammar($em->getReference('Application\Entity\EikenLevel', $gm_result[$pupilId]->result_grammar));
                $inv->setResultGrammarName($gm_result[$pupilId]->result_grammarName);
                $em->persist($inv);
            }
            $em->flush();
            $em->clear();
        }
    }

    // mapping level api getSimpleTest
    function mappingLevel($gm_result)
    {
        $level = array('A0' => 7, 'A1L' => 7, 'A1' => 6, 'A1U' => 5, 'A2' => 4, 'B1' => 3, 'B2' => 2, 'C1' => 1, 'C2' => 1);
        $levelName = array(7 => '5級', 6 => '4級', 5 => '3級', 4 => '準2級', 3 => '2級', 2 => '準1級', 1 => '1級',);
        foreach ($gm_result as $key => $value) {
            if (substr($gm_result[$key]->result_vocabulary, 0, 1) == 'B' || substr($gm_result[$key]->result_vocabulary, 0, 1) == 'C') {
                $gm_result[$key]->result_vocabulary = substr($gm_result[$key]->result_vocabulary, 0, 2);
            }
            if (substr($gm_result[$key]->result_grammar, 0, 1) == 'B' || substr($gm_result[$key]->result_grammar, 0, 1) == 'C') {
                $gm_result[$key]->result_grammar = substr($gm_result[$key]->result_grammar, 0, 2);
            }
            $gm_result[$key]->result_vocabulary = ($level[$gm_result[$key]->result_vocabulary]) ? $level[$gm_result[$key]->result_vocabulary] : null;
            $gm_result[$key]->result_grammar = ($level[$gm_result[$key]->result_grammar]) ? $level[$gm_result[$key]->result_grammar] : null;
            $gm_result[$key]->result_vocabularyName = ($levelName[$gm_result[$key]->result_vocabulary]) ? $levelName[$gm_result[$key]->result_vocabulary] : null;
            $gm_result[$key]->result_grammarName = ($levelName[$gm_result[$key]->result_grammar]) ? $levelName[$gm_result[$key]->result_grammar] : null;
        }

        return $gm_result;
    }

    //function of button save
    function updateRecommendLevel($OrganizationId, $listId, $EikenScheduleId)
    {
        //format array value string to int
        foreach ($listId as $key => $value) {
            if ((int)$value != 0) {
                $listPupilId[$key] = (int)$value;
            }
        }
        $em = $this->getEntityManager();
        if (!empty($listPupilId)) {
            $ListPupilIdOld = $em->getRepository('Application\Entity\RecommendLevel')->getListRecommendPupilId(array_keys($listPupilId), $EikenScheduleId);
        }
        if (!empty($ListPupilIdOld)) {
            foreach ($ListPupilIdOld as $pupilId => $val) {
                if (!empty($listPupilId[$pupilId]) && $listPupilId[$pupilId] > 0 || $listPupilId[$pupilId] < 8) {
                    /*@var $inv \Application\Entity\RecommendLevel */
                    $inv = $em->getReference('Application\Entity\RecommendLevel', $val['id']);
                    $inv->setEikenLevel($em->getReference('Application\Entity\EikenLevel', $listPupilId[$pupilId]));
                    $inv->setIsManuallySet(1);
                    $em->persist($inv);
                    unset($listPupilId[$pupilId]);
                }
            }
            $em->flush();
            $em->clear();
        }
        if (!empty($listPupilId)) {
            //check data pupil id;
            $recommend = $em->getRepository('Application\Entity\RecommendLevel')->getResultPupil($OrganizationId, array_keys($listPupilId), $EikenScheduleId);
            foreach ($listPupilId as $key => $val) {
                $recommend[$key]['resultLevel'] = $val;
            }
            $this->setData($listPupilId, $recommend, $EikenScheduleId, $type = 'insert',true);
        }
    }

    public function checkDateLine($year, $kai)
    {
        $EikenSchedule = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->findOneBy(array('year' => $year, 'kai' => $kai));
        if (!empty($EikenSchedule) && $EikenSchedule->getDeadlineTo()) {
            return date('Y/m/d') <= $EikenSchedule->getDeadlineTo()->format('Y/m/d');
        }

        return false;
    }

    public function checkStandardLevelSetting($OrganizationId, $year)
    {
        $StandardLevelSetting = $this->getEntityManager()->getRepository('Application\Entity\StandardLevelSetting')->findOneBy(array('organizationId' => $OrganizationId, 'year' => $year));
        if (empty($StandardLevelSetting)) {
            return false;
        }

        return true;
    }

    public function getEikenSchedule($dateStart, $dateEnd)
    {
        $deadline = array();
        $data = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->getAllEikenSchedule();
        if (!empty($data)) {
            foreach ($data as $val) {
                $year[$val['year']] = $val['year'];
                $result[$year[$val['year']]][$val['kai']] = $val['id'];
                $deadline[$year[$val['year']]][$val['id']] = empty($val['deadlineTo']) ? '' : $val['deadlineTo']->format('Y/m/d');
            }
        }
        $results['deadline'] = $deadline;
        for ($i = $dateEnd; $i >= $dateStart; $i--) {
            $results['year'][$i] = empty($result[$i]) ? array() : $result[$i];
        }

        return $results;
    }

    public function getCurrentEikenSchedule($year = '', $id = '')
    {
        if (!empty($id)) {
            $val = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->find($id);

            return (object)array('year' => $val->getYear(), 'kai' => $val->getKai(), 'id' => $val->getId());
        }
        $data = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->getCurrentEikenSchedule();
        if (empty($data)) {
            return (object)array('year' => $year, 'kai' => '', 'id' => '');
        }

        return (object)$data;
    }

    public function getListSchoolYearByYear($orgId, $year)
    {
        return $this->getEntityManager()->getRepository('Application\Entity\ClassJ')->ListSchoolYearByYear($orgId, $year);
    }

    public function getListClass($year, $orgSchoolYear, $orgId)
    {
        return $this->getEntityManager()->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYearAndYear($year, $orgSchoolYear, $orgId);
    }

    public function getEikenLevel()
    {
        return $this->getEntityManager()->getRepository('Application\Entity\EikenLevel')->ListEikenLevel();
    }

    public function mapEikenLevel()
    {
        $EikenLevel = array();
        foreach ($this->getEikenLevel() as $val) {
            $EikenLevel[$val['id']] = $val['levelName'];
        }

        return $EikenLevel;
    }

    protected function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
    public function vaildateStandardlevel($data)
    {
        $result = 1;
        if (!empty($data)) {
            foreach ($data as $values){
                if (empty($values['standardlevelId'])) {
                        $result = 0;
                }
            }
        }
        return $result;
    }
}