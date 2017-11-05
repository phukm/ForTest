<?php

namespace IBA\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;

class StringFullSize extends NoObjectExists {

    public function isValid($value, $context = null) {
        
        if (preg_match("/(?:[a-zA-Z0-9-_\'!@#$%^&*()\x{ff5F}-\x{ff9F}\x{0020}])/u", $value)) {
            $this->error(self::ERROR_OBJECT_FOUND, $value);
            return false;
        } else {
            if (preg_match("/[\x{0020}]/u", $value)) {
                $this->error(self::ERROR_OBJECT_FOUND, $value);
                return false;
            }
        }
        return true;
    }

}
