<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Db;

class Select extends \Zend\Db\Sql\Select {

	protected function escape($string){
		return addcslashes((string) $string, "\x00\n\r\\'\"\x1a");
	}

	public function setComlumn($value, $index=null, $prefixColumnsWithTable=true){
		if(gettype($index) != 'NULL'){
			$this->columns[$index] = $value;
		} else {
			$this->columns[] = $value;
		}
		$this->prefixColumnsWithTable = (bool)$prefixColumnsWithTable;
	}

	public function removeColumn($value){
		if(isset($this->columns[$value])){
			unset($this->columns[$value]);
		} else {
			$col = array_search($value, $this->columns, true);
			if(is_int($col) && isset($this->columns[$col])){
				unset($this->columns[$col]);
			}
		}
	}

	public function count($col='*'){
		$this->setComlumn(new \Zend\Db\Sql\Expression('COUNT('.$col.')'), 'count');
	}

	public function like($table, $comlumns = [], $queryString='', $isNeedEachWord=false){
		$queries = explode(" ", $queryString);
		$rebuildQuery = [];
		
		foreach($comlumns as $key => $val) {
			if(count($queries) == 1 && count($comlumns) == 1){
				$rebuildQuery[] = "`".$table."`.`".$val."` LIKE '%".$this->escape($queryString)."%'";
			}
			else {
				$rebuildQuery[] = "(`".$table."`.`".$val."` LIKE '%".$this->escape($queryString)."%')";
			}
		}
		if($isNeedEachWord){
			if(count($queries) > 1){
				foreach($queries as $query){
					$rebuildStrings = [];
					foreach($comlumns as $key => $val) {
						$rebuildStrings[] = "`".$table."`.`".$val."` LIKE '%".$this->escape($query)."%'";
					}
					
					$rebuildQuery[] = "(" . implode(" OR ", $rebuildStrings) . ")";
				}
			}
		}
		$queryStringNew = implode(" OR ", $rebuildQuery);
		
		if(count($queries) == 1 && count($comlumns) == 1){
			return $queryStringNew;
		} else {
			return "(" . $queryStringNew . ")";
		}
	}

	public function getGroup(){
		return $this->group;
	}

	public function setGroup($group = null){
		if($group === null){
			$this->group = null;
		} else {
			$this->group($group);
		}
        return $this;
	}
}
?>