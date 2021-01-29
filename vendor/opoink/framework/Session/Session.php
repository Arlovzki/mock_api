<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Session;

class Session {
	
	public function __construct(){
		if(session_status() == PHP_SESSION_NONE) {
			session_start();
		}
	}
	
	public function destroy(){
		session_destroy();
	}
	
	public function setData($key, $val){
		$_SESSION[$key] = $val;
		return $this;
	}
	
	public function getData($key=null){
		if($key){
			if(isset($_SESSION[$key])){
				return $_SESSION[$key];
			} else {
				return null;
			}
		} else {
			return $_SESSION;
		}
	}
	
	public function unsetData($key){
		if(isset($_SESSION[$key])){
			unset($_SESSION[$key]);
		}
	}
}
?>