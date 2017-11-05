<?php

namespace AccessKey\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;

class WrongAccessKey extends NoObjectExists
{
    const LOGIN_SESSION_KEY = 'LoginSessionKey';
    protected $serviceLocator;
    
    public function __construct(array $options) {
        $this->serviceLocator = $options['serviceLocator'];
        parent::__construct($options);
    }
    
    public function isValid($value, $context = null)
    {
        foreach ($this->fields as $field) {
            $valueArray[] = $context[$field];
        }
        $value = $this->cleanSearchValue($valueArray);
        $accessKeyService = $this->serviceLocator->get('AccessKey\Service\AccessKeyServiceInterface');
        $privateSession = new \Dantai\PrivateSession();
        $dataLogin = $privateSession->getData(self::LOGIN_SESSION_KEY);
        $orgNo = '';
        if(!empty($dataLogin) && array_key_exists('orgNo', $dataLogin)){
            $orgNo = $dataLogin['orgNo'];
        }
        if(array_key_exists('organizationNo', $value)){
            $orgNo = $value['organizationNo'];
        }
        if (!empty($value['accessKey']) && !empty($orgNo)) {
            $data = $accessKeyService->wrongAccessKey($value);
            if ($data) {
                return true;
            }
        }
        $this->error(self::ERROR_OBJECT_FOUND, $value);
        return false;
    }

}
