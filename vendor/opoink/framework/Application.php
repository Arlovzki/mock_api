<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of;

use Of\Route\Router;
use Of\Http\Request;

class Application {
	
	/*
	*	PHP Dependency injector
	*/
	protected $_di;
	
	/*
	*	class \Of\Route\Router
	*	router class
	*/
	protected $_router;
	
	/*
	*	class \Of\Http\Request
	*	http request post query 
	*/
	protected $_request;
	
	/*
	*	string 
	*	html content 
	*/
	protected $content;
	
	protected $config;
	
	public function __construct($Router=null, $Request=null, $config=null){
		$this->_di = new \Of\Std\Di();
		
		$this->_router = $Router;
		$this->_request = $Request;
		$this->config = $config;
		$this->init();
	}
	
	/*
	*	initialize the app
	*/
	private function init(){
		if($this->_router->validateDomain()){
			$controller = $this->_router->getControllerClass();
			$this->prepareController($controller);
		} else {
			$this->error(404);
		}
	}
	
	/*
	*	prepare page controller
	*	check if the request url has existing controller set at set at /etc/config.php
	*	if controller is not set, check if the request is supported, like css, js, images, etc
	*	set the html content
	*/
	protected function prepareController($controller){
		ob_start();
			$this->checkInstall();
			if($controller){
				$sysRoute = 'system';
				if(isset($this->config['system_url'])){
					$sysRoute .= $this->config['system_url'];
				}
				if($this->_router->getRoute(false) === $sysRoute){
					$controller = $this->_di->get($controller)
					->setRouter($this->_router)
					->setDi($this->_di)
					->setConfig($this->config)
					->run();
				} else {
					$targetClass = ROOT .DS.'App'.DS.'Ext' . DS . ltrim(str_replace('\\', DS, $controller), DS) . '.php';
					if(file_exists($targetClass)){
						$controller = $this->_di->get($controller)
						->setConfig($this->config)
						->setRequest($this->_request)
						->setLayout($this->_router)
						->setDi($this->_di)
						->run();
					} else {
						$this->error(404);
					}
				}
			} else {
				$controller = $this->isSupportedFile();
				if(!$controller){
					$this->error(404);
				}
			}
			$this->content = ob_get_contents();
		ob_end_clean();
	}
	
	/*
	*	check whether the system was installed or not
	*	redirect to installation page if not
	*/
	protected function checkInstall(){
		$installFlag = ROOT.DS.'etc'.DS.'install_flag.php';
		if(!file_exists($installFlag)){
			$route = $this->_router->getRoute(false);
			$controller = $this->_router->getController(false);
			
			$goToInstallation = false;
			
			if($route != 'system' && $controller != 'install'){
				$goToInstallation = true;
			} elseif($route == 'system' && $controller != 'install'){
				$goToInstallation = true;
			}
			
			if($goToInstallation){
				$url = new \Of\Http\Url();
				$url->redirect('/system/install');
			}
		}
	}
	
	/*
	*	look for the request extension
	*	if spported or not
	*	return the content if supported
	*/
	protected function isSupportedFile(){
		if(isset($_SERVER['REQUEST_URI'])){
			$requestUri = $_SERVER['REQUEST_URI'];
			$uri = explode('?', $requestUri);
			if(isset($uri[0])){
				if($uri[0] != '' || $uri[0] != null){
					$uri = explode('/', $uri[0]);
					if(count($uri) > 0) {
						$file =  explode('.', end($uri));
						if(count($file) >= 2) {
							$ext = strtolower(end($file));
							$imgExt = $this->config['images'];

							if($ext == 'css'){
								return $this->_di->make('Of\Controller\Css')->run(end($uri));
							}
							elseif($ext == 'js'){
								return $this->_di->make('Of\Controller\Js')->run(end($uri));
							}
							elseif(in_array($ext, $imgExt)){
								return $this->_di->make('Of\Controller\Image')->run(end($uri));
							}
							else {
								return $this->_di->make('Of\Controller\File')->run(end($uri));
							}
						}
					}
				}
			}
		}
		return false;
	}	
	
	protected function error($code=404){
		return $this->_di->get('Of\Controller\Error')->run($code);
	}
	
	public function run(){
    	if (opoink_hasError()) {
    		opoink_renderError();
    	} else {
			echo $this->content;
    	}
	}
	
	public function getConfig(){
		return $this->config;
	}
	
	public static function create(){
		$config = ROOT.DS.'etc'.DS.'Config.php';
		if(file_exists($config)){
			$config = include($config);
		} else {
			$config = null;
		}

		return new self(
			new Router($config),
			new Request(),
			$config
		);
	}
}
?>