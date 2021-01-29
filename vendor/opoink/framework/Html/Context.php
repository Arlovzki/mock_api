<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Html;

class Context {
	
	protected $childrens = [];
	protected $tplPath;
	protected $cacheable = false;
	protected $cacheMaxAge;
	protected $container;
	protected $generation;
	
	protected $_url;
	protected $_config;
	protected $_controller;
	
	public function __construct(
		\Of\Http\Url $Url,
		\Of\Config $Config
	){
		$this->_url = $Url;
		$this->_config = $Config;
	}
	
	public function setCacheMaxAge($maxAge){
		$this->cacheMaxAge = (int)$maxAge;
		return $this;
	}
	
	public function setCacheable($isCacheable=false){
		if($isCacheable){
			$this->cacheable = true;
		}
		return $this;
	}
	
	public function setController($controller){
		$this->_controller = $controller;
		return $this;
	}
	
	protected function getUrl($path='', $param=array()){
		return $this->_url->getUrl($path, $param);
	}

	protected function getAdminUrl($path='', $param=array()){
		return $this->_url->getAdminUrl($path, $param);
	}

	/*
	*	
	*	vendor: vendorname
	*	module: modulename
	*	path: image file path from /Vendor/Module/View/path/to/image/file
	*	filename: filename.jpg
	*	resize: [
	*		'type' => ToShortSide,
	*		'max_short' => int ex 20,
	*		'allow_enlarge' => bool
	*	]
	*	resize: [
	*		'type' => ToLongSide,
	*		'max_long' => max long shize ex 200px,
	*		'allow_enlarge' => bool
	*	]
	*	resize: [
	*		'type' => ToHeight,
	*		'height' => height ex 200px,
	*		'allow_enlarge' => bool
	*	]
	*	resize: [
	*		'type' => ToWidth,
	*		'width' => width ex 200px,
	*		'allow_enlarge' => bool
	*	]
	*	resize: [
	*		'type' => ToBestFit,
	*		'max_width' => max_width ex 200px,
	*		'max_height' => max_height ex 300px,
	*		'allow_enlarge' => bool
	*	]
	*	resize: [
	*		'type' => ToBestFit,
	*		'width' => width ex 200px,
	*		'height' => height ex 300px,
	*		'allow_enlarge' => bool
	*	]
	*	resize: [
	*		'type' => ToBestFit,
	*		'width' => width ex 200px,
	*		'height' => height ex 300px,
	*		'allow_enlarge' => bool
	*	]
	*	resize: [
	*		'type' => crop,
	*		'width' => width ex 200px,
	*		'height' => height ex 300px,
	*		'allow_enlarge' => bool,
	*		'position' => CROPCENTER | CROPTOP | CROPBOTTOM | CROPLEFT | CROPRIGHT | CROPTOPCENTER
	*	]
	*/
	protected function getImageUrl($params=array()){
		$str = 'the_image_param_must_be_an_array';
		if(is_array($params)){
			if(isset($params['path'])){
				$path = str_replace('=', '', strtr(base64_encode(json_encode($params)), '+/', '-_'));

				$path = strtolower($params['vendor']) . '/';
				$path .= strtolower($params['module']) . '/';
				$path .= opoink_b64encode($params['path']) . '/';

				if(isset($params['resize'])){
					$resize = $params['resize'];
					foreach($resize as $key => $val){
						$path .= strtolower($key) . '/' . strtolower($val) . '/';
					}
				}

				$str =  $this->getPublicUrl('/images/'.$path.$params['filename']);
			} else {
				$str = 'the_path_of_image_is_important';
			}
		}
		return $str;
	}

	protected function getGenTime(){
		if(!$this->generation){
			$generation = ROOT.DS.'public'.DS.'generation.php';
			if(file_exists($generation)){
				$this->generation = include($generation);
			}
		}
		return $this->generation;
	}

	protected function getPublicPath(){
		return '/public/deploy' . $this->getGenTime();
		
	}

	protected function getPublicUrl($path='', $param=array()){
		$p = $this->getPublicPath();
		$p .= $path;
		return $this->getUrl($p, $param);
	}
	
	public function getChildHtml($name=null){
		$html = '';
		if(!$name){
			foreach($this->childrens as $child){
				$html .= $child->toHtml();
			}
			return $html;
		}
		
		if(isset($this->childrens[$name])){
			$child = $this->childrens[$name];
			return $child->toHtml();
		}
	}
	
	public function addContainer($htmlElem){
		$this->container = $htmlElem;
	}
	
	public function addChild($name, $class){
		$this->childrens[$name] = $class;
		return $this;
	}
	
	public function setTemplate($tplPath){
		$this->tplPath = $tplPath;
		return $this;
	}
	
	public function toHtml(){
		if($this->_config->getConfig('mode') == \Of\Constants::MODE_PROD){
			if($this->cacheable){
				$this->getCacheTemplate();
			}
		}
		if($this->container){
			echo $this->container;
				echo $this->getChildHtml();
			echo '</div>';
		} else {
			include($this->tplPath);
		}
	}
	
	protected function getCacheTemplate(){
		$targetDir = ROOT.'/Var/Layout/Template/'.strtolower($this->_url->getProtocol());
		$templateName = hash('sha256', $this->tplPath);
		$newTemplatePath = $targetDir.DS.$templateName.'.php';
		
		if(!$this->validateCache($newTemplatePath)){
			ob_start();
				include($this->tplPath);
				$htmlString = ob_get_contents();
			ob_end_clean();
			
			$writer = new \Of\File\Writer();
			$writer->setDirPath($targetDir)
			->setData($htmlString)
			->setFilename($templateName)
			->setFileextension('php')
			->write();
		}
		$this->tplPath = $newTemplatePath;
	}
	
	protected function validateCache($cacheTemplatePath){
		if(!file_exists($cacheTemplatePath)){
			return false;
		}
		$cacheMaxAge = filemtime($cacheTemplatePath) + $this->cacheMaxAge;
		if($cacheMaxAge < time()){
			return false;
		}
		
		return true;
	}
}
?>