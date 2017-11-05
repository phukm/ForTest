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
 * @ORM\Entity
  */
class EikenExamHistory extends Common
{
    /**
     * @ORM\Column(type="string", name="pupilId", nullable=true)
     *
     * @var integer
     */
    protected $pupilId;

    /**
     * @ORM\Column(type="string", name="eikenLevelId", nullable=true)
     *
     * @var integer
     */
    protected $eikenLevelId;

    /**
     * @ORM\Column(type="string", name="ibaLevelId", nullable=true)
     *
     * @var integer
     */
    protected $ibaId;

    /**
     * @ORM\Column(type="string", name="ibaId", nullable=true)
     *
     * @var integer
     */
    protected $ibaLevelId;

    /**
     * @ORM\Column(type="string", name="historyYear", nullable=true)
     *
     * @var integer
     */
    protected $historyYear;

    /**
     * @ORM\Column(type="string", name="schoolYearName", nullable=true)
     *
     * @var integer
     */
    protected $schoolYearName;

    /**
     * @ORM\Column(type="string", name="schoolYearCode", nullable=true)
     *
     * @var integer
     */
    protected $schoolYearCode;

    /**
     * @ORM\Column(type="string", name="className", nullable=true)
     *
     * @var integer
     */
    protected $className;

    /**
     * @ORM\Column(type="string", name="classCode", nullable=true)
     *
     * @var integer
     */
    protected $classCode;

    /**
     * @ORM\Column(type="string", name="pupilNo", nullable=true)
     *
     * @var integer
     */
    protected $pupilNo;

    /**
     * @ORM\Column(type="string", name="pupilName", nullable=true)
     *
     * @var integer
     */
    protected $pupilName;

    /**
     * @ORM\Column(type="string", name="type", nullable=true)
     *
     * @var integer
     */
    protected $type;
    
    /**
     * @ORM\Column(type="string", name="testType", nullable=true)
     *
     * @var string
     */
    protected $testType;
    
    /**
     * @ORM\Column(type="string", name="total", nullable=true)
     *
     * @var integer
     */
    protected $total;

}