<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Application\Entity;

use Application\Entity\Common;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="SystemConfig")
 */
class SystemConfig extends Common
{

    /* Foreing key */

    /* Property */
    /**
     * @ORM\Column(type="string", name="ConfigKey", length=100, nullable=false)
     *
     * @var string
     */
    protected $configKey;

    /**
     * @ORM\Column(type="text", name="ConfigValue", nullable=false)
     *
     * @var string
     */
    protected $configValue;

    /**
     * @ORM\Column(type="string", name="Description", length=1000, nullable=true)
     *
     * @var string
     */
    protected $description;

    /**
     * @return string
     */
    public function getConfigKey()
    {
        return $this->configKey;
    }

    /**
     * @param string $configKey
     */
    public function setConfigKey($configKey)
    {
        $this->configKey = $configKey;
    }

    /**
     * @return string
     */
    public function getConfigValue()
    {
        return $this->configValue;
    }

    /**
     * @param string $configValue
     */
    public function setConfigValue($configValue)
    {
        $this->configValue = $configValue;
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

    /* Relationship */

    /* Getter and Setter */

}
