<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Std;

class Versioncompare {
	
	protected $currentVersion;
	protected $newVersion;
	
	public function setCurrentVersion($version = '1.0.0'){
		$this->currentVersion = $version;
		return $this;
	}
	
	public function setNewVersion($version = '1.0.0'){
		$this->newVersion = $version;
		return $this;
	}

	public function compare(){
		return version_compare($this->newVersion,$this->currentVersion);
	}
}