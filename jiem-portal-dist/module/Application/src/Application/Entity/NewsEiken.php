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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\NewsEikenRepository")
 * @ORM\Table(name="NewsEiken")
 */
class NewsEiken extends Common
{
    /**
     *
     * @ORM\Column(type="datetime", name="NewsDate", nullable=true)
     *
     * @var datetime
     */
    protected $newsDate;
    /**
     * @ORM\Column(type="string", name="Description", length=500, nullable=true)
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(type="string", name="Url", length=255, nullable=true)
     *
     * @var string
     */
    protected $url;
    /**
     * @ORM\Column(type="string", name="Type", length=10, nullable=true)
     *
     * @var string
     */
    protected $type;
    
    public function getNewsDate() {
        return $this->newsDate;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getType() {
        return $this->type;
    }

    public function setNewsDate(datetime $newsDate) {
        $this->newsDate = $newsDate;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setUrl($url) {
        $this->url = $url;
    }
    public function setType($type) {
        $this->type = $type;
    }

}