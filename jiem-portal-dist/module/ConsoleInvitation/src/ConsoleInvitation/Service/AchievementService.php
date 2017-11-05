<?php

/**
 * @description this function process business for Org Goal and CSE score
 * @author minhbn1<minhbn1@fsoft.com.vn>
 */

namespace ConsoleInvitation\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Doctrine\ORM\Query\ResultSetMapping;

class AchievementService implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    protected $sl;
    protected $em;
    protected $temp = array();

    //
    const PASSED = 1;
    const DECIMALLIMIT = 0;
    const NOT_DELETE_VALUE = 0;

    //
    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        $this->setServiceLocator($serviceManager);
        $this->sl = $this->getServiceLocator();
        if ($this->sl)
            $this->em = $this->getEntityManager();
    }

    /**
     * @return array|object
     */
    public function getEntityManager() {
        return $this->sl->get('doctrine.entitymanager.orm_default');
    }

    //*************************************************** BEGIN GOALD *************************************************************
    /**
     * return total pupils of all class of school year (follow organization)
     */
    function getTs($orgSchoolYearId = 0, $year = 0) {
        $orgSchoolYearId = (int) $orgSchoolYearId;
        $year = (int) $year;
        if (!$year)
            $year = (int) date('Y');
        //
        $ts = isset($this->temp ['Ts_' . $orgSchoolYearId . $year]) ? $this->temp ['Ts_' . $orgSchoolYearId . $year] : 0;
        if (!isset($this->temp ['Ts_' . $orgSchoolYearId . $year])) {
            $ts = $this->em->getRepository('Application\Entity\ClassJ')->sumPupilWithOptions(array('year' => $year, 'orgSchoolYearId' => $orgSchoolYearId));
            $this->temp ['Ts_' . $orgSchoolYearId . $year] = $ts;
        }
        return (int) $ts;
    }

    /**
     * return total pupils of Class
     *
     * @param number $classId        	
     * @return unknown
     */
    function getTc($classId = 0) {
        $classId = (int) $classId;
        //
        $tc = isset($this->temp ['Tc_' . $classId]) ? $this->temp ['Tc_' . $classId] : 0;
        if (!isset($this->temp ['Tc_' . $classId])) {
            $tc = $this->em->getRepository('Application\Entity\ClassJ')->sumPupilWithOptions(array('classId' => $classId));
            $this->temp ['Tc_' . $classId] = $tc;
        }
        return (int) $tc;
    }

    /**
     * return number of pupils of OrgSchoolYear 
     * 
     * @param type $organizationId
     * @param type $year
     */
    function getTo($organizationId = 0, $year = 0) {
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        if (!$year)
            $year = (int) date('Y');
        //
        $to = isset($this->temp ['To_' . $organizationId . $year]) ? $this->temp ['To_' . $organizationId . $year] : 0;
        if (!isset($this->temp ['To_' . $organizationId . $year])) {
            $to = $this->em->getRepository('Application\Entity\ClassJ')->sumPupilWithOptions(array('year' => $year, 'organizationId' => $organizationId));
            $this->temp ['To_' . $organizationId . $year] = $to;
        }
        return (int) $to;
    }

    /**
     * return number of people is pass Eiken of schoolyear follow Kai and year
     * 
     * @param type $eikenId
     * @param type $orgSchoolYearId
     * @param type $year
     * @return type
     */
    function getTAs($eikenId = 0, $orgSchoolYearId = 0, $year = 0) {
        $year = (int) $year;
        $eikenId = (int) $eikenId;
        $orgSchoolYearId = (int) $orgSchoolYearId;
        //
        $tas = isset($this->temp ['TAs_' . $eikenId . $orgSchoolYearId . $year]) ? $this->temp ['TAs_' . $eikenId . $orgSchoolYearId . $year] : 0;
        if (!isset($this->temp ['TAs_' . $eikenId . $orgSchoolYearId . $year])) {
            $tas = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->countRecords(
                    array(
                        'year' => $year,
                        'isPass' => self::PASSED,
                        'orgSchoolYearId' => $orgSchoolYearId,
                        'eikenLevelId' => $eikenId,
                    )
            );
            $this->temp ['TAs_' . $eikenId . $orgSchoolYearId . $year] = $tas;
        }
        return (int) $tas;
    }

    /**
     *
     * @return number 'number of people is pass Eiken of class follow Kai and year'
     * @param type $eikenId
     * @param type $classId
     * @param type $year
     * @return type
     */
    function getTAc($eikenId = 0, $classId = 0, $year = 0) {
        $year = (int) $year;
        $classId = (int) $classId;
        $eikenId = (int) $eikenId;
        //
        $tas = isset($this->temp ['TAc_' . $eikenId . $classId . $year]) ? $this->temp ['TAc_' . $eikenId . $classId . $year] : 0;
        if (!isset($this->temp ['TAc_' . $eikenId . $classId . $year])) {
            $tas = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->countRecords(
                    array(
                        'year' => $year,
                        'isPass' => self::PASSED,
                        'classId' => $classId,
                        'eikenLevelId' => $eikenId,
                    )
            );
            $this->temp ['TAc_' . $eikenId . $classId . $year] = $tas;
        }
        return (int) $tas;
    }

    /**
     * The total number of parking certain assumptions Eiken in Kai certain examinations of schoolyear follow year
     * 
     * @param type $eikenId
     * @param type $orgSchoolYearId
     * @param type $year
     * @return type
     */
    function getTDs($eikenId = 0, $orgSchoolYearId = 0, $year = 0) {
        $year = (int) $year;
        $eikenId = (int) $eikenId;
        $orgSchoolYearId = (int) $orgSchoolYearId;
        //
        $tds = isset($this->temp ['TDs_' . $eikenId . $orgSchoolYearId . $year]) ? $this->temp ['TDs_' . $eikenId . $orgSchoolYearId . $year] : 0;
        if (!isset($this->temp ['TDs_' . $eikenId . $orgSchoolYearId . $year])) {
            $ListEikenId = $this->getListEikenUpLine($eikenId);
            foreach ($ListEikenId as $_EikenId) {
                $tds += $this->getTAs($_EikenId, $orgSchoolYearId, $year);
            }
            $this->temp ['TDs_' . $eikenId . $orgSchoolYearId . $year] = $tds;
        }
        return $tds;
    }

    /**
     * The total number of parking certain assumptions Eiken in Kai certain examinations of class Year
     *
     * @param type $eikenId
     * @param type $classId
     * @param type $year
     * @return type
     */
    function getTDc($eikenId = 0, $classId = 0, $year = 0) {
        $year = (int) $year;
        $eikenId = (int) $eikenId;
        $classId = (int) $classId;
        //
        $tdc = isset($this->temp ['TDc_' . $eikenId . $classId . $year]) ? $this->temp ['TDc_' . $eikenId . $classId . $year] : 0;
        if (!isset($this->temp ['TDc_' . $eikenId . $classId . $year])) {
            $ListEikenId = $this->getListEikenUpLine($eikenId);
            foreach ($ListEikenId as $_EikenId) {
                $tdc += $this->getTAc($_EikenId, $classId, $year);
            }
            $this->temp ['TDc_' . $eikenId . $classId . $year] = $tdc;
        }
        return $tdc;
    }

    /**
     * assumption pass rate of OrgSchoolYear
     * @param number $eikenId        	
     * @param number $orgSchoolYearId        	
     * @param number $year        	
     * @return Ambigous <number, multitype:>
     */
    function getGDs($eikenId = 0, $orgSchoolYearId = 0, $year = 0) {
        $gd = isset($this->temp ['GDs_' . $eikenId . $orgSchoolYearId . $year]) ? $this->temp ['GDs_' . $eikenId . $orgSchoolYearId . $year] : 0;
        if (!isset($this->temp ['GDs_' . $eikenId . $orgSchoolYearId . $year])) {
            $Ts = $this->getTs($orgSchoolYearId, $year);
            if ($Ts) {
                $TDs = $this->getTDs($eikenId, $orgSchoolYearId, $year);
                $gd = round(($TDs / $Ts) * 100, self::DECIMALLIMIT);
            }
            $this->temp ['GDs_' . $eikenId . $orgSchoolYearId . $year] = $gd;
        }
        return $gd;
    }

    /**
     * assumption pass rate of class
     * 
     * @param type $eikenId
     * @param type $classId
     * @param type $year
     * @return type
     */
    function getGDc($eikenId = 0, $classId = 0, $year = 0) {
        $gdc = isset($this->temp ['GDc_' . $eikenId . $classId . $year]) ? $this->temp ['GDc_' . $eikenId . $classId . $year] : 0;
        if (!isset($this->temp ['GDc_' . $eikenId . $classId . $year])) {
            $Tc = $this->getTc($classId);
            if ($Tc) {
                $TDc = $this->getTDc($eikenId, $classId, $year);
                $gdc = round(($TDc / $Tc) * 100, self::DECIMALLIMIT);
            }
            $this->temp ['GDc_' . $eikenId . $classId . $year] = $gdc;
        }

        return $gdc;
    }

    /**
     * Actual pass rate of OrgSchoolYear
     * 
     * @param type $eikenId
     * @param type $orgSchoolYearId
     * @param type $year
     * @return type
     */
    function getGAs($eikenId = 0, $orgSchoolYearId = 0, $year = 0) {
        $gas = isset($this->temp ['GAs_' . $eikenId . $orgSchoolYearId . $year]) ? $this->temp ['GAs_' . $eikenId . $orgSchoolYearId . $year] : 0;
        if (!isset($this->temp ['GAs_' . $eikenId . $orgSchoolYearId . $year])) {
            $Ts = $this->getTs($orgSchoolYearId, $year);
            if ($Ts) {
                $TAs = $this->getTAs($eikenId, $orgSchoolYearId, $year);
                $gas = round(($TAs / $Ts) * 100, self::DECIMALLIMIT);
            }
            $this->temp ['GAs_' . $eikenId . $orgSchoolYearId . $year] = $gas;
        }
        return $gas;
    }

    /**
     * Actual pass rate of Class
     * 
     * @param type $eikenId
     * @param type $classId
     * @param type $year
     * @return type
     */
    function getGAc($eikenId = 0, $classId = 0, $year = 0) {
        $gac = isset($this->temp ['GAc_' . $eikenId . $classId . $year]) ? $this->temp ['GAc_' . $eikenId . $classId . $year] : 0;
        if (!isset($this->temp ['GAc_' . $eikenId . $classId . $year])) {
            $Tc = $this->getTc($classId);
            if ($Tc) {
                $TAc = $this->getTAc($eikenId, $classId, $year);
                $gac = round(($TAc / $Tc) * 100, self::DECIMALLIMIT);
            }
            $this->temp ['GAc_' . $eikenId . $classId . $year] = $gac;
        }
        return $gac;
    }

    /**
     * assumption pass rate of OrgSchoolYear
     * 
     * @param type $eikenId
     * @param type $orgSchoolYearId
     * @param type $year
     * @return type
     */
    function getRDs($eikenId = 0, $orgSchoolYearId = 0, $year = 0) {
        return $this->getGDs($eikenId, $orgSchoolYearId, $year);
    }

    /**
     * assumption pass rate of class
     * 
     * @param type $eikenId
     * @param type $classId
     * @param type $year
     * @return type
     */
    function getRDc($eikenId = 0, $classId = 0, $year = 0) {
        return $this->getGDc($eikenId, $classId, $year);
    }

    /**
     * Actual pass rate of OrgSchoolYear
     * 
     * @param type $eikenId
     * @param type $orgSchoolYearId
     * @param type $year
     * @return type
     */
    function getRAs($eikenId = 0, $orgSchoolYearId = 0, $year = 0) {
        return $this->getGAs($eikenId, $orgSchoolYearId, $year);
    }

    /**
     * Actual pass rate of class
     * 
     * @param type $eikenId
     * @param type $classId
     * @param type $year
     * @return type
     */
    function getRAc($eikenId = 0, $classId = 0, $year = 0) {
        return $this->getGAc($eikenId, $classId, $year);
    }

    //*************************************************** END GOALD *************************************************************
    //
    //*************************************************** BEGIN CSE *************************************************************
    /**
     * Total of pupil in EikenTestResults of organization for each Kai
     * 
     * @param type $organizationId
     * @param type $year
     * @return type
     */
    function getCeo($kai = 0, $organizationId = 0, $year = 0) {
        $kai = (int) $kai;
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        //
        $ceo = isset($this->temp ['Ceo_' . $kai . $organizationId . $year]) ? $this->temp ['Ceo_' . $kai . $organizationId . $year] : 0;
        if (!isset($this->temp ['Ceo_' . $kai . $organizationId . $year])) {
            $ceo = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->countRecords(
                    array(
                        'year' => $year,
                        'join' => array(
                            array(
                                'entity' => '\Application\Entity\Organization',
                                'alias' => 'Organization',
                                'expr' => \Doctrine\ORM\Query\Expr\Join::WITH,
                                'condition' => 'EikenTestResult.organizationNo = Organization.organizationNo'
                            )
                        ),
                        'condition' => 'Organization.id=' . $organizationId,
                        'kai' => $kai,
                    )
            );
            $this->temp ['Ceo_' . $kai . $organizationId . $year] = $ceo;
        }
        return (int) $ceo;
    }

    /**
     * Total of pupil in EikenTestResult of OrgSchoolYear for each kai
     * 
     * @param type $kai
     * @param type $orgSchoolYearId
     * @param type $year
     * @return type
     */
    function getCes($kai = 0, $orgSchoolYearId = 0, $year = 0) {
        $kai = (int) $kai;
        $orgSchoolYearId = (int) $orgSchoolYearId;
        $year = (int) $year;
        //
        $ces = isset($this->temp ['Ces_' . $kai . $orgSchoolYearId . $year]) ? $this->temp ['Ces_' . $kai . $orgSchoolYearId . $year] : 0;
        if (!isset($this->temp ['Ces_' . $kai . $orgSchoolYearId . $year])) {
            $ces = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->countRecords(
                    array(
                        'year' => $year,
                        'kai' => $kai,
                        'orgSchoolYearId' => $orgSchoolYearId,
                    )
            );
            $this->temp ['Ces_' . $kai . $orgSchoolYearId . $year] = $ces;
        }
        return (int) $ces;
    }

    /**
     * count total of pupil in EikenTestResult of class
     * 
     * @param type $kai
     * @param type $classId
     * @param type $year
     * @return type
     */
    function getCec($kai = 0, $classId = 0, $year = 0) {
        $kai = (int) $kai;
        $classId = (int) $classId;
        $year = (int) $year;
        //
        $cec = isset($this->temp ['Cec_' . $kai . $classId . $year]) ? $this->temp ['Cec_' . $kai . $classId . $year] : 0;
        if (!isset($this->temp ['Cec_' . $kai . $classId . $year])) {
            $cec = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->countRecords(
                    array(
                        'year' => $year,
                        'kai' => $kai,
                        'classId' => $classId,
                    )
            );
            $this->temp ['Cec_' . $kai . $classId . $year] = $cec;
        }
        return (int) $cec;
    }

    /**
     * Total Reading skill score of all pupil in EikenTestResult of organization
     * 
     * @param type $kai
     * @param type $organizationId
     * @param type $year
     */
    function getRSeo($kai = 0, $organizationId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        $rseo = isset($this->temp ['RSeo_' . $eikenId . $kai . $organizationId . $year]) ? $this->temp ['RSeo_' . $eikenId . $kai . $organizationId . $year] : 0;
        if (!isset($this->temp ['RSeo_' . $eikenId . $kai . $organizationId . $year])) {
            $rseo = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->sumCSEScore(
                    array(
                        'field' => 'cSEScoreReading',
                        'year' => $year,
                        'join' => array(
                            array(
                                'entity' => '\Application\Entity\Organization',
                                'alias' => 'Organization',
                                'expr' => \Doctrine\ORM\Query\Expr\Join::WITH,
                                'condition' => 'EikenTestResult.organizationNo = Organization.organizationNo'
                            )
                        ),
                        'condition' => 'Organization.id=' . $organizationId,
                        'kai' => $kai,
                    )
            );
            $this->temp ['RSeo_' . $eikenId . $kai . $organizationId . $year] = $rseo;
        }
        return (int) $rseo;
    }

    /**
     * Total listening skill score of all pupil in EikenTestResult of organization 
     * 
     * @param type $kai
     * @param type $organizationId
     * @param type $year
     */
    function getLSeo($kai = 0, $organizationId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        $lseo = isset($this->temp ['LSeo_' . $eikenId . $kai . $organizationId . $year]) ? $this->temp ['LSeo_' . $eikenId . $kai . $organizationId . $year] : 0;
        if (!isset($this->temp ['LSeo_' . $eikenId . $kai . $organizationId . $year])) {
            $lseo = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->sumCSEScore(
                    array(
                        'field' => 'cSEScoreListening',
                        'year' => $year,
                        'join' => array(
                            array(
                                'entity' => '\Application\Entity\Organization',
                                'alias' => 'Organization',
                                'expr' => \Doctrine\ORM\Query\Expr\Join::WITH,
                                'condition' => 'EikenTestResult.organizationNo = Organization.organizationNo'
                            )
                        ),
                        'condition' => 'Organization.id=' . $organizationId,
                        'kai' => $kai,
                    )
            );
            $this->temp ['LSeo_' . $eikenId . $kai . $organizationId . $year] = $lseo;
        }
        return (int) $lseo;
    }

    /**
     * Total Speaking skill score of all pupil in EikenTestResult of organization
     * 
     * @param type $kai
     * @param type $organizationId
     * @param type $year
     */
    function getSSeo($kai = 0, $organizationId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        $sseo = isset($this->temp ['SSeo_' . $eikenId . $kai . $organizationId . $year]) ? $this->temp ['SSeo_' . $eikenId . $kai . $organizationId . $year] : 0;
        if (!isset($this->temp ['SSeo_' . $eikenId . $kai . $organizationId . $year])) {
            $sseo = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->sumCSEScore(
                    array(
                        'field' => 'cSEScoreSpeaking',
                        'year' => $year,
                        'join' => array(
                            array(
                                'entity' => '\Application\Entity\Organization',
                                'alias' => 'Organization',
                                'expr' => \Doctrine\ORM\Query\Expr\Join::WITH,
                                'condition' => 'EikenTestResult.organizationNo = Organization.organizationNo'
                            )
                        ),
                        'condition' => 'Organization.id=' . $organizationId,
                        'kai' => $kai,
                    )
            );
            $this->temp ['SSeo_' . $eikenId . $kai . $organizationId . $year] = $sseo;
        }
        return (int) $sseo;
    }

    /**
     * Total writing skill score of all pupil in EikenTestResult of organization
     * 
     * @param type $kai
     * @param type $organizationId
     * @param type $year
     */
    function getWSeo($kai = 0, $organizationId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        $wseo = isset($this->temp ['WSeo_' . $eikenId . $kai . $organizationId . $year]) ? $this->temp ['WSeo_' . $eikenId . $kai . $organizationId . $year] : 0;
        if (!isset($this->temp ['WSeo_' . $eikenId . $kai . $organizationId . $year])) {
            $wseo = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->sumCSEScore(
                    array(
                        'field' => 'cSEScoreWriting',
                        'year' => $year,
                        'join' => array(
                            array(
                                'entity' => '\Application\Entity\Organization',
                                'alias' => 'Organization',
                                'expr' => \Doctrine\ORM\Query\Expr\Join::WITH,
                                'condition' => 'EikenTestResult.organizationNo = Organization.organizationNo'
                            )
                        ),
                        'condition' => 'Organization.id=' . $organizationId,
                        'kai' => $kai,
                    )
            );
            $this->temp ['WSeo_' . $eikenId . $kai . $organizationId . $year] = $wseo;
        }
        return (int) $wseo;
    }

    /**
     * Total of 4 skill score of all pupil in EikenTestResult belong to organization
     * 
     * @param type $kai
     * @param type $organizationId
     * @param type $year
     */
    function getESeo($kai = 0, $organizationId = 0, $year = 0) {
        return $this->getRSeo($kai, $organizationId, $year) + $this->getLSeo($kai, $organizationId, $year) + $this->getSSeo($kai, $organizationId, $year) + $this->getWSeo($kai, $organizationId, $year);
    }

    /**
     * lookup total CSE score lowest of Eiken Test Results
     * 
     * @param type $kai
     * @param type $organizationId
     * @param type $year
     */
    function getLTSeo($kai = 0, $organizationId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        $ltseo = isset($this->temp ['LTSeo_' . $eikenId . $kai . $organizationId . $year]) ? $this->temp ['LTSeo_' . $eikenId . $kai . $organizationId . $year] : 0;
        if (!isset($this->temp ['LTSeo_' . $eikenId . $kai . $organizationId . $year])) {
            $eikenTestResultRepository = $this->em->getRepository('Application\Entity\EikenTestResult');
            $ltseo = $eikenTestResultRepository->getEikenEdgeScore($kai, $year, $eikenTestResultRepository::SCORE_TYPE_NAME_ORG, $organizationId, $eikenTestResultRepository::SCORE_EDGE_LEAST);
            $this->temp ['LTSeo_' . $eikenId . $kai . $organizationId . $year] = $ltseo;
        }
        return (int) $ltseo;
    }

    /**
     * lookup total CSE score Highest of Eiken Test Results
     * 
     * @param type $kai
     * @param type $organizationId
     * @param type $year
     */
    function getHTSeo($kai = 0, $organizationId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        $HTSeo = isset($this->temp ['HTSeo_' . $eikenId . $kai . $organizationId . $year]) ? $this->temp ['HTSeo_' . $eikenId . $kai . $organizationId . $year] : 0;
        if (!isset($this->temp ['HTSeo_' . $eikenId . $kai . $organizationId . $year])) {
            $eikenTestResultRepository = $this->em->getRepository('Application\Entity\EikenTestResult');
            $HTSeo = $eikenTestResultRepository->getEikenEdgeScore($kai, $year, $eikenTestResultRepository::SCORE_TYPE_NAME_ORG, $organizationId, $eikenTestResultRepository::SCORE_EDGE_GREATEST);
            $this->temp ['HTSeo_' . $eikenId . $kai . $organizationId . $year] = $HTSeo;
        }
        return (int) $HTSeo;
    }

    /**
     * Percentage of pupils participating in the implementation of Org
     * 
     * @param type $kai
     * @param type $organizationId
     * @param type $year
     * @return type
     */
    function getAReo($kai = 0, $organizationId = 0, $year = 0) {
        $to = $this->getTo($organizationId, $year);
        $per = 0;
        if ($to) {
            $ceo = $this->getCeo($kai, $organizationId, $year);
            $per = round($ceo / $to * 100, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * Average total scores of the students take the test
     * 
     * @param type $kai
     * @param type $organizationId
     * @param type $year
     * @return type
     */
    function getASeo($kai = 0, $organizationId = 0, $year = 0) {
        $ceo = $this->getCeo($kai, $organizationId, $year);
        $per = 0;
        if ($ceo) {
            $eseo = $this->getESeo($kai, $organizationId, $year);
            $per = round($eseo / $ceo, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * AverageReadingScore theo Org
     *
     * @param type $kai
     * @param type $organizationId
     * @param type $year
     */
    function getARSeo($kai = 0, $organizationId = 0, $year = 0) {
        $ceo = $this->getCeo($kai, $organizationId, $year);
        $per = 0;
        if ($ceo) {
            $rseo = $this->getRSeo($kai, $organizationId, $year);
            $per = round($rseo / $ceo, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * AverageListeningScore theo Org
     *
     * @param type $kai
     * @param type $organizationId
     * @param type $year
     */
    function getALSeo($kai = 0, $organizationId = 0, $year = 0) {
        $ceo = $this->getCeo($kai, $organizationId, $year);
        $per = 0;
        if ($ceo) {
            $lseo = $this->getLSeo($kai, $organizationId, $year);
            $per = round($lseo / $ceo, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * AverageSpeakingScore of Org
     * 
     * @param type $kai
     * @param type $organizationId
     * @param type $year
     * @return type
     */
    function getASSeo($kai = 0, $organizationId = 0, $year = 0) {
        $ceo = $this->getCeo($kai, $organizationId, $year);
        $per = 0;
        if ($ceo) {
            $sseo = $this->getSSeo($kai, $organizationId, $year);
            $per = round($sseo / $ceo, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * AveragewritingScore of Org
     * 
     * @param type $kai
     * @param type $organizationId
     * @param type $year
     * @return type
     */
    function getAWSeo($kai = 0, $organizationId = 0, $year = 0) {
        $ceo = $this->getCeo($kai, $organizationId, $year);
        $per = 0;
        if ($ceo) {
            $wseo = $this->getWSeo($kai, $organizationId, $year);
            $per = round($wseo / $ceo, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * Total Reading skill score of all pupil in EikenTestResult of OrgSchoolYear
     * 
     * @param type $kai
     * @param type $orgSchoolYearId
     * @param type $year
     */
    function getRSEs($kai = 0, $orgSchoolYearId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $orgSchoolYearId = (int) $orgSchoolYearId;
        $year = (int) $year;
        $rses = isset($this->temp ['RSes_' . $eikenId . $kai . $orgSchoolYearId . $year]) ? $this->temp ['RSes_' . $eikenId . $kai . $orgSchoolYearId . $year] : 0;
        if (!isset($this->temp ['RSes_' . $eikenId . $kai . $orgSchoolYearId . $year])) {
            $rses = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->sumCSEScore(
                    array(
                        'field' => 'cSEScoreReading',
                        'year' => $year,
                        'orgSchoolYearId' => $orgSchoolYearId,
                        'kai' => $kai,
                    )
            );
            $this->temp ['RSes_' . $eikenId . $kai . $orgSchoolYearId . $year] = $rses;
        }
        return (int) $rses;
    }

    /**
     * Total Listening skill score of all pupil in EikenTestResult of OrgSchoolYear
     * 
     * @param type $kai
     * @param type $orgSchoolYearId
     * @param type $year
     */
    function getLSEs($kai = 0, $orgSchoolYearId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $orgSchoolYearId = (int) $orgSchoolYearId;
        $year = (int) $year;
        $lses = isset($this->temp ['LSes_' . $eikenId . $kai . $orgSchoolYearId . $year]) ? $this->temp ['LSes_' . $eikenId . $kai . $orgSchoolYearId . $year] : 0;
        if (!isset($this->temp ['LSes_' . $eikenId . $kai . $orgSchoolYearId . $year])) {
            $lses = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->sumCSEScore(
                    array(
                        'field' => 'cSEScoreListening',
                        'year' => $year,
                        'orgSchoolYearId' => $orgSchoolYearId,
                        'kai' => $kai,
                    )
            );
            $this->temp ['LSes_' . $eikenId . $kai . $orgSchoolYearId . $year] = $lses;
        }
        return (int) $lses;
    }

    /**
     * Total Speaking skill score of all pupil in EikenTestResult of OrgSchoolYear
     * 
     * @param type $kai
     * @param type $orgSchoolYearId
     * @param type $year
     */
    function getSSEs($kai = 0, $orgSchoolYearId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $orgSchoolYearId = (int) $orgSchoolYearId;
        $year = (int) $year;
        $sses = isset($this->temp ['SSes_' . $eikenId . $kai . $orgSchoolYearId . $year]) ? $this->temp ['SSes_' . $eikenId . $kai . $orgSchoolYearId . $year] : 0;
        if (!isset($this->temp ['SSes_' . $eikenId . $kai . $orgSchoolYearId . $year])) {
            $sses = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->sumCSEScore(
                    array(
                        'field' => 'cSEScoreSpeaking',
                        'year' => $year,
                        'orgSchoolYearId' => $orgSchoolYearId,
                        'kai' => $kai,
                    )
            );
            $this->temp ['SSes_' . $eikenId . $kai . $orgSchoolYearId . $year] = $sses;
        }
        return (int) $sses;
    }

    /**
     * Total writing skill score of all pupil in EikenTestResult of OrgSchoolYear
     * 
     * @param type $kai
     * @param type $orgSchoolYearId
     * @param type $year
     */
    function getWSEs($kai = 0, $orgSchoolYearId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $orgSchoolYearId = (int) $orgSchoolYearId;
        $year = (int) $year;
        $wses = isset($this->temp ['WSes_' . $eikenId . $kai . $orgSchoolYearId . $year]) ? $this->temp ['WSes_' . $eikenId . $kai . $orgSchoolYearId . $year] : 0;
        if (!isset($this->temp ['WSes_' . $eikenId . $kai . $orgSchoolYearId . $year])) {
            $wses = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->sumCSEScore(
                    array(
                        'field' => 'cSEScoreWriting',
                        'year' => $year,
                        'orgSchoolYearId' => $orgSchoolYearId,
                        'kai' => $kai,
                    )
            );
            $this->temp ['WSes_' . $eikenId . $kai . $orgSchoolYearId . $year] = $wses;
        }
        return (int) $wses;
    }

    /**
     * Total of 4 skill score of all pupil in EikenTestResult belong to OrgSChoolYear
     * 
     * @param type $kai
     * @param type $orgSchoolYearId
     * @param type $year
     */
    function getESEs($kai = 0, $orgSchoolYearId = 0, $year = 0) {
        return $this->getRSEs($kai, $orgSchoolYearId, $year) + $this->getSSEs($kai, $orgSchoolYearId, $year) + $this->getLSEs($kai, $orgSchoolYearId, $year) + $this->getWSEs($kai, $orgSchoolYearId, $year);
    }

    /**
     * lookup total CSE score lowest of Eiken Test Results belong to OrgchoolYear
     * 
     * @param type $kai
     * @param type $orgSchoolYearId
     * @param type $year
     */
    function getLTSes($kai = 0, $orgSchoolYearId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $orgSchoolYearId = (int) $orgSchoolYearId;
        $year = (int) $year;
        $LTSes = isset($this->temp ['LTSes_' . $eikenId . $kai . $orgSchoolYearId . $year]) ? $this->temp ['LTSes_' . $eikenId . $kai . $orgSchoolYearId . $year] : 0;
        if (!isset($this->temp ['LTSes_' . $eikenId . $kai . $orgSchoolYearId . $year])) {
            $eikenTestResultRepository = $this->em->getRepository('Application\Entity\EikenTestResult');
            $LTSes = $eikenTestResultRepository->getEikenEdgeScore($kai, $year, $eikenTestResultRepository::SCORE_TYPE_NAME_ORGSCHOOLYEAR, $orgSchoolYearId, $eikenTestResultRepository::SCORE_EDGE_LEAST);
            $this->temp ['LTSes_' . $eikenId . $kai . $orgSchoolYearId . $year] = $LTSes;
        }
        return (int) $LTSes;
    }

    /**
     * lookup total CSE score Highest of Eiken Test Results belong to OrgSchoolYear
     * 
     * @param type $kai
     * @param type $orgSchoolYearId
     * @param type $year
     */
    function getHTSes($kai = 0, $orgSchoolYearId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $orgSchoolYearId = (int) $orgSchoolYearId;
        $year = (int) $year;
        $HTSes = isset($this->temp ['HTSes_' . $eikenId . $kai . $orgSchoolYearId . $year]) ? $this->temp ['HTSes_' . $eikenId . $kai . $orgSchoolYearId . $year] : 0;
        if (!isset($this->temp ['HTSes_' . $eikenId . $kai . $orgSchoolYearId . $year])) {
            $eikenTestResultRepository = $this->em->getRepository('Application\Entity\EikenTestResult');
            $HTSes = $eikenTestResultRepository->getEikenEdgeScore($kai, $year, $eikenTestResultRepository::SCORE_TYPE_NAME_ORGSCHOOLYEAR, $orgSchoolYearId, $eikenTestResultRepository::SCORE_EDGE_GREATEST);
            $this->temp ['HTSes_' . $eikenId . $kai . $orgSchoolYearId . $year] = $HTSes;
        }
        return (int) $HTSes;
    }

    /**
     * Percentage of pupils participating in the implementation of OrgScholYear
     * 
     * @param type $kai
     * @param type $orgSchoolYearId
     * @param type $year
     * @return type
     */
    function getARes($kai = 0, $orgSchoolYearId = 0, $year = 0) {
        $ts = $this->getTs($orgSchoolYearId, $year);
        $per = 0;
        if ($ts) {
            $ces = $this->getCes($kai, $orgSchoolYearId, $year);
            $per = round($ces / $ts * 100, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * 
     * @param type $kai
     * @param type $orgSchoolYearId
     * @param type $year
     */
    function getASes($kai = 0, $orgSchoolYearId = 0, $year = 0) {
        $ces = $this->getCes($kai, $orgSchoolYearId, $year);
        $per = 0;
        if ($ces) {
            $eses = $this->getESEs($kai, $orgSchoolYearId, $year);
            $per = round($eses / $ces, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * AverageReadingScore of school year
     * 
     * @param type $kai
     * @param type $orgSchoolYearId
     * @param type $year
     */
    function getARSes($kai = 0, $orgSchoolYearId = 0, $year = 0) {
        $ces = $this->getCes($kai, $orgSchoolYearId, $year);
        $per = 0;
        if ($ces) {
            $ases = $this->getRSEs($kai, $orgSchoolYearId, $year);
            $per = round($ases / $ces, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * AverageListeningScore of school year
     * 
     * @param type $kai
     * @param type $orgSchoolYearId
     * @param type $year
     */
    function getALSes($kai = 0, $orgSchoolYearId = 0, $year = 0) {
        $ces = $this->getCes($kai, $orgSchoolYearId, $year);
        $per = 0;
        if ($ces) {
            $ases = $this->getLSEs($kai, $orgSchoolYearId, $year);
            $per = round($ases / $ces, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * AverageSpeakingScore of school year
     * 
     * @param type $kai
     * @param type $orgSchoolYearId
     * @param type $year
     */
    function getASSes($kai = 0, $orgSchoolYearId = 0, $year = 0) {
        $ces = $this->getCes($kai, $orgSchoolYearId, $year);
        $per = 0;
        if ($ces) {
            $ases = $this->getSSEs($kai, $orgSchoolYearId, $year);
            $per = round($ases / $ces, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * AveragewritingScore of school year
     * 
     * @param type $kai
     * @param type $orgSchoolYearId
     * @param type $year
     */
    function getAWSes($kai = 0, $orgSchoolYearId = 0, $year = 0) {
        $ces = $this->getCes($kai, $orgSchoolYearId, $year);
        $per = 0;
        if ($ces) {
            $ases = $this->getWSEs($kai, $orgSchoolYearId, $year);
            $per = round($ases / $ces, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * Total Reading skill score of all pupil in EikenTestResult belong to Class
     * 
     * @param type $kai
     * @param type $classId
     * @param type $year
     * @return type
     */
    function getRSec($kai = 0, $classId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $classId = (int) $classId;
        $year = (int) $year;
        $rsec = isset($this->temp ['RSec_' . $eikenId . $kai . $classId . $year]) ? $this->temp ['RSec_' . $eikenId . $kai . $classId . $year] : 0;
        if (!isset($this->temp ['RSec_' . $eikenId . $kai . $classId . $year])) {
            $rsec = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->sumCSEScore(
                    array(
                        'field' => 'cSEScoreReading',
                        'year' => $year,
                        'classId' => $classId,
                        'kai' => $kai,
                    )
            );
            $this->temp ['RSec_' . $eikenId . $kai . $classId . $year] = $rsec;
        }
        return (int) $rsec;
    }

    /**
     * Total listening skill score of all pupil in EikenTestResult belong to corresponding class
     * 
     * @param type $kai
     * @param type $classId
     * @param type $year
     * @return type
     */
    function getLSec($kai = 0, $classId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $classId = (int) $classId;
        $year = (int) $year;
        $lsec = isset($this->temp ['LSec_' . $eikenId . $kai . $classId . $year]) ? $this->temp ['LSec_' . $eikenId . $kai . $classId . $year] : 0;
        if (!isset($this->temp ['LSec_' . $eikenId . $kai . $classId . $year])) {
            $lsec = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->sumCSEScore(
                    array(
                        'field' => 'cSEScoreListening',
                        'year' => $year,
                        'classId' => $classId,
                        'kai' => $kai,
                    )
            );
            $this->temp ['LSec_' . $eikenId . $kai . $classId . $year] = $lsec;
        }
        return (int) $lsec;
    }

    /**
     * Total speaking skill score of all pupil in EikenTestResult belong to corresponding class
     * 
     * @param type $kai
     * @param type $classId
     * @param type $year
     * @return type
     */
    function getSSec($kai = 0, $classId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $classId = (int) $classId;
        $year = (int) $year;
        $ssec = isset($this->temp ['SSec_' . $eikenId . $kai . $classId . $year]) ? $this->temp ['SSec_' . $eikenId . $kai . $classId . $year] : 0;
        if (!isset($this->temp ['SSec_' . $eikenId . $kai . $classId . $year])) {
            $ssec = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->sumCSEScore(
                    array(
                        'field' => 'cSEScoreSpeaking',
                        'year' => $year,
                        'classId' => $classId,
                        'kai' => $kai,
                    )
            );
            $this->temp ['SSec_' . $eikenId . $kai . $classId . $year] = $ssec;
        }
        return (int) $ssec;
    }

    /**
     * Total writing skill score of all pupil in EikenTestResult belong to corresponding class
     * 
     * @param type $kai
     * @param type $classId
     * @param type $year
     * @return type
     */
    function getWSec($kai = 0, $classId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $classId = (int) $classId;
        $year = (int) $year;
        $wsec = isset($this->temp ['WSec_' . $eikenId . $kai . $classId . $year]) ? $this->temp ['WSec_' . $eikenId . $kai . $classId . $year] : 0;
        if (!isset($this->temp ['WSec_' . $eikenId . $kai . $classId . $year])) {
            $wsec = $this->em->getRepository('Application\Entity\EikenTestResult')
                    ->sumCSEScore(
                    array(
                        'field' => 'cSEScoreWriting',
                        'year' => $year,
                        'classId' => $classId,
                        'kai' => $kai,
                    )
            );
            $this->temp ['WSec_' . $eikenId . $kai . $classId . $year] = $wsec;
        }
        return (int) $wsec;
    }

    /**
     * Total of 4 skill score of all pupil in EikenTestResult belong to Class
     * 
     * @param type $kai
     * @param type $classId
     * @param type $year
     * @return type
     */
    function getESec($kai = 0, $classId = 0, $year = 0) {
        return $this->getRSec($kai, $classId, $year) + $this->getLSec($kai, $classId, $year) + $this->getSSec($kai, $classId, $year) + $this->getWSec($kai, $classId, $year);
    }

    /**
     * lookup total CSE score lowest of Eiken Test Results of class
     * 
     * @param type $kai
     * @param type $classId
     * @param type $year
     */
    function getLTSec($kai = 0, $classId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $classId = (int) $classId;
        $year = (int) $year;
        $LTSec = isset($this->temp ['LTSec_' . $eikenId . $kai . $classId . $year]) ? $this->temp ['LTSec_' . $eikenId . $kai . $classId . $year] : 0;
        if (!isset($this->temp ['LTSec_' . $eikenId . $kai . $classId . $year])) {
            $eikenTestResultRepository = $this->em->getRepository('Application\Entity\EikenTestResult');
            $LTSec = $eikenTestResultRepository->getEikenEdgeScore($kai, $year, $eikenTestResultRepository::SCORE_TYPE_NAME_CLASS, $classId, $eikenTestResultRepository::SCORE_EDGE_LEAST);
            $this->temp ['LTSec_' . $eikenId . $kai . $classId . $year] = $LTSec;
        }
        return (int) $LTSec;
    }

    /**
     * lookup total CSE score highest of Eiken Test Results of class
     * 
     * @param type $kai
     * @param type $classId
     * @param type $year
     */
    function getHTSec($kai = 0, $classId = 0, $year = 0) {
        $eikenId = 0;
        $kai = (int) $kai;
        $classId = (int) $classId;
        $year = (int) $year;
        $HTSec = isset($this->temp ['HTSec_' . $eikenId . $kai . $classId . $year]) ? $this->temp ['HTSec_' . $eikenId . $kai . $classId . $year] : 0;
        if (!isset($this->temp ['HTSec_' . $eikenId . $kai . $classId . $year])) {
            $eikenTestResultRepository = $this->em->getRepository('Application\Entity\EikenTestResult');
            $HTSec = $eikenTestResultRepository->getEikenEdgeScore($kai, $year, $eikenTestResultRepository::SCORE_TYPE_NAME_CLASS, $classId, $eikenTestResultRepository::SCORE_EDGE_GREATEST);
            $this->temp ['HTSec_' . $eikenId . $kai . $classId . $year] = $HTSec;
        }
        return (int) $HTSec;
    }

    /**
     * Percentage of pupils participating in the implementation of class
     * 
     * @param type $kai
     * @param type $classId
     * @param type $year
     * @return type
     */
    function getARec($kai = 0, $classId = 0, $year = 0) {
        $tc = $this->getTc($classId);
        $per = 0;
        if ($tc) {
            $cec = $this->getCec($kai, $classId, $year);
            $per = round($cec / $tc * 100, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * 
     * @param type $eikenId
     * @param type $kai
     * @param type $classId
     * @param type $year
     * @return type
     */
    function getASec($kai = 0, $classId = 0, $year = 0) {
        $cec = $this->getCec($kai, $classId, $year);
        $per = 0;
        if ($cec) {
            $esec = $this->getESEc($kai, $classId, $year);
            $per = round($esec / $cec, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * AverageReadingScore of class
     * 
     * @param type $kai
     * @param type $classId
     * @param type $year
     */
    function getARSec($kai = 0, $classId = 0, $year = 0) {
        $cec = $this->getCec($kai, $classId, $year);
        $per = 0;
        if ($cec) {
            $asec = $this->getRSEc($kai, $classId, $year);
            $per = round($asec / $cec, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * AverageListeningScore of class
     * 
     * @param type $kai
     * @param type $classId
     * @param type $year
     */
    function getALSec($kai = 0, $classId = 0, $year = 0) {
        $cec = $this->getCec($kai, $classId, $year);
        $per = 0;
        if ($cec) {
            $asec = $this->getLSEc($kai, $classId, $year);
            $per = round($asec / $cec, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * AverageSpeakingScore of class
     * 
     * @param type $kai
     * @param type $classId
     * @param type $year
     */
    function getASSec($kai = 0, $classId = 0, $year = 0) {
        $cec = $this->getCec($kai, $classId, $year);
        $per = 0;
        if ($cec) {
            $asec = $this->getSSEc($kai, $classId, $year);
            $per = round($asec / $cec, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * AveragewritingScore of class
     * 
     * @param type $kai
     * @param type $classId
     * @param type $year
     */
    function getAWSec($kai = 0, $classId = 0, $year = 0) {
        $cec = $this->getCec($kai, $classId, $year);
        $per = 0;
        if ($cec) {
            $asec = $this->getWSEc($kai, $classId, $year);
            $per = round($asec / $cec, self::DECIMALLIMIT);
        }
        return $per;
    }

    //*************************************************** IBA Analyst *************************************************** */

    /**
     * Total of pupil in IBATestResults of organization for each exam
     * @param type $organizationId
     * @param type $year
     * @param type $examDate
     * @return type
     */
    function getCio($organizationId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        //
        $cio = isset($this->temp ['Cio_' . $organizationId . $year . $examDate . $examType . $jisshiId]) ? $this->temp ['Cio_' . $organizationId . $year . $examDate . $examType . $jisshiId] : 0;
        if (!isset($this->temp ['Cio_' . $organizationId . $year . $examDate . $examType . $jisshiId])) {
            $cio = $this->em->getRepository('Application\Entity\IBATestResult')
                    ->countRecords(
                    array(
                        'year' => $year,
                        'join' => array(
                            array(
                                'entity' => '\Application\Entity\Organization',
                                'alias' => 'Organization',
                                'expr' => \Doctrine\ORM\Query\Expr\Join::WITH,
                                'condition' => 'IBATestResult.organizationNo = Organization.organizationNo'
                            )
                        ),
                        'condition' => 'Organization.id=' . $organizationId,
                        'examDate' => $examDate,
                        'examType' => $examType,
                        'jisshiId' => $jisshiId,
                    )
            );
            $this->temp ['Cio_' . $organizationId . $year . $examDate . $examType . $jisshiId] = $cio;
        }
        return (int) $cio;
    }

    /**
     * Total of pupil in IBATestResults of OrgSchoolYear for each exam
     * 
     * @param type $orgSchoolYearId
     * @param type $year
     * @param type $examDate
     * @return type
     */
    function getCis($orgSchoolYearId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $orgSchoolYearId = (int) $orgSchoolYearId;
        $year = (int) $year;
        //
        $cis = isset($this->temp ['Cis_' . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId]) ? $this->temp ['Cis_' . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId] : 0;
        if (!isset($this->temp ['Cis_' . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId])) {
            $cis = $this->em->getRepository('Application\Entity\IBATestResult')
                    ->countRecords(
                    array(
                        'year' => $year,
                        'orgSchoolYearId' => $orgSchoolYearId,
                        'examDate' => $examDate,
                        'examType' => $examType,
                        'jisshiId' => $jisshiId,
                    )
            );
            $this->temp ['Cis_' . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId] = $cis;
        }
        return (int) $cis;
    }

    /**
     * Total of pupil in IBATestResults of Class for each exam
     * 
     * @param type $classId
     * @param type $year
     * @param type $examDate
     * @return type
     */
    function getCic($classId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $classId = (int) $classId;
        $year = (int) $year;
        //
        $cic = isset($this->temp ['Cic_' . $classId . $year . $examDate . $examType . $jisshiId]) ? $this->temp ['Cic_' . $classId . $year . $examDate . $examType . $jisshiId] : 0;
        if (!isset($this->temp ['Cic_' . $classId . $year . $examDate . $examType . $jisshiId])) {
            $cic = $this->em->getRepository('Application\Entity\IBATestResult')
                    ->countRecords(
                    array(
                        'year' => $year,
                        'classId' => $classId,
                        'examDate' => $examDate,
                        'examType' => $examType,
                        'jisshiId' => $jisshiId,
                    )
            );
            $this->temp ['Cic_' . $classId . $year . $examDate . $examType . $jisshiId] = $cic;
        }
        return (int) $cic;
    }

    /**
     * Total reading skill score of all pupil in organization of IBA Test Results
     * 
     * @param type $organizationId
     * @param type $year
     * @param type $examDate
     * @return type
     */
    function getRSio($organizationId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $eikenId = 0;
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        $rsio = isset($this->temp ['RSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId]) ? $this->temp ['RSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId] : 0;
        if (!isset($this->temp ['RSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId])) {
            $rsio = $this->em->getRepository('Application\Entity\IBATestResult')
                    ->sumIBAScore(
                    array(
                        'field' => 'read',
                        'year' => $year,
                        'join' => array(
                            array(
                                'entity' => '\Application\Entity\Organization',
                                'alias' => 'Organization',
                                'expr' => \Doctrine\ORM\Query\Expr\Join::WITH,
                                'condition' => 'IBATestResult.organizationNo = Organization.organizationNo'
                            )
                        ),
                        'condition' => 'Organization.id=' . $organizationId,
                        'examDate' => $examDate,
                        'examType' => $examType,
                        'jisshiId' => $jisshiId,
                    )
            );
            $this->temp ['RSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId] = $rsio;
        }
        return (int) $rsio;
    }

    /**
     * Total listening skill score of all pupil in organization of IBA Test Results
     * 
     * @param type $organizationId
     * @param type $year
     * @param type $examDate
     * @return type
     */
    function getLSio($organizationId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $eikenId = 0;
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        $lsio = isset($this->temp ['LSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId]) ? $this->temp ['LSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId] : 0;
        if (!isset($this->temp ['LSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId])) {
            $lsio = $this->em->getRepository('Application\Entity\IBATestResult')
                    ->sumIBAScore(
                    array(
                        'field' => 'listen',
                        'year' => $year,
                        'join' => array(
                            array(
                                'entity' => '\Application\Entity\Organization',
                                'alias' => 'Organization',
                                'expr' => \Doctrine\ORM\Query\Expr\Join::WITH,
                                'condition' => 'IBATestResult.organizationNo = Organization.organizationNo'
                            )
                        ),
                        'condition' => 'Organization.id=' . $organizationId,
                        'examDate' => $examDate,
                        'examType' => $examType,
                        'jisshiId' => $jisshiId,
                    )
            );
            $this->temp ['LSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId] = $lsio;
        }
        return (int) $lsio;
    }

    /**
     * Total of 2 skill score of all pupil in IBA Test Results belong to organization 
     * 
     * @param type $organizationId
     * @param type $year
     * @param type $examDate
     * @return type
     */
    function getESio($organizationId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        return $this->getRSio($organizationId, $year, $examDate, $examType, $jisshiId) + $this->getLSio($organizationId, $year, $examDate, $examType, $jisshiId);
    }

    /**
     * AttendRate of Org in IBA
     * 
     * @param type $organizationId
     * @param type $year
     * @param type $examDate
     * @return type
     */
    function getARio($organizationId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $to = $this->getTo($organizationId, $year);
        $per = 0;
        if ($to) {
            $cio = $this->getCio($organizationId, $year, $examDate, $examType, $jisshiId);
            $per = round($cio / $to * 100, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * 
     * @param type $organizationId
     * @param type $year
     * @param type $examDate
     * @return type
     */
    function getASio($organizationId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $cio = $this->getCio($organizationId, $year, $examDate, $examType, $jisshiId);
        $per = 0;
        if ($cio) {
            $asio = $this->getESio($organizationId, $year, $examDate, $examType, $jisshiId);
            $per = round($asio / $cio, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * lookup total CSE score lowest of IBA Test Results
     *
     * @param type $organizationId
     * @param integer $year
     * @param string $examDate
     * @return type
     */
    function getLTSio($organizationId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $eikenId = 0;
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        $ltsio = isset($this->temp ['LTSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId]) ? $this->temp ['LTSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId] : 0;
        if (!isset($this->temp ['LTSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId])) {
            $iBATestResultRepository = $this->em->getRepository('Application\Entity\IBATestResult');
            $ltsio = $iBATestResultRepository->getIBAEdgeScore($year, $iBATestResultRepository::SCORE_TYPE_NAME_ORG, $organizationId, $iBATestResultRepository::SCORE_EDGE_LEAST, $examDate, $examType, $jisshiId);
            $this->temp ['LTSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId] = $ltsio;
        }
        return (int) $ltsio;
    }

    /**
     * 
     * @param type $organizationId
     * @param type $year
     * @param string $examDate
     * @return type
     */
    function getHTSio($organizationId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $eikenId = 0;
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        $htsio = isset($this->temp ['HTSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId]) ? $this->temp ['HTSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId] : 0;
        if (!isset($this->temp ['HTSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId])) {
            $iBATestResultRepository = $this->em->getRepository('Application\Entity\IBATestResult');
            $htsio = $iBATestResultRepository->getIBAEdgeScore($year, $iBATestResultRepository::SCORE_TYPE_NAME_ORG, $organizationId, $iBATestResultRepository::SCORE_EDGE_GREATEST, $examDate, $examType, $jisshiId);
            $this->temp ['HTSio_' . $eikenId . $organizationId . $year . $examDate . $examType . $jisshiId] = $htsio;
        }
        return (int) $htsio;
    }

    /**
     * AverageReadingScore of Org in IBA
     * 
     * @param type $organizationId
     * @param type $year
     * @param string $examDate
     * @return type
     */
    function getARSio($organizationId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $cio = $this->getCio($organizationId, $year, $examDate, $examType, $jisshiId);
        $per = 0;
        if ($cio) {
            $rsio = $this->getRSio($organizationId, $year, $examDate, $examType, $jisshiId);
            $per = round($rsio / $cio, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * AverageListeningScore of Org in IBA
     *
     * @param type $organizationId
     * @param type $year
     * @param string $examDate
     * @return type
     */
    function getALSio($organizationId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $cio = $this->getCio($organizationId, $year, $examDate, $examType, $jisshiId);
        $per = 0;
        if ($cio) {
            $lsio = $this->getLSio($organizationId, $year, $examDate, $examType, $jisshiId);
            $per = round($lsio / $cio, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * Total reading skill score of all pupil in school year of IBA Test Results
     * 
     * @param type $orgSchoolYearId
     * @param type $year
     * @param string $examDate
     * @return type
     */
    function getRSis($orgSchoolYearId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $eikenId = 0;
        $orgSchoolYearId = (int) $orgSchoolYearId;
        $year = (int) $year;
        $rsis = isset($this->temp ['RSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId]) ? $this->temp ['RSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId] : 0;
        if (!isset($this->temp ['RSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId])) {
            $rsis = $this->em->getRepository('Application\Entity\IBATestResult')
                    ->sumIBAScore(
                    array(
                        'field' => 'read',
                        'year' => $year,
                        'orgSchoolYearId' => $orgSchoolYearId,
                        'examDate' => $examDate,
                        'examType' => $examType,
                        'jisshiId' => $jisshiId,
                    )
            );
            $this->temp ['RSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId] = $rsis;
        }
        return (int) $rsis;
    }

    /**
     * Total listening skill score of all pupil in school year of IBA Test Results
     * 
     * @param type $orgSchoolYearId
     * @param type $year
     * @param string $examDate
     * @return type
     */
    function getLSis($orgSchoolYearId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $eikenId = 0;
        $orgSchoolYearId = (int) $orgSchoolYearId;
        $year = (int) $year;
        $lsis = isset($this->temp ['LSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId]) ? $this->temp ['LSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId] : 0;
        if (!isset($this->temp ['LSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId])) {
            $lsis = $this->em->getRepository('Application\Entity\IBATestResult')
                    ->sumIBAScore(
                    array(
                        'field' => 'listen',
                        'year' => $year,
                        'orgSchoolYearId' => $orgSchoolYearId,
                        'examDate' => $examDate,
                        'examType' => $examType,
                        'jisshiId' => $jisshiId,
                    )
            );
            $this->temp ['LSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId] = $lsis;
        }
        return (int) $lsis;
    }

    /**
     * Total of 2 skill score of all pupil in IBATestResult belong to OrgSChoolYear
     * 
     * @param type $orgSchoolYearId
     * @param type $year
     * @param string $examDate
     * @return type
     */
    function getESis($orgSchoolYearId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        return $this->getRSis($orgSchoolYearId, $year, $examDate, $examType, $jisshiId) + $this->getLSis($orgSchoolYearId, $year, $examDate, $examType, $jisshiId);
    }

    /**
     * 
     * @param type $orgSchoolYearId
     * @param type $year
     * @param string $examDate
     * @return type
     */
    function getARis($orgSchoolYearId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $ts = $this->getTs($orgSchoolYearId, $year);
        $per = 0;
        if ($ts) {
            $cis = $this->getCis($orgSchoolYearId, $year, $examDate, $examType, $jisshiId);
            $per = round($cis / $ts * 100, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * 
     * @param type $orgSchoolYearId
     * @param type $year
     * @param string $examDate
     * @return type
     */
    function getASis($orgSchoolYearId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $cis = $this->getCis($orgSchoolYearId, $year, $examDate, $examType, $jisshiId);
        $per = 0;
        if ($cis) {
            $esis = $this->getESis($orgSchoolYearId, $year, $examDate, $examType, $jisshiId);
            $per = round($esis / $cis, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * lookup total CSE score lowest of IBA Test Results belong to school year
     * 
     * @param type $orgSchoolYearId
     * @param type $year
     * @param string $examDate
     * @return type
     */
    function getLTSis($orgSchoolYearId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $eikenId = 0;
        $orgSchoolYearId = (int) $orgSchoolYearId;
        $year = (int) $year;
        $ltsis = isset($this->temp ['LTSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId]) ? $this->temp ['LTSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId] : 0;
        if (!isset($this->temp ['LTSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId])) {
            $iBATestResultRepository = $this->em->getRepository('Application\Entity\IBATestResult');
            $ltsis = $iBATestResultRepository->getIBAEdgeScore($year, $iBATestResultRepository::SCORE_TYPE_NAME_ORGSCHOOLYEAR, $orgSchoolYearId, $iBATestResultRepository::SCORE_EDGE_LEAST, $examDate, $examType, $jisshiId);
            $this->temp ['LTSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId] = $ltsis;
        }
        return (int) $ltsis;
    }

    /**
     * lookup total CSE score highest of IBA Test Results belong to school year
     * 
     * @param type $orgSchoolYearId
     * @param type $year
     * @param string $examDate
     * @return type
     */
    function getHTSis($orgSchoolYearId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $eikenId = 0;
        $orgSchoolYearId = (int) $orgSchoolYearId;
        $year = (int) $year;
        $htsis = isset($this->temp ['HTSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId]) ? $this->temp ['HTSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId] : 0;
        if (!isset($this->temp ['HTSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId])) {
            $iBATestResultRepository = $this->em->getRepository('Application\Entity\IBATestResult');
            $htsis = $iBATestResultRepository->getIBAEdgeScore($year, $iBATestResultRepository::SCORE_TYPE_NAME_ORGSCHOOLYEAR, $orgSchoolYearId, $iBATestResultRepository::SCORE_EDGE_GREATEST, $examDate, $examType, $jisshiId);
            $this->temp ['HTSis_' . $eikenId . $orgSchoolYearId . $year . $examDate . $examType . $jisshiId] = $htsis;
        }
        return (int) $htsis;
    }

    /**
     * 
     * @param type $orgSchoolYearId
     * @param type $year
     * @param string $examDate
     */
    function getARSis($orgSchoolYearId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $cis = $this->getCis($orgSchoolYearId, $year, $examDate, $examType, $jisshiId);
        $per = 0;
        if ($cis) {
            $as = $this->getRSis($orgSchoolYearId, $year, $examDate, $examType, $jisshiId);
            $per = round($as / $cis, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * 
     * @param type $orgSchoolYearId
     * @param type $year
     * @param string $examDate
     */
    function getALSis($orgSchoolYearId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $cis = $this->getCis($orgSchoolYearId, $year, $examDate, $examType, $jisshiId);
        $per = 0;
        if ($cis) {
            $as = $this->getLSis($orgSchoolYearId, $year, $examDate, $examType, $jisshiId);
            $per = round($as / $cis, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * Total reading skill score of all pupil in class of IBA Test Results
     * 
     * @param type $classId
     * @param type $year
     * @param string $examDate
     * @return type
     */
    function getRSic($classId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $eikenId = 0;
        $classId = (int) $classId;
        $year = (int) $year;
        $RSic = isset($this->temp ['RSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId]) ? $this->temp ['RSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId] : 0;
        if (!isset($this->temp ['RSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId])) {
            $RSic = $this->em->getRepository('Application\Entity\IBATestResult')
                    ->sumIBAScore(
                    array(
                        'field' => 'read',
                        'year' => $year,
                        'classId' => $classId,
                        'examDate' => $examDate,
                        'examType' => $examType,
                        'jisshiId' => $jisshiId,
                    )
            );
            $this->temp ['RSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId] = $RSic;
        }
        return (int) $RSic;
    }

    /**
     * Total listening skill score of all pupil in class of IBA Test Results
     * 
     * @param type $classId
     * @param type $year
     * @param string $examDate
     * @return type
     */
    function getLSic($classId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $eikenId = 0;
        $classId = (int) $classId;
        $year = (int) $year;
        $LSic = isset($this->temp ['LSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId]) ? $this->temp ['LSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId] : 0;
        if (!isset($this->temp ['LSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId])) {
            $LSic = $this->em->getRepository('Application\Entity\IBATestResult')
                    ->sumIBAScore(
                    array(
                        'field' => 'listen',
                        'year' => $year,
                        'classId' => $classId,
                        'examDate' => $examDate,
                        'examType' => $examType,
                        'jisshiId' => $jisshiId,
                    )
            );
            $this->temp ['LSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId] = $LSic;
        }
        return (int) $LSic;
    }

    /**
     * Total of 2 skill score of all pupil in IBA Test Results belong to class 
     *
     * @param type $classId
     * @param type $year
     * @param string $examDate
     * @return type
     */
    function getESic($classId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        return $this->getRSic($classId, $year, $examDate, $examType, $jisshiId) + $this->getLSic($classId, $year, $examDate, $examType, $jisshiId);
    }

    /**
     * AttendRate
     * 
     * @param type $classId
     * @param type $year
     * @param string $examDate
     */
    function getARic($classId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $tc = $this->getTc($classId);
        $per = 0;
        if ($tc) {
            $cic = $this->getCic($classId, $year, $examDate, $examType, $jisshiId);
            $per = round($cic / $tc * 100, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * AverageScore
     * 
     * @param type $classId
     * @param type $year
     * @param string $examDate
     */
    function getASic($classId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $cic = $this->getCic($classId, $year, $examDate, $examType, $jisshiId);
        $per = 0;
        if ($cic) {
            $esic = $this->getESic($classId, $year, $examDate, $examType, $jisshiId);
            $per = round($esic / $cic, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * lookup total CSE score lowest of IBA Test Results of class
     * 
     * @param type $classId
     * @param type $year
     * @param string $examDate
     */
    function getLTSic($classId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $eikenId = 0;
        $classId = (int) $classId;
        $year = (int) $year;
        $LTSic = isset($this->temp ['LTSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId]) ? $this->temp ['LTSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId] : 0;
        if (!isset($this->temp ['LTSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId])) {
            $iBATestResultRepository = $this->em->getRepository('Application\Entity\IBATestResult');
            $LTSic = $iBATestResultRepository->getIBAEdgeScore($year, $iBATestResultRepository::SCORE_TYPE_NAME_CLASS, $classId, $iBATestResultRepository::SCORE_EDGE_LEAST, $examDate, $examType, $jisshiId);
            $this->temp ['LTSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId] = $LTSic;
        }
        return (int) $LTSic;
    }

    /**
     * lookup total CSE score highest of IBA Test Results of class
     * 
     * @param type $classId
     * @param type $year
     * @param string $examDate
     */
    function getHTSic($classId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $eikenId = 0;
        $classId = (int) $classId;
        $year = (int) $year;
        $HTSic = isset($this->temp ['HTSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId]) ? $this->temp ['HTSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId] : 0;
        if (!isset($this->temp ['HTSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId])) {
            $iBATestResultRepository = $this->em->getRepository('Application\Entity\IBATestResult');
            $HTSic = $iBATestResultRepository->getIBAEdgeScore($year, $iBATestResultRepository::SCORE_TYPE_NAME_CLASS, $classId, $iBATestResultRepository::SCORE_EDGE_GREATEST, $examDate, $examType, $jisshiId);
            $this->temp ['HTSic_' . $eikenId . $classId . $year . $examDate . $examType . $jisshiId] = $HTSic;
        }
        return (int) $HTSic;
    }

    /**
     * AverageReadingScore
     * 
     * @param type $classId
     * @param type $year
     * @param string $examDate
     * @return type
     */
    function getARSic($classId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $cic = $this->getCic($classId, $year, $examDate, $examType, $jisshiId);
        $per = 0;
        if ($cic) {
            $as = $this->getRSic($classId, $year, $examDate, $examType, $jisshiId);
            $per = round($as / $cic, self::DECIMALLIMIT);
        }
        return $per;
    }

    /**
     * AverageListeningScore
     * 
     * @param type $classId
     * @param type $year
     * @param string $examDate
     * @return type
     */
    function getALSic($classId = 0, $year = 0, $examDate = '', $examType = '', $jisshiId = '') {
        $cic = $this->getCic($classId, $year, $examDate, $examType, $jisshiId);
        $per = 0;
        if ($cic) {
            $as = $this->getLSic($classId, $year, $examDate, $examType, $jisshiId);
            $per = round($as / $cic, self::DECIMALLIMIT);
        }
        return $per;
    }

    //*************************************************** END IBA CSEScore *************************************************************
    //
    //*************************************************** Support Functions ****************************************************
    /**
     * ExamDate
     * @param type $kai
     * @param type $organizationId
     * @param type $year
     */
    function getEikenTestDate($kai = 0, $organizationId = 0, $year = 0) {
        $kai = (int) $kai;
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        //
        $date = isset($this->temp ['EikenTestDate_' . $kai . $organizationId . $year]) ? $this->temp ['EikenTestDate_' . $kai . $organizationId . $year] : '';
        if (!isset($this->temp ['EikenTestDate_' . $kai . $organizationId . $year])) {
            $date = $this->em->getRepository('Application\Entity\EikenTestResult')->getTestDate($kai, $organizationId, $year);
            $this->temp ['EikenTestDate_' . $kai . $organizationId . $year] = $date;
        }
        return $date;
    }

    /**
     * ExamDate
     * @param type $kai
     * @param type $organizationId
     * @param type $year
     */
    function getIBATestDate($organizationId = 0, $year = 0) {
        $kai = 0;
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        //
        $date = isset($this->temp ['IBATestDate_' . $kai . $organizationId . $year]) ? $this->temp ['IBATestDate_' . $kai . $organizationId . $year] : '';
        if (!isset($this->temp ['IBATestDate_' . $kai . $organizationId . $year])) {
            $date = $this->em->getRepository('Application\Entity\IBATestResult')->getTestDate($organizationId, $year);
            $this->temp ['IBATestDate_' . $kai . $organizationId . $year] = $date;
        }
        return $date;
    }

    /**
     * reuturn list Eiken Id down line
     *
     * @param number $eikenId        	
     */
    function getListEikenUpLine($eikenId = 0) {
        $listEL = $this->getListEikenLevel();
        $eikenId = (int) $eikenId;
        $results = isset($this->temp ['ListEikenUpLine_' . $eikenId]) ? $this->temp ['ListEikenUpLine_' . $eikenId] : array();
        if (!isset($this->temp ['ListEikenUpLine_' . $eikenId])) {
            $check = false;
            //
            foreach ($listEL as $el) {
                if ($el ['id'] == $eikenId) {
                    $results [] = $eikenId;
                    $check = true;
                    break;
                } else
                    $results [] = $el ['id'];
            }
            if (!$check)
                $results = array();
            $this->temp ['ListEikenUpLine_' . $eikenId] = $results;
        }
        return $results;
    }

    /**
     * return list EikenLevel
     */
    function getListEikenLevel() {
        if (!isset($this->temp ['listEL'])) {
            $ELClass = $this->em->getRepository('Application\Entity\EikenLevel');
            $listEL = $ELClass->ListEikenLevel();
            foreach ($listEL as $eL)
                $listEL['id'] = $eL;
            $this->temp ['listEL'] = $listEL;
        } else
            $listEL = $this->temp ['listEL'];
        return $listEL;
    }

    /**
     * get list class of OrgSchoolYear
     * @param type $orgSchoolYearId
     * @param type $year
     * @return type
     */
    function getListClassByOrgSchoolYear($orgSchoolYearId = 0, $year = 0) {
        $orgSchoolYearId = (int) $orgSchoolYearId;
        $year = (int) $year;
        $results = isset($this->temp ['listClassByOrgSchoolYear_' . $orgSchoolYearId . $year]) ? $this->temp ['listClassByOrgSchoolYear' . $orgSchoolYearId . $year] : array();
        if (!$results) {
            $ClassJ = $this->em->getRepository('Application\Entity\ClassJ');
            $classes = $ClassJ->getListClassByOrgSchoolYear($orgSchoolYearId, $year);
            if ($classes)
                $results = $classes;
            $this->temp ['listClassByOrgSchoolYear' . $orgSchoolYearId . $year] = $classes;
        }
        return $results;
    }

    /**
     * return list distinct kai of Org follow year
     * @param type $organizationId
     * @param type $year
     * @return type
     */
    function getDistinctKaiOfOrg($organizationId = 0, $year = 0) {
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        $results = isset($this->temp ['DistinctKaiOfOrg_' . $organizationId . $year]) ? $this->temp ['DistinctKaiOfOrg_' . $organizationId . $year] : array();
        if (!$results) {
            $results = $this->em->getRepository('Application\Entity\EikenTestResult')->getDistinctKaiOfOrg($organizationId, $year);
            $this->temp ['DistinctKaiOfOrg_' . $organizationId . $year] = $results;
        }
        return $results;
    }

    /**
     * get list examdate of Org in IBA
     * 
     * @param type $organizationId
     * @param type $year
     * @return type
     */
    function getDistinctExamDateOfOrg($organizationId = 0, $year = 0) {
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        $results = isset($this->temp ['DistinctExamDateOfOrg_' . $organizationId . $year]) ? $this->temp ['DistinctExamDateOfOrg_' . $organizationId . $year] : array();
        if (!$results) {
            $results = $this->em->getRepository('Application\Entity\IBATestResult')->getDistinctExamDateOfOrg($organizationId, $year);
            $this->temp ['DistinctExamDateOfOrg_' . $organizationId . $year] = $results;
        }
        return $results;
    }
    
    /**
     * get list exam IBA of Org in IBA
     * 
     * @param type $organizationId
     * @param type $year
     * @return array
     */
    function getDistinctExamIBAOfOrg($organizationId, $year){
        $organizationId = (int) $organizationId;
        $year = (int) $year;
        $results = isset($this->temp ['DistinctExamIBAOfOrg_' . $organizationId . $year]) ? $this->temp ['DistinctExamDateOfOrg_' . $organizationId . $year] : array();
        if (!$results) {
            $results = $this->em->getRepository('Application\Entity\IBATestResult')->getDistinctExamIBAOfOrg($organizationId, $year);
            $this->temp ['DistinctExamIBAOfOrg_' . $organizationId . $year] = $results;
        }
        return $results;
    }

    //*************************************************** END Support Functions ****************************************************
}
