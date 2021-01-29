<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller;

class Controller {
	
	protected $_router;
	protected $_di;
	protected $_request;
	protected $config;
	protected $page;
	protected $pageTitle = '';
	
	public function getTitle(){
		return $this->pageTitle;
	}

	public function setRequest($request){
		$this->_request = $request;
		return $this;
	}

	public function getFile($param=null){
		return $this->_request->getFile($param);
	}

	public function getPost($param=null){
		return $this->_request->getPost($param);
	}

	public function getParam($param=null){
		return $this->_request->getParam($param);
	}
	
	public function setDi($di){
		$this->_di = $di;
		return $this;
	}
	
	public function setConfig($config){
		$this->config = $config;
		return $this;
	}
	
	public function setLayout($router){
		$this->_router = $router;
		$layout = new \Of\Html\Layout($router, $this->config);
		$this->page = $layout->run();
		
		return $this;
	}
	
	public function run(){
		if($this->page){
			if(file_exists($this->page)){
				include($this->page);
			}
		}
	}
}

?>