<?php

namespace AccessKey\Helper\ValidateForm;

use DoctrineModule\Validator\NoObjectExists;
class ExpiredAccessKey extends NoObjectExists
{
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
        $expiredAccessKey = $accessKeyService->expiredAccessKey($value);
        if (!empty($expiredAccessKey)) {
            // get format current date
            $currentDate = date(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT);
            // get format current deadlineFrom
            if ($expiredAccessKey->getDeadlineFrom()) {
                $deadlineFrom = $expiredAccessKey->getDeadlineFrom()->format(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT);
                // before one day of deadlineFrom
                $beforeOneDayDeadlineFrom = date(\Dantai\Utility\DateHelper::DATE_FORMAT_MYSQL_DEFAULT, strtotime($deadlineFrom . ' -1 day'));
                // check access key expired
                if ($currentDate <= $beforeOneDayDeadlineFrom) {
                    return true;
                }
            }
        }
        $this->error(self::ERROR_OBJECT_FOUND, $value);
        return false;
    }

}
