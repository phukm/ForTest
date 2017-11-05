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
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\DoubleEikenMessagesRepository")
 * @ORM\Table(name="DoubleEikenMessages")
 */
class DoubleEikenMessages extends Common
{

    /* Foreing key */
    
    /* Property */
    /**
     * @ORM\Column(type="string", name="Title", length=250, nullable=true)
     *
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(type="string", name="Message", length=500, nullable=true)
     *
     * @var string
     */
    protected $messages;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param string $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }
    /* Relationship */
    
    /* Getter and Setter */


}

?>