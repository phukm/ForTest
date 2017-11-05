<?php

namespace ConsoleInvitation\Service;

class ProcessSatelliteLogManager implements \Zend\ServiceManager\ServiceLocatorAwareInterface{
    use \Zend\ServiceManager\ServiceLocatorAwareTrait;
    
    const ProcessLog = '\Application\Entity\ProcessLog';


    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
    
    private $pupilId;
    private $scheduleId;
    private $processLog;
    protected function __construct($pupilId,$scheduleId,$serviceLocator) {
        $this->pupilId = $pupilId;
        $this->scheduleId = $scheduleId;
        $this->setServiceLocator($serviceLocator);
    }
    
    public static $instance = array();
    /**
     * @param type $pupilId
     * @param type $scheduleId
     * @param type $serviceLocator
     * @return ProcessLogManager
     */
    public static function getInstance($pupilId,$scheduleId,$serviceLocator){
        $key = $pupilId.'_'.$scheduleId;
        if(array_key_exists($key, self::$instance)){
            return self::$instance[$key];
        }
        
        self::$instance[$key] = new self($pupilId,$scheduleId,$serviceLocator);
        return self::$instance[$key];
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilder(){
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq ->where('ProcessLog.pupilId = :pupilId AND ProcessLog.scheduleId = :scheduleId')
            ->setParameter('pupilId', $this->pupilId)
            ->setParameter('scheduleId', $this->scheduleId);
        return $dq;
    }

    public function countDownTotal(){
        $dq = $this->getQueryBuilder();
        $dq->update(self::ProcessLog, 'ProcessLog')
            ->set('ProcessLog.total', 'ProcessLog.total - 1');
        $dq->getQuery()->execute();
    }
    
    /**
     * @return \Application\Entity\ProcessLog
     */
    public function getProcessLog($refresh = true){
        if(empty($this->processLog) || $refresh){
            $dq = $this->getQueryBuilder();
            $dq ->select('ProcessLog')
                ->from(self::ProcessLog,'ProcessLog');
            $this->processLog =  $dq->getQuery()->getOneOrNullResult();
        }
        return $this->processLog;
    }
    
    public function saveProcessLog(){
        $this->getEntityManager()->persist($this->processLog);
        $this->getEntityManager()->flush();
    }
    
    public function removeProcessLog(){
        $processLog = $this->getProcessLog();
        if(!empty($processLog)){
            $this->getEntityManager()->remove($processLog);
            $this->getEntityManager()->flush();
        }
    }

    public function setRunable(){
        $dq = $this->getQueryBuilder();
        $dq->update(self::ProcessLog, 'ProcessLog')
            ->set('ProcessLog.active', 'ProcessLog.total');
        $dq->getQuery()->execute();
    }
    
    public function removeProcessLogComplete(){
        $dq = $this->getQueryBuilder();
        $dq ->delete(self::ProcessLog, 'ProcessLog')
            ->andWhere('ProcessLog.total = 0');
        $dq->getQuery()->execute();
    }

    public function isGenerateComplete($removeProcessLogWhenComplete = false){
        $processLog = $this->getProcessLog();
        if(empty($processLog)){
            return false;
        }
        $isComplete = ($processLog->getTotal() <= 0);

        if (FALSE === $removeProcessLogWhenComplete) {
            return $isComplete;
        }

        $this->removeProcessLogComplete();

        return $isComplete;
    }
}
