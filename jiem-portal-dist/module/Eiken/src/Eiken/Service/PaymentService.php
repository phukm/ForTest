<?php

namespace Eiken\Service;

use Eiken\Service\ServiceInterface\ApplyEikenPupilServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Application\Entity\ApplyEikenLevel;
use Eiken\Service\ServiceInterface\PaymentServiceInterface;
class PaymentService implements PaymentServiceInterface,ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;




}