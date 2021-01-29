<?php 
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\File;

class Dirmanager {
	
	public function create($path){
		$create = false;
		if(!is_dir($path)){
			if (mkdir($path, 0777, true)){
				$create = true;
			}
		}
		return $create;
	}
	
	public function createDir($path){
		return $this->create($path);
	}
	
	public function deleteDir($dirPath) {
		if (! is_dir($dirPath)) {
			return false;
			/* throw new \InvalidArgumentException("$dirPath must be a directory"); */
		}
		if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
			$dirPath .= '/';
		}
		$files = glob($dirPath . '*', GLOB_MARK);
		foreach ($files as $file) {
			if (is_dir($file)) {
				$this->deleteDir($file);
			} else {
				unlink($file);
			}
		}
		return rmdir($dirPath);
	}
	
	public function copyDir($src, $dst) { 
		$dir = opendir($src);
		$this->create($dst);
		while(false !== ( $file = readdir($dir)) ) { 
			if (( $file != '.' ) && ( $file != '..' )) { 
				if ( is_dir($src . '/' . $file) ) { 
					$this->copyDir($src . '/' . $file,$dst . '/' . $file); 
				} 
				else { 
					copy($src . '/' . $file,$dst . '/' . $file); 
				} 
			} 
		} 
		closedir($dir); 
	}	
}