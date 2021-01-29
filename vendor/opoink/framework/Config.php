<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of;

class Config {
	
	protected $config;
	
	public function __construct(){
		$config = ROOT.DS.'etc'.DS.'Config.php';
		$this->config = include($config);
	}
	
	public function getConfig($param=null){
		if($param){
			if(isset($this->config[$param])){
				return $this->config[$param];
			}
		} else {
			return $this->config;
		}
	}
}

?>