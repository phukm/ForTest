<?php
namespace OrgMnt\Controller;

use Application\Service\CommonService;
use Application\Service\ServiceInterface\CommonServiceInterface;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Dantai\PublicSession;
use Doctrine\ORM\EntityManager;
use OrgMnt\Service\UserService;
use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Role;
use Application\Entity\User;
use Application\Entity\Organization;
use Application\Entity\Permission;
use OrgMnt\Form\CreateUserForm;
use OrgMnt\Form\SearchUserForm;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use stdClass;
use OrgMnt\Service\ServiceInterface\UserServiceInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use SebastianBergmann\Environment\Console;

class UserController extends AbstractActionController
{
    const POST_PER_PAGE = 20;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var UserServiceInterface
     */
    protected $userService;

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(DantaiServiceInterface $dantaiService, UserServiceInterface $userService, EntityManager $entityManager)
    {
        $this->dantaiService = $dantaiService;
        $this->userService = $userService;
        $this->em = $entityManager;
    }

    // list user
    public function indexAction()
    {
        $viewModel = new ViewModel();
        $em = $this->getEntityManager();
        $form = new SearchUserForm();
        $page = $this->params()->fromRoute("page", 1);
        $limit = self::POST_PER_PAGE;
        $offset = ($page < 0) ? 0 : ($page - 1) * $limit;
        $jsMessages = array();

        $userId = "";
        $fullName = "";
        $roleId = "";

        $isset = $this->params()->fromPost();
        // set value search width section
        $routeMatch = $this->getEvent()
            ->getRouteMatch()
            ->getParam('controller') . '_' . $this->getEvent()
            ->getRouteMatch()
            ->getParam('action');
        if ($this->getRequest()->isPost()) {
            $userId = $this->removeSpecialCharacters($this->params()
                ->fromPost('id'));
            $userId = trim($userId);
            $fullName = $this->removeSpecialCharacters($this->params()
                ->fromPost('name'));
            $fullName = trim($fullName);
            $roleId = $this->removeSpecialCharacters($this->params()
                ->fromPost('Role'));
            $roleId = trim($roleId);

            // set section

            $search = array(
                'user_id' => $userId,
                'name_user' => $fullName,
                'role_id' => $roleId
            );

            $this->dantaiService->setSearchKeywordToSession($routeMatch, $search);
        } else {
            $search = $this->dantaiService->getSearchKeywordFromSession($routeMatch);
            if (! empty($search)) {
                $userId = $search['user_id'];
                $fullName = $search['name_user'];
                $roleId = $search['role_id'];
            }
        }
        // set form search
        $form->get("id")->setValue($userId);
        $form->get("name")->setValue($fullName);
        $form->get("Role")->setValue($roleId);

        $user = $this->dantaiService->getCurrentUser();
        // get ogranization no
        $idUser = $user['id'];
        $orgid = $user['organizationId'];
        $orgNo = $em->getRepository('Application\Entity\User')->findOneBy(array(
            'id' => $idUser
        ));
        if(!empty($orgNo)){
            $oldorgid = $orgNo->getOrganizationId();
        }
        

        // check get role
        $oldRoleLv = '';
        if ($oldorgid == $orgid) {
            $roleLevelId = $user['roleId'];
        } else {
            $roleLevelId = 4;
        }
        $oldRoleLv = $roleLevelId;
        if ($roleLevelId == 5) {
            $roleLevelId = 4;
        }

        // get list role
        $objectListRole = $em->getRepository('Application\Entity\Role')->ListRole($roleLevelId);
        $listRole = array();
        $listRole[''] = '';
        if (! empty($objectListRole)) {
            foreach ($objectListRole as $key => $value) {
                $listRole[$value['id']] = $value['roleName'];
            }
        }

        // set option for dropdownlist
        $form->get("Role")->setValueOptions($listRole);
        if (! empty($isset) || $isset == 0) {}
        // get list user
        
        $paginator = $em->getRepository('Application\Entity\User')->searchUserOrg($userId, $fullName, $roleId, $orgid, $roleLevelId);
        
        $jsonMessage = \Dantai\Utility\JsonModelHelper::getInstance();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $mess = array();
        if (! empty($this->flashMessenger()->getMessages())) {
            $mess = $this->flashMessenger()->getMessages();
            if(isset($mess[1])){
                $mess = json_decode($mess[1], true);
            }
        }
        if (empty($mess['MSG58'])) {
            $jsMessages = array(
                'MSG1004' => $translator->translate('MSG1004'),
                'MSGselectdelete' => $translator->translate('MSGselectdelete'),
                'MSGdeletemyuser' => $translator->translate('MSGdeletemyuser'),
                'MSG58' => ''
            );
        } else {
            $jsMessages = array(
                'MSG1004' => $translator->translate('MSG1004'),
                'MSGselectdelete' => $translator->translate('MSGselectdelete'),
                'MSGdeletemyuser' => $translator->translate('MSGdeletemyuser'),
                'MSG58' => $translator->translate('MSGUserPermission')
            );
        }
        //Uthv delete cross msg
       // $crossMessages = $this->dantaiService->getCrossEditingMessage('Application\Entity\User');
      //  $jsMessages['conflictWarning'] = $crossMessages['conflictWarning'];
      //  $jsMessages['conflictType'] = $crossMessages['conflictType'];
        
        $jsonMessage->setFail();
        $jsonMessage->setData($jsMessages);
        $viewModel->setVariables(array(
            "form" => $form,
            "items" => $paginator->getItems($offset, $limit, false),
            "role" => $oldRoleLv,
            "jsMessages" => $jsonMessage,
            "id_user" => $idUser,
            "page" => $page,
            "numPerPage" => $limit,
            "paginator" => $paginator,
            'searchVisible' => empty($isset)? 0 : 1
        ));
        return $viewModel;
    }

    // detail user
    public function showAction()
    {
        // get info user
        $user = $this->dantaiService->getCurrentUser();
        // get ogranization no
        $roleLevelId = $user['roleId'];
        $curentUserId = $user['id'];
        $viewModel = new ViewModel();
        $form = new CreateUserForm();

        $em = $this->getEntityManager();
        $request_id = intval($this->params('id', 0));

        $checkEqId = 0;
        if ($request_id == $curentUserId) {
            $checkEqId = 1;
        }
        if (! empty($request_id)) {
            $repository = $em->getRepository('Application\Entity\User');
            $validFields = array(
                "id" => $request_id,
                'isDelete' => '0'
            );
            if ($roleLevelId != 1) {
                $validator = new \DoctrineModule\Validator\ObjectExists(array(
                    'object_repository' => $repository,
                    'fields' => array(
                        'id',
                        'isDelete',
                        'organizationId'
                    )
                ));
                $validFields['organizationId'] = $user['organizationId'];
            } else {
                $validator = new \DoctrineModule\Validator\ObjectExists(array(
                    'object_repository' => $repository,
                    'fields' => array(
                        'id',
                        'isDelete'
                    )
                ));
            }
            $check_id = $validator->isValid($validFields);
            if ($check_id == true) {
                $user_info = $em->getRepository('Application\Entity\User')->find($request_id);

                $translator = $this->getServiceLocator()->get('MVCTranslator');
                $jsMessages = array(
                    'MSGselectdelete' => $translator->translate('MSGselectdelete'),
                    'MSGdeletemyuser' => $translator->translate('MSGdeletemyuser')
                );
                
                
                $serviceType = $user_info->getServiceType();
                if(!empty($serviceType)){
                    if (stripos($serviceType, 'All') === 0) {
                        $form->get("ServiceType")->setValue(array(
                            '英検',
                            'IBA'
                        ))->setAttribute('disabled', 'disabled');
                    } else {
                        $form->get("ServiceType")->setValue($serviceType)->setAttribute('disabled', 'disabled');
                    }
                }else{
                    $form->get("ServiceType")->setAttribute('disabled', 'disabled');
                }
                
                $viewModel->setVariables(array(
                    "form" => $form,
                    "item" => $user_info,
                    "lv_role" => $roleLevelId,
                    "jsMessages" => json_encode($jsMessages),
                    "curentId" => json_encode($curentUserId),
                    "id" => $request_id,
                    "checkEqId" => $checkEqId
                ));
                return $viewModel;
            } else {
                return $this->redirect()->toRoute('org-mnt/default', array(
                    'controller' => 'user',
                    'action' => 'index'
                ));
            }
        } else {
            return $this->redirect()->toRoute('org-mnt/default', array(
                'controller' => 'user',
                'action' => 'index'
            ));
        }
    }
    // show from addnew
    public function addAction()
    {
        $viewModel = new ViewModel();
        $em = $this->getEntityManager();
        $form = new CreateUserForm();
        $mess = array();
        // check role
        $user = $this->dantaiService->getCurrentUser();

        $orgid = $user["organizationId"];
        $idcurentuser = $user["id"];
        $oldorgno = $orgNo = $em->getRepository('Application\Entity\User')
            ->findOneBy(array(
            'id' => $idcurentuser
        ))
            ->getOrganizationId();
        // check get role
        if ($oldorgno == $orgid) {
            $roleLevelId = $user['roleId'];
        } else {
            $roleLevelId = 4;
        }

        $orgNo = $em->getRepository('Application\Entity\Organization')
            ->findOneBy(array(
            'id' => $orgid
        ))
            ->getOrganizationNo();
        // set org number and disable input

        // get lisst role
        $objectListRole = $em->getRepository('Application\Entity\Role')->ListRole($roleLevelId);
        $listRole = array();
        $listRole[''] = '';
        if (! empty($objectListRole)) {
            foreach ($objectListRole as $key => $value) {
                $listRole[$value['id']] = $value['roleName'];
            }
        }
        // set option for dropdownlist
        $form->get("Role")->setValueOptions($listRole);
        $translator = $this->getServiceLocator()->get('MVCTranslator');

        $jsonMessage = \Dantai\Utility\JsonModelHelper::getInstance();
        $jsMessages = array();
        if (! empty($this->flashMessenger()->getMessages())) {

            $mess = $this->flashMessenger()->getMessages();
            $mess = json_decode($mess[0], true);
            if (isset($mess['data']) && ! empty($mess['data'])) {
                $form->setData($mess['data']);
            }

        $jsMessages = array(
                'MSG019' => $translator->translate('MSG019'),
                'MSGemailformat' => $translator->translate('MSGemailformat'),
                'MSGrequeri' => $translator->translate('MSGrequeri'),
                'MSG001' => $translator->translate('MSG001'),
                'MSG017' => '',
                'EmailIsUse' => ''
            );
            if (isset($mess['MSG017']) && ! empty($mess['MSG017'])) {
                $jsMessages['MSG017'] = $mess['MSG017'];
            }
            if (isset($mess['EmailIsUse']) && ! empty($mess['EmailIsUse'])) {
                $jsMessages['EmailIsUse'] = $mess['EmailIsUse'];
            }
        } else {
            $jsMessages = array(
                'MSG019' => $translator->translate('MSG019'),
                'MSGemailformat' => $translator->translate('MSGemailformat'),
                'MSGrequeri' => $translator->translate('MSGrequeri'),
                'MSG017' => '',
                'MSG001' => $translator->translate('MSG001'),
                'EmailIsUse' => ''
            );
        }
        if ($roleLevelId < 5) {
            $jsonMessage->setFail();
//             $jsonMessage->addMessage($mess['MSG017']);
            $jsonMessage->setData($jsMessages);
            $viewModel->setVariables(array(
                "form" => $form,
                "jsMessages" => $jsonMessage,
                "orgno" => $orgNo
            ));
            return $viewModel;
        } else {
            return $this->redirect()->toRoute('accessDenied');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function saveAction()
    {
        $msg_arr = array();
        $em = $this->getEntityManager();
        $request = $this->getRequest();
        $datafake = '';
        // get object role
        $role = $em->getReference('Application\Entity\Role', array(
            'id' => $this->params()
                ->fromPost('Role')
        ));
        // check org number
        $org_number = '';

        $user = $this->dantaiService->getCurrentUser();
        $orgid = $user["organizationId"];

        $org_number = $em->getRepository('Application\Entity\Organization')
            ->findOneBy(array(
            'id' => $orgid
        ))->getOrganizationNo();

        $repository = $em->getRepository('Application\Entity\Organization');
        $validator = new \DoctrineModule\Validator\ObjectExists(array(
            'object_repository' => $repository,
            'fields' => array(
                'organizationNo'
            )
        ));

        $org_no_check = $validator->isValid($org_number);
        // get object org
        $object_org = $em->getRepository('Application\Entity\Organization')->findOneBy(array(
            'id' => $orgid
        ));
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        if (empty($org_no_check)) {
            $msg_arr['MSG020'] = $translator->translate("MSG020");
        }
        // check userid
        $user_check = false;
        if (! empty($this->params()->fromPost('txtUserID'))) {
            $datafake = $this->params()->fromPost();

            $user_name = $this->params()->fromPost('txtUserID');
            $user_name = trim($user_name);
            $name_fist = $this->params()->fromPost('txtFistname');
            $name_fist = trim($name_fist);
            $name_last = $this->params()->fromPost('txtlastname');
            $name_last = trim($name_last);
            $email = $this->params()->fromPost('txtEmailAddress');
            $email = strip_tags(trim($email));
            $check_save = 0;
            $rolelv = $this->params()->fromPost('Role');
            $servicetype = $this->params()->fromPost('ServiceType');
            if (count($servicetype) == 2) {
                $servicetype = "All";
            } else {
                $servicetype = $servicetype[0];
            }

            $repository = $em->getRepository('Application\Entity\User');
            $validator = new \DoctrineModule\Validator\ObjectExists(array(
                'object_repository' => $repository,
                'fields' => array(
                    'userId',
                    'organizationNo'
                )
            ));
            $user_check = $validator->isValid(array(
                "userId" => $user_name,
                "organizationNo" => $org_number
            ));

            $validatorEmail = new \DoctrineModule\Validator\ObjectExists(array(
                'object_repository' => $repository,
                'fields' => array(
                    'emailAddress'
                )
            ));

            $email_check = $validatorEmail->isValid(array(
                'emailAddress' => $email
            ));
            $validatorEmail = new \DoctrineModule\Validator\ObjectExists(array(
                'object_repository' => $repository,
                'fields' => array(
                    'emailAddress'
                )
            ));
            $email_check = $validatorEmail->isValid(array(
                'emailAddress' => $email
            ));
        }
        if ($user_check == true && $email_check == true) {
            $msg_arr['data'] = $datafake;
            $msg_arr['MSG017'] = $translator->translate("MSG017");
            $msg_arr['EmailIsUse'] = $translator->translate("EmailIsUse");
            $msg_arr = json_encode($msg_arr);
        }else if($user_check == true){
            $msg_arr['data'] = $datafake;
            $msg_arr['MSG017'] = $translator->translate("MSG017");
            $msg_arr = json_encode($msg_arr);
        }
        else if($email_check == true){
            $msg_arr['data'] = $datafake;
            $msg_arr['EmailIsUse'] = $translator->translate("EmailIsUse");
            $msg_arr = json_encode($msg_arr);
        }
        $this->flashMessenger()->addMessage($msg_arr);
        // set password
        $pass = rand(10000000, 99999999);
        $pass_nomd5 = $pass;
        $pass = $pass . 'FPT';
        $pass = md5($pass);
        $user = new User();
        /*@var $user \Application\Entity\User */
        if ($request->isPost() && $user_check == false && $org_no_check == true && $email_check == false) {
            $em->getConnection()->beginTransaction();
            try {

                $user->setOrganizationNo($org_number);
                $user->setUserId($user_name);
                $user->setPassword($pass);
                $user->setFirstNameKanji($name_fist);
                $user->setLastNameKanji($name_last);
                $user->setRole($role);
                $user->setStatus($this->params()
                    ->fromPost('Status'));
                $user->setEmailAddress($email);
                $user->setAnnouncement($this->params()
                    ->fromPost('hope'));
                $user->setCountLoginFailure(0);
                $user->setOrganization($object_org);
                $user->setIsDelete(0);
                $user->setFirstSendPass(1);
                $user->setServiceType($servicetype);
                if ($rolelv == 1) {
                    $user->setFirstLogin(0);
                    $user->setAgreePolicy(1);
                } else {
                    $user->setFirstLogin(1);
                    $user->setAgreePolicy(0);
                }

                $em->persist($user);
                $em->flush();
                $em->getConnection()->commit();
            } catch (Exception $e) {
                $check_save = 1;
                $em->getConnection()->rollback();
            }
            if ($check_save == 0) {
                $protocol = $this->getRequest()->getServer('HTTPS') ? 'https://' : 'http://';
                $globalConfig = $this->getServiceLocator()->get('config');
                $source = isset($globalConfig['emailSender']) ? $globalConfig['emailSender'] : 'dantai@mail.eiken.or.jp';

                $to = array(
                    $email
                );
                $type = 2;
                $data = array(
                    'name' => $name_fist . $name_last,
                    'orgName' => $object_org->getOrgNameKanji(),
                    'orgNo' => $org_number,
                    'url' => $protocol . $this->getRequest()->getServer('SERVER_NAME'),
                    'userId' => $user_name,
                    'password' => $pass_nomd5,
                    'confirmUrl' => $protocol . $this->getRequest()->getServer('SERVER_NAME') . "/login"
                );

                try {
                    \Dantai\Aws\AwsSesClient::getInstance()->deliver($source, $to, $type, $data);
                } catch (Exception $e) {
                    // TODO Report this exception to system admin
                    // throw $e;
                }
            }
            return $this->redirect()->toRoute('org-mnt/default', array(
                'controller' => 'user',
                'action' => 'index'
            ));
        } else {
            return $this->redirect()->toRoute('org-mnt/default', array(
                'controller' => 'user',
                'action' => 'add'
            ));
        }
    }

    public function editAction()
    {
        $viewModel = new ViewModel();
        $em = $this->getEntityManager();
        $form = new CreateUserForm();
        $user = $this->dantaiService->getCurrentUser();

        $request_id = intval($this->params('id', 0));
        $orgid = $user['organizationId'];
        if ($request_id) {
            $repository = $em->getRepository('Application\Entity\User');
            $validFields = array(
                "id" => $request_id,
                'isDelete' => '0'
            );
            if ($user['roleId'] != 1)
            {
                $validator = new \DoctrineModule\Validator\ObjectExists(array(
                    'object_repository' => $repository,
                    'fields' => array(
                        'id',
                        'organizationId',
                        'isDelete'
                    )
                ));
                $validFields['organizationId'] = $orgid;
            }
            else
            {
                $validator = new \DoctrineModule\Validator\ObjectExists(array(
                    'object_repository' => $repository,
                    'fields' => array(
                        'id',
                        'isDelete'
                    )
                ));
            }
            $check_id = $validator->isValid($validFields);
            if ($check_id != true) {
                return $this->redirect()->toRoute('org-mnt/default', array(
                    'controller' => 'user',
                    'action' => 'index'
                ));
            }
        }

        // lay ogranization no
        $idUser = $user['id'];
        $oldorgid = $em->getRepository('Application\Entity\User')->findOneBy(array(
            'id' => $idUser
        ))->getOrganizationId();
        // check get role
        if ($oldorgid == $orgid) {
            $roleLevelId = $user['roleId'];
        } else {
            $roleLevelId = 4;
        }
        if ($roleLevelId == 5 && $request_id != $idUser) {
            return $this->redirect()->toRoute('accessDenied');
        }
        $user_name = $user['userId'];
//       if($idUser == $request_id){
//           return $this->redirect()->toRoute('editProfile');
//       }
        $user_list = "";
        // get role user duoc edit
        $get_lv_role_info = $em->getReference('Application\Entity\User', array(
            'id' => $this->params('id', 0)
        ))
            ->getroleId();
        // neu role dang nhap < role sua - > tra ve trang index
        if ($get_lv_role_info < $roleLevelId) {
            return $this->redirect()->toRoute('org-mnt/default', array(
                'controller' => 'user',
                'action' => 'index'
            ));
        }

        // get form
        // $form->get("txtOrgNumber")->setAttribute("disabled", "disabled");
        // get lisst role
        $objectListRole = $em->getRepository('Application\Entity\Role')->ListRole($roleLevelId);
        $listRole = array();
        $listRole[''] = '';
        if (! empty($objectListRole)) {
            foreach ($objectListRole as $key => $value) {
                $listRole[$value['id']] = $value['roleName'];
            }
        }
        // set option for dropdownlist
        $form->get("Role")->setValueOptions($listRole);
        // end get form
        $idUser = $this->params()->fromRoute('id', 0);
        if (! empty($idUser)) {
            $repository = $em->getRepository('Application\Entity\User');
            $validator = new \DoctrineModule\Validator\ObjectExists(array(
                'object_repository' => $repository,
                'fields' => array(
                    'id'
                )
            ));
            $check_id = $validator->isValid($idUser);
            if ($check_id == true) {
                /*@var $user_info \Application\Entity\User */
                $user_info = $em->getRepository('Application\Entity\User')->find($idUser);
                $roleUser = $user_info->getRoleId();
                $serviceType = $user_info->getServiceType();
                if (stripos($serviceType, 'All') === 0) {
                    $form->get("ServiceType")->setValue(array(
                        '英検',
                        'IBA'
                    ));
                } else {
                    $form->get("ServiceType")->setValue($serviceType);
                }
                $checkChangeFirstPass = $user_info->getFirstLogin();
                // die();
                $userName = '';
                if (! empty($user_info)) {
                    $userName = $user_info->getUserId();
                }
                $form->get("txtOrgNumber")->setValue($userName);
                if ($checkChangeFirstPass == 0) {
                    $form->get("txtUserID")
                        ->setValue($user_info->getUserId())
                        ->setAttribute("disabled", "disabled");
                } else {
                    $form->get("txtUserID")->setValue($userName);
                }

                $form->get("txtFistname")->setValue($user_info->getFirstNameKanji());
                $form->get("txtlastname")->setValue($user_info->getLastNameKanji());
                $form->get("txtEmailAddress")->setValue($user_info->getEmailAddress());
                $form->get("Role")->setValue($user_info->getroleId());
                $form->get("Status")->setValue($user_info->getstatus());
                $form->get("hope")->setValue($user_info->getAnnouncement());

                $translator = $this->getServiceLocator()->get('MVCTranslator');
                $jsMessages = array();

                $jsonMessage = \Dantai\Utility\JsonModelHelper::getInstance();

                $mess = array();
                $jsMessages = array(
                    'MSG019' => $translator->translate('MSG019'),
                    'MSGemailformat' => $translator->translate('MSGemailformat'),
                    'MSGrequeri' => $translator->translate('MSGrequeri'),
                    'MSG017' => '',
                    'EmailIsUse' => '',
                    'MSG001' => $translator->translate('MSG001')
                );
                if (! empty($this->flashMessenger()->getMessages())) {
                    $mess = $this->flashMessenger()->getMessages();
                    $mess = json_decode($mess[0], true);
                    if (isset($mess['data']) && ! empty($mess['data'])) {
                        $form->setData($mess['data']);
                    }
                    if (isset($mess['MSG017']) && ! empty($mess['MSG017'])){
                        $jsMessages['MSG017'] = $mess['MSG017'];
                    }
                    if (isset($mess['EmailIsUse']) && ! empty($mess['EmailIsUse'])){
                        $jsMessages['EmailIsUse'] = $mess['EmailIsUse'];
                    }
                }
                $orgNo = $oldorgid = $em->getRepository('Application\Entity\User')
                    ->findOneBy(array(
                    'id' => $idUser
                ))
                    ->getOrganizationNo();

               // $this->dantaiService->startCrossEditing('Application\Entity\User', array('id' => $idUser));
               // $crossMessages = $this->dantaiService->restoreCrossEditingForm('Application\Entity\User', $form);
              //  $jsMessages['conflictWarning'] = $crossMessages['conflictWarning'];
               // $jsMessages['conflictType'] = $crossMessages['conflictType'];
                
                $jsonMessage->setFail();
                $jsonMessage->setData($jsMessages);
                $viewModel->setVariables(array(
                    "form" => $form,
                    "jsMessages" => $jsonMessage,
                    "id" => $idUser,
                    "orgno" => $orgNo,
                    'checkChangeFirstPass' => $checkChangeFirstPass,
                    'UserId' => $userName,
                    'roleUser' => $roleUser,
                    'user_info' => $user_info,
                    'user' => $user
                   // 'conflictWarning' => $crossMessages['conflictWarning'],
                   // 'conflictType' => $crossMessages['conflictType']
                ));
                return $viewModel;
            } else {
                return $this->redirect()->toRoute('org-mnt/default', array(
                    'controller' => 'user',
                    'action' => 'index'
                ));
            }
        } else {
            return $this->redirect()->toRoute('org-mnt/default', array(
                'controller' => 'user',
                'action' => 'index'
            ));
        }
    }

    public function updateAction()
    {
        $msg_arr = array();
        $em = $this->getEntityManager();
        $request = $this->getRequest();
        $user = $this->dantaiService->getCurrentUser();
        $datafake = "";
        // get object role
        $role = $em->getReference('Application\Entity\Role', array(
            'id' => $this->params()
                ->fromPost('Role')
        ));

        // check org number
        $orgid = $user["organizationId"];
        $org_number = $em->getRepository('Application\Entity\Organization')->findOneBy(array(
            'id' => $orgid
        ))->getOrganizationNo();
        
        $idUser = intval($this->params('id', 0));
        $email = '';
        $repository = $em->getRepository('Application\Entity\User');
        $validFields = array(
            'id' => $idUser
        );
        if ($user['roleId'] != 1)
        {
            $validator = new \DoctrineModule\Validator\ObjectExists(array(
                'object_repository' => $repository,
                'fields' => array(
                    'id',
                    'organizationId'
                )
            ));
            $validFields['organizationId'] = $orgid;
        } 
        else 
        {
            $validator = new \DoctrineModule\Validator\ObjectExists(array(
                'object_repository' => $repository,
                'fields' => array(
                    'id'
                )
            ));
        }
        $org_no_check = $validator->isValid($validFields);
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        if ($org_no_check == false) {
            $msg_arr["org"] = $translator->translate("MSG020");
        }

        if ($request->isPost() && $org_no_check == true) {
            $validator = new \DoctrineModule\Validator\ObjectExists(array(
                'object_repository' => $repository,
                'fields' => array(
                    'id'
                )
            ));
            $idUser_check = $validator->isValid($idUser);
            if ($idUser_check == true) {
                $datafake = $this->params()->fromPost();

                $user_name = $this->params()->fromPost('txtUserID');
                // remove html tags and ' "
                $name_fist = $this->params()->fromPost('txtFistname');
                $name_fist = trim($name_fist);
                $name_last = $this->params()->fromPost('txtlastname');
                $name_last = trim($name_last);
                $email = $this->params()->fromPost('txtEmailAddress');
                $email = strip_tags(trim($email));
                $status = $this->params()->fromPost('Status');
                $getServiceType = $this->params()->fromPost('ServiceType');
                $roleRq = $this->params()->fromPost('Role');
                $objEmailCheck = $repository->findBy(array(
                    'emailAddress' => $email
                ));
                if(!empty($objEmailCheck)){
                    foreach ($objEmailCheck as $value){
                        if($value->getId() != $idUser){
                            $translator = $this->getServiceLocator()->get('MVCTranslator');
                            $msg_arr["EmailIsUse"] = $translator->translate("EmailIsUse");
                            $msg_arr['data'] = $datafake;
                            $msg_arr = json_encode($msg_arr);
                            $this->flashMessenger()->addMessage($msg_arr);
                            return $this->redirect()->toRoute('org-mnt/default', array(
                                'controller' => 'user',
                                'action' => 'edit',
                                'id' => $this->params('id', 0)
                            ));
                        }
                    }
                }
                if($roleRq == 2){
                    if (count($getServiceType) == 2) {
                        $getServiceType = "All";
                    } else {
                        $getServiceType = $getServiceType[0];
                    }
                }else{
                        $getServiceType = '';
                }
                
                // set data

                $user = new User();
                /*@var $user \Application\Entity\User */
                $user = $em->getRepository('Application\Entity\User')->findOneBy(array(
                    "id" => $idUser
                ));

                if (empty($user_name) || $user_name == null) {
                    $user_name = $user->getuserId();
                }

                $check_duplicator = $em->getRepository('Application\Entity\User')->objectexistsupdate($idUser, $org_number, $user_name);

                if ($check_duplicator == false) {

                    $timeFirstLogin = time() - strtotime($user->getInsertAt()->format('Y-m-d H:i:s'));
                    $timeFirstLoginRule = 86400 * 3;

                    if ($user->getFirstSendPass() == 1 && $timeFirstLogin > $timeFirstLoginRule && $user->getStatus() == "Disable" && $status == "Enable") {
                        $user->setFirstSendPass(0);
                    }

                    $route = array(
                        'controller' => 'user',
                        'action' => 'index'
                    );
                    //Uthv Delete cross edit
                   // $crossMessages = $this->dantaiService->checkCrossEditing($user, null, $this->params()->fromPost());
//                    if($crossMessages['conflictWarning']){
//                        if($crossMessages['conflictType'] == 'edit'){
//                            $route['action'] = 'edit';
//                            $route['id'] = $idUser;
//                        }
//                        return $this->redirect()->toRoute('org-mnt/default', $route);
//                    }
                    
                    // $user->setOrganizationNo($org_number);
                    // $user->setUserId($user_name);
                    $user->setFirstNameKanji($name_fist);
                    $user->setLastNameKanji($name_last);
                    $user->setRole($role);
                    $user->setStatus($status);
                    $user->setEmailAddress($email);
                    $user->setServiceType($getServiceType);
                    $user->setAnnouncement($this->params()
                        ->fromPost('hope'));
                    $em->persist($user);
                    $em->flush();
                    
                    return $this->redirect()->toRoute('org-mnt/default', $route);
                } else {
                    $translator = $this->getServiceLocator()->get('MVCTranslator');
                    $msg_arr["MSG017"] = $translator->translate("MSG017");
                    $msg_arr['data'] = $datafake;
                    $msg_arr = json_encode($msg_arr);
                    $this->flashMessenger()->addMessage($msg_arr);
                    return $this->redirect()->toRoute('org-mnt/default', array(
                        'controller' => 'user',
                        'action' => 'edit',
                        'id' => $this->params('id', 0)
                    ));
                }
            }
        } else {
            $this->flashMessenger()->addMessage($msg_arr);
            return $this->redirect()->toRoute('org-mnt/default', array(
                'controller' => 'user',
                'action' => 'index'
            ));
        }
    }

    public function deleteAction()
    {
        $em = $this->getEntityManager();
        $data = $this->params()->fromPost('input');
        $curentUser = $this->dantaiService->getCurrentUser();
        $org_id = $curentUser['organizationId'];
        $role_id = $curentUser['roleId'];
        $user = new User();
        $msg_arr = array();
        $ids = array();
        $ids = array_keys($data);
        // delete show send to
        if (empty($ids)) 
        {
            $ids[] = $this->params('id', 0); 
        }
        $user = $em->getRepository('Application\Entity\User')->findBy(array(
            'id' => $ids
        ));
        $check_delete = $em->getRepository('Application\Entity\User')->objectexistdelete($ids, $org_id, $role_id);

        // delete
        if (! empty($user) && $check_delete == true) {
            foreach ($user as $key => $value) {
                $value->setStatus('Locked');
                $value->setIsDelete(1);
                $em->flush();
            }
            $em->flush();
            $em->clear();
        }

        // set filter back
        $isset = $this->params()->fromPost();
        if (! empty($isset)) {
            $userId = $this->params()->fromPost('id');
            $fullName = $this->params()->fromPost('name');
            $roleId = $this->params()->fromPost('Role');
        }
        // set msg
        // $translator = $this->getServiceLocator()->get('MVCTranslator');
        // $msg_arr["MSGsuccessdelete"] = $translator->translate('MSGsuccessdelete');
        $this->flashMessenger()->addMessage($msg_arr);
        if (! empty($user) && $check_delete == true) {
            $msg_arr['MSG58'] = '';
        } else {
            $msg_arr['MSG58'] = 'MSG58';
        }
        $msg_arr = json_encode($msg_arr);
        $this->flashMessenger()->addMessage($msg_arr);
        return $this->redirect()->toRoute('org-mnt/default', array(
            'controller' => 'user',
            'action' => 'index'
        ));
    }

    /**
     *
     * @return array|object
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    // bo ky tu '' va ""
    public function removeSpecialCharacters($string)
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
}
