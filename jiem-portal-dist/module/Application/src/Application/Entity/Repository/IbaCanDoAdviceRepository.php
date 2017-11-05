<?php

namespace Application\Entity\Repository;

use Dantai\DantaiConstants;
use Doctrine\ORM\EntityRepository;

class IbaCanDoAdviceRepository extends EntityRepository 
{

    public function getIbaScoreCanDo($levelName){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('i.reading, i.listening')
            ->from('\Application\Entity\IbaCanDoAdvice', 'i')
            ->where('i.type = :type')
            ->andWhere('i.isDelete = 0')
            ->andWhere('i.ibaLevelName = :levelName')
            ->setParameter(':levelName', $levelName)
            ->setParameter(':type', DantaiConstants::TYPE_CAN_DO)
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getSingleResult();
    }

    public function getIbaScoreAdvice($levelName){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('i.reading, i.listening, i.vocab')
            ->from('\Application\Entity\IbaCanDoAdvice', 'i')
            ->where('i.type = :type')
            ->andWhere('i.isDelete = 0')
            ->andWhere('i.ibaLevelName = :levelName')
            ->setParameter(':levelName', $levelName)
            ->setParameter(':type', DantaiConstants::TYPE_ADVICE)
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getSingleResult();
    }
}
