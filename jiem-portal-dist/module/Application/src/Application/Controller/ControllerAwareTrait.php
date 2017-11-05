<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

trait ControllerAwareTrait
{
    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;
    
    /**
     * 
     * @param unknown $key
     * @return string
     */
    public function translate($key)
    {
        return $this->getServiceLocator()
        ->get('MVCTranslator')
        ->translate($key);
    }
    
    /**
     * 
     * @return boolean
     */
    public function isPost(){
        return $this->getRequest() instanceof \Zend\Http\Request ? $this->getRequest()->isPost() : count($this->params()->fromPost()) > 0;
    }
    
}
