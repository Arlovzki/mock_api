<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

use Zend\Json\Json;

class Sys implements SysInterface {
	
	protected $_systemSession;
	protected $_formSession;
	protected $_request;
	protected $_router;
	protected $_config;
	protected $_url;
	protected $_message;
	protected $_di;
	protected $pageTitle = 'Opoink Framework';
	protected $inlineJs = '';
	
	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message
	){
		$this->_systemSession = $SystemSession;
		$this->_formSession = $FormSession;
		$this->_request = $Request;
		$this->_url = $Url;
		$this->_message = $Message;
	}
	
	public function setDi($di){
		$this->_di = $di;
		return $this;
	}	
	
	public function setRouter($Router){
		$this->_router = $Router;
		$this->_url->setRouter($Router);
		return $this;
	}
	
	public function setConfig($config){
		$this->_config = $config;
		return $this;
	}
	
	public function getConfig($param=null){
		if($param){
			if(isset($this->_config[$param])){
				return $this->_config[$param];
			}
		} else {
			return $this->_config;
		}
	}	
	
	protected function requireLogin(){
		if(!$this->_systemSession->isLogedIn()){
			$this->_systemSession->setReturnUrl($this->_url->getCurrent());
			$this->_url->redirectTo($this->getUrl('/system/login'));
		}
	}
	
	protected function requireNotLogin(){
		if($this->_systemSession->isLogedIn()){
			$this->_url->redirectTo($this->getUrl('/system'));
		}
	}
	
	protected function requireInstalled(){
		if(!$this->checkInstall()){
			$this->_url->redirectTo($this->getUrl('/system/install'));
		}
	}
	
	protected function requireNotInstalled(){
		if($this->checkInstall()){
			$this->_url->redirectTo($this->getUrl('/system'));
		}
	}

	protected function checkInstall(){
		$installFlag = ROOT.DS.'etc'.DS.'install_flag.php';
		if(file_exists($installFlag)){
			return true;
		} else {
			return false;
		}
	}
	
	protected function addInlineJs($templateFile=null){
		$systemTemplate = ROOT.DS.'vendor'.DS.'opoink'.DS.'framework'.DS.'View'.DS.'Sys'.DS.'Js';
		
		$jsTemplate = '';
		if($templateFile){
			$jsTpl = $systemTemplate.DS.$templateFile;
		} else {
			$currentRoute = 'system';
			$currentRoute .=  '_'.$this->_router->getController(false);
			$currentRoute .=  '_'.$this->_router->getAction(false);
			$jsTpl = $systemTemplate.DS.$currentRoute.'.phtml';
		}
		if(file_exists($jsTpl)){
			ob_start();
				include($jsTpl);
				$jsTemplate = ob_get_contents();
			ob_end_clean();
		}
		$this->inlineJs .= $jsTemplate;
	}
	
	protected function renderHtml($templateFile=null, $layout='default.phtml'){
		$systemLayout = ROOT.DS.'vendor'.DS.'opoink'.DS.'framework'.DS.'View'.DS.'Sys'.DS.'Layout';
		
		$layout = $systemLayout.DS.$layout;
		$template = '';
		
		$systemTemplate = ROOT.DS.'vendor'.DS.'opoink'.DS.'framework'.DS.'View'.DS.'Sys'.DS.'Templates';
		if($templateFile){
			$tpl = $systemTemplate.DS.$templateFile;
		} else {
			$currentRoute = 'system';
			$currentRoute .=  '_'.$this->_router->getController(false);
			$currentRoute .=  '_'.$this->_router->getAction(false);
			
			$tpl = $systemTemplate.DS.$currentRoute.'.phtml';
			
		}
		if(file_exists($tpl)){
			ob_start();
				include($tpl);
				$template = ob_get_contents();
			ob_end_clean();
		}
		
		if(file_exists($layout)){
			include($layout);
		}
	}
	
	protected function validateFormKey(){
		return $this->_formSession->validateFormKey();
	}
	
	protected function getFormKey(){
		return $this->_formSession->getFormKey();
	}
	
	/*
	*	this will validate required fields
	*	if the field is array will recursive check the array for validation
	*	using validateRequiredFieldHelper function
	*/
	protected function validateRequiredField($postFields=[], $requiredFields=[]){
		$result = [
			'error' => 0,
			'message' => 'Success',
		];
		
		foreach($requiredFields as $requiredField){
			$helper = $this->validateRequiredFieldHelper($postFields, $requiredField);
			if(!is_int($helper)){
				$result = [
					'error' => 1,
					'message' => $helper,
				];
				break;
			}
		}
		
		return $result;
	}
	
	protected function validateRequiredFieldHelper($postFields, $requiredField, $recursive=0){
		$result = 0;
		$matchFound = false;
		foreach($postFields as $key => $val){
			if($key == $requiredField){
				$matchFound = true;
				if($val == null || $val == ""){
					$result = 0;
				} else {
					$result++;
				}
				break;
			} else {
				if(is_array($val)){
					$recurse = $this->validateRequiredFieldHelper($val, $requiredField, 1);
					if($recurse > 0){
						$result = 1;
						$matchFound = true;
					}
				}
			}
		}
		
		if($result > 0 && $matchFound == true){
			$output = 1;
		} else {
			$output = $requiredField;
		}
		return $output;
	}
	
	protected function getParam($param=null){
		return $this->_request->getParam($param);
	}
	
	protected function jsonEncode($data){
		$j = Json::encode($data);
		header("Content-Type: application/json; charset=UTF-8");
		echo $j;
		exit;
		die;
	}	
	
	public function run(){
		
	}
	
	protected function getUrl($path='', $param=array()){
		$currentRoute = '/system';
		if(isset($this->_config['system_url'])){
			$currentRoute .= $this->_config['system_url'];
		}
		$path = str_replace('/system', $currentRoute ,$path);
		return $this->_url->getUrl($path, $param);
	}
	
	protected function validateGRecaptcha(){
		$valid = true;
		$gRecaptcha = $this->getConfig('sys_g_recaptcha');
		
		if($gRecaptcha){
			if(isset($gRecaptcha['status'])){
				if($gRecaptcha['status'] == 1){
					$url = 'https://www.google.com/recaptcha/api/siteverify?';
					$param = [
						'secret' => $gRecaptcha['secret'],
						'response' => $this->_request->getParam('g-recaptcha-response'),
						'remoteip' => $this->_request->getClientIp(),
					];
					$url .= http_build_query($param);
					$response = file_get_contents($url);
					if($response){
						$response = json_decode($response);
						if(isset($response->success)){
							if($response->success == false){
								$valid = false;
							}
						} else {
							$valid = false;
						}
					} else {
						$valid = false;
					}
				}
			}
		}
		return $valid;
	}
}