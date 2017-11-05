<?php
namespace Dantai\Aws;

use Aws\Ses\SesClient;

class AwsSesClient
{

    /**
     * Client for AmazonSes
     */
    protected $sesClient;

    protected $sesConfig;
    
    /**
     *
     * @var \Dantai\Aws\AwsSesClient
     */
    protected static $_instance = null;

    /**
     * Singleton
     *
     * @return \Dantai\Aws\AwsSesClient
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self(Config::getConfig(Config::AWS_SES));
        }
        
        return self::$_instance;
    }

    protected function __construct($config = null)
    {
        $this->sesConfig = $config;
    }

    protected function getClient(){
        if(!$this->sesClient){
            $this->sesClient = new \Aws\Ses\SesClient($this->sesConfig);
        }
        return $this->sesClient;
    }
    
    public function deliver($source, $to, $type, $data)
    {
        $configExt = Config::getConfig(Config::AWS_SES_EXT);
        $source = array_key_exists('source-mail', $configExt) ? $configExt['source-mail'] : $source;
        $email = $this->buildEmail($type, $data);
        if ($email['subject'] != 'Errorrrr') {
            $this->getClient()->sendEmail(array(
                // Source is required
                'Source' => $source,
                // Destination is required
                'Destination' => array(
                    'ToAddresses' => $to
                ),
                // Message is required
                'Message' => array(
                    // Subject is required
                    'Subject' => array(
                        // Data is required
                        'Data' => $email['subject'],
                        'Charset' => 'UTF-8'
                    ),
                    // Body is required
                    'Body' => array(
                        'Text' => array(
                            // Data is required
                            'Data' => $email['body'],
                            'Charset' => 'UTF-8'
                        ),
                        'Html' => array(
                            // Data is required
                            'Data' => $email['body'],
                            'Charset' => 'UTF-8'
                        )
                    )
                )
            ));
            // 'ReplyToAddresses' => array( 'haidongdo1994@gmail.com' ),
            // 'ReturnPath' => 'haidongdo1994@gmail.com'
            
        }
    }
    
    public function send($subject,$mailBody,$listEmailTo,$listEmailCc=array()){
        $configExt = Config::getConfig(Config::AWS_SES_EXT);
        $mailFrom = $configExt['source-mail'] ;
        if(empty($listEmailTo) || empty($subject) || empty($mailBody) || empty($mailFrom)){
            throw new \Exception('Can not insert empty data!');
        }
        $this->getClient()->sendEmail(array(
            'Source' => $mailFrom,
            'Destination' => array(
                'ToAddresses' => $listEmailTo,
                'CcAddresses' => $listEmailCc,
            ),
            'Message' => array(
                'Subject' => array(
                    'Data' => $subject,
                    'Charset' => 'UTF-8'
                ),
                'Body' => array(
                    'Text' => array(
                        'Data' => $mailBody,
                        'Charset' => 'UTF-8'
                    ),
                    'Html' => array(
                        'Data' => $mailBody,
                        'Charset' => 'UTF-8'
                    )
                )
            )
        ));
    }

    private function buildEmail($type, $data)
    {
        $generator = new \Dantai\Utility\HTMLGenerator(array());
        
        $templatePath = __DIR__ . '/../Template/Email';
        $tempPathEikenApply = __DIR__ . '/../Template/Email/mail-template';
        switch ($type) {
            case 1:
                $password = $this->buildPasswordPattern($data['password']);
                $subject = '【英検団体サポート】パスワード変更通知';
                $body = $generator->render($templatePath, 'changePasswordNotification.phtml', array(
                    'url' => $data['url'],
                    'name' => $data['name'],
                    'orgName' => $data['orgName'],
                    'organizationNo' => $data['organizationNo'],
                    'userId' => $data['userId'],
                    'password' => $password
                ));
                break;
            case 2:
                $password = $data['password'];
                $subject = '【英検団体サポート】ユーザ登録完了通知';
                $body = $generator->render($templatePath, 'registerConfirmation.phtml', array(
                    'url' => $data['url'],
                    'name' => $data['name'],
                    'orgName' => $data['orgName'],
                    'orgNo' => $data['orgNo'],
                    'userId' => $data['userId'],
                    'password' => $password,
                    'confirmUrl' => $data['confirmUrl']
                ));
                break;
            case 3:
                $subject = '【英検団体サポート】受験案内状作成エラー通知';
                $body = $generator->render($templatePath, 'errorMessage.phtml', array(
                    //'messages' => $data,
                    'name' => $data['name'],
                    'orgName' => $data['orgName'],
                    'url' => $data['url']
                ));
                break;
            case 4:
                $subject = '【英検団体サポート】受験案内状作成完了通知';
                $body = $generator->render($templatePath, 'letterGeneratedSuccess.phtml', array(
                    'name' => $data['name'],
                    'orgName' => $data['orgName'],
                    'url' => $data['url']
                ));
                break;
            case 5:
                $subject = '【英検団体サポート】受験案内状の作成問題通知';
                $body = $generator->render($templatePath, 'errorTimeoutMessage.phtml', array(
                    'name' => $data['name'],
                ));
                break;
            // Send Email after submit successfully for the first time
            case 6:
                $subject = '【英検団体サポート】英検団体申込確定通知';
                $body = $generator->render($templatePath, 'submitEiken.phtml', array(
                    'orgInfo' => $data['orgInfo'],
                    'detailPrice' => $data['detailPrice'],
                    'detailFee' => $data['detailFee'],
                    'eikenOrgDetail' => $data['eikenOrgDetail']
                ));
                break;
            case 61:
                $subject = '【英検団体サポート】英検団体申込確定通知';
                $body = $generator->render($tempPathEikenApply, 'non-smv-collective.phtml', array(
                    'data'=>array(
                            'orgInfo' => $data['orgInfo'],
                            'detailPrice' => $data['detailPrice'],
                            'detailFee' => $data['detailFee'],
                            'eikenOrgDetail' => $data['eikenOrgDetail'],
                            'semi' => $data['eikenOrgDetail'],
                            'beneficiary' => $data['beneficiary'],
                            'paymentType' => $data['paymentType']  
                            )
                    ));
                break;
            case 62:
                $subject = '【英検団体サポート】英検団体申込確定通知';
                $body = $generator->render($tempPathEikenApply, 'email-individual-template.phtml', array(
                    'orgInfo' => $data['orgInfo'],
                    'detailPrice' => $data['detailPrice'],
                    'detailFee' => $data['detailFee'],
                    'eikenOrgDetail' => $data['eikenOrgDetail'],
                    'semi' => $data['eikenOrgDetail'],
                    'beneficiary' => $data['beneficiary'],
                    'paymentType' => $data['paymentType']
                ));
                break;
            case 63:
                $subject = '【英検団体サポート】英検団体申込確定通知';
                $body = $generator->render($tempPathEikenApply, 'smv-dantai-collective.phtml', array(
                    'data'=>array(
                            'orgInfo' => $data['orgInfo'],
                            'detailPrice' => $data['detailPrice'],
                            'detailFee' => $data['detailFee'],
                            'eikenOrgDetail' => $data['eikenOrgDetail'],
                            'semi' => $data['eikenOrgDetail'],
                            'beneficiary' => $data['beneficiary'],
                            'paymentType' => $data['paymentType']  
                            )
                    ));
                break;
            case 64:
                $subject = '【英検団体サポート】英検団体申込確定通知';
                $body = $generator->render($tempPathEikenApply, 'email-individual-template.phtml', array(
                    'orgInfo' => $data['orgInfo'],
                    'detailPrice' => $data['detailPrice'],
                    'detailFee' => $data['detailFee'],
                    'eikenOrgDetail' => $data['eikenOrgDetail'],
                    'semi' => $data['eikenOrgDetail'],
                    'beneficiary' => $data['beneficiary'],
                    'paymentType' => $data['paymentType']
                ));
                break;
            case 65:
                $subject = '【英検団体サポート】英検団体申込確定通知';
                $body = $generator->render($tempPathEikenApply, 'smv-student-collective.phtml', array(
                    'data'=>array(
                            'orgInfo' => $data['orgInfo'],
                            'detailPrice' => $data['detailPrice'],
                            'detailFee' => $data['detailFee'],
                            'eikenOrgDetail' => $data['eikenOrgDetail'],
                            'semi' => $data['eikenOrgDetail'],
                            'beneficiary' => $data['beneficiary'],
                            'paymentType' => $data['paymentType']  
                            )
                    ));
                break;
            case 66:
                $subject = '【英検団体サポート】英検団体申込確定通知';
                $body = $generator->render($tempPathEikenApply, 'email-individual-template.phtml', array(
                    'orgInfo' => $data['orgInfo'],
                    'detailPrice' => $data['detailPrice'],
                    'detailFee' => $data['detailFee'],
                    'eikenOrgDetail' => $data['eikenOrgDetail'],
                    'semi' => $data['eikenOrgDetail'],
                    'beneficiary' => $data['beneficiary'],
                    'paymentType' => $data['paymentType']
                ));
                break;
            case 71:
                $subject = '【英検団体サポート】英検団体申込確定通知';
                $body = $generator->render($tempPathEikenApply, 'non-smv-individual-public-funding.phtml',array(
                    'data' => array(
                        'orgInfo' => $data['orgInfo'],
                        'detailPrice' => $data['detailPrice'],
                        'detailFee' => $data['detailFee'],
                        'eikenOrgDetail' => $data['eikenOrgDetail'],
                        'semi' => $data['eikenOrgDetail'],
                        'beneficiary' => $data['beneficiary'],
                        'paymentType' => $data['paymentType']
                        )
                    )
                );
                break;
            case 7:
                $password = $data['password'];
                $subject = '【英検団体サポート】ユーザ登録完了通知';
                $body = $generator->render($templatePath, 'registerFirstUserConfirmation.phtml', array(
                    'url' => $data['url'],
                    'name' => $data['name'],
                    'orgName' => $data['orgName'],
                    'orgNo' => $data['orgNo'],
                    'userId' => $data['userId'],
                    'password' => $password,
                    'confirmUrl' => $data['confirmUrl']
                ));
                break;
            case 8: // send mail expired time execute Combini to Dantai Admin
                $subject = '【' . $data["orgNo"] . '】受験案内状生成課題';
                $body = $generator->render($templatePath, 'expiredExecuteTimeSendCombini.phtml', array(
                    'name' => $data['name'],
                    'orgName' => $data['orgName'],
                    'orgNo' => $data['orgNo'],
                    'timeGenerate' => $data['timeGenerate'],
                    'url' => $data['url']
                ));
                break;
            case 9: // send mail expired time execute Combini to Eiken Admin, FPT Admin
                $subject = '【' . $data["orgNo"] . '】英検サービスセンター';
                $body = $generator->render($templatePath, 'expiredExecuteTimeSendCombiniToAdmin.phtml', array(
                    'name' => $data['name'],
                    'orgName' => $data['orgName'],
                    'orgNo' => $data['orgNo'],
                    'timeGenerate' => $data['timeGenerate'],
                    'url' => $data['url']
                ));
                break;
            default:
                $subject = 'Errorrrr';
                $body = 'Errorrrr';
        }
        
        $email['subject'] = $subject;
        $email['body'] = $body;
        
        return $email;
    }

    private function buildPasswordPattern($password)
    {
        $length = strlen($password);
        $result = "";
        for ($i = 0; $i < $length - 3; $i ++) {
            $result .= '*';
        }
        
        for ($i = $length - 3; $i < $length; $i ++) {
            $result .= $password[$i];
        }
        
        return $result;
    }
}