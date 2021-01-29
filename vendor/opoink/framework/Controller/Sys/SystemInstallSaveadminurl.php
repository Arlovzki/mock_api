<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

use Of\Std\Password;

class SystemInstallSaveadminurl extends Sys {
	
	protected $pageTitle = 'Save Admin Url';
	
	public function run(){
		$this->requireNotInstalled();
		$response = [
			'error' => 1,
			'message' => ''
		];
		
		$validate = $this->validateFormKey();
		
		if($this->validateFormKey()){
			$admin_url = $this->getParam('admin_url');
			$system_url = $this->getParam('system_url');
			$target = ROOT.DS.'etc'.DS.'Config.php';
			
			if(file_exists($target)){
				$config = include($target);
			} else {
				$config = [];
			}
			
			$config['domains'] = [];
			$config['admin'] = $admin_url;
			$config['system_url'] = $system_url;
			$config['mode'] = 'developer';
			$config['auth'] = [
				'key' => $this->getParam('auth_key'),
				'secret' => $this->getParam('auth_secret'),
			];
			$config['images'] = [
			    0 => 'jpg',
			    1 => 'png',
			    2 => 'gif',
			    3 => 'ico',
		  	];
		  	$config['cache'] = [
		  		'max-age' => '86400',
		  	];
			
			$newConfig = '<?php' . PHP_EOL;
			$newConfig .= 'return ' . var_export($config, true) . PHP_EOL;
			$newConfig .= '?>';
			
			$_writer = new \Of\File\Writer();
			$_writer->setDirPath(ROOT . DS . 'etc')
			->setData($newConfig)
			->setFilename('Config')
			->setFileextension('php')
			->write();
			
			$_writer->setDirPath(ROOT . DS . 'etc')
			->setData(time())
			->setFilename('install_flag')
			->setFileextension('php')
			->write();

			$data = '<?php return '.time().'; ?>';
			$_writer->setDirPath(ROOT.DS.'public')
			->setData($data)
			->setFilename('generation')
			->setFileextension('php')
			->write();
			
			$response['error'] = 0;
			$response['message'] = 'Url saved successfully';
		} else {
			$response['message'] = 'Invalid request';
		}
		$this->jsonEncode($response);
	}
	

}