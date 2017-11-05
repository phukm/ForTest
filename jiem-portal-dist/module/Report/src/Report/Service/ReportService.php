<?php

namespace Report\Service;

use Report\Service\ServiceInterface\ReportServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Doctrine\ORM\EntityManager;
use History\HistoryConst;

class ReportService implements ReportServiceInterface, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function getDataGoalResult($orgId, $search, $lastOrgSchoolYearId = 0) {
        $classIds = array();
        $goalResultSchoolYear = array();
        $goalResultClass = array();
        $graduationGoal = array();
        $em = $this->getEntityManager();

        $data = $em->getRepository('Application\Entity\GoalResults')->getListDataByOrgAndArraySearch($orgId, $search);

        if ($data) {
            foreach ($data as $value) {
                if (trim($value['objectType']) == 'OrgSchoolYear') {
                    $goalResultSchoolYear[$value['year']][$value['objectId']] = $value;
                }
                if (trim($value['objectType']) == 'Class') {
                    $goalResultClass[$value['year']][$value['referenceId']][$value['objectId']] = $value;
                    $classIds[] = $value['objectId'];
                }
            }
        }

        $classJ = $em->getRepository('Application\Entity\ClassJ')->getDataClassByClassIds($classIds);

        $dataGoal = $em->getRepository('Application\Entity\OrgGraduationGoal')->getListDataByOrgAndArraySearch($orgId, $search);
        if ($dataGoal) {
            foreach ($dataGoal as $value) {
                if(!empty($value['orgSchoolYearId'])){
                    $graduationGoal[$value['year']][$value['orgSchoolYearId']] = $value;
                }else{
                    $graduationGoal[$value['year']][$lastOrgSchoolYearId] = $value;
                }
                
            }
        }
        return array($goalResultSchoolYear, $goalResultClass, $graduationGoal, $classJ);
    }

    public function getDataGoalResultOfOrgSchoolYears($orgId, $search, $lastOrgSchoolYearId = 0) {

        $goalResultSchoolYear = array();
        $graduationGoal = array();
        $schoolYearIds = array();
        $em = $this->getEntityManager();

        $data = $em->getRepository('Application\Entity\GoalResults')->getListDataByOrgAndArraySearch($orgId, $search);

        if ($data) {
            foreach ($data as $value) {
                if (trim($value['objectType']) == 'OrgSchoolYear') {
                    $goalResultSchoolYear[$value['year']][$value['objectId']] = $value;
                }
            }
        }
        
        $dataGoal = $em->getRepository('Application\Entity\OrgGraduationGoal')->getListDataByOrgAndArraySearch($orgId, $search);
        if ($dataGoal) {
            foreach ($dataGoal as $value) {
                if(!empty($value['orgSchoolYearId'])){
                    $graduationGoal[$value['year']][$value['orgSchoolYearId']] = $value;
                }else{
                    $graduationGoal[$value['year']][$lastOrgSchoolYearId] = $value;
                }
            }
        }
        return array($goalResultSchoolYear, $graduationGoal);
    }

    public function getDataCseResult($orgId, $search) {
        $cseScore = array();
        $cseResultTitle = array();
        $objectType = !empty($search['objectType']) ? trim($search['objectType']) : 'Organization';
        $em = $this->getEntityManager();
        $cseResulRepo = $em->getRepository('Application\Entity\CseResults');
        $cseResult = $cseResulRepo->getDataCseByOrgIdAndObjTypeAndArraySearch($orgId, $objectType, $search);
        foreach ($cseResult as $value) {
            $yearIBA = !empty($value['testDate']) ? $value['testDate']->format('Y') : '0000';
            $monthIBA = !empty($value['testDate']) ? $value['testDate']->format('n') : '0';
            
            $cseScore['avg'][] = $value['averageScore'];
            $cseScore['min'][] = $value['lowestScore'];
            $cseScore['max'][] = $value['highestScore'];
            
            $cseScore['reading'][] = $value['averageReadingScore'];
            $cseScore['listening'][] = $value['averageListeningScore'];

            if ($value['type'] == 'EIKEN') {
                $cseResultTitle[] = '英検：' . $value['year'] . '年度第' . $value['kai'] . '回 - ';
                $cseScore['speaking'][] = $value['averageSpeakingScore'];
                $cseScore['writing'][] = $value['averageWritingScore'];
            } else if ($value['type'] == 'IBA') {
                $cseResultTitle[] = '英検IBA：' . $yearIBA . '年' . $monthIBA . '月 - ';
                $cseScore['speaking'][] = -1;
                $cseScore['writing'][] = -1;
            } else {
                $cseResultTitle[] = '能力向上事業：' . $yearIBA . '年' . $monthIBA . '月 - ';
                $cseScore['speaking'][] = -1;
                $cseScore['writing'][] = -1;
            }
        }
        
        return array($cseResult, $cseScore, $cseResultTitle);
    }

}
