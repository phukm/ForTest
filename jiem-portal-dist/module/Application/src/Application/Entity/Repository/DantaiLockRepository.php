<?php
namespace Application\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class DantaiLockRepository extends EntityRepository
{
    const LOCK_TIMEOUT = '-3600 seconds';
    
    /**
     * Try to lock the given $module for $locker
     * This function returns number if lock is created success fully, false if the current lock is created by another locker
     * 
     * @param string $module
     * @param number $locker
     * 
     * @return number|false
     */
    public function getLock($module, $locker = 0){
        $dantaiLock = new \Application\Entity\DantaiLock();
        $dantaiLock->setLocker($locker);
        $dantaiLock->setModule($module);
        
        try {
            $this->_em->persist($dantaiLock);
            $this->_em->flush();
            $this->_em->refresh($dantaiLock);
        } catch (\Exception $e) {
            // Concurent lock, and this locker is luckyless
            return false;
        }
        return $dantaiLock->getId();
    }
    
    /**
     * Removes lock by given $module and $locker
     * 
     * @param \Application\Entity\Common|string $module
     * @param number $locker
     */
    public function removeLock($module, $locker = 0){
        
        if($module instanceof \Application\Entity\Common){
            $module = get_class($module) . $module->getId();
        }
        
        $qb = $this->_em->createQueryBuilder();
        $qb->delete('\Application\Entity\DantaiLock', 'dantaiLock')
            ->where('dantaiLock.module = :lockedModule')
            ->andWhere('dantaiLock.locker = :locker')
            ->setParameter('lockedModule', $module)
            ->setParameter('locker', $locker);
        return $qb->getQuery()->execute();
    }
    
    /**
     * Removes all expired locks by given $locker
     * 
     * @param number $locker
     * @return mixed
     */
    public function purgeExpiredLocks($locker = 0) {
        $locker = (int)$locker;
        
        $deadLockTime = new \DateTime('now');
        $deadLockTime->modify(self::LOCK_TIMEOUT);
        $qb = $this->_em->createQueryBuilder();
        $qb->delete('\Application\Entity\DantaiLock', 'dantaiLock')
            ->where('dantaiLock.insertAt < :expriredDateTime')
            ->setParameter('expriredDateTime', $deadLockTime->format('Y-m-d H:i:s'));
        
        if($locker){
            $qb->andWhere('dantaiLock.locker = :locker')
                ->setParameter('locker', $locker);
        }
        
        return $qb->getQuery()->execute();
    }
}