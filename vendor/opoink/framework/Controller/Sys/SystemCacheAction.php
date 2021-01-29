<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemCacheAction extends Sys {
	
	protected $pageTitle = 'Opoink Cache Action';
	protected $_less;
	protected $_deployedFiles;
	protected $_xmlHtml;
	protected $_database;

	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\CacheManager\Purge\Less $Less,
		\Of\CacheManager\Purge\DeployedFiles $DeployedFiles,
		\Of\CacheManager\Purge\XmlHtml $XmlHtml,
		\Of\CacheManager\Purge\Database $Database
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->_less = $Less;
		$this->_deployedFiles = $DeployedFiles;
		$this->_xmlHtml = $XmlHtml;
		$this->_database = $Database;
	}

	public function run(){
		$this->requireInstalled();
		$this->requireLogin();

		$services = $this->_request->getParam('cache_services');
		if(is_array($services)){
			foreach($services as $service){
				$s = strtolower($service);
				$result = null;
				if($s === 'less'){
					$result = $this->_less->execute();
				}
				if($s === 'deployed_files'){
					$result = $this->_deployedFiles->execute();
				}
				if($s === 'xml'){
					$result = $this->_xmlHtml->execute();
				}
				if($s === 'database'){
					$result = $this->_database->execute();
				}

				if(is_array($result)){
					$type = 'success';
					if($result['error'] == 1){
						$type = 'danger';
					}
					$this->_message->setMessage($result['message'], $type);
				}
			}
		}
		$this->_url->redirectTo($this->getUrl('/system/cache'));
	}
}