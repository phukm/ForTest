<?php
namespace Eiken\Form\EikenOrg;

use Zend\Form\Form;

class EikenExamForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('searcheikenexam');
        $this->setAttribute('method', 'post');
        $this->setAttribute('id', 'searcheikenexam');

//         $this->add(array(
//             'name' => 'txtKai',
//             'type' => 'Zend\Form\Element\Text',
//             'attributes' => array(
//                 'class' => 'form-control',
//                 'id' => 'txtKai'
//             ),
//             'options' => array()
//         ));

        $this->add(array(
            'name' => 'ddlKai',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlKai',
            ),
             'options' => array(
                'value_options' => array(
                    ''=>'',
                    1=>'1',
                    2=>'2',
                    3=>'3'
                )
            )
        ));


        $this->add(array(
            'name' => 'ddlExamName',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control ',
                'id' => 'ddlExamName',
            ),
            'options' => array(
                'empty_option' => '',
                'value_options' => array(
                    '英検' => '英検',
                    'IBA' => 'IBA'
                )
            )
        ));

        $this->add(array(
            'name' => 'dtStartDate',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'dtStartDate',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
        $this->add(array(
            'name' => 'dtEndDate',
            'type' => 'Text',
            'attributes' => array(
                'id' => 'dtEndDate',
                'class' => 'form-control'
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'ddlYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'ddlYear',
                'value' => date('Y')
            ),
            'options' => array(

            )
        ));
    }
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
}


