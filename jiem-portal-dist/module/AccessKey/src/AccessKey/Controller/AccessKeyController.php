<?php

namespace AccessKey\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use AccessKey\Service\ServiceInterface\AccessKeyServiceInterface;
use Doctrine\ORM\EntityManager;
use AccessKey\Form\AccessKeyForm;
use AccessKey\Form\CreateFirstUserForm;
use AccessKey\Form\AccessKeyToActivateForm;
use Zend\Session\Container;
use AccessKey\AccessKeyConst;
use Dantai\PrivateSession;

class AccessKeyController extends AbstractActionController {

    const LOGIN_SESSION_KEY = 'LoginSessionKey';
    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var \AccessKey\Service\AccessKeyService
     */
    protected $accessKeyService;

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(DantaiServiceInterface $dantaiService, AccessKeyServiceInterface $accessKeyService, EntityManager $entityManager) {
        $this->dantaiService = $dantaiService;
        $this->accessKeyService = $accessKeyService;
        $this->em = $entityManager;
    }

    /**
     * @author ChungDV
     * @return mixed
     */
    public function indexAction() {
        $this->layout("/layout/LoginMasterPage");
        $form = new AccessKeyForm($this->getServiceLocator());
        $isError = false;

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                $isError = true;
            }
            if ($form->isValid()) {
                $data = $form->getData();
                $privateSession = new PrivateSession();
                $privateSession->setData(AccessKeyConst::SESSION_ACCESS_KEY, $data);
                return $this->redirect()->toRoute('access-key/default', array(
                            'controller' => 'access-key',
                            'action' => 'add',
                ));
            }
        }
        $viewModel = new ViewModel();
        return $viewModel->setVariables(array(
                    'form' => $form,
                    'isError' => $isError
        ));
    }

    public function addAction() {
        $this->layout("/layout/LoginMasterPage");
        $privateSession = new PrivateSession();
        $dataAccessKey = $privateSession->getData(AccessKeyConst::SESSION_ACCESS_KEY);
        if (!$dataAccessKey) {
            return $this->redirect()->toRoute('access-key/default', array(
                        'controller' => 'access-key',
                        'action' => 'index',
            ));
        }
        $viewModel = new ViewModel();
        $form = new CreateFirstUserForm($this->getServiceLocator());
        $flagValid = false;
        $orgNameKanji = '';
        $orgItem = ($dataAccessKey['organizationNo']) ? $this->em->getRepository('Application\Entity\Organization')->findOneBy(array('organizationNo' => $dataAccessKey['organizationNo'])) : null;
        if ($orgItem) {
            $orgNameKanji = $orgItem->getOrgNameKanji();
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                $flagValid = true;
            }
            if ($form->isValid()) {
                $organizationNo = $dataAccessKey['organizationNo'];
                $accessKey = $dataAccessKey['accessKey'];
                $resultUser = $this->accessKeyService->saveFirstUser($request->getPost(), $organizationNo, $this->em);
                if ($resultUser == AccessKeyConst::SAVE_DATABASE_SUCCESS) {
                    return $viewModel->setVariables(array(
                                'form' => $form,
                                'flagValid' => $flagValid,
                                'orgNameKanji' => $orgNameKanji,
                                'status' => 1
                    ));
                }
            }
        }
        return $viewModel->setVariables(array(
                    'form' => $form,
                    'flagValid' => $flagValid,
                    'orgNameKanji' => $orgNameKanji
        ));
    }

    public function removeSessionAction() {
        $privateSession = new PrivateSession();
        $privateSession->clear(AccessKeyConst::SESSION_ACCESS_KEY);
        return $this->redirect()->toRoute('access-key/default', array('controller' => 'access-key', 'action' => 'index'));
    }

    public function isUseAccessKeyAction() {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $result['status'] = 0;
        $result['MSG'] = '';
        $request = $this->getRequest();
        if ($request->isPost()) {
            $orgNo = $request->getPost('orgNo');
            $accessKey = $request->getPost('accessKey');
            $isAccessKey = $this->em->getRepository('Application\Entity\AccessKey')->findBy(array('organizationNo' => $orgNo, 'accessKey' => $accessKey, 'status' => 'Enable'));
            if (!empty($isAccessKey)) {
                $isAccessKeyUse = $this->em->getRepository('Application\Entity\User')->findBy(array('organizationNo' => $orgNo,'statusInit' => 1));
                if (!empty($isAccessKeyUse)) {
                    $result['status'] = 1;
                    $result['MSG'] = $translator->translate('AccessKeyIsUse');
                }
            }
        }
        $result = json_encode($result);
        return $this->response->setContent($result);
    }

    public function deleteUserAction() {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $orgNo = $request->getPost('orgNo');
            $this->accessKeyService->deleteUser($orgNo);
        }
        return $this->response->setContent(1);
    }

    public function activateAction() {
        $this->layout("/layout/LoginMasterPage");
        $viewModel = new ViewModel();
        $privateSession = new \Dantai\PrivateSession();
        $dataLogin = $privateSession->getData(self::LOGIN_SESSION_KEY);
        if(empty($dataLogin)){
           return $this->redirect()->toRoute('login');
        }
        $form = new AccessKeyToActivateForm($this->getServiceLocator());
        $isError = false;

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                $isError = true;
            }
            if ($form->isValid()) {
                $orgNo = $dataLogin['orgNo'];
                $userId = $dataLogin['userId'];
                $user = $this->accessKeyService->activateUser($orgNo, $userId);
//                login
                /*@var $uacService \BasicConstruction\Service\UACService*/
                if(!empty($user)){
                    $uacService = $this->getServiceLocator()->get('BasicConstruction\Service\UACServiceInterface');
                    $uacService->authentication($user, $dataLogin, $this->em);
                    $privateSession->clear(self::LOGIN_SESSION_KEY);
                    $userActivitySession = new Container('user_activity');
                    $userActivitySession->LAST_ACTIVITY = time();
                    return $this->redirect()->toRoute('home-page/default', array('controller' => 'homepage'));
                }
            }
        }
        return $viewModel->setVariables(array(
                    'form' => $form,
                    'isError' => $isError
        ));
    }

}
