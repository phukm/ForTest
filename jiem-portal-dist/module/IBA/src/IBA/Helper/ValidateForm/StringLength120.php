<?php

namespace IBA\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;

class StringLength120 extends NoObjectExists {

    public function isValid($value, $context = null) {
        $this->error(self::ERROR_OBJECT_FOUND, $value);
        $value = str_replace(array("\n","\r"), array("",""), $value);
        return mb_strlen($value,'UTF-8') < 240;
    }

}
