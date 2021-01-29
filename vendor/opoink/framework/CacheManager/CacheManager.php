<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\CacheManager;

class CacheManager extends \Of\File\Dirmanager {

	const SERVICES = [
		'less',
		'xml',
		'deployed_files',
		'database',
	];

	const UPDATED = 'updated';
	const OUTDATED = 'outdated';

	protected $result = [
		'error' => 1,
		'message' => ''
	];

	protected $targetDir;

	public function setTargetDir($targetDir) {
		$this->targetDir = $targetDir;
		return $this;
	}

	public function execute(){
		$purge = (bool)$this->deleteDir($this->targetDir);
		return $purge;
	}

	public function getStatus($key=null){
		$targetStatus = ROOT . DS . 'etc' . DS . 'CacheStatus.php';

		if(!file_exists($targetStatus)){
			$services = self::SERVICES;

			$s = [];
			foreach($services as $service){
				$s[$service] = ['status' => self::OUTDATED];
			}
			$this->saveStatus($s);
			
		} else {
			$s = include($targetStatus);
		}
		
		if($key && is_array($s)){
			if(isset($s[$key])){
				return $s[$key]['status'];
			}
		} else {
			return $s;
		}
	}

	protected function saveStatus($s){
		$status = '<?php' . PHP_EOL;
		$status .= 'return ' . var_export($s, true) . PHP_EOL;
		$status .= '?>';
		
		$_writer = new \Of\File\Writer();
		$_writer->setDirPath(ROOT . DS . 'etc')
		->setData($status)
		->setFilename('CacheStatus')
		->setFileextension('php')
		->write();
	}
}