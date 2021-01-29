<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemLoginForgetpassword extends Sys {
	
	protected $pageTitle = 'Opoink Login';
	protected $email = '';
	protected $password = '';
	
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
		
		$this->email = $this->getParam('email');
		
		if($this->email != ''){
			if($this->validateGRecaptcha()) {
				$user = $this->_systemAdmin->getByColumn(['email' => $this->email]);
				if($user){
					$this->_jwt->setIssuer($this->_url->getUrl())
					->setAudience($user->getData('firstname') . ' ' . $user->getData('lastname'))
					->setExpiration(time() + (60*60*24))
					->setId($user->getData('id'))
					->setIssuedAt($this->_url->getDomain())
					->setSubject('Forgot system password')
					->setSecret($this->_config['auth']['secret']);

					$messageTemplate = "Hi " . $user->getData('firstname') . "! <br>";
					$messageTemplate .= "<br><br>";
					$messageTemplate .= "You recently request to reset you password for your system administrator's account at " . $this->_url->getDomain();
					$messageTemplate .= "<br><br>";
					$messageTemplate .= "A computer generated password will be mailed to you after clicking the link below.";
					$messageTemplate .= "<br><br>";

					$resetLink = $this->_url->getUrl('/system'.$this->_config['system_url'].'/login/resetpassword', ['token'=>$this->_jwt->getToken()]);

					$messageTemplate .= '<a href="'.$resetLink.'">'.$resetLink.'</a>';
					$messageTemplate .= "<br><br>";
					$messageTemplate .= "If you did not request for a password reset, please ignore this email.";
					$messageTemplate .= "<br><br><br>";
					$messageTemplate .= "Thanks! <br>";
					$messageTemplate .= "Opoink";
					
					$this->_mailer->addAddress($user->getData('email'), $user->getData('firstname'), 'To')
					->setFrom('support@opoink.com', 'support')
					->setSubject('Opoink system admin password request')
					->setTemplatePath(ROOT.'/vendor/opoink/framework/View/Sys/Templates/mail/default.phtml')
					->setMessage($messageTemplate)
					->send();
					$this->_message->setMessage('An instruction was sent to ' . $user->getData('email'), 'success');
				} else {
					$this->_message->setMessage('Email not found.', 'danger');
				}
			} else {
				$this->_message->setMessage('Invalid reCaptcha.', 'danger');
			}
		}

		$this->_url->redirect('/system'.$this->_config['system_url'].'/login');
	}
	
}