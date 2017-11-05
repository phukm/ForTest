<?php

namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class IbaScoreMasterDataRepository extends EntityRepository
{
    public function getIbaScoreStarNumber($type, $testSet, $score){
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('i.starNumber, i.canDoName, i.adviceName')
            ->from('\Application\Entity\IbaScoreMasterData', 'i')
            ->where('i.type = :type')
            ->andWhere('i.testSet = :testSet')
            ->andWhere('i.scoreRangeFrom <= :score')
            ->andWhere('i.scoreRangeTo >= :score')
            ->andWhere('i.isDelete = 0')
            ->setParameter('type', $type)
            ->setParameter('testSet', $testSet)
            ->setParameter('score', $score)
            ->orderBy('i.scoreRangeFrom','DESC')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
    
    public function getListIbaScoreMasterData($type=null)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('i.type, i.testSet, i.scoreRangeFrom, i.scoreRangeTo, i.ibaLevelName')
           ->from('\Application\Entity\IbaScoreMasterData', 'i');
        if($type!=null)
        {
           $qb ->where('i.type = :type')
           ->setParameter('type', $type);
        }        
        return $qb->getQuery()->getArrayResult();      
    }
}