<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Action;
use Application\Entity\Role;
use Application\Entity\RoleAction;

class IndexController extends AbstractActionController
{
    public function zipcodeAction(){
        $zipcode = $this->params()->fromPost('zipcode');
        if(!$this->getRequest()->isXmlHttpRequest()){
            return $this->notFoundAction();
        }
        $dantaiService = new \Application\Service\DantaiService();
        $dantaiService->setServiceLocator($this->getServiceLocator());
        $addressInfo = $dantaiService->zipcode2Address($zipcode);
        $jsonModel = \Dantai\Utility\JsonModelHelper::getInstance();
        if(empty($addressInfo)){
            $jsonModel->setFail();
            $jsonModel->addMessage($this->getServiceLocator()->get('MvcTranslator')->translate('ZipCode_Not_Found'));
        }else{
            $jsonModel->setSuccess();
            $jsonModel->setData($addressInfo);
        }
        return new \Zend\View\Model\JsonModel($jsonModel->toArray());
    }
    
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
}
