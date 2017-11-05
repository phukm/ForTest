<?php

namespace AccessKey\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;

class StringEmailAddress extends NoObjectExists {
    public function isValid($value) {      
        $regex = '/^([a-zA-Z])+([a-zA-Z0-9_.+-])*([a-zA-Z0-9])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/';
        $regex2 = '/^([a-zA-Z]){1}\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/';
        $this->error(self::ERROR_OBJECT_FOUND, $value);
        return preg_match($regex,$value) || preg_match($regex2,$value);
    }
}
