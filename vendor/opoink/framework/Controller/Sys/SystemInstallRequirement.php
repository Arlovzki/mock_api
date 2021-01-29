<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemInstallRequirement extends Sys {
	
	protected $pageTitle = 'Requirement Opoink Framework';
	
	public function run(){
		$this->requireNotInstalled();
		
		$data = [
			'phpversion' => phpversion(),
			'memory_limit' => ini_get('memory_limit')
		];
		$this->jsonEncode($data);
	}
	
}