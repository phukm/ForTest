<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Satellite\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Satellite\Constants;
use Dantai\Api\UkestukeClient;

class SatelliteService implements ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    private $entityManager;
    private $examGrade = array(
        '1級' => 1,
        '準1級' => 2,
        '2級' => 3,
        '準2級' => 4,
        '3級' => 5,
        '4級' => 6,
        '5級' => 7
    );
    private $ukestukeClient;

    public function getEntityManager() {
        if(!$this->entityManager){
            $this->entityManager = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }

        return $this->entityManager;
    }

    public function setEntityManager($entityManager) {
        $this->entityManager = $entityManager;
    }

    public function updateSessionId($authenKey, $organizationNo, $sessionId){
        try {
            $em = $this->getEntityManager();
            $authenKeyObj = $em->getRepository('Application\Entity\AuthenticationKey')->findOneBy(
                array(
                    'authenKey'      => $authenKey,
                    'organizationNo' => hexdec($organizationNo),
                    'isDelete'       => 0
                )
            );
            if(empty($authenKeyObj)){
                return false;
            }

            $authenKeyObj->setSessionId($sessionId);
            $em->persist($authenKey);
            $em->flush();
            return true;
        } catch (\Exception $ex){
            return false;
        }
    }
 
   public function setAvailableKyu($applied, $list, $doubleEiken){       
       if(count($applied) >=2 || (count($applied) === 1 && $doubleEiken == Constants::DOUBLE_EIKEN)){
            return array();
       }else if(count($applied) == 0){
         return $list;
       }else {
            $available = array();
            foreach($applied as $k){
                if(in_array($k-1, $list) ){
                    array_push($available, $k-1);
                }
                
                if(in_array($k+1, $list) ){
                    array_push($available, $k+1);
                }
            }
            return array_diff(array_unique($available), $applied);
        }
   }
   
    public function setUkestukeClient($client = null) {
        $this->ukestukeClient = $client ? $client : UkestukeClient::getInstance();
    }
   
    public function getEikenAppliedFromUketuke($eikenId, $year, $kai){
//      $test = json_decode('{"eikenArray":[{"kekka":"10","dantaino":null,"kyucd":null,"siteflg":null}]}');
//      $test = json_decode('{"eikenArray":[{"kekka":"10","dantaino":"99900600","kyucd":"2","siteflg":"2"}]}');
//      $test = json_decode('{"eikenArray":[{"kekka":"10","dantaino":"99900300","kyucd":"1","siteflg":"2"},{"kekka":"10","dantaino":"99900300","kyucd":"2","siteflg":"2"}]}');
//      $test = json_decode('{"eikenArray":[{"kekka":"00","dantaino":null,"kyucd":null,"siteflg":null}]}');  
//      $test = json_decode('{"kekka":"99"}');  
//      return $test;
      
      
      
      
        $config = $this->getServiceLocator()->get('Config')['orgmnt_config']['api'];
        
        $resultErr = json_decode('{"kekka":"99"}'); 
        
        try {
            if (!$this->ukestukeClient) {
                $this->setUkestukeClient();
            }
            $result = $this->ukestukeClient->callEikenApplied($config, array(
                    "eikenid"=>$eikenId,
                    "nendo"=>$year,
                    "kai"=>$kai
            ));
            
        } catch (\Exception $e) {
            return $resultErr;
        }

        if(!isset($result)){
            return $resultErr;
        }
        
        return $result;
    }
}

