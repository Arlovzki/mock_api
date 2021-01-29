<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller;

class Css {
	
	protected $_config;
	protected $_lessBuilder;
	
	public function __construct(
		\Of\Config $Config,
		\Of\Less\Builder $LessBuilder
	){
		
		$this->_config = $Config;
		$this->_lessBuilder = $LessBuilder;
	}
	
	public function run($file){
		$css = $this->_lessBuilder
		->setConfig($this->_config)
		->build($file);

		if($css){
			echo header("Content-type: text/css", true);
			echo $css;
			exit;
			die;
		} else {
			return false;
		}
	}
}

?>