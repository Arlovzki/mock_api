<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemInstallFormkey extends Sys {
	
	protected $pageTitle = 'Installation Formkey';
	protected $referer;
	
	public function run(){
		$this->referer = $this->_request->getServer('HTTP_REFERER');
		
		$data = [
			'error' => 1,
			'formKey' => null,
			'message' => 'Invalid request',
		];
		
		if($this->referer){
			if($this->checkDomain()){
				$data = [
					'error' => 0,
					'formKey' => $this->_formSession->generateFormKey()->getFormKey(),
					'message' => 'Success',
				];
			}
		}
		$this->jsonEncode($data);
	}
	
	protected function checkDomain(){
		$parse = parse_url($this->referer);
		if(isset($parse['host'])){
			$refIp = gethostbyname($parse['host']);
			
			if($refIp === $this->_request->getServer('SERVER_ADDR')){
				return true;
			}
		}
	}
}