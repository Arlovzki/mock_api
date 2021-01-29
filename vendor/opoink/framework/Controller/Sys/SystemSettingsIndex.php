<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemSettingsIndex extends Sys {
	
	protected $pageTitle = 'Opoink System Settings';
	
	
	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
	}
	
	public function run(){
		$this->requireInstalled();
		$this->requireLogin();
		
		if($this->_request->getParam('form_key')){
			return $this->saveSetting();
		}
		
		$this->addInlineJs();
		return $this->renderHtml();
	}
	
	protected function saveSetting(){
		$response = [
			'error' => 1,
			'message' => ''
		];
		
		$validate = $this->validateFormKey();
		if($validate){
			$domains = $this->_request->getParam('domains');
			if($domains){
				$domains = array_map('trim', explode(',', $domains));
				if(count($domains) > 0){
					$this->_config['domains'] = $domains;
				}
			} else {
				$this->_config['domains'] = [];
			}

			$admin = $this->_request->getParam('admin');
			$this->_config['admin'] = $admin;
				
			$system_url = $this->_request->getParam('system_url');
			$this->_config['system_url'] = $system_url;
			
			$cache = $this->_request->getParam('cache');
			$this->_config['cache'] = $cache;
			
			$mode = $this->_request->getParam('mode');
			$this->_config['mode'] = $mode;
			
			$images = $this->_request->getParam('images');
			$this->_config['images'] = array_map('ltrim', explode(',', $images));
			
			$sys_g_recaptcha = (int)$this->_request->getParam('sys_g_recaptcha');
			$this->_config['sys_g_recaptcha'] = [
				'status' => $sys_g_recaptcha,
				'key' => $this->_request->getParam('g_recaptcha_key'),
				'secret' => $this->_request->getParam('g_recaptcha_secret'),
			];
			
			$newConfig = '<?php' . PHP_EOL;
			$newConfig .= 'return ' . var_export($this->_config, true) . PHP_EOL;
			$newConfig .= '?>';
			
			$_writer = new \Of\File\Writer();
			$_writer->setDirPath(ROOT . DS . 'etc')
			->setData($newConfig)
			->setFilename('Config')
			->setFileextension('php')
			->write();			
		}
		
		$this->jsonEncode($this->_config);
		die;
	}
	
}