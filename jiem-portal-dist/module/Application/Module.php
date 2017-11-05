<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Dantai\PrivateSession;
use Dantai\Session\SaveHandler\Doctrine as DoctrineSaveHandler;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Session\Config\SessionConfig;
use Dantai\PublicSession;

class Module {

    public function onBootstrap(MvcEvent $e) {
        
        $this->bootstrapSession($e);
        
        $eventManager = $e->getApplication()->getEventManager();
        $serviceManager = $e->getApplication()->getServiceManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $eventManager->attach(MvcEvent::EVENT_ROUTE, array(
            $this,
            'authPreDispatch'
        ));

        $eventManager->attach(MvcEvent::EVENT_FINISH, array(
            $this,
            'autoCleanLock'
        ));
        
        $eventManager->attach(MvcEvent::EVENT_FINISH, array(
            $this,
            'logActivity'
        ));
        $this->initAwsClients($serviceManager);
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function initAwsClients(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        if (!class_exists('\Dantai\Aws\Config')) {
            return;
        }
        $config = $serviceManager->get('Config');
        \Dantai\Aws\Config::setConfig($config['aws']);
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }

    public function authPreDispatch(\Zend\Mvc\MvcEvent $e) {
        // Check authentication
        if ($e->getRequest() instanceof \Zend\Console\Request) {
            return;
        }
        $serviceManager = $e->getApplication()->getServiceManager();
        $auth = $serviceManager->get('doctrine.authenticationservice.orm_default');
        // Get current action
        $routeMatch = $e->getRouteMatch();
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');
        
        if('BasicConstruction\Controller\User' == $controller){
            return;
        }

        $array_controller = explode('\\', $controller);

        $controllerCurrent = end($array_controller);
        $moduleName = reset($array_controller);
        if (!empty($moduleName) && in_array($moduleName, array('DantaiApi', 'AccessKey'))) {
            return;
        }
        
        $config = $serviceManager->get('Configuration');
        
        // Check session time out
        $userActivitySession = new Container('user_activity');
        if (isset($userActivitySession->LAST_ACTIVITY) && (time() - $userActivitySession->LAST_ACTIVITY) > $config['session']['time_out_duration']) {
            $auth->clearIdentity();
        }
        else if ($auth->hasIdentity()) {
            // Identity exists; get it
            $user = $auth->getIdentity();
        }
        $userActivitySession->LAST_ACTIVITY = time();
        
        $request = $e->getRequest();

        $routerCompare = strtolower($moduleName) . '/' . strtolower($controllerCurrent) . '/' . strtolower($action);
        if(strpos($action, '-') !== false){
            $routerCompareOther = strtolower($moduleName) . '/' . strtolower($controllerCurrent) . '/' . strtolower(str_replace("-", "", $action));
        }else{
            $routerCompareOther = $routerCompare;
        }
        $pageNotNeedLogin = array(
                                    'homepage/homepage/privacy-policy',
                                    'homepage/homepage/policy'
                                );
        $controlNotNeedLogin = array(
                                    'HomePage\Controller\Homepage'
                                );
        // Redirect to login form
        if (!isset($user)) {
            if(in_array($controller,$controlNotNeedLogin) && in_array($routerCompare,$pageNotNeedLogin)){
                return;
            }
            if ($controller != "BasicConstruction\Controller\Uac" || ($controller == "BasicConstruction\Controller\Uac" && $action != "index")) {

                if ($request->isXmlHttpRequest()) {
                    $e->getResponse()->setStatusCode(406);
                } else {
                    return $this->redirect($e, "login");
                }
            }
        } else {

            $viewModel = $e->getViewModel();
            $awesomeUser = PrivateSession::getData('userIdentity');            
            $viewModel->awesomeUser = $awesomeUser;
            // Set some infomation of ApplyEikenStatus for displaying top menu
            $viewModel->applyEikenStatus = PrivateSession::getData('applyEikenStatus');
            $viewModel->isSysAdminRole = PublicSession::isSysAdminRole();
            $viewModel->isServiceManagerRole = PublicSession::isServiceManagerRole();
            $viewModel->isOrgSupervisorRole = PublicSession::isOrgSupervisorRole();;
            if (($user->getStatus() == "Locked" && $user->getIsDelete() == 1) || $user->getStatus() == 'Disable') {
                if ($controller != "BasicConstruction\Controller\Uac" || !in_array($action, array("inactivated", "logout"))) {
                    if ($request->isXmlHttpRequest()) {
                        $e->getResponse()->setStatusCode(405);
                    } else {
                        return $this->redirect($e, "inactivated");
                    }
                }
            }

            if (!empty($awesomeUser["password"])) {
                if ($awesomeUser["password"] != $user->getPassword()) {
                    // sai pass
                    if ($request->isXmlHttpRequest()) {
                        $e->getResponse()->setStatusCode(403);
                    } else {
                        return $this->redirect($e, "logout");
                    }
                }
            }

            if ($controller != "BasicConstruction\Controller\Uac") {

                $roldeId = $user->getRole()->getId();

                if ($roldeId != 1 && $user->getFirstLogin() == 1) {
                    return $this->redirect($e, 'changePasswordFirst');
                } else
                if ($roldeId != 1 && $user->getAgreePolicy() == 0) {
                    return $this->redirect($e, 'policy');
                }

                $permission = false;
                switch ($roldeId) {
                    case 1:
                        $permission = true;
                        break;
                    case 2:
                        $permission = true;
                        break;
                    default:
                        $actionRoles = PrivateSession::getData('userIdentity');
                        $actionRoles = $actionRoles['actionRoles'];
                       
                        if (in_array($routerCompare, $actionRoles) || in_array($routerCompareOther, $actionRoles)) {
                            $permission = true;
                        }
                }

                $publicPage = array(
                    'application/index/index',
                    'basicconstruction/basicconstruction/role',
                );

                if (in_array($routerCompare, $publicPage) || (strtolower($controller) == 'homepage\controller\homepage' && $action != 'downloadEikenId')) {
                    $permission = true;
                }

                // no permission
                if ($permission == false) {
                    if ($request->isXmlHttpRequest()) {
                        $e->getResponse()->setStatusCode(401);
                    } else {
                        return $this->redirect($e, 'accessDenied');
                    }
                }
            }
        }
    }

    public function autoCleanLock(\Zend\Mvc\MvcEvent $event){
        // Ignore all ajax requests
        $request = $event->getRequest();
        if ($request instanceof \Zend\Console\Request || ($request && $request->isXmlHttpRequest())) return;
        
        $serviceManager = $event->getApplication()->getServiceManager();
        $dantaiService = $serviceManager->get('Application\Service\DantaiServiceInterface');
        $dantaiService->autoCleanLock($event);
    }
    
    public function logActivity (\Zend\Mvc\MvcEvent $event) 
    {
        if ($event->getRequest() instanceof \Zend\Console\Request) {
            return;
        }
        // Get current action
        $routeMatch = $event->getRouteMatch();
        if (!$routeMatch)
            return;
        $controller = $routeMatch->getParam('controller');
        $action = str_replace('-', '', strtolower($routeMatch->getParam('action')));
        $arrayController = explode('\\', $controller);
        $controllerCurrent = str_replace('-', '', strtolower(end($arrayController)));
        $moduleName = str_replace('-', '', strtolower(reset($arrayController)));
        
        $serviceManager = $event->getApplication()->getServiceManager();
        $config = $serviceManager->get('Configuration');
        
        if (isset($config['actions_list'][$moduleName][$controllerCurrent][$action]))
        {
            $dantaiService = $serviceManager->get('Application\Service\DantaiServiceInterface');
            $dantaiService->logActivity($moduleName,$controllerCurrent, $action, $config['actions_type'], $config['actions_list'][$moduleName][$controllerCurrent][$action], $event->getRequest()->isPost(), $routeMatch);
        }
    }
    private function redirect($e, $name) {
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
}
