<?php

namespace Eiken\Factory;

use Eiken\Controller\PaymentController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PaymentControllerFactory implements FactoryInterface
{
    /**
     * Create Service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        $dantaiService        = $realServiceLocator->get('Application\Service\DantaiServiceInterface');
        $paymentService         = $realServiceLocator->get('Eiken\Service\PaymentServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');

        return new PaymentController($dantaiService, $paymentService, $entityManager);
    }
}