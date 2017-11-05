<?php
use AccessKey\AccessKeyConst;
use Dantai\PrivateSession;
class CreatNewUserAccessKeyValidateTest extends \Dantai\Test\AbstractHttpControllerTestCase {
    private $params = array(
            'userId' => 'userName',
            'firstNameKanji' => 'han vu',
            'lastNameKanji' => 'de',
            'emailAddress' => 'test@gmail.com',
            'confirmEimail' => 'test@gmail.com',
            'checkBoxPolicy' => '1',
        );
    
    private $msg01;
    private $msg21;
    private $EmailAddressDoesNotMatch;

    public function createMsg(){
        $this->msg01 = '/'.$this->getApplicationServiceLocator()->get('MVCTranslator')->translate('MSG1').'/';
        $this->msg21 = '/'.$this->getApplicationServiceLocator()->get('MVCTranslator')->translate('MSG21').'/';
        $this->EmailAddressDoesNotMatch = '/'.$this->getApplicationServiceLocator()->get('MVCTranslator')->translate('EmailAddressDoesNotMatch').'/';
    }
    
    public function setPrivateSession()
    {
        $data = array(
            'organizationNo'=>123456,
            'accessKey'=>123456
        );
        $privateSession = new PrivateSession();
        $privateSession->setData(AccessKeyConst::SESSION_ACCESS_KEY, $data);
    }
    
    public function testWhenSubmitEmtpyUserIdThenShowMessage1() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['userId'] = '';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg01);
    }
    public function testWhenSubmitEmtpyFirstNameKanjiThenShowMessage1() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['firstNameKanji'] = '';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg01);
    }
    public function testWhenSubmitEmtpyLastNameKanjiThenShowMessage1() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['lastNameKanji'] = '';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg01);
    }
    public function testWhenSubmitEmtpyFirstNameKanjiAndLastNameKanjiThenShowMessage1() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['firstNameKanji'] = '';
        $this->params['lastNameKanji'] = '';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg01);
    }
    public function testWhenSubmitEmtpyEmailAddressThenShowMessage1() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['emailAddress'] = '';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg01);
    }
    public function testWhenSubmitUserIdLessThan4CharactersThenShowMessage19() {
        $this->setPrivateSession();
        $this->params['userId'] = 'Use';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body','/ユーザIDは以下の規則に準拠し/');
        $this->assertQueryContentRegex('body','/ている必要があります。/');
        $this->assertQueryContentRegex('body','/4～31文字/');
        $this->assertQueryContentRegex('body','/半角英数字とハイフン（-）/');
        $this->assertQueryContentRegex('body','/、アンダーバー（_）/');
        $this->assertQueryContentRegex('body','/先頭の1文字目/');
        $this->assertQueryContentRegex('body','/は半角英字/');
    }
    public function testWhenSubmitUserIdGreaterThan31CharacterThenShowMessage19() {
        $this->setPrivateSession();
        $this->params['userId'] = 'Username12Username12Username1232';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body','/ユーザIDは以下の規則に準拠し/');
        $this->assertQueryContentRegex('body','/ている必要があります。/');
        $this->assertQueryContentRegex('body','/4～31文字/');
        $this->assertQueryContentRegex('body','/半角英数字とハイフン（-）/');
        $this->assertQueryContentRegex('body','/、アンダーバー（_）/');
        $this->assertQueryContentRegex('body','/先頭の1文字目/');
        $this->assertQueryContentRegex('body','/は半角英字/');
    }
    public function testWhenSubmitUserIdFontStyleIsHaftWidthThenNotShowMessage19() {
        $this->setPrivateSession();
        $this->params['userId'] = 'userName';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertNotQueryContentRegex('body','/ユーザIDは以下の規則に準拠している必要があります。/');
        $this->assertNotQueryContentRegex('body','/4～31文字/');
        $this->assertNotQueryContentRegex('body','/半角英数字とハイフン（-）/');
        $this->assertNotQueryContentRegex('body','/、アンダーバー（_）/');
        $this->assertNotQueryContentRegex('body','/先頭の1文字目/');
        $this->assertNotQueryContentRegex('body','/は半角英字/');
        
    }
    public function testWhenSubmitUserIdFontStyleIsHaftWidthThenShowMessage19() {
        $this->setPrivateSession();
        $this->params['userId'] = 'ｈａｎｖｕｄｅ';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body','/ユーザIDは以下の規則に準拠している必要があります。/');
        $this->assertQueryContentRegex('body','/4～31文字/');
        $this->assertQueryContentRegex('body','/半角英数字とハイフン（-）/');
        $this->assertQueryContentRegex('body','/、アンダーバー（_）/');
        $this->assertQueryContentRegex('body','/先頭の1文字目/');
        $this->assertQueryContentRegex('body','/は半角英字/');
    }
    public function testWhenSubmitUserIdUseUsedSymbolWithPermissionThenNotShowMessage19() {
        $this->setPrivateSession();
        $this->params['userId'] = 'user_-Name';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertNotQueryContentRegex('body','/ユーザIDは以下の規則に準拠している必要があります。/');
        $this->assertNotQueryContentRegex('body','/4～31文字/');
        $this->assertNotQueryContentRegex('body','/半角英数字とハイフン（-）/');
        $this->assertNotQueryContentRegex('body','/、アンダーバー（_）/');
        $this->assertNotQueryContentRegex('body','/先頭の1文字目/');
        $this->assertNotQueryContentRegex('body','/は半角英字/');
    }
    public function testWhenSubmitUserIdUseOutsideTheAllowedCharacterThenShowMessage19() {
        $this->setPrivateSession();
        $this->params['userId'] = 'user()Name';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body','/ユーザIDは以下の規則に準拠している必要があります。/');
        $this->assertQueryContentRegex('body','/4～31文字/');
        $this->assertQueryContentRegex('body','/半角英数字とハイフン（-）/');
        $this->assertQueryContentRegex('body','/、アンダーバー（_）/');
        $this->assertQueryContentRegex('body','/先頭の1文字目/');
        $this->assertQueryContentRegex('body','/は半角英字/');
    }
    public function testWhenSubmitUserIdTheFirstCharacterIsLetterThenNotShowMessage19() {
        $this->setPrivateSession();
        $this->params['userId'] = 'userName';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertNotQueryContentRegex('body','/ユーザIDは以下の規則に準拠している必要があります。/');
        $this->assertNotQueryContentRegex('body','/4～31文字/');
        $this->assertNotQueryContentRegex('body','/半角英数字とハイフン（-）/');
        $this->assertNotQueryContentRegex('body','/、アンダーバー（_）/');
        $this->assertNotQueryContentRegex('body','/先頭の1文字目/');
        $this->assertNotQueryContentRegex('body','/は半角英字/');
    }
    public function testWhenSubmitUserIdTheFirstCharacterIsNotLetterThenShowMessage19() {
        $this->setPrivateSession();
        $this->params['userId'] = '1userName';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body','/ユーザIDは以下の規則に準拠している必要があります。/');
        $this->assertQueryContentRegex('body','/4～31文字/');
        $this->assertQueryContentRegex('body','/半角英数字とハイフン（-）/');
        $this->assertQueryContentRegex('body','/、アンダーバー（_）/');
        $this->assertQueryContentRegex('body','/先頭の1文字目/');
        $this->assertQueryContentRegex('body','/は半角英字/');
    }
    public function testWhenSubmitUserIdUseSpacesInTheUserIdThenShowMessage19() {
        $this->setPrivateSession();
        $this->params['userId'] = 'user Name';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body','/ユーザIDは以下の規則に準拠している必要があります。/');
        $this->assertQueryContentRegex('body','/4～31文字/');
        $this->assertQueryContentRegex('body','/半角英数字とハイフン（-）/');
        $this->assertQueryContentRegex('body','/、アンダーバー（_）/');
        $this->assertQueryContentRegex('body','/先頭の1文字目/');
        $this->assertQueryContentRegex('body','/は半角英字/');
    }

    public function testWhenSubmitEmailNameHaveTildeCharThenShowMessage21() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['emailAddress'] = '~sample@eiken.or.jp';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg21);
    }
    
    public function testWhenSubmitEmailNameHaveGraveCharThenShowMessage21() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['emailAddress'] = '`sample@eiken.or.jp';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg21);
    }
    
    public function testWhenSubmitEmailNameHaveExclamationMarkCharThenShowMessage21() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['emailAddress'] = '!sample@eiken.or.jp';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg21);
    }
    
    public function testWhenSubmitEmailNameHaveAtCharThenShowMessage21() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['emailAddress'] = '@sample@eiken.or.jp';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg21);
    }
    
    public function testWhenSubmitEmailNameHaveNumberCharThenShowMessage21() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['emailAddress'] = '#sample@eiken.or.jp';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg21);
    }
    
    public function testWhenSubmitEmailNameHaveDollardSignCharThenShowMessage21() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['emailAddress'] = '$sample@eiken.or.jp';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg21);
    }
    
    public function testWhenSubmitEmailNameHavePercentCharThenShowMessage21() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['emailAddress'] = '%sample@eiken.or.jp';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg21);
    }
    
    public function testWhenSubmitEmailNameHaveCaretCharThenShowMessage21() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['emailAddress'] = '^sample@eiken.or.jp';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg21);
    }
    
    public function testWhenSubmitEmailNameHaveAndCharThenShowMessage21() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['emailAddress'] = '&sample@eiken.or.jp';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg21);
    }
    
    public function testWhenSubmitEmailNameHaveAsteriskCharThenShowMessage21() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['emailAddress'] = '*sample@eiken.or.jp';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg21);
    }
    
    public function testWhenSubmitEmailNameHaveSpecialCharThenShowMessage21() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['emailAddress'] = '()-_=+[{]};:\'\",<.>/?sample@eiken.or.jp';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body', $this->msg21);
    }
    
    public function testWhenSubmitConfirmEmailAddressDifferentFromEmailAddressThenShowMessageEmailAddressDoesNotMatch() {
        $this->setPrivateSession();
        $this->createMsg();
        $this->params['confirmEimail'] = 'test2@gmail.com';
        $data = $this->params;
        $this->dispatch('/access-key/access-key/add', \Zend\Http\Request::METHOD_POST, $data);
        $this->assertQueryContentRegex('body',$this->EmailAddressDoesNotMatch);
    }
}