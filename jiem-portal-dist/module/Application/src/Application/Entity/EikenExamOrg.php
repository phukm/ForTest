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
class EikenExamOrg extends Common
{

    
    /**
     * @ORM\Column(type="integer", name="orgId", nullable=true)
     *
     * @var integer
     */
    protected $orgId;
    
    /**
     * @ORM\Column(type="integer", name="scheId", nullable=true)
     *
     * @var integer
     */
    protected $scheId;
    
    /**
     * @ORM\Column(type="string", name="examDate", nullable=true)
     *
     * @var integer
     */
    protected $examDate;
    
    /**
     * @ORM\Column(type="string", name="examExpire", nullable=true)
     *
     * @var integer
     */
    protected $examExpire;
    
    /**
     * @ORM\Column(type="string", name="examName", nullable=true)
     *
     * @var integer
     */
    protected $examName;
    
    /**
     * @ORM\Column(type="integer", name="examYear", nullable=true)
     *
     * @var integer
     */
    protected $examYear;
    
    /**
     * @ORM\Column(type="string", name="examKai", nullable=true)
     *
     * @var integer
     */
    protected $examKai;
    
    /**
     * @ORM\Column(type="integer", name="examTotal", nullable=true)
     *
     * @var integer
     */
    protected $examTotal;
    
    /**
     * @ORM\Column(type="string", name="examMapping", nullable=true)
     *
     * @var integer
     */
    protected $examMapping;
    
        /**
     * @ORM\Column(type="string", name="examImporting", nullable=true)
     *
     * @var integer
     */
    protected $examImporting;
    

    /**
     * @ORM\Column(type="string", name="examMoshikomiId", nullable=true)
     *
     * @var integer
     */
    protected $examMoshikomiId;
    
    /**
     * @ORM\Column(type="string", name="MoshikomiId", length=100, nullable=true)
     *
     * @var string
     */
    protected $moshikomiId;

    /**
     * @ORM\Column(type="string", name="SetName", length=100, nullable=true)
     *
     * @var string
     */
    protected $setName;

    /**
     * @ORM\Column(type="integer", name="HasNewData", nullable=true)
     *
     * @var integer
     */
    protected $hasNewData;

    /**
     * @ORM\Column(type="string", name="JisshiId", length=100, nullable=true)
     *
     * @var string
     */
    protected $jisshiId;

    /**
     * @ORM\Column(type="string", name="ExamType", length=100, nullable=true)
     *
     * @var string
     */
    protected $examType;
}