<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\OrgSchoolYear;
use Zend\Db\Sql\Where;
use Doctrine\ORM\Query\ResultSetMapping;

class OrgSchoolYearRepository extends EntityRepository
{
    // OrgSchoolYear Manager
    /**
     * get Paged OrgSchoolYear list
     *
     * @param int $offset
     *
     * @param int $limit
     *
     * @return Paginator
     */
    public function getPagedOrgSchoolYearList($organizationId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('orgschoolyear')
            ->from('\Application\Entity\OrgSchoolYear', 'orgschoolyear')
            ->innerJoin('\Application\Entity\SchoolYear', 'sy', \Doctrine\ORM\Query\Expr\Join::WITH, 'orgschoolyear.schoolYear = sy.id')
            ->where('orgschoolyear.organizationId = :organizationId')
            ->setParameter(':organizationId', intval($organizationId))
            ->andWhere('orgschoolyear.isDelete = 0')
            ->orderBy('orgschoolyear.schoolYearId', 'ASC');
        $query = $qb->getQuery();
        $orgschoolyear = $query->getResult();
        return $orgschoolyear;
    }

    /**
     *
     * @param int $id
     *
     * @return orgschoolyear
     */
    public function showOrgSchoolYearDetail($id, $organizationId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('orgschoolyear')
            ->from('\Application\Entity\OrgSchoolYear', 'orgschoolyear')
            ->where('orgschoolyear.organizationId = :organizationId')
            ->andWhere('orgschoolyear.id = :id')
            ->setParameter(':organizationId', $organizationId)
            ->setParameter(':id', $id)
            ->andWhere('orgschoolyear.isDelete = 0');

        $query = $qb->getQuery();
        $orgschoolyear = $query->getSingleResult();

        return $orgschoolyear;
    }

    public function ListSchoolYear($organizationId)
    {
        return $this->listSchoolYearName($organizationId, 'ASC');
    }

    public function listSchoolYearName($organizationId, $sort = 'DESC')
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('s')
            ->from('\Application\Entity\OrgSchoolYear', 's')
            ->where('s.organizationId = :organizationId')
            ->andWhere('s.isDelete = 0')
            ->setParameter(':organizationId', $organizationId)
            ->orderBy('s.schoolYearId', $sort);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Return OrgShoolyear with key = OrgShoolYearId
     * Cannot use the above function because Other guys is fixed Key = 0 for maximum Id
     * @param unknown $org_id
     * @param unknown $year
     * @return unknown
     */
    public function getOrgSchoolYears($organizationId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('schoolYear.schoolYearId')
        ->from('\Application\Entity\OrgSchoolYear', 'schoolYear', 'schoolYear.schoolYearId')
        ->where('schoolYear.organizationId = :organizationId')
        ->setParameter(':organizationId', $organizationId)
        ->andWhere('schoolYear.isDelete = 0')
        ->orderBy('schoolYear.schoolYearId','ASC');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    /**
     * Return OrgShoolyear with key = Id
     * Cannot use the above function because Other guys is fixed Key = 0 for maximum Id
     * @param unknown $org_id
     * @param unknown $year
     * @return unknown
     */
    public function getOrgSchoolYearIdList($organizationId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('schoolYear.id')
            ->from('\Application\Entity\OrgSchoolYear', 'schoolYear')
            ->where('schoolYear.organizationId = :organizationId')
            ->setParameter(':organizationId', $organizationId)
            ->andWhere('schoolYear.isDelete = 0')
            ->orderBy('schoolYear.schoolYearId','ASC');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }

    public function ListSchoolYearByYear($org_id, $year)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('schoolYear.id,schoolYear.displayName')
            ->from('\Application\Entity\StandardLevelSetting', 'standard')
            ->innerJoin('\Application\Entity\OrgSchoolYear', 'schoolYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'standard.orgSchoolYear = schoolYear.id')
            ->where('standard.organizationId = :organizationId')
            ->andWhere('standard.year = :year')
            ->setParameter(':organizationId', $org_id)
            ->setParameter(':year', $year)
            ->andWhere('standard.isDelete = 0')
            ->orderBy('schoolYear.schoolYearId');
        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    /**
     * Check whether given criteria is already exists on database
     * 
     * @param int $id
     * @param array $criteria ([FieldName] => FieldValue)
     * @return boolean
     */
    public function checkDuplicate($id, $criteria = array())
    {
        if(empty($criteria)) return false;
        
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qe = $qb->expr();
        $where = $qe->andX();
        
        $qb->select('COUNT(orgSchoolYear.id) AS found')
            ->from('\Application\Entity\OrgSchoolYear', 'orgSchoolYear');
        
        if($id)
            $where->add($qe->neq('orgSchoolYear.id', ':orgSchoolYearId'));
        
        $fields = array_keys($criteria);
        foreach ($fields as $field)
            $where->add($qe->eq('orgSchoolYear.' . $field, ':' . $field));
        
        $qb->where($where);

        if($id)
            $qb->setParameter('orgSchoolYearId', $id);
        
        foreach ($fields as $field)
            $qb->setParameter($field, $criteria[$field]);
        
        
        $query = $qb->setMaxResults(1)->getQuery();
        $result = $query->getOneOrNullResult(2);
        
        return $result['found'] > 0;
    }
    
    /**
     * @author taivh
     * @param unknown $orgId
     */
    public function getOrgSchoolYearName($orgId) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $query = $qb->select("schYear.name, schYear.id as schoolYearId, orgSchYear.id as orgSchoolYearId, orgSchYear.displayName")
                    ->from('\Application\Entity\OrgSchoolYear', 'orgSchYear')
                    ->leftJoin('\Application\Entity\SchoolYear', 'schYear', \Doctrine\ORM\Query\Expr\Join::WITH, 'orgSchYear.schoolYearId = schYear.id')
                    ->where('orgSchYear.organizationId = :organizationId')
                    ->setParameter(':organizationId', $orgId)
                    ->andWhere('orgSchYear.isDelete = 0')
                    ->andWhere('schYear.isDelete = 0')
                    ->orderBy('schYear.name', ' DESC');
        return $query->getQuery()->getArrayResult();
    }
    
    public function getGradeByDisplayName($orgId,$displayName,$id = 0) {
        $em = $this->getEntityManager();

        $sql = "SELECT id ,displayName  "
                . "FROM OrgSchoolYear "
                . "WHERE organizationId = ".$orgId." "
                . "AND displayName = BINARY'".$displayName."'"
                . " AND isDelete = 0 AND id !=".$id;
        
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('\Application\Entity\OrgSchoolYear', 'orgSchoolYear');
        $rsm->addFieldResult('orgSchoolYear', 'id', 'id');
        $rsm->addFieldResult('orgSchoolYear', 'displayName', 'displayName');

        $query = $em->createNativeQuery($sql, $rsm);
        return $query->getArrayResult();

    }

}