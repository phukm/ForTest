<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\NumberOfStudentHistoryRepository")
 * @ORM\Table(name="NumberOfStudentHistory")
 */
class NumberOfStudentHistory extends Common
{

    /* Foreing key */
    /**
     * Foreing key reference to Organization
     * @ORM\Column(type="integer", name="OrganizationId", nullable=true)
     *
     * @var integer
     */
    protected $organizationId;

    /* Relationship */
    /**
     * Reference to table Organization
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="OrganizationId", referencedColumnName="id")
     */
    protected $organization;

    /* Property */
    
    /**
     * @ORM\Column(type="integer", name="Year", nullable=true)
     *
     * @var integer
     */
    protected $year;

    /**
     * @ORM\Column(type="integer", name="Time", nullable=true)
     *
     * @var integer
     */
    protected $time;

    /**
     * @ORM\Column(type="integer", name="NumberOfStudent", nullable=true)
     *
     * @var integer
     */
    protected $numberOfStudent;

    /**
     *
     * @return int
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }

    /**
     *
     * @param int $organizationId            
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;
    }

    /**
     *
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     *
     * @param mixed $organization            
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     *
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     *
     * @param int $year            
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     *
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     *
     * @param int $time            
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     *
     * @return int
     */
    public function getNumberOfStudent()
    {
        return $this->numberOfStudent;
    }

    /**
     *
     * @param int $numberOfStudent            
     */
    public function setNumberOfStudent($numberOfStudent)
    {
        $this->numberOfStudent = $numberOfStudent;
    }
}