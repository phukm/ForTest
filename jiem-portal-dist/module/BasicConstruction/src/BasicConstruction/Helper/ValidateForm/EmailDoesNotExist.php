<?php

namespace BasicConstruction\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;

class EmailDoesNotExist extends NoObjectExists
{
    public function isValid($value)
    {
        $emailList = $this->objectRepository->findOneBy(array('emailAddress' => trim($value), 'isDelete' => 0));
        if($emailList){
            return true;
        }
        $this->error(self::ERROR_OBJECT_FOUND, $value);
        return false;        
    }
}