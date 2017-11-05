<?php

namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\IssuingPayment;

class IssuingPaymentRepository extends EntityRepository
{
    /*
     * Get Price By List Eiken Level
     * @param $arr_eiken_level
     * @return array price
     */

    public function getDataByPupilAndEikenSchedule($pupilId, $eikenScheduleId, $priceLevels)
    {
        $result = array();
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $paymentInfo = $em->getRepository('Application\Entity\PaymentInfo')->findBy(array(
            "pupilId" => $pupilId,
            "eikenScheduleId" => $eikenScheduleId
        ));

        if ($paymentInfo != Null) {
            $arrPaymentInfoId = array();
            foreach ($paymentInfo as $payment) {
                $arrPaymentInfoId[] = $payment->getId();
            }
            if ($arrPaymentInfoId) {
                $qb->select('issuingPayment.id, issuingPayment.orderId, issuingPayment.telNo, issuingPayment.receiptNo, issuingPayment.eikenLevelId, issuingPayment.price')
                    ->from('\Application\Entity\IssuingPayment', 'issuingPayment', 'issuingPayment.id')
                    ->where('issuingPayment.paymentInfoId IN (' . implode(',', $arrPaymentInfoId) . ') ')
                    ->andWhere('issuingPayment.telNo IS NOT NULL and issuingPayment.receiptNo IS NOT NULL')
                    ->orderBy('issuingPayment.eikenLevelId', 'ASC');

                $query = $qb->getQuery();
                $data = $query->getArrayResult();

                foreach ($data as $value) {
                    if ($value['price'] == $priceLevels[$value['eikenLevelId']]['price']) {
                        $result[$value['eikenLevelId']] = $value;
                    }
                }
            }
        }
        return $result;
    }

    /*
     * create new IssuingPayment after create new PaymentInfo
     * @param \Application\Entity\PaymentInfo $paymentInfo
     * $param array $eikenLevels = array(
            [eikenLevelId] => array('name' => [eikenLevelName], 'price' => [eikenLevelPrice]) 
     * )
     * @param string $orderId
     * @param string $telNo
     */
    public function createIssuingPayment(\Application\Entity\PaymentInfo $paymentInfo, array $eikenLevels, $orderId, $telNo)
    {
        if (!$paymentInfo) {
            return false;
        }

        $em = $this->getEntityManager();
        foreach ($eikenLevels as $eikenLevelId => $eikenLevel) {
            $objEikenLevel = $em->getReference('\Application\Entity\EikenLevel', $eikenLevelId);
            $issuingPayment = new \Application\Entity\IssuingPayment();
            $productName = '実用英語技能検定' . $eikenLevel['name'];
            $issuingPayment->setPaymentInfo($paymentInfo);
            $issuingPayment->setEikenLevel($objEikenLevel);
            $issuingPayment->setTelNo($telNo);
            $issuingPayment->setProductName($productName);
            $issuingPayment->setPrice($eikenLevel['price']);
            $issuingPayment->setOrderId($orderId);
            $em->persist($issuingPayment);
            $em->flush();
        }
        return true;
    }

    public function getDataByPupilIdAndEikenScheduleId($pupilId, $eikenScheduleId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('IssuingPayment')
            ->from('\Application\Entity\PaymentInfo', 'PaymentInfo')
            ->join('\Application\Entity\IssuingPayment', 'IssuingPayment', \Doctrine\ORM\Query\Expr\Join::WITH, 'PaymentInfo.id = IssuingPayment.paymentInfoId')
            ->where('PaymentInfo.isDelete = 0')
            ->andWhere('IssuingPayment.isDelete = 0')
            ->andWhere('PaymentInfo.eikenScheduleId = :eikenScheduleId')
            ->andWhere('PaymentInfo.pupilId = :pupilId')
            ->setParameter(':eikenScheduleId', $eikenScheduleId)
            ->setParameter(':pupilId', $pupilId)
            ->orderBy('IssuingPayment.id', 'DESC');
        return $query->getQuery()->getArrayResult();
    }

    public function getDataDuplicateCreditCardByDate($date, $eikenScheduleId)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('p.pupilId, i.eikenLevelId, i.price, Count(i.id) as totalIssuing, Count(p.id) as totalPayment, Count(r.id) as totalRetrieve')
            ->from('\Application\Entity\IssuingPayment', 'i')
            ->join('\Application\Entity\PaymentInfo', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id = i.paymentInfoId')
            ->join('\Application\Entity\RetrieveBillingInfo', 'r', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id = r.paymentInfoId')
            ->where('p.eikenScheduleId = :eikenScheduleId')
            ->andWhere('p.siteCode = :siteCode')
            ->andWhere('p.paymentStatus = :paymentStatus')
            ->setParameter(':eikenScheduleId', intval($eikenScheduleId))
            ->setParameter(':siteCode', '078013')
            ->setParameter(':paymentStatus', 1)
            ->groupBy('p.pupilId')
            ->addGroupBy('i.eikenLevelId')
            ->addGroupBy('i.price')
            ->having('Count(i.id) > 1');
        if ($date) {
            $beginDate = !empty($date) ? $date . ' 00:00:00' : date('Y-m-d 00:00:00');
            $endDate = !empty($date) ? $date . ' 23:59:59' : date('Y-m-d 23:59:59');
            $query->andWhere('r.paymentDate >= :beginDate')
                ->andWhere('r.paymentDate <= :endDate')
                ->setParameter(':beginDate', $beginDate)
                ->setParameter(':endDate', $endDate);
        }
        return $query->getQuery()->getArrayResult();
    }

    public function getDataPaymentByListDuplicate($listDuplicate, $eikenScheduleId){
        if(!$listDuplicate || !is_array($listDuplicate)){
            return false;
        }
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->select('p.pupilId, p.id as paymentInfoId, i.id as issuingId, r.id as retrieveId, i.eikenLevelId, i.price, r.orderId')
            ->from('\Application\Entity\IssuingPayment', 'i')
            ->join('\Application\Entity\PaymentInfo', 'p', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id = i.paymentInfoId')
            ->join('\Application\Entity\RetrieveBillingInfo', 'r', \Doctrine\ORM\Query\Expr\Join::WITH, 'p.id = r.paymentInfoId')
            ->where('p.eikenScheduleId = :eikenScheduleId')
            ->andWhere('p.siteCode = :siteCode')
            ->andWhere('p.paymentStatus = :paymentStatus')
            ->andWhere($qb->expr()->in('CONCAT(p.pupilId ,\'-\', i.eikenLevelId, \'-\', i.price)', ':listDuplicate'))
            ->setParameter(':eikenScheduleId', intval($eikenScheduleId))
            ->setParameter(':siteCode', '078013')
            ->setParameter(':paymentStatus', 1)
            ->setParameter(':listDuplicate', $listDuplicate);
        return $query->getQuery()->getArrayResult();
    }

    public function deleteDataByListIds($listIds){
        if(!$listIds || !is_array($listIds)){
            return false;
        }
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $query = $qb->update('\Application\Entity\IssuingPayment', 'i')
            ->set('i.isDelete', 1)
            ->where($qb->expr()->in('i.id', ':listIds'))
            ->setParameter(':listIds', $listIds);
        return $query->getQuery()->execute();
    }

}
