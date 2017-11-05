<?php
namespace PupilMnt\Form;

use Zend\InputFilter;
use Zend\Form\Element;
use Zend\Form\Form;

class UploadForm extends Form
{

    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        $this->addElements();
        $this->addInputFilter();
    }

    public function addElements()
    {
        $file = new Element\File('csvfile');
        $file->setLabel('ファイル名（*.csv）')->setAttribute('id', 'csvfile');
        $file->setAttribute('class', 'importstd-textbox');
        $this->add($file);
    }

    public function addInputFilter()
    {
        $prefixFileNameUpload = \Dantai\PrivateSession::getData('PrefixFileNameUpload');
        $inputFilter = new InputFilter\InputFilter();
        $fileInput = new InputFilter\FileInput('csvfile');
        $fileInput->setRequired(true);
        $fileInput->getValidatorChain()->attachByName('fileextension', array('csv'));
        $fileInput->getFilterChain()->attachByName('filerenameupload', array(
            'target' => './data/importfile/' . $prefixFileNameUpload . 'fileupload.csv',
            'randomize' => false,
            'overwrite' => true,
            'use_upload_name' => false
        ));
        $inputFilter->add($fileInput);
        $this->setInputFilter($inputFilter);
    }
}