<?php

namespace AccessKey\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;
use AccessKey\AccessKeyConst;
use Dantai\PrivateSession;

class EmailExists extends NoObjectExists
{
    public function isValid($value, $context = null)
    {
        $emailAddress = '';
         foreach ($this->fields as $field) {
            $valueArray[] = $context[$field];
        }
        $value = $this->cleanSearchValue($valueArray);
        if (isset($value['emailAddress'])) {
            $emailAddress = $value['emailAddress'];
        }
        if (!empty($emailAddress)) {

            $data = $this->objectRepository->findBy(array(
                'emailAddress' => $emailAddress,
            ));
            if (empty($data)) {
                return true;
            }
        }
        $this->error(self::ERROR_OBJECT_FOUND, $value);
        return false;
    }
}
