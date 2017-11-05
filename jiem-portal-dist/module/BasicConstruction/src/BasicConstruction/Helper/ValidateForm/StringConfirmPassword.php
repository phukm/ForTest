<?php

namespace BasicConstruction\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;

class StringConfirmPassword extends NoObjectExists {
    public function isValid($value, $context = null) {
        if (is_array($context)) {
            if($context['txtPassword'] == '' || $value == ''){
                return true;
            }
            if ($context['txtPassword'] != '' && $value != '' && $value == $context['txtPassword']) {
                return true;
            }
        }

        $this->error(self::ERROR_OBJECT_FOUND, $value);
        return false;
    }
}
