<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query\Expr\GroupBy;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Zend\Http\Header\IfMatch;
use Composer\Autoload\ClassLoader;
use Application\Entity\ActualExamResult;
use Zend\Validator\File\Count;

/**
 *
 * @author TaiVH 2015
 *        
 */
class ActualExamResultRepository extends EntityRepository
{

    /**
     * function get data for homepage with input conditions
     *
     * @author TaiVH
     * @param            
     *
     * @return data of view
     *         Author Modified Start date End date
     *         TaiVH Creates 2015-07-24 2015-07-26
     */
    public function getMaxTime($orgId = 0, $year = null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select("MAX(aeresult.time) as time")
            ->from('\Application\Entity\ActualExamResult', 'aeresult')
            ->where('aeresult.year= :year')
            ->andWhere('aeresult.organizationId = :orgId')
            ->setParameter(':year', $year)
            ->setParameter(':orgId', $orgId)
            ->andWhere('aeresult.passFlag = 1')
            ->andWhere('aeresult.isDelete = 0')
            ->groupBy('aeresult.year');
        $res = $qb->getQuery()->getArrayResult();
        if (count($res) > 0)
            return (int) $res[0]['time'];
        return 1;
    }
    
    // -- Số người thỏa mãn các đk đầu vào, với lần thi, của tổ chức
    public function getCountPassExam($orgId = 0, $year = null, $time = 0, $eikenLevelId = 0, $attendFlag = 0, $passFlag = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        if ($year == null)
            $year = date("Y");
        
        $qb->select("count(aeresult.id)")
            ->from('\Application\Entity\ActualExamResult', 'aeresult')
            ->where('aeresult.year = :year')
            ->andWhere('aeresult.attendFlag = :attendFlag')
            ->andWhere('aeresult.organizationId = :organizationId')
            ->setParameter(':year', $year)
            ->setParameter(':attendFlag', $attendFlag)
            ->setParameter(':organizationId', $orgId)
            ->andWhere('aeresult.isDelete = 0');
        if($time != 0){
            $qb->andWhere('aeresult.time = :time')->setParameter(':time', $time);
        }
        if($eikenLevelId != 0){
            $qb->andWhere('aeresult.eikenLevelId = :eikenLevelId')
                    ->setParameter(':eikenLevelId', $eikenLevelId);
        }
        if($passFlag != 0){
            $qb->andWhere('aeresult.passFlag = :passFlag')
                    ->setParameter(':passFlag', $passFlag);
        }
        
        $res = $qb->getQuery()->getSingleScalarResult();
        return $res;
    }

    /**
     * function get number people by Year And Time(kai)
     *
     * @author DucNA
     * @param $orgId int            
     * @param $year int            
     *
     * @return data of view
     *         Author Modified Start date End date
     *         DucNA Creates 2015-07-26 2015-07-26
     */
    public function getCountPeopleByYearAndTime($orgId, $year = null)
    {
        if ($year == null)
            $year = date("Y");
        $year2 = (int) $year - 2;
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('aeresult.year, aeresult.time, COUNT(aeresult.id)')
            ->from('\Application\Entity\ActualExamResult', 'aeresult')
            ->where('aeresult.year between :year2 and :year')
            ->andWhere('aeresult.organizationId = :org_id ')
            ->setParameter(':year2', $year2)
            ->setParameter(':year', $year)
            ->setParameter(':org_id', $orgId)
            ->andWhere('aeresult.isDelete = 0 ')
            ->andWhere('aeresult.attendFlag = 1 ')
            ->groupBy('aeresult.year, aeresult.time')
            ->orderBy('aeresult.year', 'DESC');
        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        return $paginator->getQuery()->getArrayResult();
    }

    /**
     * function get number people by Year And Time(kai) detail code and orgSchoolYearId
     *
     * @author DucNA
     * @param $orgId int            
     * @param $year int            
     * @param $time int            
     *
     * @return data of view
     *         Author Modified Start date End date
     *         DucNA Creates 2015-07-27 2015-07-28
     */
    public function getCountDetailCodeByYearAndTime($orgId, $year, $time)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('aeresult.year, aeresult.time, COUNT(aeresult.id), aeresult.orgClassificationId, orgCl.code, aeresult.orgSchoolYearId, orgSY.displayName, schYear.name')
            ->from('\Application\Entity\ActualExamResult', 'aeresult')
            ->leftjoin('\Application\Entity\OrganizationClassification', 'orgCl', 'WITH', 'aeresult.orgClassificationId = orgCl.id')
            ->leftjoin('\Application\Entity\OrgSchoolYear', 'orgSY', 'WITH', 'aeresult.orgSchoolYearId = orgSY.id')
            ->leftjoin('\Application\Entity\SchoolYear', 'schYear', 'WITH', 'orgSY.schoolYearId = schYear.id')
            ->where('aeresult.organizationId = :org_id')
            ->andWhere('aeresult.year = :year')
            ->setParameter(':org_id', $orgId)
            ->setParameter(':year', $year)
            ->andWhere('aeresult.attendFlag = 1')
            ->andWhere('aeresult.isDelete = 0')
            ->orderBy('aeresult.orgClassificationId', 'Asc');
        if ($time !== 'all') {
            $qb->andWhere('aeresult.time = :time ')
                ->setParameter(':time', $time)
                ->groupBy('aeresult.orgClassificationId, aeresult.orgSchoolYearId, aeresult.time, aeresult.year');
        } else {
            $qb->groupBy('aeresult.orgClassificationId, aeresult.orgSchoolYearId, aeresult.year');
        }
        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        return $paginator->getQuery()->getArrayResult();
    }

    /**
     * function get list detail exam result
     *
     * @author DucNA
     * @param $orgClassificationId int            
     * @param $orgSchoolYearId int            
     * @param $orgId int            
     * @param $year int            
     * @param $time int            
     *
     * @return list data of table ActualExamResult
     *         Author Modified Start date End date
     *         DucNA Creates 2015-07-29 2015-07-29
     */
    public function getDataDetailTableC($orgClassificationId = null, $orgSchoolYearId = null, $orgId, $year, $time, $limit, $offset)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('aeresult.year, aeresult.time, aeresult.kumi, aeresult.studentCode, aeresult.passFail1, aeresult.passFail2, aeresult.isExemption,
            aeresult.studentName,aeresult.eikenLevelId, aeresult.passFlag, aeresult.failType1, aeresult.failType2,
            aeresult.orgClassificationId, orgCl.code, aeresult.orgSchoolYearId,
            orgSY.displayName,schYear.id, schYear.name,  eikenLv.levelName')
            ->from('\Application\Entity\ActualExamResult', 'aeresult')
            ->leftjoin('\Application\Entity\OrganizationClassification', 'orgCl', 'WITH', 'aeresult.orgClassificationId = orgCl.id')
            ->leftjoin('\Application\Entity\OrgSchoolYear', 'orgSY', 'WITH', 'aeresult.orgSchoolYearId = orgSY.id')
            ->leftjoin('\Application\Entity\EikenLevel', 'eikenLv', 'WITH', 'aeresult.eikenLevelId = eikenLv.id')
            ->leftjoin('\Application\Entity\SchoolYear', 'schYear', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN, 'orgSY.schoolYearId = schYear.id') 
            ->where('aeresult.organizationId = :org_id')
            ->andWhere('aeresult.year = :year')
            ->setParameter(':org_id', $orgId)
            ->setParameter(':year', $year)
            ->andWhere('aeresult.attendFlag = 1')
            ->andWhere('aeresult.isDelete = 0')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('schYear.id ,aeresult.kumi', 'Asc');
        if ($time !== 'all') {
            $qb->andWhere('aeresult.time = :time ')->setParameter(':time', $time);
        }
        if ($orgClassificationId == 'data') {
            if ($orgSchoolYearId == 'other') {
                $qb->andWhere('orgCl.code IS NULL or schYear.name IS NULL');
            }
        } else {
            
            $qb->andWhere('aeresult.orgClassificationId = :orgClassificationId ')
                ->andWhere('aeresult.orgSchoolYearId = :orgSchoolYearId ')
                ->setParameter(':orgClassificationId', $orgClassificationId)
                ->setParameter(':orgSchoolYearId', $orgSchoolYearId);
        }
        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        return $paginator->getQuery()->getArrayResult();
    }

    public function getLastTimeOfYear($year)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('aeresult.time')
            ->from('\Application\Entity\ActualExamResult', 'aeresult')
            ->where('aeresult.year = :year')
            ->setParameter(':year', $year)
            ->groupBy('aeresult.time')
            ->orderBy('aeresult.time', 'desc')
            ->setFirstResult('0')
            ->setMaxResults('1');
        
        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        return $paginator->getQuery()->getSingleScalarResult();
    }

    /*
     * DucNA17
     * get data Graph
     * @return array
     */
    public function getDataGraphB($orgId, $year, $type)
    {
        $year2 = (int) $year - 2;
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('aeresult.year, COUNT(aeresult.id) as totalPassed,  eikenLv.id, eikenLv.levelName')
            ->from('\Application\Entity\ActualExamResult', 'aeresult')
            ->leftjoin('\Application\Entity\EikenLevel', 'eikenLv', 'WITH', 'aeresult.eikenLevelId = eikenLv.id')
            ->where('aeresult.year between :year2 and :year')
            ->andWhere('aeresult.organizationId = :org_id')
            ->setParameter(':year', $year)
            ->setParameter(':year2', $year2)
            ->setParameter(':org_id', $orgId)
            ->andWhere('aeresult.attendFlag = 1 ')
            ->andWhere('aeresult.isDelete = 0 ')
            ->groupBy('aeresult.year, eikenLv.id')
            ->orderBy('aeresult.year', 'DESC')
            ->addOrderBy('eikenLv.id', 'DESC');
        if($type == 'pass'){
            $qb->andWhere('aeresult.passFlag = 1');
        } 
        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        return $paginator->getQuery()->getArrayResult();
    }

    /*
     * TAIVH
     * get data Detail A
     * @return array
     */
    public function getDataGraphA($orgId, $year, $type, $targetLevel = 0)
    {
        if ($targetLevel == null)
            $targetLevel = 0;

        $year2 = (int) $year - 2;
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('aeresult.year, COUNT(aeresult.id) as totalPassed,  eikenLv.id, eikenLv.levelName')
            ->from('\Application\Entity\ActualExamResult', 'aeresult')
            ->leftjoin('\Application\Entity\EikenLevel', 'eikenLv', 'WITH', 'aeresult.eikenLevelId = eikenLv.id')
            ->where('aeresult.year between :year2 and :year')
            ->andWhere('aeresult.organizationId = :org_id')
            ->andWhere('aeresult.eikenLevelId <= :eikenLevelId')
            ->setParameter(':year2', $year2)
            ->setParameter(':year', $year)
            ->setParameter(':org_id', $orgId)
            ->setParameter(':eikenLevelId', $targetLevel)
            ->andWhere('aeresult.attendFlag = 1')
            ->andWhere('aeresult.isDelete = 0')
            ->groupBy('aeresult.year, eikenLv.id')
            ->orderBy('aeresult.year', 'DESC')
            ->addOrderBy('eikenLv.id', 'DESC');
        if($type == 'pass'){
            $qb->andWhere('aeresult.passFlag = 1');
        }
        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        return $paginator->getQuery()->getArrayResult();
    }

    /*
     * DucNA17
     * get data of Class by OrgSchoolYear and OrgClassification
     */
    public function getDetailClassB($orgId, $year, $time)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('aeresult.year, aeresult.time, COUNT(aeresult.id), aeresult.orgClassificationId, aeresult.orgSchoolYearId, orgCl.code, schYear.name')
            ->from('\Application\Entity\ActualExamResult', 'aeresult')
            ->leftjoin('\Application\Entity\OrganizationClassification', 'orgCl', 'WITH', 'aeresult.orgClassificationId = orgCl.id')
            ->leftjoin('\Application\Entity\OrgSchoolYear', 'orgSY', 'WITH', 'aeresult.orgSchoolYearId = orgSY.id')
            ->leftjoin('\Application\Entity\SchoolYear', 'schYear', 'WITH', 'orgSY.schoolYearId = schYear.id')
            ->where('aeresult.year = :year')
            ->andWhere('aeresult.organizationId = :org_id')
            ->setParameter(':year', $year)
            ->setParameter(':org_id', $orgId)
            ->andWhere('aeresult.attendFlag = 1')
            ->andWhere('aeresult.isDelete = 0')
            ->andWhere('aeresult.passFlag = 1')    
            ->groupBy('aeresult.orgClassificationId, aeresult.orgSchoolYearId')
            ->orderBy('aeresult.orgClassificationId', 'ASC');
        if ($time !== 'all') {
            $qb->andWhere('aeresult.time = :time ')->setParameter(':time', $time);
        }
        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        return $paginator->getQuery()->getArrayResult();
    }

    /*
     * TAIVH
     * Fix for homepage detail a
     * get data of Class by OrgSchoolYear and OrgClassification
     */
    public function getDetailClassA($orgId, $year, $time, $targetLevel)
    {
        if ($targetLevel == null)
            $targetLevel = 0;

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('aeresult.year, aeresult.time, COUNT(aeresult.id), aeresult.orgClassificationId, aeresult.orgSchoolYearId, orgCl.code, schYear.name')
            ->from('\Application\Entity\ActualExamResult', 'aeresult')
            ->leftjoin('\Application\Entity\OrganizationClassification', 'orgCl', 'WITH', 'aeresult.orgClassificationId = orgCl.id')
            ->leftjoin('\Application\Entity\OrgSchoolYear', 'orgSY', 'WITH', 'aeresult.orgSchoolYearId = orgSY.id')
            ->leftjoin('\Application\Entity\SchoolYear', 'schYear', 'WITH', 'orgSY.schoolYearId = schYear.id')            
            ->where('aeresult.organizationId = :org_id')
            ->andWhere('aeresult.year = :year')
            ->andWhere('aeresult.time = :time')           
            ->andWhere('aeresult.eikenLevelId <= :eikenLevelId')               
            ->setParameter(':org_id', $orgId)
            ->setParameter(':year', $year)
            ->setParameter(':time', $time)
            ->setParameter(':eikenLevelId', $targetLevel)
            ->andWhere('aeresult.attendFlag = 1')
            ->andWhere('aeresult.isDelete = 0')
            ->andWhere('aeresult.passFlag = 1')
            ->groupBy('aeresult.orgClassificationId, aeresult.orgSchoolYearId')
            ->orderBy('aeresult.orgClassificationId', 'ASC');
        
        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        return $paginator->getQuery()->getArrayResult();
    }

    /*
     * DucNA17
     * get data exam detail table
     */
    public function getTableByClassB($orgClassificationId = null, $orgSchoolYearId = null, $orgId, $year, $time, $limit, $offset)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('aeresult.year, aeresult.time, aeresult.kumi, aeresult.studentCode,
            aeresult.studentName,aeresult.eikenLevelId, aeresult.passFlag, aeresult.failType1, aeresult.failType2,
            aeresult.orgClassificationId, orgCl.code, aeresult.orgSchoolYearId,
            orgSY.displayName,schYear.id, schYear.name,  eikenLv.levelName')
            ->from('\Application\Entity\ActualExamResult', 'aeresult')
            ->leftjoin('\Application\Entity\OrganizationClassification', 'orgCl', 'WITH', 'aeresult.orgClassificationId = orgCl.id')
            ->leftjoin('\Application\Entity\OrgSchoolYear', 'orgSY', 'WITH', 'aeresult.orgSchoolYearId = orgSY.id')
            ->leftjoin('\Application\Entity\EikenLevel', 'eikenLv', 'WITH', 'aeresult.eikenLevelId = eikenLv.id')
            ->leftjoin('\Application\Entity\SchoolYear', 'schYear', 'WITH', 'orgSY.schoolYearId = schYear.id')
            ->where('aeresult.organizationId = :org_id')
            ->andWhere('aeresult.year = :year')  
            ->setParameter(':org_id', $orgId)
            ->setParameter(':year', $year)
            ->andWhere('aeresult.attendFlag = 1')
            ->andWhere('aeresult.passFlag = 1')
            ->andWhere('aeresult.isDelete = 0')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('schYear.id ,aeresult.kumi', 'Asc');
        if ($time !== 'all') {
            $qb->andWhere('aeresult.time = :time ')->setParameter(':time', $time);
        }
        if ($orgClassificationId == 'data') {
            if ($orgSchoolYearId == 'other') {
                $qb->andWhere('orgCl.code IS NULL OR schYear.name IS NULL');
            }
        } else {            
            $qb->andWhere('aeresult.orgClassificationId = :orgClassificationId ')
                ->andWhere('aeresult.orgSchoolYearId = :orgSchoolYearId ')
                ->setParameter(':orgClassificationId', $orgClassificationId)
                ->setParameter(':orgSchoolYearId', $orgSchoolYearId);
        }
        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        return $paginator->getQuery()->getArrayResult();
    }

    /*
     * TAIVH
     * FIX for homepage detail a
     * get data exam detail table
     */
    public function getTableByClassA($orgClassificationId = null, $orgSchoolYearId = null, $orgId, $year, $time, $targetLevel, $limit, $offset)
    {
        if ($targetLevel == null)
            $targetLevel = 0;
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('aeresult.year, aeresult.time, aeresult.kumi, aeresult.studentCode,
            aeresult.studentName,aeresult.eikenLevelId, aeresult.passFlag, aeresult.failType1, aeresult.failType2,
            aeresult.orgClassificationId, orgCl.code, aeresult.orgSchoolYearId,
            orgSY.displayName,schYear.id, schYear.name,  eikenLv.levelName')
            ->from('\Application\Entity\ActualExamResult', 'aeresult')
            ->leftjoin('\Application\Entity\OrganizationClassification', 'orgCl', 'WITH', 'aeresult.orgClassificationId = orgCl.id')
            ->leftjoin('\Application\Entity\OrgSchoolYear', 'orgSY', 'WITH', 'aeresult.orgSchoolYearId = orgSY.id')
            ->leftjoin('\Application\Entity\EikenLevel', 'eikenLv', 'WITH', 'aeresult.eikenLevelId = eikenLv.id')
            ->leftjoin('\Application\Entity\SchoolYear', 'schYear', 'WITH', 'orgSY.schoolYearId = schYear.id')
            ->where('aeresult.organizationId = :org_id')
            ->andWhere('aeresult.year = :year')
            ->andWhere('aeresult.time = :time ')
            ->andWhere('aeresult.eikenLevelId <= :eikenLevelId')
            ->setParameter(':org_id', $orgId)
            ->setParameter(':year', $year)
            ->setParameter(':time', $time)
            ->setParameter(':eikenLevelId', $targetLevel)
            ->andWhere('aeresult.attendFlag = 1')
            ->andWhere('aeresult.passFlag = 1')         
            ->andWhere('aeresult.isDelete = 0')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('schYear.id ,aeresult.kumi', 'Asc');

        if ($orgClassificationId == 'data') {
            if ($orgSchoolYearId == 'other') {
                $qb->andWhere('orgCl.code IS NULL OR schYear.name IS NULL');
            }
        } else {           
            $qb->andWhere('aeresult.orgClassificationId = :orgClassificationId ')
                ->andWhere('aeresult.orgSchoolYearId = :orgSchoolYearId ')
                ->setParameter(':orgClassificationId', $orgClassificationId)
                ->setParameter(':orgSchoolYearId', $orgSchoolYearId);
        }
        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        return $paginator->getQuery()->getArrayResult();
    }

    /*
     * AnhNT56
     */
    public function getNumberPass($year, $orgId, $orgSchoolYear, $eikenLevel)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(actualExam.id) AS numberPass')
            ->from('\Application\Entity\ActualExamResult', 'actualExam')
            ->where('actualExam.year = :year')
            ->andWhere('actualExam.organization = :orgid')
            ->andWhere('actualExam.orgSchoolYearId = :schoolyear')
            ->andWhere('actualExam.eikenLevel <= :eikenid')
            ->setParameter(':year', $year)
            ->setParameter(':orgid', $orgId)
            ->setParameter(':schoolyear', $orgSchoolYear)
            ->setParameter(':eikenid', $eikenLevel)
            ->andWhere('actualExam.passFlag = 1 AND actualExam.isDelete = 0');
        $query = $qb->getQuery();
        return $query->getSingleResult()['numberPass'];
    }
    
/**
 * @author taivh
 *  Lấy tổng số người có mặt tại trường tất cả các năm. 
 * @param unknown $orgId
 * @param unknown $eikenLevelId
 * @param unknown $year
 * @param unknown $numYear
 * @param unknown $time
 */
    public function getTotalStudentWasInSchool($orgId, $eikenLevelId, $year, $numYear, $time)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();      
               
        $qb->select("actualExamResult.year as Year, count(actualExamResult.id) as Total, schYear.name as Name")
        ->from('\Application\Entity\ActualExamResult', 'actualExamResult')
        ->innerJoin('\Application\Entity\OrgSchoolYear', 'orgSchYear', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN, 'actualExamResult.orgSchoolYearId = orgSchYear.id')
        ->innerJoin('\Application\Entity\SchoolYear', 'schYear', \Doctrine\ORM\Query\Expr\Join::INNER_JOIN, 'orgSchYear.schoolYearId = schYear.id')    
        ->where('actualExamResult.organizationId = :orgId')
        ->andWhere('actualExamResult.year >= :year1')
        ->andWhere('actualExamResult.year <= :year2')
        ->setParameter(':orgId', $orgId)
        ->setParameter(':year1', ($year - $numYear))
        ->setParameter(':year2', $year)
        ->andWhere('orgSchYear.isDelete = 0')
        ->andWhere('schYear.isDelete = 0')
        ->andWhere('actualExamResult.passFlag = 1')
        ->andWhere('actualExamResult.isDelete = 0')
        ->addGroupBy('Year', 'schYear.name')
        ->orderBy('Year', 'ASC')
        ->addOrderBy('Name', 'ASC');
        
        if ($eikenLevelId) {
            $qb->andWhere('actualExamResult.eikenLevelId <= :eikenLevelId')
                ->setParameter(':eikenLevelId', $eikenLevelId);
        }
        
        if($time){
            $qb->andWhere('actualExamResult.time = :time')->setParameter(':time', $time);
        }  
              
        return  $qb->getQuery()->getArrayResult();
    }
}