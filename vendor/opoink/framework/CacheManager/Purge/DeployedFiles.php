<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\CacheManager\Purge;

class DeployedFiles extends \Of\CacheManager\CacheManager {

	protected $publicDir = ROOT.DS.'public'; 

	public function __construct(){
		$generation = $this->publicDir.DS.'generation.php';
		
		$this->targetDir = $this->publicDir.DS.'deploy';
		if(file_exists($generation)){
			$this->targetDir .= include($generation);
		}
	}

	public function execute(){
		$this->result['error'] = 0;
		$this->result['message'] = 'Deployed cached files successfully purged and generation time renewed.';

		if(is_dir($this->targetDir)){
			$purge = parent::execute();
			if(!$purge){
				$this->result['error'] = 1;
				$this->result['message'] = 'Can\'t purge deployed files now, please try again.';
			}
		}
		$generation = $this->publicDir.DS.'generation.php';
		if(file_exists($generation)){
			unlink($generation);
		}
		if(!file_exists($generation)){
			$data = '<?php return '.time().'; ?>';
			$writer = new \Of\File\Writer();
			$writer->setDirPath($this->publicDir)
			->setData($data)
			->setFilename('generation')
			->setFileextension('php')
			->write();
		}
		$status = $this->getStatus();
		$status['deployed_files']['status'] = self::UPDATED;
		$status['xml']['status'] = self::OUTDATED;

		$this->saveStatus($status);
		return $this->result;
	}
}