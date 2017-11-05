<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Satellite\Controller;

use Zend\View\Model\ViewModel;
use Satellite\Service\ServiceInterface\EinaviServiceInterface;
use Dantai\Api\EinaviClient;

class EinaviController extends BaseController
{

    protected $einaviService;

    public function __construct(EinaviServiceInterface $einaviService)
    {
        $this->einaviService = $einaviService;
    }

    public function registerAction()
    {
        $viewModel = new ViewModel ();
        $agent = (object)array(
            'REMOTE_ADDR'     => $this->getRequest()->getServer('REMOTE_ADDR'),
            'HTTP_USER_AGENT' => $this->getRequest()->getServer('HTTP_USER_AGENT')
        );
        if ($this->checkIsMobile()) {
            $this->layout('layout/mobile');
            $viewModel->setTemplate("/satellite/einavi/mregister.phtml");

            return $viewModel->setVariables($this->einaviService->registerEinavi($agent));
        }
        $viewModel->setTemplate("/satellite/einavi/pregister.phtml");

        return $viewModel->setVariables($this->einaviService->registerEinavi($agent));
    }

    public function submitRegisterAction()
    {
        $agent = (object)array(
            'REMOTE_ADDR'     => $this->getRequest()->getServer('REMOTE_ADDR'),
            'HTTP_USER_AGENT' => $this->getRequest()->getServer('HTTP_USER_AGENT')
        );
        $data = $this->einaviService->submitRegisterInfo($agent);

        return $this->getResponse()->setContent($data);
    }

    public function submitLoginAction()
    {
        $agent = (object)array(
            'REMOTE_ADDR'     => $this->getRequest()->getServer('REMOTE_ADDR'),
            'HTTP_USER_AGENT' => $this->getRequest()->getServer('HTTP_USER_AGENT')
        );
        $data = $this->einaviService->submitLoginInfo($agent);

        return $this->getResponse()->setContent($data);
    }

    public function loginonAction()
    {
        $viewModel = new ViewModel ();
        if ($this->checkIsMobile()) {
            $this->layout('layout/mobile');
            $viewModel->setTemplate("/satellite/einavi/mloginon.phtml");

            return $viewModel->setVariables($this->einaviService->loginEinavi());
        }
        $viewModel->setTemplate("/satellite/einavi/ploginon.phtml");

        return $viewModel->setVariables($this->einaviService->loginEinavi());
    }

    public function userManualAction()
    {
        $viewModel = new ViewModel ();
        if ($this->checkIsMobile()) {
            $this->layout('layout/mobile');
        }

        return $viewModel;
    }

    public function getTranslate()
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        return json_encode(array(
            'MSG1'                  => $translator->translate('MSG1'),
            'emailRegex'            => $translator->translate('emailRegex'),
            'emailNotExistInEinavi' => $translator->translate('emailNotExistInEinavi'),
            'errorTechnicalIssue'   => $translator->translate('errorTechnicalIssue'),
            'passwordIsCompleted'   => $translator->translate('passwordIsCompleted'),
            'userDontNotExist'   => $translator->translate('userDontNotExist')
        ));
    }

    public function forgotPasswordAction()
    {
        $viewModel = new ViewModel(array('isMobile' => $this->checkIsMobile(),'messages' => $this->getTranslate()));
        if ($this->checkIsMobile()) {
            $this->layout('layout/mobile');
        }

        return $viewModel;
    }

    public function requestChangePasswordAction()
    {
        $app = $this->getServiceLocator()->get('Application');
        $request = $app->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $config = $this->getServiceLocator()->get('Config')['satellite_config']['api_userauthen'];
            $email = (string)$data['email'];
            $birthday = (int)$data['birthday'];
            if (filter_var($email, FILTER_VALIDATE_EMAIL) && $birthday) {
                $apiParams = array(
                    'bkeapi'            => array(
                        'proc_day'       => date("YmdHis"),
                        'mail_address'   => $email,
                        'birthday'       => $birthday,
                        'recognition_id' => 'Web'
                    ),
                    'client_user_agent' => $this->getRequest()->getServer('REMOTE_ADDR'),
                    'client_ip_address' => $this->getRequest()->getServer('HTTP_USER_AGENT')
                );
                try {
                    $result = EinaviClient::getInstance()->callRequestChangePassword($config, $apiParams);

                    return $this->getResponse()->setContent(json_encode($result->bkeapi));
                }
                catch (Exception $e) {
                    return $this->getResponse()->setContent(false);
                }
            }
        }

        return $this->getResponse()->setContent(false);
    }
}
