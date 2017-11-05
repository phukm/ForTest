<?php

namespace AccessKey\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;
use AccessKey\AccessKeyConst;
use Dantai\PrivateSession;

class UsertExists extends NoObjectExists
{
    public function isValid($value, $context = null)
    {
        $orgNo = '';
         foreach ($this->fields as $field) {
            $valueArray[] = $context[$field];
        }
        $value = $this->cleanSearchValue($valueArray);
        if (isset($value['organizationNo'])) {
            $orgNo = $value['organizationNo'];
        }
        $privateSession = new PrivateSession();
        $dataAccessKey = $privateSession->getData(AccessKeyConst::SESSION_ACCESS_KEY);
        if(isset($dataAccessKey['organizationNo'])){
            $orgNo = $dataAccessKey['organizationNo'];
        }
        
        if (!empty($orgNo)) {

            $data = $this->objectRepository->findOneBy(array(
                'organizationNo' => $orgNo,
                'isDelete' => 0
            ));
            if (!$data) {
                return true;
            }
        }
        $this->error(self::ERROR_OBJECT_FOUND, $value);
        return false;
    }
}
