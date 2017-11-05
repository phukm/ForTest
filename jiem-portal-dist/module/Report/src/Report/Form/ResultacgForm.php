<?php
namespace Report\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\View\Helper\Placeholder;

class ResultacgForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct();
        
        $this->setAttribute('method', 'post');
       
        $this->add(array(
            'name' => 'ddlYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlYear',
                'value' => ''
            )
        ));
        $this->add(array(
            'name' => 'ddlClass',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'ddlClass',
                'placeholder' => '',
                'autocomplete' => 'off',
                'class' => 'form-control'
            ),
             'options' => array(
                'empty_option' => '',
                'value_options' => array(
                    '小学校1年相当'=>'小学校1年相当',
                    '小学校2年相当'=>'小学校2年相当',
                    '小学校3年相当'=>'小学校3年相当',
                )
            )
        ));
        
        
        $this->add(array(
            'name' => 'rdresult',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'id' => 'rdresult',
                'value' => '0'
            ),
            'options' => array(
                'value_options' => array(
                    array(
                        'value' => '0',
                        'attributes' => array(
                            'tabindex' => 5
                        ),
                        'label' => 'みなし'
                    ),
                    array(
                        'value' => '1',
                        'attributes' => array(
                            'tabindex' => 6
                        ),
                        'label' => '実数'
                    )
                )
            )
        ));
    }
}
