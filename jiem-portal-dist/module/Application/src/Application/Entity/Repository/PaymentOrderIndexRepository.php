<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class PaymentOrderIndexRepository extends EntityRepository
{

    /**
     * function get data for homepage with input conditions
     *
     * @author MinhTN6
     * @param string $orderPrefix           
     * @return Object
     */
    
    public function getPaymentOrderIndexByPrefix($orderPrefix){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb->select('PaymentOrderIndex')
                    ->from('\Application\Entity\PaymentOrderIndex','PaymentOrderIndex')
                    ->where('PaymentOrderIndex.prefix = :prefix')
                    ->setParameter('prefix', $orderPrefix);
        return $query->getQuery()->getOneOrNullResult();
    }
   
}