<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
// use Application\Entity\TemplateInvitationMsg;
class TemplateInvitationMsgRepository extends EntityRepository
{

    public function getTemplateInvitationMsg()
    {
        return array(
            'pupil' => self::messageType(0),
            'parent' => self::messageType(1)
        );
    }

    function messageType($type)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t')
            ->from('\Application\Entity\TemplateInvitationMsg', 't')
            ->where("t.type = :msgType")
            ->setParameter('msgType', $type)
            ->orderBy('t.name','ASC');
        $result = $qb->getQuery()->getArrayResult();
        
        return $result;
    }
}