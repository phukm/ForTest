<?php
namespace InvitationMnt\Form;

use Zend\Form\Form;

class Recommendedform extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('Recommendedform');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'txtName',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control recmlvl-text inset-shadow',
                'id' => 'txtName'
            )
        ));
        
        $this->add(array(
            'name' => 'ddbSchoolYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control recmlvl-select inset-shadow',
                'id' => 'ddbSchoolYear'
            )
        ));
        
        $this->add(array(
            'name' => 'ddbClass',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control recmlvl-select inset-shadow',
                'id' => 'ddbClass'
            ),
            'options' => array(
                ''
            )
        ));
        
        $this->add(array(
            'name' => 'ddbRecommenLevel',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control recmlvl-select inset-shadow',
                'id' => 'ddbRecommenLevel'
            )
        ));
        
        $this->add(array(
            'name' => 'ddbLevelChange',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control rcm-invi-select inset-shadow'
            )
        ));
        
        $this->add(array(
            'name' => 'ddbYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control recmlvl-select-year inset-shadow',
                'id' => 'ddbYear',
                'value' => date("Y")
            ),
            'options' => array(
                'value_options' => $this->setYear()
            )
        ));
        
        $this->add(array(
            'name' => 'ddbKai',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control recmlvl-select-year inset-shadow',
                'id' => 'ddbKai'
            ),
            'options' => array(
                ''
            )
        ));
    }

    public function setYear()
    {
        $currentYear = date("Y");
        $lst_y = array();
        for ($i = $currentYear + 2; $i >= 2010; $i --) {
            $lst_y[$i] = $i;
        }
        return $lst_y;
    }

    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
}