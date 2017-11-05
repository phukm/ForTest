<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class RetrieveBillingInfoRepository extends EntityRepository
{
    
    public function getDataOrderCreditInDateBySiteCode($shipDate, $siteCode, $getAllOrder = 0, $isDelete=0){
        $endDate = !empty($shipDate) ? $shipDate.' 23:59:59' : date('Y-m-d 23:59:59');
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('billing.orderId, billing.paymentInfoId, billing.paymentBy, billing.paymentDate, billing.ordAmount')
                    ->from('Application\Entity\RetrieveBillingInfo', 'billing')
                    ->where('billing.shopId = :shopId')
                    ->andWhere('billing.paymentDate <= :endDate')
                    ->setParameter(':shopId', trim($siteCode))
                    ->setParameter(':endDate', $endDate)
                    ->andWhere('billing.paymentBy = 1')
                    ->andWhere('billing.isDelete = :isDelete')
                    ->setParameter(':isDelete', $isDelete);
        if($getAllOrder == 0){
            $beginDate = !empty($shipDate) ? $shipDate.' 00:00:00' : date('Y-m-d 00:00:00');
            $query->andWhere('billing.paymentDate >= :beginDate')
                  ->setParameter(':beginDate', $beginDate);
        }
        return $query->getQuery()->getArrayResult();
    }

    public function deleteDataByListIds($listIds){
        if(!$listIds || !is_array($listIds)){
            return false;
        }
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->update('\Application\Entity\RetrieveBillingInfo', 'r')
            ->set('r.isDelete', 1)
            ->where($qb->expr()->in('r.id', ':listIds'))
            ->setParameter(':listIds', $listIds);
        return $query->getQuery()->execute();
    }
}
