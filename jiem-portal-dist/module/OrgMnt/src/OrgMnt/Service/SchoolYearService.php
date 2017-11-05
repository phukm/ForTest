<?php

namespace OrgMnt\Service;
use OrgMnt\Service\ServiceInterface\SchoolYearServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class SchoolYearService implements SchoolYearServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
    }
