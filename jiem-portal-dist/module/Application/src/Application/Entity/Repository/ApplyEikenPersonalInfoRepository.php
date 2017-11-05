<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\ApplyEikenPersonalInfo;
/**
 * @author LangDD
 *
 */
class ApplyEikenPersonalInfoRepository extends  EntityRepository
{
    /**
     * @param unknown $orgId
     * @param number $limit
     * @param number $offset
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getPagedEikenApplyPersonalInfo($orgId, $limit = 10, $offset = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('eikenpersonal')
            ->from('\Application\Entity\ApplyEikenPersonalInfo', 'eikenpersonal')
            ->join('eikenpersonal.organization', 'organization')
            ->where('organization.id = '.(int) $orgId)
            ->andwhere('eikenpersonal.isDelete = 0')
            ->orderBy('eikenpersonal.id', 'DESC');
    
        $query = $qb->getQuery();
        $paginator = new Paginator($query);
        $paginator
                ->getQuery()
                ->setFirstResult($offset)
                ->setMaxResults($limit);
        return $paginator;
    }
    
    public function getAppEikenPersonalInfo() {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('applyeikenpersonalinfo')
        ->from('\Application\Entity\ApplyEikenPersonalInfo','applyeikenpersonalinfo');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();
        return $result;
    }
    
    public function getInforStudent($pupilId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('applyEikenPersonalInfo')
            ->from('\Application\Entity\ApplyEikenPersonalInfo', 'applyEikenPersonalInfo')
            ->where('applyEikenPersonalInfo.pupilId = '.(int) $pupilId)
            ->andwhere('applyEikenPersonalInfo.eikenId IS NOT null')
            ->andwhere('applyEikenPersonalInfo.isDelete = 0')
            ->orderBy('applyEikenPersonalInfo.id', 'DESC')
            ->setMaxResults(1);
        return $qb->getQuery()->getOneOrNullResult();
    }
    
}