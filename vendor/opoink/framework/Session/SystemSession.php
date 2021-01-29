<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Session;

class SystemSession extends Session {
	
	protected $sessionKey = 'sys';
	
	public function __construct(){
		parent::__construct();
		if(!isset($_SESSION[$this->sessionKey])) {
			$_SESSION[$this->sessionKey] = [];
		}
	}
	
	public function getLogedInUser(){
		$user_id = 0;
		if(isset($_SESSION[$this->sessionKey]['user_id'])) {
			$user_id = $_SESSION[$this->sessionKey]['user_id'];
		}
		return $user_id;
	}
	
	public function isLogedIn(){
		return (bool)$this->getLogedInUser();
	}
	
	public function setAsLogedIn($userId){
		$_SESSION[$this->sessionKey]['user_id'] = $userId;
	}
	
	public function setReturnUrl($url){
		$_SESSION[$this->sessionKey]['return_url'] = $url;
	}
	
	public function getReturnUrl(){
		if(isset($_SESSION[$this->sessionKey]['return_url'])){
			return $_SESSION[$this->sessionKey]['return_url'];
		}
	}
	
	public function setAsLogout(){
		$_SESSION[$this->sessionKey] = [];
	}
}
?>