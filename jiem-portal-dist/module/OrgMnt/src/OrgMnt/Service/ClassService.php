<?php
namespace OrgMnt\Service;

use OrgMnt\Service\ServiceInterface\ClassServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Dantai\PrivateSession;
use OrgMnt\Form\AddFormClass;
use OrgMnt\Form\ClasssearchForm;
use OrgMnt\Form\ClassEditForm;
use Doctrine\ORM\EntityManager;
use Zend\Json\Json;
use Dantai\Utility\DateHelper;
use PupilMnt\Service\ServiceInterface\ImportPupilServiceInterface;
use Application\ApplicationConst;

class ClassService implements ClassServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    private $id_org;

    protected $em;

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function __construct()
    {
        $user = PrivateSession::getData('userIdentity');
        $this->id_org = $user['organizationId'];
    }

    public function getPagedListClass($event, $routeMatch, $request, $params, $flashMessenger, $dantaiService)
    {
        $em = $this->getEntityManager();
        $sm = $event->getApplication()->getServiceManager();
        $redirect = $sm->get('ControllerPluginManager')->get('redirect');
        
        $searchYear = '';
        $searchSchool = '';
        $searchCriteria = $dantaiService->getSearchCriteria($event, $params
            ->fromPost());
        if ($request->isPost() && $searchCriteria['token']) {
            return $redirect->toUrl('/org/class/index/search/' . $searchCriteria['token']);
        }
        if($searchCriteria)
        {
            $searchYear = isset($searchCriteria['year'])?$searchCriteria['year']:$this->year();
            $searchSchool = isset($searchCriteria['school_year_add'])?$searchCriteria['school_year_add']:'';
        }
        $form = $this->getFormSearch($searchYear, $searchSchool, $em);
        $mess = $flashMessenger->getMessages();
        if (! empty($mess)) {
            $mess = json_decode($mess[0]);
        }
        
        //$crossMessages = $dantaiService->getCrossEditingMessage('Application\Entity\ClassJ');
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsMessages = array(
            'MSG1003' => $translator->translate('MSG1003'),
            'MSG1004' => $translator->translate('MSG1004'),
            'MSG1063' => $translator->translate('MSG1063')
//            'conflictWarning' => $crossMessages['conflictWarning'],
//            'conflictType' => $crossMessages['conflictType']
        );
        $page = $params->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $class = $em->getRepository('Application\Entity\ClassJ')->getPagedClassList($limit, $offset, $searchYear, $searchSchool, $this->id_org);
        return array(
            'classj' => $class,
            'form' => $form,
            'mess' => $mess,
            'jsMessages' => json_encode($jsMessages),
            'page' => $page,
            'numPerPage' => $limit,
            'searchVisible' => empty($search)? 0 : 1,
            'param' => isset($searchCriteria['token']) ? $searchCriteria['token'] : ''
        );
    }

    public function getFormSearch($searchYear, $searchSchool, $em)
    {
        $form = new ClasssearchForm();
        $objSchoolyear = $em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->id_org);
        $yearschool = array();
        if (isset($objSchoolyear)) {
            $yearschool[''] = '';
            foreach ($objSchoolyear as $key => $value) {
                $yearschool[$value['id']] = $value['displayName'];
            }
        }
        if ($searchSchool) {
            $form->get("school_year_add")
                ->setValueOptions($yearschool)
                ->setAttributes(array(
                'value' => $searchSchool,
                'selected' => true,
                'escape' => false
            ));
        } else {
            $form->get("school_year_add")
                ->setValueOptions($yearschool)
                ->setAttributes(array(
                'value' => '',
                'selected' => true,
                'escape' => false
            ));
        }
        if ($searchYear) {
            $form->get("year")
                ->setValueOptions($this->year())
                ->setAttributes(array(
                'value' => $searchYear,
                'selected' => true,
                'escape' => false
            ));
        } else {
            $form->get("year")
                ->setValueOptions($this->year())
                ->setAttributes(array(
                'value' => date('Y'),
                'selected' => true,
                'escape' => false
            ));
        }
        return $form;
    }

    function getDetailClass($id, $flashMessenger)
    {
        $mess = false;
        $em = $this->getEntityManager();
        $detail = $em->getRepository('Application\Entity\ClassJ')->getDetail($id, $this->id_org);
        if (! isset($detail)) {
            return 1;
        }
        if (! empty($flashMessenger->getMessages())) {
            $messs = $flashMessenger->getMessages();
            $mess = $messs[0];
        }
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsMessages = array(
            'MSG1003' => $translator->translate('MSG1003'),
            'MSG1005' => $translator->translate('MSG1005')
        );
        return array(
            'detail' => $detail,
            'mess' => $mess,
            'jsMessages' => json_encode($jsMessages)
        );
    }

    public function getAddClass()
    {
        $form = new AddFormClass();
        $em = $this->getEntityManager();
        $objSchoolyear = $em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->id_org);
        $yearschool = array();
        if (isset($objSchoolyear)) {
            $yearschool[''] = '';
            foreach ($objSchoolyear as $key => $value) {
                $yearschool[$value['id']] = $value['displayName'];
            }
        }
        $form->get("school_year_add")
            ->setValueOptions($yearschool)
            ->setAttributes(array(
            'value' => '',
            'selected' => true,
            'escape' => false
        ));
        $form->get("year")
            ->setValueOptions($this->year())
            ->setAttributes(array(
            'value' => DateHelper::getCurrentYear(),
            'selected' => true,
            'escape' => false
        ));
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsMessages = array(
            'MSG001' => $translator->translate('MSG001'),
            'MSG014' => $translator->translate('MSG014'),
            'MSG015' => $translator->translate('MSG015'),
            'MSG040' => $translator->translate('MSG040'),
            'MSG046' => $translator->translate('MSG046'),
            'MSG999' => $translator->translate('MSG999'),
            'MSG1000' => $translator->translate('MSG1000'),
            'MSG1001' => $translator->translate('MSG1001'),
            'MSG1005' => $translator->translate('MSG1005'),
            'MSG0141' => $translator->translate('MSG0141')
        );
        return array(
            'form_class' => $form,
            'jsMessages' => json_encode($jsMessages)
        );
    }

    public function getSaveClass($request)
    {
        $em = $this->getEntityManager();
        $class = new \Application\Entity\ClassJ();
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        if ($request->isPost()) {
            $data = $request->getPost();
            foreach ($data['items'] as $k => $v) {
                if (empty($v['year_date'])) {
                    $v['year_date'] = '';
                }
                $data['classname'] = addslashes($data['classname']);
                $repository = $em->getRepository('Application\Entity\OrgSchoolYear');
                $validator = new \DoctrineModule\Validator\ObjectExists(array(
                    'object_repository' => $repository,
                    'fields' => array(
                        'id',
                        'organization',
                        'isDelete'
                    )
                ));
                $checkSchoolYear = $validator->isValid(array(
                    'id' => (int) $v['year_date'],
                    'organization' => $this->id_org,
                    'isDelete' => 0
                ));
                if ($checkSchoolYear === false) {
                    return 1;
                }
                $v['class_name'] = trim($v['class_name']);
                $v['size_class'] = trim($v['size_class']);
                if ($v['size_class'] == '') {
                    $v['size_class'] = 0;
                }
                $class->setClassName($v['class_name']);
                $class->setNumberOfStudent($v['size_class']);
                $class->setYear($v['year']);
                $shyear = $em->getReference('Application\Entity\OrgSchoolYear', $v['year_date']);
                $or = $em->getReference('Application\Entity\Organization', $this->id_org);
                $class->setOrganization($or);
                $class->setOrgSchoolYear($shyear);
                $em->persist($class);
                $em->flush();
                $em->clear();
                $dantaiService->addOrgToQueue($this->id_org, $v['year']);
            }
            $em->flush();
            $em->clear();

        }
    }

    public function getEditByClass($id, $request, $params, $flashMessenger)
    {
        $mess = false;
        $em = $this->getEntityManager();
        $detail = $em->getRepository('Application\Entity\ClassJ')->getDetail($id, $this->id_org);
        if (empty($detail)) {
            return 1;
        }
        $em->refresh($detail);
        
        /**
         *
         * @var \Application\Service\ServiceInterface\DantaiServiceInterface
         */
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        $dantaiService->startCrossEditing($detail);
        
        $form = new ClassEditForm();
        $form->get("classname")->setValue($detail->getClassName());
        $form->get("sizes")->setValue($detail->getNumberOfStudent());
        $listyear = $this->year();
        $objSchoolyear = $em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->id_org);
        $yearschool = array();
        if (! empty($objSchoolyear)) {
            $yearschool[''] = '';
            foreach ($objSchoolyear as $key => $value) {
                $yearschool[$value['id']] = $value['displayName'];
            }
        }
        $form->get("school_year")
            ->setValueOptions($yearschool)
            ->setAttributes(array(
            'value' => $detail->getOrgSchoolYear()
                ->getID(),
            'selected' => true,
            'escape' => false
        ));
        $form->get("year")
            ->setValueOptions($listyear)
            ->setAttributes(array(
            'value' => $detail->getYear(),
            'selected' => true,
            'escape' => false
        ));
        
        $previousData = $flashMessenger->getMessages();
        if (count($previousData)) {
            $previousData = json_decode($previousData[0], true);
            $form->setData($previousData['data']);
            $previousData = $previousData['mess'];
        }
        else {
            $previousData = '';
        }
        
       // $crossMessages = $dantaiService->restoreCrossEditingForm($detail, $form);
        
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsMessages = array(
            'MSG001' => $translator->translate('MSG001'),
            'MSG0141' => $translator->translate('MSG0141'),
            'MSG014' => $translator->translate('MSG014'),
            'MSG040' => $translator->translate('MSG040'),
            'MSG046' => $translator->translate('MSG046'),
            'MSG999' => $translator->translate('MSG999'),
            'MSG1000' => $translator->translate('MSG1000'),
            'MSG1001' => $translator->translate('MSG1001'),
            'MSG1005' => $translator->translate('MSG1005')
//            'conflictWarning' => $crossMessages['conflictWarning'],
//            'conflictType' => $crossMessages['conflictType']
        );
        
        return array(
            'id' => $id,
            'form_class' => $form,
            'mess' => $previousData,
            'jsMessages' => json_encode($jsMessages)
        );
    }

    public function getUpdateClass($request, $params, $flashMessenger)
    {
        $return = array(
            'module' => 'org-mnt',
            'controller' => 'class',
            'action' => 'index'
        );
        
        $em = $this->getEntityManager();
        $mess = array();
        if ($request->isPost()) {
            $data = $params->fromPost();
            $check = $em->getRepository('Application\Entity\ClassJ')->getCheckDuplicate($data, $this->id_org);
            if ($check) {
                $mess['data'] = $data;
                $mess['mess'] = 'MSG1005';
                $mess = Json::encode($mess);
                $flashMessenger->addMessage($mess);
                
                $return['action'] = 'edit';
                $return['id'] = $data['id'];
                
                return $return;
            }
            $class = $em->getReference('Application\Entity\ClassJ', array(
                'id' => (int) $data['id']
            ));
            $em->refresh($class);
            $oldNumberStudent = $class->getNumberOfStudent() !== Null ? $class->getNumberOfStudent() : 0;
            /**
             * @var \Application\Service\ServiceInterface\DantaiServiceInterface
             */
           $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
           //Uthv Delete cross edit
            //  $crossMessages = $dantaiService->checkCrossEditing($class, null, $data);
//            if($crossMessages['conflictWarning'])
//            {
//                if('edit' == $crossMessages['conflictType']){
//                    $return['action'] = 'edit';
//                    $return['id'] = $data['id'];
//                }
//                
//                return $return;
//            }

            $data['classname'] = trim($data['classname']);
            $data['sizes'] = (int) trim($data['sizes']);
            $data['year'] = (int) trim($data['year']);
            $class->setClassName($data['classname']);
            $class->setNumberOfStudent($data['sizes']);
            $class->setYear($data['year']);
            $objSchoolYear = $em->getReference('Application\Entity\OrgSchoolYear', $data['school_year']);
            $objOrganization = $em->getReference('Application\Entity\Organization', $this->id_org);
            $class->setOrganization($objOrganization);
            $class->setOrgSchoolYear($objSchoolYear);
            $em->persist($class);
            $em->flush();
            if($oldNumberStudent != $data['sizes']){
                $dantaiService->addOrgToQueue($this->id_org, $data['year']);
            }
        }
        
        return $return;
    }

    public function checkaddslashes($str)
    {
        if (strpos(str_replace("\'/", "", " $str"), "'") != false)
            return addslashes($str);
        else
            return $str;
    }

    public function getDeleteClass($id, $request, $params, $flashMessenger)
    {
        $flag = 0;
        $em = $this->getEntityManager();
        $mess = false;
        $arr = array();
        $arrayCheck = array();
        $data = $params->fromPost('input');
        $checkdc = false;
        if (! empty($data)) {
            $dataId = array_keys($data);
        }
        if (! empty($dataId)) {
            $checks = $em->getRepository('Application\Entity\Pupil')->findby(array(
                'class' => $dataId,
                'isDelete' => 0,
                'organization' => $this->id_org
            ));
            if (! empty($checks)) {
                foreach ($checks as $k => $v) {
                    $arrayCheck[$v->getClass()->getId()] = $v->getClass()->getId();
                }
                $arr = array_diff($dataId, $arrayCheck);
                $mess = json_encode($arrayCheck);
            } else {
                $arr = $dataId;
            }
        }
        if (isset($id) && $id != 0) {
            $repository = $em->getRepository('Application\Entity\Pupil');
            $validator = new \DoctrineModule\Validator\ObjectExists(array(
                'object_repository' => $repository,
                'fields' => array(
                    'class',
                    'organization',
                    'isDelete'
                )
            ));
            
            $checkdc = $validator->isValid(array(
                'class' => $id,
                'organization' => $this->id_org,
                'isDelete' => 0
            ));
            if ($checkdc) {
                $mess = 'MSG1005';
                $flashMessenger->addMessage($mess);
                $datas = array();
                $datas['redilect'] = true;
                $datas['data'] = array(
                    'module' => 'org-mnt',
                    'controller' => 'class',
                    'action' => 'show',
                    'id' => $id
                );
                return $datas;
            }
        }
        // $this->removeClass($id, $arr, $checkdc);
        $checkremove = $this->removeClassInsert($id, $arr, $checkdc, $this->id_org);
        if ($checkremove == 'noremove') {
            return 1;
        }
        
        $flashMessenger->addMessage($mess);
    }

    public function checkremove($id = false)
    {
        $em = $this->getEntityManager();
        if (empty($id)) {
            $id = '';
        }
        $repository = $em->getRepository('Application\Entity\Classj');
        $validator = new \DoctrineModule\Validator\ObjectExists(array(
            'object_repository' => $repository,
            'fields' => array(
                'id',
                'organization',
                'isDelete'
            )
        ));
        $check = $validator->isValid(array(
            'id' => (int) $id,
            'organization' => $this->id_org,
            'isDelete' => 0
        ));
        if ($check === false) {
            return 'noremove';
        }
    }

    public function convertdata($data)
    {
        $array_data = array();
        $importPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        if (! empty($data)) {
            foreach ($data as $key => $value) {
                $id = $value[3];
                unset($value[3]);
                $array_data[$id] = $value;
            }
        }

        return $array_data;
    }

    public function getCheckDupliCate($params, $Response)
    {
        $data = array();
        $em = $this->getEntityManager();
        $data = $params->fromPost('data');
        $data = $this->convertdata($data);
        if (isset($data)) {
            $repository = $em->getRepository('Application\Entity\ClassJ');
            $validator = new \DoctrineModule\Validator\ObjectExists(array(
                'object_repository' => $repository,
                'fields' => array(
                    'year',
                    'orgSchoolYear',
                    'className',
                    'organization',
                    'isDelete'
                )
            ));
            $row = array();
            $checckarr = array();
            $checckarr = $this->checkDuplicatearray($data);
            if (! empty($checckarr)) {
                return $Response->setContent(Json::encode($checckarr));
            }
            if (! empty($data)) {
                foreach ($data as $k => $v) {
                    if (! empty($v)) {
                        $v[2] = trim($v[2]);
                        $check = $validator->isValid(array(
                            'year' => (int) $v[0],
                            'orgSchoolYear' => (int) $v[1],
                            'className' => $v[2],
                            'organization' => (int) $this->id_org,
                            'isDelete' => 0
                        ));
                        if ($check) {
                            $row[$k] = $k;
                        }
                    }
                }
            }
            if (empty($row)) {
                $row = false;
            }
            return $Response->setContent(Json::encode($row));
        }
    }

    public function checkDuplicatearray($array)
    {
        $new = array();
        $row = array();
        $arr_check = array();

        $importPupilService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        foreach ($array as $key=>$value){
            $string = $importPupilService->convertCharHalfWidthToFullWidth($value[2]);
            $array[$key][2] = $importPupilService->convertKanaHalfWidthToFullWidth($string);
        }

        foreach ($array as $k => $na) {
            $new[$k] = serialize($na);
        }

        $uniq = array_unique($new);
        $uniq = array_keys($uniq);
        $array = array_keys($array);
        if (! empty($uniq) && ! empty($array)) {
            foreach ($array as $key => $value) {
                if (! in_array($value, $uniq)) {
                    $row[$value] = $value;
                }
            }
        } else {
            $row = false;
        }

        return $row;
    }

    function removeClassInsert($id, $arr, $checkdc, $orgId)
    {
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        $em = $this->getEntityManager();
        if (! empty($id) && $id > 0 && $checkdc == false) {
            $checkremove = $this->checkremove($id);
            if ($checkremove == 'noremove') {
                return $checkremove;
            }
            $classj = $em->getReference('Application\Entity\ClassJ', array(
                'id' => $id
            ));
            $classj->setIsDelete(1);
            $em->flush();
            $dantaiService->addOrgToQueue($orgId, $classj->getYear());
        }
        if (! empty($arr) && $arr != 0) {
            foreach ($arr as $k => $v) {
                $checkremove = $this->checkremove($v);
                if ($checkremove == 'noremove') {
                    return $checkremove;
                }
                $classj = $em->getReference('Application\Entity\ClassJ', array(
                    'id' => (int) $v
                ));
                $classj->setIsDelete(1);
                $em->flush();
                $dantaiService->addOrgToQueue($orgId, $classj->getYear());
            }
        }
    }

    function removeClass($id, $arr, $checkdc)
    {
        $em = $this->getEntityManager();
        if (isset($id) && $id != 0 && $checkdc == false) {
            $classj = $em->getReference('Application\Entity\ClassJ', array(
                'id' => $id
            ));
            $classj->setIsDelete(1);
            $em->flush();
        }
        if (! empty($arr)) {
            foreach ($arr as $k => $v) {
                $classj = $em->getReference('Application\Entity\ClassJ', array(
                    'id' => (int) $v
                ));
                $classj->setIsDelete(1);
                $em->flush();
            }
        }
    }

    public function remove_special_characters($string)
    {
        $string = str_replace(array(
            "'",
            '"'
        ), array(
            "",
            ""
        ), $string);
        return $string;
    }

    public function year()
    {
        $listyear = array();
        $listYear[''] = '';
        $y = (int) date('Y') + 3;
        for ($i = 2010; $i < $y; $i ++) {
            $listyear[$i] = $i;
        }
        arsort($listyear);
        return $listyear;
    }

    public function getCurrentYear()
    {
        if (date("m") < 4) {
            return date("Y") - 1;
        } else {
            return date("Y");
        }
    }
    
    public function getCheckDuplicateUpdate($params)
    {
        $em = $this->getEntityManager();
        $data = $params->fromPost();
        if ($data) {
            $repository = $em->getRepository('Application\Entity\ClassJ')->findOneBy(array('year' => $data['year'] ? $data['year'] : '',
                'orgSchoolYear' => $data['school_year'] ? $data['school_year'] : '',
                'className' => $data['classname'] ? $data['classname'] : '',
                'isDelete' => ''));
            //exclude owner id
            //$repository->getId() != $data['id']  
            return $repository && $repository->getId() != $data['id'] ? true : false; //TRUE is mean className is exist
        }
        return false;
    }
    
    public function getMessages() {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsMessages = array(
            'MsgDuplicationClassName' => $translator->translate('MsgDuplicationClassName'),
        );

        return $jsMessages;
    }
    
    public function isNotShowMSGGradeClass($params, $Response) {
        $sl = $this->getServiceLocator();
        $vhm = $sl->get('viewhelpermanager');
        $url = $vhm->get('url');
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $urlPolicy = $url('org-mnt/default', array(
            'controller' => 'org',
            'action' => 'policy-grade-class'
        ));
        $result = array(
            'status' => 1,
            'MSG' => sprintf($translator->translate('MSGPopupWarningForClass'), $urlPolicy),
            'textOK' => $translator->translate('保存'),
            'textCamcel' => $translator->translate('textCancelClass'),
        );
        $data = array();
        $data = $params->fromPost('data');
        $data = $this->convertdata($data);
        list($dataClass, $dataInput) = $this->convertDataToCheckClass($data);
        /* @var $dantaiService \Application\Service\DantaiService */
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        $checkFirstChar = $dantaiService->isAlphanumericCharacter($dataClass, ApplicationConst::CLASS_TYPE, $dataInput);
        if ($checkFirstChar === false) {
            $result['status'] = 0;
        }

        return $Response->setContent(Json::encode($result));
    }

    public function isNotShowMSGClassForUpdate($params, $Response) {
        $sl = $this->getServiceLocator();
        $vhm = $sl->get('viewhelpermanager');
        $url = $vhm->get('url');
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $urlPolicy = $url('org-mnt/default', array(
            'controller' => 'org',
            'action' => 'policy-grade-class'
        ));
        $result = array(
            'status' => 1,
            'MSG' => sprintf($translator->translate('MSGPopupWarningForClass'), $urlPolicy),
            'textOK' => $translator->translate('保存'),
            'textCamcel' => $translator->translate('textCancelClass'),
        );
        $fromPost = $params->fromPost();
        if ($fromPost) {
            $dataClassDB = $this->getAllClassOfOrgByYear(array((int) $fromPost['year']), (int) $fromPost['id']);
            /* @var $dantaiService \Application\Service\DantaiService */
            $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
            $checkFirstChar = $dantaiService->isAlphanumericCharacter($dataClassDB, ApplicationConst::CLASS_TYPE, array($fromPost['year'] . $fromPost['school_year'] . ApplicationConst::DELIMITER_VALUE . $fromPost['classname']));
            if ($checkFirstChar === false) {
                $result['status'] = 0;
            }
        }
        return $Response->setContent(Json::encode($result));
    }

    public function convertDataToCheckClass($data) {
        $years = array();
        $arrResultInput = array();
        $arrResult = array();
        if (!empty($data)) {
            foreach ($data as $value) {
                array_push($arrResultInput, $value[0] . $value[1] . ApplicationConst::DELIMITER_VALUE . $value[2]);
                if (!in_array($value[0], $years)) {
                    array_push($years, $value[0]);
                }
            }
        }
        if ($years) {
            $dataClass = $this->getEntityManager()->getRepository('Application\Entity\ClassJ')->ListClassByYear($this->id_org, $years);
            if ($dataClass) {
                foreach ($dataClass as $row) {
                    array_push($arrResult, $row['year'] . $row['orgSchoolYearId'] . ApplicationConst::DELIMITER_VALUE . $row['className']);
                }
            }
        }

        return array($arrResult, $arrResultInput);
    }

    public function getAllClassOfOrgByYear($years, $id) {
        $arrResult = array();
        if ($years) {
            $dataClass = $this->getEntityManager()->getRepository('Application\Entity\ClassJ')->listClassByYear($this->id_org, $years);
            if ($dataClass) {
                foreach ($dataClass as $row) {
                    if (!empty($id) && $row['idClass'] != $id)
                        array_push($arrResult, $row['year'] . $row['orgSchoolYearId'] . ApplicationConst::DELIMITER_VALUE . $row['className']);
                }
            }
        }
        return $arrResult;
    }

}