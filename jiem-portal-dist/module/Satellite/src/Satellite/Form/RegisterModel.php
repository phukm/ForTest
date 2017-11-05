<?php
namespace Satellite\Form;
/**
 * @author ThanhNX6
 *
 */
class RegisterModel {
	
	private $emailaddress;
	private $password;
	private $password2;
	private $firstname;
	private $lastname;
	private $sex;
	private $year;
	private $month;
	private $day;
	private $postcode1;
	private $postcode2;
	private $receive;
	private $agree;
	
	function setEmailaddress($emailaddress) {
		$this->emailaddress = $emailaddress;
	}
	function getEmailaddress() {
		return $this->emailaddress;
	}
	function setPassword($password) {
		$this->password = $password;
	}
	function getPassword() {
		return $this->password;
	}
	function setPassword2($password2) {
		$this->password2 = $password2;
	}
	function getPassword2() {
		return $this->password2;
	}
	function setFirstname($firstname) {
		$this->firstname = $firstname;
	}
	function getFirstname() {
		return $this->firstname;
	}
	function setLastname($lastname) {
		$this->lastname = $lastname;
	}
	function getLastname() {
		return $this->lastname;
	}
	function setSex($sex) {
		$this->sex = $sex;
	}
	function getSex() {
		return $this->sex;
	}
	function setYear($year) {
		$this->year = $year;
	}
	function getYear() {
		return $this->year;
	}
	function setMonth($month) {
		$this->month = $month;
	}
	function getMonth() {
		return $this->month;
	}
	function setDay($day) {
		$this->day = $day;
	}
	function getDay() {
		return $this->day;
	}
	function setPostcode1($postcode1) {
		$this->postcode1 = $postcode1;
	}
	function getPostcode1() {
		return $this->postcode1;
	}
	function setPostcode2($postcode2) {
		$this->postcode2 = $postcode2;
	}
	function getPostcode2() {
		return $this->postcode2;
	}
	function setReceive($receive) {
		$this->receive = $receive;
	}
	function getReceive() {
		return $this->receive;
	}
	function setAgree($agree) {
		$this->agree = $agree;
	}
	function getAgree() {
		return $this->agree;
	}
}

?>