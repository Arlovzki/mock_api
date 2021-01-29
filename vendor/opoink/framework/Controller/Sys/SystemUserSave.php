<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemUserSave extends Sys {
	
	protected $pageTitle = 'Opoink Account Settings';
	protected $_systemAdmin;
	protected $_password;
	protected $_user;

	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\Db\Entity\SystemAdmin $SystemAdmin,
		\Of\Std\Password $Password
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->_systemAdmin = $SystemAdmin;
		$this->_password = $Password;
	}

	public function run(){
		$this->requireInstalled();
		$this->requireLogin();
		
		$validate = $this->validateFormKey();
		
		if($validate){
			$this->user = $this->_systemAdmin->getByColumn(['id' => $this->_systemSession->getLogedInUser()]);
			if($this->user){
				$email = $this->_request->getParam('email');
				$firstname = $this->_request->getParam('firstname');
				$lastname = $this->_request->getParam('lastname');
				$new_password = $this->_request->getParam('new_password');
				$retype_new_password = $this->_request->getParam('retype_new_password');
				$current_password = $this->_request->getParam('current_password');

				if($firstname == null || empty($firstname)){
					$this->_message->setMessage('First name is required.', 'danger');
				}
				elseif($lastname == null || empty($lastname)){
					$this->_message->setMessage('Last name is required.', 'danger');
				}
				elseif($email == null || empty($email)){
					$this->_message->setMessage('Email is required.', 'danger');
				} else {
					$this->user->setData('firstname', $firstname);
					$this->user->setData('lastname', $lastname);
					if($email != $this->user->getData('email')){
						$emailExist = $this->_systemAdmin->getByColumn(['email' => $email]);
						if(!$emailExist){
							$this->user->setData('email', $email);
						} else {
							$this->_message->setMessage('Email '.$email.' already exist.', 'danger');
						}
					}

					$verify = $this->_password->setPassword($current_password)->setHash($this->user->getData('password'))->verify();
					if($verify && !empty($retype_new_password) && !empty($new_password)){
						if($retype_new_password === $new_password){
							$password = $this->_password->setPassword($new_password)->getHash();
							$this->user->setData('password', $password);
						} else {
							$this->_message->setMessage('Password did not match.', 'danger');
						}
					}

					$this->user->__save();
					$this->_message->setMessage('Info uccessfully updated.', 'success');
				}
			} else {
				$this->_message->setMessage('User not found.', 'danger');
			}
		} else {
			$this->_message->setMessage('Invalid request.', 'danger');
		}
		
		$this->_url->redirectTo($this->getUrl('/system/user'));
	}
}