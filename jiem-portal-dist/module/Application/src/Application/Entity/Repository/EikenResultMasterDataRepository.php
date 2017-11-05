<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\EikenTestResult;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Eiken\Helper\NativePaginator as DTPaginator;
use Doctrine\ORM\Query\ResultSetMapping;

class EikenResultMasterDataRepository extends EntityRepository
{
    public function getEikenMasterData($year, $kai,$isInland,$dateOfWeek)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenResultMasterData.year,eikenResultMasterData.kai,eikenResultMasterData.isInland,'
                . 'eikenResultMasterData.dateOfWeek,eikenResultMasterData.eikenLevelId,'
                . 'eikenResultMasterData.reading,eikenResultMasterData.listening,eikenResultMasterData.writing'
                . ',eikenResultMasterData.speaking,eikenResultMasterData.maxScoreRound1,eikenResultMasterData.maxScoreRound2'
                . ',eikenResultMasterData.cseBand1,eikenResultMasterData.cseBand2')
            ->from('\Application\Entity\EikenResultMasterData', 'eikenResultMasterData')
            ->where('eikenResultMasterData.year = :year')
            ->andWhere('eikenResultMasterData.kai= :kai')
            ->andWhere('eikenResultMasterData.isInland= :isInland')
            ->andWhere('eikenResultMasterData.dateOfWeek= :dateOfWeek')
            ->setParameter(':year', $year)
            ->setParameter(':kai', $kai)
            ->setParameter(':isInland', $isInland)
            ->setParameter(':dateOfWeek', $dateOfWeek);
        $result = $qb->getQuery()->getArrayResult();
        return $result;
    }
    
    public function getEikenMasterDataByKyu($year, $kai,$isInland,$dateOfWeek,$kyu)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('eikenResultMasterData')
            ->from('\Application\Entity\EikenResultMasterData', 'eikenResultMasterData')
            ->where('eikenResultMasterData.year = :year')
            ->andWhere('eikenResultMasterData.kai= :kai')
            ->andWhere('eikenResultMasterData.isInland= :isInland')
            ->andWhere('eikenResultMasterData.dateOfWeek= :dateOfWeek')
            ->andWhere('eikenResultMasterData.eikenLevelId= :eikenLevelId')
            ->setParameter(':year', $year)
            ->setParameter(':kai', $kai)
            ->setParameter(':isInland', $isInland)
            ->setParameter(':dateOfWeek', $dateOfWeek)
            ->setParameter(':eikenLevelId', $kyu);
        $result = $qb->getQuery()->getArrayResult();
        return $result ? $result[0] : array();
    }

    public function getEikenResultMasterData($year = 2016, $kai = 1)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e.id, e.listening, e.reading, e.writing, e.speaking, e.cseBand1, e.cseBand2, e.eikenLevelId, CONCAT(e.eikenLevelId, e.isInland, e.dateOfWeek) AS ids')
            ->from('\Application\Entity\EikenResultMasterData', 'e')
            ->where('e.year = :year')
            ->andWhere('e.kai= :kai')
            ->setParameter(':year', $year)
            ->setParameter(':kai', $kai);

        return $qb->getQuery()->getArrayResult();
    }
}
