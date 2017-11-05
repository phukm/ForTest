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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\FileDownloadRepository")
 * @ORM\Table(name="FileDownload")
 */
class FileDownload extends Common
{
    /**
     * @ORM\Column(type="string", name="OrganizationNo", length=50, nullable=true)
     *
     * @var string
     */
    protected $organizationNo;

    /**
     * @ORM\Column(type="integer", name="EikenScheduleId",length=4, nullable=false)
     * @var integer
     */

    protected $eikenScheduleId;

    /**
     * @ORM\Column(type="integer", name="Year",length=4, nullable=false)
     * @var integer
     */

    protected $year;
    /**
     * @ORM\Column(type="integer", name="Kai", length=2, nullable=false)
     * @var integer
     */
    protected $kai;

    /**
     * @ORM\Column(type="string", name="Type", length=20, nullable=false)
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(type="string", name="Filename", length=50, nullable=false)
     * @var string
     */
    protected $filename;

    /**
     * @ORM\Column(type="datetime", name="StartDate", options={"default":"CURRENT_TIMESTAMP"})
     */
    protected $startDate;

    /**
     * @ORM\Column(type="datetime", name="EndDate", options={"default":"CURRENT_TIMESTAMP"})
     */
    protected $endDate;

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
     * @return string
     */
    public function getOrganizationNo()
    {
        return $this->organizationNo;
    }

    /**
     * @param string $organizationNo
     */
    public function setOrganizationNo($organizationNo)
    {
        $this->organizationNo = $organizationNo;
    }

    /**
     * @return int
     */
    public function getEikenScheduleId()
    {
        return $this->eikenScheduleId;
    }

    /**
     * @param int $eikenScheduleId
     */
    public function setEikenScheduleId($eikenScheduleId)
    {
        $this->eikenScheduleId = $eikenScheduleId;
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
    public function getKai()
    {
        return $this->kai;
    }

    /**
     * @param int $kai
     */
    public function setKai($kai)
    {
        $this->kai = $kai;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }


}