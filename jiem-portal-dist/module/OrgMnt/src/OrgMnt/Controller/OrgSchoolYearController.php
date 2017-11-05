<?php

namespace OrgMnt\Controller;

use Application\ApplicationConst;
use Application\Service\ServiceInterface\CommonServiceInterface;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Dantai\Utility\JsonModelHelper;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use OrgMnt\Form\NewSchoolYearForm;
use OrgMnt\Service\ServiceInterface\OrgSchoolYearServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class OrgSchoolYearController extends AbstractActionController
{

	protected $id_org = 0;
	
	/**
	 *
	 * @var DantaiServiceInterface
	 */
	protected $dantaiService;
	
	/**
	 *
	 * @var OrgSchoolYearServiceInterface
	 */
	protected $orgSchoolYearService;
	
	/**
	 *
	 * @var EntityManager
	 */
	protected $em;
        const KYU1 = 1;
        const PRKYU1 = 2;
        const KYU2 = 3;
        const PRKYU2 = 4;
        const KYU3 = 5;
        const KYU4 = 6;
        const KYU5 = 7;
        

    public function __construct(DantaiServiceInterface $dantaiService, OrgSchoolYearServiceInterface $orgSchoolYearService, EntityManager $entityManager)
    {
		$this->dantaiService = $dantaiService;
		$this->orgSchoolYearService = $orgSchoolYearService;
		$this->em = $entityManager;
		$user = $this->dantaiService->getCurrentUser ();
		$this->id_org = $user ['organizationId'];
	}

	public function indexAction()
	{
		$trans = $this->params()->fromQuery('trans');
		if ($trans == 1) {
			$validator = new \DoctrineModule\Validator\ObjectExists (array(
				'object_repository' => $this->em->getRepository('Application\Entity\OrgSchoolYear'),
				'fields'            => array(
					'organizationId',
					'isDelete'
				)
			));
			$checkValid = $validator->isValid(array($this->id_org, 0));
			if ($checkValid == true) {
				return $this->redirect()->toRoute(null, array('module'     => 'org-mnt', 'controller' => 'orgschoolyear', 'action'     => 'index'));
			}
			return $this->redirect()->toRoute(null, array('module'     => 'org-mnt', 'controller' => 'orgschoolyear', 'action'     => 'add'));
		}
		$page = $this->params()->fromRoute('page', 1);
		$em = $this->getEntityManager();
		$orgschoolyears = $em->getRepository('Application\Entity\OrgSchoolYear')->getPagedOrgSchoolYearList($this->id_org);
		$schoolyear = $em->getRepository('Application\Entity\SchoolYear')->ListSchoolyear();
		$translator = $this->getServiceLocator()->get('MVCTranslator');
		$jsMessages = array(
			'SYMSG004'        => $translator->translate('SYMSG004'),
			'MSG1004'         => $translator->translate('MSG1004'),
			'MSGselectdelete' => $translator->translate('MSGselectdelete')
		);
		$mess = $this->flashMessenger()->getMessages();
		if (count($mess)) {
			$mess = json_decode(current($mess),true);
		}
		return new ViewModel (array(
			'orgschoolyears' => $orgschoolyears,
			'schoolyear'     => $schoolyear,
			'mess'           => $mess,
			'page'           => $page,
			'jsMessages'     => json_encode($jsMessages)
		));
	}

    public function showAction()
    {
		$mess = false;
		$id = $this->params ( 'id' );
		$em = $this->getEntityManager ();
		$orgschoolyear = $em->getRepository ( 'Application\Entity\OrgSchoolYear' )->findOneBy ( array (
				'id' => $id,
				'organizationId' => $this->id_org,
				'isDelete' => 0 
		) );
		
		if (! isset ( $orgschoolyear )) {
			return $this->redirect ()->toRoute ( null, array (
					'module' => 'org-mnt',
					'controller' => 'orgschoolyear',
					'action' => 'index' 
			) );
		}
		
		$schoolyear_id = $orgschoolyear->getSchoolYear ();
		
		if (! empty ( $this->flashMessenger ()->getMessages () )) {
			$messs = $this->flashMessenger ()->getMessages ();
			$mess = $messs [0];
		}
		return new ViewModel ( array (
				'orgschoolyear' => $orgschoolyear,
				'schoolyear_id' => $schoolyear_id,
				'mess' => $mess 
		) );
	}

    public function addAction()
    {
		$form = new NewSchoolYearForm ();
		$em = $this->getEntityManager ();
		$schoolyear = $em->getRepository ( 'Application\Entity\SchoolYear' )->ListSchoolYear ();
		
		$yearschool = array ();
		$yearschool [''] = '';
		if (isset ( $schoolyear )) {
			foreach ( $schoolyear as $key => $value ) {
				$yearschool [$value ['id']] = $value ['name'];
			}
		}
		$form->get ( "schoolYear" )->setValueOptions ( $yearschool );
		$translator = $this->getServiceLocator ()->get ( 'MVCTranslator' );
		
		$messs = $this->flashMessenger ()->getMessages ();
		$jsonMessageHelper = JsonModelHelper::getInstance();
                $isNotShowMSG = 1;
		if (! empty ( $messs )) {
		    $errorMessages = array();
			$messs = json_decode ( $messs [0], true );
                        if(isset($messs[3])){
                            $form->setData ( $messs[3] );
                        }
			if(isset($messs[1])){
                            $jsonMessageHelper->setFail();
			    $errorMessages[] = array('id' => 'schoolYear', 'message' => $translator->translate ( 'SYMSG002' ));
                        }
			
			if(isset($messs[2])){
                            $jsonMessageHelper->setFail();
			    $errorMessages[] = array('id' => 'displayName', 'message' => $translator->translate ( 'SYMSG003' ));
                        }
                        if(isset($messs[4])){
			    $isNotShowMSG = 0;
                        }
			
			$jsonMessageHelper->setData($errorMessages);
		}
		return array (
				'form' => $form,
				'jsMessages' => $jsonMessageHelper->jsonSerialize(),
                                'isNotShowMSG' => $isNotShowMSG
                                
		);
	}

    public function saveAction()
    {
		$form = new NewSchoolYearForm ();
		$mess = array ();
		$em = $this->getEntityManager ();
		$orgschoolyear = new \Application\Entity\OrgSchoolYear ();
		
		$data = $this->params ()->fromPost ();
		
		$form->setData ( $data );
		
		$schoolyear = $em->getReference ( 'Application\Entity\SchoolYear', array (
				'id' => $data ['schoolYear'] 
		) );
		
		$dn = $this->remove_special_characters ( trim ( $data ['displayName'] ) );
		
		// Check duplicate when create new
		$repository = $em->getRepository ( 'Application\Entity\OrgSchoolYear' );
		
		$validator_schoolyear = new \DoctrineModule\Validator\ObjectExists ( array (
				'object_repository' => $repository,
				'fields' => array (
						'isDelete',
						'organizationId',
						'schoolYear' 
				) 
		) );
		$check_schoolyear = $validator_schoolyear->isValid ( array (
				0,
				$this->id_org,
				$data ['schoolYear'] 
		) );
		
                $displayName = $repository->getGradeByDisplayName($this->id_org,trim($dn));
                $check_displayname = $displayName ? true : false;
                
                // show msg for warning Grade Class
                $isNotShowMSG = true;
                
                if($data['confirmSave'] == 0){
                    $arrGradeInput = array($dn);
                    $gradeObj = $this->getEntityManager()->getRepository('Application\Entity\OrgSchoolYear')
                                    ->findBy(array(
                                                'organizationId' => $this->id_org,
                                                'isDelete' => 0
                                                ));
                    $arrGrade = array();
                    if($gradeObj){
                        /*@var $row \Application\Entity\OrgSchoolYear*/
                        foreach ($gradeObj as $row){
                            array_push($arrGrade, $row->getDisplayName()); 
                        }
                    }
                    $isNotShowMSG = $this->dantaiService->isAlphanumericCharacter($arrGrade,ApplicationConst::GRADE_TYPE, $arrGradeInput);
                }
		if ($check_schoolyear && $check_displayname) {
			$mess [1] = "SYMSG002";
			$mess [2] = "SYMSG003";
			$mess [3] = $data;
			$messjon = json_encode ( $mess );
			$this->flashMessenger ()->addMessage ( $messjon );
			return $this->redirect ()->toRoute ( 'org-mnt/default', array (
					'controller' => 'orgschoolyear',
					'action' => 'add' 
			) );
		} elseif ($check_schoolyear) {
			$mess [1] = "SYMSG002";
			$mess [3] = $data;
			$messjon = json_encode ( $mess );
			$this->flashMessenger ()->addMessage ( $messjon );
			return $this->redirect ()->toRoute ( 'org-mnt/default', array (
					'controller' => 'orgschoolyear',
					'action' => 'add' 
			) );
		} elseif ($check_displayname) {
			$mess [2] = "SYMSG003";
			$mess [3] = $data;
			$messjon = json_encode ( $mess );
			$this->flashMessenger ()->addMessage ( $messjon );
			return $this->redirect ()->toRoute ( 'org-mnt/default', array (
					'controller' => 'orgschoolyear',
					'action' => 'add' 
			) );
		}elseif ($isNotShowMSG == false) {
			$mess [4] = "MSGPopupWarningGradeClass";
			$mess [3] = $data;
			$messjon = json_encode ( $mess );
			$this->flashMessenger ()->addMessage ( $messjon );
			return $this->redirect ()->toRoute ( 'org-mnt/default', array (
					'controller' => 'orgschoolyear',
					'action' => 'add' 
			) );
		}else {
			
			// Set data, submit to server and redirect to index page
			$org = $em->getReference ( 'Application\Entity\Organization', array (
					'id' => $this->id_org 
			) );
			if (empty ( $dn )) {
				$displayName = $schoolyear->getName ();
			} else {
				$displayName = $this->remove_special_characters ( trim ( $data ['displayName'] ) );
			}
			
			$orgschoolyear->setSchoolYear ( $schoolyear );
			$orgschoolyear->setDisplayName ( $displayName );
			$orgschoolyear->setOrganization ( $org );
			$orgschoolyear->setOrdinal ( 1 );
			
			$em->persist ( $orgschoolyear );
			
			$em->flush ();
			/* Add OrgId to sqs queue  */
			$year = $this->dantaiService->getCurrentYear();
			$this->dantaiService->addOrgToQueue($this->id_org, $year);
                        
			return $this->redirect ()->toRoute ( null, array (
					'module' => 'org-mnt',
					'controller' => 'orgschoolyear',
					'action' => 'index' 
			) );
		}
	}

    public function editAction()
    {
		$mess1 = false;
		$mess2 = false;
                $isNotShowMSG = 1;
		
		$form = new NewSchoolYearForm ();
		$form->get ( 'submit' )->setAttribute ( 'value', 'Edit' );
		$em = $this->getEntityManager ();
		$id = $this->params ()->fromRoute ( 'id', 0 );
		
		$orgschoolyear = $em->getRepository ( 'Application\Entity\OrgSchoolYear' )->findOneBy ( array (
				'id' => $id,
				'organizationId' => $this->id_org,
				'isDelete' => 0 
		) );
		
        if (! $orgschoolyear) {
			return $this->redirect ()->toRoute ( null, array (
					'module' => 'org-mnt',
					'controller' => 'orgschoolyear',
					'action' => 'index' 
			) );
		}
		
//         $this->dantaiService->startCrossEditing($orgschoolyear);
        
		$schoolyear = $em->getRepository ( 'Application\Entity\SchoolYear' )->ListSchoolyear ();
		
		$yearschool = array ();
		$yearschool [''] = '';
		if (isset ( $schoolyear )) {
			foreach ( $schoolyear as $key => $value ) {
				$yearschool [$value ['id']] = $value ['name'];
			}
		}
		$form->get ( 'schoolYear' )->setValueOptions ( $yearschool );
		
		$form->setHydrator ( new DoctrineHydrator ( $em, 'Application\Entity\OrgSchoolYear' ) );
		$form->bind ( $orgschoolyear );
		
		$translator = $this->getServiceLocator ()->get ( 'MVCTranslator' );
        $messages = $this->flashMessenger()->getMessages();
				$jsMessages = array (
						'SYMSG002' => '',
						'SYMSG003' => '' 
				);
        if (!empty($messages)) {
            $messages = json_decode($messages[0], true);
            $dataform = $messages[3];
            $form->setData($dataform);
            
            if(array_key_exists(1, $messages))
                $jsMessages['SYMSG002'] = $translator->translate('SYMSG002');
            
            if(array_key_exists(2, $messages))
                $jsMessages['SYMSG003'] = $translator->translate('SYMSG003');
            if(array_key_exists(4, $messages))
                $isNotShowMSG = 0;
			}
            
		
        $crossEditMessages = $this->dantaiService->restoreCrossEditingForm($orgschoolyear, $form);
        $jsMessages['conflictWarning'] = $crossEditMessages['conflictWarning'];
        $jsMessages['conflictType'] = $crossEditMessages['conflictType'];
        
		return array (
				'form' => $form,
				'id' => $id,
				'mess1' => $mess1,
				'mess2' => $mess2,
				'jsMessages' => json_encode ( $jsMessages ),
                                'isNotShowMSG' => $isNotShowMSG
		);
	}

    public function updateAction()
    {
		$form = new NewSchoolYearForm ();
		$mess = array ();
		$id = $this->params ()->fromRoute ( 'id', 0 );
		$em = $this->getEntityManager ();
		$request = $this->getRequest ();
		
		if ($request->isPost ()) {
			$data = $this->params ()->fromPost ();
			
			$schoolyear = $em->getReference ( 'Application\Entity\SchoolYear', array (
					'id' => $data ['schoolYear'] 
			) );
            $orgSchoolYear = $em->getRepository('Application\Entity\OrgSchoolYear')->findOneBy(array(
					'id' => $id,
                'organizationId' => $this->id_org
			) );
			
            // Cross edit ???
//             $crossMessages = $this->dantaiService->checkCrossEditing($orgSchoolYear, null, $data);
//             if($crossMessages['conflictWarning']){
//                 $redirect = array(
//                     'controller' => 'orgschoolyear',
//                     'action' => 'index'
//                 );
//                 if($crossMessages['conflictType'] == 'edit'){
//                     $redirect['action'] = 'edit';
//                     $redirect['id'] = $id;
//                 }
            
//                 return $this->redirect()->toRoute('org-mnt/default', $redirect);
//             }
            
			$dn = $this->remove_special_characters ( trim ( $data ['displayName'] ) );
			// $org = $em->getReference('Application\Entity\Organization', $this->id_org);
			
            $currentDisplayName = $orgSchoolYear->getDisplayName();
            $currentSchoolYear = $orgSchoolYear->getSchoolYear()->getId();
			
            /**
             * @var \Application\Entity\Repository\OrgSchoolYearRepository
             */
            $orgSchoolYearRepo = $em->getRepository('Application\Entity\OrgSchoolYear');
            $schoolYearExist = $orgSchoolYearRepo->checkDuplicate($id, array(
                'isDelete' => 0, 
                'organizationId' => $this->id_org,
                'schoolYear' => $data['schoolYear']
			) );
			
            
            $displayName = $orgSchoolYearRepo->getGradeByDisplayName($this->id_org,trim($dn),$id);
            $displayNameExist = $displayName ? true : false;
            // Not modified
            if ($currentSchoolYear == $data ['schoolYear'] && $currentDisplayName == trim ( $data ['displayName'] )) {
                if($data['confirmSave'] === 1){
                    return $this->redirect ()->toRoute ( 'org-mnt/default', array (
                                    'controller' => 'orgschoolyear',
                                    'action' => 'index' 
                    ) );
                }else{
                    // show msg for warning Grade Class
                    $isNotShowMSG = true;

                    if($data['confirmSave'] == 0){
                        $arrGradeInput = array(0=>$dn);
                        $gradeObj = $this->getEntityManager()->getRepository('Application\Entity\OrgSchoolYear')->findBy(array(
                        'organizationId' => $this->id_org,
                        'isDelete' => 0
                        ));
                        $arrGrade = array();
                        if($gradeObj){
                            /*@var $row \Application\Entity\OrgSchoolYear*/
                            foreach ($gradeObj as $row){
                                if($id != $row->getId())
                                array_push($arrGrade, $row->getDisplayName()); 
                            }
                        }
                        $isNotShowMSG = $this->dantaiService->isAlphanumericCharacter($arrGrade,ApplicationConst::GRADE_TYPE, $arrGradeInput); 
                   }
                    if(count($mess) === 0){
                        if ($isNotShowMSG == false) {
                            $mess [4] = "MSGPopupWarningGradeClass";
                        }
                    }
                    if (count($mess)) {
					$mess [3] = $data;
					$messjon = json_encode ( $mess );
					$this->flashMessenger ()->addMessage ( $messjon );
					return $this->redirect ()->toRoute ( 'org-mnt/default', array (
							'controller' => 'orgschoolyear',
							'action' => 'edit',
							'id' => $id 
					) );
                }
                }
            }
            else
            {
                                
                // show msg for warning Grade Class
                $isNotShowMSG = true;
                
                if($data['confirmSave'] == 0){
                    $arrGradeInput = array(0=>$dn);
                    $gradeObj = $this->getEntityManager()->getRepository('Application\Entity\OrgSchoolYear')->findBy(array(
                    'organizationId' => $this->id_org,
                    'isDelete' => 0
                    ));
                    $arrGrade = array();
                    if($gradeObj){
                        /*@var $row \Application\Entity\OrgSchoolYear*/
                        foreach ($gradeObj as $row){
                            if($id != $row->getId())
                            array_push($arrGrade, $row->getDisplayName()); 
                        }
                    }
                    $isNotShowMSG = $this->dantaiService->isAlphanumericCharacter($arrGrade,ApplicationConst::GRADE_TYPE, $arrGradeInput);
                }
                if ($schoolYearExist) {
    			     $mess [1] = "SYMSG002";
    			}
                if ($displayNameExist) {
                    $mess[2] = "SYMSG003";
				}
                if(count($mess) === 0){
                    if ($isNotShowMSG == false) {
			$mess [4] = "MSGPopupWarningGradeClass";
                    }
                }
                if (count($mess)) {
                    
					$mess [3] = $data;
					$messjon = json_encode ( $mess );
					$this->flashMessenger ()->addMessage ( $messjon );
					return $this->redirect ()->toRoute ( 'org-mnt/default', array (
							'controller' => 'orgschoolyear',
							'action' => 'edit',
							'id' => $id 
					) );
                }
    					
    			// Set data, submit to server and redirect to index page
    			$schoolyear = $em->getReference ( 'Application\Entity\SchoolYear', array (
    					'id' => $data ['schoolYear'] 
    			) );
    			$org = $em->getReference ( 'Application\Entity\Organization', array (
    					'id' => $this->id_org 
    			) );
    			
    			if (empty ( $dn )) {
    				$displayName = $schoolyear->getName ();
    			} else {
    				$displayName = $this->remove_special_characters ( trim ( $data ['displayName'] ) );
    			}
    					
                $orgSchoolYear->setDisplayName($displayName);
    					
                $orgSchoolYear->setSchoolYear($schoolyear);
                $orgSchoolYear->setOrganization($org);
                $orgSchoolYear->setOrdinal(1);
                $em->persist($orgSchoolYear);
				$em->flush ();
				$em->clear ();
            }
        
			return $this->redirect ()->toRoute ( null, array (
					'module' => 'org-mnt',
					'controller' => 'orgschoolyear',
					'action' => 'index' 
			) );
		}
    }

	public function deleteAction()
	{
		$listGradeId = array_keys($this->params()->fromPost('input'));
		$gradeId = $this->params('id', 0);
		if ($gradeId) {
			$listGradeId = array($gradeId);
		}
		$listGradeNotDelete = array();
		foreach ($listGradeId as $gradeId) {
			$pupil = $this->getEntityManager()->getRepository('Application\Entity\Pupil')->findOneBy(array('organizationId' => $this->id_org, 'isDelete' => 0, 'orgSchoolYearId' => (int)$gradeId));
			if ($pupil) {
				$listGradeNotDelete[] = $gradeId;
			}
			else {
				$orgSchoolYear = $this->getEntityManager()->getRepository('Application\Entity\OrgSchoolYear')->findOneBy(array(
					'id'             => $gradeId,
					'organizationId' => $this->id_org
				));
                if ($orgSchoolYear) {
                    $orgSchoolYear->setIsDelete(1);
                    $this->getEntityManager()->flush();
                    $this->addOrgInQueue($this->id_org, $gradeId);
                    $this->getEntityManager()->getRepository('Application\Entity\ClassJ')->updateIsDeleteClass($this->id_org, (int)$gradeId);
                    $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenStudent')->updateIsDeleteApplyEikenStudent((int)$gradeId);
                    $this->getEntityManager()->getRepository('Application\Entity\StandardLevelSetting')->deleteDataByOrgAndOrgSchoolYearId($this->id_org, (int)$gradeId);
                    $listAppEikenStudent = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenStudent')->getApplyEikenStudentByGrade((int)$gradeId, \Eiken\EikenConst::HASS_TYPE_IS_STANDER_HALL);
                    if (!empty($listAppEikenStudent)) {
                        /* @var $applyDetailRepo \Application\Entity\Repository\ApplyEikenOrgDetailsRepository */
                        $applyDetailRepo = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenOrgDetails');
                        foreach ($listAppEikenStudent as $list => $item) {
                            switch ($item['eikenLevelId']) {
                                case self::KYU1 :
                                    $eikenLevel = array('Columlevel' => 'lev1', 'Vallevel' => $item['lev1'], 'ColumName' => 'discountLev1', 'Value' => $item['discountLev1']);
                                    break;
                                case self::PRKYU1 :
                                    $eikenLevel = array('Columlevel' => 'preLev1', 'Vallevel' => $item['preLev1'], 'ColumName' => 'discountPreLev1', 'Value' => $item['discountPreLev1']);
                                    break;
                                case self::KYU2 :
                                    $eikenLevel = array('Columlevel' => 'lev2', 'Vallevel' => $item['lev2'], 'ColumName' => 'discountLev2', 'Value' => $item['discountLev2']);
                                    break;
                                case self::PRKYU2 :
                                    $eikenLevel = array('Columlevel' => 'preLev2', 'Vallevel' => $item['preLev2'], 'ColumName' => 'discountPreLev2', 'Value' => $item['discountPreLev2']);
                                    break;
                                case self::KYU3 :
                                    $eikenLevel = array('Columlevel' => 'lev3', 'Vallevel' => $item['lev3'], 'ColumName' => 'discountLev3', 'Value' => $item['discountLev3']);
                                    break;
                                case self::KYU4 :
                                    $eikenLevel = array('Columlevel' => 'lev4', 'Vallevel' => $item['lev4'], 'ColumName' => 'discountLev4', 'Value' => $item['discountLev4']);
                                    break;
                                case self::KYU5 :
                                    $eikenLevel = array('Columlevel' => 'lev5', 'Vallevel' => $item['lev5'], 'ColumName' => 'discountLev5', 'Value' => $item['discountLev5']);
                                    break;
                                default :
                                    $eikenLevel = [];
                                    break;
                            }
                            if ((int)$eikenLevel['Value'] >= (int)$item['totalStudent'] && (int)$eikenLevel['Vallevel'] >= (int)$item['totalStudent']) {
                                $eikenLevel['Vallevel'] = (int)$eikenLevel['Vallevel'] - (int)$item['totalStudent'];
                                $eikenLevel['Value'] = (int)$eikenLevel['Value'] - (int)$item['totalStudent'];
                                $applyDetailRepo->updateDiscountKyu($item['id'], $eikenLevel);
                            }
                        }
                    }

                    /* Add OrgId to sqs queue  */
                    $year = $this->dantaiService->getCurrentYear();
                    $this->dantaiService->addOrgToQueue($this->id_org, $year);
                }
			}
		}
		if ($listGradeNotDelete) {
			$mess['arrId'] = $listGradeNotDelete;
			$mess['mess'] = 'SYMSG004';
		}
                if(isset($mess)){
                    $this->flashMessenger()->addMessage(json_encode($mess));
                }

		return $this->redirect()->toRoute(null, array('module' => 'org-mnt', 'controller' => 'orgschoolyear', 'action' => 'index'));
	}
        
    public function getEntityManager()
    {
		return $this->getServiceLocator ()->get ( 'doctrine.entitymanager.orm_default' );
	}

    public function remove_special_characters($string)
    {
		$string = str_replace ( array (
				"'",
				'"' 
		), array (
				"",
				"" 
		), $string );
		return $string;
	}

	private function addOrgInQueue($orgId, $orgSchoolYearId){
        $listYears = $this->getEntityManager()->getRepository('Application\Entity\ClassJ')
            ->getListClassYearByOrgSchoolYear($orgSchoolYearId);
        foreach ($listYears as $year){
            /* Add OrgId to sqs queue  */
            $this->dantaiService->addOrgToQueue($orgId, $year);
        }
    }
}