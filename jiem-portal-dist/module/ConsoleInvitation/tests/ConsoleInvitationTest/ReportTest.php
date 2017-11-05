<?php

/**
 * Zend Framework (http://framework.zend.com/)
 * 
 * @author minhbn1<minhbn1@fsoft.com.vn>
 *
 * @link      http://github.com/zendframework/BasicConstruction for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ConsoleInvitationTest;

use ConsoleInvitation\Service\AchievementService;
use ConsoleInvitation\Service\GoalResultService;
use ConsoleInvitation\Service\CseResultService;

class ReportTest extends \Dantai\Test\AbstractHttpControllerTestCase {

    const DECIMAL_LENG = 0;

    protected $achievementService;

    function __construct() {

    }

    function testWhenDeleteClass() {
//        $dantaiService = $this->getApplicationServiceLocator()->get('Application\Service\DantaiServiceInterface');
//        $achievementService = new AchievementService($this->getApplicationServiceLocator());
//        $goalResultService = new GoalResultService($this->getApplicationServiceLocator());
//        $cseResultService = new CseResultService($this->getApplicationServiceLocator());
//        $em = $this->getEntityManager();
//        //
//        $qb = $em->createQueryBuilder();
//        $qb->delete('\Application\Entity\GoalResults', 'GoalResults')
//                ->where('GoalResults.year=' . $year)
//                ->andWhere('GoalResults.organizationId= ' . $organizationId);
//        return $qb->getQuery()->execute();
        //
        //$this->assertSame();
    }

    /**
     * when get number of pupils of class
     */
    function testWhenGetTc() {
        $classId = 1;
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $Tc = $achievementService->getTc($classId);
        $compare = 0;
        if($Tc){
            $class = $this->getEntityManager()->getRepository('Application\Entity\ClassJ')->findOneBy(array(
                'id' => $classId
            ));
            if ($class) {
                $compare = (int) $class->getNumberOfStudent();
            }
            $this->assertSame($compare, $Tc, '"Tc" with class id is 1 was not set correctly');
        }
    }

    /**
     * when get number of pupils of OrgSchoolYear
     */
    function testWhenGetTs() {
        $orgSchoolYearId = 1;
        $year = $this->getCurrentYear();
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $Ts = $achievementService->getTs($orgSchoolYearId, $year);
        $compare = 0;
        // get all Class Of OrgSchoolYear
        $classs = $achievementService->getListClassByOrgSchoolYear($orgSchoolYearId, $year);
        foreach ($classs as $class) {
            if ($class)
                $compare+=(int) $class->getNumberOfStudent();
        }
        $this->assertSame($compare, $Ts, '"Ts" with OrgSchooYear id is 1 was not set correctly');
    }

    /**
     * when get number of pupils of OrgSchoolYear
     */
    function testWhenOrgSchoolYearHaveNoClass() {
        $orgSchoolYearId = 10000000; // To suppose the OrgSchoolYear have no class
        $year = $this->getCurrentYear();
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $Ts = $achievementService->getTs($orgSchoolYearId, $year);
        $compare = 0;
        // get all Class Of OrgSchoolYear
        $classs = $achievementService->getListClassByOrgSchoolYear($orgSchoolYearId, $year);
        foreach ($classs as $class) {
            if ($class)
                $compare+=(int) $class->getNumberOfStudent();
        }
        $this->assertSame($compare, $Ts, '"Ts" with OrgSchooYear have no class was not set correctly');
    }

    /**
     * when get TDs5
     */
    function testWhenGetTDs5() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 7; // s5
        $year = $this->getCurrentYear();
        //
        $TDs5 = $achievementService->getTDs($eikenId, $orgSchoolYearId, $year);
        //
        $compare = $achievementService->getTAs(7, $orgSchoolYearId, $year) +
                $achievementService->getTAs(6, $orgSchoolYearId, $year) +
                $achievementService->getTAs(5, $orgSchoolYearId, $year) +
                $achievementService->getTAs(4, $orgSchoolYearId, $year) +
                $achievementService->getTAs(3, $orgSchoolYearId, $year) +
                $achievementService->getTAs(2, $orgSchoolYearId, $year) +
                $achievementService->getTAs(1, $orgSchoolYearId, $year);
        //
        $this->assertSame($compare, $TDs5, '"TDs5" was not set correctly');
    }

    /**
     * when get TDc5
     */
    function testWhenGetTDc5() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $classId = 1;
        $eikenId = 7; // c5
        $year = $this->getCurrentYear();
        //
        $TDc5 = $achievementService->getTDc($eikenId, $classId, $year);
        //
        $compare = $achievementService->getTAc(7, $classId, $year) +
                $achievementService->getTAc(6, $classId, $year) +
                $achievementService->getTAc(5, $classId, $year) +
                $achievementService->getTAc(4, $classId, $year) +
                $achievementService->getTAc(3, $classId, $year) +
                $achievementService->getTAc(2, $classId, $year) +
                $achievementService->getTAc(1, $classId, $year);
        //
        $this->assertSame($compare, $TDc5, '"TDc5" was not set correctly');
    }

    /**
     * when get TDs4
     */
    function testWhenGetTDs4() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 6; // s4
        $year = (int) date('Y');
        //
        $TDs4 = $achievementService->getTDs($eikenId, $orgSchoolYearId, $year);
        //
        $compare = $achievementService->getTAs(6, $orgSchoolYearId, $year) +
                $achievementService->getTAs(5, $orgSchoolYearId, $year) +
                $achievementService->getTAs(4, $orgSchoolYearId, $year) +
                $achievementService->getTAs(3, $orgSchoolYearId, $year) +
                $achievementService->getTAs(2, $orgSchoolYearId, $year) +
                $achievementService->getTAs(1, $orgSchoolYearId, $year);
        //
        $this->assertSame($compare, $TDs4, '"TDs4" was not set correctly');
    }

    /**
     * when get TDc4
     */
    function testWhenGetTDc4() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $classId = 1;
        $eikenId = 6; // c4
        $year = $this->getCurrentYear();
        //
        $TDc4 = $achievementService->getTDc($eikenId, $classId, $year);
        //
        $compare = $achievementService->getTAc(6, $classId, $year) +
                $achievementService->getTAc(5, $classId, $year) +
                $achievementService->getTAc(4, $classId, $year) +
                $achievementService->getTAc(3, $classId, $year) +
                $achievementService->getTAc(2, $classId, $year) +
                $achievementService->getTAc(1, $classId, $year);
        //
        $this->assertSame($compare, $TDc4, '"TDc4" was not set correctly');
    }

    /**
     * when get TDs3
     */
    function testWhenGetTDs3() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 5; // s3
        $year = $this->getCurrentYear();
        //
        $TDs3 = $achievementService->getTDs($eikenId, $orgSchoolYearId, $year);
        //
        $compare = $achievementService->getTAs(5, $orgSchoolYearId, $year) +
                $achievementService->getTAs(4, $orgSchoolYearId, $year) +
                $achievementService->getTAs(3, $orgSchoolYearId, $year) +
                $achievementService->getTAs(2, $orgSchoolYearId, $year) +
                $achievementService->getTAs(1, $orgSchoolYearId, $year);
        //
        $this->assertSame($compare, $TDs3, '"TDs3" was not set correctly');
    }

    /**
     * when get TDc3
     */
    function testWhenGetTDc3() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $classId = 1;
        $eikenId = 5; // c3
        $year = $this->getCurrentYear();
        //
        $TDc3 = $achievementService->getTDc($eikenId, $classId, $year);
        //
        $compare = $achievementService->getTAc(5, $classId, $year) +
                $achievementService->getTAc(4, $classId, $year) +
                $achievementService->getTAc(3, $classId, $year) +
                $achievementService->getTAc(2, $classId, $year) +
                $achievementService->getTAc(1, $classId, $year);
        //
        $this->assertSame($compare, $TDc3, '"TDc3" was not set correctly');
    }

    /**
     * when get TDsp2
     */
    function testWhenGetTDsp2() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 4; // sp2
        $year = $this->getCurrentYear();
        //
        $TDsp2 = $achievementService->getTDs($eikenId, $orgSchoolYearId, $year);
        //
        $compare = $achievementService->getTAs(4, $orgSchoolYearId, $year) +
                $achievementService->getTAs(3, $orgSchoolYearId, $year) +
                $achievementService->getTAs(2, $orgSchoolYearId, $year) +
                $achievementService->getTAs(1, $orgSchoolYearId, $year);
        //
        $this->assertSame($compare, $TDsp2, '"TDsp2" was not set correctly');
    }

    /**
     * when get TDcp2
     */
    function testWhenGetTDcp2() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $classId = 1;
        $eikenId = 4; // cp2
        $year = $this->getCurrentYear();
        //
        $TDcp2 = $achievementService->getTDc($eikenId, $classId, $year);
        //
        $compare = $achievementService->getTAc(4, $classId, $year) +
                $achievementService->getTAc(3, $classId, $year) +
                $achievementService->getTAc(2, $classId, $year) +
                $achievementService->getTAc(1, $classId, $year);
        //
        $this->assertSame($compare, $TDcp2, '"TDcp2" was not set correctly');
    }

    /**
     * when get TDs2
     */
    function testWhenGetTDs2() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 3; // s2
        $year = $this->getCurrentYear();
        //
        $TDs2 = $achievementService->getTDs($eikenId, $orgSchoolYearId, $year);
        //
        $compare = $achievementService->getTAs(3, $orgSchoolYearId, $year) +
                $achievementService->getTAs(2, $orgSchoolYearId, $year) +
                $achievementService->getTAs(1, $orgSchoolYearId, $year);
        //
        $this->assertSame($compare, $TDs2, '"TDs2" was not set correctly');
    }

    /**
     * when get TDc2
     */
    function testWhenGetTDc2() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $classId = 1;
        $eikenId = 3; // c2
        $year = $this->getCurrentYear();
        //
        $TDc2 = $achievementService->getTDc($eikenId, $classId, $year);
        //
        $compare = $achievementService->getTAc(3, $classId, $year) +
                $achievementService->getTAc(2, $classId, $year) +
                $achievementService->getTAc(1, $classId, $year);
        //
        $this->assertSame($compare, $TDc2, '"TDc2" was not set correctly');
    }

    /**
     * when get TDsp1
     */
    function testWhenGetTDsp1() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 2; // sp1
        $year = $this->getCurrentYear();
        //
        $TDsp1 = $achievementService->getTDs($eikenId, $orgSchoolYearId, $year);
        //
        $compare = $achievementService->getTAs(2, $orgSchoolYearId, $year) +
                $achievementService->getTAs(1, $orgSchoolYearId, $year);
        //
        $this->assertSame($compare, $TDsp1, '"TDsp1" was not set correctly');
    }

    /**
     * when get TDcp1
     */
    function testWhenGetTDcp1() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $classId = 1;
        $eikenId = 2; // cp1
        $year = $this->getCurrentYear();
        //
        $TDcp1 = $achievementService->getTDc($eikenId, $classId, $year);
        //
        $compare = $achievementService->getTAc(2, $classId, $year) +
                $achievementService->getTAc(1, $classId, $year);
        //
        $this->assertSame($compare, $TDcp1, '"TDcp1" was not set correctly');
    }

    /**
     * when get TDs1
     */
    function testWhenGetTDs1() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 1; // s1
        $year = $this->getCurrentYear();
        //
        $TDs1 = $achievementService->getTDs($eikenId, $orgSchoolYearId, $year);
        //
        $compare = $achievementService->getTAs(1, $orgSchoolYearId, $year);
        //
        $this->assertSame($compare, $TDs1, '"TDs1" was not set correctly');
    }

    /**
     * when get TDcp1
     */
    function testWhenGetTDc1() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $classId = 1;
        $eikenId = 2; // c1
        $year = $this->getCurrentYear();
        //
        $TDc1 = $achievementService->getTDc($eikenId, $classId, $year);
        //
        $compare = $achievementService->getTAc(2, $classId, $year) +
                $achievementService->getTAc(1, $classId, $year);
        //
        $this->assertSame($compare, $TDc1, '"TDc1" was not set correctly');
    }

    /**
     * when get GDs5
     */
    function testWhenGetGDs5() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 7; // s5
        $year = $this->getCurrentYear();
        $GDs5 = $achievementService->getGDs($eikenId, $orgSchoolYearId, $year);
        $ts = $achievementService->getTs($orgSchoolYearId, $year);
        $compare = $ts ? round($achievementService->getTDs($eikenId, $orgSchoolYearId, $year) / $ts * 100, self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $GDs5, '"GDs5" was not set correctly');
    }

    /**
     * when get GDs4
     */
    function testWhenGetGDs4() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 6; // s4
        $year = $this->getCurrentYear();
        $ts = $achievementService->getTs($orgSchoolYearId, $year);
        $GDs5 = $achievementService->getGDs($eikenId, $orgSchoolYearId, $year);
        $compare = $ts ? round($achievementService->getTDs($eikenId, $orgSchoolYearId, $year) / $ts * 100, self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $GDs5, '"GDs4" was not set correctly');
    }

    /**
     * when get GDc5
     */
    function testWhenGetGDc5() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $classId = 1;
        $eikenId = 7; // c5
        $year = $this->getCurrentYear();
        $tc = $achievementService->getTc($classId);
        $GDc5 = $achievementService->getGDc($eikenId, $classId, $year);
        $compare = $tc ? round($achievementService->getTDc($eikenId, $classId, $year) / $tc * 100, self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $GDc5, '"GDc5" was not set correctly');
    }

    /**
     * when get GDc5
     */
    function testWhenGetGDc4() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $classId = 1;
        $eikenId = 6; // c4
        $year = $this->getCurrentYear();
        $tc = $achievementService->getTc($classId);
        $GDc4 = $achievementService->getGDc($eikenId, $classId, $year);
        $compare = $tc ? round($achievementService->getTDc($eikenId, $classId, $year) / $tc * 100, self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $GDc4, '"GDc4" was not set correctly');
    }

    /**
     * when get GDc5
     */
    function testWhenGetGDc3() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $classId = 1;
        $eikenId = 5; // c3
        $year = $this->getCurrentYear();
        $tc = $achievementService->getTc($classId);
        $GDc3 = $achievementService->getGDc($eikenId, $classId, $year);
        $compare = $tc ? round($achievementService->getTDc($eikenId, $classId, $year) / $tc * 100, self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $GDc3, '"GDc3" was not set correctly');
    }

    /**
     * when get GDs5 with Ts=0
     */
    function testWhenGetGDs5WithTsEqual0() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 100000000000;
        $eikenId = 7; // s5
        $year = $this->getCurrentYear();
        $GDs5 = $achievementService->getGDs($eikenId, $orgSchoolYearId, $year);
        $compare = 0;
        $this->assertSame($compare, $GDs5, '"GDs5WithTs0" was not set correctly');
    }

    /**
     * when get RDs5 and RDc5
     */
    function testWhenGetRD5() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 7; // s5
        $year = $this->getCurrentYear();
        $RDs5 = $achievementService->getRDs($eikenId, $orgSchoolYearId, $year);
        $compare = $achievementService->getGDs($eikenId, $orgSchoolYearId, $year);
        $this->assertSame($compare, $RDs5, '"RDs5" was not set correctly');
        //
        $classId = 1;
        $RDc5 = $achievementService->getRDc($eikenId, $classId, $year);
        $compare = $achievementService->getGDc($eikenId, $classId, $year);
        $this->assertSame($compare, $RDc5, '"RDc5" was not set correctly');
    }

    /**
     * when get RDs4 and RDc4
     */
    function testWhenGetRD4() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 6; // s4
        $year = $this->getCurrentYear();
        $RDs4 = $achievementService->getRDs($eikenId, $orgSchoolYearId, $year);
        $compare = $achievementService->getGDs($eikenId, $orgSchoolYearId, $year);
        $this->assertSame($compare, $RDs4, '"RDs4" was not set correctly');
        //
        $classId = 1;
        $RDc4 = $achievementService->getRDc($eikenId, $classId, $year);
        $compare = $achievementService->getGDc($eikenId, $classId, $year);
        $this->assertSame($compare, $RDc4, '"RDc4" was not set correctly');
    }

    /**
     * when get RDs3 and RDc3
     */
    function testWhenGetRD3() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 5; // s3
        $year = $this->getCurrentYear();
        $RDs3 = $achievementService->getRDs($eikenId, $orgSchoolYearId, $year);
        $compare = $achievementService->getGDs($eikenId, $orgSchoolYearId, $year);
        $this->assertSame($compare, $RDs3, '"RDs3" was not set correctly');
        //
        $classId = 1;
        $RDc3 = $achievementService->getRDc($eikenId, $classId, $year);
        $compare = $achievementService->getGDc($eikenId, $classId, $year);
        $this->assertSame($compare, $RDc3, '"RDc3" was not set correctly');
    }

    /**
     * when get RDs1 and RDc1
     */
    function testWhenGetRD1() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 1; // s1
        $year = $this->getCurrentYear();
        $RDs1 = $achievementService->getRDs($eikenId, $orgSchoolYearId, $year);
        $compare = $achievementService->getGDs($eikenId, $orgSchoolYearId, $year);
        $this->assertSame($compare, $RDs1, '"RDs1" was not set correctly');
        //
        $classId = 1;
        $RDc1 = $achievementService->getRDc($eikenId, $classId, $year);
        $compare = $achievementService->getGDc($eikenId, $classId, $year);
        $this->assertSame($compare, $RDc1, '"RDc1" was not set correctly');
    }

    //****************************************** Actual Rate ********************************************************
    /**
     * When get TAs5 and TAc5
     */
    function testWhenGetTA5() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $eikenId = 7; // k5
        $year = $this->getCurrentYear();
        $orgSchoolYearId = 1;
        $classId = 1;
        $TAc5 = $achievementService->getTAc($eikenId, $classId, $year);
        //
        $TAs5 = $achievementService->getTAs($eikenId, $orgSchoolYearId, $year);
        $compare = 0;
        $classs = $achievementService->getListClassByOrgSchoolYear($orgSchoolYearId, $year);
        foreach ($classs as $class) {
            if ($class)
                $compare+=$achievementService->getTAc($eikenId, $class->getId(), $year);
        }
        $this->assertSame($compare, $TAs5, '"TAs5" was not set correctly');
    }

    /**
     * When get TAs4 and TAc4
     */
    function testWhenGetTA4() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $eikenId = 6; // k4
        $year = $this->getCurrentYear();
        $orgSchoolYearId = 1;
        $classId = 1;
        $TAc4 = $achievementService->getTAc($eikenId, $classId, $year);
        //
        $TAs4 = $achievementService->getTAs($eikenId, $orgSchoolYearId, $year);
        $compare = 0;
        $classs = $achievementService->getListClassByOrgSchoolYear($orgSchoolYearId, $year);
        foreach ($classs as $class) {
            if ($class)
                $compare+=$achievementService->getTAc($eikenId, $class->getId(), $year);
        }
        $this->assertSame($compare, $TAs4, '"TAs4" was not set correctly');
    }

    /**
     * When get TAs3 and TAc3
     */
    function testWhenGetTA3() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $eikenId = 5; // k3
        $year = $this->getCurrentYear();
        $orgSchoolYearId = 1;
        $classId = 1;
        $TAc3 = $achievementService->getTAc($eikenId, $classId, $year);
        //
        $TAs3 = $achievementService->getTAs($eikenId, $orgSchoolYearId, $year);
        $compare = 0;
        $classs = $achievementService->getListClassByOrgSchoolYear($orgSchoolYearId, $year);
        foreach ($classs as $class) {
            if ($class)
                $compare+=$achievementService->getTAc($eikenId, $class->getId(), $year);
        }
        $this->assertSame($compare, $TAs3, '"TAs3" was not set correctly');
    }

    /**
     * When get TAs1 and TAc1
     */
    function testWhenGetTA1() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $eikenId = 1; // k1
        $year = $this->getCurrentYear();
        $orgSchoolYearId = 1;
        $classId = 1;
        $TAc1 = $achievementService->getTAc($eikenId, $classId, $year);
        //
        $TAs1 = $achievementService->getTAs($eikenId, $orgSchoolYearId, $year);
        $compare = 0;
        $classs = $achievementService->getListClassByOrgSchoolYear($orgSchoolYearId, $year);
        foreach ($classs as $class) {
            if ($class)
                $compare+=$achievementService->getTAc($eikenId, $class->getId(), $year);
        }
        $this->assertSame($compare, $TAs1, '"TAs1" was not set correctly');
    }

    /**
     * when get GAs5            
     */
    function testWhenGetGAs5() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $eikenId = 7; // s5
        $year = $this->getCurrentYear();
        $orgSchoolYearId = 1;
        $ts = $achievementService->getTs($orgSchoolYearId, $year);
        $GAs5 = $achievementService->getGAs($eikenId, $orgSchoolYearId, $year);
        $compare = $ts ? round($achievementService->getTAs($eikenId, $orgSchoolYearId, $year) / $ts * 100, self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $GAs5, '"GAs5" was not set correctly');
    }

    /**
     * when get GAs4           
     */
    function testWhenGetGAs4() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $eikenId = 6; // s4
        $year = $this->getCurrentYear();
        $orgSchoolYearId = 1;
        $ts = $achievementService->getTs($orgSchoolYearId, $year);
        $GAs4 = $achievementService->getGAs($eikenId, $orgSchoolYearId, $year);
        $compare = $ts ? round($achievementService->getTAs($eikenId, $orgSchoolYearId, $year) / $ts * 100, self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $GAs4, '"GAs4" was not set correctly');
    }

    /**
     * when get GAs4           
     */
    function testWhenGetGAs2() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $eikenId = 3; // s2
        $year = $this->getCurrentYear();
        $orgSchoolYearId = 1;
        $ts = $achievementService->getTs($orgSchoolYearId, $year);
        $GAs2 = $achievementService->getGAs($eikenId, $orgSchoolYearId, $year);
        $compare = $ts ? round($achievementService->getTAs($eikenId, $orgSchoolYearId, $year) / $ts * 100, self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $GAs2, '"GAs2" was not set correctly');
    }

    /**
     * when get RAs5 and RAc5
     */
    function testWhenGetRA5() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 7; // s5
        $year = $this->getCurrentYear();
        $RDs5 = $achievementService->getRAs($eikenId, $orgSchoolYearId, $year);
        $compare = $achievementService->getGAs($eikenId, $orgSchoolYearId, $year);
        $this->assertSame($compare, $RDs5, '"RAs5" was not set correctly');
        //
        $classId = 1;
        $RDc5 = $achievementService->getRAc($eikenId, $classId, $year);
        $compare = $achievementService->getGAc($eikenId, $classId, $year);
        $this->assertSame($compare, $RDc5, '"RAc5" was not set correctly');
    }

    /**
     * when get RAs5 and RAc5
     */
    function testWhenGetRA4() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 6; // s4
        $year = $this->getCurrentYear();
        $RDs4 = $achievementService->getRAs($eikenId, $orgSchoolYearId, $year);
        $compare = $achievementService->getGAs($eikenId, $orgSchoolYearId, $year);
        $this->assertSame($compare, $RDs4, '"RAs4" was not set correctly');
        //
        $classId = 1;
        $RDc4 = $achievementService->getRAc($eikenId, $classId, $year);
        $compare = $achievementService->getGAc($eikenId, $classId, $year);
        $this->assertSame($compare, $RDc4, '"RAc4" was not set correctly');
    }

    /**
     * when get RAs5 and RAc5
     */
    function testWhenGetRA2() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $eikenId = 3; // s2
        $year = $this->getCurrentYear();
        $RDs2 = $achievementService->getRAs($eikenId, $orgSchoolYearId, $year);
        $compare = $achievementService->getGAs($eikenId, $orgSchoolYearId, $year);
        $this->assertSame($compare, $RDs2, '"RAs2" was not set correctly');
        //
        $classId = 1;
        $RDc2 = $achievementService->getRAc($eikenId, $classId, $year);
        $compare = $achievementService->getGAc($eikenId, $classId, $year);
        $this->assertSame($compare, $RDc2, '"RAc2" was not set correctly');
    }

    //****************************************** END Actual Rate ********************************************************
    //
    //****************************************** Eiken CSE Score ********************************************************
    function testWhenGetCeo() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $kai = 1;
        $year = $this->getCurrentYear();
        $ceo = $achievementService->getCeo($kai, $organizationId, $year);
        $this->assertTrue(true);
    }

    function testWhenGetRSeo() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $kai = 1;
        $year = $this->getCurrentYear();
        $RSeo = $achievementService->getRSeo($kai, $organizationId, $year);
        $this->assertTrue(true);
    }

    function testWhenGetLSeo() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $kai = 1;
        $year = $this->getCurrentYear();
        $LSeo = $achievementService->getLSeo($kai, $organizationId, $year);
        $this->assertTrue(true);
    }

    function testWhenGetSSeo() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $kai = 1;
        $year = $this->getCurrentYear();
        $SSeo = $achievementService->getSSeo($kai, $organizationId, $year);
        $this->assertTrue(true);
    }

    function testWhenGetWSeo() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $kai = 1;
        $year = $this->getCurrentYear();
        $WSeo = $achievementService->getWSeo($kai, $organizationId, $year);
        $this->assertTrue(true);
    }

    /**
     * when get total of 4 skill
     */
    function testWhenGetESeo() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $kai = 1;
        $year = $this->getCurrentYear();
        $ESeo = $achievementService->getESeo($kai, $organizationId, $year);
        $compare = $achievementService->getRSeo($kai, $organizationId, $year) + $achievementService->getLSeo($kai, $organizationId, $year) + $achievementService->getSSeo($kai, $organizationId, $year) + $achievementService->getWSeo($kai, $organizationId, $year);
        $this->assertSame($compare, $ESeo, '"ESeo" was not set correctly');
    }

    /**
     * AttendRate
     */
    function testWhenGetAReo() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $kai = 1;
        $year = $this->getCurrentYear();
        $AReo = $achievementService->getAReo($kai, $organizationId, $year);
        $compare = ($achievementService->getTo($organizationId, $year)) ? round($achievementService->getCeo($kai, $organizationId, $year) / $achievementService->getTo($organizationId, $year) * 100, self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $AReo, '"AReo" was not set correctly');
    }

    /**
     * AverageScore
     */
    function testWhenGetASeo() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $kai = 1;
        $year = $this->getCurrentYear();
        $ASeo = $achievementService->getASeo($kai, $organizationId, $year);
        $compare = ($achievementService->getCeo($kai, $organizationId, $year)) ? round($achievementService->getESeo($kai, $organizationId, $year) / $achievementService->getCeo($kai, $organizationId, $year), self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ASeo, '"ASeo" was not set correctly');
    }

    /**
     * AverageReadingScore
     */
    function testWhenGetARSeo() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $kai = 1;
        $year = $this->getCurrentYear();
        $ARSeo = $achievementService->getARSeo($kai, $organizationId, $year);
        $compare = ($achievementService->getCeo($kai, $organizationId, $year)) ? round($achievementService->getRSeo($kai, $organizationId, $year) / $achievementService->getCeo($kai, $organizationId, $year), self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ARSeo, '"ARSeo" was not set correctly');
    }

    /**
     * AverageListeningScore
     */
    function testWhenGetALSeo() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $kai = 1;
        $year = $this->getCurrentYear();
        $ALSeo = $achievementService->getALSeo($kai, $organizationId, $year);
        $compare = ($achievementService->getCeo($kai, $organizationId, $year)) ? round($achievementService->getLSeo($kai, $organizationId, $year) / $achievementService->getCeo($kai, $organizationId, $year), self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ALSeo, '"ALSeo" was not set correctly');
    }

    /**
     * AverageSpeakingScore
     */
    function testWhenGetASSeo() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $kai = 1;
        $year = $this->getCurrentYear();
        $ASSeo = $achievementService->getASSeo($kai, $organizationId, $year);
        $compare = ($achievementService->getCeo($kai, $organizationId, $year)) ? round($achievementService->getSSeo($kai, $organizationId, $year) / $achievementService->getCeo($kai, $organizationId, $year), self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ASSeo, '"ASSeo" was not set correctly');
    }

    /**
     * AveragewritingScore
     */
    function testWhenGetAWSeo() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $kai = 1;
        $year = $this->getCurrentYear();
        $AWSeo = $achievementService->getAWSeo($kai, $organizationId, $year);
        $compare = ($achievementService->getCeo($kai, $organizationId, $year)) ? round($achievementService->getWSeo($kai, $organizationId, $year) / $achievementService->getCeo($kai, $organizationId, $year), self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $AWSeo, '"AWSeo" was not set correctly');
    }

    //****************************************** END Eiken CSE Score ****************************************************
    //
    //****************************************** IBA CSE Score ****************************************************
    function testWhenGetCio() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $cio = $achievementService->getCio($organizationId, $year, $examDate);
    }

    function testWhenGetRSio() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $rsio = $achievementService->getRSio($organizationId, $year, $examDate);
    }

    function testWhenGetLSio() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $lsio = $achievementService->getLSio($organizationId, $year, $examDate);
    }

    /**
     * when get ESio
     */
    function testWhenGetESio() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $ESio = $achievementService->getESio($organizationId, $year, $examDate);
        $compare = $achievementService->getRSio($organizationId, $year, $examDate) + $achievementService->getLSio($organizationId, $year, $examDate);
        $this->assertSame($compare, $ESio, '"ESio" was not set correctly');
    }

    /**
     * when get AttendRate
     */
    function testWhenGetARio() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $ARio = $achievementService->getARio($organizationId, $year, $examDate);
        $compare = ($achievementService->getTo($organizationId, $year)) ? round($achievementService->getCio($organizationId, $year, $examDate) / $achievementService->getTo($organizationId, $year) * 100, self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ARio, '"ARio" was not set correctly');
    }

    /**
     * when get AverageScore
     */
    function testWhenGetASio() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $ASio = $achievementService->getASio($organizationId, $year, $examDate);
        $compare = ($achievementService->getCio($organizationId, $year, $examDate)) ? round($achievementService->getESio($organizationId, $year, $examDate) / $achievementService->getCio($organizationId, $year, $examDate), self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ASio, '"ASio" was not set correctly');
    }

    /**
     * when get AverageReadingScore
     */
    function testWhenGetARSio() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $ARSio = $achievementService->getARSio($organizationId, $year, $examDate);
        $compare = ($achievementService->getCio($organizationId, $year, $examDate)) ? round($achievementService->getRSio($organizationId, $year, $examDate) / $achievementService->getCio($organizationId, $year, $examDate), self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ARSio, '"ARSio" was not set correctly');
    }

    /**
     * when get AverageListeningScore
     */
    function testWhenGetALSio() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $organizationId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $ALSio = $achievementService->getALSio($organizationId, $year, $examDate);
        $compare = ($achievementService->getCio($organizationId, $year, $examDate)) ? round($achievementService->getLSio($organizationId, $year, $examDate) / $achievementService->getCio($organizationId, $year, $examDate), self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ALSio, '"ALSio" was not set correctly');
    }

    /**
     * when get ESis
     */
    function testWhenGetESis() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $ESis = $achievementService->getESis($orgSchoolYearId, $year, $examDate);
        $compare = $achievementService->getRSis($orgSchoolYearId, $year, $examDate) + $achievementService->getLSis($orgSchoolYearId, $year, $examDate);
        $this->assertSame($compare, $ESis, '"ESis" was not set correctly');
    }

    /**
     * when get AttendRate
     */
    function testWhenGetARis() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $ARis = $achievementService->getARis($orgSchoolYearId, $year, $examDate);
        $compare = ($achievementService->getTs($orgSchoolYearId, $year, $examDate)) ? round($achievementService->getCis($orgSchoolYearId, $year, $examDate) / $achievementService->getTs($orgSchoolYearId, $year, $examDate) * 100, self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ARis, '"ARis" was not set correctly');
    }

    /**
     * when get AverageScore
     */
    function testWhenGetASis() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $ASis = $achievementService->getASis($orgSchoolYearId, $year, $examDate);
        $compare = ($achievementService->getCis($orgSchoolYearId, $year, $examDate)) ? round($achievementService->getESis($orgSchoolYearId, $year, $examDate) / $achievementService->getCis($orgSchoolYearId, $year, $examDate), self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ASis, '"ASis" was not set correctly');
    }

    /**
     * when get AverageReadingScore
     */
    function testWhenGetARSis() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $ARSis = $achievementService->getARSis($orgSchoolYearId, $year, $examDate);
        $compare = ($achievementService->getCis($orgSchoolYearId, $year, $examDate)) ? round($achievementService->getRSis($orgSchoolYearId, $year, $examDate) / $achievementService->getCis($orgSchoolYearId, $year, $examDate), self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ARSis, '"ARSis" was not set correctly');
    }

    /**
     * when get AverageListeningScore
     */
    function testWhenGetALSis() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $orgSchoolYearId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $ALSis = $achievementService->getALSis($orgSchoolYearId, $year, $examDate);
        $compare = ($achievementService->getCis($orgSchoolYearId, $year, $examDate)) ? round($achievementService->getLSis($orgSchoolYearId, $year, $examDate) / $achievementService->getCis($orgSchoolYearId, $year, $examDate), self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ALSis, '"ALSis" was not set correctly');
    }

    //
    /**
     * when get ESic
     */
    function testWhenGetESic() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $classId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $ESic = $achievementService->getESic($classId, $year, $examDate);
        $compare = $achievementService->getRSic($classId, $year, $examDate) + $achievementService->getLSic($classId, $year, $examDate);
        $this->assertSame($compare, $ESic, '"ESic" was not set correctly');
    }

    /**
     * when get AttendRate
     */
    function testWhenGetARic() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $classId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $ARic = $achievementService->getARic($classId, $year, $examDate);
        $compare = ($achievementService->getTc($classId, $year, $examDate)) ? round($achievementService->getCic($classId, $year, $examDate) / $achievementService->getTc($classId, $year, $examDate) * 100, self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ARic, '"ARic" was not set correctly');
    }

    /**
     * when get AverageScore
     */
    function testWhenGetASic() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $classId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $ASic = $achievementService->getASic($classId, $year, $examDate);
        $compare = ($achievementService->getCic($classId, $year, $examDate)) ? round($achievementService->getESic($classId, $year, $examDate) / $achievementService->getCic($classId, $year, $examDate), self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ASic, '"ASic" was not set correctly');
    }

    /**
     * when get AverageReadingScore
     */
    function testWhenGetARSic() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $classId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $ARSic = $achievementService->getARSic($classId, $year, $examDate);
        $compare = ($achievementService->getCic($classId, $year, $examDate)) ? round($achievementService->getRSic($classId, $year, $examDate) / $achievementService->getCic($classId, $year, $examDate), self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ARSic, '"ARSic" was not set correctly');
    }

    /**
     * when get AverageListeningScore
     */
    function testWhenGetALSic() {
        $achievementService = new AchievementService($this->getApplicationServiceLocator());
        $classId = 1;
        $year = $this->getCurrentYear();
        $examDate = '2015-03-15 00:00:00';
        $ALSic = $achievementService->getALSic($classId, $year, $examDate);
        $compare = ($achievementService->getCic($classId, $year, $examDate)) ? round($achievementService->getLSic($classId, $year, $examDate) / $achievementService->getCic($classId, $year, $examDate), self::DECIMAL_LENG) : 0;
        $this->assertSame($compare, $ALSic, '"ALSic" was not set correctly');
    }

    //****************************************** END IBA CSE Score ****************************************************
    /**
     * Get current year for japan
     * @return number
     */
    public function getCurrentYear() {
        $month = date('m');
        if ($month < 4)
            return (int) date('Y') - 1;
        return (int) date('Y');
    }

}
