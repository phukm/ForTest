<?php
namespace Satellite\Service;

use Satellite\Service\ServiceInterface\EinaviServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Application\Entity\ApplyEikenPersonalInfo;
use Zend\Session\Container as SessionContainer;
use Zend\Json\Json;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Satellite\Form\RegisterForm;
use Satellite\Form\LoginEinaviForm;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;
use Zend\Validator;
use Satellite\Form\RegisterModel;
use Satellite\Form\LoginEinaviModel;
use Satellite\Form\LoginForm;
use Dantai\Api\EinaviClient;
use Satellite\Constants;
use Dantai\PrivateSession;

class EinaviService implements EinaviServiceInterface, ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;

    /**
     *
     * @return array|object
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    private static function gengo($value)
    {
        // tinh nam shouwa ex: value - 1925
        if ($value >= 1925 && $value <= 1989) {
            $no = $value - 1925;

            return $value . "(昭和 " . $no . ")";
        }
        // tinh nam heisei
        if ($value >= 1989) {
            $no = $value - 1988;

            return $value . "(平成 " . $no . ")";
        }

        return $value;
    }

    public function registerEinavi()
    {
        $form = new RegisterForm ();
        // year
        $curYear = (int)date("Y");
        $listyear = array('' => '');
        for ($i = $curYear; $i >= $curYear - 30; $i--) {
            $listyear [$i] = $this->gengo($i);
        }
        $form->get("ddlYear")->setValueOptions($listyear);
        $jsMessages = $this->getMessages();
        $result = array(
            'form'       => $form,
            'jsMessages' => Json::encode($jsMessages)
        );

        return $result;
    }

    // login service
    public function loginEinavi()
    {
        $form = new LoginEinaviForm ();

        return array('form' => $form, 'jsMessages' => Json::encode($this->getMessages()));
    }

    // return object register if valid
    public function validateInfo()
    {
        $app = $this->getServiceLocator()->get('Application');
        $request = $app->getRequest();
        // validate on server
        $inputFilter = new InputFilter ();
        // email
        $email = new Input('txtMailAdd');
        $email->getValidatorChain()->attach(new Validator\EmailAddress());
        // password
        $password = new Input('txtPassword');
        $password->getValidatorChain()->attach(new Validator\StringLength((array(
            'min' => 6,
            'max' => 32
        ))));
        // password2
        $password2 = new Input('txtPassword2');
        $password2->getValidatorChain()->attach(new Validator\StringLength((array(
            'min' => 6,
            'max' => 32
        ))));
        // firstname
        $firstname = new Input('txtFirstName');
        $firstname->getValidatorChain()->attach(new Validator\StringLength((array(
            'min' => 1,
            'max' => 18
        ))));
        // lastname
        $lastname = new Input('txtLastName');
        $lastname->getValidatorChain()->attach(new Validator\StringLength((array(
            'min' => 1,
            'max' => 18
        ))));
        // TODO SEX
        // year
        $year = new Input('ddlYear');
        $year->getValidatorChain()->attach(new Validator\Digits());
        // month
        $month = new Input('ddlMonth');
        $month->getValidatorChain()->attach(new Validator\Digits());
        // day
        $day = new Input('ddlDay');
        $day->getValidatorChain()->attach(new Validator\Digits());
        // postcode1
        $postcode1 = new Input('txtPostalCode1');
        $postcode1->getValidatorChain()->attach(new Validator\StringLength(3));
        // postcode2
        $postcode2 = new Input('txtPostalCode2');
        $postcode2->getValidatorChain()->attach(new Validator\StringLength(4));
        // agree
        //        $agree = new Input('chkAgree');
        //        $agree->getValidatorChain()->attach(new Validator\NotEmpty());
        $inputFilter->add($email)->add($password)->add($password2)->add($firstname)->add($lastname)->add($year)->add($month)->add($day)->add($postcode1)->add($postcode2)->setData($request->getPost());

        // valid => set to class
        return $inputFilter->isValid();
    }

    // return object login if valid
    public function validateLoginInfo()
    {
        $app = $this->getServiceLocator()->get('Application');
        $request = $app->getRequest();
        // validate on server
        $inputFilter = new InputFilter ();
        // email
        $email = new Input('txtMailAdd');
        $email->getValidatorChain()->attach(new Validator\StringLength((array(
            'min' => 1,
            'max' => 256
        ))));
        // password
        $password = new Input('txtPassword');
        $password->getValidatorChain()->attach(new Validator\StringLength((array(
            'min' => 1,
            'max' => 32
        ))));
        $inputFilter->add($email)->add($password)->setData($request->getPost());
        // valid => set to class
        if ($inputFilter->isValid()) {
            // create model
            $loginEinaviModel = new LoginEinaviModel ();
            $loginEinaviModel->setEmailaddress($email->getValue());
            $loginEinaviModel->setPassword($password->getValue());

            return $loginEinaviModel;
        }

        return null;
    }

    public function submitLoginInfo($agent)
    {
        $app = $this->getServiceLocator()->get('Application');
        $request = $app->getRequest();
        if (!$request->isPost()) {
            return Json::encode(array('msg' => $this->getMessages()['MsgAnError'], 'status' => false));
        }
        $login = $this->validateLoginInfo();
        if ($login) {
            $user = $login->getEmailaddress();
            $pass = $login->getPassword();
            $resultAuthen = $this->userAuthentication($user, $pass, $agent);
            $resultLoginEinavi = $resultAuthen->bkeapi;
            if ($resultAuthen && $resultLoginEinavi->result == 1) {
                $personalId = $resultLoginEinavi->personal_id;
                $userSession = PrivateSession::getData(Constants::SESSION_SATELLITE);
                if (!empty($userSession['pupilId'])) {
                    $this->savePupilInfor($personalId, $userSession['pupilId']);
                    $this->saveEinaviInfo($personalId, $userSession['pupilId']);
                }

                return Json::encode(array('msg' => $this->getMessages()['MsgLoginSucess'], 'status' => true));
            }
            else if ($resultLoginEinavi->auth_result == 'BKE_0006') {
                return Json::encode(array('msg' => $this->getMessages()['MsgInvalidUserOrPassword'], 'status' => false));
            }

            return Json::encode(array('msg' => $this->getMessages()['MsgAnError'], 'status' => false));
        }

        return Json::encode(array('msg' => $this->getMessages()['MsgAnError'], 'status' => false));
    }

    public function submitRegisterInfo($agent)
    {
        $app = $this->getServiceLocator()->get('Application');
        $request = $app->getRequest();
        if (!$request->isPost()) {
            return Json::encode(array('msg' => $this->getMessages()['MsgAnError'], 'status' => false));
        }
        if ($this->validateInfo()) {
            $data = $request->getPost();
            $resultCheckUserId = $this->CheckUserId($data, $agent);
            $resultCheckExist = $resultCheckUserId->bkeapi->result;
            if ($resultCheckExist == 1) {
                $resultSetUserData = $this->setUserData($data, $agent);
                $resultRegister = $resultSetUserData->bkeapi->result;
                $responseMessageRegister = $resultSetUserData->bkeapi->message;
                if ($resultRegister == 1) {
                    $personalId = $resultSetUserData->bkeapi->personal_id;
                    $userSession = PrivateSession::getData(Constants::SESSION_SATELLITE);
                    if (!empty($userSession['pupilId'])) {
                        $this->savePupilInfor($personalId, $userSession['pupilId']);
                        $this->saveEinaviInfo($personalId, $userSession['pupilId']);
                    }

                    return Json::encode(array('msg' => $this->getMessages()['MsgRegisterSuccess'], 'status' => true));
                }
                elseif ($resultRegister == 'BKE_0005') {
                    //pass have !@$@%#$^%*^&)(&)
                    return Json::encode(array('msg' => explode(")", $responseMessageRegister)[1], 'status' => false));
                }

                return Json::encode(array('msg' => $this->getMessages()['MsgAnError'], 'status' => false));
            }
            elseif ($resultCheckExist == "BKE_0005") {
                return Json::encode(array('msg' => $this->getMessages()['MsgEmailIsAlready'], 'status' => false));
            }

            return Json::encode(array('msg' => $this->getMessages()['MsgAnError'], 'status' => false));
        }

        return false;
    }

    private function getMessages()
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsMessages = array(
            'MsgInvalidEmail'            => $translator->translate('MsgInvalidEmail'),
            'MsgInvalidUserOrPassword'   => $translator->translate('MsgInvalidUserOrPassword'),
            'MsgRequired3'               => $translator->translate('MsgRequired3'),
            'MsgInvalidPassword'         => $translator->translate('MsgInvalidPassword'),
            'MsgRequired4'               => $translator->translate('MsgRequired4'),
            'MsgRequiredNumber'          => $translator->translate('MsgRequiredNumber'),
            'MsgPasswordConfirmNotMatch' => $translator->translate('MsgPasswordConfirmNotMatch'),
            'MsgLoginSucess'             => $translator->translate('MsgLoginSucess'),
            'MsgAnError'                 => $translator->translate('MsgAnError'),
            'MsgRegisterSuccess'         => $translator->translate('MsgRegisterSuccess'),
            'MsgEmailIsAlready'          => $translator->translate('MsgEmailIsAlready'),
            'InvalidBirthday'            => $translator->translate('InvalidBirthday'),
            'SystemError'                => $translator->translate('SystemError'),
            'NoResultFound'              => $translator->translate('NoResultFound'),
            'addressredirect'            => $this->getServiceLocator()->get('Config')['satellite_config']['api_userauthen']['addressredirect'],
        );

        return $jsMessages;
    }

    /*
     * This function allows pupils to log into Ei-navi system
     * redirect home page of HonePage Einavi
     */
    protected function userAuthentication($username, $password, $agent)
    {
        $config = $this->getServiceLocator()->get('Config')['satellite_config']['api_userauthen'];
        $apiParams = array(
            'bkeapi'            => array(
                'proc_day'       => date("YmdHis"),
                'login_kind'     => '1', // 22
                'login_string'   => trim($username),
                'login_password' => $password
            ),
            'client_user_agent' => $agent->HTTP_USER_AGENT,
            'client_ip_address' => $agent->REMOTE_ADDR
        );
        try {
            $result = EinaviClient::getInstance()->callCheckLoginUser($config, $apiParams);
            //Todo get callCheckBasicCorpon
            if ($result->bkeapi->result == 1) {
                $this->checkBasicCoupon($result, $agent);
            }
        }
        catch (Exception $e) {
            return false;
        }

        return $result;
    }

    protected function checkBasicCoupon($resultLogin, $agent)
    {
        $config = $this->getServiceLocator()->get('Config')['satellite_config']['api_userauthen'];
        $apiParams = array(
            'bkeapi'            => array(
                'proc_day'    => date("YmdHis"),
                'personal_id' => $resultLogin->bkeapi->personal_id,
                'attestation' => $resultLogin->bkeapi->attestation
            ),
            'client_user_agent' => $agent->HTTP_USER_AGENT,
            'client_ip_address' => $agent->REMOTE_ADDR
        );
        try {
            $result = EinaviClient::getInstance()->callCheckBasicCoupon($config, $apiParams);
            if ($result->bkeapi->result == 1 && $result->bkeapi->check_result != 1) {
                $this->groupGetBasicTicketInfo($resultLogin, $agent);
            }
        }
        catch (Exception $e) {
            return false;
        }

        return true;
    }

    protected function groupGetBasicTicketInfo($resultLogin, $agent)
    {
        $userSession = PrivateSession::getData(Constants::SESSION_SATELLITE);
        $config = $this->getServiceLocator()->get('Config')['satellite_config']['api_userauthen'];
        $apiParams = array(
            'bkeapi'            => array(
                'proc_day'    => date("YmdHis"),
                'personal_id' => $resultLogin->bkeapi->personal_id,
                'attestation' => $resultLogin->bkeapi->attestation,
                'group_id'    => !empty($userSession['organizationNo']) ? $userSession['organizationNo'] : '',
            ),
            'client_user_agent' => $agent->HTTP_USER_AGENT,
            'client_ip_address' => $agent->REMOTE_ADDR
        );
        try {
            EinaviClient::getInstance()->callGroupGetBasicTicketInfo($config, $apiParams);
        }
        catch (Exception $e) {
            return false;
        }

        return true;
    }

    /*
     * Check email address exits Einavi
     * Return True/False
     */
    protected function checkUserId($data, $agent)
    {
        $email = trim($data['txtMailAdd']);
        $password = $data['txtPassword'];
        $apiParams = array(
            'bkeapi'            => array(
                'proc_day'     => date("YmdHis"),
                'einavi_id'    => $email, //einavi_id;
                'mail_address' => $email, //mail_address
                'password'     => $password, //password;
                'token'        => time(), //token;
                'token_secret' => time(), //token_secret
                'check_flag'   => 2 // 1:id - 2:email - 3:facebook - 4:tiwtter
            ),
            'client_user_agent' => $agent->HTTP_USER_AGENT,
            'client_ip_address' => $agent->REMOTE_ADDR
        );
        $config = $this->getServiceLocator()->get('Config')['satellite_config']['api_userauthen'];
        try {
            $result = EinaviClient::getInstance()->callCheckUserId($config, $apiParams);
        }
        catch (Exception $e) {
            return false;
        }

        return $result;
    }

    /*
     * This function allows pupils to register for new Einavi ID
     * redirect home page of Satellites.
     */
    protected function setUserData($data, $agent)
    {
        $userSession = PrivateSession::getData(Constants::SESSION_SATELLITE);
        $month = $data['ddlMonth'];
        if ($month < 10) {
            $month = "0" . $month;
        }
        $day = $data['ddlDay'];
        if ($day < 10) {
            $day = "0" . $day;
        }
        $birthday = trim($data['ddlYear']) . trim($month) . trim($day);
        $zipcode = trim($data['txtPostalCode1']) . trim($data['txtPostalCode2']);
        $sex = $data['rdSex'];
        $email = trim($data['txtMailAdd']);
        $password = $data['txtPassword'];
        $fn = trim($data['txtFirstName']);
        $ln = trim($data['txtLastName']);
        $mailFlag = $data['rdReceive'];
        $parent = trim($data['txtParent']);
        $apiParams = array(
            'bkeapi'            => array(
                'proc_day'         => date("YmdHis"),
                'personal_id'      => '', // 登録のときNULL
                'attestation'      => '', // 登録のときNULL
                'edit_mode'        => 0, // 登録 Or 更新
                'mandatory_einavi' => array(
                    'gender'        => (int)$sex, //  sex - 1 (male) / 2 (female)
                    'birthday'      => $birthday, // Ymd
                    'zip_code'      => $zipcode, // tokyo
                    'mail_address'  => $email, // email
                    'mail_flag'     => (int)$mailFlag, // 1-0
                    'parent'        => $parent,
                    'user_password' => $password //pass
                ),
                'mandatory_app'    => array(
                    'user_id'         => '',
                    'last_name'       => $ln,
                    'first_name'      => $fn,
                    'last_name_kana'  => '',
                    'first_name_kana' => '',
                    'last_name_alp'   => '',
                    'first_name_alp'  => '',
                    'prefecture'      => '',
                    'city'            => '',
                    'town'            => '',
                    'street'          => '',
                    'building'        => '',
                    'phone_no'        => '',
                    'study_year'      => '',
                    'study_month'     => '',
                    'group_id'        => !empty($userSession['organizationNo']) ? $userSession['organizationNo'] : '', //so to chuc
                    'occupation'      => '',
                    'school'          => '',
                    'school_name'     => '',
                    'school_grade'    => '',
                    'school_class'    => '',
                    'jido_mail'       => '',
                    'jido_info'       => '',
                ),
                'social_info'      => array(
                    'social_kind'   => '',
                    'social_string' => '',
                    'token'         => time(),
                    'token_secret'  => time()
                )
            ),
            'client_user_agent' => $agent->HTTP_USER_AGENT,
            'client_ip_address' => $agent->REMOTE_ADDR
        );
        $config = $this->getServiceLocator()->get('Config')['satellite_config']['api_userauthen'];
        try {
            $result = EinaviClient::getInstance()->callSetUserData($config, $apiParams);
            //Todo get callCheckBasicCorpon
            if ($result->bkeapi->result == 1) {
                $this->checkBasicCoupon($result, $agent);
            }
        }
        catch (Exception $e) {
            return false;
        }

        return $result;
    }

    private function savePupilInfor($personalId, $pupil)
    {
        $em = $this->getEntityManager();
        $pupilExist = $em->getRepository('Application\Entity\Pupil')->find($pupil);
        if ($pupilExist && (!empty($pupilExist) && !empty($personalId))) {
            //save to database
            $pupilExist->setPersonalId($personalId);
            $em->persist($pupilExist);
            $em->flush();
            $em->clear();
        }
    }

    private function saveEinaviInfo($personalId, $pupil)
    {
        $em = $this->getEntityManager();
        $pupilExist = $em->getRepository('Application\Entity\Pupil')->find($pupil);
        if ($pupilExist) {
            $einaviInfor = $em->getRepository('Application\Entity\EinaviInfo')->findOneBy(array('pupilId' => $pupil, 'personalId' => $personalId));
            if (!$einaviInfor && (!empty($pupilExist) && !empty($personalId))) {
                //save to database
                $einaviInfor = new \Application\Entity\EinaviInfo;
                $einaviInfor->setPupil($pupilExist);
                $einaviInfor->setPersonalId($personalId);
                $em->persist($einaviInfor);
                $em->flush();
                $em->clear();
            }
        }
    }

}
