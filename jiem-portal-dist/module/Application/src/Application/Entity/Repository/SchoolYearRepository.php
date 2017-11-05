<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\SchoolYear;

class SchoolYearRepository extends EntityRepository
{
    // SchoolYear Manager
    /**
     * get Paged SchoolYear list
     *
     * @param int $offset            
     *
     * @param int $limit            
     *
     * @return Paginator
     */
    public function getPagedSchoolYearList($limit = 10, $offset = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('schoolyear')
            ->from('\Application\Entity\SchoolYear', 'schoolyear')
            ->orderBy('schoolyear.id', 'DESC')
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
    public function showSchoolYearDetail($id)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $qb->select('schoolyear')
            ->from('\Application\Entity\SchoolYear', 'schoolyear')
            ->where('schoolyear.id = ?1')
            ->setParameter('1', $id);
        
        $query = $qb->getQuery();
        $schoolyear = $query->getSingleResult();
        
        return $schoolyear;
    }
    
    // get list role
    public function ListSchoolYear()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('schoolyear')
            ->from('\Application\Entity\SchoolYear', 'schoolyear')
            ->where('schoolyear.isDelete = 0');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
    // get one object
    public function getSchoolyear($id)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('schoolyear')
            ->from('\Application\Entity\School', 'schoolyear')
            ->where('schoolyear.id = :cond_id')
            ->setParameter('cond_id', $id);
        $query = $qb->getQuery();
        $result = $query->getSingleResult();
        return $result;
    }
    // get all schooyear and orgschoolyear by org
    public function getListUniversalGrade()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('SchoolYear.name','OrgSchoolYear.displayName','OrgSchoolYear.organizationId')
            ->from('\Application\Entity\SchoolYear', 'SchoolYear')
            ->leftJoin('\Application\Entity\OrgSchoolYear', 'OrgSchoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'OrgSchoolYear.schoolYearId = SchoolYear.id AND OrgSchoolYear.isDelete = 0')
            ->where('SchoolYear.isDelete = 0');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
    
    public function getListSchoolYearNotUsed($schoolYearIdsUsed = array()) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('schoolYear.id', 'schoolYear.name')
                ->from('\Application\Entity\SchoolYear', 'schoolYear')
                ->where('schoolYear.isDelete = 0');
        if ($schoolYearIdsUsed) {
            $qb->andWhere($qb->expr()->notIn('schoolYear.id', ':schoolYearIdsUsed'))
                    ->setParameter(':schoolYearIdsUsed', $schoolYearIdsUsed);
        }
        return $qb->getQuery()->getArrayResult();
    }

}