<?php
namespace Report\Service;

use Report\Service\ServiceInterface\EikenLevelRateServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class EikenLevelRateService implements EikenLevelRateServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
}
