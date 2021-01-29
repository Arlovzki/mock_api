<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Html;

Use Of\Constants;
Use Of\File\Mergexml;
Use Of\File\Xmltohtml;
Use Of\File\Writer;
Use Of\Http\Url;

class Layout {
	
	protected $_router;
	protected $layoutDir;
	protected $htmlDir;
	protected $config;
	
	public function __construct($router=null, $config=null){
		$this->config = $config;
		$this->setRouter($router);
	}
	
	public function setRouter($router){
		$this->_router = $router;
	}
	
	public function getLayoutDir(){
		if(!$this->layoutDir){
			$this->layoutDir = ROOT.DS.'Var'.DS.'Layout'.DS.'Xml';
		}
		return $this->layoutDir;
	}
	
	public function getHtmlDir(){
		if(!$this->htmlDir){
			$url = new Url();
			$this->htmlDir = ROOT.DS.'Var'.DS.'Layout'.DS.'Html'.DS. strtolower($url->getProtocol());
		}
		return $this->htmlDir;
	}
	
	public function getFile(){
		return $this->_router->getPageName(false).'.xml';
	}
	
	public function run(){
		return $this->getXmlFiles();
	}
	
	/*
	*	get all xml file and combine it to one
	*/
	protected function getXmlFiles($recurseive=false){
		$targetXml = $this->getLayoutDir().DS.$this->getFile();
		$targetHtml = $this->getHtmlDir().DS.$this->_router->getPageName(false).'.php';

		if(file_exists($targetHtml)){
			return $targetHtml;
		}
		if(file_exists($targetXml)){
			$xmlToHtml = new Xmltohtml(
				new \Of\Http\Url(),
				$this->config['mode']
			);
			$html = $xmlToHtml->getHtml($targetXml);
			
			$writer = new Writer();
			$writer->setDirPath($this->getHtmlDir())
			->setData($html)
			->setFilename($this->_router->getPageName(false))
			->setFileextension('php')
			->write();

			return $targetHtml;
		} else {
			if($recurseive){
				return false;
			}
			$xml = new Mergexml();
			$layouts = false;
			foreach($this->config['modules'] as $vendor => $modules){
				foreach($modules as $module){
					$themeLayoutFile = ROOT.DS.'App'.DS.'Theme'.DS.$vendor.DS.$module.DS.'Layout';
					$coreLayoutFile = ROOT.DS.'App'.DS.'Ext'.DS.$vendor.DS.$module.DS.'View'.DS.'Layout';

					$layoutQueue = '';
					if($this->_router->getAdminRoute()){
						$layoutQueue .= DS.'Admin';
					}
					$layoutQueue .= DS.$this->getFile();

					/************************* use theme xml if available ***************************/
					$pageLayoutFile = $themeLayoutFile . $layoutQueue;

					if(!file_exists($pageLayoutFile)) {
						$pageLayoutFile = $coreLayoutFile . $layoutQueue;
					}
					/*
					*	look for the value of "default" in xml Dom should be use
					*	as the default xml
					*/
					$default = 'default.xml';
					if(file_exists($pageLayoutFile)){
						$data = file_get_contents($pageLayoutFile);
						$data = preg_replace('/<\!--[\s\S]*?-->/', '', $data);

						$dom = new \DOMDocument('1.0', 'UTF-8');
						$dom->loadXML($data);

						$node = $dom->getElementById('html');
						if($node){
							if($node->hasAttribute('default')){
								$default = $node->getAttribute('default') . '.xml';
							}
						}
					}

					/************************* default xml ***************************/
					$layoutQueue = '';
					if($this->_router->getAdminRoute()){
						$layoutQueue .= DS.'Admin';
					}
					$layoutQueue .= DS.$default;

					$defaultLayoutFile = $themeLayoutFile . $layoutQueue;
					if(!file_exists($defaultLayoutFile)) {
						$defaultLayoutFile = $coreLayoutFile . $layoutQueue;
					}

					/************************* add the available xml into Dom ***************************/
					if(file_exists($defaultLayoutFile)){
						$xml->addXmlFile($defaultLayoutFile);
						$layouts = true;
					}

					if(file_exists($pageLayoutFile)){
						$xml->addXmlFile($pageLayoutFile);
						$layouts = true;
					}
				}
			}
			
			if($layouts){
				$writer = new Writer();
				$writer->setDirPath($this->getLayoutDir())
				->setData($xml->getXml())
				->setFilename($this->_router->getPageName(false))
				->setFileextension('xml')
				->write();
				return $this->getXmlFiles(true);
			}
		}
	}
}
?>