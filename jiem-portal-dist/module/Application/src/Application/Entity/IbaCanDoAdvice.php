<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\IbaCanDoAdviceRepository")
 * @ORM\Table(name="IbaCanDoAdvice")
 */
class IbaCanDoAdvice extends Common
{
    /**
     * @var string
     * @ORM\Column(type="string", name="IbaLevelName", nullable=true)
     */
    protected $ibaLevelName;
    
    
    /**
     * @var string
     * @ORM\Column(type="string", name="Type", nullable=true)
     */
    protected $type;
    
    
    /**
     * @var string
     * @ORM\Column(type="string", name="Reading",length=500, nullable=true)
     */
    protected $reading;
    
    
    /**
     * @var string
     * @ORM\Column(type="string", name="Listening",length=500, nullable=true)
     */
    protected $listening;
    
    
    /**
     * @var string
     * @ORM\Column(type="string", name="Vocab",length=500, nullable=true)
     */
    protected $vocab;
    
    
    /**
     * @return string
     */
    public function getIbaLevelName()
    {
        return $this->ibaLevelName;
    }
    
    /**
     * @param string $ibaLevelName
     */
    public function setIbaLevelName($ibaLevelName)
    {
        $this->ibaLevelName = $ibaLevelName;
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
    public function getReading()
    {
        return $this->reading;
    }
    
    /**
     * @param string $reading
     */
    public function setReading($reading)
    {
        $this->reading = $reading;
    }
    
    /**
     * @return string
     */
    public function getListening()
    {
        return $this->listening;
    }
    
    /**
     * @param string $listening
     */
    public function setListening($listening)
    {
        $this->listening = $listening;
    }
    
    /**
     * @return string
     */
    public function getVocab()
    {
        return $this->vocab;
    }
    
    /**
     * @param string $vocab
     */
    public function setVocab($vocab)
    {
        $this->vocab = $vocab;
    }
}


