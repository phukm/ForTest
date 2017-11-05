<?php

namespace InvitationMnt\Controller;

use Application\Entity\SemiVenue;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Doctrine\ORM\EntityManager;
use InvitationMnt\Service\GenerateService;
use InvitationMnt\Service\ServiceInterface\GenerateServiceInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use InvitationMnt\Form\GenerateForm;
use InvitationMnt\Form\InvitationsettinglistForm;
use Application\Entity\InvitationLetter;
use InvitationMnt\InvitationConst;
use dantai\PublicSession;
use Zend\Json\Json;
/**
 * GenerateController
 * Date: 16/06/2015
 *
 * @author
 *
 * @method Generate : index, edit, show
 */
class GenerateController extends AbstractActionController
{
    protected $org_id;
    protected $orgNo;
    /**
     *
     * @var DantaiServiceInterface
     */
    protected $dantaiService;
    /**
     *
     * @var GenerateService
     */
    protected $generateService;
    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(DantaiServiceInterface $dantaiService, GenerateServiceInterface $generateService, EntityManager $entityManager)
    {
        $this->dantaiService = $dantaiService;
        $this->generateService = $generateService;
        $this->em = $entityManager;
        $user = $this->dantaiService->getCurrentUser();
        $this->org_id = $user['organizationId'];
        $this->orgNo = $user['organizationNo'];
    }

    public function indexAction()
    {

        $searchVisible = 0;
        $mess = false;
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $form = new GenerateForm();
        $page = $this->params()->fromRoute('page', 1);
        $limit = 20;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $request_kai = '';
        $kai = array();
        $currentEikenSchedule = $this->em->getRepository('Application\Entity\EikenSchedule')->getCurrentEikenSchedule();
        // router search
        $routeMatch = $this->getEvent()
                ->getRouteMatch()
                ->getParam('controller') . '_' . $this->getEvent()
                ->getRouteMatch()
                ->getParam('action');
        if ($this->getRequest()->isPost()) {
            $searchVisible = 1;
            $search = $this->params()->fromPost();
            $this->dantaiService->setSearchKeywordToSession($routeMatch, $search);
        }
        $searchArray = $this->dantaiService->getSearchKeywordFromSession($routeMatch);
        if (!empty($searchArray)) {
            $request_year = $searchArray['ddbYear'];
            $request_kai = $searchArray['ddbKai'];
            $request_schoolyear = $searchArray['ddbSchoolYear'];
            $request_class = $searchArray['ddbClass'];
            $inv_class = $this->em->getRepository('Application\Entity\EikenSchedule')->getInvClassList($limit, $offset, $request_year, $request_schoolyear, $request_class, $this->org_id);
            $form->get("ddbYear")->setAttribute('value', '' . $request_year);
            $list_kai = $this->em->getRepository('Application\Entity\EikenSchedule')->getKaiByYear($request_year);
            if (isset($list_kai)) {
                foreach ($list_kai as $key => $value) {
                    $kai[$value['id']] = $value['kai'];
                }
                $form->get("ddbKai")
                    ->setAttribute('value', '' . $request_kai)
                    ->setValueOptions($kai);
            }
            $schoolyear = $this->em->getRepository('Application\Entity\ClassJ')->ListSchoolYearByYear($this->org_id, $request_year);
            $yearschool = array();
            if (isset($schoolyear)) {
                $yearschool[''] = '';
                foreach ($schoolyear as $key => $value) {
                    $yearschool[$value['id']] = $value['displayName'];
                }
            }
            $form->get("ddbSchoolYear")->setValueOptions($yearschool);
            $form->get("ddbSchoolYear")->setAttribute('value', '' . $request_schoolyear);
            if (!empty($request_schoolyear)) {
                $list_class = $this->em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYearAndYear($request_year, $request_schoolyear, $this->org_id);
                if (isset($list_class)) {
                    $class[''] = '';
                    foreach ($list_class as $key => $value) {
                        $class[$value['id']] = $value['className'];
                    }
                    $form->get("ddbClass")
                        ->setAttribute('value', '' . $request_class)
                        ->setValueOptions($class);
                }
            } else
                $form->get('ddbClass')->setValueOptions(array(
                    '' => ''
                ));
            if (count($inv_class) == 0) {
                $mess = $translator->translate('MSG013');
            }
        } else {
            $isset = $this->params()->fromPost();
            if (!empty($isset)) {
                $request_year = $this->params()->fromPost('ddbYear');
                $request_kai = $this->params()->fromPost('ddbKai');
                $request_schoolyear = $this->params()->fromPost('ddbSchoolYear');
                $request_class = $this->params()->fromPost('ddbClass');
                $inv_class = $this->em->getRepository('Application\Entity\EikenSchedule')->getInvClassList($limit, $offset, $request_year, $request_schoolyear, $request_class, $this->org_id);
                $form->get("ddbYear")->setAttribute('value', '' . $request_year);
                $list_kai = $this->em->getRepository('Application\Entity\EikenSchedule')->getKaiByYear($request_year);
                if (isset($list_kai)) {
                    foreach ($list_kai as $key => $value) {
                        $kai[$value['id']] = $value['kai'];
                    }
                    $form->get("ddbKai")
                        ->setAttribute('value', '' . $request_kai)
                        ->setValueOptions($kai);
                }
                $schoolyear = $this->em->getRepository('Application\Entity\ClassJ')->ListSchoolYearByYear($this->org_id, $request_year);
                $yearschool = array();
                if (isset($schoolyear)) {
                    $yearschool[''] = '';
                    foreach ($schoolyear as $key => $value) {
                        $yearschool[$value['id']] = $value['displayName'];
                    }
                }
                $form->get("ddbSchoolYear")->setValueOptions($yearschool);
                $form->get("ddbSchoolYear")->setAttribute('value', '' . $request_schoolyear);
                if (!empty($request_schoolyear)) {
                    $list_class = $this->em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYearAndYear($request_year, $request_schoolyear, $this->org_id);
                    if (isset($list_class)) {
                        $class[''] = '';
                        foreach ($list_class as $key => $value) {
                            $class[$value['id']] = $value['className'];
                        }
                        $form->get("ddbClass")
                            ->setAttribute('value', '' . $request_class)
                            ->setValueOptions($class);
                    }
                } else
                    $form->get('ddbClass')->setValueOptions(array(
                        '' => ''
                    ));
                if (count($inv_class) == 0) {
                    $mess = $translator->translate('MSG013');
                }
            } else {
                // load kai
                $currentYear = !empty($currentEikenSchedule['year']) ? $currentEikenSchedule['year'] : $this->dantaiService->getCurrentYear();
                $current_kai = $this->em->getRepository('Application\Entity\EikenSchedule')->getKaiByYear($currentYear);
                if (isset($current_kai)) {
                    foreach ($current_kai as $key => $value) {
                        $kai[$value['id']] = $value['kai'];
                    }
                    if ($currentEikenSchedule != null) {
                        $request_kai = $currentEikenSchedule['id'];
                    } else {
                        $request_kai = (array_shift(array_keys($kai)))?(array_shift(array_keys($kai))):1;
                    }
                }
                $form->get('ddbKai')->setValueOptions($kai);
                $form->get('ddbKai')->setAttribute('value', $request_kai);
                // load class
                $form->get('ddbClass')->setValueOptions(array(
                    '' => ''
                ));
                $inv_class = $this->em->getRepository('Application\Entity\EikenSchedule')->getInvClassList($limit, $offset, date("Y"), $request_schoolyear = false, $request_class = false, $this->org_id);
                if (count($inv_class) == 0) {
                    $mess = $translator->translate('MSG013');
                }
                // load schoolyear list
                $schoolyear = $this->em->getRepository('Application\Entity\ClassJ')->ListSchoolYearByYear($this->org_id, date("Y"));
                $yearschool = array();
                if (isset($schoolyear)) {
                    $yearschool[''] = '';
                    foreach ($schoolyear as $key => $value) {
                        $yearschool[$value['id']] = $value['displayName'];
                    }
                }
                $form->get("ddbSchoolYear")->setValueOptions($yearschool);
            }
        }
        if (!empty($this->flashMessenger()->getMessages())) {
            $messs = $this->flashMessenger()->getMessages();
            $mess = $messs[0];
        }
        $invitationParameters = array(
            'noInvitationLetters'    => isset($mess['noInvitationLetters']) ? $mess['noInvitationLetters'] : '' ,
            'currentEikenScheduleId' => $currentEikenSchedule['id']
        );

        return new ViewModel(array(
            'mess'                 => $mess,
            'invitationParameters' => $invitationParameters,
            'form'                 => $form,
            'inv_class'            => $inv_class,
            'request_kai'          => $request_kai,
            'currentEikenSchedule' => $currentEikenSchedule,
            'kai'                  => $kai,
            'page'                 => $page,
            'searchVisible'       => $searchVisible,
            'Translate'            => $this->getTranslation()
        ));
    }

    public function showAction()
    {
        $form = new GenerateForm();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $mess = false;
        $messNotFound = false;
        $id = $this->params()->fromRoute('id', 0);
        $year = $this->params()->fromRoute('year', 0);
        $kai = $this->params()->fromRoute('kai', 0);
        $limit = 20;
        $page = $this->params()->fromRoute('page', 1);
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        $searchVisible = 1;
        $routeMatch = $this->getEvent()
                ->getRouteMatch()
                ->getParam('controller') . '_' . $this->getEvent()
                ->getRouteMatch()
                ->getParam('action');
        if ($this->getRequest()->isPost()) {
            $search = $this->params()->fromPost();
            $this->dantaiService->setSearchKeywordToSession($routeMatch, $search);
        }
        $searchArray = $this->dantaiService->getSearchKeywordFromSession($routeMatch);
        if (!empty($searchArray)) {
            $request_name = $searchArray['txtNameKanji'];
            $paginator = $this->em->getRepository('Application\Entity\Pupil')->getPagedPupilList($this->org_id, $year, false, $id, $request_name);
            if (count($paginator) == 0) {
                $messNotFound = $translator->translate('MSG013');
            }
            $form->get('txtNameKanji')->setAttribute('value', $request_name);
        } else {
            $isset = $this->params()->fromPost();
            if (!empty($isset)) {
                $request_name = $this->params()->fromPost('txtNameKanji');
                $paginator = $this->em->getRepository('Application\Entity\Pupil')->getPagedPupilList($this->org_id, $year, false, $id, $request_name);
                if (count($paginator) == 0) {
                    $messNotFound = $translator->translate('MSG013');
                }
                $form->get('txtNameKanji')->setAttribute('value', $request_name);
            } else {
                $paginator = $this->em->getRepository('Application\Entity\Pupil')->getPagedPupilList($this->org_id, $year, false, $id, $name = false);
                if (count($paginator) == 0) {
                    $messNotFound = $translator->translate('MSG013');
                }
            }
        }
        if (!empty($this->flashMessenger()->getMessages())) {
            $messs = $this->flashMessenger()->getMessages();
            $mess = $messs[0];
        }
        return new ViewModel(array(
            'mess'          => $mess,
            'messNotFound'  => $messNotFound,
            'form'          => $form,
            'pupil'         => $paginator->getItems($offset, $limit, false),
            'paginator'     => $paginator,
            'numPerPage'    => $limit,
            'id'            => $id,
            'year'          => $year,
            'kai'           => $kai,
            'page'          => $page,
            'searchVisible' => $searchVisible
        ));
    }

    public function editAction()
    {
        $viewModel = new ViewModel();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $form = new GenerateForm();
        $id = $this->params()->fromRoute('id', 0);
        $year = $this->params()->fromRoute('year', 0);
        $kai = $this->params()->fromRoute('kai', 0);
        $val_temp1 = '';
        $val_temp2 = '';
        $pupil = $this->em->getRepository('Application\Entity\Pupil')->find($id);
        $eikenSchedule = $this->em->getRepository('Application\Entity\EikenSchedule')->findOneBy(array(
            'year' => $year,
            'kai'  => $kai
        ));
        if ($eikenSchedule) {
            $checkProcessLog = $this->em->getRepository('Application\Entity\ProcessLog')->findOneBy(array('orgId' => $this->org_id, 'scheduleId' => $eikenSchedule->getId()));
            if (!empty($checkProcessLog)) {
                $this->flashMessenger()->addMessage($translator->translate('MSG073'));

                return $this->redirect()->toRoute(null, array(
                    'module'     => 'invitation-mnt',
                    'controller' => 'generate',
                    'action'     => 'show',
                    'id'         => $pupil->getClassId(),
                    'year'       => $year,
                    'kai'        => $kai
                ));
            }
        }
        // $deadlineFrom = $time_deadline->getDeadlineFrom()->format('Y/m/d');
        $deadlineTo = $eikenSchedule->getDeadlineTo()->format('Y/m/d');
        if (date('Y/m/d') >= $deadlineTo) {
            $mess = sprintf($translator->translate('MSG029'), $year, $kai);
            $this->flashMessenger()->addMessage($mess);

            return $this->redirect()->toRoute(null, array(
                'module'     => 'invitation-mnt',
                'controller' => 'generate',
                'action'     => 'show',
                'id'         => $pupil->getClassId(),
                'year'       => $year,
                'kai'        => $kai
            ));
        }
        $inv_setting = $this->em->getRepository('Application\Entity\InvitationSetting')->findOneBy(array(
            'eikenScheduleId' => $eikenSchedule->getId(),
            'organizationId'  => $this->org_id,
            'isDelete'        => $isDelete = 0
        ));
        /**
         * 
         * @var \Application\Entity\InvitationLetter
         */
        $inv_letter = $this->em->getRepository('Application\Entity\InvitationLetter')->findOneBy(array(
            'pupilId'         => $id,
            'eikenScheduleId' => $eikenSchedule->getId()
        ));
        if ($inv_letter) {
            $form->get('message1')->setValue($inv_letter->getMessages1());
            $form->get('message2')->setValue($inv_letter->getMessages2());
            $val_temp1 = $inv_letter->getTemplateInvitationMsgId1();
            $val_temp2 = $inv_letter->getTemplateInvitationMsgId2();
            
            $this->dantaiService->startCrossEditing($inv_letter);
            
        } elseif ($inv_setting) {
            $form->get('message1')->setValue($inv_setting->getMessage1());
            $form->get('message2')->setValue($inv_setting->getMessage2());
            $val_temp1 = $inv_setting->getTemplateInvitationMsgId1();
            $val_temp2 = $inv_setting->getTemplateInvitationMsgId2();
        }
        $list_temp1 = $this->em->getRepository('Application\Entity\TemplateInvitationMsg')->messageType($type = 0);
        $list_temp2 = $this->em->getRepository('Application\Entity\TemplateInvitationMsg')->messageType($type = 1);
        
        $viewModel->setVariables(array(
//             'jsMessages' => Json::encode($jsMessages),
            'form'       => $form,
            'list_temp1' => $list_temp1,
            'list_temp2' => $list_temp2,
            'val_temp1'  => $val_temp1,
            'val_temp2'  => $val_temp2,
            'id'         => $id,
            'year'       => $year,
            'kai'        => $kai,
            'classId'    => $pupil->getClassId()
        ));
        $this->getIndexBreadCumbs($pupil->getClassId(),$year,$kai);
        return $viewModel;
    }

    public function updateAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $year = $this->params()->fromRoute('year', 0);
        $kai = $this->params()->fromRoute('kai', 0);
        $request = $this->getRequest();
        $repository = $this->em->getRepository('Application\Entity\InvitationSetting');
        $pupil = $this->em->getRepository('Application\Entity\Pupil')->find($id);
        if ($request->isPost()) {
            $data = $request->getPost();
            $eikenSchedule = $this->em->getRepository('Application\Entity\EikenSchedule')->findOneBy(array(
                'year' => $year,
                'kai'  => $kai
            ));
            $inv_setting = $this->em->getRepository('Application\Entity\InvitationSetting')->findOneBy(array(
                'organizationId'  => $this->org_id,
                'eikenScheduleId' => $eikenSchedule->getId()
            )); // 'invitationSettingId' =>
            $inv_letter = $this->em->getRepository('Application\Entity\InvitationLetter')->findOneBy(array(
                'pupilId'         => $id,
                'eikenScheduleId' => $eikenSchedule->getId()
            ));
            if ($inv_letter) {

                if ($data['TemplateMsg1'] != '') {
                    $inv_letter->setTemplateInvitationMsgId1($data['TemplateMsg1']);
                } else {
                    $inv_letter->setTemplateInvitationMsgId1(null);
                }
                if ($data['TemplateMsg2'] != '') {
                    $inv_letter->setTemplateInvitationMsgId2($data['TemplateMsg2']);
                } else {
                    $inv_letter->setTemplateInvitationMsgId2(null);
                }
                $inv_letter->setMessages1($data['message1']);
                $inv_letter->setMessages2($data['message2']);
                $inv_letter->setUpdateByHand('1');
                $this->em->flush();
            } else {
                $inv_letter = new InvitationLetter();
                $eikenSchedule = $this->em->getReference('Application\Entity\EikenSchedule', array(
                    'id' => $eikenSchedule->getId()
                ));
                $inv_letter->setPupil($pupil);
                if ($data['TemplateMsg1'] != '') {
                    $temp1 = $this->em->getReference('Application\Entity\TemplateInvitationMsg', array(
                        'id' => (int)$data['TemplateMsg1']
                    ));
                    $inv_letter->setTemplate1($temp1);
                }
                if ($data['TemplateMsg2'] != '') {
                    $temp2 = $this->em->getReference('Application\Entity\TemplateInvitationMsg', array(
                        'id' => (int)$data['TemplateMsg2']
                    ));
                    $inv_letter->setTemplate2($temp2);
                }
                $inv_letter->setMessages1($data['message1']);
                $inv_letter->setMessages2($data['message2']);
                $inv_letter->setEikenSchedule($eikenSchedule);
                $inv_letter->setUpdateByHand('1');
                $this->em->persist($inv_letter);
                $this->em->flush();
                $this->em->clear();
            }
        }

        return $this->redirect()->toRoute(null, array(
            'module'     => 'invitation-mnt',
            'controller' => 'generate',
            'action'     => 'show',
            'id'         => $pupil->getClassId(),
            'year'       => $year,
            'kai'        => $kai
        ));
    }

    // author : PhucVV3
    // function : invitationLetterAction
    // param :
    // Description : process action invatationLetter generate
    public function invitationLetterAction()
    {
        $orgId = $this->org_id;
        $year = (int) $this->params()->fromPost('year', 0);
        $eikenScheduleId = (int) $this->params()->fromPost('kai', 0);
        // TODO Validation rule
        // If (Year) or/and (Kai) in Search is blank, system will show error message MSG 35.
        // If Invitation Setting for selected Year and Kai does not exists, system displays error message MSG 32.
        // If Current Date < Application Start Date or Current Date >= Application End Date of selected Year and Kai, system displays error message MSG 29
        $jsonMessage = \Dantai\Utility\JsonModelHelper::getInstance();
        $em = $this->getEntityManager();
        /**
         *
         * @var $eikenSchedule \Application\Entity\EikenSchedule
         */
        $eikenSchedule = $em->getRepository('Application\Entity\EikenSchedule')->findOneBy(array(
            'id'   => $eikenScheduleId,
            'year' => $year
        ));

        if (!$eikenSchedule || !$orgId) {
            $jsonMessage->setFail();
            $jsonMessage->addMessage($this->translate('MSG035'));

            return $this->responseJsonMessage($jsonMessage);
        }

        // Get eikenSchedule.
        $now = date('Y-m-d');
        $deadlineTo = $eikenSchedule->getDeadlineTo()->format('Y-m-d');
        $deadlineFrom = $eikenSchedule->getDeadlineFrom()->format('Y-m-d');
        $isOutOfKai = $now < $deadlineFrom || $now > $deadlineTo;
        if ($isOutOfKai) {
            $jsonMessage->setFail();
            $jsonMessage->addMessage(sprintf($this->translate('MSG029'), $year, $eikenSchedule->getKai()));

            return $this->responseJsonMessage($jsonMessage);
        }
        // Check exist invitation setting.
        $invitationQb = $em->createQueryBuilder();
        $classExpr = $invitationQb->expr();
        $andExpr = $classExpr->andX($classExpr->eq('i.eikenScheduleId', $eikenScheduleId), $classExpr->eq('i.organizationId', $orgId), $classExpr->neq('i.isDelete', 1));
        $invitationQb->select('e.deadlineFrom', 'e.deadlineTo')
            ->from('\Application\Entity\InvitationSetting', 'i')
            ->leftJoin('\Application\Entity\EikenSchedule', 'e', \Doctrine\ORM\Query\Expr\Join::WITH, $classExpr->eq('i.eikenScheduleId', 'e.id'))
            ->where($andExpr);
        $query = $invitationQb->getQuery();
        $invitationSetting = $query->getArrayResult();
        if (!count($invitationSetting)) {
            $jsonMessage->setFail();
            $jsonMessage->addMessage(sprintf($this->translate('MSG032'), $year, $eikenSchedule->getKai()));

            return $this->responseJsonMessage($jsonMessage);
        }

        // Check processLog.
        if (!empty($eikenScheduleId)) {
            $checkProcessLog = $this->em->getRepository('Application\Entity\ProcessLog')->findOneBy(array('orgId' => $this->org_id, 'scheduleId' => $eikenScheduleId));
            if (!empty($checkProcessLog)) {
                $jsonMessage->setFail();
                $jsonMessage->addMessage($this->translate('MSG073'));

                return $this->responseJsonMessage($jsonMessage);
            }
        }
        /* @var $invitationSetting \Application\Entity\InvitationSetting */
        $invitationSetting = $em->getRepository('\Application\Entity\InvitationSetting')->findOneBy(array(
            'organizationId'  => $orgId,
            'eikenScheduleId' => $eikenScheduleId
        ));

        $semiVenue = $this->dantaiService->getSemiMainVenueOrigin($orgId, $eikenScheduleId);
        if(empty($invitationSetting->getBeneficiary()) && $semiVenue == 1){
            $jsonMessage->setFail();
            $jsonMessage->addMessage($this->translate('update_setting_car_before_gen_letter'));

            return $this->responseJsonMessage($jsonMessage);
        }

        // check exist pupil
        $dqCheckExitPupil = $em->createQueryBuilder();
        $dqCheckExitPupil->select('COUNT(Pupil.id)')
            ->from('\Application\Entity\Pupil', 'Pupil')
            ->innerJoin('\Application\Entity\ClassJ', 'ClassJ'
                , \Doctrine\ORM\Query\Expr\Join::WITH
                , 'ClassJ.id = Pupil.classId AND ClassJ.isDelete = 0')
            ->where('Pupil.isDelete = 0 AND Pupil.organizationId = :orgId AND ClassJ.year = :year')
            ->setParameter('orgId', $orgId)
            ->setParameter('year', $year);
        $countCheckExistPupil = $dqCheckExitPupil->getQuery()->getSingleScalarResult();
        if ($countCheckExistPupil <= 0) {
            $jsonMessage->setFail();
            $jsonMessage->addMessage($this->translate('MSG092_Empty_Pupil_For_Gen_Invitation'));

            return $this->responseJsonMessage($jsonMessage);
        }
        if ($invitationSetting->getInvitationType() == 1 || $invitationSetting->getInvitationType() == 2) {
            $dqCheckRecommendLevel = $em->createQueryBuilder();
            $dqCheckRecommendLevel->select('COUNT(Pupil.id)')
                ->from('\Application\Entity\Pupil', 'Pupil')
                ->innerJoin('\Application\Entity\ClassJ', 'ClassJ'
                    , \Doctrine\ORM\Query\Expr\Join::WITH
                    , 'ClassJ.id = Pupil.classId AND ClassJ.isDelete = 0')
                ->leftJoin('\Application\Entity\RecommendLevel', 'RecommendLevel', \Doctrine\ORM\Query\Expr\Join::WITH, 'Pupil.id = RecommendLevel.pupilId ' . 'AND RecommendLevel.isDelete = 0 ' . 'AND RecommendLevel.eikenScheduleId = :eikenScheduleId ')
                ->where('RecommendLevel.eikenLevelId IS NULL '
                    . 'AND Pupil.isDelete = 0 '
                    . 'AND Pupil.organizationId = :orgId '
                    . 'AND ClassJ.year = :year ')
                ->setParameter('orgId', $orgId)
                ->setParameter('year', $year)
                ->setParameter('eikenScheduleId', $eikenScheduleId);
            $countCheckRecomendLevel = $dqCheckRecommendLevel->getQuery()->getSingleScalarResult();
            if ($countCheckRecomendLevel > 0) {
                $jsonMessage->setFail();
                $jsonMessage->addMessage($this->translate('Msg_Please_Recommend_Level_For_Pupil'));

                return $this->responseJsonMessage($jsonMessage);
            }
        }
        if ($jsonMessage->isSuccess()) {
            // $this->generateInvatationLetter($pupilIds, $eikenScheduleId, $orgId);
            // Send SQS when template is not eiken, shool version
            $combiniBatch = $this->generateService->splitOrgCombini($orgId, $year, $eikenScheduleId);
            $userIdentity = \Dantai\PrivateSession::getData('userIdentity');
            if ($combiniBatch['total']) {
                // Add one process log record
                $processExists = $this->generateService->addProcessLogRecord($orgId, $eikenScheduleId, $combiniBatch['total'], array(
                    'email'  => $userIdentity['emailAddress'],
                    // FIXME Only for Japanese name
                    'name'   => $userIdentity['firstName'] . $userIdentity['lastName'],
                    'active' => $combiniBatch['sqsType'] == 'combini' ? 0 : $combiniBatch['total']
                ));
                // Do not book SQS while processing generate letter
                if ($processExists != 1) {
                    $listEikenLevel = json_decode($invitationSetting->getListEikenLevel(), true);
                    $priceLevels = $this->dantaiService->getListPriceOfOrganization($this->orgNo, $listEikenLevel);
                    $messages = array();
                    foreach ($combiniBatch['sqsData'] as $classId => $startIndex) {
                        $messages[] = array(
                            'Id'          => $classId,
                            'MessageBody' => \Zend\Json\Encoder::encode(array(
                                'classId'    => $classId,
                                'orgId'      => $orgId,
                                'scheduleId' => $eikenScheduleId,
                                'startIndex' => $startIndex,
                                'priceLevels' => $priceLevels,
                            ))
                        );
                    }
                    if ($combiniBatch['sqsType'] == 'combini') {
                        $sqsMessages = array(
                            'QueueUrl' => \Dantai\Aws\AwsSqsClient::QUEUE_GEN_COMBIBI,
                            'Entries'  => array(
                                reset($messages)
                            )
                        );
                        \Dantai\Aws\AwsSqsClient::getInstance()->sendMessageBatch($sqsMessages);
                    }
                    $countMessage = count($messages);
                    $countMesSent = 0;
                    while ($countMesSent < $countMessage) {
                        $msgSend = array_slice($messages, $countMesSent, 10);
                        $sqsMessages = array(
                            'QueueUrl' => \Dantai\Aws\AwsSqsClient::QUEUE_GEN_INVITATION,
                            'Entries'  => $msgSend
                        );
                        \Dantai\Aws\AwsSqsClient::getInstance()->sendMessageBatch($sqsMessages);
                        $countMesSent += 10;
                    }
                }
            }
            $jsonMessage->addMessage(sprintf($this->translate('MSG061'), $userIdentity['emailAddress']));
        }
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent($jsonMessage->jsonSerialize());

        return $response;
    }

    public function responseJsonMessage($jsonMessage)
    {
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent($jsonMessage->jsonSerialize());

        return $response;
    }

    public function genAuthenKeyForPupil($listPupilId, $eikenScheduleId, $organizationId)
    {
        $countPupil = count($listPupilId);
        $listExistAuthenKey = $this->getListExistAuthenKey($organizationId);
        $authenKeyExists = array();
        $authenKeys = array();
        foreach ($listExistAuthenKey as $authKey) {
            /* @var $authKey \Application\Entity\AuthenticationKey */
            $authenKeyExists[] = (int)$authKey->getAuthenKey();
            $authenKeys[$authKey->getPupilId()] = $authKey;
        }
        $listNewUniqueKey = $this->makeListUniqueId($countPupil, $authenKeyExists);
        $countBatch = 0;
        foreach ($listPupilId as $pupilId) {
            if (array_key_exists($pupilId, $authenKeys)) {
                continue;
            }
            $newAuthenKey = array_shift($listNewUniqueKey);
            /* @var $pupil \Application\Entity\Pupil */
            $pupil = $this->getEntityManager()
                ->getRepository('\Application\Entity\Pupil')
                ->find($pupilId);
            $authenticationKey = new \Application\Entity\AuthenticationKey();
            $authenticationKey->setAuthenKey(sprintf("%'.08d", $newAuthenKey));
            $authenticationKey->setOrganizationNo($pupil->getOrganization()
                ->getOrganizationNo());
            $authenticationKey->setPupil($pupil);
            $authenticationKey->setEikenSchedule($this->getEntityManager()
                ->getReference('\Application\Entity\EikenSchedule', $eikenScheduleId));
            $this->getEntityManager()->persist($authenticationKey);
            $countBatch++;
            if ($countBatch == 20) {
                $countBatch = 0;
                $this->getEntityManager()->flush();
            }
        }
        $this->getEntityManager()->flush();
    }

    public function getListExistAuthenKey($organizationId)
    {
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq->select('AuthenticationKey')
            ->from('\Application\Entity\AuthenticationKey', 'AuthenticationKey')
            ->join('\Application\Entity\Pupil', 'Pupil', \Doctrine\ORM\Query\Expr\Join::WITH, 'Pupil.id = AuthenticationKey.pupilId')
            ->where('Pupil.isDelete = 0 AND AuthenticationKey.isDelete = 0 ' . 'AND Pupil.organizationId = :orgId ')
            ->setParameter('orgId', $organizationId);

        return $dq->getQuery()->getResult();
    }

    public function makeListUniqueId($numOfList, $existKey = array())
    {
        $res = array();
        for ($i = 1; $i <= $numOfList; $i++) {
            $number = rand(1, 99999999);
            while (in_array($number, $existKey) || in_array($number, $res)) {
                $number = rand(1, 99999999);
            }
            $res[] = $number;
        }

        return $res;
    }

    public function getRecomendedLevelMessage($pupilId, $eikenScheduleId)
    {
        $recommendLevel = $this->em->getRepository('Application\Entity\RecommendLevel')->findOneBy(array(
            'pupilId'         => $pupilId,
            'eikenScheduleId' => $eikenScheduleId
        ));
        if ($recommendLevel != null) {
            $eikenRepository = $this->em->getRepository('Application\Entity\EikenTestResult');
            $ibaRepository = $this->em->getRepository('Application\Entity\IBATestResult');
            $eikenLevelId = $recommendLevel->getEikenLevelId();
            $eikenResult123 = $eikenRepository->getDataResultLastestByPupilIdAndType($pupilId, 0);
            $eikenResult45 = $eikenRepository->getDataResultLastestByPupilIdAndType($pupilId, 1);
            $ibaResult = $ibaRepository->getDataResultLastestByPupilId($pupilId);
            if ($eikenResult123 == null && $eikenResult45 == null && $ibaResult == null) {
                // Eiken History and IBA of corresponding pupil is blank
                $condition = 'condition1';
            } else {
                $eikenDate123 = ($eikenResult123['secondCertificationDate'] != null) ? $eikenResult123['secondCertificationDate']->format('Y-m-d') : '';
                $eikenDate45 = ($eikenResult45['certificationDate'] != null) ? $eikenResult45['certificationDate']->format('Y-m-d') : '';
                $ibaDate = ($ibaResult["examDate"] != null) ? $ibaResult["examDate"]->format('Y-m-d') : '';
                // get ekienLevel by date lastest of eiken or IBA
                $arrDate = array(
                    $eikenDate123,
                    $eikenDate45,
                    $ibaDate
                );
                if (max($arrDate) == $eikenDate123) {
                    $level = $eikenResult123["eikenLevelId"];
                } else
                    if (max($arrDate) == $eikenDate45) {
                        $level = $eikenResult45["eikenLevelId"];
                    } else {
                        $level = $ibaResult["eikenLevelId"];
                    }
                if ($eikenLevelId > $level) {
                    // Recommended Level < Level thi latest của Eiken/IBA
                    $condition = 'condition1';
                } elseif ($eikenLevelId < $level) {
                    // Recommended Level > Level dự thi của latest IBA/Eiken
                    $condition = 'condition2';
                } else {
                    // Recommended Level = Level thi latest của Eiken/IBA
                    $condition = 'condition3';
                }
            }
            $message = $this->em->getRepository('Application\Entity\ConditionMessages')->findOneBy(array(
                'eikenLevelId' => $eikenLevelId,
                'type'         => 1,
                'condition'    => $condition
            ));
            if ($message != null) {
                $response = array(
                    'id'       => $message->getId(),
                    'messages' => $message->getMessages()
                );
            } else {
                $response = 'exits';
            }

            return $response;
        } else {
            return 'exits';
        }
    }

    public function getClassAction()
    {
        $schoolyearId = $this->params()->fromQuery('schoolyear');
        $year = $this->params()->fromQuery('year');
        $data = $this->em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYearAndYear($year, $schoolyearId, $this->org_id);
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($data));

        return $response;
    }

    public function getKaiAction()
    {
        $year = $this->params()->fromQuery('year');
        $data = $this->em->getRepository('Application\Entity\EikenSchedule')->getKaiByYear($year);
        if (count($data) == 0) {
            $data[''] = '';
        }
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($data));

        return $response;
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

    public function downloadClassAction()
    {
        $year = (int) $this->params('year');
        $kai = (int) $this->params('kai');
        $classId = (int) $this->params('id');
        if ($classId == null || $year == null || $kai == null) {
            return $this->redirect()->toUrl('/error/index');
        }
        /* @var $eikenSchedule \Application\Entity\EikenSchedule */
        $eikenSchedule = $this->em->getRepository('Application\Entity\EikenSchedule')->findOneBy(array(
            "year" => $year,
            "kai"  => $kai
        ));
        /* @var $classj \Application\Entity\ClassJ */
        $classj = $this->em->getRepository('Application\Entity\ClassJ')->findOneBy(array(
            "id"             => $classId,
            "organizationId" => $this->org_id,
            "isDelete"       => 0
        ));
        if ($eikenSchedule != null && $classj != null) {
            $checkProcessLog = $this->em->getRepository('Application\Entity\ProcessLog')->findOneBy(array('orgId' => $this->org_id, 'scheduleId' => $eikenSchedule->getId()));
            if (!empty($checkProcessLog)) {
                $msgError = array('noInvitationLetters' => $this->translate('MSG073'));
                $this->flashMessenger()->addMessage($msgError);

                return $this->redirect()->toRoute(null, array(
                    'module'     => 'invitation-mnt',
                    'controller' => 'generate',
                    'action'     => 'index'
                ));
            } else {
                $schoolYear = $classj->getOrgSchoolYear()->getDisplayName();
                $keyObject = $eikenSchedule->getId() . '/' . $classId . '/all.pdf';
                $contentType = 'application/pdf';
                $filename = '受験案内状_' . $year . '_' . $kai . '_' . $schoolYear . '_' . $classj->getClassName() . '.pdf';
                $pupils = $this->em->getRepository('Application\Entity\Pupil')->findOneBy(array(
                    "classId"  => $classId,
                    "isDelete" => 0
                ));
                $isDelete = $pupils === null ? 1 : 0;
                $this->downloadPdfFromS3($keyObject, $contentType, $filename, $isDelete);
            }
        } else {
            //Do not exits this eiken schedule or this class
            return $this->redirect()->toUrl('/error/index');
        }
    }

    public function downloadPupilAction()
    {
        $year = (int) $this->params('year');
        $kai = (int) $this->params('kai');
        $pupilId = (int) $this->params('id');
        if ($pupilId == null || $year == null || $kai == null) {
            $errors = "Empty PupilId Or Year Or Kai";
            $response = $this->getResponse();
            $response->setContent($errors);

            return $response;
        }
        /* @var $eikenSchedule \Application\Entity\EikenSchedule */
        $eikenSchedule = $this->em->getRepository('Application\Entity\EikenSchedule')->findOneBy(array(
            "year" => $year,
            "kai"  => $kai
        ));
        /* @var $pupil \Application\Entity\Pupil */
        $pupil = $this->em->getRepository('Application\Entity\Pupil')->findOneBy(array(
            "id"             => $pupilId,
            "organizationId" => $this->org_id,
            "isDelete"       => 0
        ));
        if (!empty($eikenSchedule)) {
            $checkProcessLog = $this->em->getRepository('Application\Entity\ProcessLog')->findOneBy(array('orgId' => $this->org_id, 'scheduleId' => $eikenSchedule->getId()));
            if (!empty($checkProcessLog)) {
                $translator = $this->getServiceLocator()->get('MVCTranslator');
                $this->flashMessenger()->addMessage($translator->translate('MSG073'));

                return $this->redirect()->toRoute(null, array(
                    'module'     => 'invitation-mnt',
                    'controller' => 'generate',
                    'action'     => 'show',
                    'id'         => $pupil->getClassId(),
                    'year'       => $year,
                    'kai'        => $kai
                ));
            }
        }
        if ($eikenSchedule != null && $pupil != null) {
            $className = $pupil->getClass() != null ? $pupil->getClass()->getClassName() : "";
            $pupilName = $pupil->getFirstNameKanji() . $pupil->getLastNameKanji();
            $schoolYear = $pupil->getOrgSchoolYear()->getDisplayName();
            // $keyObject = '1/255/383.pdf';
            $keyObject = $eikenSchedule->getId() . '/' . $pupil->getClassId() . '/' . $pupilId . '.pdf';
            $contentType = 'application/pdf';
            $filename = '受験案内状_' . $year . '_' . $kai . '_' . $schoolYear . '_' . $className . '_' . $pupilName . '.pdf';
            $this->downloadPdfFromS3($keyObject, $contentType, $filename);
        } else {
            $error = 'Do not exits this eiken schedule or this class';
            $response = $this->getResponse();
            $response->setContent($error);

            return $response;
        }
    }

    public function downloadPdfFromS3($keyObject, $contentType, $filename, $isDelete = 0)
    {
        if ($this->getRequest()->getHeader('Referer') != false) {
            $redirectUrl = $this->getRequest()
                ->getHeader('Referer')
                ->getUri();
        } else {
            $redirectUrl = $this->url()->fromRoute('invitation-mnt/default', array(
                'controller' => 'generate',
                'action'     => 'index'
            ));
        }
        $bucket = 'dantai' . getenv('APP_ENV');
        if ($isDelete == 1) {
            \Dantai\Aws\AwsS3Client::getInstance()->deleteObject($bucket, $keyObject);
            $result["status"] = 404;
        } else {
            $result = \Dantai\Aws\AwsS3Client::getInstance()->readObject($bucket, $keyObject);
        }
        if ($result["status"] == 1) {
            $resultObj = $result["content"];
            $filename = \Dantai\Utility\CharsetConverter::utf8ToShiftJis($filename);
            header('Content-type: ' . $contentType . ';charset=utf-8');
            header('Content-Length: ' . strlen($resultObj['Body']));
            header('Content-Disposition: attachment; filename="' . $filename.'"');
            header('Cache-Control: max-age=0');
            echo $resultObj["Body"];
        } else if ($result["status"] == 404) {
            $msgError = array(
                'noInvitationLetters' => $this->translate('Msg_No_Invitation_Letter_To_Download')
            );
            $this->flashMessenger()->addMessage($msgError);
            return $this->redirect()->toUrl($redirectUrl);
        } else if ($result["status"] == 0) {
            $msgError = array(
                'noInvitationLetters' => $this->translate('Msg_No_Invitation_Letter_To_Download')
            );
            $this->flashMessenger()->addMessage($msgError);
            return $this->redirect()->toUrl($redirectUrl);
        }
    }
    
    protected function getIndexBreadCumbs ($classId,$year,$kai)
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('id', 'inv-generate');
        $page->setParams(array(
            'id' => $classId,
            'year' => $year,
            'kai' => $kai
        ));
    }
    
    protected function isFirstGeneratedAction ()
    {
        $eikenScheduleId = (int) $this->params()->fromPost('eikenScheduleId', 0);
        $result = array( 'success' => 0,
                         'id'      => 0
                    );
        if(empty($eikenScheduleId)){
            return $this->getResponse()->setContent(Json::encode($result));
        }
        /* @var $inv \Application\Entity\InvitationSetting */
        $inv = $this->em->getRepository('Application\Entity\InvitationSetting')->findOneBy(array(
            "organizationId" => $this->org_id,
            "eikenScheduleId" => $eikenScheduleId,
            "isDelete"       => 0
        ));
        if(!empty($inv) && $inv->getStatus() != 1){
            $result['success'] = 1;
            $result['id'] = $inv->getId();
        }
        /* @var $inv \Application\Entity\ProcessLog */
        $process = $this->em->getRepository('Application\Entity\ProcessLog')->findOneBy(array(
            "orgId" => $this->org_id,
            "scheduleId" => $eikenScheduleId
        ));
        $currentEikenSchedule = $this->em->getRepository('Application\Entity\EikenSchedule')->getCurrentEikenSchedule();
        if($process || intval($currentEikenSchedule['id']) != $eikenScheduleId){
            $result['success'] = 0;
        }
        return $this->getResponse()->setContent(Json::encode($result));
    }
    
    public function getTranslation() {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $sl  = $this->getServiceLocator();
        $vhm = $sl->get('viewhelpermanager');
        $url = $vhm->get('url');
        $urlPolicy = $url('org-mnt/default', array(
                                                'controller' => 'org',
                                                'action' => 'policy-grade-class'
                                            ));
        $messages = array(
            'okConfirmGenarateEx' => $translator->translate('okConfirmGenarateEx'),
            'cancelConfirmGenarateEx' => $translator->translate('cancelConfirmGenarateEx'),
            'MSGConfirmGenarateEx' => $translator->translate('MSGConfirmGenarateEx'),
            'MSGPopupWaringGradeClassECSetting' => sprintf($translator->translate('MSGPopupWaringGradeClassECSetting'), $urlPolicy)
        );

        return json_encode($messages);
    }
    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function translate($key)
    {
        return $this->getServiceLocator()
            ->get('MVCTranslator')
            ->translate($key);
    }
}
