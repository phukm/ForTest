<?php

namespace BasicConstruction\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;

class StringPassword extends NoObjectExists {
    public function isValid($value) {      
        if (preg_match('/^[a-zA-Z0-9-_\'!@#$%^&*()\x{ff5F}-\x{ff9F}\x{0020}]*$/u',$value) && mb_strlen($value,'UTF-8') > 0) 
        {
            if(preg_match('/(:?[a-z])/',$value) && preg_match('/(:?[A-Z])/',$value) && preg_match('/(:?[0-9])/',$value)){
                return true;
            }
        }
        $this->error(self::ERROR_OBJECT_FOUND, $value);
        return false;
    }
}
