<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Less;

/* https://www.minifier.org/ */
use MatthiasMullie\Minify;

require_once( __DIR__ . '/Less.php');

class Builder extends \Less_Parser {
	
	protected $filePaths = [];
	protected $_config;
	
	public function setConfig($Config){
		$this->_config = $Config;
		return $this;
	}
	
	/*
	*	parse the array of less file to get
	*	and combine those less file into one
	*	css file.
	*	return string
	
	*	$less_files = array( '/var/www/mysite/bootstrap.less' => 'https://example.com/mysite/' );
	*	$options = array( 'cache_dir' => '/var/www/writable_folder' );
	*	$variables = array( 'width' => '100px' );
	*	$css_file_name = Less_Cache::Get( $less_files, $options, $variables );
	*	$compiled = file_get_contents( '/var/www/writable_folder/'.$css_file_name );
	*
	*	more info https://github.com/oyejorge/less.php
	*/
	public function build($file) {
		$requestUri = $_SERVER['REQUEST_URI'];
		$uri = explode('?', $requestUri);

		if($uri[0] != '' || $uri[0] != null){
			$path = str_replace('/'.$file, '', $uri[0]);
			$path = strtolower(str_replace('/', DS, $path));

			$fileInfo = pathinfo($file);

			$cssFileName = $fileInfo['filename'] . '.css';
			$lessFileName = $fileInfo['filename'] . '.less';
			
			$cache_dir = ROOT.DS.'Var'.DS.'Less';
		
			$less_files = $this->getCssFiles($path, $lessFileName, $cssFileName);

			$options = ['cache_dir' => $cache_dir];
			
			$this->makeDir($cache_dir);
			
			$css_file_name = \Less_Cache::Get($less_files, $options);

			$deploy = '/public/deploy';
			if(file_exists(ROOT.DS.'public'.DS.'generation.php')){
				$deploy .= include(ROOT.DS.'public'.DS.'generation.php');
			}

			return $this->changeVar('public_url', $deploy, $cache_dir, $css_file_name, $path, $file);
		}
	}

	/*
	*	change the variable from css files
	*	and output the result 
	*/
	protected function changeVar($search, $replace, $cache_dir, $css_file_name, $path, $file){
		$chachedFile = $cache_dir.DS.$css_file_name;

		$css = file_get_contents($chachedFile);
		$css = str_replace('{{'.$search.'}}', $replace, $css);
		
		$minifier = new Minify\CSS();
		$minifier->add($css);
		if($this->_config->getConfig('mode') == \Of\Constants::MODE_PROD){
			$publicCssDir = ROOT.$path;
			$this->makeDir($publicCssDir);
			$destinationFile = $publicCssDir.DS.$file;
			return $minifier->minify($destinationFile);
		} else {
			return $minifier->minify();
		}
	}


	/*
	*	this will scann all installed modules with 
	*	with css of the same file name as the request
	*	will return an array of absolute file path
	*	of each css files
	*/
	protected function getCssFiles($path, $fileName, $cssFileName){
		$realPath = $this->getRealPath($path);

		$css = [];
		foreach($this->_config->getConfig('modules') as $vendor => $modules) {
			foreach($modules as $module){
				$layoutFile = ROOT.DS.'App'.DS.'Ext'.DS.$vendor.DS.$module.DS.'View'.DS;
				$lesspath = ltrim(strtolower($realPath.DS.$fileName), DS);
				$csspath = ltrim(strtolower($realPath.DS.$cssFileName), DS);
				
				$less = $layoutFile.$lesspath;
				$_css = $layoutFile.$csspath;

				if(file_exists($less)){
					$css[$less] = '';
				}
				elseif(file_exists($_css)){
					$css[$_css] = '';
				}
			}
		}
		return $css;
	}
	
	public function getRealPath($path){
		$targetFile = ROOT.DS.'public'.DS.'generation.php';
		$deployPath = DS.'public'.DS.'deploy' . include($targetFile);
		
		$realPath = str_replace($deployPath, '', $path);
		return $realPath;
	}
	
	protected function makeDir($dir){
		if(!is_dir($dir)){
			mkdir($dir, 0777, true);
		}
	}
}