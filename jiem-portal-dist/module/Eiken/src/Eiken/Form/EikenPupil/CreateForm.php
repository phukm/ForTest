<?php
namespace Eiken\Form\EikenPupil;

use Zend\Form\Element;
use Zend\Form\Form;

class CreateForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('form-appeik-level');

        $this->setAttribute('method', 'post');

        $this->setAttribute('action', '/eiken/eikenpupil/save');

        $this->add(array(
            'name' => 'feeFirstTime',
            'type' => 'Zend\Form\Element\Radio',
            'attributes' => array(
                'id' => 'feeFirstTime'
            ),
            'options' => array(
                'value_options' => array(
                    '1' => 'はい',
                    '0' => 'いいえ'
                )
            )
        ));

        $this->add(array(
            'name' => 'firstPassedTime',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'firstPassedTime'
            )
        ));

        $this->add(array(
            'name' => 'areaNumber1',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'areaNumber1'
            )
        ));

        $this->add(array(
            'name' => 'areaPersonal1',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'areaPersonal1'
            )
        ));

        $this->add(array(
            'name' => 'cityId1',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'cityId1',
                'onchange' => 'eikenPupil.loadMainHallAddress($(this).val(), "mainHallAddressId1")'
            )
        ));

        $this->add(array(
            'name' => 'mainHallAddressId1',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'mainHallAddressId1'
            )
        ));

        $this->add(array(
            'name' => 'cityId2',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'cityId2',
                'onchange' => 'eikenPupil.loadMainHallAddress($(this).val(), "mainHallAddressId2")'
            )
        ));

        $this->add(array(
            'name' => 'mainHallAddressId2',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'mainHallAddressId2'
            )
        ));

        $this->add(array(
            'name' => 'csrf',
            'type' => 'Zend\Form\Element\Csrf'
        ));
        $this->add(array(
            'name' => 'app_eik_id',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'app_eik_id',
                'type' => 'hidden'
            )
        ));
    }
}