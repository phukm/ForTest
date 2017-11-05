<?php

/**
 * @description this function process business for Org Goal and CSE score
 * @author minhbn1<minhbn1@fsoft.com.vn>
 */

namespace ConsoleInvitation\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class EikenService implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    protected $sl;
    protected $em;

    //
    const PASSED = 1;

    //
    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        $this->setServiceLocator($serviceManager);
        $this->sl = $this->getServiceLocator();
        if ($this->sl)
            $this->em = $this->getEntityManager();
    }

    /**
     * @return array|object
     */
    public function getEntityManager() {
        return $this->sl->get('doctrine.entitymanager.orm_default');
    }
    
    public function saveDummyEiken($year, $kai) {
        $this->em->getConnection()->beginTransaction();
        try {
            $this->em->getConnection()->commit();
            $result = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenOrg')->insertDummyEikenOrg($year, $kai);
            $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenOrgDetails')->insertDummyEikenOrgDetail($year, $kai);
            echo 'Had create dummy header eiken for ' . $result . ' dantai' . PHP_EOL;
        } catch (Exception $ex) {
            $this->em->getConnection()->rollback();
            echo 'Import dummy eiken flase ' . PHP_EOL;
        }
    }

}
