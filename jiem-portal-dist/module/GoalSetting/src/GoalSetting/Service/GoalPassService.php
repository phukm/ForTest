<?php
namespace GoalSetting\Service;

use GoalSetting\Service\ServiceInterface\GoalPassServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

class GoalPassService implements GoalPassServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;
}
