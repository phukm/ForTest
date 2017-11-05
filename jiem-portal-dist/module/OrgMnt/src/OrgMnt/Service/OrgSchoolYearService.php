<?php

namespace OrgMnt\Service;
use OrgMnt\Service\ServiceInterface\OrgSchoolYearServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class OrgSchoolYearService implements OrgSchoolYearServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
}