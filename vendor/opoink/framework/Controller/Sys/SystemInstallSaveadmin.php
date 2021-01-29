<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

use Of\Std\Password;

class SystemInstallSaveadmin extends Sys {
	
	protected $pageTitle = 'Save Admin';
	
	protected $_systemAdmin;
	protected $_password;
	
	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\Db\Entity\SystemAdmin $SystemAdmin,
		Password $Password
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->_systemAdmin = $SystemAdmin;
		$this->_password = $Password;
	}	
	
	public function run(){
		$this->requireNotInstalled();
		$response = [
			'error' => 1,
			'message' => ''
		];
		
		$validate = $this->validateFormKey();
		
		if($this->validateFormKey()){
			$postFields = $this->getParam();
			$requiredFields = ['firstname', 'lastname', 'email', 'password', 'retypepassword'];
			$validateFields = $this->validateRequiredField($postFields, $requiredFields);
			if($validateFields['error'] == 0){
				$id = (int)$this->getParam('id');
				$firstname = $this->getParam('firstname');
				$lastname = $this->getParam('lastname');
				$email = $this->getParam('email');
				$password = $this->getParam('password');
				$retypepassword = $this->getParam('retypepassword');
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$response['message'] = 'invalidemail';
				} else {
					if(!$this->_password->confirmPassword($password, $retypepassword)){
						$response['message'] = 'retypepasswordnotmatch';
					} else {
						if($id > 0){
							$this->_systemAdmin->setData('id', 1);
						}
						$this->_systemAdmin->setData('firstname', $firstname);
						$this->_systemAdmin->setData('lastname', $lastname);
						$this->_systemAdmin->setData('email', $email);
						$this->_systemAdmin->setData('password', $this->_password->setPassword($password)->getHash());
						$save = $this->_systemAdmin->__save();
						
						if($save > 0) {
							$response['error'] = 0;
							$response['message'] = 'System admin account saved.';
						} else {
							$response['error'] = 1;
							$response['message'] = 'Error, please try again.';
						}
					}
				}
			} else {
				$response['message'] = $validateFields['message'];
			}
		} else {
			$response['message'] = 'Invalid request';
		}
		$this->jsonEncode($response);
	}
	

}