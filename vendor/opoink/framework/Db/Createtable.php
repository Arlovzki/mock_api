<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Db;

class Createtable {

	const _BIGINT = 'BIGINT';
	const _BINARY = 'BINARY';
	const _BLOB = 'BLOB';
	const _BOOLEAN = 'BOOLEAN';
	const _CHAR = 'CHAR';
	const _DATE = 'DATE';
	const _DATETIME = 'DATETIME';
	const _DECIMAL = 'DECIMAL';
	const _FLOAT = 'FLOAT';
	const _INT = 'INTEGER';
	const _TINYINT = 'TINYINT';
	const _TEXT = 'TEXT';
	const _TIME = 'TIME';
	const _TIMESTAMP = 'timestamp';
	const _VARBINARY = 'VARBINARY';
	const _VARCHAR = 'VARCHAR';	
	
	const _AI = 'auto_increment';
	const _NULL = 'NULL';
	const _NOTNULL = 'NOT NULL';
	const _CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';
	
	/*
	*	table name
	*/
	protected $tablename;
	
	/*
	*	this are created column by addColumn
	*/
	protected $columns = [];
	
	/*
	*	type of the column default is integer
	*/
	protected $type = 'INTEGER';
	
	/*
	*	name of the column
	*/
	protected $name;
	
	/*
	*	length of the column
	*/
	protected $length;
	
	/*
	*	BOOLEAN the column nullable true or false
	*/
	protected $nullable;
	
	/*
	*	the default value for the column
	*/
	protected $default;
	
	/*
	*	the comennt for column
	*/
	protected $comment;
	
	/*
	*	the primary column for the table
	*/
	protected $primarykey;
	
	/*
	*	the on update value of table
	*/
	protected $onupdate;
	
	/*
	*	database config info
	*/
	protected $db;
	
	/*
	*	database adapter
	*/
	protected $_adapter;
	
	public function __construct(
		\Of\Std\Versioncompare $Versioncompare
	){
		$this->_versioncompare = $Versioncompare;
		$path = ROOT.DS.'etc';
		
		$filename = 'database.php';
		$configPath = $path.DS.$filename;
		try {
			$this->db = include($configPath);
		} catch (\Exception $e) {
			echo 'Caught exception: ' . $e->getMessage();
			die;
		}
	}
	
	/*
	*	set table name
	*/
	public function setTablename($tablename){
		$this->tablename = $this->db['table_prefix'].$this->escape($tablename);
	}
	
	/*
	*	set type of the column
	*/
	public function setType($type){
		$this->type = $type;
	}
	
	/*
	*	get type of the column
	*/
	public function getType(){
		return $this->type;
	}
	
	/*
	*	set name of the column
	*/
	public function setName($name){
		$this->name = $name;
	}
	
	/*
	*	get name of the column
	*/
	public function getName(){
		return $this->name;
	}
	
	/*
	*	set length of the column
	*/
	public function setLength($length){
		$this->length = $length;
	}
	
	/*
	*	get length of the column
	*/
	public function getLength(){
		return $this->length;
	}
	
	/*
	*	set is column is nullable or not
	*/
	public function setNullable($nullable){
		$this->nullable = $nullable;
	}
	
	/*
	*	get nullable state
	*/
	public function getNullable(){
		return $this->nullable;
	}
	
	/*
	*	set the default value of the column
	*/
	public function setDefault($default){
		$this->default = $default;
	}
	
	/*
	*	get the default value of the column
	*/
	public function getDefault(){
		return $this->default;
	}
	
	/*
	*	set the comment for coulmun
	*/
	public function setComment($comment){
		$this->comment = $this->escape($comment);
	}
	
	/*
	*	get the default value of the column
	*/
	public function getComment(){
		return $this->comment;
	}
	
	/*
	*	set the primary key for table
	*/
	public function setPrimarykey($primarykey){
		$this->primarykey = $primarykey;
	}
	
	/*
	*	get the default value of the column
	*/
	public function getPrimarykey(){
		return $this->primarykey;
	}
	
	/*
	*	set on update value
	*/
	public function setOnupdate($primarykey){
		$this->onupdate = $primarykey;
	}
	
	/*
	*	get on update value
	*/
	public function getOnupdate(){
		return $this->onupdate;
	}
	
	/*
	*	this will add a new column for the table
	*/
	public function addColumn($columnValue = array()) {
		if(isset($columnValue['type'])){
			$this->setType($columnValue['type']);
		}
		if(isset($columnValue['length'])){
			$this->setLength($columnValue['length']);
		}
		if(isset($columnValue['name'])){
			$this->setName($columnValue['name']);
		}
		if(isset($columnValue['nullable'])){
			$this->setNullable($columnValue['nullable']);
		}
		if(isset($columnValue['default'])){
			$this->setDefault($columnValue['default']);
		}
		if(isset($columnValue['comment'])){
			$this->setComment($columnValue['comment']);
		}
		if(isset($columnValue['onupdate'])){
			$this->setOnupdate($columnValue['onupdate']);
		}
		
		$column = "";
		if($this->getName() != null){
			$column .= "`{$this->getName()}` {$this->getType()}";
			if($this->length){
				$column .= "({$this->getLength()})";
			}
			if($this->nullable){
				$column .= " NULL ";
			} else {
				$column .= " NOT NULL ";
			}
			if($this->default){
				$column .= "DEFAULT {$this->getDefault()} ";
			}
			if($this->onupdate){
				$column .= "ON UPDATE {$this->getOnupdate()} ";
			}
			if($this->getName() == $this->getPrimarykey()){
				$column .= "auto_increment ";
			}
			if($this->comment){
				$column .= "COMMENT '{$this->getComment()}' ";
			}
		}
		
		$this->columns[] = $column;
		$this->resetColumn();
		return $this;
	}
	
	/*
	*	after adding column we have to reset our variable
	*	this function will do it for us
	*/	
	protected function resetColumn(){
		$this->default = null;
		$this->nullable = null;
		$this->length = null;
		$this->name = null;
		$this->onupdate = null;
		$this->comment = null;
		$this->type = 'INTEGER';
	}
	
	public function getQueryString() {
		$query = 'CREATE TABLE IF NOT EXISTS `'.$this->getTablename().'` (';
		$query .= implode(', ', $this->columns);
		if($this->getPrimarykey()){
			$query .= ', PRIMARY KEY (`'.$this->getPrimarykey().'`) ';
		}
		$query .= ')ENGINE=InnoDB DEFAULT CHARSET=utf8;';
		
		return $query;
	}
	
	public function addForeignKey($tableName, $column, $referenceTableName, $referenceColumn){	
		$query = "ALTER TABLE `".$this->getTablename($tableName)."` ";
		$query .= "ADD FOREIGN KEY (`".$column."`) REFERENCES ".$this->getTablename($referenceTableName)."(`".$referenceColumn."`); ";
		$this->save($query);
	}
	
	public function saveUpdate(){	
		$query = "ALTER TABLE `".$this->getTablename()."` ";
		$query .= "ADD ".implode(', ', $this->columns).";";
		$this->save($query);
	}
	
	public function save($query=null){
		if(!$query){
			$qry = $this->getQueryString();
		} else {
			$qry = $query;
		}
		$save = $this->_adapter->query(
			$qry,
			\Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE
		);
		$this->tablename = null;
		$this->primarykey = null;
		$this->columns = [];
		return $save;
	}
	
	protected function escape($string){
		return addcslashes((string) $string, "\x00\n\r\\'\"\x1a");
	}
	
	/*
	*	get table name
	*/
	protected function getTablename($tableName=null){
		if($tableName){
			return $this->db['table_prefix'].$tableName;
		}
		return $this->tablename;
	}
	
	public function setAdapter($adapter=null){
		if(!$adapter){
			$adapter = new \Zend\Db\Adapter\Adapter($this->db);
		}
		$this->_adapter = $adapter;
		return $this;
	}
	
	protected function versionCompare($currentVersion='1.0.0', $newVersio='1.0.0'){
		return $this->_versioncompare
		->setCurrentVersion($currentVersion)
		->setNewVersion($newVersio)
		->compare();
	}
}

?>