<?php

namespace OrgMnt\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ClasssearchForm extends Form {
	public function __construct($name = null) {
		parent::__construct ( 'classmanager' );

		$this->setAttribute ( 'method', 'post' );

		$this->add ( array (
				'name' => 'year',
				'type' => 'Zend\Form\Element\Select',
				'attributes' => array (
						'class' => 'form-control',
						'id'   => 'year',
				        'value' => '',
				),

		) );

		$this->add ( array (
		    'name' 	=> 'school_year_add',
		    'type' 	=> 'Zend\Form\Element\Select',
		    'attributes' => array (
		        'class' => 'form-control' ,
		        'id' 	=> 'school_year_add',
		    		'value' => '',
		    ),
		) );


	}

}