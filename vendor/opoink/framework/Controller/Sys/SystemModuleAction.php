<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemModuleAction extends Sys {
	
	protected $pageTitle = 'Opoink Module Action';
	protected $_modManager;
	
	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\ModManager\Module $modManager
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->_modManager = $modManager;
	}
	
	public function run(){
		$this->requireInstalled();
		$this->requireLogin();
		
		$validate = $this->validateFormKey();
		if($validate){
			$intalledModule = $this->_request->getParam('intalledModule');
			$upgradeds = $this->_modManager->setDi($this->_di)->moduleAction();
			
			$this->_message->setMessage('Module successfully updated', 'success');
		} else {
			$this->_message->setMessage('Invalid request.', 'danger');
		}
		
		$this->_url->redirectTo($this->getUrl('/system/module'));
	}
}