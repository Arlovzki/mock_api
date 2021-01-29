<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\File;

use Of\Constants;

class Xmltohtml {
	
	/* xml type */
	const XML_HTML = 'html';
	const XML_HEAD = 'head';
	const XML_BODY = 'body';
	const XML_CONTAINER = 'container';
	const XML_TEMPLATE = 'template';
	const XML_CSS = 'css';
	const XML_JS = 'js';
	const XML_LINK = 'link';
	
	/*
	*	route info create by \Of\Route\Route
	*	this already created and just pass it in here
	*/
	protected $routeInfo;
	
	/*
	*	detemine whether to render close tags
	*/
	protected $isCloseTag = true;

	/*
	*	the file name of current xml
	*/
	protected $XMLpath;
	
	protected $mode;
	
	public function __construct(
		\Of\Http\Url $Url,
		$mode
	){
		$this->_config = new \Of\Config();
		$this->_url = $Url;
		$this->mode = $mode;
	}

	public function setRouteInfo($routeInfo){
		$this->routeInfo = $routeInfo;
		return $this;
	}
	
	public function build(){
		$layoutCacheDir = ROOT . 'var/Extension/Layout/'.ucfirst($this->routeInfo['type']);
		$htmlCacheDir = ROOT . 'var/Extension/Html/'.ucfirst($this->routeInfo['type']);
		$filename = strtolower($this->routeInfo['route'].'_'.$this->routeInfo['controllerDir'].'_'.$this->routeInfo['controller']);
		
		$this->getHtml($layoutCacheDir.'/'.$filename.'.xml');
		die;
	}
	
	public function getAcceptedNodeName(){
		return [
			self::XML_HTML,
			self::XML_HEAD,
			self::XML_BODY,
			self::XML_CONTAINER,
			self::XML_TEMPLATE,
			self::XML_CSS,
			self::XML_JS,
			self::XML_LINK,
		];
	}
	
	/*
	*	return html content
	*/
	public function getHtml($XMLpath, $asPlainText=false){
		$data = file_get_contents($XMLpath);
		$this->XMLpath = $XMLpath;
		
		$dom = new \DOMDocument('1.0', 'UTF-8');
		$dom->loadXML($data);
		$node = $dom->documentElement;
		
		$html = '<!DOCTYPE html>' . PHP_EOL;
		$html .= $this->getHtmlHelper($node);
		
		if($asPlainText){
			header ("Content-Type:text/plain");
			echo $html;
			die;
		} else {
			return $html;
		}
	}
	
	protected function getHtmlHelper($node, $loop=0){
		$html = '';
		if(in_array($node->nodeName, $this->getAcceptedNodeName())){
			$html .= $this->createOpenTag($node, $loop);
			$closeTag = $this->createCloseTag($node, $loop);
				if($node->hasChildNodes()){
					$indent = $loop + 1;
					foreach ($node->childNodes as $childNode) {
						if($childNode->nodeName === '#text'){
							continue;
						}
						$html .= $this->getHtmlHelper($childNode, $indent);
					}
				}
			$html .= $closeTag;
		} else {
			$error = 'Invalid node name ' . $node->nodeName;
			$this->errorThrow($error);
		}
		return $html;
	}
	
	/*
	*	create html open tag
	*/
	protected function createOpenTag($node, $indent=0){
		$attribute = '';
		if($node->hasAttribute('attr')){
			$attribute = $node->getAttribute('attr');
		}
		$tag = '';
		if($indent > 0 && $this->mode == Constants::MODE_DEV){
			$tag .= str_repeat("\t", ($indent));
		}
		
		if($node->nodeName == self::XML_CSS){
			$tag .= $this->createCssTag($node);
		}
		elseif($node->nodeName == self::XML_JS){
			$tag .= $this->createJsTag($node);
		}
		elseif($node->nodeName == self::XML_CONTAINER){
			$tag .= $this->getContainer($node, $indent);
		}
		elseif($node->nodeName == self::XML_TEMPLATE){
			$tag .= $this->getTemplate($node, $indent);
		}
		else {
			$tag .= '<'.$node->nodeName.' '.$attribute.'>';
		}
		
		if($this->mode == Constants::MODE_DEV){
			$tag .= PHP_EOL;
		}
		return $tag;
	}
	
	/*
	*	return container open tag
	*/
	protected function getContainer($node, $indent=0){
		$htmlId = '';
		if($node->hasAttribute('htmlId')){
			$htmlId = ' id="'.$node->getAttribute('htmlId').'"';
		}
		$htmlClass = '';
		if($node->hasAttribute('htmlClass')){
			$htmlClass = ' class="'.$node->getAttribute('htmlClass').'"';
		}

		$htmlTag = 'div';
		if($node->hasAttribute('htmlTag')){
			$htmlTag = $node->getAttribute('htmlTag');
		}

		$div = '<'.$htmlTag.$htmlId.$htmlClass.'>';
		
		$i = '';
		if($indent > 0 && $this->mode == Constants::MODE_DEV){
			$i = str_repeat("\t", ($indent));
		}
		$eol = '';
		if($this->mode == Constants::MODE_DEV){
			$eol .= PHP_EOL;
		}
		
		if($node->parentNode->nodeName == self::XML_TEMPLATE){
			$nodeId = $node->getAttribute('xml:id');
			$parentNodeId = $node->parentNode->getAttribute('xml:id');
			$this->isCloseTag = false;
			
			$container = '<?php ' . $eol;
			$container .= $i . '$tplClass_'.$nodeId.' = $this->_di->make(\'Of\\Html\\Context\')->setController($this) ?>' . $eol;
			$container .= $i . '<?php $tplClass_'.$nodeId.'->addContainer(\''.$div.'\'); ?>' . $eol;
			$container .= $i . '<?php $tplClass_'.$parentNodeId.'->addChild(\''.$nodeId.'\', $tplClass_'.$nodeId.'); ?>' . $eol;
			return $container;
		} else {
			$this->isCloseTag = true;
			return $div;
		}
	}
	
	/*
	*	create html close tag
	*/
	protected function createCloseTag($node, $indent=0){
		if(!$this->isCloseTag){
			$this->isCloseTag = true;
			return '';
		}
		
		$tag = '';
		if($indent > 0 && $this->mode == Constants::MODE_DEV){
			$tag .= str_repeat("\t", ($indent));
		}
		
		
		if($node->nodeName == self::XML_TEMPLATE){
			$tag .= '<?php echo $tplClass_'.$node->getAttribute('xml:id').'->toHtml(); ?>';
		}
		elseif($node->nodeName == self::XML_CONTAINER){
			$htmlTag = 'div';
			if($node->hasAttribute('htmlTag')){
				$htmlTag = $node->getAttribute('htmlTag');
			}
			$tag .= '</'.$htmlTag.'>';
		}
		else {
			$tag .= '</'.$node->nodeName.'>';
		}
		
		
		if($this->mode == Constants::MODE_DEV){
			$tag .= PHP_EOL;
			$tag .= PHP_EOL;
		}
		return $tag;
	}
	
	/*
	*	get template 
	*/
	protected function getTemplate($node, $indent=0){
		$error = null;
		if(!$node->hasAttribute('template')){
			$error = 'Undefined template from xml ID: ' . $node->getAttribute('xml:id');
		}
		if(!$node->hasAttribute('vendor')){
			$error = 'Undefined vendor: in template ID: ' . $node->getAttribute('xml:id');
		}
		if(!$node->hasAttribute('module')){
			$error = 'Undefined module: in template ID: ' . $node->getAttribute('xml:id');
		}
		
		$template = '';
		if($error){
			$this->errorThrow($error);
		} else {
			$i = '';
			if($indent > 0 && $this->mode == Constants::MODE_DEV){
				$i = str_repeat("\t", ($indent));
			}
			
			$class = 'Of\\Html\\Context';
			if($node->hasAttribute('class')){
				$class = $node->getAttribute('class');
			}
			$eol = '';
			if($this->mode == Constants::MODE_DEV){
				$eol .= PHP_EOL;
			}
			
			$vendor = $node->getAttribute('vendor');
			$module = $node->getAttribute('module');
			$templateDir = ROOT.'/App/Ext/'.ucfirst($vendor).'/'.ucfirst($module).'/View/Template/';
			$templateName = $node->getAttribute('template');
			
			$template .= '<?php ' . $eol;
			$template .= $i . '$tplClass_'.$node->getAttribute('xml:id').' = $this->_di->make(\''.$class.'\')' . $eol;
			$template .= $i . '->setController($this)' . $eol;
			if($this->isCacheable($node)){
				$template .= $i . '->setCacheable(true)' . $eol;
				$maxAge = $this->getAttr($node, 'max-age');
				if($maxAge === ''){
					$cache = $this->_config->getConfig('cache');
					$maxAge = $cache['max-age'];
				}
				$template .= $i . '->setCacheMaxAge('.$maxAge.')' . $eol;
			}
			$template .= $i . '?>' . $eol;
			
			if(file_exists($templateDir.$templateName)){
				$template .= $i . '<?php $tplClass_'.$node->getAttribute('xml:id').'->setTemplate(\''.$templateDir.$templateName.'\'); ?>';
			} else {
				$error = 'Template not found on xml ID: ' . $node->getAttribute('xml:id');
				$this->errorThrow($error);
			}
			$template .= $this->getTemplateHelper($node, $indent);
			
			/* if($node->parentNode->nodeName == $node->nodeName){
				$parentNodeId = $node->parentNode->getAttribute('xml:id');
				$template .= $eol . $i . '<?php $tplClass_'.$parentNodeId.'->addChild(\''.$node->getAttribute('xml:id').'\', $tplClass_'.$node->getAttribute('xml:id').'); ?>' . $eol;
				
				$this->isCloseTag = false;
			} else {
				$this->isCloseTag = true;
			} */
		}
		return $template;
	}
	/*
	*	determine if xml attribut 
	*/
	protected function getTemplateHelper($node, $indent){
		$i = '';
		if($indent > 0 && $this->mode == Constants::MODE_DEV){
			$i = str_repeat("\t", ($indent));
		}
		
		$eol = '';
		if($this->mode == Constants::MODE_DEV){
			$eol .= PHP_EOL;
		}
		
		$parentNodeId = $node->parentNode->getAttribute('xml:id');
		$template = $eol . $i . '<?php $tplClass_'.$parentNodeId.'->addChild(\''.$node->getAttribute('xml:id').'\', $tplClass_'.$node->getAttribute('xml:id').'); ?>' . $eol;
		
		if($node->parentNode->nodeName == $node->nodeName){
			$this->isCloseTag = false;
			return $template;
		}elseif($node->hasAttribute('toHtml')){
			if($node->getAttribute('toHtml') === 'false') {
				$this->isCloseTag = false;
				return $template;
			} else {
				$this->isCloseTag = true;
				return '';
			}
		} else {
			$this->isCloseTag = true;
			return '';
		}
	}
	
	/*
	*	return attribute if found
	*	return empty string if not
	*/
	public function getAttr($node, $attr){
		if($node->hasAttribute($attr)){
			return $node->getAttribute($attr);
		} else {
			return '';
		}
	}
	/*
	*	return bool 
	*/
	public function isCacheable($node){
		if($node->hasAttribute('cacheable')){
			$cacheable = $node->getAttribute('cacheable');
			if($cacheable === 1 || $cacheable === '1'){
				return true;
			}
		}
		return false;
	}
	
	/*
	*	create css tag
	*/
	protected function createCssTag($node){
		if(!$node->hasAttribute('src')){
			$error = 'CSS src is required from xml ID: ' . $node->getAttribute('xml:id');
			$this->errorThrow($error);
		}
		
		$tag = '<link';
		
		if($node->hasAttribute('external')){
			$tag .= ' href="'.$node->getAttribute('src').'" ';
		} else {
			$deployTIme = 'deploy'.$this->getDeployTime();
			$src = $this->_url->getUrl('/public/'.$deployTIme.'/'.ltrim($node->getAttribute('src'), '/'));
			$src .= '.css';
			$tag .= ' href="'.$src.'" ';
		}
		if($node->hasAttribute('media')){
			$tag .= ' media="'.$node->getAttribute('media').'" ';
		}
		if(!$node->hasAttribute('extra')){
			$tag .= $node->getAttribute('extra');
		}
		$tag .= ' rel="stylesheet" type="text/css" />';
		$this->isCloseTag = false;
		return $tag;
	}
	
	/*
	*	create js tag
	*/
	protected function createJsTag($node){
		if(!$node->hasAttribute('src')){
			$error = 'JS src is required from xml ID: ' . $node->getAttribute('xml:id');
			$this->errorThrow($error);
		}
		if(!$node->hasAttribute('vendor')){
			$error = 'JS vendor is required from xml ID: ' . $node->getAttribute('xml:id');
			$this->errorThrow($error);
		}
		if(!$node->hasAttribute('module')){
			$error = 'JS module is required from xml ID: ' . $node->getAttribute('xml:id');
			$this->errorThrow($error);
		}
		
		$tag = '<script ';
		if($node->hasAttribute('external')){
			$tag .= ' src="'.$node->getAttribute('src').'" ';
		} else {
			$deployTIme = 'deploy'.$this->getDeployTime();
			$vendor = strtolower($node->getAttribute('vendor'));
			$module = strtolower($node->getAttribute('module'));
			$src = $this->_url->getUrl('/public/'.$deployTIme.'/'.$vendor.'/'.$module.'/'.ltrim($node->getAttribute('src'), '/'));
			$src .= '.js';
			$tag .= ' src="'.$src.'" ';
		}
		if(!$node->hasAttribute('extra')){
			$tag .= $node->getAttribute('extra');
		}
		$tag .= ' type="text/javascript"></script>';
		$this->isCloseTag = false;
		return $tag;
	}
	
	/*
	*	return value of time()
	*	this will renew all css and javascript from
	*	being cached after new generated css and js 
	*	so that we are sure that the browser
	*	request new copy of css and js
	*	every single clean of generated xml file 
	*/
	protected function getDeployTime(){
		$time = '';
		$targetFile = Constants::GENERATION_TIME;
		
		if(!file_exists($targetFile)){
			$data = '<?php return '.time().'; ?>';
			$writer = new Writer();
			$writer->setDirPath(ROOT.DS.'public')
			->setData($data)
			->setFilename('generation')
			->setFileextension('php')
			->write();
		}
		
		$deployTime = include($targetFile);
		
		return $deployTime;
	}

	protected function errorThrow($msg){
		$error = $this->XMLpath . ': ' . $msg;
		$e = new \Exception($error);
		opoink_log_exception($e);
		if(file_exists($this->XMLpath)){
			unlink($this->XMLpath);
		}
		opoink_renderError();
		exit;
		die;
	}
}