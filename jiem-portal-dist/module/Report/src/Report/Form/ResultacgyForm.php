<?php
namespace Report\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\View\Helper\Placeholder;

class ResultacgyForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct();
        
        $this->setAttribute('method', 'post');
        
    
    }
}
