<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\ApplyEikenLog;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Eiken\Helper\NativePaginator as DTPaginator;
use Doctrine\ORM\Query\ResultSetMapping;

class ApplyEikenLogRepository extends EntityRepository
{
    public function getListApplyEikenLog($searchData)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $startDate = !empty($searchData['fromDate']) ? $searchData['fromDate'] . ' 00:00:00' : '';
        $endDate = !empty($searchData['toDate']) ? $searchData['toDate'] . ' 23:59:59' : '';
        
        $qb->select('eikenlogs')
            ->from('Application\Entity\ApplyEikenLog', 'eikenlogs')
            ->where('eikenlogs.isDelete = 0');
        
        if($searchData['organizationNo'] != ''){
            $qb->andWhere('eikenlogs.organizationNo LIKE :paramOrganizationNo')
                ->setParameter('paramOrganizationNo', '%' . $searchData['organizationNo'] . '%');
        }
        if($searchData['organizationName'] != ''){
            $qb->andWhere('eikenlogs.organizationName LIKE :paramOrganizationName')
                ->setParameter('paramOrganizationName', '%' . $searchData['organizationName'] . '%');
        }
        if($searchData['action'] != ''){
            $qb->andWhere('eikenlogs.action LIKE :paramAction')
                ->setParameter('paramAction', '%' . $searchData['action'] . '%');
        }
        if($searchData['fromDate']) {
            $qb->andWhere('eikenlogs.insertAt >= :paramFromDate');
            $qb->setParameter('paramFromDate', $startDate);
        }
        if($searchData['toDate']) {
            $qb->andWhere('eikenlogs.insertAt <= :paramToDate');
            $qb->setParameter('paramToDate', $endDate);
        }
        
        $qb->orderBy('eikenlogs.insertAt', 'DESC');
        
        $paginator = new DTPaginator($qb, 'DoctrineORMQueryBuilder');
        return $paginator;
    }
    public function getPreviousLogs($orgId, $eikenScheduleId){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $nullRefund = '';
        $qb->select('eikenlogs')
            ->from('Application\Entity\ApplyEikenLog', 'eikenlogs')
            ->where('eikenlogs.isDelete = 0');
        $qb->andWhere('eikenlogs.organizationId = :organizationId')
            ->setParameter('organizationId', $orgId);
        $qb->andWhere('eikenlogs.eikenScheduleId = :eikenScheduleId')
            ->setParameter('eikenScheduleId', $eikenScheduleId);
        $qb->andWhere('eikenlogs.refundDetail != :refundDetail')
            ->setParameter('refundDetail', $nullRefund);
        $qb->orderBy('eikenlogs.insertAt', 'DESC');
        return $qb->getQuery()->getArrayResult();
    }
}