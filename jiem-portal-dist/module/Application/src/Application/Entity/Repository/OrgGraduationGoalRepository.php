<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\OrgGraduationGoal;

class OrgGraduationGoalRepository extends EntityRepository
{
    /*
     * TuanNv21
     * get rate by orgcode and CityId and Year and schoolyear
     */
    public function getListTargetPassByYear($orgId = 0, $data)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select("OrgGraduation.year, OrgGraduation.targetPass, OrgGraduation.eikenLevelId,OrgGraduation.eikenLevelId,OrgGraduation.orgSchoolYearId,EikenLevel.levelName,OrgGraduation.isGraduationGoal")
        ->from('\Application\Entity\OrgGraduationGoal', 'OrgGraduation')
        ->leftJoin('\Application\Entity\EikenLevel', 'EikenLevel', \Doctrine\ORM\Query\Expr\Join::WITH, "OrgGraduation.eikenLevelId=EikenLevel.id")
        ->where('OrgGraduation.year >= :yearMin')
        ->andWhere('OrgGraduation.year <= :yearMax')
        ->andWhere('OrgGraduation.organizationId = :org')
        ->andWhere('OrgGraduation.isDelete = 0')
        ->orderBy('OrgGraduation.year', 'DESC')
        ->setParameter('org', (int)$orgId)
        ->setParameter('yearMax', (int)$data['yearMax'])
        ->setParameter('yearMin', (int)$data['yearMin']);
        if($data['isGraduationGoal']==0) {
            $query->andWhere('OrgGraduation.isGraduationGoal =1');
        }
        return $query->getQuery()->getArrayResult();
    }
    
    /*
     * ChungDV
     * return count graduation goal
     */
    public function getCountGraduationGoal($orgId, $year, $isGraduationGoal)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('count(og.id)')
           ->from('\Application\Entity\OrgGraduationGoal', 'og')
           ->where(
                $qb->expr()->andX(
                        $qb->expr()->eq('og.organizationId', ':organizationId'),
                        $qb->expr()->gte('og.year', ':year'),
                        $qb->expr()->eq('og.isGraduationGoal', ':isGraduationGoal'),
                        $qb->expr()->eq('og.isDelete', ':isDelete')
                    )
                );

        $qb->setParameters(array(
            'organizationId'    => $orgId,
            'year'              => $year,
            'isGraduationGoal'  => $isGraduationGoal,
            'isDelete'          => 0
        ));
        $count = $qb->getQuery()->getSingleScalarResult();
        
        return $count;
    }
    
    /*
     * @author: MinhTN6
     * get all data org graduation goal of organization and array parameter
     * @param int $orgId
     * @param int $schoolYearId
     * $param $search = array{
            'year' => int,
            'yearFrom' => int,
            'yearTo' => int,
     * }
     * @return array
     */
    public function getListDataByOrgAndArraySearch($orgId, $search=array()){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('graduationGoal.organizationId, graduationGoal.eikenLevelId, eikenLevel.levelName, graduationGoal.year, graduationGoal.targetPass, graduationGoal.orgSchoolYearId, graduationGoal.isGraduationGoal')
            ->from('\Application\Entity\OrgGraduationGoal', 'graduationGoal')
            ->leftJoin('\Application\Entity\EikenLevel', 'eikenLevel', \Doctrine\ORM\Query\Expr\Join::WITH, 'graduationGoal.eikenLevelId = eikenLevel.id')
            ->where('graduationGoal.organizationId = :organizationId')
            ->setParameter(':organizationId', $orgId)
            ->andWhere('graduationGoal.isDelete = 0')
            ->orderBy('graduationGoal.isGraduationGoal', 'DESC')
            ->addOrderBy('graduationGoal.eikenLevelId', 'DESC');
        
        if(!empty($search['year'])){
            $qb->andWhere('graduationGoal.year = :year')->setParameter(':year', intval($search['year']));
        }
        
        if(!empty($search['yearFrom'])){
            $qb->andWhere('graduationGoal.year >= :yearFrom')->setParameter(':yearFrom', intval($search['yearFrom']));
        }
        
        if(!empty($search['yearTo'])){
            $qb->andWhere('graduationGoal.year <= :yearTo')->setParameter(':yearTo', intval($search['yearTo']));
        }
        
        return $qb->getQuery()->getArrayResult();
    }
    
    /**
     * @author taivh
     * @param number $orgId
     * @param number $orgSchoolYearId
     * @param number $year
     * @param number $isGraduationGoal
     */
    public function getTarget($orgId = 0, $orgSchoolYearId = 0, $year = 2010, $isGraduationGoal = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select("OrgGraduation.eikenLevelId, OrgGraduation.targetPass,OrgGraduation.isGraduationGoal, EikenLevel.levelName")
        ->from('\Application\Entity\OrgGraduationGoal', 'OrgGraduation')
        ->leftJoin('\Application\Entity\EikenLevel', 'EikenLevel', \Doctrine\ORM\Query\Expr\Join::WITH, "OrgGraduation.eikenLevelId=EikenLevel.id")
        ->where('OrgGraduation.organizationId = :orgId')        
        ->andWhere('OrgGraduation.year = :year')
        ->andWhere('OrgGraduation.isDelete = 0')
        ->setParameter(':orgId', $orgId)        
        ->setParameter(':year', $year)
        ->orderBy('OrgGraduation.year', 'ASC');
        //->orderBy('OrgGraduation.isGraduationGoal','ASC');
        if($isGraduationGoal != 2)
        {
            $query->andWhere('OrgGraduation.isGraduationGoal = :isGraduationGoal')
            ->setParameter(':isGraduationGoal', $isGraduationGoal);
        }
        if($orgSchoolYearId != 0)
        {
            $query->andWhere('OrgGraduation.orgSchoolYearId = :orgSchoolYearId')
            ->setParameter(':orgSchoolYearId', $orgSchoolYearId);
        }
        return $query->getQuery()->getArrayResult();
    }
}
