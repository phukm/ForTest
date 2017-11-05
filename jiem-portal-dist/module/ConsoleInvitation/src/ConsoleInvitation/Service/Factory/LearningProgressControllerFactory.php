<?php
namespace ConsoleInvitation\Service\Factory;

use ConsoleInvitation\Controller\ConsoleLearningProgressController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LearningProgressControllerFactory implements FactoryInterface
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
        $learningProgressService = $realServiceLocator->get('ConsoleInvitation\Service\LearningProgressServiceInterface');
        $entityManager = $realServiceLocator->get('doctrine.entitymanager.orm_default');
        return new ConsoleLearningProgressController($dantaiService, $learningProgressService, $entityManager);
    }
}