<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller;

use Of\Constants;

class File extends Filecontroller {
	protected $_config;

	public function __construct(
		\Of\Config $Config,
		\Of\Http\Url $Url
	){
		$this->_config = $Config;
		$this->_url = $Url;
	}

	public function run($file){
		$path = $this->getPath($file);
		if($path){
			$realPath = $this->getRealPath($path);
			
			$targetFile = $realPath.$file;
			$destinationFile = ROOT.$path.DS.$file;

			if(file_exists($targetFile)){
				if($this->_config->getConfig('mode') != Constants::MODE_PROD){
					$this->makeDir(ROOT.$path);
					copy($targetFile, $destinationFile);
				}
				
				$this->renderFile($targetFile, $file);
			}
		}
	}
}
?>