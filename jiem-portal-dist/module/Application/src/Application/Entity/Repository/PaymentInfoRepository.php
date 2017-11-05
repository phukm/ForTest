<?php

namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class PaymentInfoRepository extends EntityRepository
{
    public function createPaymentInfo(\Application\Entity\Pupil $pupil, \Application\Entity\EikenSchedule $eikenSchedule, $config) {
        if (!$pupil || !$eikenSchedule) {
            return Null;
        }
        $em = $this->getEntityManager();
        $deadline = clone $eikenSchedule->getCreditCardDeadline();

        $paymentInfo = new \Application\Entity\PaymentInfo();

        $paymentInfo->setPupil($pupil);
        $paymentInfo->setSiteCode($config['site_code']);
        $paymentInfo->setMailAddress($config['email']);
        $paymentInfo->setName($pupil->getFirstNameKanji() . $pupil->getLastNameKanji());
        $paymentInfo->setDeadLine($deadline);
        $paymentInfo->setEikenSchedule($eikenSchedule);
        $em->persist($paymentInfo);
        $em->flush();
        return $paymentInfo;
    }


    public function deleteDataByListIds($listIds){
        if(!$listIds || !is_array($listIds)){
            return false;
        }
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->update('\Application\Entity\PaymentInfo', 'p')
            ->set('p.isDelete', 1)
            ->where($qb->expr()->in('p.id', ':listIds'))
            ->setParameter(':listIds', $listIds);
        return $query->getQuery()->execute();
    }
}

