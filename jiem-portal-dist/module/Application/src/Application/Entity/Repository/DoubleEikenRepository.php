<?php

namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\Kai;

class DoubleEikenRepository extends EntityRepository
{
    public function getMessages()
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('DoubleEiken')
            ->from('\Application\Entity\DoubleEiken', 'DoubleEiken')
            ->orderBy('DoubleEiken.id', 'desc');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();

        return $result;

    }
}