<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\CacheManager\Purge;

class XmlHtml extends \Of\CacheManager\CacheManager {

	public function __construct(){
		$this->targetDir = ROOT.DS.'Var'.DS.'Layout';
	}

	public function execute(){
		$this->result['error'] = 0;
		$this->result['message'] = 'XML and HTML cached files successfully purged.';
		if(is_dir($this->targetDir)){
			$purge = parent::execute();
			if(!$purge){
				$this->result['error'] = 1;
				$this->result['message'] = 'Can\'t purge cache XML and HTML files now, please try again.';
			} 
		}
		$status = $this->getStatus();
		$status['xml']['status'] = self::UPDATED;
		$this->saveStatus($status);
		
		return $this->result;
	}
}