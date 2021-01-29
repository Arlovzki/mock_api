<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Http;

class Url {
	
	protected $ssl;
	
	/*
	*	server protocol
	*/
	protected $sp;
	/*
	*	protocol
	*/
	protected $protocol;
	protected $port;
	protected $server;
	protected $host;
	protected $deployTime;
	
	protected $_router;
	
	public function __construct() {
		$config = ROOT.DS.'public'.DS.'generation.php';
		$this->setUrl();
	}
	
	public function setRouter($Router){
		$this->_router = $Router;
		return $this;
	}	
	
	public function setUrl($use_forwarded_host=false){
		$this->server = $_SERVER;
		$this->ssl = (!empty($this->server['HTTPS']) && $this->server['HTTPS'] == 'on' );
		$this->sp = strtolower($this->server['SERVER_PROTOCOL']);
		$this->protocol = substr($this->sp, 0, strpos($this->sp, '/') ) . ( ( $this->ssl ) ? 's' : '' );
		$port = $this->server['SERVER_PORT'];
		$this->port = $port = ( ( ! $this->ssl && $port=='80' ) || ( $this->ssl && $port=='443' ) ) ? '' : ':'.$port;
		
		$this->host = ( $use_forwarded_host && isset( $this->server['HTTP_X_FORWARDED_HOST'] ) ) ? $this->server['HTTP_X_FORWARDED_HOST'] : ( isset( $this->server['HTTP_HOST'] ) ? $this->server['HTTP_HOST'] : null );
		$this->host = isset( $host ) ? $host : $this->server['SERVER_NAME'] . $port;
	}
	
	public function getProtocol(){
		return $this->protocol;
	}
	
	public function getCurrent(){
		return $this->protocol."://".$this->host.$this->server['REQUEST_URI']; 
	}

	protected function getBaseUrl(){
		return $this->protocol.'://'.$this->host;
	}
	
	public function getDomain(){
		return $this->host;
	}
	
	/*
	*	@path the uri path
	*	@param query parameters
	*/
	public function getUrl($path='', $param=array()){
		$baseUrl = $this->getBaseUrl();
		$baseUrl .= $path;
		if(count($param) >= 1){
			$baseUrl .= '?' . http_build_query($param);
		}
		return $baseUrl;
	}
	
	/* 
	*	replace the string "/admin" in $path
	*	with the route set in config
	*	return string admin url
	*/
	public function getAdminUrl($path='', $param=array()){
		$p = ltrim($path, '/');
		$p = '/' . $this->_router->getAdminRoute() . '/' . $p;
		return $this->getUrl($p, $param);
	}
	
	public function getStaticUrl($path){
		$deployDir = $this->getDeployTime();
		$baseUrl = $this->getBaseUrl().'/public/'.$deployDir;
		$baseUrl .= $path;
		return $baseUrl;
	}
	
	/*
	*	return deploy concat with generated time
	*/
	protected function getDeployTime(){
		if(!$this->deployTime){
			$targetFile = \Of\Constants::GENERATION_TIME;
			$deployTime = include($targetFile);
			
			$this->deployTime = 'deploy'.$deployTime;
		}
		
		return $this->deployTime;
	}	
	
	/*
	*	@path the uri path
	*	@param query parameters
	*/
	public function redirect($path='', $param=array()){
		$url = $this->getUrl($path, $param);
		if($this->_router){
			if($this->_router->getAdminRoute()){
				$url = $this->getAdminUrl($path, $param);
			}
		}
		$this->redirectTo($url);
	}
	
	/*
	*	@url direct url where to redirect
	*/
	public function redirectTo($url){
		header("Location: " . $url);
		exit;
		die;
	}
}
?>