<?php
namespace InvitationMnt\Factory;

use InvitationMnt\Controller\TemplateController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TemplateControllerFactory implements FactoryInterface
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
        $templateService = $realServiceLocator->get('InvitationMnt\Service\TemplateServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');

        return new TemplateController($dantaiService, $templateService, $entityManager);
    }

}