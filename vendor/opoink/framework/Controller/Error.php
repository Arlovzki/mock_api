<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller;

class Error {
	protected $error = [
		'_404' => 'Page Not Found',
		'_500' => 'Internal Server Error',
		'_503' => 'Service Unavailable',
		'_301' => 'Moved Permanently',
		'_302' => 'Temporary Redirect',
		'_400' => 'Bad Request',
		'_401' => 'Unauthorized',
		'_502' => 'Bad Gateway',
	];

	protected $_config;

	public function __construct(
		\Of\Config $Config
	){
		$this->_config = $Config;
	}

	public function run($code=404){
		$title = '';
		if(isset($this->error['_'.$code])){
			$title = $this->error['_'.$code];
		}

		header("HTTP/1.0 " . $code . " " . $title);
		$sysDir = ROOT.DS.'vendor'.DS.'opoink'.DS.'framework'.DS.'View'.DS.'Sys'.DS;

		$layoutFile = $sysDir.'Layout'.DS.'error.phtml';
		$tplFile = $sysDir.'Templates'.DS.'error'.DS.'error'.$code.'.phtml';
		$template = '';
		if(file_exists($tplFile)){
			ob_start();
				include($tplFile);
				$template = ob_get_contents();
			ob_end_clean();
		}

		if(file_exists($layoutFile)){
			include($layoutFile);
		}
		/*echo $code . ' ' . $msg;*/
		die;
	}
}

?>