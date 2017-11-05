<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class SchoolYearMappingRepository extends EntityRepository
{

    public function getAllDataByArraySchoolYearIds(array $schoolYearIds)
    {
        if (!$schoolYearIds) {
            return array();
        }
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('schoolyear.schoolYearId, schoolyear.schoolYearName, schoolyear.orgCode, schoolyear.schoolYearCode')
            ->from('\Application\Entity\SchoolYearMapping', 'schoolyear')
            ->where($qb->expr()->in('schoolyear.schoolYearId', ':schoolYearIds'))
            ->setParameter(':schoolYearIds', $schoolYearIds)
            ->andWhere('schoolyear.isDelete = 0');

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Function get list schoolYearMapping by OrgNo
     *
     * @param $orgNo
     * @return array
     */
    public function getListOrgSchoolYearNameByOrgNo($orgNo)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('schoolYearMapping.schoolYearId, schoolYearMapping.schoolYearCode,
                        schoolYearMapping.schoolYearName, orgSchYear.id as orgSchoolYearId,
                        CASE
                            WHEN orgSchYear.id IS NOT NULL THEN orgSchYear.displayName
                            ELSE schYear.name
                        END as orgSchoolYearName')
            ->from('\Application\Entity\SchoolYearMapping', 'schoolYearMapping')
            ->leftJoin('\Application\Entity\Organization', 'org', \Doctrine\ORM\Query\Expr\Join::LEFT_JOIN, 'org.organizationCode = schoolYearMapping.orgCode')
            ->leftJoin('\Application\Entity\OrgSchoolYear', 'orgSchYear', \Doctrine\ORM\Query\Expr\Join::LEFT_JOIN, 'orgSchYear.organizationId = org.id AND orgSchYear.schoolYearId = schoolYearMapping.schoolYearId AND orgSchYear.isDelete = 0')
            ->leftJoin('\Application\Entity\SchoolYear', 'schYear', \Doctrine\ORM\Query\Expr\Join::LEFT_JOIN, 'schYear.id = schoolYearMapping.schoolYearId')
            ->where('org.organizationNo = :orgNo')
            ->andWhere('org.isDelete = 0')
            ->andWhere('schoolYearMapping.isDelete = 0')
            ->andWhere('schYear.isDelete = 0')
            ->setParameter(':orgNo', $orgNo)
            ->orderBy('schoolYearMapping.schoolYearCode');

        return $qb->getQuery()->getArrayResult();
    }
}