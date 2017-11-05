<?php
namespace Report\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Report\Service\ServiceInterface\EikenLevelRateServiceInterface;
use Application\Service\CommonService;
use Doctrine\ORM\EntityManager;
/**
 * EikenLevelRateController
 *
 * @author
 *
 * @version
 *
 */
class EikenLevelRateController extends AbstractActionController
{

    protected $org_id;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var eikenLevelRateServiceInterface
     */
    protected $eikenLevelRateService;

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(DantaiServiceInterface $dantaiService,EikenLevelRateServiceInterface $eikenLevelRateService, EntityManager $entityManager)
    {
        $this->dantaiService = $dantaiService;
        $this->eikenLevelRateService = $eikenLevelRateService;
        $this->em = $entityManager;
        $user = $this->dantaiService->getCurrentUser();
        $this->org_id = $user['organizationId'];
    }
    /**
     * The default action - show the home page
     */
    public function indexAction()
    {
        // TODO Auto-generated EikenLevelRateController::indexAction() default action
        return new ViewModel();
    }
}