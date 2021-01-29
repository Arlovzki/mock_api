<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemLogoutIndex extends Sys {
	
	public function run(){
		$this->requireInstalled();
		$this->requireLogin();
		
		$this->_systemSession->setAsLogout();
		$this->_message->setMessage('You are now loged out', 'success');
		$this->_url->redirectTo($this->getUrl('/system/login'));
	}
	
}