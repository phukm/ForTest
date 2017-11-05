<?php

/**
 * @author minhbn1<minhbn1@fsoft.com.vn>
 */

namespace ConsoleInvitation\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class GoalResultService implements ServiceLocatorAwareInterface {

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
     * Lua ket qua thuc te dua vao muc tieu
     * 
     * @param type $data
     * @return boolean
     */
    function saveActualGoal($data = array()) {
        $em = $this->getEntityManager();
        $RA = isset($data['RA']) ? $data['RA'] : null;
        $objectId = isset($data['objectId']) ? $data['objectId'] : null;
        $objectType = isset($data['objectType']) ? $data['objectType'] : null;
        $referenceId = isset($data['referenceId']) ? $data['referenceId'] : null;
        $organizationId = isset($data['organizationId']) ? $data['organizationId'] : null;
        $year = isset($data['year']) ? $data['year'] : (int) date('Y');
        $achievementService = isset($data['achievementService']) ? $data['achievementService'] : null;
        //
        if (!$RA || !$objectId || !$objectType || !$organizationId || !$achievementService || !in_array($objectType, $this->getObjectTypes()))
            return false;
        //
        $goalResult = new \Application\Entity\GoalResults();
        //
        $tfunction = ''; // Function to get number of pupil pass
        $numberFunction = ''; // Function to get number of pupil
        switch ($objectType) {
            case $this->getOrgSchoolYearType():
                $tfunction = 'getTAs';
                $numberFunction = 'getTs';
                break;
            case $this->getClassType():
                $tfunction = 'getTAc';
                $numberFunction = 'getTc';
                break;
        }
        $mapField = $this->mapFieldWithEiken();
        //
        $goalResult->setType('Actual');
        $goalResult->setObjectId($objectId);
        $goalResult->setObjectType($objectType);
        $goalResult->setReferenceId($referenceId);
        $goalResult->setOrganizationId($organizationId);
        $goalResult->setYear($year);
        $goalResult->setNumberOfPeople($achievementService->$numberFunction($objectId, $year));
        $goalResult->setPeoplePassLevel5($achievementService->$tfunction($mapField['PeoplePassLevel5'], $objectId, $year));
        $goalResult->setPrecentPassLevel5($RA[$mapField['PeoplePassLevel5']]);
        $goalResult->setPeoplePassLevel4($achievementService->$tfunction($mapField['PeoplePassLevel4'], $objectId, $year));
        $goalResult->setPrecentPassLevel4($RA[$mapField['PeoplePassLevel4']]);
        $goalResult->setPeoplePassLevel3($achievementService->$tfunction($mapField['PeoplePassLevel3'], $objectId, $year));
        $goalResult->setPrecentPassLevel3($RA[$mapField['PeoplePassLevel3']]);
        $goalResult->setPeoplePassLevelPre2($achievementService->$tfunction($mapField['PeoplePassLevelPre2'], $objectId, $year));
        $goalResult->setPrecentPassLevelPre2($RA[$mapField['PeoplePassLevelPre2']]);
        $goalResult->setPeoplePassLevel2($achievementService->$tfunction($mapField['PeoplePassLevel2'], $objectId, $year));
        $goalResult->setPrecentPassLevel2($RA[$mapField['PeoplePassLevel2']]);
        $goalResult->setPeoplePassLevelPre1($achievementService->$tfunction($mapField['PeoplePassLevelPre1'], $objectId, $year));
        $goalResult->setPrecentPassLevelPre1($RA[$mapField['PeoplePassLevelPre1']]);
        $goalResult->setPeoplePassLevel1($achievementService->$tfunction($mapField['PeoplePassLevel1'], $objectId, $year));
        $goalResult->setPrecentPassLevel1($RA[$mapField['PeoplePassLevel1']]);
        $goalResult->setUpdateAt(new \DateTime(date('Y-m-d H:i:s')));
        $em->persist($goalResult);
        $em->flush();
    }

    /**
     * Lua ket qua gia dinh dua vao muc tieu
     * 
     * @param type $data
     * @return boolean
     */
    function saveDeemGoal($data = array()) {
        $em = $this->getEntityManager();
        $RD = isset($data['RD']) ? $data['RD'] : null;
        $objectId = isset($data['objectId']) ? $data['objectId'] : null;
        $objectType = isset($data['objectType']) ? $data['objectType'] : null;
        $referenceId = isset($data['referenceId']) ? $data['referenceId'] : null;
        $organizationId = isset($data['organizationId']) ? $data['organizationId'] : null;
        $year = isset($data['year']) ? $data['year'] : (int) date('Y');
        $achievementService = isset($data['achievementService']) ? $data['achievementService'] : null;
        //
        if (!$RD || !$objectId || !$objectType || !$organizationId || !$achievementService || !in_array($objectType, $this->getObjectTypes()))
            return false;
        //
        $goalResult = new \Application\Entity\GoalResults();
        //
        $tfunction = '';
        switch ($objectType) {
            case $this->getOrgSchoolYearType():
                $tfunction = 'getTDs';
                $numberFunction = 'getTs';
                break;
            case $this->getClassType():
                $tfunction = 'getTDc';
                $numberFunction = 'getTc';
                break;
        }
        $mapField = $this->mapFieldWithEiken();
        //
        $goalResult->setType('Deem');
        $goalResult->setObjectId($objectId);
        $goalResult->setObjectType($objectType);
        $goalResult->setReferenceId($referenceId);
        $goalResult->setOrganizationId($organizationId);
        $goalResult->setYear($year);
        $goalResult->setNumberOfPeople($achievementService->$numberFunction($objectId, $year));
        $goalResult->setPeoplePassLevel5($achievementService->$tfunction($mapField['PeoplePassLevel5'], $objectId, $year));
        $goalResult->setPrecentPassLevel5($RD[$mapField['PeoplePassLevel5']]);
        $goalResult->setPeoplePassLevel4($achievementService->$tfunction($mapField['PeoplePassLevel4'], $objectId, $year));
        $goalResult->setPrecentPassLevel4($RD[$mapField['PeoplePassLevel4']]);
        $goalResult->setPeoplePassLevel3($achievementService->$tfunction($mapField['PeoplePassLevel3'], $objectId, $year));
        $goalResult->setPrecentPassLevel3($RD[$mapField['PeoplePassLevel3']]);
        $goalResult->setPeoplePassLevelPre2($achievementService->$tfunction($mapField['PeoplePassLevelPre2'], $objectId, $year));
        $goalResult->setPrecentPassLevelPre2($RD[$mapField['PeoplePassLevelPre2']]);
        $goalResult->setPeoplePassLevel2($achievementService->$tfunction($mapField['PeoplePassLevel2'], $objectId, $year));
        $goalResult->setPrecentPassLevel2($RD[$mapField['PeoplePassLevel2']]);
        $goalResult->setPeoplePassLevelPre1($achievementService->$tfunction($mapField['PeoplePassLevelPre1'], $objectId, $year));
        $goalResult->setPrecentPassLevelPre1($RD[$mapField['PeoplePassLevelPre1']]);
        $goalResult->setPeoplePassLevel1($achievementService->$tfunction($mapField['PeoplePassLevel1'], $objectId, $year));
        $goalResult->setPrecentPassLevel1($RD[$mapField['PeoplePassLevel1']]);
        $goalResult->setUpdateAt(new \DateTime(date('Y-m-d H:i:s')));
        $em->persist($goalResult);
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
        $qb->delete('\Application\Entity\GoalResults', 'GoalResults')
                ->where('GoalResults.year=' . $year)
                ->andWhere('GoalResults.organizationId= ' . $organizationId);
        return $qb->getQuery()->execute();
    }

    /**
     * 
     * @return array
     */
    function getObjectTypes() {
        return array(
            $this->getOrgSchoolYearType(),
            $this->getClassType(),
        );
    }

    /**
     * OrgSchoolYear Type
     * @return string
     */
    function getOrgSchoolYearType() {
        return 'OrgSchoolYear';
    }

    /**
     * Class type
     * @return string
     */
    function getClassType() {
        return 'Class';
    }

    /**
     * Goal types
     * 
     * @return type
     */
    function getTypes() {
        return array(
            'Actual',
            'Deem',
        );
    }

    /**
     * 
     * @return type
     */
    function mapFieldWithEiken() {
        return array(
            'PeoplePassLevel5' => 7,
            'PrecentPassLevel5' => 7,
            'PeoplePassLevel4' => 6,
            'PrecentPassLevel4' => 6,
            'PeoplePassLevel3' => 5,
            'PrecentPassLevel3' => 5,
            'PeoplePassLevelPre2' => 4,
            'PrecentPassLevelPre2' => 4,
            'PeoplePassLevel2' => 3,
            'PrecentPassLevel2' => 3,
            'PeoplePassLevelPre1' => 2,
            'PrecentPassLevelPre1' => 2,
            'PeoplePassLevel1' => 1,
            'PrecentPassLevel1' => 1,
        );
    }

    /**
     * 
     * @param type $eikenLevelId
     * @param type $listEikenLevel
     */
    function getEikenLevelName($eikenLevelId = 0, $listEikenLevel = array()) {
        $name = '';
        $eikenLevelId = (int) $eikenLevelId;
        if (isset($listEikenLevel[$eikenLevelId]))
            $name = $listEikenLevel[$eikenLevelId]['levelName'];
        return $name;
    }

}
