<?php

namespace BasicConstruction\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;

class StringPasswordSameLastest extends NoObjectExists {
    public function isValid($value, $context = null) {
        if (is_array($context)) {
            if (isset($context['txtUserName'])) {      
                $userItem = $this->objectRepository->findOneBy(array('userId' => trim($context['txtUserName']), 'organizationNo' => trim($context['txtOrganizationNo']), 'isDelete' => 0));
                $newPassword = $userItem::generatePassword(trim($value));
                if($userItem && $userItem->getPassword() != $newPassword && $userItem->getOldPasswordFirst() != $newPassword && $userItem->getOldPasswordSecond() != $newPassword){
                    return true;
                }
            }
        }

        $this->error(self::ERROR_OBJECT_FOUND, $value);
        return false;
    }
}
