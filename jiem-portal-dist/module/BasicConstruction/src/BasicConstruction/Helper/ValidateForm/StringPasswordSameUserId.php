<?php

namespace BasicConstruction\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;

class StringPasswordSameUserId extends NoObjectExists {
    public function isValid($value, $context = null) {

        if (is_array($context)) {
            if (isset($context['txtUserName']) && ($value != $context['txtUserName'])) {                     
                return true;
            }
        }

        $this->error(self::ERROR_OBJECT_FOUND, $value);
        return false;
    }
}
