<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemInstallIndex extends Sys {
	
	protected $pageTitle = 'Install Opoink Framework';
	protected $_systemAdmin;
	
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
		$this->requireNotInstalled();
		return $this->renderHtml();
	}
	
	protected function getSystemAdminAccount(){
		$systemAdmin = null;

		if($this->getDbInfo()){
			try{
				$systemAdmin = $this->_systemAdmin->getByColumn(['id' => 1]);
			} catch (\Exception $e) {
				$systemAdmin = null;
			}
		}
		return $systemAdmin; 
	}
	
	protected function getDbInfo(){
		$target = ROOT . DS . 'etc' . DS . 'database.php';
		if(file_exists($target)){
			return include($target);
		}
	}
}