<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemLoginIndex extends Sys {
	
	protected $pageTitle = 'Opoink Login';
	protected $email = '';
	protected $password = '';
	
	protected $_systemAdmin;
	protected $_password;
	
	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\Std\Password $Password,
		\Of\Db\Entity\SystemAdmin $SystemAdmin
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->_systemAdmin = $SystemAdmin;
		$this->_password = $Password;
		
	}
	
	public function run(){
		$this->requireInstalled();
		$this->requireNotLogin();
		
		$this->email = $this->getParam('email');
		$this->password = $this->getParam('password');
		
		if($this->email != ''){
			if($this->validateGRecaptcha()) {
				$user = $this->_systemAdmin->getByColumn(['email' => $this->email]);
				if($user){
					$verify = $this->_password->setPassword($this->password)->setHash($user->getData('password'))->verify();
					if($verify){
						$this->_systemSession->setAsLogedIn($user->getData('id'));
						$this->_message->setMessage('Welcome back ' . $user->getData('firstname'), 'success');
						$this->_url->redirectTo(
							$this->_systemSession->getReturnUrl()
						);
					} else {
						$this->_message->setMessage('Wrong password.', 'danger');
					}
				} else {
					$this->_message->setMessage('Email not found.', 'danger');
				}
			} else {
				$this->_message->setMessage('Invalid reCaptcha.', 'danger');
			}
		}
		
		$this->addInlineJs();
		return $this->renderHtml();
	}
	
}