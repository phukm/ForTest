<?php

namespace PupilMnt\Service;

use Application\Entity\Repository\ApplyEikenLevelRepository;
use PupilMnt\PupilConst;
use PupilMnt\Service\ServiceInterface\PupilServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Dantai\PrivateSession;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Filter\Null;
use Application\Entity\ClassJ;
use Application\Entity\OrgSchoolYear;
use PupilMnt\Form\NewPupilForm;
use PupilMnt\Form\UploadForm;
use PupilMnt\Form\SearchPupilForm;
use Zend\Validator\File\Count;
use Dantai\Utility\CharsetConverter;
use Zend\Validator\Explode;
use Doctrine\ORM\Query\AST\Functions\TrimFunction;
use Zend\Filter\HtmlEntities;
use Zend\Json\Json;
use Dantai;
use Dantai\Utility\DateHelper;
use Dantai\PublicSession;
class PupilService implements PupilServiceInterface, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    private $id_org = 0;
    protected $em;

    public function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function __construct() {
        $user = PrivateSession::getData('userIdentity');
        $this->id_org = $user['organizationId'];
    }

    public function checkResultEikenAndIbaPupil($pupilListId)
    {
        $pupilListId = explode(',', $pupilListId);
        $eikenPupilList = $this->getEntityManager()->getRepository('Application\Entity\EikenTestResult')->checkEikenTestResultPupil($pupilListId);
        $pupilListId = array_flip($pupilListId);
        foreach ($eikenPupilList as $pupilId => $val) {
            unset($pupilListId[$pupilId]);
        }
        $pupilListId = array_flip($pupilListId);
        $ibaPupilList = $this->getEntityManager()->getRepository('Application\Entity\IBATestResult')->checkResultIbaTestResultPupil($pupilListId);

        return array_merge($eikenPupilList, $ibaPupilList);
    }

    private function getKeyOfFieldImport() {
        return array(
            'Year' => 0
            , 'SchoolYear' => 1
            , 'Class' => 2
            , 'PupilNumber' => 3
            , 'FirstnameKanji' => 4
            , 'LastnameKanji' => 5
            , 'FirstnameKana' => 6
            , 'LastnameKana' => 7
            , 'Birthday' => 8
            , 'Gender' => 9
            , 'EinaviId' => 10
            , 'EikenId' => 11
            , 'EikenPassword' => 12
            , 'EikenLevel' => 13
            , 'EikenYear' => 14
            , 'Kai' => 15
            , 'EikenScoreReading' => 16
            , 'EikenScoreListening' => 17
            , 'EikenScoreWriting' => 18
            , 'EikenScoreSpeaking' => 19
            , 'IBALevel' => 20
            , 'IBADate' => 21
            , 'IBAScoreReading' => 22
            , 'IBAScoreListening' => 23
            , 'WordLevel' => 24
            , 'GrammarLevel' => 25
        );
    }

    private function getMessageError() {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsMessages = array(
            'PMSG0095' => $translator->translate('PMSG0095'),
            'PMSG0096' => $translator->translate('PMSG0096'),
            'PMSG0099' => $translator->translate('PMSG0099'),
            'PMSG0098' => $translator->translate('PMSG0098'),
            'kanaError' => $translator->translate('kanaError'),
            'kanjError' => $translator->translate('kanjError'),
            'birthday' => $translator->translate('birthday'),
            'equal2Years' => $translator->translate('equal2Years'),
            'birthdayNow' => $translator->translate('birthdayNow'),
            'MsgYearError1' => $translator->translate('MsgYearError1'),
            'MsgYearError2' => $translator->translate('MsgYearError2'),
            'MsgSchoolYearError1' => $translator->translate('MsgSchoolYearError1'),
            'MsgClassError1' => $translator->translate('MsgClassError1'),
            'MsgPupilNumberError1' => $translator->translate('MsgPupilNumberError1'),
            'MsgPupilNumberError2' => $translator->translate('MsgPupilNumberError2'),
            'MsgFirstnameKanjiError1' => $translator->translate('MsgFirstnameKanjiError1'),
            'MsgFirstnameKanjiError2' => $translator->translate('MsgFirstnameKanjiError2'),
            'MsgLastnameKanjiError1' => $translator->translate('MsgLastnameKanjiError1'),
            'MsgLastnameKanjiError2' => $translator->translate('MsgLastnameKanjiError2'),
            'MsgNameKanjiError1' => $translator->translate('MsgNameKanjiError1'),
            'MsgFirstnameKanaError1' => $translator->translate('MsgFirstnameKanaError1'),
            'MsgFirstnameKanaError2' => $translator->translate('MsgFirstnameKanaError2'),
            'MsgLastnameKanaError1' => $translator->translate('MsgLastnameKanaError1'),
            'MsgLastnameKanaError2' => $translator->translate('MsgLastnameKanaError2'),
            'MsgBirthdayError1' => $translator->translate('MsgBirthdayError1'),
            'MsgBirthdayError2' => $translator->translate('MsgBirthdayError2'),
            'MsgBirthdayError3' => $translator->translate('MsgBirthdayError3'),
            'MsgBirthdayError4' => $translator->translate('MsgBirthdayError4'),
            'MsgGenderError1' => $translator->translate('MsgGenderError1'),
            'MsgEinaviIdError1' => $translator->translate('MsgEinaviIdError1'),
            'MsgEikenIdError1' => $translator->translate('MsgEikenIdError1'),
            'MsgEikenPasswordError1' => $translator->translate('MsgEikenPasswordError1'),
            'MsgEikenLevelError1' => $translator->translate('MsgEikenLevelError1'),
            'MsgEikenYearError1' => $translator->translate('MsgEikenYearError1'),
            'MsgEikenYearError2' => $translator->translate('MsgEikenYearError2'),
            'MsgEikenKaiError1' => $translator->translate('MsgEikenKaiError1'),
            'MsgEikenKaiError2' => $translator->translate('MsgEikenKaiError2'),
            'MsgEikenScoreReadingError1' => $translator->translate('MsgEikenScoreReadingError1'),
            'MsgEikenScoreReadingError2' => $translator->translate('MsgEikenScoreReadingError2'),
            'MsgEikenScoreListeningError1' => $translator->translate('MsgEikenScoreListeningError1'),
            'MsgEikenScoreListeningError2' => $translator->translate('MsgEikenScoreListeningError2'),
            'MsgEikenScoreWritingError1' => $translator->translate('MsgEikenScoreWritingError1'),
            'MsgEikenScoreWritingError2' => $translator->translate('MsgEikenScoreWritingError2'),
            'MsgEikenScoreSpeakingError1' => $translator->translate('MsgEikenScoreSpeakingError1'),
            'MsgEikenScoreSpeakingError2' => $translator->translate('MsgEikenScoreSpeakingError2'),
            'MsgIBALevelError1' => $translator->translate('MsgIBALevelError1'),
            'MsgIBADateError1' => $translator->translate('MsgIBADateError1'),
            'MsgIBADateError2' => $translator->translate('MsgIBADateError2'),
            'MsgIBAScoreReadingError1' => $translator->translate('MsgIBAScoreReadingError1'),
            'MsgIBAScoreReadingError2' => $translator->translate('MsgIBAScoreReadingError2'),
            'MsgIBAScoreListeningError1' => $translator->translate('MsgIBAScoreListeningError1'),
            'MsgIBAScoreListeningError2' => $translator->translate('MsgIBAScoreListeningError2'),
            'MsgWordLevelError1' => $translator->translate('MsgWordLevelError1'),
            'MsgGrammarLevelError1' => $translator->translate('MsgGrammarLevelError1'),
            'MsgClassOfSchoolYearError1' => $translator->translate('MsgClassOfSchoolYearError1'),
            'MsgDuplicatePupilError1' => $translator->translate('MsgDuplicatePupilError1'),
            'MsgEmptyYear' => $translator->translate('MsgEmptyYear'),
            'msgEmptyNameKana' => $translator->translate('msgEmptyNameKana')
        );
        return $jsMessages;
    }

    public function getPagedListPupil($event, $routeMatch, $request, $params, $flashMessenger, $dantaiService) {
        $form = new SearchPupilForm();
        $sm = $event->getApplication()->getServiceManager();
        $redirect = $sm->get('ControllerPluginManager')->get('redirect');
        $em = $this->getEntityManager();
        $page = $params->fromRoute('page', 1);
        $limit = 20;
        $mess = false;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;
        
        $searchYear = '';
        $searchOrgSchoolYear = '';
        $searchClass = '';
        $searchName = '';
        
        $searchCriteria = $dantaiService->getSearchCriteria($event, $params->fromPost());
        
        if ($request->isPost() && $searchCriteria['token']) {
            return $redirect->toUrl('/pupil/pupil/index/search/' . $searchCriteria['token']);
        }
        if($searchCriteria)
        {
            $searchYear = isset($searchCriteria['year'])?$searchCriteria['year']:'';
            $searchOrgSchoolYear = isset($searchCriteria['orgSchoolYear'])?$searchCriteria['orgSchoolYear']:'';
            $searchClass = isset($searchCriteria['classj'])?$searchCriteria['classj']:'';
            $searchName = isset($searchCriteria['name'])?$searchCriteria['name']:'';
        }
        
        $listclass = $this->listSearchClass($searchYear, $searchOrgSchoolYear);
        $yearschool = $this->listshoolyear();
        
        $form->get("year")
                ->setValueOptions($this->year())
                ->setAttributes(array(
                    'value' => $searchYear,
                    'selected' => true,
                    'escape' => false
        ));
        $form->get("orgSchoolYear")
                ->setValueOptions($yearschool)
                ->setAttributes(array(
                    'value' => $searchOrgSchoolYear,
                    'selected' => true,
                    'escape' => false
        ));
        $form->get("name")->setAttributes(array(
            'value' => $searchName
        ));
        $form->get("classj")
                ->setValueOptions($listclass)
                ->setAttributes(array(
                    'value' => $searchClass,
                    'selected' => true,
                    'escape' => false
        ));
        if (!empty($flashMessenger->getMessages())) {
            $messs = $flashMessenger->getMessages();
            $mess = $messs[0];
        }
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsMessages = array(
            'MSG0097' => $translator->translate('MSG0097'),
            'CanNotDeletePupil' => sprintf($translator->translate('CanNotDeletePupil'),$this->getCurrentEikenSchedule() ? $this->getCurrentEikenSchedule()['year'] : '',$this->getCurrentEikenSchedule() ? $this->getCurrentEikenSchedule()['kai'] : '')
        );
        $paginator = $em->getRepository('Application\Entity\Pupil')->getSearchPupilList($getOrganizationNo = $this->id_org, $searchYear, $searchOrgSchoolYear, $searchClass, $searchName);
        $flashMessenger->addMessage($mess);
        $testResultPupilList = $this->msgConfirmEikenIbaResult(PrivateSession::getData(PupilConst::TEST_RESULT_PUPIL_LIST));
        if ($testResultPupilList) {
            PrivateSession::clear(PupilConst::TEST_RESULT_PUPIL_LIST);
        }
        return array(
            'pupil' => $paginator->getItems($offset, $limit, false),
            'paginator' => $paginator,
            'numPerPage' => $limit,
            'form' => $form,
            'mess' => $mess,
            'jsMessages' => json_encode($jsMessages),
            'page' => $page,
            'searchVisible' => empty($search)? 0 : 1,
            'roleLimit' => PublicSession::isDisableDownloadButtonRole() || PublicSession::isViewerRole(),
            'testResultPupilList' => $testResultPupilList,
            'param' => isset($searchCriteria['token']) ? $searchCriteria['token'] : ''
        );
    }

    function msgConfirmEikenIbaResult($testResult)
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $message = array();
        if (count($testResult) > 1) {
            foreach ($testResult as $pupil) {
                $message[] = $pupil['pupilName'];
            }

            return $translator->translate('MsgConfirmEikenIbaResult') . '<br>' . implode('、', $message);
        }
        if (count($testResult) == 1) {
            return $translator->translate('MsgConfirmEikenIbaResultOne');
        }

        return false;
    }

    // detail
    public function getDetailPupil($id, $flashMessenger) {
        $em = $this->getEntityManager();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $pupil = $em->getRepository('Application\Entity\Pupil')->getPupilDetail($id, $this->id_org);
        if (empty($pupil)) {
            return 1;
        }
        $lisEkein = $this->getData();
        foreach ($pupil as $key => $value) {
            $pupil[$key] = isset($value) ? $value : '';
        }
        $pupil['eikenlevelName'] = !empty($pupil['eikenLevelIdEkr']) ? $lisEkein[$pupil['eikenLevelIdEkr']] : NULL;
        $pupil['ibalevelName'] = !empty($pupil['eikenLevelIdIbar']) ? $lisEkein[$pupil['eikenLevelIdIbar']] : NULL;
        $jsMessages = array(
            'CanNotDeletePupil' => sprintf($translator->translate('CanNotDeletePupil'),$this->getCurrentEikenSchedule() ? $this->getCurrentEikenSchedule()['year'] : '',$this->getCurrentEikenSchedule() ? $this->getCurrentEikenSchedule()['kai'] : '')
        );
        return array(
            'pupil' => $pupil,
            'jsMessages' => json_encode($jsMessages)
        );
    }

    public function getEditByPupil($id, $request, $params, $flashMessenger) {
        $mess = false;
        $em = $this->getEntityManager();
        $pupil = $em->getRepository('Application\Entity\Pupil')->getPupilDetail($id, $this->id_org);
        if (empty($pupil)) {
            return 1;
        }
        $urlPath = $request->getHeader('Referer')->uri()->getPath();
        if($urlPath == '/history/iba/empty-name-kana'){
            $redirectUrl = $urlPath.'?'.$request->getHeader('Referer')->uri()->getQuery();
        }
        else{
            $redirectUrl = '/pupil/pupil/index';
        }
        /**
         * @var \Application\Service\ServiceInterface\DantaiServiceInterface
         */
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        $dantaiService->startCrossEditing('Application\Entity\Pupil', array('id' => $id, 'organizationId' => $this->id_org));

        $yearEkr = !empty($pupil['yearEkr']) ? $pupil['yearEkr'] : NULL;
        $year = !empty($pupil['year']) ? $pupil['year'] : date('Y');
        $eikenLevelIdEkr = !empty($pupil['eikenLevelIdEkr']) ? $pupil['eikenLevelIdEkr'] : '';
        $eikenLevelIdIbar = !empty($pupil['eikenLevelIdIbar']) ? $pupil['eikenLevelIdIbar'] : '';
        $vocabularySMResult = !empty($pupil['vocabularySMResult']) ? $pupil['vocabularySMResult'] : '';
        $grammarSMResult = !empty($pupil['grammarSMResult']) ? $pupil['grammarSMResult'] : '';
        $kai = !empty($pupil['kaiEkr']) ? $pupil['kaiEkr'] : 0;
        if (empty($pupil['birthday'])) {
            $loaddatabirdday = true;
        } else {
            $loaddatabirdday = false;
        }
        $form = new NewPupilForm();
        $form->setFormEditPupil($pupil, $id)
                ->setListClass($this->listclass($pupil['year'], $pupil['orgSchoolYearId'], false), $pupil['idCl'])
                ->setListSchoolYear($this->listshoolyear(), $pupil['orgSchoolYearId'])
                ->setListKai($kai)
                ->setListBirthDay($pupil['birthday'], $loaddatabirdday)
                ->setListEikenYear($yearEkr, $year)
                ->setListEikenLevel($this->getData(true), $eikenLevelIdEkr, $eikenLevelIdIbar, $vocabularySMResult, $grammarSMResult);

        if (!empty($flashMessenger->getMessages())) {
            $messs = $flashMessenger->getMessages();
            $mess = $messs[0];
        }




      //  $crossMessages = $dantaiService->restoreCrossEditingForm('Application\Entity\Pupil', $form);
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsMessages = $this->getMessageError();
      //  $jsMessages['conflictWarning'] = $crossMessages['conflictWarning'];
      //  $jsMessages['conflictType'] = $crossMessages['conflictType'];
        
        $successMessage = ''; 
        if (!empty($flashMessenger->getMessages('addAction'))){
            $successMessage = $flashMessenger->getMessages('addAction')[0];
            $flashMessenger->clearMessages('addAction');
            $redirectUrl = 'pupil/pupil/index';
        }
        else if (!empty($flashMessenger->getMessages('emptyNameKana'))){
            $successMessage = $this->translate('MsgUpdateSuccessFully');
            $flashMessenger->clearMessages('emptyNameKana');
            $redirectUrl = 'history/iba/empty-name-kana?year='.$year;
        }
        
        return array(
            'form' => $form,
            'id' => $id,
            'mess' => $mess,
            'jsMessages' => json_encode($jsMessages),
            'successMessage' => $successMessage,
            'redirectUrl' => $redirectUrl,
            'urlPath' => $urlPath,
            'year' => $year
        );
    }
   
    // add
    public function getAddPupil($id, $request, $params, $flashMessenger) {
        $mess = false;
        $em = $this->getEntityManager();

        $form = new NewPupilForm();
        $form->setListClass($this->listclass())
                ->setListSchoolYear($this->listshoolyear())
                ->setListKai()
                ->setListBirthDay(null, true)
                ->setListEikenYear()
                ->setListEikenLevel($this->getData(true));
        
        if (!empty($flashMessenger->getMessages())) {
            $messs = $flashMessenger->getMessages();
            $mess = $messs[0];                      
        }        
        $successMessage = '';        
        if (!empty($flashMessenger->getMessages('addAction'))){
            $successMessage = $flashMessenger->getMessages('addAction')[0];
            $flashMessenger->clearMessages('addAction');
        }
        
        $jsMessages = $this->getMessageError();
        return array(
            'form' => $form,
            'mess' => $mess,
            'jsMessages' => json_encode($jsMessages),
            'successMessage' => $successMessage
        );
    }

    // end add
    public function UpdatePupil($id, $request, $params, $flashMessenger) {
        $em = $this->getEntityManager();
        $checkReturnEmtyNameKana = $params->fromQuery('emptyNameKana','');
        $return = array(
            'module' => 'pupil-mnt',
            'controller' => 'pupil',
            'action' => 'index'
        );
        if ($request->isPost()) {
            $data = $params->fromPost();
            foreach ($data as $key => $value) {
                $data[$key] = isset($value) ? trim($value) : '';
            }
            $pupil = $this->setEntityPupil($data, $id);
//Uthv Delete cross edit
//            if ($id) {
//                /**
//                 * @var \Application\Service\ServiceInterface\DantaiServiceInterface
//                 */
//                $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
//                $crossMessages = $dantaiService->checkCrossEditing('Application\Entity\Pupil', array('id' => $id), $data);
//                if ($crossMessages['conflictWarning']) {
//                    if ('edit' == $crossMessages['conflictType']) {
//                        $return['action'] = 'edit';
//                        $return['id'] = $id;
//                    }
//
//                    return $return;
//                }
//            }

            $ibaScore = $this->setEntityIBAScore($data, $pupil, $id);
            $eikenScore = $this->setEntityEikenScore($data, $pupil, $id);
            $simpleMResult = $this->setEntitySimpleMResult($data, $pupil, $id);
            $em->persist($pupil);
            $em->persist($ibaScore);
            $em->persist($eikenScore);
            $em->persist($simpleMResult);     
            $translator = $this->getServiceLocator()->get('MVCTranslator');
            if($checkReturnEmtyNameKana){
                $flashMessenger->addMessage($translator->translate('MsgUpdateSuccessFully'), 'emptyNameKana'); 
            }
            else{
                $flashMessenger->addMessage($translator->translate('MsgUpdateSuccessFully'), 'addAction'); 
            }
            try {
                $em->flush();
                $em->clear();
            } catch (Exception $e) {
                throw $e;
            }
        }
        return $return;
    }

    public function getImportPupil($request, $Response) {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsMessages = array(
            'MSG21' => $translator->translate('MSG21'),
            'MSG22' => $translator->translate('MSG22'),
            'MSG23' => $translator->translate('MSG23'),
            'MSG001' => $translator->translate('MSG001'),
            'MSNOR' => $translator->translate('MSNOR'),
        );
        $form = new UploadForm('upload-form');
        if ($request->isPost()) {
            $post = array_merge_recursive($request->getPost()->toArray(), $request->getFiles()->toArray());
            $form->setData($post);
            if ($form->isValid()) {
                $dataform = $form->getData();
                if (isset($dataform)) {
                    $status = 1;
                    $namesrc = '';
                    $namesrc = str_replace("\\", '/', $dataform["csvfile"]["tmp_name"]);
                    $data = $this->readfilecsv($namesrc);
                    if (!empty($data) && ($data['status'] == 2 || $data['status'] == 3 || $data['status'] == 4)) {
                        @unlink($namesrc);
                    }
                    $dataview = Json::encode($data);
                    if (!isset($dataview) && !$dataview) {
                        @unlink($namesrc);
                        $data = array(
                            'status' => 2
                        );
                        $dataview = Json::encode($data);
                    }
                    return $Response->setContent($dataview);
                }
            } else {
                @unlink($namesrc);
                $data = array(
                    'status' => 2
                );
                return $Response->setContent(Json::encode($data));
            }
        }
        return array(
            'form' => $form,
            'masagepupil' => Json::encode($jsMessages)
        );
    }

    public function getSaveImportPupil($request, $params) {
        $em = $this->getEntityManager();
        $namesrc = $params->fromPost('scrfile', false);
        if (isset($namesrc) && $namesrc) {
            $csv = CharsetConverter::shiftJisToUtf8(file_get_contents($namesrc));
            $datas = \Dantai\Utility\CsvHelper::csvStrToArray($csv);
            unset($datas[0]);
            if (!empty($datas)) {
                $importline = 1000;
                $z = 0;
                foreach ($datas as $k => $v) {
                    for ($i = 0; $i < 26; $i ++) {
                        $v[$i] = trim($v[$i]);
                        if (!isset($v[$i]) || (isset($v[$i]) && $v[$i] == '')) {
                            $v[$i] = NULL;
                        }
                    }
                    $pupil = $this->setEntityPupil($v, 0, true);
                    $ibaScore = $this->setEntityIBAScore($v, $pupil, 0, true);
                    $eikenScore = $this->setEntityEikenScore($v, $pupil, 0, true);
                    $simpleMResult = $this->setEntitySimpleMResult($v, $pupil, 0, true);
                    $em->persist($pupil);
                    $em->persist($eikenScore);
                    $em->persist($ibaScore);
                    $em->persist($simpleMResult);
                    if (($z % $importline) == 0) {
                        try {
                            $em->flush();
                            $em->clear('\Application\Entity\Pupil');
                            $em->clear('\Application\Entity\EikenScore');
                            $em->clear('\Application\Entity\IBAScore');
                            $em->clear('\Application\Entity\SimpleMeasurementResult');
                        } catch (Exception $e) {
                            throw $e;
                        }
                    }
                    $z ++;
                }
                $em->flush();
                $em->clear('\Application\Entity\Pupil');
                $em->clear('\Application\Entity\EikenScore');
                $em->clear('\Application\Entity\IBAScore');
                $em->clear('\Application\Entity\SimpleMeasurementResult');
            }
        }
    }

    public function getAjaxListClass($params, $Response) {
        $em = $this->getEntityManager();
        $year = $params->fromPost('year', null);
        $schoolyear = $params->fromPost('schoolyear', null);
        $string = array();
        if (isset($schoolyear) && $schoolyear) {
            $objClass = $em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYearAndYearAjax($year, $schoolyear, $this->id_org);
            if (!empty($objClass)) {
                foreach ($objClass as $key => $value) {
                    $string['classj'][$key] = $value;
                }
            }
        } else {
            $string['classj'] = '';
        }
        return $Response->setContent(Json::encode($string));
    }

    public function getExportTemplate($response) {

//        $header = '年度（*）,学年（*）,クラス（*）,番号（*）,氏名（姓）（漢字）（*）,氏名（名）（漢字）（*）,氏名（姓）（カナ）（*）,氏名（名）（カナ）（*）,生年月日（*）,性別,英ナビ！個人ID,英検ID,英検パスワード,取得済級,英検年度,英検回,英検CSEスコアリーディング,英検CSEスコアリスニング,英検CSEスコア作文 ,英検CSEスコアスピーキング,IBAレベル判定,IBA実施日,IBA CSEスコアリーディング,IBA CSEスコアリスニング,単語レベル,文法レベル';
//        $header = explode(',', $header);
        $importService = $this->getServiceLocator()->get('PupilMnt\Service\ImportPupilServiceInterface');
        $field = $importService->getKeyOfFieldImport();
        $arrFieldJapan = array();
        foreach ($field as $key => $value) {
            $arrFieldJapan[] = $this->translate('Import' . $key);
        }
        $header = $arrFieldJapan;
        $csv = \Dantai\Utility\CsvHelper::arrayToStrCsv(array($arrFieldJapan));
        $csv = trim(mb_convert_encoding($csv, 'SJIS', 'UTF-8'));
        $filenames = "生徒名簿登録用テンプレート";
        $filenames = \Dantai\Utility\CharsetConverter::utf8ToShiftJis($filenames);
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'application/csv, charset=Shift_JIS');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"$filenames.csv\"");
        $headers->addHeaderLine('Accept-Ranges', 'bytes');
        $headers->addHeaderLine('Content-Length', strlen($csv));
        $headers->addHeaderLine('Content-Transfer-Encoding: Shift_JIS');
        $response->setHeaders($headers);
        $response->setContent($csv);
        return $response;
    }

    public function getExportPupil($response, $params, $routeMatch, $request, $dantaiService) {
        $data = $params->fromPost("exportItem");
        $cookieExport = $this->getCookieExport($data);
        
        $field = $this->getKeyOfFieldImport();
        $arrFieldJapan = array();
        foreach ($field as $key => $value) {
            if($key == 'SchoolYear'){
                $arrFieldJapan[] = $this->translate('ImportOrgSchoolYear');
            }else{
                $arrFieldJapan[] = $this->translate('Import' . $key);
            }
            
        }
        $header = $arrFieldJapan;
        $em = $this->getEntityManager();
        if ($request->isPost()) {
            $search = $params->fromPost();
            $dantaiService->setSearchKeywordToSession($routeMatch, $search);
        }
        $searchArray = $dantaiService->getSearchKeywordFromSession('PupilMnt\Controller\Pupil_index');
        if (!empty($searchArray)) {
            $searchYear = (isset($searchArray['year'])) ? $searchArray['year'] : '';
            $searchOrgSchoolYear = (isset($searchArray['orgSchoolYear'])) ? $searchArray['orgSchoolYear'] : '';
            $searchClass = (isset($searchArray['classj'])) ? $searchArray['classj'] : '';
            $searchName = (isset($searchArray['name'])) ? $searchArray['name'] : '';
        } else {
            $searchYear = $params->fromPost('year', date('Y'));
            $searchOrgSchoolYear = $params->fromPost('orgSchoolYear', '');
            $searchClass = $params->fromPost('classj', '');
            $searchName = $this->remove_special_characters(trim($params->fromPost('name', '')));
        }
        $pupil = $em->getRepository('Application\Entity\Pupil')->getPupilExport($getOrganizationNo = $this->id_org, $searchYear, $searchOrgSchoolYear, $searchClass, $searchName, $cookieExport);
        
        
        $pupils[0] = $header;
        $lisEkein = $this->getData();
        if (!empty($pupil) && is_array($pupil)) {
            $pupilIds = array_keys($pupil);
            $last6year = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d"), date("Y") - 6));
            $last3year = date('Y-m-d', mktime(0, 0, 0, date("m"), date("d"), date("Y") - 3));
            
            $dataEikenScore = $em->getRepository('Application\Entity\EikenScore')->getEikenScoreByPupilIdsAndDate($pupilIds, $last6year);
            $dataIBAScore = $em->getRepository('Application\Entity\IBAScore')->getIBAScoreByPupilIdsAndDate($pupilIds, $last3year);
            $eikenScore = array();
            foreach($dataEikenScore as $value){
                if(empty($eikenScore[$value['pupilId']])){
                    $eikenScore[$value['pupilId']] = $value;      
                }
            }
            
            $ibaScore = array();
            foreach($dataIBAScore as $value){
                if(empty($ibaScore[$value['pupilId']])){
                    $ibaScore[$value['pupilId']] = $value;      
                }
            }
            $dataPupil = $this->mapMultiArray($pupil, $this->mapMultiArray($ibaScore, $eikenScore));
            foreach($dataPupil as $key=>$value){
                unset($dataPupil[$key]['id']);
                unset($dataPupil[$key]['pupilId']);
            }

            foreach ($dataPupil as $k => $line) {
                unset($pupil[$k]['id']);
                if (isset($line['gender']) && $line['gender'] === 0) {
                    $line['gender'] = '女';
                } else {
                    $line['gender'] = '男';
                }
                if (isset($line['birthday']) && $line['birthday']) {
                    $line['birthday'] = $line['birthday']->format('Y/m/d');
                } else {
                    $line['birthday'] = '';
                }
                if (isset($line['examDate']) && $line['examDate']) {
                    $line['examDate'] = $line['examDate']->format('Y/m/d');
                } else {
                    $line['examDate'] = '';
                }
                if(isset($line['firstNameKanji']) && $line['firstNameKanji']){
                    if (strpos($line['firstNameKanji'], '﨑髙') !== false) {
                        $line['firstNameKanji'] = str_replace('﨑髙', '？？', $line['firstNameKanji']);
                    }
                }
                if(isset($line['lastNameKanji']) && $line['lastNameKanji']){
                    if (strpos($line['lastNameKanji'], '﨑髙') !== false) {
                        $line['lastNameKanji'] = str_replace('﨑髙', '？？', $line['lastNameKanji']);
                    }
                }
//                if (isset($line['examDateEkien']) && $line['examDateEkien']) {
//                    $line['examDateEkien'] = $line['examDateEkien']->format('Y/m/d');
//                } else {
//                    $line['examDateEkien'] = '';
//                }
                $line['eikenlevelName'] = !empty($line['eikenlevelName']) ? $lisEkein[$line['eikenlevelName']] : NULL;
                $line['ibalevelName'] = !empty($line['ibalevelName']) ? $lisEkein[$line['ibalevelName']] : NULL;
                array_push($pupils, $line);
            }
        }
        //
        $csv = \Dantai\Utility\CsvHelper::arrayToStrCsv($pupils);
        $csv = mb_convert_encoding($csv, 'SJIS', 'UTF-8');
        //
        $OrganizationName = '';
        $Organization = $em->getReference('Application\Entity\Organization', array(
            'id' => $this->id_org
        ));
        if (empty($Organization)) {
            $nameOrganization = '';
        } else {
            $nameOrganization = $Organization->getOrgNameKanji();
        }
        $filenames = "生徒名簿__" . $nameOrganization . "_" . date('Ymd');
        $filenames = \Dantai\Utility\CharsetConverter::utf8ToShiftJis($filenames);
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'application/csv, charset=Shift_JIS');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"$filenames.csv\"");
        $headers->addHeaderLine('Accept-Ranges', 'bytes');
        $headers->addHeaderLine('Content-Length', strlen($csv));
        $headers->addHeaderLine('Content-Transfer-Encoding: Shift_JIS');
        $response->setHeaders($headers);
        $response->setContent($csv);
        return $response;
    }
   

    function mapMultiArray($array1 = array(), $array2 = array())
    {
        foreach ($array2 as $key => $data) {
            foreach ($data as $field => $value) {
                $array1[$key][$field] = $value;
            }
        }

        return $array1;
    }

    public function getDeletePupil($id, $params, $Messenger) {
        $em = $this->getEntityManager();
        $data = $params->fromPost('exportItem');
        if (!empty($id)) {
            $pupil = $em->getRepository('Application\Entity\Pupil')->findOneBy(array(
                'id' => (int) $id,
                'organizationId' => $this->id_org,
                'isDelete' => 0
            ));
            if ($pupil) {
                $InvitationLetter = $em->getRepository('Application\Entity\InvitationLetter')->findOneBy(array(
                    'pupilId' => (int) $id,
                    'isDelete' => 0
                ));
                $RecommendLevel = $em->getRepository('Application\Entity\RecommendLevel')->findOneBy(array(
                    'pupilId' => (int) $id,
                    'isDelete' => 0
                ));
                $PaymentInfo = $em->getRepository('Application\Entity\PaymentInfo')->findOneBy(array(
                    'pupilId' => (int) $id,
                    'isDelete' => 0
                ));
                if ($InvitationLetter) {
                    $InvitationLetter->setIsDelete(1);
                    $em->persist($InvitationLetter);
                }
                if ($RecommendLevel) {
                    $RecommendLevel->setIsDelete(1);
                    $em->persist($RecommendLevel);
                }
                if ($PaymentInfo) {
                    $PaymentInfo->setIsDelete(1);
                    $em->persist($PaymentInfo);
                }
                $pupil->setIsDelete(1);
                $em->persist($pupil);
                $em->flush();
            }
        }
        if (!empty($data)) {
            $data = trim(str_replace(',,', ',', $data), ',');
            $data = explode(',', trim(trim($data, ',')));
        }
        if (!empty($data)) {
            $needFlush = false;
            foreach ($data as $k => $pupilId) {
                $pupil = $em->getRepository('Application\Entity\Pupil')->findOneBy(array(
                    'id' => (int) $pupilId,
                    'organizationId' => $this->id_org,
                    'isDelete' => 0
                ));
                if ($pupil) {
                    $InvitationLetter = $em->getRepository('Application\Entity\InvitationLetter')->findOneBy(array(
                        'pupilId' => (int) $pupilId,
                        'isDelete' => 0
                    ));
                    $RecommendLevel = $em->getRepository('Application\Entity\RecommendLevel')->findOneBy(array(
                        'pupilId' => (int) $pupilId,
                        'isDelete' => 0
                    ));
                    $PaymentInfo = $em->getRepository('Application\Entity\PaymentInfo')->findOneBy(array(
                        'pupilId' => (int) $pupilId,
                        'isDelete' => 0
                    ));
                    if ($InvitationLetter) {
                        $InvitationLetter->setIsDelete(1);
                        $em->persist($InvitationLetter);
                    }
                    if ($RecommendLevel) {
                        $RecommendLevel->setIsDelete(1);
                        $em->persist($RecommendLevel);
                    }
                    if ($PaymentInfo) {
                        $PaymentInfo->setIsDelete(1);
                        $em->persist($PaymentInfo);
                    }
                    $needFlush = true;
                    $pupil->setIsDelete(1);
                    $em->persist($pupil);
                }
            }
            if ($needFlush) {
                $em->flush();
            }
        }
    }

    public function setEntitySimpleMResult($data = NULL, $pupil = NULL, $id = false, $upload = false) {
        static $resultVocabularyS = array();
        static $resultGrammarS = array();
        static $callTime = 0;

        if (++$callTime > 5000) {
            $resultVocabularyS = array();
            $resultGrammarS = array();
        }
        $em = $this->getEntityManager();
        if ($upload) {
            $field = $this->getKeyOfFieldImport();
            $resultVocabularyData = array(
                'levelName' => $data[$field['WordLevel']],
                'isDelete' => 0
            );
            $key = md5(serialize($resultVocabularyData));
            if (array_key_exists($key, $resultVocabularyS)) {
                $resultVocabulary = $resultVocabularyS[$key];
            } else {
                $resultVocabularyS[$key] = $resultVocabulary = $em->getRepository('Application\Entity\EikenLevel')->findOneBy($resultVocabularyData);
            }
            $resultGrammarData = array(
                'levelName' => $data[$field['GrammarLevel']],
                'isDelete' => 0
            );
            $key = md5(serialize($resultGrammarData));
            if (array_key_exists($key, $resultGrammarS)) {
                $resultGrammar = $resultGrammarS[$key];
            } else {
                $resultGrammarS[$key] = $resultGrammar = $em->getRepository('Application\Entity\EikenLevel')->findOneBy($resultGrammarData);
            }
            if (empty($resultVocabulary)) {
                $resultVocabulary = NULL;
                $ResultVocabularyName = NULL;
            } else {
                $ResultVocabularyName = $resultVocabulary->getLevelName();
            }
            if (empty($resultGrammar)) {
                $resultGrammar = NULL;
                $ResultGrammarName = NULL;
            } else {
                $ResultGrammarName = $resultGrammar->getLevelName();
            }
        } else {
            $simpleMResult = $em->getRepository('Application\Entity\SimpleMeasurementResult')->findOneBy(array(
                'pupilId' => (int) $id,
                'status' => 'Active',
                'isDelete' => 0
            ));
            $idresultGrammar = (!empty($data['resultGrammar'])) ? $this->remove_special_characters(trim($data['resultGrammar'])) : NULL;
            $idresultVocabulary = (!empty($data['resultVocabulary'])) ? $this->remove_special_characters(trim($data['resultVocabulary'])) : NULL;
            $resultGrammar = $em->getRepository('Application\Entity\EikenLevel')->findOneBy(array(
                'id' => (int) $idresultGrammar,
                'isDelete' => 0
            ));
            $resultVocabulary = $em->getRepository('Application\Entity\EikenLevel')->findOneBy(array(
                'id' => (int) $idresultVocabulary,
                'isDelete' => 0
            ));
            if (empty($resultVocabulary)) {
                $resultVocabulary = NULL;
                $ResultVocabularyName = NULL;
            } else {
                $ResultVocabularyName = $resultVocabulary->getLevelName();
            }

            if (empty($resultGrammar)) {
                $resultGrammar = NULL;
                $ResultGrammarName = NULL;
            } else {
                $ResultGrammarName = $resultGrammar->getLevelName();
            }
        }
        if (empty($simpleMResult)) {
            $simpleMResult = new \Application\Entity\SimpleMeasurementResult();
        }
        $simpleMResult->setStatus('Active');
        $simpleMResult->setPupil($pupil);
        $simpleMResult->setResultVocabulary($resultVocabulary);
        $simpleMResult->setResultVocabularyName($ResultVocabularyName);
        $simpleMResult->setResultGrammar($resultGrammar);
        $simpleMResult->setResultGrammarName($ResultGrammarName);
        return $simpleMResult;
    }

    public function setEntityEikenScore($data, $pupil, $id, $upload = false) {
        $em = $this->getEntityManager();
        static $eikenLevelS = array();
        static $callTime = 0;

        if (++$callTime > 5000) {
            $eikenLevelS = array();
        }

        if ($upload) {
            $field = $this->getKeyOfFieldImport();
            $levelData = array(
                'levelName' => $data[$field['EikenLevel']],
                'isDelete' => 0
            );
            $year = (!empty($data[$field['EikenYear']])) ? (int) trim($data[$field['EikenYear']]) : NULL;
            $kai = (!empty($data[$field['Kai']])) ? (int) trim($data[$field['Kai']]) : NULL;
            $key = md5(serialize($levelData));
            if (array_key_exists($key, $eikenLevelS)) {
                $eikenLevel1 = $eikenLevelS[$key];
            } else {
                $eikenLevelS[$key] = $eikenLevel1 = $em->getRepository('Application\Entity\EikenLevel')->findOneBy($levelData);
            }
            $cSEScoreReading = (isset($data[$field['EikenScoreReading']])) ? trim($data[$field['EikenScoreReading']]) : NULL;
            $cSEScoreListening = (isset($data[$field['EikenScoreListening']])) ? trim($data[$field['EikenScoreListening']]) : NULL;
            $cSEScoreWriting = (isset($data[$field['EikenScoreWriting']])) ? trim($data[$field['EikenScoreWriting']]) : NULL;
            $cSEScoreSpeaking = (isset($data[$field['EikenScoreSpeaking']])) ? trim($data[$field['EikenScoreSpeaking']]) : NULL;
        } else {
            $ideikenLevel = (isset($data['eikenLevel'])) ? $data['eikenLevel'] : NULL;
            $cSEScoreReading = (isset($data['eikenRead'])) ? trim($data['eikenRead']) : NULL;
            $cSEScoreListening = (isset($data['eikenListen'])) ? trim($data['eikenListen']) : NULL;
            $cSEScoreWriting = (isset($data['eikenWrite'])) ? trim($data['eikenWrite']) : NULL;
            $cSEScoreSpeaking = (isset($data['eikenSpeak'])) ? trim($data['eikenSpeak']) : NULL;
            $eikenLevel1 = $em->getRepository('Application\Entity\EikenLevel')->findOneBy(array(
                'id' => (int) $ideikenLevel,
                'isDelete' => 0
            ));
            $kai = (!empty($data['kai'])) ? (int) trim($data['kai']) : NULL;
            $year = (!empty($data['eikenYear'])) ? (int) trim($data['eikenYear']) : NULL;
        }
        $total = (int) $cSEScoreReading + (int) $cSEScoreListening + (int) $cSEScoreWriting + (int) $cSEScoreSpeaking;
        if (empty($eikenLevel1)) {
            $eikenLevel1 = NULL;
        }
//        if (empty($data['examDateEkien'])) {
//            $examDateString = NULL;
//        } else {
//            $examDateString = new \Datetime(date($data['examDateEkien']));
//        }      
        $eikenLevelId = $eikenLevel1 === Null ? 0 : $eikenLevel1->getId();
        $examDateString = $this->getExamDateByYearAndKaiAndEikenLevel($year, $kai, $eikenLevelId);

        if ($id != 0) {
            $eikenScore = $em->getRepository('Application\Entity\EikenScore')->findOneBy(array(
                'pupilId' => (int) $id,
                'status' => 'Active',
                'isDelete' => 0
            ));
            if (!empty($eikenScore)) {
                if (empty($eikenScore->getKai())) {
                    $eikenScoreKai = '';
                } else {
                    $eikenScoreKai = $eikenScore->getKai();
                }
                if (empty($eikenScore->getYear())) {
                    $eikenScoreYear = '';
                } else {
                    $eikenScoreYear = $eikenScore->getYear();
                }
                if (empty($eikenScore->getEikenLevelId())) {
                    $eikenScoreEikenLevelId = 0;
                } else {
                    $eikenScoreEikenLevelId = $eikenScore->getEikenLevelId();
                }
                $Readingsc = $eikenScore->getReadingScore();
                $Listeningsc = $eikenScore->getListeningScore();
                $Writingsc = $eikenScore->getCSEScoreWriting();
                $Speakingsc = $eikenScore->getCSEScoreSpeaking();
                if ($Readingsc != $cSEScoreReading || $Listeningsc != $cSEScoreListening || $Writingsc != $cSEScoreWriting || $examDateString != $eikenScore->getCertificationDate() || $Speakingsc != $cSEScoreSpeaking || (int) $kai != (int) $eikenScoreKai || $year != $eikenScoreYear || (int) $ideikenLevel != (int) $eikenScoreEikenLevelId) {
                    $eikenScore->setStatus('Inactive');
                    $em->persist($eikenScore);
                    $em->flush();
                    $em->clear('Application\Entity\EikenScore');
                    $eikenScore = new \Application\Entity\EikenScore();
                }
            }
        }
        if (empty($eikenScore) || $id == 0) {
            $eikenScore = new \Application\Entity\EikenScore();
        }
        if ($cSEScoreReading == '') {
            $cSEScoreReading = NULL;
        }
        if ($cSEScoreListening == '') {
            $cSEScoreListening = NULL;
        }
        if ($cSEScoreWriting == '') {
            $cSEScoreWriting = NULL;
        }
        if ($cSEScoreSpeaking == '') {
            $cSEScoreSpeaking = NULL;
        }
        $eikenScore->setStatus('Active');
        $eikenScore->setPassFailFlag(1);
        $eikenScore->setCertificationDate($examDateString);
        $eikenScore->setKai($kai);
        $eikenScore->setYear($year);
        $eikenScore->setReadingScore($cSEScoreReading);
        $eikenScore->setListeningScore($cSEScoreListening);
        $eikenScore->setCSEScoreWriting($cSEScoreWriting);
        $eikenScore->setCSEScoreSpeaking($cSEScoreSpeaking);
        $eikenScore->setEikenLevel($eikenLevel1);
        $eikenScore->setEikenCSETotal($total);
        $eikenScore->setPupil($pupil);
        return $eikenScore;
    }

    public function setEntityIBAScore($data, $pupil, $id, $upload = false) {

        static $eikenLevelS = array();
        static $callTime = 0;

        if (++$callTime > 5000) {
            $eikenLevelS = array();
        }
        $em = $this->getEntityManager();
        if ($upload) {
            $field = $this->getKeyOfFieldImport();
            $data['datetime'] = $data[$field['IBADate']];
            $read = (isset($data[$field['IBAScoreReading']])) ? trim($data[$field['IBAScoreReading']]) : NULL;
            $listen = (isset($data[$field['IBAScoreListening']])) ? trim($data[$field['IBAScoreListening']]) : NULL;
            $levelData = array(
                'levelName' => $data[$field['IBALevel']],
                'isDelete' => 0
            );
            $key = md5(serialize($levelData));
            if (array_key_exists($key, $eikenLevelS)) {
                $eikenLevel2 = $eikenLevelS[$key];
            } else {
                $eikenLevelS[$key] = $eikenLevel2 = $em->getRepository('Application\Entity\EikenLevel')->findOneBy($levelData);
            }
        } else {
            $read = (isset($data['ibaRead'])) ? trim($data['ibaRead']) : NULL;
            $listen = (isset($data['ibaListen'])) ? trim($data['ibaListen']) : NULL;
            $ibaLevel = (isset($data['ibaLevel'])) ? (int) trim($data['ibaLevel']) : NULL;
            $eikenLevel2 = $em->getRepository('Application\Entity\EikenLevel')->findOneBy(array(
                'id' => (int) $ibaLevel,
                'isDelete' => 0
            ));
        }
        if (empty($eikenLevel2)) {
            $eikenLevel2 = NULL;
        }
        if (empty($data['datetime'])) {
            $examDateString = NULL;
        } else {
            $examDateString = new \Datetime(date($data['datetime']));
        }
        if ($id != 0) {
            $ibaScore = $em->getRepository('Application\Entity\IBAScore')->findOneBy(array(
                'pupilId' => (int) $id,
                'status' => 'Active',
                'isDelete' => 0
            ));
            if (!empty($ibaScore)) {
                if (empty($ibaScore->getEikenLevelId())) {
                    $ibaEikenLevelId = 0;
                } else {
                    $ibaEikenLevelId = $ibaScore->getEikenLevelId();
                }
                $Readingsc = $ibaScore->getReadingScore();
                $Listeningsc = $ibaScore->getListeningScore();
                if ($Readingsc != $read || $Listeningsc != $listen || $examDateString != $ibaScore->getExamDate() || (int) $ibaLevel != (int) $ibaEikenLevelId) {
                    $ibaScore->setStatus('Inactive');
                    $em->persist($ibaScore);
                    $em->flush();
                    $em->clear('Application\Entity\IBAScore');
                    $ibaScore = new \Application\Entity\IBAScore();
                }
            }
        }
        if (empty($ibaScore) || $id == 0) {
            $ibaScore = new \Application\Entity\IBAScore();
        }
        if ($read == '') {
            $read = NULL;
        }
        if ($listen == '') {
            $listen = NULL;
        }
        $ibaScore->setPupil($pupil);
        $ibaScore->setStatus('Active');
        $ibaScore->setExamDate($examDateString);
        $ibaScore->setReadingScore($read);
        $ibaScore->setListeningScore($listen);
        $ibaScore->setIBACSETotal((int) $read + (int) $listen);
        $ibaScore->setIBALevel($eikenLevel2);
        return $ibaScore;
    }

    public function setEntityPupil($data, $id, $upload = false) {

        static $orgschoolyears = array();
        static $classes = array();
        static $callTime = 0;

        if (++$callTime > 5000) {
            $orgschoolyears = array();
            $classes = array();
        }
        $em = $this->getEntityManager();
        $org = $em->getReference('Application\Entity\Organization', $this->id_org);
        if ($id != 0) {
            $pupil = $em->getRepository('Application\Entity\Pupil')->findOneBy(array(
                'id' => (int) $id,
                'isDelete' => 0
            ));
        }
        if (empty($pupil) || $id == 0) {
            $pupil = new \Application\Entity\Pupil();
        }
        if (empty($org)) {
            $org = NULL;
        }
        if ($upload == true) {
            $field = $this->getKeyOfFieldImport();
            //$pId = $data[0];
            $number = $data[$field['PupilNumber']];
            $year = $data[$field['Year']];
            $eikenId = $this->remove_special_characters(trim($data[$field['EikenId']]));
            $eikenPassword = $this->remove_special_characters(trim($data[$field['EikenPassword']]));
            $einaviId = $this->remove_special_characters(trim($data[$field['EinaviId']]));
            $fNKanji = $this->remove_special_characters(trim($data[$field['FirstnameKanji']]));
            $lNKanji = $this->remove_special_characters(trim($data[$field['LastnameKanji']]));
            $fNKana = $this->remove_special_characters(trim($data[$field['FirstnameKana']]));
            $lNKana = $this->remove_special_characters(trim($data[$field['LastnameKana']]));
            if (empty($data[$field['Birthday']])) {
                $birthTimeString = NULL;
            } else {
                $times = date('Y-m-d', strtotime($data[$field['Birthday']]));
                $birthTimeString = new \DateTime(date($times));
            }
            $gender = !empty($data[$field['Gender']]) ? ($data[$field['Gender']] == '女' ? 0 : 1) : -1;

            $orgChoolYearData = array(
                'displayName' => $data[$field['SchoolYear']],
                'organizationId' => $this->id_org,
                'isDelete' => 0
            );
            $key = md5(serialize($orgChoolYearData));
            if (array_key_exists($key, $orgschoolyears)) {
                $orgschoolyear = $orgschoolyears[$key];
            } else {
                $orgschoolyears[$key] = $orgschoolyear = $em->getRepository('Application\Entity\OrgSchoolYear')->findOneBy($orgChoolYearData);
            }
            if (empty($orgschoolyear->getId())) {
                $idsh = Null;
            } else {
                $idsh = $orgschoolyear->getId();
            }
            $classData = array(
                'className' => $data[$field['Class']],
                'organizationId' => $this->id_org,
                'orgSchoolYearId' => $idsh,
                'year' => $data[$field['Year']],
                'isDelete' => 0
            );
            $keyClass = md5(serialize($classData));
            if (array_key_exists($keyClass, $classes)) {
                $class = $classes[$keyClass];
            } else {
                $classes[$keyClass] = $class = $em->getRepository('Application\Entity\ClassJ')->findOneBy($classData);
            }
        } else {
            if (empty($data['birthDay']) || empty($data['birthMonth']) || empty($data['birthYear'])) {
                $birthTime = NULL;
            } else {
                $birthTime = (int) $data['birthDay'] . "-" . (int) $data['birthMonth'] . "-" . (int) $data['birthYear'];
            }
            if (empty($birthTime)) {
                $birthTimeString = NULL;
            } else {
                $birthTimeString = new \Datetime(date($birthTime));
            }

            $year = $data['year'];
            ///$pId = (isset($data['pupilId']))?$data['pupilId']:NULL;
            $number = (isset($data['Number'])) ? $data['Number'] : NULL;
            $gender = isset($data['gender']) && $data['gender'] !== '' ? $data['gender'] : -1;
            
            $eikenId = $this->remove_special_characters(trim($data['eikenId']));
            $eikenPassword = $this->remove_special_characters(trim($data['eikenPassword']));
            $einaviId = $this->remove_special_characters(trim($data['einaviId']));
            $fNKanji = $this->remove_special_characters(trim($data['firstNameKanji']));
            $lNKanji = $this->remove_special_characters(trim($data['lastNameKanji']));
            $fNKana = $this->remove_special_characters(trim($data['firstNameKana']));
            $lNKana = $this->remove_special_characters(trim($data['lastNameKana']));
            $orgschoolyear = $em->getReference('Application\Entity\OrgSchoolYear', array(
                'id' => (int) $data['orgSchoolYear'],
                'isDelete' => 0
            ));
            $class = $em->getReference('Application\Entity\ClassJ', array(
                'id' => (int) $data['classj'],
                'isDelete' => 0
            ));
            if (empty($orgschoolyear)) {
                $orgschoolyear = NULL;
            }
        }
//         if($pId==''){
//             $pId =Null;
//         }
        if ($number == '') {
            $number = Null;
        }
        //$pupil->setPupilID($pId);
        $pupil->setNumber($number);
        $pupil->setFirstNameKanji($fNKanji?$fNKanji:NULL);
        $pupil->setLastNameKanji($lNKanji?$lNKanji:NULL);
        $pupil->setFirstNameKana($fNKana?$fNKana:NULL);
        $pupil->setLastNameKana($lNKana?$lNKana:NULL);
        $pupil->setBirthday($birthTimeString);
        $pupil->setGender($gender);
        $pupil->setYear($year);
        $pupil->setOrgSchoolYear($orgschoolyear);
        $pupil->setClass($class);
        $pupil->setEinaviId($einaviId);
        $pupil->setEikenId($eikenId);
        $pupil->setEikenPassword($eikenPassword);
        $pupil->setOrganization($org);
        return $pupil;
    }

    // read and check file

    public function readfilecsv($File) {
        $response = array(
            'status' => 1,
            'data' => '',
            'error' => '',
            'scrfile' => $File,
        );
        $csv = CharsetConverter::shiftJisToUtf8(file_get_contents($File));
        if (empty($csv)) {
            $response['status'] = 2;
            return $response;
        }
        $data = \Dantai\Utility\CsvHelper::csvStrToArray($csv);
        if (!empty($data) && !empty($data[1])) {
            if ((count($data) > 5001)) {
                $response['status'] = 3;
                return $response;
            }
        } else {
            $response['status'] = 4;
            return $response;
        }
        unset($data[0]);
        if (empty($data)) {
            $response['status'] = 4;
            return $response;
        }
        $response['error'] = array();
        $field = $this->getKeyOfFieldImport();
        $data = $this->removeSpaceOfDataImport($data);
        foreach ($data as $key => $v) {
            if (count($v) != 26) {
                $response['status'] = 2;
                return $response;
            }
            $response['error'][$key] = array();
            $errors1 = $this->validatePupilYear($v[$field['Year']]);
            $errors2 = $this->validatePupilSchoolYear($v[$field['SchoolYear']]);
            $errors3 = $this->validatePupilClass($v[$field['Class']]);
            $errors4 = $this->validatePupilNumber($v[$field['PupilNumber']]);
            $errors5 = $this->validatePupilNameKanji($v[$field['FirstnameKanji']], $v[$field['LastnameKanji']]);
            $errors6 = $this->validatePupilNameKana($v[$field['FirstnameKana']], $v[$field['LastnameKana']]);
            $errors7 = $this->validatePupilBirthdayAndGender($v[$field['Birthday']], $v[$field['Gender']]);
            $errors8 = $this->validatePupilEnaviIdAndEikenIdAndPassword($v[$field['EinaviId']], $v[$field['EikenId']], $v[$field['EikenPassword']]);
            $errors9 = $this->validatePupilEikenLevelAndYearAndKai($v[$field['EikenLevel']], $v[$field['EikenYear']], $v[$field['Kai']]);
            $errors10 = $this->validatePupilEikenScore($v[$field['EikenScoreReading']], $v[$field['EikenScoreListening']], $v[$field['EikenScoreWriting']], $v[$field['EikenScoreSpeaking']]);
            $errors11 = $this->validatePupilIBA($v[$field['IBALevel']], $v[$field['IBADate']]);
            $errors12 = $this->validatePupilIBAScore($v[$field['IBAScoreReading']], $v[$field['IBAScoreListening']]);
            $errors13 = $this->validatePupilWordLevelAndGrammarLevel($v[$field['WordLevel']], $v[$field['GrammarLevel']]);

            /* get other data without current key to check duplicate pupilName of class */
            $dataImport = $data;
            unset($dataImport[$key]);
            $errors14 = $this->validatePupilDuplicate($v[$field['Year']], $v[$field['SchoolYear']], $v[$field['Class']], $v[$field['PupilNumber']], $dataImport);

            $errors = array_merge($errors1, $errors2, $errors3, $errors4, $errors5, $errors6, $errors7, $errors8, $errors9, $errors10, $errors11, $errors12, $errors13, $errors14);
            if ($errors) {
                foreach ($errors as $keyError => $error) {
                    $response['error'][$key][$keyError] = $error;
                }
            }
            // If has any error
            if (!empty($response['error'][$key])) {
                $response['dataeror'][$key] = $v;
            } else {
                unset($response['error'][$key]);
            }
        }

        if (!empty($response['dataeror'])) {
            $response['data'] = $response['dataeror'];
        } else {
            $response['error'] = false;
            $response['data'] = $data;
        }

        unset($response['dataeror']);
        return $response;
    }

    public function checkYearEiken($year) {
        if (!preg_match('/^[0-9]{4}$/', $year) || (int) $year < 2010 || (int) $year > (int) (date('Y') + 2)) {
            return true;
        } else {
            return false;
        }
    }

    public function validationEkienYear($year) {
        if (!preg_match('/^[0-9]{4}$/', $year) || !in_array($year, $this->year())) {
            return true;
        }
    }

    private function getValidationError() {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        return array(
            'required' => $translator->translate('requiredError'),
            'Isnumber' => $translator->translate('IsnumberError'),
            'IsnumberScore' => $translator->translate('IsnumberScoreError'),
            'kana' => $translator->translate('kanaError'),
            'birthDay' => $translator->translate('birthDayError'),
            'year' => $translator->translate('yearError'),
            'IsClass' => $translator->translate('classError'),
            'gender' => $translator->translate('genderError'),
            'ekienLeve' => $translator->translate('ekienLeveError'),
            'kanj' => $translator->translate('kanjError'),
            'kai' => $translator->translate('kaiError'),
            'equal2Years' => $translator->translate('equal2Years'),
            'birthdaySpace' => $translator->translate('birthdaySpace'),
            'birthdayNow' => $translator->translate('birthdayNow')
        );
    }

    function checkFullSize($namekanj) {
        if (preg_match("/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])|[\x20-\x7E]/", $namekanj)) {
            return false;
        }
        return true;
    }

    function checkGender($gender) {
        if ($gender === '男') {
            return false;
        } elseif ($gender === '女') {
            return false;
        } elseif ($gender == '') {
            return false;
        } else {
            return true;
        }
    }

    function is_int_val($value) {
        if ($value == (string) ((integer) $value)) {
            return true;
        } else {
            return false;
        }
    }

    function dupliCateArrayDB() {
        $dupliCateArray = array();
        $em = $this->getEntityManager();
        $classAndYear = $em->getRepository('Application\Entity\ClassJ')->getListClassByOrg($this->id_org);
        $classAndYearCount = count($classAndYear);
        for ($i = 0; $i < $classAndYearCount; $i++) {
            $dupliCateArray[] = trim($classAndYear[$i]['year']) . '|' . trim($classAndYear[$i]['schoolyearName']) . '|' . trim($classAndYear[$i]['className']);
        }
        return $dupliCateArray;
    }

    function isDate($date) {
        if (preg_match("/^\d{4}[\/]\d{1,2}[\/]\d{1,2}$/", $date)) {
            if (!empty($date)) {
                $arrayDate = explode('/', $date);
                $dtDay = $arrayDate[2];
                $dtMonth = $arrayDate[1];
                $dtYear = $arrayDate[0];
                if ($dtMonth < 1 || $dtMonth > 12) {
                    return true;
                } elseif ($dtDay < 1 || $dtDay > 31) {
                    return true;
                } elseif (($dtMonth == 4 || $dtMonth == 6 || $dtMonth == 9 || $dtMonth == 11) && $dtDay == 31) {
                    return true;
                } elseif ($dtMonth == 2) {
                    $check = ($dtYear % 4 == 0 && ($dtYear % 100 != 0 || $dtYear % 400 == 0));
                    if ($dtDay > 29 || ($dtDay == 29 && !$check)) {
                        return true;
                    }
                } else {
                    return false;
                }
            }
        } else {
            return true;
        }
    }

    function getData($flag = false) {
        $arrayCheck = array();
        $em = $this->getEntityManager();
        $objEikenLevel = $em->getRepository('Application\Entity\EikenLevel')->ListEikenLevel();
        foreach ($objEikenLevel as $k => $item) {
            if ($flag) {
                $arrayCheck[0] = '';
            }
            $arrayCheck[$item['id']] = $item['levelName'];
        }
        return $arrayCheck;
    }

    public function listshoolyear() {
        $em = $this->getEntityManager();
        $yearschool = array('' => '');
        $objSchoolyear = $em->getRepository('Application\Entity\OrgSchoolYear')->ListSchoolYear($this->id_org);
        if (isset($objSchoolyear) && $objSchoolyear) {
            foreach ($objSchoolyear as $key => $value) {
                $yearschool[$value['id']] = $value['displayName'];
            }
        }
        return $yearschool;
    }

    public function listclass($searchYear = false, $searchOrgSchoolYear = false, $flat = false) {
        $em = $this->getEntityManager();
        $listclass = array('' => '');
        if (!empty($searchYear) && !empty($searchOrgSchoolYear) && $flat == false) {
            $objClass = $em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYearAndYearAjax($searchYear, $searchOrgSchoolYear, $this->id_org);
        }
        if ($flat == true) {
            $objClass = $em->getRepository('Application\Entity\ClassJ')->getListClass($this->id_org);
        }
        if (!empty($objClass)) {
            foreach ($objClass as $key => $value) {
                $listclass[$value['id']] = $value['className'];
            }
        }
        return $listclass;
    }

    // end file
    public function remove_special_characters($string) {
        $string = str_replace(array(
            "'",
            '"'
                ), array(
            "",
            ""
                ), $string);
        return $string;
    }

    public function bYear() {
        $listyear = array();
        $y = (int) date('Y');
        $listyear[0] = '';
        for ($i = $y; $i >= $y - 100; $i --) {
            $listyear[$i] = $i;
        }
        return $listyear;
    }

    public function bMonth() {
        $listmonth = array();
        for ($i = 1; $i < 13; $i ++) {
            if ($i == 1) {
                $listmonth[0] = '';
            }
            $listmonth[$i] = $i;
        }
        return $listmonth;
    }

    public function bDay() {
        $listday = array();
        for ($i = 1; $i < 32; $i ++) {
            if ($i == 1) {
                $listday[''] = '';
            }
            $listday[$i] = $i;
        }
        return $listday;
    }

    public function year() {
        $listYear = array();
        $y = (int) 2009;
        $listYear[''] = '';
        for ($i = (int) date('Y') + 2; $i > $y; $i --) {
            $listYear[$i] = $i;
        }
        return $listYear;
    }

    public function eikenYear() {
        $listEikenYear = array();
        $y = (int) 2009;
        // $listEikenYear[0] = (int) date('Y');
        for ($i = (int) date('Y') + 2; $i > $y; $i --) {
            $listEikenYear[$i] = $i;
        }
        return $listEikenYear;
    }

    function getCookieExport($stringData = false) {
        
        if ($stringData && $stringData != '[]') {
            $data = explode(',', $stringData);
        } else {
            $data = array();
        }
        return $data;
    }
    function checkCountPupil() {
        $currentDate = date('Y-m-d');
        $year = DateHelper::getCurrentYear();
        $result = array();
        $msg = "現在の年度の";
        $flag = false;
        $em = $this->getEntityManager();
        $schoolYear = $em->getRepository('Application\Entity\OrgSchoolYear')->findOneBy(array('organizationId' => $this->id_org, 'isDelete' => 0));

        if ($schoolYear === NULL) {
            $flag = true;
            $msg = $msg . "学年";
        }
        $class = $em->getRepository('Application\Entity\ClassJ')->findOneBy(array('organizationId' => $this->id_org, 'year' => $year, 'isDelete' => 0));
        if ($class === NULL) {
            if ($flag) {
                $msg.="、";
            }
            $msg = $msg . "クラス";
            $flag = true;
        }
        $pupil = $em->getRepository('Application\Entity\Pupil')->findOneBy(array('organizationId' => $this->id_org, 'year' => $year, 'isDelete' => 0));
        if ($pupil === NULL) {
            if ($flag) {
                $msg.="、";
            }
            $msg = $msg . "生徒";
            $flag = true;
        }
        $result['error'] = $flag;
        $msg .= "が存在していません。続行しますか。";
        if ($flag === false) {
            $msg = "";
        }
        $result['message'] = $msg;
        return $result;
    }

    /**

     * 
     * @param int $year
     * @param int $kai
     * @param int $eikenLevelId    
     * @return Datetime $eikenDate
     */
    public function getExamDateByYearAndKaiAndEikenLevel($year, $kai, $eikenLevelId) {
        if ($year == Null || $kai == Null) {
            return Null;
        }
        $em = $this->getEntityManager();
        /* @var $eikenSchedule \Application\Entity\EikenSchedule */
        $eikenSchedule = $em->getRepository('Application\Entity\EikenSchedule')->findOneBy(array(
            'year' => $year,
            'kai' => $kai
        ));

        if ($eikenSchedule === Null) {
            return Null;
        }

        if (in_array($eikenLevelId, array(1, 2, 3, 4, 5))) {

            $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
            $dateRule = $dantaiService->getDateRound2EachKyu($eikenSchedule->getId());
            // 1級 -> 3級
            $eikenDate = new \DateTime("now");
            if(isset($dateRule[$eikenLevelId])){
                $eikenDate = $eikenSchedule->getRound2Day2ExamDate() != Null ? $eikenSchedule->getRound2Day2ExamDate() : $eikenDate;
                if($dateRule[$eikenLevelId] === 1){
                    $eikenDate = $eikenSchedule->getRound2Day1ExamDate() != Null ? $eikenSchedule->getRound2Day1ExamDate() : $eikenDate;
                }
            }

        } else {
            //4級 -> 5級
            if ($eikenSchedule->getSunDate() == Null && $eikenSchedule->getFriDate() == Null && $eikenSchedule->getSatDate() == Null) {
                $eikenDate = NULL;
            } else {
                $sunDate = $eikenSchedule->getSunDate() != Null ? $eikenSchedule->getSunDate()->format('Ymd') : 0;
                $friDate = $eikenSchedule->getFriDate() != Null ? $eikenSchedule->getFriDate()->format('Ymd') : 0;
                $satDate = $eikenSchedule->getSatDate() != Null ? $eikenSchedule->getSatDate()->format('Ymd') : 0;

                $arrWday = array();
                if ($sunDate > 0)
                    $arrWday[] = $sunDate;
                if ($friDate > 0)
                    $arrWday[] = $friDate;
                if ($satDate > 0)
                    $arrWday[] = $satDate;

                if (max($arrWday) == $sunDate) {
                    $eikenDate = $eikenSchedule->getSunDate();
                } else if (max($arrWday) == $friDate) {
                    $eikenDate = $eikenSchedule->getFriDate();
                } else {
                    $eikenDate = $eikenSchedule->getSatDate();
                }
            }
        }

        return $eikenDate;
    }

    /* begin validate pupil */

    public function translate($messageKey) {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        return $translator->translate($messageKey);
    }

    public function checkYearFormat($year) {
        if (preg_match('/^[0-9]{4}$/', $year) && intval($year) > 2010 && intval($year) <= (date('Y') + 2)) {
            return true;
        } else {
            return false;
        }
    }

    public function checkIsNumber($value) {
        return (is_numeric($value) && intval($value) >= 0);
    }

    public function checkIsNumberScore($value) {
        return (intval($value) >= 0 && intval($value) <= 999);
    }

    public function checkKatakana($value) {
        return preg_match('/^[\x{30A0}-\x{30FF}\x{FF5F}-\x{FF9F}\s]*$/u', $value);
    }

    public function checkDuplicatePupilNumberInFileImport($dataImport, $year, $schoolyear, $class, $pupilNumber) {
        $field = $this->getKeyOfFieldImport();
        $arrNumber = array();
        foreach ($dataImport as $import) {
            if (!empty($import[$field['Year']]) && !empty($import[$field['SchoolYear']]) && !empty($import[$field['Class']]) && !empty($import[$field['PupilNumber']])) {
                $arrNumber[] = $import[$field['Year']] . '|' . $import[$field['SchoolYear']] . '|' . $import[$field['Class']] . '|' . $import[$field['PupilNumber']];
            }
        }
        $pupilNumberOfClass = $year . '|' . $schoolyear . '|' . $class . '|' . $pupilNumber;
        return in_array($pupilNumberOfClass, $arrNumber);
    }

    public function checkDuplicatePupilNumber($year, $schoolyear, $class, $pupilNumber) {
        $arrNumber = array();
        $em = $this->getEntityManager();
        $pupils = $em->getRepository('Application\Entity\Pupil')->getListPupilOfClassByOrg($this->id_org);
        foreach ($pupils as $pupil) {
            $arrNumber[] = trim($pupil['year']) . '|' . trim($pupil['schoolyearName']) . '|' . trim($pupil['className']) . '|' . trim($pupil['number']);
        }
        $pupilNumberOfClass = $year . '|' . $schoolyear . '|' . $class . '|' . $pupilNumber;
        return (in_array($pupilNumberOfClass, $arrNumber));
    }

    public function getArrayClassOfSchoolYearByCurrentOrg() {
        $classOfSchoolYear = array();
        $em = $this->getEntityManager();
        $classes = $em->getRepository('Application\Entity\ClassJ')->getListClassByOrg($this->id_org);
        foreach ($classes as $class) {
            $classOfSchoolYear[] = trim($class['year']) . '|' . trim($class['schoolyearName']) . '|' . trim($class['className']);
        }

        return $classOfSchoolYear;
    }

    function getArrayEikenLevel() {
        $arrEikenLevel = array();
        $em = $this->getEntityManager();
        $eikenLevels = $em->getRepository('Application\Entity\EikenLevel')->ListEikenLevel();
        foreach ($eikenLevels as $eikenLevel) {
            $arrEikenLevel[$eikenLevel['id']] = $eikenLevel['levelName'];
        }
        return $arrEikenLevel;
    }

    function checkDateFormat($date) {
        if (preg_match("/^\d{4}[\/]\d{1,2}[\/]\d{1,2}$/", $date)) {
            if (!empty($date)) {
                $arrayDate = explode('/', $date);
                $dtDay = $arrayDate[2];
                $dtMonth = $arrayDate[1];
                $dtYear = $arrayDate[0];
                if ($dtMonth < 1 || $dtMonth > 12) {
                    return false;
                } elseif ($dtDay < 1 || $dtDay > 31) {
                    return false;
                } elseif (($dtMonth == 4 || $dtMonth == 6 || $dtMonth == 9 || $dtMonth == 11) && $dtDay == 31) {
                    return false;
                } elseif ($dtMonth == 2) {
                    $check = ($dtYear % 4 == 0 && ($dtYear % 100 != 0 || $dtYear % 400 == 0));
                    if ($dtDay > 29 || ($dtDay == 29 && !$check)) {
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    public function validatePupilYear($year) {
        $error = array();
        if ($year === '') {
            $error['year']['title'] = $this->translate('MsgYearError1');
            $error['year']['field'] = $this->translate('ImportYear');
        } else {
            if (!$this->checkYearFormat($year)) {
                $error['year']['title'] = $this->translate('MsgYearError2');
                $error['year']['field'] = $this->translate('ImportYear');
            }
        }

        return $error;
    }

    public function validatePupilSchoolYear($schoolYear) {
        $error = array();
        if ($schoolYear === '') {
            $error['schoolYear']['title'] = $this->translate('MsgSchoolYearError1');
            $error['schoolYear']['field'] = $this->translate('ImportSchoolYear');
        }
        return $error;
    }

    public function validatePupilClass($class) {
        $error = array();
        if ($class === '') {
            $error['class']['title'] = $this->translate('MsgClassError1');
            $error['class']['field'] = $this->translate('ImportClass');
        }
        return $error;
    }

    public function validatePupilNumber($pupilNumber) {
        $error = array();
        if ($pupilNumber === '') {
            $error['pupilNumber']['title'] = $this->translate('MsgPupilNumberError1');
            $error['pupilNumber']['field'] = $this->translate('ImportPupilNumber');
        } else {
            if (!$this->checkIsNumber($pupilNumber)) {
                $error['pupilNumber']['title'] = $this->translate('MsgPupilNumberError2');
                $error['pupilNumber']['field'] = $this->translate('ImportPupilNumber');
            }
        }

        return $error;
    }

    public function validatePupilNameKanji($firstName, $lastName) {
        $error = array();
        if (empty($firstName)) {
            $error['firstnameKanji']['title'] = $this->translate('MsgFirstnameKanjiError1');
            $error['firstnameKanji']['field'] = $this->translate('ImportFirstnameKanji');
        }

        if (empty($lastName)) {
            $error['lastnameKanji']['title'] = $this->translate('MsgLastnameKanjiError1');
            $error['lastnameKanji']['field'] = $this->translate('ImportLastnameKanji');
        }
        if (!empty($firstName) && !empty($lastName)) {
            $nameKanji = $firstName . $lastName;
            if (!$this->checkFullSize($nameKanji) || mb_strlen($nameKanji, 'utf-8') > 10) {
                $error['nameKanji']['title'] = $this->translate('MsgNameKanjiError1');
                $error['nameKanji']['field'] = $this->translate('ImportNameKanji');
            }
        }
        return $error;
    }

    public function validatePupilNameKana($firstName, $lastName) {
        $error = array();
        if (empty($firstName)) {
            $error['firstnameKana']['title'] = $this->translate('MsgFirstnameKanaError1');
            $error['firstnameKana']['field'] = $this->translate('ImportFirstnameKana');
        } else {
            if (!$this->checkKatakana($firstName)) {
                $error['firstnameKana']['title'] = $this->translate('MsgFirstnameKanaError2');
                $error['firstnameKana']['field'] = $this->translate('ImportFirstnameKana');
            }
        }

        if (empty($lastName)) {
            $error['lastnameKana']['title'] = $this->translate('MsgLastnameKanaError1');
            $error['lastnameKana']['field'] = $this->translate('ImportLastnameKana');
        } else {
            if (!$this->checkKatakana($lastName)) {
                $error['lastnameKana']['title'] = $this->translate('MsgLastnameKanaError2');
                $error['lastnameKana']['field'] = $this->translate('ImportLastnameKana');
            }
        }
        return $error;
    }

    public function validatePupilBirthdayAndGender($birthday, $gender) {
        $error = array();
        if (empty($birthday)) {
            $error['birthday']['title'] = $this->translate('MsgBirthdayError1');
            $error['birthday']['field'] = $this->translate('ImportBirthday');
        } else {
            $year = date('Y', strtotime($birthday));
            $intDate = date('Ymd', strtotime($birthday));
            if (!$this->checkDateFormat($birthday)) {
                $error['birthday']['title'] = $this->translate('MsgBirthdayError2');
                $error['birthday']['field'] = $this->translate('ImportBirthday');
            } else if ($year < 1916) {
                $error['birthday']['title'] = $this->translate('MsgBirthdayError3');
                $error['birthday']['field'] = $this->translate('ImportBirthday');
            } else if ($intDate > date('Ymd')) {
                $error['birthday']['title'] = $this->translate('MsgBirthdayError4');
                $error['birthday']['field'] = $this->translate('ImportBirthday');
            }
        }

        if (!empty($gender)) {
            if (!in_array($gender, array('男', '女'))) {
                $error['gender']['title'] = $this->translate('MsgGenderError1');
                $error['gender']['field'] = $this->translate('ImportGender');
            }
        }
        return $error;
    }

    public function validatePupilEnaviIdAndEikenIdAndPassword($enaviId, $eikenId, $password) {
        $error = array();

        if ($enaviId !== '') {
            if (!preg_match('/^[0-9]*$/', $enaviId) || mb_strlen($enaviId, 'utf-8') != 10) {
                $error['enaviId']['title'] = $this->translate('MsgEinaviIdError1');
                $error['enaviId']['field'] = $this->translate('ImportEinaviId');
            }
        }

        if ($eikenId !== '') {
            if (!preg_match('/^[0-9]*$/', $eikenId) || mb_strlen($eikenId, 'utf-8') != 11) {
                $error['eikenId']['title'] = $this->translate('MsgEikenIdError1');
                $error['eikenId']['field'] = $this->translate('ImportEikenId');
            }
        }

        if ($password !== '') {
            if (!preg_match('/^[a-zA-Z0-9]*$/', $password) || mb_strlen($password, 'utf-8') > 6 || mb_strlen($password, 'utf-8') < 4) {
                $error['eikenPassword']['title'] = $this->translate('MsgEikenPasswordError1');
                $error['eikenPassword']['field'] = $this->translate('ImportEikenPassword');
            }
        }
        return $error;
    }

    public function validatePupilEikenLevelAndYearAndKai($eikenLevel, $eikenYear, $kai) {
        $error = array();
        $arrEikenLevel = $this->getArrayEikenLevel();
        if ($eikenLevel !== '') {
            if (!in_array($eikenLevel, $arrEikenLevel)) {
                $error['eikenLevel']['title'] = $this->translate('MsgEikenLevelError1');
                $error['eikenLevel']['field'] = $this->translate('ImportEikenLevel');
            }

            if ($eikenYear === '') {
                $error['eikenYear']['title'] = $this->translate('MsgEikenYearError1');
                $error['eikenYear']['field'] = $this->translate('ImportEikenYear');
            } else {
                if (!$this->checkYearFormat($eikenYear)) {
                    $error['eikenYear']['title'] = $this->translate('MsgEikenYearError2');
                    $error['eikenYear']['field'] = $this->translate('ImportEikenYear');
                }
            }

            if ($kai === '') {
                $error['kai']['title'] = $this->translate('MsgEikenKaiError1');
                $error['kai']['field'] = $this->translate('ImportKai');
            } else {
                if ($kai > 3 || $kai < 1) {
                    $error['kai']['title'] = $this->translate('MsgEikenKaiError2');
                    $error['kai']['field'] = $this->translate('ImportKai');
                }
            }
        }
        return $error;
    }

    public function validatePupilEikenScore($reading, $listening, $writing, $speaking) {
        $error = array();
        if ($reading !== '') {
            if (!$this->checkIsNumber($reading)) {
                $error['eikenScoreReading']['title'] = $this->translate('MsgEikenScoreReadingError1');
                $error['eikenScoreReading']['field'] = $this->translate('ImportEikenScoreReading');
            } else if (!$this->checkIsNumberScore($reading)) {
                $error['eikenScoreReading']['title'] = $this->translate('MsgEikenScoreReadingError2');
                $error['eikenScoreReading']['field'] = $this->translate('ImportEikenScoreReading');
            }
        }

        if ($listening !== '') {
            if (!$this->checkIsNumber($listening)) {
                $error['eikenScoreListening']['title'] = $this->translate('MsgEikenScoreListeningError1');
                $error['eikenScoreListening']['field'] = $this->translate('ImportEikenScoreListening');
            } else if (!$this->checkIsNumberScore($listening)) {
                $error['eikenScoreListening']['title'] = $this->translate('MsgEikenScoreListeningError2');
                $error['eikenScoreListening']['field'] = $this->translate('ImportEikenScoreListening');
            }
        }

        if ($writing !== '') {
            if (!$this->checkIsNumber($writing)) {
                $error['eikenScoreWriting']['title'] = $this->translate('MsgEikenScoreWritingError1');
                $error['eikenScoreWriting']['field'] = $this->translate('ImportEikenScoreWriting');
            } else if (!$this->checkIsNumberScore($writing)) {
                $error['eikenScoreWriting']['title'] = $this->translate('MsgEikenScoreWritingError2');
                $error['eikenScoreWriting']['field'] = $this->translate('ImportEikenScoreWriting');
            }
        }

        if ($speaking !== '') {
            if (!$this->checkIsNumber($speaking)) {
                $error['eikenScoreSpeaking']['title'] = $this->translate('MsgEikenScoreSpeakingError1');
                $error['eikenScoreSpeaking']['field'] = $this->translate('ImportEikenScoreSpeaking');
            } else if (!$this->checkIsNumberScore($speaking)) {
                $error['eikenScoreSpeaking']['title'] = $this->translate('MsgEikenScoreSpeakingError2');
                $error['eikenScoreSpeaking']['field'] = $this->translate('ImportEikenScoreSpeaking');
            }
        }

        return $error;
    }

    public function validatePupilIBA($ibaLevel, $ibaDate) {
        $error = array();
        $arrEikenLevel = $this->getArrayEikenLevel();
        if ($ibaLevel !== '') {
            if (!in_array($ibaLevel, $arrEikenLevel)) {
                $error['ibaLevel']['title'] = $this->translate('MsgIBALevelError1');
                $error['ibaLevel']['field'] = $this->translate('ImportIBALevel');
            }

            if ($ibaDate === '') {
                $error['ibaDate']['title'] = $this->translate('MsgIBADateError1');
                $error['ibaDate']['field'] = $this->translate('ImportIBADate');
            } else {
                if (!$this->checkDateFormat($ibaDate)) {
                    $error['ibaDate']['title'] = $this->translate('MsgIBADateError2');
                    $error['ibaDate']['field'] = $this->translate('ImportIBADate');
                }
            }
        }
        return $error;
    }

    public function validatePupilIBAScore($reading, $listening) {
        $error = array();

        if ($reading !== '') {
            if (!$this->checkIsNumber($reading)) {
                $error['ibaScoreReading']['title'] = $this->translate('MsgIBAScoreReadingError1');
                $error['ibaScoreReading']['field'] = $this->translate('ImportIBAScoreReading');
            } else if (!$this->checkIsNumberScore($reading)) {
                $error['ibaScoreReading']['title'] = $this->translate('MsgIBAScoreReadingError2');
                $error['ibaScoreReading']['field'] = $this->translate('ImportIBAScoreReading');
            }
        }

        if ($listening !== '') {
            if (!$this->checkIsNumber($listening)) {
                $error['ibaScoreListening']['title'] = $this->translate('MsgIBAScoreListeningError1');
                $error['ibaScoreListening']['field'] = $this->translate('ImportIBAScoreListening');
            } else if (!$this->checkIsNumberScore($listening)) {
                $error['ibaScoreListening']['title'] = $this->translate('MsgIBAScoreListeningError2');
                $error['ibaScoreListening']['field'] = $this->translate('ImportIBAScoreListening');
            }
        }

        return $error;
    }

    public function validatePupilWordLevelAndGrammarLevel($wordLevel, $grammarLevel) {
        $error = array();
        $arrEikenLevel = $this->getArrayEikenLevel();
        if ($wordLevel !== '') {
            if (!in_array($wordLevel, $arrEikenLevel)) {
                $error['wordLevel']['title'] = $this->translate('MsgWordLevelError1');
                $error['wordLevel']['field'] = $this->translate('ImportWordLevel');
            }
        }

        if ($grammarLevel !== '') {
            if (!in_array($grammarLevel, $arrEikenLevel)) {
                $error['grammarLevel']['title'] = $this->translate('MsgGrammarLevelError1');
                $error['grammarLevel']['field'] = $this->translate('ImportGrammarLevel');
            }
        }
        return $error;
    }

    public function validatePupilDuplicate($year, $schoolYear, $class, $pupilNumber, $dataImport = false) {
        $error = array();
        if (!empty($year) && !empty($schoolYear) && !empty($class)) {
            $arrClassOfSchoolYear = $this->getArrayClassOfSchoolYearByCurrentOrg();
            $classOfSchoolYear = $year . '|' . $schoolYear . '|' . $class;
            if (!in_array($classOfSchoolYear, $arrClassOfSchoolYear)) {
                $error['class']['title'] = $this->translate('MsgClassOfSchoolYearError1');
                $error['class']['field'] = $this->translate('ImportExistClass');
            }

            if ($pupilNumber !== '') {
                if ($dataImport != false) {
                    $duplicateFile = $this->checkDuplicatePupilNumberInFileImport($dataImport, $year, $schoolYear, $class, $pupilNumber);
                    $duplicateDb = $this->checkDuplicatePupilNumber($year, $schoolYear, $class, $pupilNumber);
                    if ($duplicateFile || $duplicateDb) {
                        $error['class']['title'] = $this->translate('MsgDuplicatePupilError1');
                        $error['class']['field'] = $this->translate('ImportDuplicatePupilNumber');
                    }
                } else {
                    $duplicateDb = $this->checkDuplicatePupilNumber($year, $schoolYear, $class, $pupilNumber);
                    if ($duplicateDb) {
                        $error['class']['title'] = $this->translate('MsgDuplicatePupilError1');
                        $error['class']['field'] = $this->translate('ImportDuplicatePupilNumber');
                    }
                }
            }
        }
        return $error;
    }

    public function checkDuplicateNumberOfPupil($year, $classId, $orgSchoolYearId, $pupilNumber, $pupilId) {
        $em = $this->getEntityManager();
        $pupil = $em->getRepository('Application\Entity\Pupil')
                ->getPupilNumberExistInClassByOrg($this->id_org, $year, $classId, $orgSchoolYearId, $pupilNumber, $pupilId);
        return ($pupil === NULL) ? false : true;
    }

    public function removeSpaceOfDataImport($data) {      
        foreach ($data as $line => $import) {
            foreach ($import as $columnKey => $columnValue) {
                $data[$line][$columnKey] = trim($data[$line][$columnKey]);
            }
        }
        return $data;
    }
    
    public function getAjaxListClassName($params, $Response) {
        $em = $this->getEntityManager();
        $year = $params->fromPost('year', null);
        $schoolyear = $params->fromPost('schoolyear', null);
        $string = array();
        if (isset($schoolyear) && $schoolyear) {
            $objClass = $em->getRepository('Application\Entity\ClassJ')->getListClassNameAjax($year, $schoolyear, $this->id_org);
            if (!empty($objClass)) {
                foreach ($objClass as $key => $value) {
                    $string['classj'][$key] = $value;
                }
            }
        } else {
            $string['classj'] = '';
        }
        return $Response->setContent(Json::encode($string));
    }
    public function listSearchClass($searchYear = false, $searchOrgSchoolYear = false) {
        $em = $this->getEntityManager();
        $listclass = array('' => '');
    if (!empty($searchYear) || !empty($searchOrgSchoolYear)) {
            $objClass = $em->getRepository('Application\Entity\ClassJ')->getListClassNameAjax($searchYear, $searchOrgSchoolYear, $this->id_org);
        }
        if (!empty($objClass)) {
            foreach ($objClass as $key => $value) {
                $listclass[$value['className']] = $value['className'];
            }
        }
        return $listclass;
    }
    
    /* end validate pupil */
    
    protected $pupilMock;
    public function setPupilRepository($pupil = null){
        $this->pupilMock = $pupil ? $pupil : $this->getEntityManager()->getRepository('Application\Entity\Pupil');
    }
    public function getAjaxCheckDuplicatePupil($isPost, $params, $em =false) {
        $em = $em ? $em : $this->getEntityManager();
        $results = array(
            'status' => 0, 'data' => array()
        );
        if ($isPost) {
//            $params = $request->getPost();
            $listDuplicate = '';
            $firstNameKanji = trim($params['firstNameKanji']) ? trim($params['firstNameKanji']) : '';
            $lastNameKanji = trim($params['lastNameKanji']) ? trim($params['lastNameKanji']) : '';
            $firstNameKana = trim($params['firstNameKana']) ? trim($params['firstNameKana']) : '';
            $lastNameKana = trim($params['lastNameKana']) ? trim($params['lastNameKana']) : '';
            $birthDay = '';
            if($params['birthYear'] && $params['birthMonth'] && $params['birthDate']){
                $birthDay = new \DateTime($params['birthYear'].'-'.$params['birthMonth'].'-'.$params['birthDate']);
                $birthDay = $birthDay->format('Y-m-d H:i:s');
            }
            $year = ($params['year'])?$params['year']:'';
            if(!$this->pupilMock){
                $this->setPupilRepository();
            }
            $listDuplicate = $this->pupilMock->checkDuplicatePupil($this->id_org,$firstNameKanji,$lastNameKanji,$firstNameKana,$lastNameKana,$birthDay,$year);
            if(!empty($listDuplicate)){
                $results['status'] = 1;
                $results['data'] = $listDuplicate;
            }
        }
        return $results;
    }

    public function checkPupilApplyEikenOrPaidBefore($listPupilId)
    {
        $this->em = $this->getEntityManager();

        /** @var ApplyEikenLevelRepository $applyEikenLevelRepo */
        $applyEikenLevelRepo = $this->em->getRepository('Application\Entity\ApplyEikenLevel');

        return $applyEikenLevelRepo->getListApplyEikenLevelByListPupidIds($listPupilId);

    }
    
    public function getLisIdPupilCanNotDelete($data)
    {
        
        $data = $data->fromPost('pupilIds') ? explode(',',$data->fromPost('pupilIds')) : array();
        if(empty($this->em)){
            $this->em = $this->getEntityManager();
        }
        
        $currentEikenSchId = $this->getCurrentEikenSchedule() ? $this->getCurrentEikenSchedule()['id'] : '';
        $year = $this->getCurrentEikenSchedule() ? $this->getCurrentEikenSchedule()['year'] : '';
        if(empty($currentEikenSchId)){ return array(); }
        
        $pupils = array();
        $processLogEC = $this->em->getRepository('Application\Entity\ProcessLog')->findBy(array(
                'orgId' => $this->id_org,
                'scheduleId' => $currentEikenSchId
            ), array('id' => 'DESC'));
        
        if($processLogEC){
            $pupils = $this->getListIdPupilByYear($year);
        }else{
            $pupils = $this->getListIdPupilRenECByEikenSchedule($currentEikenSchId);
        }
        $pupilIds = array();
        if(empty($data)){
            return array(
                    'success' => 1,
                    'pupilIds' => $pupilIds,
                    'status' => 0
                );
        }
        if(empty($pupils)){
            return array(
                    'success' => 1,
                    'pupilIds' => $pupilIds,
                    'status' => 1
                );
        }
        if($data && $pupils){
            foreach ($data as $key => $value){
                foreach ($pupils as $k => $v){
                    if(intval($value) == intval($v)){
                        array_push($pupilIds,$v);
                    }
                }
            }
        }
        return array(
            'sucess' => 1,
            'pupilIds' => $pupilIds,
            'status' => !empty($pupilIds) ? 3 : 1
        );
    }
    
    public function getCurrentEikenSchedule(){
        if(empty($this->em)){
            $this->em = $this->getEntityManager();
        }
        
        return  $this->em->getRepository('Application\Entity\EikenSchedule')->getCurrentEikenSchedule();
    }
    
    public function getListIdPupilByYear($year){
        if(empty($this->em)){
            $this->em = $this->getEntityManager();
        }
        if(empty($year)){ return array(); }
        $pupils = $this->em->getRepository('Application\Entity\Pupil')->findBy(array(
                'organizationId' => $this->id_org,
                'year' => $year,
                'isDelete' => 0
            ));
        $pupilId = array();
        if($pupils){
            foreach ($pupils as $key => $value){
                array_push($pupilId, $value->getId());
            }
        }
        
        return  $pupilId;
    }
    
    public function getListIdPupilRenECByEikenSchedule($currentEikenSchId){
        if(empty($this->em)){
            $this->em = $this->getEntityManager();
        }
        if(empty($currentEikenSchId)){ return array(); }
        
        $invi = $this->em->getRepository('Application\Entity\InvitationSetting')->findOneBy(array(
            'organizationId' => $this->id_org,
            'eikenScheduleId' => $currentEikenSchId,
            'isDelete' => 0
        ));
        
        if(empty($invi)){ return array(); }
        
        $inviLetter = $this->em->getRepository('Application\Entity\InvitationLetter')->findBy(array(
                'invitationSettingId' => $invi->getId(),
                'isDelete' => 0
            ));
        
        $pupilId = array();
        if($inviLetter){
            foreach ($inviLetter as $key => $value){
                array_push($pupilId, $value->getPupilId());
            }
        }
        
        return  $pupilId;
    }
}