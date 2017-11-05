<?php
namespace Dantai\Session\SaveHandler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Repository;
/**
 * Doctine Session Save Handle
 *
 * @author QuyenLM2
 */
class Doctrine implements \Zend\Session\SaveHandler\SaveHandlerInterface
{
    
    /**
     * Session Save Path
     *
     * @var string
     */
    protected $sessionSavePath;

    /**
     * Session Name
     *
     * @var string
     */
    protected $sessionName;

    /**
     * Lifetime
     * @var int
     */
    protected $lifetime;

    /**
     * Doctrine ORM Repository
     * @var Repository
     */
    protected $repository;
    
    /**
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Constructor
     *
     */
    public function __construct(EntityManager $entityManager,$sessionEntityName = null)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository($sessionEntityName ? : 'Application\Entity\Session');
    }

    /**
     * Open Session
     *
     * @param  string $savePath
     * @param  string $name
     * @return bool
     */
    public function open($savePath, $name)
    {
        $this->sessionSavePath = $savePath;
        $this->sessionName     = $name;
        $this->lifetime        = ini_get('session.gc_maxlifetime');

        return true;
    }

    /**
     * Close session
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $row = $this->repository->findOneBy(array(
            'id'   => $id,
            'name' => $this->sessionName,
        ));

        if ($row !== NULL) {
            if ($row->getModified() + $row->getLifetime() > time()) {
                return $row->getData();
            }
            $this->destroy($id);
        }
        return '';
    }

    /**
     * Write session data
     *
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write($id, $data)
    {
        $row = $this->repository->findOneBy(array(
            'id'   => $id,
            'name' => $this->sessionName,
        ));

        if ($row !== NULL) {
            $row->setModified(time());
            $row->setData((string) $data);
            $this->entityManager->persist($row);
            $this->entityManager->flush();
            return true;
        }
        
        $session = new \Application\Entity\Session();
        $session->setModified(time());
        $session->setData((string) $data);
        $session->setLifetime($this->lifetime);
        $session->setId($id);
        $session->setName($this->sessionName);
        $this->entityManager->persist($session);
        $this->entityManager->flush();
        return true;
    }

    /**
     * Destroy session
     *
     * @param  string $id
     * @return bool
     */
    public function destroy($id)
    {
        $this->entityManager->remove($this->repository->findOneBy(['id' => $id]));
    }

    /**
     * Garbage Collection
     *
     * @param int $maxlifetime
     * @return true
     */
    public function gc($maxlifetime)
    {
        $this->repository->gcSession($this->lifetime);
        return true;
    }

}
