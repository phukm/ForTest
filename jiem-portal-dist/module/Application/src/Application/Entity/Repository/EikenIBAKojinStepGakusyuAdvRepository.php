<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\EikenIBAKojinStepGakusyuAdv;

class EikenIBAKojinStepGakusyuAdvRepository extends EntityRepository
{
    function getCanDo($testsyubetsu, $sinkyuKbn, $gino, $status, $score)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('i.id,i.readCapaStatment,i.listenCapaStatment,i.gakusyuAdvStep,i.kojinStep')
            ->from('\Application\Entity\EikenIBAKojinStepGakusyuAdv', 'i')
            ->where('i.status = :status')
            ->andWhere('i.gino = :gino')
            ->andWhere('i.sinkyuKbn = :sinkyuKbn')
//             ->andWhere('i.testsyubetsu = :testsyubetsu') // Remove this condition to fix the following issue: [UAT2] DANTAI3-17_Nội dung hiển thị trên bảng thành tích cá nhân Eiken IBA (field testsyubetsu trong master data không liên quan gì đến Test Type A, B, ...)
            ->andWhere('i.scoreRangeFrom <= :score')
            ->andWhere('i.scoreRangeTo >= :score')
            ->orderBy('i.scoreRangeFrom','DESC')
//             ->setParameter(':testsyubetsu', $testsyubetsu)
            ->setParameter(':gino', $gino)
            ->setParameter(':sinkyuKbn', $sinkyuKbn)
            ->setParameter(':status', $status)
            ->setParameter(':score', $score)
            ->setMaxResults(1);

        return $query->getQuery()->getOneOrNullResult();
    }

    function getAdvice($testsyubetsu, $sinkyuKbn, $bunya,$gakusyuAdvStep, $status)
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('i.id,i.advBun')
            ->from('\Application\Entity\EikenIBAKojinStepGakusyuAdv', 'i')
            ->where('i.status = :status')
            ->andWhere('i.sinkyuKbn = :sinkyuKbn')
//             ->andWhere('i.testsyubetsu = :testsyubetsu') // Remove this condition to fix the following issue: [UAT2] DANTAI3-17_Nội dung hiển thị trên bảng thành tích cá nhân Eiken IBA (field testsyubetsu trong master data không liên quan gì đến Test Type A, B, ...)
            ->andWhere('i.bunya = :bunya')
            ->andWhere('i.gakusyuAdvStep = :gakusyuAdvStep')
//             ->setParameter(':testsyubetsu', $testsyubetsu)
            ->setParameter(':sinkyuKbn', $sinkyuKbn)
            ->setParameter(':bunya', $bunya)
            ->setParameter(':gakusyuAdvStep', $gakusyuAdvStep)
            ->setParameter(':status', $status)
            ->setMaxResults(1);
        return $query->getQuery()->getOneOrNullResult();
    }
}