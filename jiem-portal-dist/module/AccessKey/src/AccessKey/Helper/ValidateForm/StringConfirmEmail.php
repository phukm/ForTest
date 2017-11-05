<?php

namespace AccessKey\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;

class StringConfirmEmail extends NoObjectExists {
    public function isValid($value, $context = null) {
        if (is_array($context)) {
            if (isset($context['emailAddress']) && ($value == $context['emailAddress'])) {
                return true;
            }
        }

        $this->error(self::ERROR_OBJECT_FOUND, $value);
        return false;
    }
}
