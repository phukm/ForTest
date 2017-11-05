<?php

namespace AccessKey\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;

class StringUserId extends NoObjectExists {

    public function isValid($value) {     
        if($value != '' && preg_match('/^[a-zA-Z0-9-_\x{FF61}-\x{FFDC}\x{FFE8}-\x{FFEE}]*$/u',$value) && preg_match('/^[a-zA-Z]{1}/',$value)){
            if (mb_strlen($value,'UTF-8') > 3 && mb_strlen($value,'UTF-8') < 32) {
                return true;
            }
        }
        $this->error(self::ERROR_OBJECT_FOUND, $value);
        return false;
    }

}
