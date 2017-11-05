<?php
namespace Report\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\View\Helper\Placeholder;

class CseScoreTotalForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct();

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'orgshoollYear',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'orgshoollYear',
                'placeholder' => '',
                'class' => 'form-control',
                'value' => '1'
            ),
              'options' => array(
                'value_options' => array(
                    '1'=> '小学校1年相当',
                    '2'=> '小学校2年相当',
                    '3'=> '小学校3年相当'
                )
            )
        ));
        $this->add(array(
            'name' => 'ddlYear1',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'ddlYear1',
                'placeholder' => '',
                'class' => 'form-control',
                'value' => '2014'
            ),
            'options' => array(
                'value_options' => array(
                    '2017'=> '2017',
                   '2016'=> '2016',
                   '2015'=> '2015',
                   '2014'=> '2014',
                   '2013'=> '2013',
                   '2012'=> '2012',
                   '2011'=> '2011',
                   '2010'=> '2010',
                )
            )
        ));
        $this->add(array(
            'name' => 'ddlYear2',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'ddlYear2',
                'placeholder' => '',
                'class' => 'form-control',
                'value' => '2015'
            ),
            'options' => array(
                'value_options' => array(
                   '2017'=> '2017',
                   '2016'=> '2016',
                   '2015'=> '2015',
                   '2014'=> '2014',
                   '2013'=> '2013',
                   '2012'=> '2012',
                   '2011'=> '2011',
                   '2010'=> '2010',
                )
            )
        ));
        $this->add(array(
            'name' => 'ddlClass',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'ddlClass',
                'placeholder' => '',
                'autocomplete' => 'off',
                'class' => 'form-control',
                'value' => '0'
            ),
             'options' => array(
                'empty_option' => '',
                'value_options' => array(
                    '0'=>'Class 1',
                    '1'=>'Class 2',
                    '2'=>'Class 3',
                )
            )
        ));
        $this->add(array(
            'name' => 'testName',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'testName',
                'autocomplete' => 'off',
                'class' => 'form-control'
            ),
            'options' => array(
                'empty_option' => '',
                'value_options' => array(
                    ''=>'',
                    '0'=>'英検',
                    '1'=>'英検IBA',
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
                        'label' => '団体'
                    ),
                    array(
                        'value' => '1',
                        'attributes' => array(
                            'tabindex' => 6
                        ),
                        'label' => '学年'
                    ),
                    array(
                        'value' => '2',
                        'attributes' => array(
                            'tabindex' => 7
                        ),
                        'label' => 'クラス'
                    )
                )
            )
        ));
    }
}
