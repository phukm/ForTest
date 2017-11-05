<?php
namespace Satellite\Form;
/**
 * @author ThanhNX6
 *
 */
class LoginEinaviModel {
	
	private $emailaddress;
	private $password;
	
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
}

?>