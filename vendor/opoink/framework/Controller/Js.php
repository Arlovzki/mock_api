<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller;

/* https://www.minifier.org/ */
use MatthiasMullie\Minify;
use Of\File\Writer;

class Js {
	
	protected $_config;
	protected $vendor;
	protected $module;
	protected $min = false;
	
	public function __construct(
		\Of\Config $Config
	){
		
		$this->_config = $Config;
	}
	
	public function run($file){
		$file = $this->getFilename($file);
		$requestUri = $_SERVER['REQUEST_URI'];
		$uri = explode('?', $requestUri);
		
		$js = null; 
		if($uri[0] != '' || $uri[0] != null){
			if($this->min){
				$path = str_replace('/'.$this->min, '', $uri[0]);
			} else {
				$path = str_replace('/'.$file, '', $uri[0]);
			}

			$realPath = $this->getRealPath($path);

			if($realPath){
				$targetFile = $realPath.$file;
				if($this->min){
					$destinationFile = ROOT.$path.DS.$this->min;
				} else {
					$destinationFile = ROOT.$path.DS.$file;
				}

				if(file_exists($targetFile)){

					$js = file_get_contents($targetFile);

					$deploy = '/public/deploy';
					if(file_exists(ROOT.DS.'public'.DS.'generation.php')){
						$deploy .= include(ROOT.DS.'public'.DS.'generation.php');
					}
					$js = str_replace('{{public_url}}', $deploy, $js);

					if($this->_config->getConfig('mode') == \Of\Constants::MODE_PROD){
						if($this->min){
							$minifier = new Minify\JS();
							$minifier->add($js);
							$this->makeDir(ROOT.$path);
							$js = $minifier->minify($destinationFile);
						} else {
							$_info = pathinfo($file);
							if(isset($_info['filename'])){
								$writer = new Writer();
								$writer->setDirPath(ROOT.$path)
								->setData($js)
								->setFilename($_info['filename'])
								->setFileextension('js')
								->write();
							}
						}
					}
				}
			}
		}
		
		if($js){
			echo header("Content-type: application/javascript", true);
			echo $js;
			exit;
			die;
		} else {
			return false;
		}
	}

	protected function getFilename($file){
		$f = explode('.', $file);
		$min = array_search('min', $f);
		if(is_int($min) && isset($f[$min])){
			unset($f[$min]);
			$this->min = $file;
		}
		$f = implode('.', $f);
		return $f;
	}
	
	public function getRealPath($path){
		$targetFile = ROOT.DS.'public'.DS.'generation.php';
		$deployPath = '/public/deploy' . include($targetFile);
		$path = ltrim(str_replace($deployPath, '', $path), '/');
		
		$vendor = $this->getVendor($path);
		$module = $this->getModule($path);

		if($vendor != null && $module != null){
			$pathFromUrl = strtolower($path);
			$toRemove = strtolower($vendor . '/' . $module . '/');
			$pathFromUrl = str_replace($toRemove, '', $pathFromUrl);
			if($this->min){
				$pathFromUrl = str_replace($this->min, '', $pathFromUrl);
			}
			$realPath = ROOT.DS.'App'.DS.'Ext'.DS.$vendor.DS.$module.DS.'View'.DS.$pathFromUrl.DS;
			return $realPath;
		}
	}
	
	/*
	*	retrieve vendor name from
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
	
	protected function isValidModule($module){
		if(!$this->module){
			$modules = $this->_config->getConfig('modules');
			if($this->vendor){
				$vendor = $modules[$this->vendor];
				if(in_array(ucfirst($module), $vendor)){
					$this->module = ucfirst($module);
				}
			}
		}
		return $this->module;
	}
	
	protected function isValidVendor($vendor){
		$modules = $this->_config->getConfig('modules');
		$vendor = ucfirst($vendor);
		if(isset($modules[$vendor])){
			return true;
		} else {
			return false;
		}
	}
	
	protected function makeDir($dir){
		if(!is_dir($dir)){
			mkdir($dir, 0777, true);
		}
	}
}

?>