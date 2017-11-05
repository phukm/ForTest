<?php
namespace BasicConstruction\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Crypt\Password\Bcrypt;

class ForgotUserService implements ServiceLocatorAwareInterface
{

    use ServiceLocatorAwareTrait;
    
    private $keyEncrypt = 'jiem-reset-password';
    private $crypt = null;
    
    private function getCrypt()
    {
        if (null === $this->crypt) {
            $this->crypt = new Bcrypt();
        }

        return $this->crypt;
    }
    
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function getNewToken()
    {
        return $this->getCrypt()->create($this->keyEncrypt);
    }
    
    public function generateTokenForgot($userItem)
    {
        $em = $this->getEntityManager();
        $forgotUserItem = $em->getRepository('Application\Entity\ForgotPasswordToken')->findOneBy(array('userId' => $userItem->getId(), 'isDelete' => 0));
        if (!$forgotUserItem) {
            $forgotUserItem = new \Application\Entity\ForgotPasswordToken();
            $forgotUserItem->setUserId($userItem->getId());
        }
        $forgotUserItem->setToken($this->getNewToken());
        $em->persist($forgotUserItem);
        $em->flush();

        return $forgotUserItem->getToken();
    }
    
    public function savePassword($userId, $password, $currentPassword, $oldPasswordFirst, $forgotPasswordId)
    {
        $em = $this->getEntityManager();
        $userItem = $em->getRepository('Application\Entity\User')->find($userId);
        $userItem->setPassword($password);
        $userItem->setOldPasswordFirst($currentPassword);
        $userItem->setOldPasswordSecond($oldPasswordFirst);
        $em->persist($userItem);
        $forgotPasswordItem = $em->getRepository('Application\Entity\ForgotPasswordToken')->find($forgotPasswordId);
        $forgotPasswordItem->setToken('');
        $em->persist($forgotPasswordItem);
        $em->flush();
        
        return $userItem->getId();
    }

    public function isExpired($updateAtDate)
    {
        $date = !empty($updateAtDate) ? $updateAtDate->format('Ymd') : '';
        $dateNowCompare = date('Ymd', strtotime('-7 days'));

        return $date > $dateNowCompare;
    }   
}
