<?php

/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 * 
 * @author minhbn1<minhbn1@fsoft.com.vn>
 */

namespace ConsoleInvitation\Controller;

use Dantai\Api\JsonClient;
use Dantai\Utility\CharsetConverter;
use Zend\Mvc\Controller\AbstractActionController;
use ConsoleInvitation\Service\AchievementService;
use ConsoleInvitation\Service\GoalResultService;
use ConsoleInvitation\Service\CseResultService;

class ReportController extends AbstractActionController {

    const TIME_RANGE_LIMIT = 18000; // 5e 
    const TIME_RUN = 23;
    const TIME_LAST = 24;
    const MEMORY_LIMIT = '4096M';

    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    /**
     * Cron to get Org Goal and Cse Score and update to DB
     */
    public function analyseAchievementAction() {
        $this->cronProcess(time());
        exit(0);
    }

    function cronProcess($startTime = 0) {
        $start = microtime(true);
        $logProcess = '';
        $env = getenv('APP_ENV');
        // 23h yesterday to 23h today
        if ((int) date('H') < self::TIME_RUN)
            $breakTime = strtotime(date('d-m-Y 00:00:00')) - (self::TIME_LAST - self::TIME_RUN) * 3600;
        else
            $breakTime = strtotime(date('d-m-Y 23:59:59')) + 1 - (self::TIME_LAST - self::TIME_RUN) * 3600;
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        while (true) {
            $endTime = time();
            if ($endTime > $startTime + self::TIME_RANGE_LIMIT)
                break;
            $orgInfo = $dantaiService->getOrgInQueue();
            if (!$orgInfo)
                break;
            if ($env == 'production') {
                if ((int) $orgInfo['Body']['time'] > $breakTime)
                    break;
            }
            $achievementService = new AchievementService($this->getServiceLocator());
            $goalResultService = new GoalResultService($this->getServiceLocator());
            $cseResultService = new CseResultService($this->getServiceLocator());
            $em = $this->getEntityManager();
            //
            $listEikenLevel = $achievementService->getListEikenLevel();
            //
            $organizationId = isset($orgInfo['Body']['orgId']) ? (int) $orgInfo['Body']['orgId'] : 0;
            $year = isset($orgInfo['Body']['year']) ? (int) $orgInfo['Body']['year'] : 0;
            if ($organizationId) {
                if (!$logProcess)
                    $logProcess.='P: ' . $organizationId;
                else
                    $logProcess.=',' . $organizationId;
                // Delete old goal
                $goalResultService->deleteByOrg($organizationId, $year);
                //
                $listOrgSchoolYear = $em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($organizationId);
                //********************************Process Org Goal*************************************************/
                // get goal of org
                foreach ($listOrgSchoolYear as $ogSchoolYear) {
                    //******************************************* Follow OrgSchoolYear *********************************
                    $orgSchoolYearId = $ogSchoolYear['id'];
                    $RDs = array();
                    $RAs = array();
                    foreach ($listEikenLevel as $Eiken) {
                        $EikenId = (int) $Eiken['id'];
                        if (!$EikenId)
                            continue;
                        $RAs[$EikenId] = $achievementService->getRAs($EikenId, $orgSchoolYearId, $year);
                        $RDs[$EikenId] = $achievementService->getRDs($EikenId, $orgSchoolYearId, $year);
                    }
                    //
                    $goalResultService->saveActualGoal(array(
                        'year' => $year,
                        'objectId' => $orgSchoolYearId,
                        'objectType' => $goalResultService->getOrgSchoolYearType(),
                        'referenceId' => $organizationId,
                        'organizationId' => $organizationId,
                        'RA' => $RAs,
                        'achievementService' => $achievementService,
                    ));
                    $goalResultService->saveDeemGoal(array(
                        'year' => $year,
                        'objectId' => $orgSchoolYearId,
                        'objectType' => $goalResultService->getOrgSchoolYearType(),
                        'referenceId' => $organizationId,
                        'organizationId' => $organizationId,
                        'RD' => $RDs,
                        'achievementService' => $achievementService,
                    ));
                    //*******************************************END Follow OrgSchoolYear *********************************
                    //
                    //******************************************* Follow Class *******************************************
                    $Classes = $achievementService->getListClassByOrgSchoolYear($orgSchoolYearId, $year);
                    foreach ($Classes as $class) {
                        $RAc = array();
                        $RDc = array();
                        $ClassId = $class->getId();
                        foreach ($listEikenLevel as $Eiken) {
                            $EikenId = (int) $Eiken['id'];
                            if (!$EikenId)
                                continue;
                            $RAc[$EikenId] = $achievementService->getRAc($EikenId, $ClassId, $year);
                            $RDc[$EikenId] = $achievementService->getRDc($EikenId, $ClassId, $year);
                        }

                        $goalResultService->saveActualGoal(array(
                            'year' => $year,
                            'objectId' => $ClassId,
                            'objectType' => $goalResultService->getClassType(),
                            'referenceId' => $orgSchoolYearId,
                            'organizationId' => $organizationId,
                            'RA' => $RAc,
                            'achievementService' => $achievementService,
                        ));
                        $goalResultService->saveDeemGoal(array(
                            'year' => $year,
                            'objectId' => $ClassId,
                            'objectType' => $goalResultService->getClassType(),
                            'referenceId' => $orgSchoolYearId,
                            'organizationId' => $organizationId,
                            'RD' => $RDc,
                            'achievementService' => $achievementService,
                        ));
                    }

                    unset($RAc);
                    unset($RDc);
                    unset($RAs);
                    unset($RDs);
                    //******************************************* END Follow Class *******************************************
                }
                //********************************END Process Org Goal*************************************************/
                //
            //********************************BEGIN CSE Score******************************************************/
                //
            //********************************BEGIN CSE Eiken Score******************************************************/
                // Delete Eiken and IBA cse old
                $cseResultService->deleteByOrg($organizationId, $year);
                //
                $eikenSchedules = $achievementService->getDistinctKaiOfOrg($organizationId, $year);
                if ($eikenSchedules) {
                    foreach ($eikenSchedules as $schedule) {
                        $Kai = $schedule['kai'];
                        $cseResultService->saveEikenCSE(array(
                            'type' => $cseResultService->getEikenType(),
                            'organizationId' => $organizationId,
                            'objectId' => $organizationId,
                            'objectType' => $cseResultService->getOrgType(),
                            'year' => $year,
                            'kai' => $Kai,
                            'achievementService' => $achievementService,
                        ));
                        foreach ($listOrgSchoolYear as $ogSchoolYear) {
                            $orgSchoolYearId = $ogSchoolYear['id'];
                            $Classes = $achievementService->getListClassByOrgSchoolYear($orgSchoolYearId, $year);
                            if ($Classes) {
                                $cseResultService->saveEikenCSE(array(
                                    'type' => $cseResultService->getEikenType(),
                                    'organizationId' => $organizationId,
                                    'objectId' => $orgSchoolYearId,
                                    'objectType' => $cseResultService->getOrgSchoolYearType(),
                                    'year' => $year,
                                    'kai' => $Kai,
                                    'achievementService' => $achievementService,
                                ));
                                foreach ($Classes as $class) {
                                    $ClassId = $class->getId();
                                    $cseResultService->saveEikenCSE(array(
                                        'type' => $cseResultService->getEikenType(),
                                        'organizationId' => $organizationId,
                                        'objectId' => $ClassId,
                                        'objectType' => $cseResultService->getClassType(),
                                        'year' => $year,
                                        'kai' => $Kai,
                                        'achievementService' => $achievementService,
                                    ));
                                }
                            }
                            $Classes = NULL;
                        }
                    }
                }
                unset($eikenSchedules);
                ////********************************END CSE Eiken Score******************************************************/
                //
            //********************************BEGIN IBA Score******************************************************/
            $this->executeCSEIBA($achievementService, $cseResultService, $organizationId, $year, $listOrgSchoolYear);
                //********************************END IBA Score******************************************************/
                //
            //********************************END CSE Score********************************************************/
            }
            unset($listOrgSchoolYear);
            $achievementService = NULL;
            $goalResultService = NULL;
            $cseResultService = NULL;
            $dantaiService->deleteOrgQueue($organizationId, $year, $orgInfo['ReceiptHandle']);
            usleep(100000); // 1/10 s
        }
        // write Log
        $end = microtime(true);
        echo '[' . date('d-m-Y H:i:s') . '] ' . '[' . $logProcess . ']' . ' [Start At: ' . date('d-m-Y H:i:s', $start) . ' , End: ' . date('d-m-Y H:i:s', $end) . ' , Time spent: ' . ($end - $start) . ']' . "\n";
    }

    /**
     * first run: add All org to queue
     */
    function firstRunAction() {
        $startTime = microtime(true);
        $em = $this->getEntityManager();
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        $orgs = $em->getRepository('Application\Entity\Organization')->findAll();
        $year = $dantaiService->getCurrentYear();
        for ($i = 0; $i < 100; $i++) {
            foreach ($orgs as $org) {
                $organizationId = $org->getId();
                if ($organizationId)
                    $dantaiService->addOrgToQueue($organizationId, $year);
            }
        }
        $endTime = microtime(true);
        echo 'Done, Time spent: ' . ($endTime - $startTime) . "\n";
    }
    
    /*
     * test job
     */
    function testJobAction(){
        $startTime = microtime(true);
        $arrOrgNo = array('36359600', '12978000');
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getEntityManager();
        $query = $em->createQueryBuilder();
        $query->select('organization')
                ->from('Application\Entity\Organization', 'organization')
                ->where('organization.organizationNo IN (' . implode(',', $arrOrgNo) . ')');
        
        $orgs = $query->getQuery()->getArrayResult();
        /*@var $dantaiService \Application\Service\DantaiService */
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        $year = $dantaiService->getCurrentYear();
        foreach($orgs as $org){
            $dantaiService->addOrgToQueue($org['id'], $year);
        }
        $endTime = microtime(true);
        echo 'Done, Time spent: ' . ($endTime - $startTime) . "\n";
    }
    
    function testIbaAction(){
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getEntityManager();
        $achievementService = new AchievementService($this->getServiceLocator());
        $cseResultService = new CseResultService($this->getServiceLocator());
        
        $org = $em->getRepository('Application\Entity\Organization')->findOneBy(array(
            'organizationNo' => '12978000'
        ));
        $organizationId = $org->getId(); //9906
        $year = 2015;
        $listOrgSchoolYear = $em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($organizationId);
        $this->executeCSEIBA($achievementService, $cseResultService, $organizationId, $year, $listOrgSchoolYear);
    }
    
    
    function executeCSEIBA($achievementService, $cseResultService, $organizationId, $year, $listOrgSchoolYear){
        $listExam = $achievementService->getDistinctExamIBAOfOrg($organizationId, $year);
        if ($listExam) {
            foreach ($listExam as $exam) {
                if(!$exam['examType'] || !$exam['jisshiId'])
                    continue;
                
                $typeExam = ($exam['examType'] == '01' || $exam['examType'] == '02') ? $cseResultService->getIBAType() : '';
                $cseResultService->saveIBACSE(array(
                    'type' => $typeExam,
                    'organizationId' => $organizationId,
                    'objectId' => $organizationId,
                    'objectType' => $cseResultService->getOrgType(),
                    'year' => $year,
                    'examDate' => $exam['examDate'], 
                    'examType' => $exam['examType'], 
                    'jisshiId' => $exam['jisshiId'], 
                    'achievementService' => $achievementService,
                ));
                foreach ($listOrgSchoolYear as $ogSchoolYear) {
                    $orgSchoolYearId = $ogSchoolYear['id'];
                    //
                    $classes = $achievementService->getListClassByOrgSchoolYear($orgSchoolYearId, $year);
                    if ($classes) {
                        $cseResultService->saveIBACSE(array(
                            'type' => $typeExam,
                            'organizationId' => $organizationId,
                            'objectId' => $orgSchoolYearId,
                            'objectType' => $cseResultService->getOrgSchoolYearType(),
                            'year' => $year,
                            'examDate' => $exam['examDate'],
                            'examType' => $exam['examType'], 
                            'jisshiId' => $exam['jisshiId'],
                            'achievementService' => $achievementService,
                        ));
                        //
                        foreach ($classes as $class) {
                            $classId = $class->getId();
                            $cseResultService->saveIBACSE(array(
                                'type' => $typeExam,
                                'organizationId' => $organizationId,
                                'objectId' => $classId,
                                'objectType' => $cseResultService->getClassType(),
                                'year' => $year,
                                'examDate' => $exam['examDate'],
                                'examType' => $exam['examType'], 
                                'jisshiId' => $exam['jisshiId'],
                                'achievementService' => $achievementService,
                            ));
                        }
                    }
                }
            }
        }
    }

    
    function executeCSEIBA_Bk($achievementService, $cseResultService, $organizationId, $year, $listOrgSchoolYear){
        $ListDistinctExamDate = $achievementService->getDistinctExamDateOfOrg($organizationId, $year);
        if ($ListDistinctExamDate) {
            foreach ($ListDistinctExamDate as $examDate) {
                if (!isset($examDate['examDate']) || !$examDate['examDate'])
                    continue;
                $cseResultService->saveIBACSE(array(
                    'type' => $cseResultService->getIBAType(),
                    'organizationId' => $organizationId,
                    'objectId' => $organizationId,
                    'objectType' => $cseResultService->getOrgType(),
                    'year' => $year,
                    'examDate' => $examDate['examDate'],
                    'achievementService' => $achievementService,
                ));
                foreach ($listOrgSchoolYear as $ogSchoolYear) {
                    $orgSchoolYearId = $ogSchoolYear['id'];
                    //
                    $Classes = $achievementService->getListClassByOrgSchoolYear($orgSchoolYearId, $year);
                    if ($Classes) {
                        $cseResultService->saveIBACSE(array(
                            'type' => $cseResultService->getIBAType(),
                            'organizationId' => $organizationId,
                            'objectId' => $orgSchoolYearId,
                            'objectType' => $cseResultService->getOrgSchoolYearType(),
                            'year' => $year,
                            'examDate' => $examDate['examDate'],
                            'achievementService' => $achievementService,
                        ));
                        //
                        foreach ($Classes as $class) {
                            $ClassId = $class->getId();
                            $cseResultService->saveIBACSE(array(
                                'type' => $cseResultService->getIBAType(),
                                'organizationId' => $organizationId,
                                'objectId' => $ClassId,
                                'objectType' => $cseResultService->getClassType(),
                                'year' => $year,
                                'examDate' => $examDate['examDate'],
                                'achievementService' => $achievementService,
                            ));
                        }
                    }
                    $Classes = NULL;
                }
            }
        }
        unset($ListDistinctExamDate);
    }
}
