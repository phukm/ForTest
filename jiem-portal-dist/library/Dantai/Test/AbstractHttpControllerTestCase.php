<?php
namespace Dantai\Test;
use PHPUnit_Framework_ExpectationFailedException;
use Zend\Dom\Document;

abstract class AbstractHttpControllerTestCase extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase {

    public function setUp() {
        $this->setApplicationConfig(
            include APP_DIR . '/config/application.config.php'
        );
        parent::setUp();
    }

    public function tearDown() {
        if($this->getEntityManager()->getConnection()){
            $this->getEntityManager()->getConnection()->close();
        }
        return parent::tearDown();
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        return $this->getApplicationServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    protected function login(){
        $applicationService = $this->getApplicationServiceLocator();

        $authServiceMock = $this->getMock('\Zend\Authentication\AuthenticationService');
        $authServiceMock->expects($this->any())
            ->method('hasIdentity')
            ->will($this->returnValue(true));

        $authServiceMock->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($this->getIdentityMock()));
        $applicationService->setAllowOverride(TRUE);
        $applicationService->setService('doctrine.authenticationservice.orm_default', $authServiceMock);

        \Dantai\PrivateSession::setData('userIdentity', $this->getIdentityDataSession());
    }

    protected function getIdentityMock(){
        $identityMock = $this->getEntityManager()->getRepository('\Application\Entity\User')->find(1)?:new \Application\Entity\User();
        $hydrator = new \DoctrineModule\Stdlib\Hydrator\DoctrineObject($this->getEntityManager(), '\Application\Entity\User');
        $userIdentity =  $hydrator->hydrate($this->getIdentityDataSession(), $identityMock);
        $userIdentity->setRole($this->getEntityManager()->getReference('\Application\Entity\Role', $this->getIdentityDataSession()['roleId']));

        return $userIdentity;
    }

    protected function getIdentityDataSession(){
        return array(
            'id' => 1,
            'userId' => 'USER001',
            'password' => '22e46605485731b50057e597feb80792',
            'firstName' => 'USER001',
            'lastName' => 'USER001',
            'emailAddress' => 'dunghp@fsoft.com.vn',
            'roleId' => 1,
            'role' => 'System Administrator',
            'organizationNo' => '90010100',
            'organizationCode' => '00',
            'organizationId' => 1,
            'organizationName' => '検査01',
            'organizationCode' => '00',
            'currentKai' => '2',
        );
    }

    /**
     * Execute a DOM/XPath query
     *
     * @param  string $path
     * @param  bool $useXpath
     * @return Document\NodeList
     */
    private function query($path, $useXpath = false)
    {
        $response = $this->getResponse();
        $document = new Document($response->getContent());

        if ($useXpath) {
            $document->registerXpathNamespaces($this->xpathNamespaces);
        }

        $result = Document\Query::execute(
            $path,
            $document,
            $useXpath ? Document\Query::TYPE_XPATH : Document\Query::TYPE_CSS
        );

        return $result;
    }

    /**
     * Assert against DOM/XPath selection; node should contain content
     *
     * @param  string $path CSS selector path
     * @param  string $match content that should be contained in matched nodes
     * @param bool $useXpath
     */
    private function queryContentContainsAssertion($path, $match, $useXpath = false)
    {
        $result = $this->query($path, $useXpath);

        if ($result->count() == 0) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                'Failed asserting node DENOTED BY %s EXISTS',
                $path
            ));
        }

        $nodeValues = array();

        foreach ($result as $node) {
            if (false !== strpos($node->nodeValue, $match)) {
                $this->assertContains($match, $node->nodeValue);
                return;
            }
            $nodeValues[] = $node->nodeValue;
        }

        throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
            'Failed asserting node denoted by %s CONTAINS content "%s", Contents: [%s]',
            $path,
            $match,
            implode(',', $nodeValues)
        ));
    }
    
    /**
     * Assert against DOM selection; node should contain content
     *
     * @param  string $path CSS selector path
     * @param  string $match content that should be contained in matched nodes
     */
    public function assertQueryContentContains($path, $match)
    {
        $this->queryContentContainsAssertion($path, $match, false);
    }

    /**
     * Assert against XPath selection; node should contain content
     *
     * @param  string $path XPath path
     * @param  string $match content that should be contained in matched nodes
     */
    public function assertXpathQueryContentContains($path, $match)
    {
        $this->queryContentContainsAssertion($path, $match, true);
    }
}