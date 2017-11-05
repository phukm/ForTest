<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\OrgTarget;

/**
 *
 * @author TaiVH
 * no using
 *
 */
class OrgTargetRepository extends EntityRepository
{

    public function getListTargetPassByYear($orgId = 0, $year)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select("orgTarget.year, orgTarget.targetPass, orgTarget.eikenLevelId,EikenLevel.levelName")
        ->from('\Application\Entity\OrgTarget', 'orgTarget')
        ->leftJoin('\Application\Entity\EikenLevel', 'EikenLevel', \Doctrine\ORM\Query\Expr\Join::WITH, "orgTarget.eikenLevelId=EikenLevel.id")
        ->where('orgTarget.year >= :yearMin')
        ->andWhere('orgTarget.year <= :yearMax')
        ->andWhere('orgTarget.organizationId = :org')
        ->andWhere('orgTarget.isDelete = 0')
        ->orderBy('orgTarget.year', 'DESC')
        ->setParameter('org', (int)$orgId)
        ->setParameter('yearMax', (int)$year['yearMax'])
        ->setParameter('yearMin', (int)$year['yearMin']);
        return $qb->getQuery()->getArrayResult();
    }

}