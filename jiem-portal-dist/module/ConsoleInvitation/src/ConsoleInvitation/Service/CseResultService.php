<?php

/**
 * @author minhbn1<minhbn1@fsoft.com.vn>
 */

namespace ConsoleInvitation\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class CseResultService implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    protected $sl;

    const DECIMALLIMIT = 0;

    //
    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        $this->setServiceLocator($serviceManager);
        $this->sl = $this->getServiceLocator();
    }

    /**
     * @return array|object
     */
    public function getEntityManager() {
        return $this->sl->get('doctrine.entitymanager.orm_default');
    }

    /**
     * save Eiken CSE score to DB
     * @param type $data
     * @return null
     */
    function saveEikenCSE($data = array()) {
        $em = $this->getEntityManager();
        $type = isset($data['type']) ? $data['type'] : null;
        $objectId = isset($data['objectId']) ? $data['objectId'] : null;
        $objectType = isset($data['objectType']) ? $data['objectType'] : null;
        $organizationId = isset($data['organizationId']) ? $data['organizationId'] : null;
        $year = isset($data['year']) ? $data['year'] : (int) date('Y');
        $kai = isset($data['kai']) ? $data['kai'] : null;
        $achievementService = isset($data['achievementService']) ? $data['achievementService'] : null;
        //
        if (!$type || !$objectId || !$objectType || !$organizationId || !$achievementService || !$kai || !in_array($objectType, $this->getObjectTypes()))
            return false;
        //
        $alias = '';
        switch ($objectType) {
            case $this->getOrgType(): $alias = 'o';
                break;
            case $this->getOrgSchoolYearType(): $alias = 's';
                break;
            case $this->getClassType(): $alias = 'c';
                break;
        }
        //
        $cseResult = new \Application\Entity\CseResults();
        //
        $cseResult->setType($type);
        $cseResult->setObjectId($objectId);
        $cseResult->setObjectType($objectType);
        $cseResult->setOrganizationId($organizationId);
        $cseResult->setKai($kai);
        $cseResult->setYear($year);
        $cseResult->setTestDate(new \DateTime($achievementService->getEikenTestDate($kai, $organizationId, $year)));
        $cseResult->setUpdateAt(new \DateTime(date('Y-m-d H:i:s')));
        $attendRate = 'getARe' . $alias;
        $cseResult->setAttendRate($achievementService->$attendRate($kai, $objectId, $year));
        $averageScore = 'getASe' . $alias;
        $cseResult->setAverageScore($achievementService->$averageScore($kai, $objectId, $year));
        $lowestScore = 'getLTSe' . $alias;
        $cseResult->setLowestScore($achievementService->$lowestScore($kai, $objectId, $year));
        $highestScore = 'getHTSe' . $alias;
        $cseResult->setHighestScore($achievementService->$highestScore($kai, $objectId, $year));
        $averageReadingScore = 'getARSe' . $alias;
        $cseResult->setAverageReadingScore($achievementService->$averageReadingScore($kai, $objectId, $year));
        $averageListeningScore = 'getALSe' . $alias;
        $cseResult->setAverageListeningScore($achievementService->$averageListeningScore($kai, $objectId, $year));
        $averageSpeakingScore = 'getASSe' . $alias;
        $cseResult->setAverageSpeakingScore($achievementService->$averageSpeakingScore($kai, $objectId, $year));
        $averageWritingScore = 'getAWSe' . $alias;
        $cseResult->setAverageWritingScore($achievementService->$averageWritingScore($kai, $objectId, $year));
        //
        $em->persist($cseResult);
        $em->flush();
    }

    /**
     * save Eiken CSE score to DB
     * @param type $data
     * @return null
     */
    function saveIBACSE($data = array()) {
        $em = $this->getEntityManager();
        $type = isset($data['type']) ? $data['type'] : null;
        $objectId = isset($data['objectId']) ? $data['objectId'] : null;
        $objectType = isset($data['objectType']) ? $data['objectType'] : null;
        $organizationId = isset($data['organizationId']) ? $data['organizationId'] : null;
        $year = isset($data['year']) ? $data['year'] : (int) date('Y');
        $examDate = isset($data['examDate']) ? $data['examDate'] : NULL;
        $examType = isset($data['examType']) ? $data['examType'] : '';
        $jisshiId = isset($data['jisshiId']) ? $data['jisshiId'] : '';
        $achievementService = isset($data['achievementService']) ? $data['achievementService'] : null;
        //
        if (!$examType || !$jisshiId || !$type || !$objectId || !$objectType || !$organizationId || !$achievementService || !in_array($objectType, $this->getObjectTypes()))
            return false;
        //
        $alias = '';
        switch ($objectType) {
            case $this->getOrgType(): $alias = 'o';
                break;
            case $this->getOrgSchoolYearType(): $alias = 's';
                break;
            case $this->getClassType(): $alias = 'c';
                break;
        }
        //
        $cseResult = new \Application\Entity\CseResults();
        //
        $dateString = $examDate->format('Y-m-d H:i:s');
        //
        $cseResult->setType($type);
        $cseResult->setObjectId($objectId);
        $cseResult->setObjectType($objectType);
        $cseResult->setOrganizationId($organizationId);
        $cseResult->setYear($year);
        $cseResult->setExamType($examType);
        $cseResult->setJisshiId($jisshiId);
        $cseResult->setTestDate($examDate);
        $cseResult->setUpdateAt(new \DateTime(date('Y-m-d H:i:s')));
        $attendRate = 'getARi' . $alias;
        $cseResult->setAttendRate($achievementService->$attendRate($objectId, $year, $dateString, $examType, $jisshiId));
        $averageScore = 'getASi' . $alias;
        $cseResult->setAverageScore($achievementService->$averageScore($objectId, $year, $dateString, $examType, $jisshiId));
        $lowestScore = 'getLTSi' . $alias;
        $cseResult->setLowestScore($achievementService->$lowestScore($objectId, $year, $dateString, $examType, $jisshiId));
        $highestScore = 'getHTSi' . $alias;
        $cseResult->setHighestScore($achievementService->$highestScore($objectId, $year, $dateString, $examType, $jisshiId));
        $averageReadingScore = 'getARSi' . $alias;
        $cseResult->setAverageReadingScore($achievementService->$averageReadingScore($objectId, $year, $dateString, $examType, $jisshiId));
        $averageListeningScore = 'getALSi' . $alias;
        $cseResult->setAverageListeningScore($achievementService->$averageListeningScore($objectId, $year, $dateString, $examType, $jisshiId));
        //
        $em->persist($cseResult);
        $em->flush();
    }

    /**
     * Delete follow Org ID
     * @param type $organizationId
     * 
     */
    function deleteByOrg($organizationId = 0, $year = 0) {
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        if (!$organizationId || !$year)
            return false;
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->delete('\Application\Entity\CseResults', 'CseResults')
                ->where('CseResults.year=' . $year)
                ->andWhere('CseResults.organizationId= ' . $organizationId);
        return $qb->getQuery()->execute();
    }

    /**
     * return Object Types
     * @return array
     */
    function getObjectTypes() {
        return array(
            $this->getOrgType(),
            $this->getOrgSchoolYearType(),
            $this->getClassType(),
        );
    }

    /**
     * return Eiken type
     * 
     * @return string
     */
    function getEikenType() {
        return 'EIKEN';
    }

    /**
     * return IBA type
     * 
     * @return string
     */
    function getIBAType() {
        return 'IBA';
    }
    
    /**
     * return IBA type
     * 
     * @return string
     */
    function getIBATypeOther() {
        return '能力向上事業';
    }

    /**
     * return Org type
     * 
     * @return string
     */
    function getOrgType() {
        return 'Organization';
    }

    /**
     * return SchoolYear type
     * 
     * @return string
     */
    function getOrgSchoolYearType() {
        return 'OrgSchoolYear';
    }

    /**
     * return Class type
     * 
     * @return string
     */
    function getClassType() {
        return 'Class';
    }

}
