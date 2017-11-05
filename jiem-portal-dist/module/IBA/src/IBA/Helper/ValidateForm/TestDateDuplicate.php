<?php

namespace IBA\Helper\ValidateForm;
use DoctrineModule\Validator\NoObjectExists;

class TestDateDuplicate extends NoObjectExists {

    public function isValid($value, $context = null) {
        $testDate = date_create($value);
        $data = \Dantai\PrivateSession::getData('userIdentity');
        $isTestDateIBA = $this->objectRepository->isExistTestDateApplyIBAOrg($data['organizationId'], 'DRAFT', $testDate,$context['idDraft']);
        $this->error(self::ERROR_OBJECT_FOUND, $value);
        return ($isTestDateIBA) ? false : true;
    }


}
