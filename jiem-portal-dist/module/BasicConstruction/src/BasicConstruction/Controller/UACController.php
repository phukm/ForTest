<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/BasicConstruction for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace BasicConstruction\Controller;

use Application\Service\ServiceInterface\DantaiServiceInterface;
use BasicConstruction\Service\UACService;
use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;
use BasicConstruction\Form\LoginForm;
use BasicConstruction\Form\ChangePasswordForm;
use BasicConstruction\Form\ChangePasswordFirstForm;
use BasicConstruction\Form\EditProfileForm;
use Application\Entity\User;
use Dantai\PrivateSession;
use Application\Entity\AccessKey;

class UACController extends AbstractActionController {
    
    const LoginSessionKey = 'LoginSessionKey';

    private $identity;
    private $translator;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var UACService
     */
    protected $uacService;

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(DantaiServiceInterface $dantaiService, UACService $uacService, EntityManager $entityManager) {
        $this->dantaiService = $dantaiService;
        $this->uacService = $uacService;
        $this->em = $entityManager;
    }

    public function onDispatch(\Zend\Mvc\MvcEvent $e) {
        $this->translator = $this->getServiceLocator()->get('MVCTranslator');
        $this->identity = $this->getEntityAuthenticateManager()->getIdentity();
        $routeMatch = $e->getRouteMatch();
        $action = $routeMatch->getParam('action');
        if ($this->identity != Null && $action == 'index') {
            $this->redirectByRole($this->identity);
        }
        return parent::onDispatch($e);
    }

    public function accessDeniedAction() {
        $viewModel = new ViewModel();
        return $viewModel;
    }

    public function inactivatedAction() {
        $viewModel = new ViewModel();
        return $viewModel;
    }

    public function samplePolicyAction() {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    public function policyAction() {
        $errors = array();
        $user = $this->identity;
        $viewModel = new ViewModel();
        // user has agree policy
        if ($user->getAgreePolicy() == 1) {
            return $this->redirect()->toRoute('profile');
        }
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            if (!empty($params['agreePolicy']) && $params['agreePolicy'] == 1) {
                $user->setAgreePolicy(1);
                $this->em->flush();
                $this->em->clear();
                $this->redirectByRole($user);
            } else {
                $errors['agreePolicy'] = 'Msg_Error_Field_Is_Blank';
            }
        }
        $viewModel->setVariables(array(
            'errors' => $errors
        ));
        return $viewModel;
    }

    /* action Authentication */

    public function indexAction() {
        $wrong_pass = false;
        $errors = array();
        $viewModel = new ViewModel();
        $form = new LoginForm();
        $this->layout("/layout/LoginMasterPage");
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            $params["orgNo"] = trim($params["orgNo"]);
            $params["userId"] = trim($params["userId"]);
            $form->setData($params);
            if (empty($params["orgNo"])) {
                $errors["orgNo"] = 'Msg_Error_Field_Is_Blank';
            }
            if (empty($params["userId"])) {
                $errors["userId"] = 'Msg_Error_Field_Is_Blank';
            }
            if (empty($params["password"])) {
                $errors["password"] = 'Msg_Error_Field_Is_Blank';
            }
            if (empty($_COOKIE) || count($_COOKIE) <= 0) {
                $errors = 'testing' === getenv('APP_ENV') ? $errors : array('cookie' => 'Disable coookie');
            }
            if ($errors == false) {
                /* @var $user \Application\Entity\User */
                $user = $this->em->getRepository('Application\Entity\User')->findOneBy(array(
                    'userId' => $params["userId"],
                    'organizationNo' => $params["orgNo"]
                ));
                if ($user != Null) {
                    $timeFirstLogin = time() - strtotime($user->getInsertAt()->format('Y-m-d H:i:s'));
                    $timeFirstLoginRule = 86400 * 3;

                    if ($user->getFirstSendPass() == 1 && $timeFirstLogin > $timeFirstLoginRule) {
                        $errors['userId'] = 'Msg_Error_Login_Past_3_Days';
                        $user->setStatus('Disable');
                        $this->em->flush();
                        $this->em->clear();
                    }else if ($user->getStatus() == "Locked" && $user->getIsDelete() == 1) {
                        $errors['userId'] = 'Msg_Error_Locked_Account';
                    } else {
                        $response = $this->uacService->authentication($user, $params, $this->em);
                        $wrong_pass = $response["wrong_pass"];
                        if ($response["status"] == 1) {
                            if ($user->getStatus() == 'Disable') {
                                // clear session auth login
                                $auth = $this->getEntityAuthenticateManager();
                                $auth->getAdapter()->setIdentityValue("");
                                $auth->getAdapter()->setCredentialValue("");
                                $auth->authenticate();

                                PrivateSession::clear();
        //                        active user by AccessKey here
                                $privateSession = new \Dantai\PrivateSession();
                                $privateSession->setData(self::LoginSessionKey, $params);
                                return $this->redirect()->toRoute('access-key/default', array('controller' => 'access-key','action'=>'activate'));
        //                        $errors['password'] = 'Msg_Error_Disable_Account';
                            }
                            if ($user->getRoleId() == 4 || $user->getRoleId() == 5) {
                                $protocol = getenv('APP_ENV') ? 'http://' : 'https://';
                                $httpHost = $this->getRequest()->getServer('HTTP_HOST');
                                $url = $protocol . $httpHost;
                                $this->uacService->checkImportEikenTestResult($user->getOrganizationId(), $user->getOrganizationNo(), $url);
                                $this->uacService->checkImportIBATestResult($user->getOrganizationId(), $user->getOrganizationNo(),$user->getUserId(), $url);
                            }
                            $this->redirectByRole($response["identity"]);
                        } else {

                            if ($user->getCountLoginFailure() == 5 && $user->getStatus() == "Enable") {
                                $errors['password'] = 'Msg_Error_Password_Wrong_6_Times_Continuously';
                            } else {
                                $errors['password'] = $response["error"];
                            }
                        }
                    }

                    $this->uacService->updateFailedLogin($wrong_pass, $user, $this->em);
                } else {
                    $errors['all'] = 'Msg_Error_Wrong_Account';
                }
            }
        }
        $viewModel->setVariables(array(
            'form' => $form,
            'errors' => $errors
        ));
        return $viewModel;
    }

    function curPageURL() {
        $pageURL = 'http';
        if ($_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    public function logoutAction() {
        $session = session_id();
        $auth = $this->getEntityAuthenticateManager();
        $auth->getAdapter()->setIdentityValue("");
        $auth->getAdapter()->setCredentialValue("");
        $result = $auth->authenticate();
        PrivateSession::clear();
        session_regenerate_id(true);
        $this->uacService->deleteSession($session);
        return $this->redirect()->toRoute('login');
    }

    public function profileAction() {
        if ($this->getRequest()->getHeader('Referer') != false) {
            $redirectUrl = $this->getRequest()
                    ->getHeader('Referer')
                    ->getUri();
        } else {
            $redirectUrl = $this->url()->fromRoute('home');
        }
        $user = $this->identity;
        $roldeId = $user->getRole()->getId();

        if ($roldeId != 1 && $user->getFirstLogin() == 1) {
            return $this->redirect()->toRoute('changePasswordFirst');
        } else
        if (intval($user->getAgreePolicy()) == 0) {
            return $this->redirect()->toRoute('policy');
        }
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'user' => $user,
            'redirectUrl' => $redirectUrl
        ));
        return $viewModel;
    }

    public function editProfileAction() {
        $errors = array();
        $user = $this->identity;
        $form = new EditProfileForm('edit-profile', $user);
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();

            $params['txtUserID'] = trim($user->getUserId());
            $params['txtEmailAddress'] = trim($params['txtEmailAddress']);
            $params['txtFistname'] = trim(str_replace(" ", "", $params['txtFistname']));
            $params['txtlastname'] = trim(str_replace(" ", "", $params['txtlastname']));

            $form->setData($params);
            $errors = $this->uacService->validateProfile($params, $user, $this->em, 1);

            if ($errors == false) {
                $user->setUserId($params['txtUserID']);
                $user->setFirstNameKanji($params['txtFistname']);
                $user->setLastNameKanji($params['txtlastname']);
                $user->setEmailAddress($params['txtEmailAddress']);
                $this->em->flush();
                $this->em->clear();

                $userIdentity = PrivateSession::getData('userIdentity');
                $userIdentity["firstName"] = $user->getFirstNameKanji();
                $userIdentity["lastName"] = $user->getLastNameKanji();
                $userIdentity["emailAddress"] = $user->getEmailAddress();
                PrivateSession::setData('userIdentity', $userIdentity);
                return $this->redirect()->toRoute('profile');
            }
        }
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'form' => $form,
            'user' => $user,
            'errors' => $errors
        ));
        return $viewModel;
    }

    public function changePasswordAction() {
        $user = $this->identity;
        if ($this->getRequest()->getHeader('Referer') != false) {
            $referer = $this->getRequest()->getHeader('Referer');
            if (strpos($referer, '/org/user/edit') !== false) {
                $redirectUrl = $this->url()->fromRoute('org-mnt/default', array("controller" => "user", "action" => "show", "id" => $user->getId()));
            } else {
                $redirectUrl = $this->url()->fromRoute('profile');
            }
        } else {
            $redirectUrl = $this->url()->fromRoute('profile');
        }
        $form = new ChangePasswordForm();

        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'form' => $form,
            'user' => $user,
            'redirectUrl' => $redirectUrl,
            'errors' => array()
        ));
        return $viewModel;
    }

    public function ajaxChangePasswordAction() {
        $results = array(
            'status' => 0,
            'error' => array(),
            'agreePolicy' => 0
        );
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();
            /* @var $user \Application\Entity\User */
            $user = $this->identity;
            $errors = $this->uacService->validatePassword($user, $params);
            foreach ($errors as $key => $error) {
                $results['error'][$key] = $this->translator->translate($error);
            }
            if ($results['error'] == false) {
                $newPasswordMd5 = User::generatePassword($params['newPassword']);

                $user->setOldPasswordSecond($user->getOldPasswordFirst());
                $user->setOldPasswordFirst($user->getPassword());
                $user->setPassword($newPasswordMd5);
                $user->setFirstLogin(0);
                $this->em->flush();
                $this->em->clear();

                $results['status'] = 1;
                $results['agreePolicy'] = $user->getAgreePolicy();

                $userIdentity = PrivateSession::getData('userIdentity');
                $userIdentity["password"] = $newPasswordMd5;
                PrivateSession::setData('userIdentity', $userIdentity);
                /* begin send mail */
                $resultSendMail = $this->uacService->sendMail($user, $params['newPassword']);
                if ($resultSendMail["status"] != 1) {
                    $results['error']["all"] = $resultSendMail["msg"];
                }
            }
        }
        return $this->getResponse()->setContent(Json::encode($results));
    }

    public function changePasswordFirstAction() {
        $errors = array();
        $user = $this->identity;

        if ($user->getFirstLogin() != 1) {
            return $this->redirect()->toRoute('profile');
        }
        $form = new ChangePasswordFirstForm('change-password-first', $user);
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            'form' => $form,
            'user' => $user,
            'errors' => $errors
        ));
        return $viewModel;
    }

    public function ajaxChangePasswordFirstAction() {
        $user = $this->identity;
        $results = array(
            'status' => 0,
            'error' => array(),
            'agreePolicy' => 0
        );
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getPost();

            $params['txtUserID'] = trim($params['txtUserID']);
            $params['txtEmailAddress'] = trim($params['txtEmailAddress']);
            $params['txtFistname'] = trim(str_replace(" ", "", $params['txtFistname']));
            $params['txtlastname'] = trim(str_replace(" ", "", $params['txtlastname']));

            $error_profile = $this->uacService->validateProfile($params, $user, $this->em);
            $error_password = $this->uacService->validatePassword($user, $params);
            $errors = array_merge($error_profile, $error_password);

            foreach ($errors as $key => $error) {
                $results['error'][$key] = $this->translator->translate($error);
            }
            if ($results['error'] == false) {
                $newPasswordMd5 = User::generatePassword($params['newPassword']);
                $user->setUserId($params['txtUserID']);
                $user->setFirstNameKanji($params['txtFistname']);
                $user->setLastNameKanji($params['txtlastname']);
                $user->setEmailAddress($params['txtEmailAddress']);
                $user->setOldPasswordSecond($user->getOldPasswordFirst());
                $user->setOldPasswordFirst($user->getPassword());
                $user->setPassword($newPasswordMd5);
                $user->setFirstLogin(0);
                $this->em->flush();
                $this->em->clear();

                $results['status'] = 1;
                $results['agreePolicy'] = $user->getAgreePolicy();

                $userIdentity = PrivateSession::getData('userIdentity');
                $userIdentity["userId"] = $user->getUserId();
                $userIdentity["password"] = $newPasswordMd5;
                $userIdentity["firstName"] = $user->getFirstNameKanji();
                $userIdentity["lastName"] = $user->getLastNameKanji();
                $userIdentity["emailAddress"] = $user->getEmailAddress();
                PrivateSession::setData('userIdentity', $userIdentity);
                /* begin send mail */
                $resultSendMail = $this->uacService->sendMail($user, $params['newPassword']);
                if ($resultSendMail["status"] != 1) {
                    $results['error']["all"] = $resultSendMail["msg"];
                }
            }
        }
        return $this->getResponse()->setContent(Json::encode($results));
    }

    public function redirectByRole($identity) {
        /*
         * @var $identity \Application\Entity\AccessKey
         */
        $checkFirstLoginAndPolicy = 0;
        $roldeId = $identity->getRole()->getId();
        $organizationNo = $identity->getOrganizationNo();
        $userId = $identity->getUserId();
        $this->uacService->disableAccessKey($organizationNo , $userId,  $this->em);
//            Disable accesskey Here
        // user not System Administrator and first login
        if ($roldeId != 1 && $identity->getFirstLogin() == 1) {
            $checkFirstLoginAndPolicy = 1;
            return $this->redirect()->toRoute('changePasswordFirst');
        } else if (intval($identity->getAgreePolicy()) == 0) {
            $checkFirstLoginAndPolicy = 1;
            return $this->redirect()->toRoute('policy');
        }

        if ($checkFirstLoginAndPolicy == 0) {
            if (in_array($roldeId, array(1, 2, 3))) {
                // user is System Administrator, Service Manager, or Organization Supervisor
                return $this->redirect()->toRoute('org-mnt/default', array('controller' => 'org', 'action' => 'index'
                ));
            } else {
                // user other
                return $this->redirect()->toRoute('home-page/default', array('controller' => 'homepage'));
            }
        }
    }

    public function getEntityAuthenticateManager() {
        return $this->getServiceLocator()->get('doctrine.authenticationservice.orm_default');
    }

}
