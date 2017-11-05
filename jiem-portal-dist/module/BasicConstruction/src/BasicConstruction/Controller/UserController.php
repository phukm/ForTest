<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/BasicConstruction for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace BasicConstruction\Controller;

use Aws\Ec2\Exception\Ec2Exception;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use BasicConstruction\Form\ForgotPasswordOrUserIDlForm;
use BasicConstruction\Form\ResetPasswordForm;
use BasicConstruction\Service\ForgotUserService;

class UserController extends AbstractActionController
{
    /**
     * @var ForgotUserService
     */
    protected $forgotUserService;

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->forgotUserService = $this->getServiceLocator()->get('BasicConstruction.ForgotUserService');
        parent::onDispatch($e);
        $this->layout('/layout/LoginMasterPage');
    }
    
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    private function translate($message)
    {
        return $this->getServiceLocator()->get('MvcTranslator')->translate($message);
    }
    
    public function resetPasswordAction()
    {
        $form = new ResetPasswordForm($this->getServiceLocator());
        $token = $this->getRequest()->getQuery('token');
        if ($this->getRequest()->getPost('txtToken')) {
            $token = $this->getRequest()->getPost('txtToken');
        }
        $flagValid = true;
        $isExpired = false;
        $jsExpiredMessage = $this->translate('MSG7-forgot-password-mail-expired-time');
        $forgotPasswordItem = $this->getEntityManager()->getRepository('Application\Entity\ForgotPasswordToken')->findOneBy(array('token' => $token, 'isDelete' => 0));
        if ($forgotPasswordItem && $token) {
            $isExpired = true;
            $userItem = $this->getEntityManager()->getRepository('Application\Entity\User')->find($forgotPasswordItem->getUserId());
            if ($this->getRequest()->isPost()) {
                $form->setData($this->getRequest()->getPost());
                $forgotPasswordItem = $this->getEntityManager()->getRepository('Application\Entity\ForgotPasswordToken')->findOneBy(array('token' => $token, 'isDelete' => 0));
                if ($forgotPasswordItem && $forgotPasswordItem->getToken()) {
                    if (!$form->isValid()) {
                        $flagValid = false;
                    }
                    else if (!$this->forgotUserService->isExpired($forgotPasswordItem->getUpdateAt())) {
                        $isExpired = false;
                    }
                    if ($flagValid && $isExpired) {
                        $newPassword = $userItem->generatePassword($this->getRequest()->getPost('txtPassword'));
                        $result = $this->forgotUserService->savePassword($userItem->getId(), $newPassword, $userItem->getPassword(), $userItem->getOldPasswordFirst(), $forgotPasswordItem->getId());
                    }
                }
            }
        }
        
        return new ViewModel(array(
            'form'                 => $form,
            'flagValid'            => $flagValid,
            'isExpired'            => $isExpired,
            'jsExpiredMessage'     => isset($jsExpiredMessage) ? $jsExpiredMessage : '',
            'userItem'             => isset($userItem) ? $userItem : '',
            'token'                => $token,
            'resetPasswordSuccess' => $this->translate('resetPasswordSuccess'),
            'result'               => isset($result) ? $result : false
        ));
    }

    public function forgotAction()
    {
        $form = new ForgotPasswordOrUserIDlForm($this->getServiceLocator());
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if (!$form->isValid()) {
                $flagValid = true;
            }
            $dataForgot = $this->params()->fromPost();
            $userItem = $this->getEntityManager()->getRepository('Application\Entity\User')->findOneBy(array('emailAddress' => $dataForgot['txtEmail'], 'isDelete' => 0));
            if ($userItem) {
                $token = $dataForgot['radioOption'] ? $this->forgotUserService->generateTokenForgot($userItem) : null;
                $this->sendMail($dataForgot['radioOption'], $userItem, $userItem->getOrganization(), $token);
                $jsMessage = sprintf($this->translate('MSG7-forgot-password-mail-forgot-alert'), $dataForgot['txtEmail']);
            }
        }

        return new ViewModel(array(
            'form'      => $form,
            'flagValid' => isset($flagValid) ? $flagValid : '',
            'jsMessage' => isset($jsMessage) ? $jsMessage : ''
        ));
    }

    /**
     *
     * @param type $mailType
     * @param \Application\Entity\User $userItem
     * @param type $token
     * @return type
     */
    public function sendMail($mailType, $userItem, $orgItem, $token = null)
    {
        $startTime = microtime(true);
        $subject = ($mailType) ? $this->translate('MSG7-forgot-password-mail-forgot-password-title') : $this->translate('MSG7-forgot-password-mail-forgot-userid-title');
        $template = ($mailType) ? 'forgot-password.phtml' : 'forgot-userid.phtml';
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true)
            ->setTemplate('basic-construction/user/email-template/' . $template)
            ->setVariables(array(
                'userItem' => $userItem,
                'orgItem'  => $orgItem,
                'token'    => $token
            ));
        $mailBody = $this->getServiceLocator()->get('viewrenderer')->render($viewModel);
        $listMailTo = array($userItem->getEmailAddress());
        if (empty($listMailTo)) {
            return;
        }
        try {
            $ses = \Dantai\Aws\AwsSesClient::getInstance();
            $ses->send($subject, $mailBody, $listMailTo);
        }
        catch (\Exception $e) {
            $logPath = DATA_PATH . '/sendMailForgotPassword_' . date('Ymd') . '.txt';
            $stream = @fopen($logPath, 'a', false);
            if ($stream) {
                $writer = new \Zend\Log\Writer\Stream($logPath);
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $time = microtime(true) - $startTime;
                $logger->info('REQUEST: ' . \Zend\Json\Json::encode($subject)
                    . ' - RESPONSE: ' . $e
                    . ' - EXECUTE TIME: ' . (string)$time);
            }

            return;
        }
    }
}
