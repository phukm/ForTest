<?php

namespace InvitationMnt\Service;
use InvitationMnt\Service\ServiceInterface\StandardServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class StandardService implements StandardServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
}