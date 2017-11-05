<?php
namespace OrgInforManagement\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class ClassmanagerForm extends Form
{

    public function __construct($name = null)
    {
        parent::__construct('classmanager');

        $start = '';
        $end = '';

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'year',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'year',
                'value' => $this->yearnow()
            ),
            'options' => array(
                'label' => '',
                'value_options' => $this->year($start, $end)
            )
        ));

        $this->add(array(
            'name' => 'school_year',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'school_year'
            ),
            'options' => array(
                'label' => '',
                'value_options' => $this->shoolyear()
            )
        ));

        $this->add(array(
            'name' => 'school_year_add',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'school_year_add'
            ),
            'options' => array(
                'label' => '',
                'value_options' => $this->shoolyear_add()
            )
        ));

        $this->add(array(
            'name' => 'classname',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'classname',
                'class' => 'form-control'
            ),
            'options' => array()
        ));

        $this->add(array(
            'name' => 'sizes',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'id' => 'sizes',
                'class' => 'form-control'
            ),
            'options' => array()
        ));
    }

    // public function year(){

    // $array =array();
    // for($i=1990; $i < 2020;$i++ ){
    // if($i==1990){$array[0]='';}
    // $array[$i] = $i;
    // }
    // return $array;
    // }
    function year($start, $end, $step = 1)
    {
        $end = date('Y') - 50;
        $start = date('Y') + 20;

        $range = array();

        foreach (range($start, $end) as $index) {

            if (! (($index - $end) % $step)) {
                $range[$index] = $index;
            }
        }

        return $range;
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

    public function shoolyear()
    {
        $array = array();
        $array['小学校1年相当'] = '小学校1年相当';
        $array['小学校2年相当'] = '小学校2年相当';
        $array['小学校3年相当'] = '小学校3年相当';
        $array['小学校4年相当'] = '小学校4年相当';
        $array['小学校5年相当'] = '小学校5年相当';
        $array['小学校6年相当'] = '小学校6年相当';
        $array['中学校1年相当'] = '中学校1年相当';
        $array['中学校2年相当'] = '中学校2年相当';
        $array['中学校3年相当'] = '中学校3年相当';
        $array['高校1年相当'] = '高校1年相当';
        $array['高校2年相当'] = '高校2年相当';
        $array['高校3年相当'] = '高校3年相当';
        $array['大学1年相当'] = '大学1年相当';
        $array['大学2年相当'] = '大学2年相当';
        $array['大学3年相当'] = '大学3年相当';
        $array['大学4年相当'] = '大学4年相当';
        return $array;
    }

    public function shoolyear_add()
    {
        $array = array();
        $array[0] = '';
        $array['小学校1年相当'] = '小学校1年相当';
        $array['小学校2年相当'] = '小学校2年相当';
        $array['小学校3年相当'] = '小学校3年相当';
        $array['小学校4年相当'] = '小学校4年相当';
        $array['小学校5年相当'] = '小学校5年相当';
        $array['小学校6年相当'] = '小学校6年相当';
        $array['中学校1年相当'] = '中学校1年相当';
        $array['中学校2年相当'] = '中学校2年相当';
        $array['中学校3年相当'] = '中学校3年相当';
        $array['高校1年相当'] = '高校1年相当';
        $array['高校2年相当'] = '高校2年相当';
        $array['高校3年相当'] = '高校3年相当';
        $array['大学1年相当'] = '大学1年相当';
        $array['大学2年相当'] = '大学2年相当';
        $array['大学3年相当'] = '大学3年相当';
        $array['大学4年相当'] = '大学4年相当';
        return $array;
    }

    public function yearnow()
    {
        return date('Y');
    }
}