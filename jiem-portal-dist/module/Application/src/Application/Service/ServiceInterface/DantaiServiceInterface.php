<?php

namespace Application\Service\ServiceInterface;

use Application\Entity\User;
use Doctrine\ORM\EntityManager;

interface DantaiServiceInterface
{

    /**
     * Get current authenticated user identities
     * 
     * @return array( 
     *         [id] => int,
     *         [userId] => string,
     *         [password] => string,
     *         [firstName] => string,
     *         [lastName] => string,
     *         [emailAddress] => string,
     *         [roleId] => int,
     *         [role] => string,
     *         [organizationNo] => string,
     *         [organizationId] => int,
     *         [organizationName] => string,
     *         [organizationCode] => string,
     *         [currentKai] => int,
     *         [actionRoles] => array()
     *         );
     *        
     */
    public function getCurrentUser();

    /**
     * @param number $organizationId
     */
    public function changeOrganizationId($organizationId);
    /**
     * @return EntityManager
     */
    public function getEntityManager();

    /**
     * Start editing Entity with cross editing protected.
     * This function does not remove any previous message or data.
     *
     * @param \Application\Entity\Common|string $entityName
     * @param array $entitySearchCriteria
     *
     * @return array (
     *         [conflictWarning] => string,
     *         [conflictType] => string (edit|delete)
     *
     *         )
     */
    public function startCrossEditing($entityName, $entitySearchCriteria = null);
    
    /**
     * Get edit current session's cross editing messages.
     * This function should be called once per action.
     * This function should clean all previous messages and data.
     * 
     * @param \Application\Entity\Common|string $entityName
     * 
     * @return array (
     *         [conflictWarning] => string,
     *         [conflictType] => string (edit|delete)
     *         )
     */
    public function getCrossEditingMessage($entityName);
    
    /**
     * Check editing Entity whether edited by some one else before saving data at curruent session.
     * This function should be called once per action.
     * This function should clean all previous messages and data if there is no cross edit occurs.
     * 
     * @param \Application\Entity\Common|string $entityName
     * @param array $entitySearchCriteria
     * @param array $postData
     * 
     * @return array (
     *         [conflictWarning] => string,
     *         [conflictType] => string (edit|delete)
     *         [data] => \Application\Entity\Common|null
     *         )
     */
    public function checkCrossEditing($entityName, $entitySearchCriteria, $postData);
    
    /**
     * Restore last posted data while conflicting.
     * This function should be called once per action.
     * This function should clean all previous messages and data.
     * 
     * @param \Application\Entity\Common|string $entityName
     * @param \Zend\Form\Form $form
     * @param array $maping mapping field to restore
     * 
     * @return array (
     *         [conflictWarning] => string,
     *         [conflictType] => string (edit|delete),
     *         [data] => \Zend\Form\Form|array
     *         )
     */
    public function restoreCrossEditingForm($entityName, $form, $maping = array());
    
    /**
     * Try to lock the given $module.
     * It returns false if failed to lock, lockId if lock is already owned or created
     * 
     * @param \Application\Entity\Common|string $module
     * 
     * @return array(
     *         [lockId]         => false|number,
     *         [lockMessage]    => string
     *         )
     */
    public function lockModule($module);
    
    /**
     * Releases lock for the given $module.
     * 
     * @param \Application\Entity\Common|string $module
     */
    public function releaseLockModule($module);
    
    /**
     * Remove all expired locks for current locker if $purgeAll is false, purge all if $purgeAll is true
     * 
     * @param boolean $purgeAll
     */
    public function purgeExpiredLocks($purgeAll = null);
    
    /**
     * Register an automatically clean lock event, when user visits to other page
     * 
     * @param \Zend\Mvc\MvcEvent $event
     * @param \Application\Entity\Common|string $module
     */
    public function registerCleanLock($event, $module);
    
    /**
     * Auto clean all locks when user visits to other page
     * 
     * @param \Zend\Mvc\MvcEvent $event
     */
    public function autoCleanLock($event);
    
    /**
     * Get search criteria from given event at controller based on specified parameter list on $criteriaDefault
     * 
     * @param \Zend\Mvc\MvcEvent $event
     * @param array('key' => 'default value', ...) $criteriaDefault
     * 
     * @return array('key' => 'default value', ..., 'token' => 'crc32 hashed')
     */
    public function getSearchCriteria(\Zend\Mvc\MvcEvent $event, array $criteriaDefault);
    
    /**
     * @param mixed $search
     *
     * @return string
     */
    public function getToken($search);

    /**
     * @param string $routeMatch
     *
     * @return mixed
     */
    public function getSearchKeywordFromSession($routeMatch);

    /**
     * @param string $routeMatch
     *
     * @param mixed $searchData
     */
    public function setSearchKeywordToSession($routeMatch, $searchData);
    
    public function getCurrentYear();

    public function getSemiMainVenue($orgId, $eikenScheduleId);

    public function getSemiMainVenueOrigin($orgId, $eikenScheduleId);

    public function getBeneficiaryVenue($orgId, $eikenScheduleId);

    public function getBeneficiaryVenueOrigin($orgId, $eikenScheduleId);

    public function isSpecialOrg($orgId, $eikenScheduleId);
}