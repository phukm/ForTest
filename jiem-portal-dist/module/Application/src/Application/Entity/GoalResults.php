<?php

/**
 * @author minhbn1<minhbn1@fsoft.com.vn>
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Goalresults
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\GoalResultsRepository")
 * @ORM\Table(name="GoalResults")
 */
class GoalResults extends Common {

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=32, nullable=false, options={"default":""})
     */
    protected $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="ObjectId", type="integer", nullable=false, options={"default":0})
     */
    protected $objectId = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="ObjectType", type="string", length=32, nullable=true)
     */
    protected $objectType;

    /**
     * @var integer
     *
     * @ORM\Column(name="ReferenceId", type="integer", nullable=false, options={"default":0})
     */
    protected $referenceId = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="OrganizationId", type="integer", nullable=false, options={"default":0})
     */
    protected $organizationId = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="NumberOfPeople", type="integer", nullable=false, options={"default":0})
     */
    protected $numberOfPeople = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="Year", type="smallint", nullable=false, options={"default":0})
     */
    protected $year = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="PeoplePassLevel5", type="smallint", nullable=false, options={"default":0})
     */
    protected $peoplePassLevel5 = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="PrecentPassLevel5", type="smallint", nullable=false, options={"default":0})
     */
    protected $precentPassLevel5 = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="PeoplePassLevel4", type="smallint", nullable=false, options={"default":0})
     */
    protected $peoplePassLevel4 = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="PrecentPassLevel4", type="smallint", nullable=false, options={"default":0})
     */
    protected $precentPassLevel4 = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="PeoplePassLevel3", type="smallint", nullable=false, options={"default":0})
     */
    protected $peoplePassLevel3 = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="PrecentPassLevel3", type="smallint", nullable=false, options={"default":0})
     */
    protected $precentPassLevel3 = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="PeoplePassLevelPre2", type="smallint", nullable=false, options={"default":0})
     */
    protected $peoplePassLevelPre2 = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="PrecentPassLevelPre2", type="smallint", nullable=false, options={"default":0})
     */
    protected $precentPassLevelPre2 = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="PeoplePassLevel2", type="smallint", nullable=false, options={"default":0})
     */
    protected $peoplePassLevel2 = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="PrecentPassLevel2", type="smallint", nullable=false, options={"default":0})
     */
    protected $precentPassLevel2 = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="PeoplePassLevelPre1", type="smallint", nullable=false, options={"default":0})
     */
    protected $peoplePassLevelPre1 = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="PrecentPassLevelPre1", type="smallint", nullable=false, options={"default":0})
     */
    protected $precentPassLevelPre1 = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="PeoplePassLevel1", type="smallint", nullable=false, options={"default":0})
     */
    protected $peoplePassLevel1 = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="PrecentPassLevel1", type="smallint", nullable=false, options={"default":0})
     */
    protected $precentPassLevel1 = 0;

    public function getObjectId() {
        return $this->objectId;
    }

    public function getObjectType() {
        return $this->objectType;
    }

    public function getReferenceId() {
        return $this->referenceId;
    }

    public function getOrganizationId() {
        return $this->organizationId;
    }

    public function getYear() {
        return $this->year;
    }

    public function getPeoplePassLevel5() {
        return $this->peoplePassLevel5;
    }

    public function getPrecentPassLevel5() {
        return $this->precentPassLevel5;
    }

    public function getPeoplePassLevel4() {
        return $this->peoplePassLevel4;
    }

    public function getPrecentPassLevel4() {
        return $this->precentPassLevel4;
    }

    public function getPeoplePassLevel3() {
        return $this->peoplePassLevel3;
    }

    public function getPrecentPassLevel3() {
        return $this->precentPassLevel3;
    }

    public function getPeoplePassLevelPre2() {
        return $this->peoplePassLevelPre2;
    }

    public function getPrecentPassLevelPre2() {
        return $this->precentPassLevelPre2;
    }

    public function getPeoplePassLevel2() {
        return $this->peoplePassLevel2;
    }

    public function getPrecentPassLevel2() {
        return $this->precentPassLevel2;
    }

    public function getPeoplePassLevelPre1() {
        return $this->peoplePassLevelPre1;
    }

    public function getPrecentPassLevelPre1() {
        return $this->precentPassLevelPre1;
    }

    public function getPeoplePassLevel1() {
        return $this->peoplePassLevel1;
    }

    public function getPrecentPassLevel1() {
        return $this->precentPassLevel1;
    }

    public function setObjectId($objectId) {
        $this->objectId = $objectId;
    }

    public function setObjectType($objectType) {
        $this->objectType = $objectType;
    }

    public function setReferenceId($referenceId) {
        $this->referenceId = $referenceId;
    }

    public function setOrganizationId($organizationId) {
        $this->organizationId = $organizationId;
    }

    public function setYear($year) {
        $this->year = $year;
    }

    public function setPeoplePassLevel5($peoplePassLevel5) {
        $this->peoplePassLevel5 = $peoplePassLevel5;
    }

    public function setPrecentPassLevel5($precentPassLevel5) {
        $this->precentPassLevel5 = $precentPassLevel5;
    }

    public function setPeoplePassLevel4($peoplePassLevel4) {
        $this->peoplePassLevel4 = $peoplePassLevel4;
    }

    public function setPrecentPassLevel4($precentPassLevel4) {
        $this->precentPassLevel4 = $precentPassLevel4;
    }

    public function setPeoplePassLevel3($peoplePassLevel3) {
        $this->peoplePassLevel3 = $peoplePassLevel3;
    }

    public function setPrecentPassLevel3($precentPassLevel3) {
        $this->precentPassLevel3 = $precentPassLevel3;
    }

    public function setPeoplePassLevelPre2($peoplePassLevelPre2) {
        $this->peoplePassLevelPre2 = $peoplePassLevelPre2;
    }

    public function setPrecentPassLevelPre2($precentPassLevelPre2) {
        $this->precentPassLevelPre2 = $precentPassLevelPre2;
    }

    public function setPeoplePassLevel2($peoplePassLevel2) {
        $this->peoplePassLevel2 = $peoplePassLevel2;
    }

    public function setPrecentPassLevel2($precentPassLevel2) {
        $this->precentPassLevel2 = $precentPassLevel2;
    }

    public function setPeoplePassLevelPre1($peoplePassLevelPre1) {
        $this->peoplePassLevelPre1 = $peoplePassLevelPre1;
    }

    public function setPrecentPassLevelPre1($precentPassLevelPre1) {
        $this->precentPassLevelPre1 = $precentPassLevelPre1;
    }

    public function setPeoplePassLevel1($peoplePassLevel1) {
        $this->peoplePassLevel1 = $peoplePassLevel1;
    }

    public function setPrecentPassLevel1($precentPassLevel1) {
        $this->precentPassLevel1 = $precentPassLevel1;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getNumberOfPeople() {
        return $this->numberOfPeople;
    }

    public function setNumberOfPeople($numberOfPeople) {
        $this->numberOfPeople = $numberOfPeople;
    }

}
