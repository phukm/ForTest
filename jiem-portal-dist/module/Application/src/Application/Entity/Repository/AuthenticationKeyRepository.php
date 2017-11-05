<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Application\Entity\AuthenticationKey;
use Zend\Validator\File\Count;

class AuthenticationKeyRepository extends EntityRepository
{

    public function auth($organizationNo, $authenKey, $expireDate)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('authen')
            ->from('\Application\Entity\AuthenticationKey', 'authen')
            ->where('authen.authenKey = :authenKey')
            ->andWhere('authen.organizationNo = :organizationNo')
            ->andWhere('authen.expireDate = :expireDate')
            ->setParameter(':authenKey', $authenKey)
            ->setParameter(':organizationNo', $organizationNo)
            ->setParameter(':expireDate', $expireDate);
        
        $result = $qb->getQuery()->getArrayResult();
        return $result;
    }
}