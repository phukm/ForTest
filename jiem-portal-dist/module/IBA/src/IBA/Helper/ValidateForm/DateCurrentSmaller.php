<?php

namespace IBA\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;

class DateCurrentSmaller extends NoObjectExists {

    public function isValid($value, $context = null) {
        $date = \DateTime::createFromFormat('Y/m/d',$value);
        $dateValid = new \DateTime('+14 days');
        
        if($date < $dateValid){
            $this->error(self::ERROR_OBJECT_FOUND, $value);
            return false;
        }
        return true;
    }

}
