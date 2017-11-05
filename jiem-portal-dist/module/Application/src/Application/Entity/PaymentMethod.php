<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Entity\Repository\PaymentMethodRepository")
 * @ORM\Table(name="PaymentMethod")
 */
class PaymentMethod extends Common
{
    /**
     * Foreign key reference to Organization
     * @ORM\Column(type="integer", name="OrganizationId", nullable=true)
     * 
     * @var integer
     */
    protected $organizationId;
    
    /**
     * Foreign key reference to EikenSchedule
     * @ORM\Column(type="integer", name="EikenScheduleId", nullable=true)
     *
     * @var integer
     */
    protected $eikenScheduleId;
    
    /**
     * Foreign key reference to ApplyEikenOrg
     * @ORM\Column(type="integer", name="ApplyEikenOrgId", nullable=true)
     *
     * @var integer
     */
    protected $applyEikenOrgId;
    
    /**
     * Foreign key reference to InvitationSetting
     * @ORM\Column(type="integer", name="InvitationSettingId", nullable=true)
     *
     * @var integer
     */
    protected $invitationSettingId;
    
    /**
     * @ORM\Column(type="integer", name="PaymentBill",  nullable=true)
     * @var integer
     */
    protected $paymentBill;
    
    /**
     * @ORM\Column(type="integer", name="PublicFunding",  nullable=true)
     * @var integer
     */
    protected $publicFunding;
    
    /* Relationship */
    /**
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(name="OrganizationId", referencedColumnName="id")
     */
    protected $organization;

    /**
     * @ORM\ManyToOne(targetEntity="EikenSchedule")
     * @ORM\JoinColumn(name="EikenScheduleId", referencedColumnName="id")
     */
    protected $eikenSchedule;
    
    /**
     * @ORM\ManyToOne(targetEntity="ApplyEikenOrg")
     * @ORM\JoinColumn(name="ApplyEikenOrgId", referencedColumnName="id")
     */
    protected $applyEikenOrg;
    
    /**
     * @ORM\ManyToOne(targetEntity="InvitationSetting")
     * @ORM\JoinColumn(name="InvitationSettingId", referencedColumnName="id")
     */
    protected $invitationSetting;
    
    /**
     * @return int
     */
    function getOrganizationId() {
        return $this->organizationId;
    }

    /**
     * @return int
     */
    function getEikenScheduleId() {
        return $this->eikenScheduleId;
    }

    /**
     * @return int
     */
    function getApplyEikenOrgId() {
        return $this->applyEikenOrgId;
    }

    /**
     * @return int
     */
    function getInvitationSettingId() {
        return $this->invitationSettingId;
    }

        
    /**
     * @return int
     */
    function getPaymentBill() {
        return $this->paymentBill;
    }
    
    /**
     * @return int
     */
    function getPublicFunding() {
        return $this->publicFunding;
    }

    /**
     * @return mixed
     */
    function getOrganization() {
        return $this->organization;
    }

    /**
     * @return mixed
     */
    function getEikenSchedule() {
        return $this->eikenSchedule;
    }

    /**
     * @return mixed
     */
    function getApplyEikenOrg() {
        return $this->applyEikenOrg;
    }

    /**
     * @return mixed
     */
    function getInvitationSetting() {
        return $this->invitationSetting;
    }

        
    /**
     * @param mixed $organizationId
     */
    function setOrganizationId($organizationId) {
        $this->organizationId = $organizationId;
    }

    /**
     * @param mixed $eikenScheduleId
     */
    function setEikenScheduleId($eikenScheduleId) {
        $this->eikenScheduleId = $eikenScheduleId;
    }

    /**
     * @param mixed $applyEikenOrgId
     */
    function setApplyEikenOrgId($applyEikenOrgId) {
        $this->applyEikenOrgId = $applyEikenOrgId;
    }

    /**
     * @param mixed $invitationSettingId
     */
    function setInvitationSettingId($invitationSettingId) {
        $this->invitationSettingId = $invitationSettingId;
    }

        
    /**
     * @param int $paymentBill
     */
    function setPaymentBill($paymentBill) {
        $this->paymentBill = $paymentBill;
    }

    /**
     * @param int $publicFunding
     */
    function setPublicFunding($publicFunding) {
        $this->publicFunding = $publicFunding;
    }
       
    /**
     * @param mixed $organization
     */
    function setOrganization($organization) {
        $this->organization = $organization;
    }

    /**
     * @param mixed $eikenSchedule
     */
    function setEikenSchedule($eikenSchedule) {
        $this->eikenSchedule = $eikenSchedule;
    }
    
    /**
     * @param mixed $applyEikenOrg
     */
    function setApplyEikenOrg($applyEikenOrg) {
        $this->applyEikenOrg = $applyEikenOrg;
    }

    /**
     * @param mixed $invitationSetting
     */
    function setInvitationSetting($invitationSetting) {
        $this->invitationSetting = $invitationSetting;
    }


}