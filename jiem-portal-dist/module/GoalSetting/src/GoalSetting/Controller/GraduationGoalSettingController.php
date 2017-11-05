<?php
namespace GoalSetting\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Doctrine\ORM\EntityManager;
use GoalSetting\Service\ServiceInterface\GraduationGoalSettingServiceInterface;
use Zend\View\Model\ViewModel;
use Dantai\PrivateSession;
use GoalSetting\Form\GraduationGoal\GraduationGoalSearchForm;
use GoalSetting\Form\GraduationGoal\GraduationGoalNationalSearchForm;
use GoalSetting\Form\GraduationGoal\GraduationGoalAcquisitionRateSearchForm;
use GoalSetting\Form\GraduationGoal\GraduationGoalAcquisitionRateEditForm;

/**
 * GraduationGoalSettingController
 *
 * @author
 *
 * @version
 *
 */
class GraduationGoalSettingController extends AbstractActionController
{

    protected $graduationGoalSettingService;

    protected $em;

    private $id_org;

    private $code_org;

    private $no_org;

    public function __construct(GraduationGoalSettingServiceInterface $graduationGoalSetting, EntityManager $entityManager)
    {
        $this->graduationGoalSettingService = $graduationGoalSetting;
        $this->em = $entityManager;
        $user = PrivateSession::getData('userIdentity');
        $this->no_org = $user['organizationNo'];
        $this->id_org = $user['organizationId'];
        $this->code_org = $user['organizationCode'];
    }

    /**
     * Create: TuanNV21
     * Update: Uthv
     * UpdateAt: 30/09/2015
     * *
     */
    public function indexAction()
    {
        //This functionality is temporarily closed on production GNCCNCJDM-258
        return $this->redirect()->toUrl('/');
        
        $viewModel = new ViewModel();
        $em = $this->getEntityManager();

        $org = $em->getReference('Application\Entity\Organization', $this->id_org);
        $cityCode = NULL;
        if ($org->getCity() != NULL)
            $cityCode = $org->getCity()->getCityCode();
        //         check clear data session
        $token = $this->params('token');
        if(empty($token)){
            PrivateSession::clear('SearchGoalSetting');
            $token = uniqid();
            return $this->redirect()->toRoute('goal-setting/default', array(
                'controller' => 'graduationgoalsetting',
                'action' => 'index',
                'token' => $token
            ));
        }
        $search = PrivateSession::getData('SearchGoalSetting');
        if (empty($search)) {
            $search = array(
                'edit' => 0,
                'rdGoalSetting' => 1,
                'ddbYear' => $this->graduationGoalSettingService->formatYear()
            );
            PrivateSession::setData('SearchGoalSetting', $search);
        }
        $form = new GraduationGoalSearchForm();
        $form->setListYear($search['ddbYear']);
        // form National
        $formNational = new GraduationGoalNationalSearchForm();
        $formNational->setListCity($this->graduationGoalSettingService->listCity(), $cityCode);
        $formNational->setListSchoolYear(false, false);
        $formNational->setListOrg($this->graduationGoalSettingService->getListOrg(), $this->code_org);
        $formAcquisitionRateEdit = new GraduationGoalAcquisitionRateEditForm();
        $formAcquisitionRate = new GraduationGoalAcquisitionRateSearchForm();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsMessages = array(
            'MSG1' => $translator->translate('MSG1'),
            'MSG25' => $translator->translate('MSG25'),
            'MSG00025' => $translator->translate('MSG00025'),
            'MSG17' => $translator->translate('MSG17')
        );
        $viewModel->setVariables(array(
            'form' => $form,
            'formNational' => $formNational,
            'formAcquisitionRateEdit' => $formAcquisitionRateEdit,
            'formAccquisitionRate' => $formAcquisitionRate,
            'searchbox' => $search,
            'jsMessages' => json_encode($jsMessages),
            'token'=>$token
        ));
        return $viewModel;
    }

    public function graduationgoalsearchAction()
    {
        $posyear = $this->params()->fromPost('ddbYear');

        if (isset($posyear)) {
            return $this->graduationGoalSettingService->getGraduationGoalSearch($this->params(), $this->getResponse(), $this->getRequest());
        } else {
            return $this->graduationGoalSettingService->listpassTheExam($this->params(), $this->getResponse(), $this->getRequest());
        }
    }

    public function listSchoolYearAction()
    {
        return $this->graduationGoalSettingService->getListSchoolYear($this->params(), $this->getResponse(), $this->getRequest());
    }

    public function updateGraduationGoalAction()
    {
        $this->graduationGoalSettingService->treatmentUpdate($this->params(), $this->getResponse(), $this->getRequest());
        $search = PrivateSession::getData('SearchGoalSetting');
        if (empty($search)) {
            $search = array(
                'edit' => 0,
                'rdGoalSetting' => 0,
                'ddbYear' => $this->graduationGoalSettingService->formatYear()
            );
        } else {
            $search['edit'] = 0;
        }
       $token = $this->params()->fromPost('tokenYear',$this->params()->fromPost('tokenGra',uniqid()));
        PrivateSession::setData('SearchGoalSetting', $search);
        return $this->redirect()->toRoute(null, array(
            'module' => 'goalsetting',
            'controller' => 'graduationgoalsetting',
            'action' => 'index',
            'token' => $token
        ));
    }

    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
}