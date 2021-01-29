<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Route;

class Router {
	
	protected $adminroute = '';
	protected $systemroute = '';
	protected $route = 'index';
	protected $controller = 'index';
	protected $action = 'index';
	protected $isAdmin  = false;
	protected $config;
	protected $_url;
	
	public function __construct($config=null){
		if($config){
			$this->setConfig($config);
		}
		$this->_url = new \Of\Http\Url();
		$this->init();
	}
	
	public function setConfig($config){
		$this->config = $config;
		$this->adminroute = 'admin'.$this->config['admin'];

		$this->systemroute = 'system';
		if(isset($this->config['system_url'])){
			$this->systemroute .= $this->config['system_url'];
		}
		return $this;
	}

	public function getConfig(){
		return $this->config;
	}

	/*
	*	validate the used domain name
	*	we do not always rely on servers domain settings
	*	we also have to validate it here
	*	the domain can be set in system panel or direct edit 
	*	on /installation_dir/etc/Config.php
	*	always return true on system route to reconfigure the 
	*	for some typo
	*	return bool 
	*/
	public function validateDomain(){
		if($this->config != null && isset($this->config['domains'])){
			$d = $this->config['domains'];

			if($this->systemroute != $this->getRoute(false)) { 
				if(count($d) > 0){
					$domain = $this->_url->getDomain();
					return in_array($domain, $d);
				}
			}
		}

		/*
		*	since there is no set domain
		*	means can use all pointed domain names
		*/
		return true;
	}
	
	/*
	*	return controller class
	*	for specific page
	*/
	public function getControllerClass(){
		$this->setSystemController();
		$controllers = $this->config['controllers'];
		
		$key = $this->getPageName(false);

		foreach($controllers as $_key => $_val){
			if(isset($_val[$key])){
				return $_val[$key];
			}
		}
	}
	
	/*
	*	set all system controller
	*	ex. system_index_index controller
	*/
	protected function setSystemController(){
		$sysRoute = 'system';
		if(isset($this->config['system_url'])){
			$sysRoute .= $this->config['system_url'];
		}
		$sysControllers = [
			$sysRoute.'_index_index' => 'Of\\Controller\\Sys\\SystemIndexIndex',
			$sysRoute.'_login_index' => 'Of\\Controller\\Sys\\SystemLoginIndex',
			$sysRoute.'_login_forgetpassword' => 'Of\\Controller\\Sys\\SystemLoginForgetpassword',
			$sysRoute.'_login_resetpassword' => 'Of\\Controller\\Sys\\SystemLoginResetpassword',
			$sysRoute.'_logout_index' => 'Of\\Controller\\Sys\\SystemLogoutIndex',
			$sysRoute.'_install_index' => 'Of\\Controller\\Sys\\SystemInstallIndex',
			$sysRoute.'_install_requirement' => 'Of\\Controller\\Sys\\SystemInstallRequirement',
			$sysRoute.'_install_database' => 'Of\\Controller\\Sys\\SystemInstallDatabase',
			$sysRoute.'_install_formkey' => 'Of\\Controller\\Sys\\SystemInstallFormkey',
			$sysRoute.'_install_saveadmin' => 'Of\\Controller\\Sys\\SystemInstallSaveadmin',
			$sysRoute.'_install_saveadminurl' => 'Of\\Controller\\Sys\\SystemInstallSaveadminurl',
			$sysRoute.'_settings_index' => 'Of\\Controller\\Sys\\SystemSettingsIndex',
			$sysRoute.'_module_action' => 'Of\\Controller\\Sys\\SystemModuleAction',
			$sysRoute.'_module_index' => 'Of\\Controller\\Sys\\SystemModuleIndex',
			$sysRoute.'_module_install' => 'Of\\Controller\\Sys\\SystemModuleInstall',
			$sysRoute.'_user_index' => 'Of\\Controller\\Sys\\SystemUserIndex',
			$sysRoute.'_user_save' => 'Of\\Controller\\Sys\\SystemUserSave',
			$sysRoute.'_cache_index' => 'Of\\Controller\\Sys\\SystemCacheIndex',
			$sysRoute.'_cache_action' => 'Of\\Controller\\Sys\\SystemCacheAction',
		];
		$this->config['controllers']['Systems_Controllers'] = $sysControllers;
	}
	
	public function getPageName($ucfirst=true, $delimiter='_'){
		$key = '';
		if($this->isAdmin){
			$key .= 'admin_';
		}
		$key .= $this->getRoute($ucfirst).$delimiter.$this->getController($ucfirst).$delimiter.$this->getAction($ucfirst);
		return $key;
	}

	public function getRoute($ucfirst=true){
		if($ucfirst){
			$o = ucfirst($this->route);
		} else {
			$o = strtolower($this->route);
		}
		return $o;
	}
	
	public function getController($ucfirst=true){
		if($ucfirst){
			$o = ucfirst($this->controller);
		} else {
			$o = strtolower($this->controller);
		}
		return $o;
	}
	public function getAction($ucfirst=true){
		if($ucfirst){
			$o = ucfirst($this->action);
		} else {
			$o = strtolower($this->action);
		}
		return $o;
	}

	protected function init(){
		$path = ltrim($_SERVER['REQUEST_URI'], "/");
		$path =	explode('?', $path);
		$paths = explode("/",ltrim($path[0], "/"));
		
		if($this->validatePath($paths, 0)){
			if($paths[0] === $this->adminroute){
				if($this->validatePath($paths, 1)){
					$this->route = $paths[1];
					if($this->validatePath($paths, 2)){
						$this->controller = $paths[2];
						if($this->validatePath($paths, 3)){
							$this->action = $paths[3];
						}
					}
				}
				$this->isAdmin = true;
			} else {
				$this->route = $paths[0];
				if($this->validatePath($paths, 1)){
					$this->controller = $paths[1];
					if($this->validatePath($paths, 2)){
						$this->action = $paths[2];
					}
				}
			}
		}
		
		$this->setQuery($paths);
		$this->phpInput($paths);
	}
	
	public function getAdminRoute(){
		if($this->isAdmin){
			return $this->adminroute;
		} else {
			return false;
		}
	}
	
	protected function validatePath($paths, $path){
		$o = false;
		if(isset($paths[$path])){
			if($paths[$path] != null || $paths[$path] != ''){
				$o = true;
			}
		}
		return $o;
	}
	
	protected function setQuery($paths){
		$pathNo = 3;
		
		if($this->isAdmin){
			$pathNo += 1;
		}
		
		if(count($paths) > 3){
			$k = $v = null;
			foreach($paths as $key => $val){
				if($key <= ($pathNo - 1)){
					continue;
				}
				if($k == null && $v == null){
					$k = $val;
				}
				elseif($k != null && $v == null){
					if($val != '' || $val != null){
						$_GET[$k] = $val;
						$k = $v = null;
					}
				}
			}
		}
	}
	
		
	protected function phpInput(){
		$phpInput = file_get_contents("php://input");
		if($phpInput){
			$postdata = json_decode($phpInput, true);
			if($postdata) {
				foreach($postdata as $key => $val){
					$_GET[$key] = $val;
				}
			}
		}
	}
}
?>