<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Application\Entity\DoubleEikenMessages;

class DoubleEikenMessagesRepository extends EntityRepository
{
    //call Module : Invitation setting
    public function getMessages()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('doubleeikenmessages')
            ->from('\Application\Entity\DoubleEikenMessages', 'doubleeikenmessages')
            ->orderBy('doubleeikenmessages.id', 'ASC');
        $result = $qb->getQuery()->getArrayResult();

        return $result;
    }
}