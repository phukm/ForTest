<?php
namespace IBA\Service;

use IBA\Service\ServiceInterface\IBAServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class IBAService implements IBAServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager(){
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
    private $uketukeClient;
    
    /**
     * 
     * @param \Application\Entity\ApplyIBAOrg $applyIBA
     */
    public function sendApplyIBAdataToApi( \Application\Entity\ApplyIBAOrg $applyIBAEntity){
        
        $applyIBA = $applyIBAEntity->toArray();
        
        $optionMenu = explode(',', $applyIBA['optionMenu']);
        $dataSend = array(
            'moshikomiid' => $applyIBA['moshikomiId'],
            'keiyakudantainame' => $applyIBA['orgNameKanji'],
            'keiyakuzipcd' => $applyIBA['zipCode1'].$applyIBA['zipCode2'],
            'keiyakutodofukencd' => $applyIBA['prefectureCode'],
            'keiyakuaddress1' => $applyIBA['address1'],
            'keiyakuaddress2' => $applyIBA['address2'],
            'keiyakutel' => $applyIBA['telNo'],
            'keiyakufax' => $applyIBA['fax'],
            'keiyakuemailshimei' => $applyIBA['mailName1'],
            'keiyakuemail' => $applyIBA['mailAddress1'],
            'jisshiyoteidate' => $applyIBA['testDate'],
            'jissimokuteki_01' => ($applyIBA['purpose'] === 'placement') ? 1: 0,
            'jissimokuteki_02' => ($applyIBA['purpose'] === 'measurement') ? 1: 0,
            'jissimokuteki_99' => ($applyIBA['purpose'] === 'other') ? 1: 0,
            'jissimokuteki_99_biko' => $applyIBA['purposeOther'],
            'ninzu_a' => $applyIBA['numberPeopleA'],
            'cd_a' => $applyIBA['numberCDA'],
            'ninzu_b' => $applyIBA['numberPeopleB'],
            'cd_b' => $applyIBA['numberCDB'],
            'ninzu_c' => $applyIBA['numberPeopleC'],
            'cd_c' => $applyIBA['numberCDC'],
            'ninzu_d' => $applyIBA['numberPeopleD'],
            'cd_d' => $applyIBA['numberCDD'],
            'ninzu_e' => $applyIBA['numberPeopleE'],
            'cd_e' => $applyIBA['numberCDE'],
            'keiyakuyobo' => $applyIBA['question1'],
            'optionflg' => $applyIBA['optionApply'],
            'seisekisofu' => in_array('1', $optionMenu)?1:0,
            'shiwake' => in_array('2', $optionMenu)?1:0,
            'shiwakesu' => in_array('2', $optionMenu)? $applyIBA['questionNo'] : null,
            'meibo' => in_array('3', $optionMenu)?1:0,
            'eikenid' => '01',
            'eikenlevel' => in_array('5', $optionMenu)?'02':'01',
            'junihyoji' => in_array('6', $optionMenu)?'02':'01',
            'junihyojiseigen' => in_array('7', $optionMenu)?$applyIBA['rankNo']:null,
            'optionyobo' => $applyIBA['question2'],
            'gokei' => $this->calculatePrice($applyIBAEntity)['total4'],
            'confirmflg' => null,
            'signature' => null,
            'keiyakudantaino' => $applyIBA['organizationNo'],
            'keiyakutanto' => $applyIBA['firtNameKanji'].$applyIBA['lastNameKanji'],
            'keiyakuemailshimei2' => null,
            'keiyakuemailshimei3' => null,
            'keiyakuemail2' => null,
            'keiyakuemail3' => null,
            'ninzu_f' => null,
            'cd_f' => null,
        );
        $config = $this->getServiceLocator()->get('Config')['iba_config']['api'];
        $config['data1'] = $config['data2'] = $applyIBA['organizationNo'];
        return \Dantai\Api\UkestukeClient::getInstance()->callEir2c01($config,$dataSend);
    }
    
    public function calculatePrice($IBAItem) {
        $priceArr = array();
        $totalPeople = $IBAItem->getTotalPeople();
       
        $optionMenuArr = explode(',', $IBAItem->getOptionMenu());
   
        $grp1Arr = '1,2,3';
        $grp1MenuArr = explode(',', $grp1Arr);
        
        $grp1CheckArr = array_intersect($grp1MenuArr, $optionMenuArr);
        
        $grp2Arr = '4,5,6,7';
        $grp2MenuArr = explode(',', $grp2Arr);
        
        $grp2CheckArr = array_intersect($grp2MenuArr, $optionMenuArr);
        
        $optionApply = $IBAItem->getOptionApply();
        
        $priceA = $IBAItem->getNumberPeopleA() * \IBA\IBAConst::IBA_UNIT_PRICE;
        $priceB = $IBAItem->getNumberPeopleB() * \IBA\IBAConst::IBA_UNIT_PRICE;
        $priceC = $IBAItem->getNumberPeopleC() * \IBA\IBAConst::IBA_UNIT_PRICE;
        $priceD = $IBAItem->getNumberPeopleD() * \IBA\IBAConst::IBA_UNIT_PRICE;
        $priceE = $IBAItem->getNumberPeopleE() * \IBA\IBAConst::IBA_UNIT_PRICE;

        $realTotal1 = $priceA + $priceB + $priceC + $priceD + $priceE;
        
        $total1 = ($totalPeople < \IBA\IBAConst::IBA_STUDENT_COUNT_DISCOUNT) ? \IBA\IBAConst::IBA_PRICE_1_MAX : $realTotal1;
        
        $subTotal1 = ($totalPeople < \IBA\IBAConst::IBA_STUDENT_COUNT_DISCOUNT) ? \IBA\IBAConst::IBA_PRICE_1_MAX - $realTotal1 : 0;
        
        $price1 = (in_array('1', $optionMenuArr) && $optionApply != 0) ? $totalPeople * \IBA\IBAConst::IBA_PRICE_1_UNIT_PRICE + \IBA\IBAConst::IBA_PRICE_COUNT_CONDITION : NULL;
 
        $price2 = (in_array('2', $optionMenuArr) && $optionApply != 0) ? ($totalPeople <= \IBA\IBAConst::IBA_PRICE_COUNT_CONDITION) ? $totalPeople * \IBA\IBAConst::IBA_PRICE_MULTIPLE_MAX : $totalPeople * \IBA\IBAConst::IBA_PRICE_MULTIPLE_MIN : NULL;

        $price3 = (in_array('3', $optionMenuArr) && $optionApply != 0) ? ($totalPeople <= \IBA\IBAConst::IBA_PRICE_COUNT_CONDITION) ? $totalPeople * \IBA\IBAConst::IBA_PRICE_MULTIPLE_MAX : $totalPeople * \IBA\IBAConst::IBA_PRICE_MULTIPLE_MIN : NULL;
 
        $price4 = (count($grp2CheckArr) > 0  && $optionApply != 0) ? ($totalPeople <= \IBA\IBAConst::IBA_PRICE_COUNT_CONDITION) ? $totalPeople * \IBA\IBAConst::IBA_PRICE_MULTIPLE_MAX : 0 : NULL;

        $total2 = $price1 + $price2 + $price3 + $price4;
        
        // @author: minhbn1 <minhbn1@fsoft.com.vn> option compensation fee
        $optionFeeprice1 = 0; 
        $optionPrice = 0;
        if(in_array('1', $optionMenuArr) && $optionApply != 0 && $totalPeople < \IBA\IBAConst::IBA_STUDENT_COUNT_DISCOUNT){
            $optionFeeprice1 = (\IBA\IBAConst::IBA_STUDENT_COUNT_DISCOUNT-$totalPeople) * \IBA\IBAConst::IBA_PRICE_1_UNIT_PRICE;
            $optionPrice+=\IBA\IBAConst::IBA_PRICE_1_UNIT_PRICE;
        }
        
        $optionFeeprice2 = 0;
        if(in_array('2', $optionMenuArr) && $optionApply != 0 && $totalPeople < \IBA\IBAConst::IBA_STUDENT_COUNT_DISCOUNT){
            $optionFeeprice2 = (\IBA\IBAConst::IBA_STUDENT_COUNT_DISCOUNT-$totalPeople) * \IBA\IBAConst::IBA_PRICE_MULTIPLE_MAX;
            $optionPrice+=\IBA\IBAConst::IBA_PRICE_MULTIPLE_MAX;
        }
        $optionFeeprice3 = 0;
        if(in_array('3', $optionMenuArr) && $optionApply != 0 && $totalPeople < \IBA\IBAConst::IBA_STUDENT_COUNT_DISCOUNT){
            $optionFeeprice3 = (\IBA\IBAConst::IBA_STUDENT_COUNT_DISCOUNT-$totalPeople) * \IBA\IBAConst::IBA_PRICE_MULTIPLE_MAX;
            $optionPrice+=\IBA\IBAConst::IBA_PRICE_MULTIPLE_MAX;
        }
        $optionFeeprice4 = 0;
        if(count($grp2CheckArr) > 0  && $optionApply != 0 && $totalPeople < \IBA\IBAConst::IBA_STUDENT_COUNT_DISCOUNT){
            $optionFeeprice4 = (\IBA\IBAConst::IBA_STUDENT_COUNT_DISCOUNT-$totalPeople) * \IBA\IBAConst::IBA_PRICE_MULTIPLE_MAX;
            $optionPrice+=\IBA\IBAConst::IBA_PRICE_MULTIPLE_MAX;
        }
        $optionFeeTotal = $optionFeeprice1+$optionFeeprice2+$optionFeeprice3+$optionFeeprice4;
        //
        $subTotal2 = $optionFeeTotal + $subTotal1;
        //
        $total4 = $total1 + $total2 + $subTotal2 - $subTotal1;
        
        $priceArr['priceA'] = $priceA;
        $priceArr['priceB'] = $priceB;
        $priceArr['priceC'] = $priceC;
        $priceArr['priceD'] = $priceD;
        $priceArr['priceE'] = $priceE;
        $priceArr['total1'] = $total1;
        $priceArr['subTotal1'] = $subTotal1;
        $priceArr['realTotal1'] = $realTotal1;
        $priceArr['price1'] = $price1;
        $priceArr['price2'] = $price2;
        $priceArr['price3'] = $price3;
        $priceArr['price4'] = $price4;
        $priceArr['optionPrice'] = $optionPrice;
        $priceArr['optionFeeprice1'] = $optionFeeprice1;
        $priceArr['optionFeeprice2'] = $optionFeeprice2;
        $priceArr['optionFeeprice3'] = $optionFeeprice3;
        $priceArr['optionFeeprice4'] = $optionFeeprice4;
        $priceArr['optionFeeTotal'] = $optionFeeTotal;
        $priceArr['subTotal2'] = $subTotal2;
        $priceArr['total2'] = $total2;
        $priceArr['grp1CheckArr'] = $grp1CheckArr;
        $priceArr['grp2CheckArr'] = $grp2CheckArr;
        $priceArr['total4'] = $total4;

        return $priceArr;
    }
    
    public function saveTempRegisterData($data,$token){
        if (!empty($data['optionMenu'])) {
            $data['optionMenu'] = implode(',', array_keys($data['optionMenu']));
        }else{
            $data['optionMenu'] = null;
        }

        if (!empty($data['rankNo'])) {
            $data['rankNo'] = intval($data['rankNo']);
        }
        $data = array_map(function($v) {
            if ('' == $v) {
                return null;
            }
            return trim($v);
        }, $data);
        $tokenPolicy = array_key_exists('token', $data)?$data['token']:'';
        $dataPolicy = \Dantai\PublicSession::getData(\IBA\Controller\IBAController::IBAPolicySessionKey.$tokenPolicy);
        $dataRegister = array_merge($data, (array) $dataPolicy);
        \Dantai\PublicSession::clear(\IBA\Controller\IBAController::IBAPolicySessionKey.$tokenPolicy);
        $tokenRegister = $token;
        if( \Dantai\PublicSession::isEmpty(\IBA\Controller\IBAController::IBA_REGISTER_DATA_KEY.$token)){
            $tokenRegister = md5(uniqid(\IBA\Controller\IBAController::IBA_REGISTER_DATA_KEY));
        }else{
            $dataRegister = array_merge(\Dantai\PublicSession::getData(\IBA\Controller\IBAController::IBA_REGISTER_DATA_KEY.$token),$dataRegister);
        }
        \Dantai\PublicSession::setData(\IBA\Controller\IBAController::IBA_REGISTER_DATA_KEY.$tokenRegister, $dataRegister);
        return $tokenRegister;
    }
    
    public function getTempRegisterData($token,$isGetRaw = false){
        $data = \Dantai\PublicSession::getData(\IBA\Controller\IBAController::IBA_REGISTER_DATA_KEY.$token);
        if($isGetRaw){
            return $data;
        }
        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($this->getEntityManager(), 'Application\Entity\ApplyIBAOrg');
        $IBA = ($data['tempId'] > 0)
                ? $this->getEntityManager()->getRepository('Application\Entity\ApplyIBAOrg')->find($data['tempId']) 
                : new \Application\Entity\ApplyIBAOrg();
        return $hydrator->hydrate($data, $IBA);
    }
    
    public function getMoshikomiId($id){
        $moshikomiId = null;
        if($id > 0){
            /* @var $oldData \Application\Entity\ApplyIBAOrg */
           $oldData = $this->getEntityManager()->getRepository('Application\Entity\ApplyIBAOrg')->find($id); 
           if(!empty($oldData)){
               $moshikomiId = $oldData->getMoshikomiId();
           }
        }
        if(empty($moshikomiId)){
            $moshikomiId = $this->getEntityManager()->getRepository('Application\Entity\ApplyIBAOrg')->getNewMoshikomiIdForOrg();
        }
        return $moshikomiId;
    }
    
    public function isSendMailUpdate($id){
        if((int)$id <=0){
            return false;
        }
        $oldData = $this->getEntityManager()->getRepository('Application\Entity\ApplyIBAOrg')->find($id); 
        if(empty($oldData)){
            return false;
        }
        return $oldData->getStatus() == \IBA\Controller\IBAController::IBA_STATUS_PENDING;
    }
     public function getAPIOrg() {
        $config = $this->getServiceLocator()->get('Config')['iba_config']['api'];
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        $user = $dantaiService->getCurrentUser();
        $orgno = $user['organizationNo'];
        $result = array();
        if (!$this->uketukeClient) $this->setUketukeClient();
        try {
            $result = $this->uketukeClient->callEir2a01($config, array(
                'dantaino' => $orgno
            ));
        } catch (Exception $e) {
            throw $e;
        }
        return $result;
    }
    public function setUketukeClient($client = null){
        if($client){
            $this->uketukeClient = $client;
        }else{
            $this->uketukeClient = \Dantai\Api\UkestukeClient::getInstance();}
        }

}
