<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\InvitationSetting;

class InvitationSettingRepository extends EntityRepository
{
    // filter ID EikenSchedule
    public function getInvSetting($eikenScheduleId = '', $OrganizationId = '', $year = '')
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('i.id,i.hallType,i.invitationType,i.paymentType,i.deadLine,e.year,e.kai,i.status')
            ->from('\Application\Entity\InvitationSetting', 'i')
            ->leftJoin('\Application\Entity\EikenSchedule', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, 'i.eikenSchedule = e.id')
            ->where('i.organizationId = :organizationId')
            ->setParameter(':organizationId', $OrganizationId)
            ->orderBy('e.year,e.kai', 'DESC');
        
        if (!empty($eikenScheduleId)) {
            $qb->andWhere('i.eikenScheduleId = :eikenScheduleId')
                    ->setParameter(':eikenScheduleId', intval($eikenScheduleId));
        }else if(!empty($year)){
            $qb->andWhere('e.year = :year')->setParameter(':year', intval($year));
        }
        $result = $qb->getQuery()->getArrayResult();

        return empty($result) ? false : $result;
    }

    public function getInvitationSetting($organizationId = 0, $eikenScheduleId = 0)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('inviteSetting.hallType,inviteSetting.paymentType, inviteSetting.listEikenLevel, inviteSetting.beneficiary, inviteSetting.tempBeneficiary')
            ->from('\Application\Entity\InvitationSetting', 'inviteSetting')
            ->join('\Application\Entity\EikenSchedule', 'eikenSchedule', \Doctrine\ORM\Query\Expr\Join::WITH, 'inviteSetting.eikenSchedule=eikenSchedule.id')
            ->where('inviteSetting.eikenScheduleId = :eikenScheduleId')
            ->andWhere('inviteSetting.organizationId = :organizationId')
            ->andWhere('inviteSetting.isDelete = 0')
            ->setParameter(':eikenScheduleId', $eikenScheduleId)
            ->setParameter(':organizationId', $organizationId)
            ->setMaxResults(1);
        try {
            return $qb->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return array();
        }
    }
    
    public function setBeneficiaryForInvitationSetting($organizationId = 0, $eikenScheduleId = 0, $value = NULL)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $query = $qb->update('\Application\Entity\InvitationSetting', 'invitationSetting')
                ->set('invitationSetting.beneficiary', ':value')
                ->where('invitationSetting.organizationId = :organizationId')
                ->andWhere('invitationSetting.eikenScheduleId = :eikenScheduleId')
                ->setParameter(':value', $value)
                ->setParameter(':organizationId', $organizationId)
                ->setParameter(':eikenScheduleId', $eikenScheduleId)
                ->getQuery();
        $query->execute();

        return true;
    }
    
}