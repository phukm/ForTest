<?php
/**
 *
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\OrgGraduationGoalRepository")
 * @ORM\Table(name="OrgGraduationGoal")
 */
class OrgGraduationGoal extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to Organization
     * @ORM\Column(type="integer", name="OrganizationId", nullable=true)
     *
     * @var integer
     */
    protected $organizationId;

    /**
     * Foreing key reference to EikenLevel
     * @ORM\Column(type="integer", name="EikenLevelId", nullable=true)
     *
     * @var integer
     */
    protected $eikenLevelId;

    /* Property */

    /**
     * @ORM\Column(type="integer", name="Year", length=4, nullable=false)
     *
     * @var integer
     */
    protected $year;

    /**
     * @ORM\Column(type="integer", name="TargetPass", length=3, nullable=false)
     *
     * @var integer
     */
    protected $targetPass;

    /**
     * @ORM\Column(type="integer", name="OrgSchoolYearId", length=3, nullable=true)
     *
     * @var integer
     */
    protected $orgSchoolYearId;

    /**
     * @ORM\Column(type="boolean", name="IsGraduationGoal", length=1, nullable=false, options={"default":0})
     *
     * @var boolean
     */
    protected $isGraduationGoal = false;

    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="OrganizationId", referencedColumnName="id")
     */
    protected $organization;

    /**
     * @ORM\ManyToOne(targetEntity="EikenLevel")
     * @ORM\JoinColumn(name="EikenLevelId", referencedColumnName="id")
     */
    protected $eikenLevel;

    /**
     * @ORM\ManyToOne(targetEntity="OrgSchoolYear")
     * @ORM\JoinColumn(name="OrgSchoolYearId", referencedColumnName="id")
     */
    protected $orgSchoolYear;

    /**
     * @return int
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     * @param int $organizationId
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    /**
     * @return int
     */
    public function getEikenLevelId()
    {
        return $this->eikenLevelId;
    }

    /**
     * @param int $eikenLevelId
     */
    public function setEikenLevelId($eikenLevelId)
    {
        $this->eikenLevelId = $eikenLevelId;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return int
     */
    public function getTargetPass()
    {
        return $this->targetPass;
    }

    /**
     * @param int $targetPass
     */
    public function setTargetPass($targetPass)
    {
        $this->targetPass = $targetPass;
    }

    /**
     * @return int
     */
    public function getOrgSchoolYearId()
    {
        return $this->orgSchoolYearId;
    }

    /**
     * @param int $orgSchoolYearId
     */
    public function setOrgSchoolYearId($orgSchoolYearId)
    {
        $this->orgSchoolYearId = $orgSchoolYearId;
    }

    /**
     * @return boolean
     */
    public function getIsGraduationGoal()
    {
        return $this->isGraduationGoal;
    }

    /**
     * @param boolean $isGraduationGoal
     */
    public function setIsGraduationGoal($isGraduationGoal)
    {
        $this->isGraduationGoal = $isGraduationGoal;
    }

    /**
     * @return Organization|NULL
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param mixed $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * @return EikenLevel|NULL
     */
    public function getEikenLevel()
    {
        return $this->eikenLevel;
    }

    /**
     * @param mixed $eikenLevel
     */
    public function setEikenLevel($eikenLevel)
    {
        $this->eikenLevel = $eikenLevel;
    }

    /**
     * @return OrgSchoolYear|NULL
     */
    public function getOrgSchoolYear()
    {
        return $this->orgSchoolYear;
    }

    /**
     * @param mixed $orgSchoolYear
     */
    public function setOrgSchoolYear($orgSchoolYear)
    {
        $this->orgSchoolYear = $orgSchoolYear;
    }
}