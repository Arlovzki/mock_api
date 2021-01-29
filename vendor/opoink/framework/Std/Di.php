<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Std;

class Di {

	protected $_di;
	protected $config;

	public function __construct(){
		$this->_di = \DI\ContainerBuilder::buildDevContainer();

	}

	public function get($class){
		return $this->_di->get($this->checkOveride($class));
	}

	public function make($class){
		return $this->_di->make($this->checkOveride($class));
	}

	private function checkOveride($class){
		if(is_array($this->config)){
			if(isset($this->config['class_override'])){
				if(isset($this->config['class_override'][$class])){
					return $this->config['class_override'][$class];
				}
			}
		}
		return $class;
	}
}


?>