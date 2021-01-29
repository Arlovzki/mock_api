<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Http;

class Request {
	
	protected $params = [];
	protected $server = [];
	
	public function __construct(){
		$this->init();
	}
	
	/*
	*	store all request param GET and POST
	*	into @$param array
	*/
	protected function init(){
		foreach($_GET as $key => $val){
			$this->params[$key] = $val;
		}
		foreach($_POST as $key => $val){
			$this->params[$key] = $val;
		}
		$this->server = $_SERVER;
	}

	public function getMethod(){
		if(isset($this->server['REQUEST_METHOD'])){
			return $this->server['REQUEST_METHOD'];
		}
	}

	public function getFile($param=null){
		if($param){
			if(isset($_FILES[$param])){
				return $_FILES[$param];
			} else {
				return null;
			}
		} else {
			return $_FILES;
		}
	}

	public function getPost($param=null){
		if($param){
			if(isset($_POST[$param])){
				if(is_array($_POST[$param])){
					return $_POST[$param];
				} else {
					return trim($_POST[$param]);
				}
			} else {
				return null;
			}
		} else {
			return $_POST;
		}
	}
	
	public function getParam($param=null){
		if($param){
			if(isset($this->params[$param])){
				if(is_array($this->params[$param])){
					return $this->params[$param];
				} else {
					return trim($this->params[$param]);
				}
			} else {
				return null;
			}
		} else {
			return $this->params;
		}
	}
	
	public function getServer($param=null){
		if($param){
			if(isset($this->server[$param])){
				return $this->server[$param];
			} else {
				return null;
			}
		} else {
			return $this->server;
		}
	}

	public function getClientIp(){
		$ip = '';
		if (!empty($this->server['HTTP_CLIENT_IP'])) {
			$ip = $this->server['HTTP_CLIENT_IP'];
		} elseif (!empty($this->server['HTTP_X_FORWARDED_FOR'])) {
			$ip = $this->server['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $this->server['REMOTE_ADDR'];
		}
		return $ip;
	}
}
?>