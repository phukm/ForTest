<?php
namespace Logs\Service;

use Logs\Service\ServiceInterface\ApplyEikenServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Dantai\PrivateSession;
use Doctrine\ORM\EntityManager;
use Dantai\Utility\JsonModelHelper;

class ApplyEikenService implements ApplyEikenServiceInterface, ServiceLocatorAwareInterface
{    
    use ServiceLocatorAwareTrait;
    
    protected $em;
    protected $organizationId;
    protected $organizationNo;
    protected $roleId;


    public function __construct()
    {
        $user = PrivateSession::getData('userIdentity');
        $this->organizationId = $user['organizationId'];
        $this->organizationNo = $user['organizationNo'];
        $this->roleId = $user['roleId'];
    }
    
    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        }

        return $this->em;
    }
    
    public function getApplyEikenLogList($searchCriteria)
    {
        $em = $this->getEntityManager();
        
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsMessages = array(
            'MSGdatefomat' => $translator->translate('MSGdatefomat'),
            'MSGdatecompare' => $translator->translate('MSGdatecompare'),
            'MSGpositivenumber' => $translator->translate('MSG1')
        );
        $jsonModelHelper = new JsonModelHelper();
        $jsonMessage = $jsonModelHelper->getInstance();
        $jsonMessage->setFail();
        $jsonMessage->setData($jsMessages);

        $data = $em->getRepository('Application\Entity\ApplyEikenLog')->getListApplyEikenLog($searchCriteria);
        return array($data, $jsonMessage);
    }
    
    private $currentScheduleRep;
    public function getCurrentSchedule($schedule = null)
    {
        $this->currentScheduleRep = $schedule ? $schedule : $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule');
    }
    
    private $nextScheduleRep;
    public function getNextSchedule($schedule = null)
    {
        $this->nextScheduleRep = $schedule ? $schedule : $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule');
    }
    
    public function getCurrentAndNextEikenSchedule()
    {
        $em = $this->getEntityManager();
        
        $currentDate = date(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT);
        $currentEikenScheduleId = 0;
        $currentYear = 0;
        $currentKai = 0;
        $currentDeadlineFrom = '';
        if(!$this->currentScheduleRep){
            $this->getCurrentSchedule();
        }
        $eikenSchedule = $this->currentScheduleRep->getCurrentKaiByYear(date('Y'));
        foreach ($eikenSchedule as $key => $value) {
            if (!empty($value['deadlineFrom']) && $value['deadlineFrom']->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) <= $currentDate) {
                $currentEikenScheduleId = $value['id'];
                $currentYear = $value['year'];
                $currentKai = $value['kai'];
                $currentDeadlineFrom = $value['deadlineFrom'];
                break;
            }
        }
        $nextYear = 0;
        $nextKai = 0;
        if($currentKai == 3){
            $nextYear = $currentYear + 1;
            $nextKai = 1;
        }else{
            $nextYear = $currentYear;
            $nextKai = $currentKai + 1;
        }
        
        if(!$this->nextScheduleRep){
            $this->getNextSchedule();
        }
        $nextEikenSchedule = $this->nextScheduleRep->getDeadlineFromOfNextKai($nextKai, $nextYear);
        
        $formCurrentDeadlineFrom = $currentDeadlineFrom->format(\Dantai\Utility\DateHelper::DATE_FORMAT_DEFAULT);
        $sqlCurrentDeadlineFrom = $currentDeadlineFrom->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT);
        $formNextDeadlineFromMinusByOneDay = date(\Dantai\Utility\DateHelper::DATE_FORMAT_DEFAULT, strtotime($nextEikenSchedule['deadlineFrom']->format(\Dantai\Utility\DateHelper::DATE_FORMAT_DEFAULT) . '-1 days'));
        $sqlNextDeadlineFromMinusByOneDay = date(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT, strtotime($nextEikenSchedule['deadlineFrom']->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT) . '-1 days'));
        
        return array($formCurrentDeadlineFrom, $formNextDeadlineFromMinusByOneDay, $sqlCurrentDeadlineFrom, $sqlNextDeadlineFromMinusByOneDay);
    }
    public function mappingDataForExport($dataDraft)
    {
        $action = $this->getServiceLocator()->get('Config')['actionName'];
        $data = array(); 
        if(!empty($dataDraft)){
            foreach($dataDraft as $key => $value){
                $data[$key]['organizationNo']  = $value['organizationNo'];
                $data[$key]['organizationName']  = $value['organizationName'];
                
                $userId = $value['userId'];
                $logTime = $value['insertAt'] ? date_format($value['insertAt'],"Y/m/d H:i") : '';
                $data[$key]['userIdAndDateTime']  = $logTime;
                $data[$key]['userId']  = $userId;

                $data[$key]['action']  = $value['action'] ? $action[$value['action']] : '';
                
                $stringMainDetai = '';
                if($value['mainDetail']){
                    $mainDetail = json_decode($value['mainDetail']);
                    if($mainDetail){
                        foreach($mainDetail as $i => $v){
                            if($v != ''){
                                if(empty($stringMainDetai)){
                                    $stringMainDetai .= '・本会場:'.$i.':'.$v;
                                }else{
                                    $stringMainDetai .= '、'.$i.':'.$v;
                                }
                            }

                        }
                    }
                } 
                $stringStandardDetail = '';
                if($value['standardDetail']){
                    $standardDetail = json_decode($value['standardDetail']);
                    if($standardDetail){
                        foreach($standardDetail as $i => $v){
                            if($v != ''){
                                if(empty($stringStandardDetail)){
                                    $stringStandardDetail .= '・準会場:'.$i.':'.$v;
                                }else{
                                    $stringStandardDetail .= '、'.$i.':'.$v;
                                }
                            }

                        }
                    }
                } 
                $stringRefundStatus = $value['refundDetail'] ? '・本会場運営費・準会場実施経費の取扱い:'.$value['refundDetail'] : '';
                $detail  = $stringMainDetai ? $stringMainDetai : '';
                if(!empty($detail)){
                        $detail  .= ($stringStandardDetail ? chr(10).$stringStandardDetail : '').($stringRefundStatus ? chr(10).$stringRefundStatus : '');
                }else{
                     $detail  .= $stringStandardDetail ? $stringStandardDetail : '';
                     if(!empty($detail)){
                        $detail  .= $stringRefundStatus ? chr(10).$stringRefundStatus : '';
                     }else{
                         $detail  .= $stringRefundStatus ? $stringRefundStatus : '';
                     }
                }
                
                $data[$key]['detail']  = $detail;
            }
        }
        return $data;
    }
    public function convertToExport($data,$header) {
        $dataExport = array();
        if ($data && $header) {
            $index = 0;
            foreach ($data as $row) {
                foreach ($header as $field => $titleHeader) {
                    $dataExport[$index][$field] = '';
                        $dataExport[$index][$field] = array_key_exists($field, $row) ? $row[$field] : '';
                        if (method_exists($this, $field)) {
                            $dataExport[$index] = array_merge($dataExport[$index],$this->$field($row,$field));
                        }
                }
                $index++;
            }
        }
        array_unshift($dataExport, $header);
        return $dataExport;
    }
}