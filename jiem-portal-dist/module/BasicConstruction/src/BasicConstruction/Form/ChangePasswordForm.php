<?php

namespace BasicConstruction\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ChangePasswordForm extends Form {
	public function __construct($name = null) {
		parent::__construct ( );
		
		$this->setAttribute ( 'method', 'post' );
		
		$this->add(array(
				'name' => 'oldPassword',
				'type' => 'Zend\Form\Element\Password',
				'attributes' => array(
					'class' => 'form-control inset-shadow text-size',
					'id' => 'oldPassword',
				    'autofocus' => 'autofocus',
 					//'required' => 'required',
				),
				'options' => array(
				),
		));
		
		$this->add(array(
				'name' => 'newPassword',
				'type' => 'Zend\Form\Element\Password',
				'attributes' => array(
					'class' => 'form-control inset-shadow text-size',
					'id' => 'newPassword',
					//'required' => 'required',
				),
				'options' => array(
				),
		));
		
		$this->add(array(
				'name' => 'confirmNewPassword',
				'type' => 'Zend\Form\Element\Password',
				'attributes' => array(
					'class' => 'form-control inset-shadow text-size',
					'id' => 'confirmNewPassword',
					//'required' => 'required',
				),
				'options' => array(
				),
		));
		
	}

}