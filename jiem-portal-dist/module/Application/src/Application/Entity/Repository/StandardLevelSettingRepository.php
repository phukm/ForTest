<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\StandardLevelSetting;
use Application\Entity\SchoolYear;

class StandardLevelSettingRepository extends EntityRepository
{
    // StandardLevelSetting Manager
    /**
     * get Paged StandardLevelSetting list
     *
     * @param int $offset            
     *
     * @param int $limit            
     *
     * @return Paginator
     */
    public function getPagedStandardList($limit = 10, $offset = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('standard')
            ->from('\Application\Entity\StandardLevelSetting', 'standard')
            ->orderBy('schoolyear.id', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);
        
        $query = $qb->getQuery();
        
        $paginator = new Paginator($query);
        
        return $paginator;
    }

    /**
     *
     * @param int $id            
     *
     * @return schoolyear
     */
    public function showStandardLevelSettingDetail($id)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('standardlevelsetting')
            ->from('\Application\Entity\StandardLevelSetting', 'standardlevelsetting')
            ->where('standardlevelsetting.id = ?1')
            ->setParameter('1', $id);
        
        $query = $qb->getQuery();
        $standardlevelsetting = $query->getSingleResult();
        
        return $standardlevelsetting;
    }
    
    // get list role
    public function ListSchoolYearByOrg($org_no)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('schoolyear_id')->from('\Application\Entity\OrgSchoolYear', 'schoolyear');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function ListStandardLevelSetting($org_no)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('standardlevel')
            ->from('\Application\Entity\StandardLevelSetting', 'standardlevel')
            ->where('standardlevel.organization = ?1 AND standardlevel.isDelete = 0')
            ->setParameter("1", $org_no);
        
        $query = $qb->getQuery();
        
        $paginator = new Paginator($query);
        
        return $paginator;
    }

    public function checkExist($org_no, $year)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('standardlevel')
            ->from('\Application\Entity\StandardLevelSetting', 'standardlevel')
            ->innerJoin('\Application\Entity\OrgSchoolYear', 'schoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'standardlevel.orgSchoolYear = schoolYear.id')
            ->where('standardlevel.organization = :org')
            ->andWhere('standardlevel.year = :year')
            ->andWhere('standardlevel.isDelete = 0')
            ->orderBy("schoolYear.displayName", "ASC")  
            ->setParameter("org", $org_no)
            ->setParameter("year", $year);
        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    public function searchStandardLevel($request_year, $request_schoolyear, $org_no)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $string_sql = "standardlevel.organization = " . $org_no;
        if (! empty($request_year)) {
            if ($request_schoolyear == 0) {
                $string_sql .= " AND standardlevel.year = '" . $request_year . "' AND standardlevel.isDelete=0";
            } else
                $string_sql .= " AND standardlevel.year = '" . $request_year . "' AND standardlevel.orgSchoolYear = '" . $request_schoolyear . "' AND standardlevel.isDelete=0";
        }
        
        $qb->select('standardlevel')
            ->from('\Application\Entity\StandardLevelSetting', 'standardlevel')
            ->innerJoin('\Application\Entity\OrgSchoolYear', 'schoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'standardlevel.orgSchoolYear = schoolYear.id')
            ->orderBy("schoolYear.schoolYearId", "ASC")
            ->where($string_sql);
        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }
    
    public function deleteDataByOrgAndOrgSchoolYearId($orgId, $orgSchoolYearId) {
        if (empty($orgId) || empty($orgSchoolYearId)) {
            return false;
        }
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->update('\Application\Entity\StandardLevelSetting', 'standardlevel')
                ->set('standardlevel.isDelete', 1)
                ->where('standardlevel.organizationId = :orgId')
                ->andWhere('standardlevel.orgSchoolYearId = :orgSchoolYearId')
                ->setParameter(':orgId', $orgId)
                ->setParameter(':orgSchoolYearId', $orgSchoolYearId);
        return $query->getQuery()->execute();
    }

//     public function getYear($year)
//     {
//         $em = $this->getEntityManager();
//         $qb = $em->createQueryBuilder();
//         $qb->select('year')
//             ->from('\Application\Entity\StandardLevelSetting', 'year')
//             ->where('year.year = ' . $year);
//         $query = $qb->getQuery();
//         $result = $query->getSingleResult();
//         return $result;
//     }
    
    // public function getStandardLevelByYear($year)
    // {
    // $em = $this->getEntityManager();
    // $qb = $em->createQueryBuilder();
    
    // $qb->select('stdlvl')
    // ->from('\Application\Entity\StandardLevelSetting', 'stdlvl')
    // ->where('stdlvl.year >= ' . $year);
    // $query = $qb->getQuery();
    // $result = $query->getResult();
    // return $result;
    // }
}
