<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\File;

class Writer extends Dirmanager {
	
	protected $DirPath;
	protected $Data;
	protected $Filename;
	protected $Fileextension;
	
	/*
	*	Set the dir where the file will be writen
	*	Try to create DIR id not exists
	*/
	public function setDirPath($path){
		$this->DirPath = $path;
		$this->create($this->DirPath);
		return $this;
	}
	
	/*
	*	retrieve the DIR path
	*/
	public function getDirPath(){
		return $this->DirPath;
	}
	
	/*
	*	set the data to write into a file
	*/
	public function setData($data){
		$this->Data = $data;
		return $this;
	}
	
	/*
	*	retrieve data
	*/
	public function getData(){
		return $this->Data;
	}
	
	/*
	*	set file name
	*/
	public function setFilename($Filename){
		$this->Filename = $Filename;
		return $this;
	}
	
	/*
	*	get file name
	*/
	public function getFilename(){
		return $this->Filename;
	}
	
	/*
	*	set File extension
	*/
	public function setFileextension($Fileextension){
		$this->Fileextension = $Fileextension;
		return $this;
	}
	
	/*
	*	get File extension
	*/
	public function getFileextension(){
		return $this->Fileextension;
	}
	
	public function write() {
		$target = $this->getDirPath() . '/' . $this->getFilename() . '.' . $this->getFileextension();
		$fp = fopen($target, 'w');
		fwrite($fp, $this->getData());
		fclose($fp);
	}
}