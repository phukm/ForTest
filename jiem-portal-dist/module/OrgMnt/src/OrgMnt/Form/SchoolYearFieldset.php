<?php
namespace OrgMnt\Form;

use Application\Entity\SchoolYear;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class SchoolYearFieldset extends Fieldset implements InputFilterProviderInterface
{

    public function __construct()
    {
        parent::__construct('schoolyear');
        
        $this->setHydrator(new ClassMethodsHydrator(false))->setObject(new SchoolYear());
        
        $this->add(array(
            'name' => 'name',
            'options' => array(
                'label' => ''
            ),
            'attributes' => array(
                'required' => 'required'
            )
        ));
    }

    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return array(
            'name' => array(
                'required' => true
            )
        );
    }
}