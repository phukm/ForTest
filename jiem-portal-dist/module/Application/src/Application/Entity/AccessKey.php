<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\AccessKeyRepository")
 * @ORM\Table(name="AccessKey")
 */
class AccessKey extends Common
{
    /**
     * @ORM\Column(name="OrganizationNo", type="string", length=50, nullable=false)
     */
    protected $organizationNo;
    /**
     * @ORM\Column(type="datetime", name="ExpireDate",nullable=true )
     */
    protected $expireDate;

    /**
     * @ORM\Column(name="AccessKey", type="string", length=50, nullable=true)
     */
    protected $accessKey;

    /**
     * @ORM\Column(type="integer", name="Year",length=4, nullable=false)
     * @var integer
     */
    protected $year;
    /**
     * @ORM\Column(type="string", name="Kai", length=2, nullable=false)
     * @var string
     */
    protected $kai;

    /**
     * @ORM\Column(type="string", name="Description", length=200, nullable=true)
     * @var string
     */
    protected $description;

    /**
     * @return mixed
     */
    public function getOrganizationNo()
    {
        return $this->organizationNo;
    }

    /**
     * @param mixed $organizationNo
     */
    public function setOrganizationNo($organizationNo)
    {
        $this->organizationNo = $organizationNo;
    }

    /**
     * @return mixed
     */
    public function getExpireDate()
    {
        return $this->expireDate;
    }

    /**
     * @param mixed $expireDate
     */
    public function setExpireDate($expireDate)
    {
        $this->expireDate = $expireDate;
    }

    /**
     * @return mixed
     */
    public function getAccessKey()
    {
        return $this->accessKey;
    }

    /**
     * @param mixed $accessKey
     */
    public function setAccessKey($accessKey)
    {
        $this->accessKey = $accessKey;
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
     * @return string
     */
    public function getKai()
    {
        return $this->kai;
    }

    /**
     * @param string $kai
     */
    public function setKai($kai)
    {
        $this->kai = $kai;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

}