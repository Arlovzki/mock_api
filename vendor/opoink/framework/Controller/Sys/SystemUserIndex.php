<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemUserIndex extends Sys {
	
	protected $pageTitle = 'Opoink Account Settings';
	protected $_systemAdmin;
	protected $_user;

	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\Db\Entity\SystemAdmin $SystemAdmin
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->_systemAdmin = $SystemAdmin;
	}

	public function run(){
		$this->requireInstalled();
		$this->requireLogin();

		$this->user = $this->_systemAdmin->getByColumn(['id' => $this->_systemSession->getLogedInUser()]);
		
		$this->addInlineJs();
		return $this->renderHtml();
	}
}