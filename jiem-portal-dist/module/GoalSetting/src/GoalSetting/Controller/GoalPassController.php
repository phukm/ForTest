<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/GoalSetting for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace GoalSetting\Controller;

use Application\Service\CommonService;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Dantai\Utility\CharsetConverter;
use Doctrine\ORM\Query\AST\Functions\TrimFunction;
use GoalSetting\Service\ServiceInterface\GoalPassServiceInterface;
use GoalSetting\Form\GoalPassForm;
use Zend\Json\Json;

class GoalPassController extends AbstractActionController
{

    protected $org_id;

    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;

    /**
     *
     * @var GoalPassServiceInterface
     */
    protected $goalPassService;

    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(DantaiServiceInterface $dantaiService, GoalPassServiceInterface $goalPassService, EntityManager $entityManager)
    {
        $this->dantaiService = $dantaiService;
        $this->goalPassService = $goalPassService;
        $this->em = $entityManager;
        $user = $this->dantaiService->getCurrentUser();
        $this->org_id = $user['organizationId'];
    }

    // goalsetting/goalpass/years/
    public function goalOfYearsAction()
    {
        $mess = false;
        $viewModel = new ViewModel();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $form = new GoalPassForm();
        $goal = array();
        $levelName = '';
        $eikenLevel = 0;
        $year = $this->getCurrentYear();
        $org = $this->em->getRepository('Application\Entity\Organization')->find($this->org_id);
        if($org->getOrganizationCode() == '01')
        {
            $eikenLevel = 5;
            $levelName = $this->em->getRepository('Application\Entity\EikenLevel')->find($eikenLevel)->getLevelName();
        }
        elseif($org->getOrganizationCode() == '05')
        {
            $eikenLevel = 4;
            $levelName = $this->em->getRepository('Application\Entity\EikenLevel')->find($eikenLevel)->getLevelName();
        }

        $schoolyear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->org_id);
        foreach ($schoolyear as $key => $value) {
            $numberPass1 = $this->em->getRepository('\Application\Entity\ActualExamResult')->getNumberPass($year, $this->org_id, $schoolyear[$key]['id'], $eikenLevel);
            $numberPass2 = $this->em->getRepository('\Application\Entity\ActualExamResult')->getNumberPass($year - 1, $this->org_id, $schoolyear[$key]['id'], $eikenLevel);
            $numberPass3 = $this->em->getRepository('\Application\Entity\ActualExamResult')->getNumberPass($year - 2, $this->org_id, $schoolyear[$key]['id'], $eikenLevel);
            $totalStudy1 = $this->em->getRepository('\Application\Entity\ClassJ')->getTotalStudentByYearAndSchoolYear($this->org_id, $year, $schoolyear[$key]['id']);
            $totalStudy2 = $this->em->getRepository('\Application\Entity\ClassJ')->getTotalStudentByYearAndSchoolYear($this->org_id, $year - 1, $schoolyear[$key]['id']);
            $totalStudy3 = $this->em->getRepository('\Application\Entity\ClassJ')->getTotalStudentByYearAndSchoolYear($this->org_id, $year - 2, $schoolyear[$key]['id']);
            $goal[$key][1] = ($totalStudy1)?round($numberPass1 / $totalStudy1 * 100) : 0;
            $goal[$key][2] = ($totalStudy2)?round($numberPass2 / $totalStudy2 * 100) : 0;
            $goal[$key][3] = ($totalStudy3)?round($numberPass3 / $totalStudy3 * 100) : 0;
        }
        $viewModel->setVariables(array(
            'form' => $form,
            'mess' => $mess,
            'schoolyear' => $schoolyear,
            'levelName' => $levelName,
            'goal' => $goal,
            'year' => $year
        ));

        return $viewModel;
    }

    // goalsetting/goalpass/provincialcity
    public function provincialCityAction()
    {
        $mess = false;
        $viewModel = new ViewModel();
        $arrayPupil = array(0,0,0,0,0,0,0,0);
        $arrayCity = array();
        $arrayNationwide = array();
        $org = false;
        $form = new GoalPassForm();
        $year = false;
        $searchVisible = 0;
        $isset = $this->params()->fromPost();
        if (!empty($isset)) {
            $searchVisible = 1;
            $year = $isset['ddbYear'];
            $arrayCity = $arrayPupil;
            $arrayNationwide = $arrayPupil;
            $orgSchoolYear = $this->em->getRepository('Application\Entity\OrgSchoolYear')->findOneBy(array(
                'organizationId' => $this->org_id,
                'schoolYearId' => $isset['ddbSchoolYear'],
                'isDelete' => 0
            ));
            if (! empty($orgSchoolYear)) {
                $orgSchoolYear = $orgSchoolYear->getId();
            } else $orgSchoolYear = null;
            $org = $this->em->getRepository('Application\Entity\Organization')->find($this->org_id);
            $nationCode = $this->em->getRepository('Application\Entity\City')->findOneBy(array('cityCode'=>'00'))->getId();
            $numberPass = $this->em->getRepository('Application\Entity\GoalPass')->getCountPupilPass($year, $this->org_id, $orgSchoolYear);
            $numberPupil = $this->em->getRepository('Application\Entity\GoalPass')->getCountPupilOfClass($year, $this->org_id, $orgSchoolYear);
            $translator = $this->getServiceLocator()->get('MVCTranslator');
            $cityPassRate = $this->em->getRepository('Application\Entity\GoalPass')->getCityPassRate($year, $isset['ddbSchoolYear'],$org->getOrganizationCode(), $org->getCity()->getId());
            $nationwidePassRate = $this->em->getRepository('Application\Entity\GoalPass')->getCityPassRate($year, $isset['ddbSchoolYear'],$org->getOrganizationCode(), $nationCode);
            if (! empty($numberPass)) {
                foreach ($numberPass as $value) {
                    $arrayPupil[$value['eikenLevelId']] = round(($value['numberPass'] / (int) $numberPupil[1]) * 100);
                }
            }
            if (! empty($cityPassRate)) {
                foreach ($cityPassRate as $value) {
                    $arrayCity[$value['eikenLevelId']] = round($value['ratePass']);
                }
            }
            if (! empty($nationwidePassRate)) {
                foreach ($nationwidePassRate as $value) {
                    $arrayNationwide[$value['eikenLevelId']] = round($value['ratePass']);
                }
            }
            $schoolyear = $this->em->getRepository('Application\Entity\ClassJ')->ListSchoolYearByYear($this->org_id, $year);
            $yearschool = array();
            if (isset($schoolyear)) {
                $yearschool[''] = '';
                $yearschool['0'] = '全';
                foreach ($schoolyear as $key => $value) {
                    $yearschool[$value['schoolYearId']] = $value['displayName'];
                }
                $form->get("ddbSchoolYear")->setValueOptions($yearschool);
                $form->get("ddbSchoolYear")->setAttribute('value', $isset['ddbSchoolYear']);
            }
            $form->get("ddbYear")->setAttribute('value', $isset['ddbYear']);
        }

        $viewModel->setVariables(array(
            'form' => $form,
            'pupilPassRate' => $arrayPupil,
            'cityPassRate' => $arrayCity,
            'nationwidePassRate' => $arrayNationwide,
            'org' => $org,
            'mess' => $mess,
            'year' => $year,
            'searchVisible' => $searchVisible
        ));
        return $viewModel;
    }

    public function getCurrentYear()
    {
        if (date("m") < 4) {
            return date("Y") - 1;
        } else {
            return date("Y");
        }
    }

    public function getSchoolYearAction()
    {
        $year = $this->params()->fromQuery('year');
        $data = $this->em->getRepository('Application\Entity\ClassJ')->ListSchoolYearByYear($this->org_id, $year);
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($data));
        return $response;
    }
}
