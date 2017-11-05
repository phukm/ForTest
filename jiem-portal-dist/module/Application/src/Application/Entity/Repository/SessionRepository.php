<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class SessionRepository extends EntityRepository
{
    public function gcSession($lifetime){
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq->delete($this->_entityName, 'Session')
            ->where('Session.modified < :modified')
                ->setParameter('modified', time() - $lifetime);
        return $dq->getQuery()->execute();
    }
}