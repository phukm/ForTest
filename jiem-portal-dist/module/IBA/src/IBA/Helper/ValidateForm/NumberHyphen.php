<?php

namespace IBA\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;

class NumberHyphen extends NoObjectExists {

    public function isValid($value, $context = null) {
        if (!preg_match("/^[0-9\-]*$/", $value)) {
            $this->error(self::ERROR_OBJECT_FOUND, $value);
            return false;
        }
        return true;
    }

}
