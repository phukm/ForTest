<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace ConsoleInvitation\Controller;

use Dantai\PrivateSession;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Zend\Http\Client;
use Zend\Mvc\Controller\AbstractActionController;
use Dantai\Api\UkestukeClient;
use Dantai\Utility\CharsetConverter;

class ExampleController extends AbstractActionController
{
    use ContainerAwareTrait;
    const CODE_OUT_LAND = 9901;

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function indexAction()
    {
        $timeBeginCallApi = microtime(true);
        $config = $this->getServiceLocator()->get('Config')['creditcard_config'];
        try {
            $parameterApi = array(
                'shopID' => $config['site_code'],
                'orderID' => '2016121640638400000063',
                'chkCode' => $config['api_cancel_order']['chkCode'],
                'ordAmount' => 2500,
            );

            $configApi = $config['api_cancel_order'];
            $result = UkestukeClient::getInstance()->callEconRcvCancelOrder($configApi, $parameterApi);
            $result['message'] = CharsetConverter::shiftJisToUtf8($result['message']);
            $response = implode(' ', $result);
        } catch (\Exception $ex) {
            $response = 'Error: ' . $ex->getMessage();
        }
        $timeEndCallApi = microtime(true);
        echo 'Time excute: ' . ($timeEndCallApi - $timeBeginCallApi) . ' | REQUEST: ' . json_encode($parameterApi) . ' | REPONSE: ' . $response . PHP_EOL;
    }

    public function sendMail()
    {
        $em = $this->getEntityManager();
        $awsSes = \Dantai\Aws\AwsSesClient::getInstance();
        /* @var $organization \Application\Entity\Organization */
        $organization = $em->getRepository('\Application\Entity\Organization')
            ->findOneBy(array('organizationNo' => '90010100'));
        $awsSes->deliver('dantai@mail.eiken.or.jp', array(
            'dunghp@fsoft.com.vn'
        ), 8, array(
            'name' => 'DungHP',
            'orgName' => empty($organization) ? '' : $organization->getOrgNameKanji(),
            'orgNo' => empty($organization) ? '' : $organization->getOrganizationNo(),
            'timeGenerate' => date('Y/m/d H:i:s'),
            'url' => ''
        ));

        $awsSes->deliver('dantai@mail.eiken.or.jp', array(
            'dunghp@fsoft.com.vn'
        ), 9, array(
            'name' => 'DungHP',
            'orgName' => empty($organization) ? '' : $organization->getOrgNameKanji(),
            'orgNo' => empty($organization) ? '' : $organization->getOrganizationNo(),
            'timeGenerate' => date('Y/m/d H:i:s'),
            'url' => ''
        ));
    }

    public function ibaMappingAction()
    {
        $orgNo = $this->params()->fromRoute('id');
        $autoMappingService = $this->getServiceLocator()->get('ConsoleInvitation\Service\AutoMappingService');
        $userIdentity = array(
            'host' => 'http://manhln.jiem-server.com',
        );
        PrivateSession::setData('userIdentity', $userIdentity);
        //$result = $autoMappingService->callEir2c02('000020496', '01');
        $result = $autoMappingService->callEir2c03($orgNo);
        print_r($result);
        //$host = 'http://manhln.jiem-server.com';
        //$uri = $host . '/rest-api/callEir2c03/'.$orgNo;
        //$this->httpClient = new Client();
        //$adptOptions = array(
        //    'ssl' => array(
        //        'verify_peer_name' => false
        //    )
        //);
        //$this->httpClient->getAdapter()->setStreamContext($adptOptions);
        //$this->httpClient->setUri($uri);
        //$this->httpClient->setMethod('GET');
        //$this->httpClient->send();
        //$result = $this->httpClient->getResponse()->getContent();
        //print_r(json_decode($result));
    }
}
