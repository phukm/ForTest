<?php
namespace Eiken\Form\EikenId;

use Zend\Form\Form;
use Zend\Form\Element;

class RegisterForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('getneweikenid');
        
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'getneweikenid');
        
        $this->add(array(
            'name' => 'txtFirtNameKanji',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtFirtNameKanji',
                'tabindex' => 1,
                'placeholder' => '姓'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtLastNameKanji',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtLastNameKanji',
                'tabindex' => 2,
                'placeholder' => '名'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtFirtNameKana',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtFirtNameKana',
                'tabindex' => 3,
                'placeholder' => '姓'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtLastNameKana',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtLastNameKana',
                'tabindex' => 4,
                'placeholder' => '名'
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'rdGender',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'id' => 'rdGender',
                'value' => '0'
            ),
            'options' => array(
                'value_options' => array(
                    array(
                        'value' => '1',
                        'attributes' => array(
                            'tabindex' => 5,
                            'onchange' => "$('input[name=rdGender]').removeClass('errorRadio error');"
                        ),
                        'label' => '男'
                    ),
                    array(
                        'value' => '2',
                        'attributes' => array(
                            'tabindex' => 6,
                            'onchange' => "$('input[name=rdGender]').removeClass('errorRadio error');"
                        ),
                        'label' => '女'
                    )
                )
            )
        ));
        
        $this->add(array(
            'name' => 'ddlYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlYear',
                'tabindex' => 7
            ),
            'options' => array(
                'value_options' => array()
            )
        ));
        
        $this->add(array(
            'name' => 'ddlMonth',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlMonth',
                'tabindex' => 8
            )
            ,
            'options' => array(
                'value_options' => array()
            )
        ));
        
        $this->add(array(
            'name' => 'ddlDay',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlDay',
                'tabindex' => 9
            ),
            'options' => array(
                'value_options' => array()
            )
        ));
        
        $this->add(array(
            'name' => 'txtPostalCode1',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width58',
                'id' => 'txtPostalCode1',
                'tabindex' => 10
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtPostalCode2',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width58',
                'id' => 'txtPostalCode2',
                'tabindex' => 11
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtArea',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtArea',
                'tabindex' => 13
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtAreaCode',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtAreaCode',
                'tabindex' => 15
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtTelCode1',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtTelCode1',
                'maxlength' => 13,
                'tabindex' => 17
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtCity',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtCity',
                'tabindex' => 12
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtVillage',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtVillage',
                'tabindex' => 14
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtBuilding',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtBuilding',
                'tabindex' => 16
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'txtMailAddress',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width220',
                'id' => 'txtMailAddress',
                'tabindex' => 18
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'ddlSchoolYear',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width58',
                'id' => 'ddlSchoolYear',
                'tabindex' => 22,
                'value' => ''
            ),
            'options' => array(
                'value_options' => array()
            )
        ));
        
        $this->add(array(
            'name' => 'ddlClass',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width58',
                'id' => 'ddlClass',
                'tabindex' => 23
            ),
            'options' => array(
                'value_options' => array()
            )
        ));
        $this->add(array(
            'name' => 'ddlJobCode',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlJobCode',
                'tabindex' => 19
            ),
            'options' => array(
                'empty_option' => ''
            )
        ));
        $this->add(array(
            'name' => 'ddlSchoolCode',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlSchoolCode',
                'tabindex' => 20
            ),
            'options' => array(
                'empty_option' => ''
            )
        ));
        
        $this->add(array(
            'name' => 'txtEikenPassword',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control width140',
                'id' => 'txtEikenPassword',
                'tabindex' => 24
            ),
            'options' => array()
        ));
        
        $this->add(array(
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf'
        ));
    }

    public function year()
    {
        $array = array();
        for ($i = 1990; $i < 2020; $i ++) {
            if ($i == 1990) {
                $array[0] = '';
            }
            $array[$i] = $i;
        }
        return $array;
    }

    public function month()
    {
        $array = array();
        for ($i = 1; $i < 13; $i ++) {
            if ($i == 1) {
                $array[0] = '';
            }
            $array[$i] = $i;
        }
        return $array;
    }

    public function day()
    {
        $array = array();
        for ($i = 1; $i < 32; $i ++) {
            if ($i == 1) {
                $array[0] = '';
            }
            $array[$i] = $i;
        }
        return $array;
    }
}

?>