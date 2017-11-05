<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
 namespace ConsoleInvitation\Controller;

use Zend\Mvc\Controller\AbstractActionController;
/**
 * Description of ConsoleUserController
 *
 * @author UtHV
 */
class ConsoleUserController extends AbstractActionController {

     /*
     * Cron job process disable user have roleId=4 and roleId=5 when begin start new kai.
     * Author: Uthv
     * Create: 24/11/2015
     */

    public function disableUserAction() {
        $em = $this->getEntityManager();   
        $objEikenSchedule = $em->getRepository('Application\Entity\EikenSchedule')->checkKaiExitByDatelineFrom();
        if (!empty($objEikenSchedule)) {
            $currentKai = $em->getRepository('Application\Entity\EikenSchedule')->getCurrentEikenSchedule();
            if($currentKai){
                $year= isset($currentKai['year']) ? intval($currentKai['year']) : 0;
                $kai= isset($currentKai['kai']) ? intval($currentKai['kai']) : 0;
                echo "update accesskey for $year kai $kai".PHP_EOL;
                $em->getRepository('Application\Entity\AccessKey')->deleteAccessKeyForNewKai($year,$kai);
            }
            echo "disable user for $year kai $kai".PHP_EOL;
            $em->getRepository('Application\Entity\User')->disableAllUserByNewKai();
        }
    }
    
     public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

}
