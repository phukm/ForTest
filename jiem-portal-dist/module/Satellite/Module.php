<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/BasicConstruction for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Satellite;

use Dantai\Session\SaveHandler\Doctrine as DoctrineSaveHandler;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Dantai\PrivateSession;

class Module implements AutoloaderProviderInterface
{

    const FORMAT_DATE = 'Y-m-d';

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/', __NAMESPACE__)
                )
            )
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function initAwsClients(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        if (!class_exists('\Dantai\Aws\Config')) {
            return;
        }
        $config = $serviceManager->get('Config');
        \Dantai\Aws\Config::setConfig($config['aws']);
    }
    
    public function onBootstrap(MvcEvent $e)
    {
        $this->bootstrapSession($e);
        // You may not need to do this if you're doing it elsewhere in your
        // application
        $eventManager = $e->getApplication()->getEventManager();
        $serviceManager = $e->getApplication()->getServiceManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array(
            $this,
            'authPreDispatch'
        ));
        $this->setVariableToLayout($e);
        $this->initAwsClients($serviceManager);
    }

    public function authPreDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->app = $e->getApplication();
        $user = PrivateSession::getData(Constants::SESSION_SATELLITE);
        $routeMatch = $e->getRouteMatch();
        $action = $routeMatch->getParam('action');
        // validate login status
        if (!$user && $action != 'login') {
            $request = $e->getRequest();
            if ($request->isXmlHttpRequest()) {
                $e->getResponse()->setStatusCode(406);
            }
            else {
                return $this->redirect($e, 'login');
            }
        }

        // bypass login page when logged in.
        if ($user && $action === 'login') {
            return $this->redirect($e, 'satellite');
        }

        // validate current sessionId
        if($user && !$this->checkCurrentSessionId($e, $user) && $action != 'logout'){
            return $this->redirect($e, 'logout');
        }
    }

    private function redirect($e, $name)
    {
        $url = $e->getRouter()->assemble(array(), array(
            'name' => $name
        ));
        $response = $e->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);
        $response->sendHeaders();
        // When an MvcEvent Listener returns a Response object,
        // It automatically short-circuit the Application running
        // -> true only for Route Event propagation see Zend\Mvc\Application::run

        // To avoid additional processing
        // we can attach a listener for Event Route with a high priority
        $stopCallBack = function ($event) use($response) {
            $event->stopPropagation();
            return $response;
        };
        // Attach the "break" as a listener with a high priority
        $e->getApplication()
            ->getEventManager()
            ->attach(MvcEvent::EVENT_ROUTE, $stopCallBack, - 10000);
        return $response;
    }

    protected function setVariableToLayout(MvcEvent $e)
    {
        $viewMododel = $e->getApplication()->getMvcEvent()->getViewModel();
        $user = PrivateSession::getData(Constants::SESSION_SATELLITE);
        $viewMododel->paymentByCreditFlag = true;
        $viewMododel->paymentInfomationStatus = empty($user['paymentInformation']) ? 0 : $user['paymentInformation'];
    }

    public function bootstrapSession($e)
    {
        $serviceManager = $e->getApplication()->getServiceManager();
        
        $sessionManager = new SessionManager();
        
        $config = $serviceManager->get('Configuration');
        if(isset($config['session']['php_ini'])){
            $sessionConfig = new SessionConfig();
            $sessionConfig->setOptions($config['session']['php_ini']);
            $sessionManager->setConfig($sessionConfig);
        }
        $sessionManager->setSaveHandler(new DoctrineSaveHandler($serviceManager->get('doctrine.entitymanager.orm_default')));
        Container::setDefaultManager($sessionManager);
    }

    /**
     * validate are current sessionId is same as sessionId in DB.
     * @param MvcEvent $e
     * @param $user
     * @return bool
     */
    public function checkCurrentSessionId(\Zend\Mvc\MvcEvent $e, $user){
        $entityManager = $e->getApplication()->getServiceManager()->get('doctrine.entitymanager.orm_default');
        $authenKeyObj = $entityManager->getRepository('Application\Entity\AuthenticationKey')->findOneBy(
            array(
                'pupilId'         => $user['pupilId'],
                'organizationNo'  => $user['organizationNo'],
                'eikenScheduleId' => $user['eikenScheduleId'],
                'isDelete'        => 0,
            )
        );
        if(!empty($authenKeyObj) && !empty($authenKeyObj->getSessionId()) && $authenKeyObj->getSessionId() != session_id()){
            return false;
        }

        return true;
    }
}
