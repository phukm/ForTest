<?php
namespace InvitationMnt\Factory;

use InvitationMnt\Controller\StandardController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class StandardControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $realServiceLocator = $serviceLocator->getServiceLocator();
        $dantaiService = $realServiceLocator->get('Application\Service\DantaiServiceInterface');
        $standardService = $realServiceLocator->get('InvitationMnt\Service\StandardServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');

        return new StandardController($dantaiService, $standardService, $entityManager);
    }

}