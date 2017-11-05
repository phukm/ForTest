<?php

namespace Application\Entity\Repository;

use Application\Entity\Year;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ClassJRepository extends EntityRepository {

    const NOT_DELETE_VALUE = 0;

    // get List all class and padding
    public function getPagedClassList($limit = 20, $offset = 0, $year = false, $schoolyear = false, $organizationId = false) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('classj')
                ->from('\Application\Entity\Classj', 'classj')
                ->leftJoin('\Application\Entity\OrgSchoolYear', 'orgschool', \Doctrine\ORM\Query\Expr\Join::WITH, 'classj.orgSchoolYear = orgschool.id')
                ->where('classj.organizationId = :organizationId')
                ->setParameter(':organizationId', $organizationId)
                ->andWhere('classj.isDelete = 0')
                ->orderBy('classj.year', 'DESC')
                ->addOrderBy('orgschool.schoolYearId', 'ASC')
                ->addOrderBy('classj.className', 'ASC')
                ->setFirstResult($offset)
                ->setMaxResults($limit);

        if (empty($year) && empty($schoolyear)) {
            $qb->andWhere('classj.year = :year')->setParameter(':year', date('Y'));
        } else if ($schoolyear && $year) {
            $qb->andWhere('classj.year = :year')
                    ->andWhere('classj.orgSchoolYear = :orgSchoolYear')
                    ->setParameter(':year', intval($year))
                    ->setParameter(':orgSchoolYear', intval($schoolyear));
        } else {
            if ($year && empty($schoolyear)) {
                $qb->andWhere('classj.year = :year')->setParameter(':year', intval($year));
            }
            if ($schoolyear && empty($year)) {
                $qb->andWhere('classj.orgSchoolYear = :orgSchoolYear')->setParameter(':orgSchoolYear', intval($schoolyear));
            }
        }
        $query = $qb->getQuery();
        $paginator = new Paginator($query, $fetchJoinCollection = false);
        return $paginator;
    }

    public function getDetail($id = 0, $organizationId = false) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('classj')
                ->from('\Application\Entity\Classj', 'classj')
                ->where('classj.id = :classId')
                ->andWhere('classj.organizationId = :organizationId')
                ->setParameter(':classId', intval($id))
                ->setParameter(':organizationId', intval($organizationId))
                ->andWhere('classj.isDelete = 0');
        $query = $qb->getQuery();
        $classj = $query->getOneOrNullResult();
        return $classj;
    }

    /*
     * Get CLASS
     */

    public function getListClass() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('classname')->from('\Application\Entity\ClassJ', 'classname');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
    
    public function getListOrgClass($orgId) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('classj')
        ->from('\Application\Entity\ClassJ', 'classj')
        ->where('classj.isDelete = 0')
        ->andWhere('classj.organizationId = :organizationId')
        ->setParameter('organizationId', $orgId);
        
        return $qb->getQuery()->getArrayResult();
    }

    public function getListClassByOrg($org) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('cl.className,cl.year,orgschoolyear.displayName as schoolyearName')
                ->from('\Application\Entity\ClassJ', 'cl')
                ->leftJoin('\Application\Entity\OrgSchoolYear', 'orgschoolyear', \Doctrine\ORM\Query\Expr\Join::WITH, 'cl.orgSchoolYearId = orgschoolyear.id')
                ->where('cl.organizationId = :organizationId')
                ->setParameter(':organizationId', intval($org))
                ->andWhere('cl.isDelete = 0');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function getListClassBySchoolYear($schoolyearId, $yearId, $orgId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c.id,c.className')
            ->from('\Application\Entity\Classj', 'c')
            ->where('c.isDelete = 0')
            ->andWhere('c.orgSchoolYearId = :orgSchoolYearId')
            ->andWhere('c.organizationId = :orgId')
            ->andWhere('c.year = :year')
            ->setParameter(':orgId', $orgId)
            ->setParameter(':year', $yearId)
            ->setParameter(':orgSchoolYearId', $schoolyearId);

        return $qb->getQuery()->getArrayResult();
    }

    public function getListClassBySchoolYearAndYear($year, $schoolyear, $org) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('classj.id,classj.className')
                ->from('\Application\Entity\Classj', 'classj')
                ->where('classj.year = :year')
                ->andWhere('classj.orgSchoolYearId = :orgSchoolYearId')
                ->andWhere('classj.organizationId = :organizationId')
                ->setParameter(':year', intval($year))
                ->setParameter(':orgSchoolYearId', intval($schoolyear))
                ->setParameter(':organizationId', intval($org))
                ->andWhere('classj.isDelete = 0')
                ->orderBy('classj.className', 'ASC');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function getListClassBySchoolYearAndYearAjax($year, $schoolyear, $org) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('classj.id,classj.className')
                ->from('\Application\Entity\Classj', 'classj')
                ->where('classj.organizationId = :organizationId')
                ->setParameter(':organizationId', intval($org))
                ->andWhere('classj.isDelete = 0')
                ->orderBy('classj.className', 'ASC');

        if (!empty($schoolyear)) {
            $qb->andWhere('classj.orgSchoolYearId = :orgSchoolYearId')
                    ->setParameter(':orgSchoolYearId', intval($schoolyear));
        }

        if (!empty($year)) {
            $qb->andWhere('classj.year = :year')
                    ->setParameter(':year', intval($year));
        }

        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
    
    public function getHistoryClassBySchoolYearAndYearAjax($year, $schoolyear, $org) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('classj.id,classj.className')
        ->from('\Application\Entity\Classj', 'classj')
        ->where('classj.organizationId = :organizationId')
        ->innerJoin('\Application\Entity\OrgSchoolYear', 'schoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'classj.orgSchoolYear = schoolYear.id')
        ->setParameter(':organizationId', intval($org))
        ->andWhere('classj.isDelete = 0')
        ->orderBy('classj.className', 'ASC');
    
        if (!empty($schoolyear)) {
            $qb->andWhere('schoolYear.displayName = :orgSchoolYearName')
            ->setParameter(':orgSchoolYearName', $schoolyear);
        }
    
        if (!empty($year)) {
            $qb->andWhere('classj.year = :year')
            ->setParameter(':year', intval($year));
        }
    
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function getListClassBySchoolYearAndBetweenYear($orgId, $orgSchoolYearId, $yearFrom, $yearTo) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('classj.id,classj.className')
                ->from('\Application\Entity\Classj', 'classj')
                ->where('classj.year >= :yearFrom')
                ->andWhere('classj.year <= :yearTo')
                ->andWhere('classj.orgSchoolYearId = :orgSchoolYearId')
                ->andWhere('classj.organizationId = :organizationId')
                ->setParameter(':yearFrom', intval($yearFrom))
                ->setParameter(':yearTo', intval($yearTo))
                ->setParameter(':orgSchoolYearId', intval($orgSchoolYearId))
                ->setParameter(':organizationId', intval($orgId))
                ->andWhere('classj.isDelete = 0')
                ->orderBy('classj.className', 'ASC');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function getCheckDuplicate($data, $org) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $classname = trim($data['classname']);

        $classname = str_replace(array("'", '"'), array("", ''), $classname);
        $qb->select('classj')
                ->from('\Application\Entity\Classj', 'classj')
                ->where('classj.organizationId = :organizationId')
                ->andWhere('classj.orgSchoolYearId = :orgSchoolYearId')
                ->andWhere('classj.year = :year')
                ->andWhere('classj.className = :className')
                ->setParameter(':organizationId', intval($org))
                ->setParameter(':orgSchoolYearId', intval($data['year']))
                ->setParameter(':year', intval($data['school_year']))
                ->setParameter(':className', $classname)
                ->andWhere($qb->expr()->neq('classj.id', intval($data['id'])))
                ->andWhere('classj.isDelete = 0');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();

        return count($result) > 0;
    }

    /*
     * Get List Class in RecommendLevel Screen
     */

    public function getClassRcm($org) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('class')
                ->from('\Application\Entity\Classj', 'class')
                ->where('class.organization = :orgId')
                ->setParameter(':orgId', (int) $org);

        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    /*
     * Get List School year
     */

    public function ListSchoolYearByYear($org_id, $year) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('distinct schoolYear.id,schoolYear.displayName, schoolYear.schoolYearId')
                ->from('\Application\Entity\ClassJ', 'class')
                ->innerJoin('\Application\Entity\OrgSchoolYear', 'schoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'class.orgSchoolYear = schoolYear.id')
                ->where('class.organizationId = :organizationId')
                ->andWhere('class.year = :year')
                ->setParameter(':organizationId', intval($org_id))
                ->setParameter(':year', intval($year))
                ->andWhere('class.isDelete = 0')
                ->orderBy('schoolYear.schoolYearId', 'ASC');
        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    /*
     * DucNA17
     * get total student of organization by year
     * @return array
     */

    public function getTotalStudentByYear($year, $orgId) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $year = (int) $year;
        $year2 = (int) $year - 2;
        $qb->select('classJ.year, Sum(classJ.numberOfStudent) as totalStudent')
                ->from('\Application\Entity\ClassJ', 'classJ')
                ->where('classJ.year between :year2 and :year')
                ->andWhere('classJ.organizationId = :organizationId')
                ->setParameter(':year', $year)
                ->setParameter(':year2', $year2)
                ->setParameter(':organizationId', $orgId)
                ->orderBy('classJ.year', 'DESC')
                ->groupBy('classJ.year');
        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        return $paginator->getQuery()->getArrayResult();
    }

    public function getTotalStudentByYearAndSchoolYear($orgId, $year, $orgSchoolYear) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('SUM(class.numberOfStudent) AS totalStudent')
                ->from('\Application\Entity\ClassJ', 'class')
                ->where('class.year = :year')
                ->andWhere('class.organization = :orgid')
                ->andWhere('class.orgSchoolYearId = :schoolyear')
                ->setParameter(':year', $year)
                ->setParameter(':orgid', $orgId)
                ->setParameter(':schoolyear', $orgSchoolYear)
                ->andWhere('class.isDelete = 0');
        $query = $qb->getQuery();
        return $query->getSingleResult()['totalStudent'];
    }

    public function getDataClassByClassIds($classIds) {
        if (!$classIds)
            return false;

        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('classJ.id, classJ.className, classJ.numberOfStudent')
                ->from('\Application\Entity\ClassJ', 'classJ')
                ->where($qb->expr()->in('classJ.id', ':classIds'))
                ->setParameter(':classIds', $classIds);
        $data = $qb->getQuery()->getArrayResult();

        foreach ($data as $value) {
            $result[$value['id']] = $value;
        }
        return !empty($result) ? $result : false;
    }

    /**
     * @author taivh
     * @param number $orgId
     * @param number $orgSchoolYearId
     * @param number $year
     * @return number
     */
    public function getNumberOfStudent($orgId = 0, $orgSchoolYearId = 0, $year = 2010) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("SUM(classj.numberOfStudent) as num")
                ->from('\Application\Entity\ClassJ', 'classj')
                ->where('classj.year = :year')
                ->andWhere('classj.isDelete = 0')
                ->andWhere('classj.organizationId = :orgId')
                ->andWhere('classj.orgSchoolYearId = :orgSchoolYearId')
                ->setParameter(':year', $year)
                ->setParameter(':orgId', $orgId)
                ->setParameter(':orgSchoolYearId', $orgSchoolYearId);
        $query = $qb->getQuery();
        try{
            return $query->getSingleScalarResult();
        } catch (\Exception $e){
            return 0;
        }
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
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('class')
                ->from('\Application\Entity\ClassJ', 'class')
                ->where('class.year=:year')
                ->andWhere('class.isDelete=' . self::NOT_DELETE_VALUE)
                ->andWhere('class.orgSchoolYearId=:orgSchoolYearId')
                ->setParameter(':year', $year)
                ->setParameter(':orgSchoolYearId', $orgSchoolYearId);
        $query = $qb->getQuery();
        $classs = $query->getResult();
        return $classs;
    }

    /**
     * @author minhbn1 <minhbn1@fsoft.com.vn>
     * 
     * get sum pupil of Class, OrgSchoolYear...
     * 
     * @param array $options('year'=>$year,'organizationId'=>$organizationId,'orgSchoolYearId'=>$orgSchoolYearId,'classId'=>$classId)
     * @return number
     */
    function sumPupilWithOptions($options = array()) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('SUM(class.numberOfStudent)')
                ->from('\Application\Entity\ClassJ', 'class')
                ->where('class.isDelete=:isDelete');
        $qb->setParameter(':isDelete', self::NOT_DELETE_VALUE);

        if (isset($options['year'])) {
            $qb->andWhere('class.year = :year');
            $qb->setParameter(':year', $options['year']);
        }
        if (isset($options['organizationId'])) {
            $qb->andWhere('class.organization = :orgid')
                    ->setParameter(':orgid', $options['organizationId']);
        }
        if (isset($options['orgSchoolYearId'])) {
            $qb->andWhere('class.orgSchoolYearId = :orgSchoolYearId')
                    ->setParameter(':orgSchoolYearId', $options['orgSchoolYearId']);
        }
        if (isset($options['classId'])) {
            $qb->andWhere('class.id=:classId')
                    ->setParameter(':classId', $options['classId']);
        }
        $query = $qb->getQuery();
        $sum = $query->getSingleScalarResult();
        return (int) $sum;
    }

    /**
     * 
     * @param type $organizationId
     * @param type $year
     * @return type
     */
    function getAllClassIdOfOrgByYear($organizationId,$year){
        $organizationId = (int)$organizationId;
        $year = (int) $year;
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('class')
                ->from('\Application\Entity\ClassJ', 'class')
                ->where('class.year=:year')
                ->andWhere('class.isDelete=' . self::NOT_DELETE_VALUE)
                ->andWhere('class.organizationId = :orgId')
                ->setParameter(':year', $year)
                ->setParameter(':orgId', $organizationId);
        $data = $qb->getQuery()->getArrayResult();
        //
        $classes = array();
        //
        foreach ($data as $cla){
            $classes[$cla['orgSchoolYearId']][$cla['id']] = $cla;
        }
        return $classes;
    }
     // get list class by org id
    public function getListClassByOrgAndGrade($id)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('SchoolYear.name','OrgSchoolYear.displayName','ClassJ.className', 'ClassJ.year')
            ->from('\Application\Entity\ClassJ', 'ClassJ')
            ->innerJoin('\Application\Entity\OrgSchoolYear', 'OrgSchoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'OrgSchoolYear.id = ClassJ.orgSchoolYearId')
            ->innerJoin('\Application\Entity\SchoolYear', 'SchoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'OrgSchoolYear.schoolYearId = SchoolYear.id')
            ->where('ClassJ.organizationId=:idOrg')
            ->andwhere('SchoolYear.isDelete = 0')
            ->andwhere('OrgSchoolYear.isDelete = 0')
            ->andwhere('ClassJ.isDelete = 0')
            ->setParameter(':idOrg', (int)$id);
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function getListSchoolAndClassId($listClassId, $organizationId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('c.id,c.className as class,c.orgSchoolYearId,s.displayName as orgSchoolYear, c.year')
            ->from('\Application\Entity\ClassJ', 'c')
            ->leftJoin('\Application\Entity\OrgSchoolYear', 's', \Doctrine\ORM\Query\Expr\Join::WITH, 's.id = c.orgSchoolYearId')
            ->where('c.organizationId = :organizationId')
            ->andWhere('c.className IN (:listClassId)')
            ->setParameter(':listClassId', $listClassId)
            ->setParameter(':organizationId', $organizationId)
            ->andWhere('c.isDelete = 0');

        return $qb->getQuery()->getArrayResult();
    }

    public function updateIsDeleteClass($organizationId, $gradeId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->update('\Application\Entity\ClassJ', 'class')
            ->set('class.isDelete', 1)
            ->where('class.orgSchoolYearId = :gradeId')
            ->andWhere('class.organizationId = :organizationId')
            ->setParameter('gradeId', $gradeId)
            ->setParameter(':organizationId', $organizationId);

        return $query->getQuery()->execute();
    }
    
    public function getListClassNameAjax($year, $schoolyear, $org) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('classj.className')
                ->from('\Application\Entity\Classj', 'classj')
                ->where('classj.organizationId = :organizationId')
                ->setParameter(':organizationId', intval($org))
                ->andWhere('classj.isDelete = 0')
                ->distinct()
                ->orderBy('classj.className', 'ASC');

        if (!empty($schoolyear)) {
            $qb->andWhere('classj.orgSchoolYearId = :orgSchoolYearId')
                    ->setParameter(':orgSchoolYearId', intval($schoolyear));
        }

        if (!empty($year)) {
            $qb->andWhere('classj.year = :year')
                    ->setParameter(':year', intval($year));
        }

        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
    
    /*
     * Get List School year
     */

    public function listClassByYear($orgId, $years) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('distinct schoolYear.id as orgSchoolYearId,schoolYear.displayName as orgSchoolYearName , class.className, class.year , class.id as idClass')
                ->from('\Application\Entity\ClassJ', 'class')
                ->innerJoin('\Application\Entity\OrgSchoolYear', 'schoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'class.orgSchoolYear = schoolYear.id')
                ->where('class.organizationId = :organizationId')
                ->setParameter(':organizationId', intval($orgId))
                ->andWhere('class.isDelete = 0')
                ->orderBy('schoolYear.schoolYearId', 'ASC');
        if($years){
            $qb->andWhere($qb->expr()->in('class.year', implode(',', $years)));
        }
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function getListClassYearByOrgSchoolYear($orgSchoolYearId) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('distinct class.year')
            ->from('\Application\Entity\ClassJ', 'class')
            ->where('class.orgSchoolYearId = :orgSchoolYearId')
            ->andWhere('class.isDelete = :isDelete')
            ->setParameters(array(
                                'orgSchoolYearId' => $orgSchoolYearId,
                                'isDelete'        => self::NOT_DELETE_VALUE,
                            ));
        $query = $qb->getQuery();
        $result = $query->getResult();
        return array_column($result ,'year');
    }
}
