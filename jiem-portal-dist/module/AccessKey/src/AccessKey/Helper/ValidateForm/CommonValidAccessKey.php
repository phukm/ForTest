<?php

namespace AccessKey\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;

class CommonValidAccessKey extends NoObjectExists
{
    /**
     * @author ChungDV suggest by SunsuKe Nomura - san
     * @param type $value
     * @param type $validFunction
     * @param type $context
     * @return boolean
     */
    public function isValidForAccessKey($value, $validFunction, $context = null)
    {
        foreach ($this->fields as $field) {
            $valueArray[] = $context[$field];
        }

        $value = $this->cleanSearchValue($valueArray);

        $match = $this->objectRepository->$validFunction($value);
        if (true == $match) {
            $this->error(self::ERROR_OBJECT_FOUND, $value);

            return false;
        }

        return true;
    }

}
