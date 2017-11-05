<?php

namespace OrgMnt\Service;
use OrgMnt\Service\ServiceInterface\UserServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class UserService implements UserServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
}