<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemLoginResetpassword extends Sys {
	
	protected $pageTitle = 'Opoink Reset Password';
	
	protected $_systemAdmin;
	protected $_password;
	protected $_mailer;
	protected $_jwt;
	
	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\Std\Password $Password,
		\Of\Db\Entity\SystemAdmin $SystemAdmin,
		\Of\Std\Mailer $Mailer,
		\Of\Token\Jwt $Jwt
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->_systemAdmin = $SystemAdmin;
		$this->_password = $Password;
		$this->_mailer = $Mailer;
		$this->_jwt = $Jwt;
	}
	
	public function run(){
		$this->requireInstalled();
		$this->requireNotLogin();

		$token = $this->_jwt
		->setSecret($this->_config['auth']['secret'])
		->validateToken($this->getParam('token'));
		if($token){
			$user = $this->_systemAdmin->getByColumn(['id' => $token['jti']]);
			if($user){
				$pass = $this->_password::generate(11);

				$hashPass = $this->_password->setPassword($pass)->getHash();

				$user->setData('password', $hashPass);

				if($user->__save() > 0){
					$messageTemplate = "Hi " . $user->getData('firstname') . "! <br>";
					$messageTemplate .= "<br><br>";
					$messageTemplate .= "Your new password is " . $pass;
					$messageTemplate .= "<br><br>";
					$messageTemplate .= "Please take note the this was a computer generated password, and must be changed after login";
					$messageTemplate .= "<br><br><br>";
					$messageTemplate .= "Thanks! <br>";
					$messageTemplate .= "Opoink";
					
					$this->_mailer->addAddress($user->getData('email'), $user->getData('firstname'), 'To')
					->setFrom('support@opoink.com', 'support')
					->setSubject('Opoink system admin password retrieved')
					->setTemplatePath(ROOT.'/vendor/opoink/framework/View/Sys/Templates/mail/default.phtml')
					->setMessage($messageTemplate)
					->send();

					$this->_message->setMessage('Your new password was sent to ' . $user->getData('email'), 'success');
				} else {
					$this->_message->setMessage('Can\'t save new password now please try again later.', 'danger');
				}
			} else {
				$this->_message->setMessage('User not found.', 'danger');
			}
		} else {
			$this->_message->setMessage('Invalid request.', 'danger');
		}

		$this->_url->redirect('/system'.$this->_config['system_url'].'/login');
	}
	
}