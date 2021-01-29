<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller;

use Of\Constants;

class Filecontroller {
	
	protected $_config;
	protected $vendor;
	protected $module;
	
	public function __construct(
		\Of\Config $Config
	){
		$this->_config = $Config;
	}
	
	protected function getPath($file, $tolower=true){
		$requestUri = $_SERVER['REQUEST_URI'];
		$uri = explode('?', $requestUri);
		
		if($uri[0] != '' || $uri[0] != null){
			$path = str_replace('/'.$file, '', $uri[0]);
			if($tolower){
				return strtolower($path);
			} else {
				return $path;
			}
		}
	}
	
	public function getRealPath($path){
		$targetFile = ROOT.DS.'public'.DS.'generation.php';
		$deployPath = '/public/deploy' . include($targetFile);

		$path = ltrim(str_replace($deployPath, '', $path), '/');
		
		$vendor = $this->getVendor($path);
		
		if($vendor) {
			$module = $this->getModule($path);
			if($module) {
				$pathFromUrl = $this->getPathFromUrl($path);
				
				$realPath = ROOT.DS.'App'.DS.'Ext'.DS.$vendor.DS.$module.DS.'View'.DS.$pathFromUrl.DS;
				
				return $realPath;
			}
		}
	}
	
	protected function getPathFromUrl($path){
		$toRemove = strtolower($this->vendor . '\/' . $this->module . '\/');
		$removed = $this->removeFromString($toRemove, '', $path);
		return $removed;
	}
	
	protected function removeFromString($search='', $replacement='', $string=''){
		$pattern = '/'.$search.'/';
		
		$newString = null;
		if(preg_match($pattern, $string)){
			$newString = preg_replace($pattern, '', $string);
		}
		return $newString;
	}
	
	/*
	*	retrieve vendor name from
	*	url request
	*/
	protected function getVendor($path){
		if(!$this->vendor){
			$pathArray = explode('/', $path);
			
			if(isset($pathArray[0])){
				$vendor = $pathArray[0];
				if($this->isValidVendor($vendor)){
					$this->vendor = ucfirst($vendor);
				}
			}
		}
		return $this->vendor;
	}
	
	/*
	*	validate vendor
	*/
	protected function isValidVendor($vendor){
		$modules = $this->_config->getConfig('modules');
		$vendor = ucfirst($vendor);
		if(isset($modules[$vendor])){
			return true;
		} else {
			return false;
		}
	}	
	
	/*
	*	retrieve module name from
	*	url request
	*/
	protected function getModule($path){
		if(!$this->module){
			$pathArray = explode('/', $path);
			if(isset($pathArray[1])){
				$module = $pathArray[1];
				if($this->isValidModule($module)){
					$this->module = ucfirst($module);
				}
			}
		}
		return $this->module;
	}
	
	/*
	*	validate module
	*/
	protected function isValidModule($module){
		if(!$this->module){
			$modules = $this->_config->getConfig('modules');
			
			$vendor = $modules[$this->vendor];
			$module = ucfirst($module);
			if(in_array($module, $vendor)){
				$this->module = ucfirst($module);
			}
		}
		return $this->module;
	}
	
	protected function makeDir($dir){
		if(!is_dir($dir)){
			mkdir($dir, 0777, true);
		}
	}

	protected function renderFile($targetFile="", $file="") {
		$mime_content_type = mime_content_type($targetFile);
		
		$lifetime = 60*60*24*60;
		header('Content-Disposition: inline; filename="'.basename($file).'"');
		header('Last-Modified: '. gmdate('D, d M Y H:i:s', filemtime($targetFile)) .' GMT');
		header('Expires: '. gmdate('D, d M Y H:i:s', time() + $lifetime) .' GMT');
		header('Pragma: ');
		header('Cache-Control: public, max-age='.$lifetime.', no-transform');
		header('Accept-Ranges: none');
		header('Content-type: ' . $mime_content_type);
		header('Content-Length: ' . filesize($targetFile));
		@readfile($targetFile);
		exit;
		die;
	}
}