<?php 
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Db;

use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Of\Db\Select;
use Of\Std\Pagination;

class Entity extends \Zend\Db\TableGateway\TableGateway {
	
	const COLUMNS = [];
	
	protected $tablename;
	protected $primaryKey;
	
	protected $_adapter;
	protected $_select;
	protected $_request;
	protected $_di;
	protected $_pagination;
	
	protected $dbConfig;
	protected $datas = [];
	protected $allowedLimit = [10, 20, 50, 100, 200];

	protected $isDbCahed = false;
	protected $config;

	/*
	*	default is null config cache lif time will be used
	*/
	protected $cacheMaxLifeTime; 

	public function __construct(
		\Of\Http\Request $Request,
		$adapter=null
	){
		$this->_request = $Request;
		if($this->setAdapter($adapter)){
			parent::__construct($this->getTablename(), $this->_adapter, new RowGatewayFeature($this->primaryKey));
			$this->_select = new Select($this->getTablename());
		}
		$this->_di = new \Of\Std\Di();
		$this->_pagination = new Pagination();

		$config = ROOT . DS . 'etc'. DS .'Config.php';
		if(file_exists($config)){
			$this->config = include($config);
		} else {
			$this->config = [
				'mode' => \Of\Constants::MODE_DEV
			];
		}
	}
	
	/*
	*	set the database addapter
	*	also set the sql
	*/
	private function setAdapter($adapter=null){
		$databaseConfig = ROOT . DS . 'etc'. DS .'database.php';
		
		if(!file_exists($databaseConfig)){
			return null;
		}
		$this->dbConfig = include($databaseConfig);
		$this->_adapter = new \Zend\Db\Adapter\Adapter($this->dbConfig);
		
		return true;
	}
	
	/*
	*	return table namespace
	*	with prefix if set 
	*/
	public function getTablename($tableName=null){
		if(!$tableName) {
			return $this->dbConfig['table_prefix'] . $this->tablename;
		} else {
			return $this->dbConfig['table_prefix'] . $tableName;
		}
	}
	
	/*
	*	return escaped string
	*/
	protected function escape($string){
		return addcslashes((string) $string, "\x00\n\r\\'\"\x1a");
	}

	/*
	*	set whether the db is cacheable or not
	*/
	public function setIsCache($isCache = true){
		$this->isDbCahed = $isCache;
		return $this;
	}

	/*
	*	get the value of is cache
	*/
	public function getIsCache(){
		return $this->isDbCahed;
	}

	/*
	*	set the max age of cached db file
	*/
	public function setCacheMaxLifeTime($cacheMaxLifeTime = null){
		$this->cacheMaxLifeTime = $cacheMaxLifeTime;
		return $this;
	}

	/*
	*	get the max age of cached db file
	*/
	public function getCacheMaxLifeTime(){
		return $this->cacheMaxLifeTime;
	}

	/*
	*	execute the query string
	*	get cache file db file instead of 
	*	going back into the database if the isDbCahed is true
	*	by default isDbCahed is false
	*	db cache will never gonna happen on developer mode
	*/
	protected function __getQuery($qry, $limit=1){
		$datas = $this->getCached($qry);
		if(!$datas){
			$datas = $this->_adapter->query(
				$qry,
				\Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE
			);
			$datas = $datas->toArray();
			
			if(isset($this->config['mode']) && $this->config['mode'] == \Of\Constants::MODE_PROD && $this->getIsCache() == true && count($datas) > 0){
				$dataString = '<?php' . PHP_EOL;
				$dataString .= 'return ' . var_export($datas, true) . PHP_EOL;
				$dataString .= '?>';
		
				$_writer = $this->_di->get('\Of\File\Writer');
				$_writer->setDirPath(ROOT . DS . 'Var' . DS . 'db')
				->setData($dataString)
				->setFilename(opoink_hash($qry))
				->setFileextension('php')
				->write();
			}
		}

		return $this->setCollection($limit, $datas);
	}

	/*
	*	get the cache db and set it into collection
	*/
	protected function getCached($qry){
		if(isset($this->config['mode']) && $this->config['mode'] == \Of\Constants::MODE_PROD){
			if($this->getIsCache()){
				$maxAge = (int)$this->getCacheMaxLifeTime();
				if($maxAge == 0){
					$maxAge = $this->config['cache']['max-age'];
				}
				$fileName = opoink_hash($qry);
				$target = ROOT . DS . 'Var' . DS . 'db' . DS . $fileName . '.php'; 
				if(file_exists($target)){
					$expiry = filemtime($target) + $maxAge;
					if($expiry > time()){
						$data = include($target);
						return $data;
					}
				}
			}
		}
		return null;
	}
	
	/*
	*	return collection of data
	*	@ $limit | limit
	*	@ $offset | offset
	*/
	public function getCollection($limit=0, $offset=0){
		if($limit >= 1){
			$this->_select->limit($limit);
		}
		if($offset >= 1){
			$this->_select->offset($offset);
		}

		return $this->__getQuery($this->getLastSqlQuery(), $limit);
	}

	/*
	*	set the collected data and put that into an array
	*	of current class instance
	*/
	public function setCollection($limit, $datas){
		$result = null;
		if($limit == 1){
			if(isset($datas[0])){
				$result = $this->_di->make(get_class($this));
				$result->setDatas($datas[0]);
			}
		} else {
			$result = [];
			foreach($datas as $data){
				$newDataEntity = $this->_di->make(get_class($this));
				$newDataEntity->setDatas($data);
				$result[] = $newDataEntity;
			}
		}
		return $result;
	}
	
	/*
	*	get last sql query query
	*/
	public function getLastSqlQuery(){
		return $this->getSql()->getSqlStringForSqlObject($this->_select);
	}
	
	/*
	*	most of the time we use join query sql
	*	RowGateway can't do save()
	*	because of other columns are added
	*	that case we use this update by ID
	*	@param | id database id
	*	@param | fields key value pair of to update data
	*/
	public function updateById($id, $fields = []){
		$this->resetQuery();
		return $this->update($fields, [$this->primaryKey => $id]);
	}
	
	public function updateByColumn($column=[], $fields = []){
		$this->resetQuery();
		return $this->update($fields, $column);
	}
	
	public function resetQuery($tablename = null){
		if(!$tablename){
			$tablename = $this->getTableName();
		}
		$this->_select = new Select($tablename);
		return $this;
	}
	
	public function getByColumn($column=[], $limit=1, $group=null, $isReset=true) {
		if($isReset){$this->resetQuery();}
		if(count($column) <= 0){$column = $this->datas;}
		
		foreach($column as $key => $value){
			$this->_select->where([$key => $value]);
		}
		
		if($group){$this->_select->group($group);}	
		$datas = $this->getCollection($limit);
		return $datas;
	}
	
	protected function getFinalResponse($limit=20){
		$page = $this->getParam('page');
		if(!$page){
			$page = 1;
		}

		$this->_select->count('`'.$this->getTablename() . '`.`'.$this->primaryKey.'`');
		
		/* need to remove group if any to prevent count mistake */
		$group = $this->_select->getGroup();
		$this->_select->setGroup(null);
		$c = $this->__getQuery($this->getLastSqlQuery());

		$count = 0;
		if($c){
			$count = $c->getData('count');

			/* need to bring back the group if it is declared */
			if(is_array($group)){
				$this->_select->setGroup($group);
			}
		}
		/* need to remove count to prevent fetching only 1 data */
		$this->_select->removeColumn('count');
		$this->_pagination->set($page, $count, $limit);
		
		$datas = $this->getCollection($limit, $this->_pagination->offset());
		
		$o = [
			'total_count' => $count,
			'total_page' => $this->_pagination->total_pages(),
			'current_page' => $this->_pagination->currentPage(),
			'per_page' => $limit,
			'allowed_limits' => $this->allowedLimit,
			'datas' => $datas
		];
		return $o;
	}
	
	/*
	*	return string
	*	uses for LIKE queries column LIKE '%query%'
	*	@param column | column name of the table
	*	$param queryString | the whole query string
	*	$param tableName | optional if not set default table name will be used
	*/
	protected function likeQuery($comlumns = [], $queryString='', $tableName=null, $isNeedEachWord=false){
		$table = $this->getTablename($tableName);
		return $this->_select->like($table, $comlumns, $queryString, $isNeedEachWord);
	}
	
	protected function beetweetQuery($fromColumn, $toColumn, $from, $to, $tableName=null){
		$table = $this->getTablename($tableName);
		return "((`".$table."`.`".$fromColumn."` >= '".$this->escape($from)."') AND (`".$table."`.`".$toColumn."` <= '".$this->escape($to)."'))";
	}
	
	public function where($column, $value, $condition='=', $tableName=null){
		$table = $this->getTablename();
		if($tableName){
			$table = $this->getTablename($tableName);
		}
		$this->_select->where('`'.$table.'`.`'.$column.'` ' . $condition . " '" . $this->escape($value) . "'");
		return $this;
	}
	
	public function getParam($param=null){
		return $this->_request->getParam($param);
	}
	
	public function setOrderBy($defaultOrderBy='id', $direction=null, $tablename=null){
		$d = 'DESC';
		if($direction){
			if($direction == 'ASC' || $direction == 'asc'){
				$d = 'ASC';
			}
		}
		
		if($tablename){
			$table = $this->getTablename($tablename);
		} else {
			$table = $this->getTablename();
		}
		
		$orderby = $table.'.'.$defaultOrderBy;
		$this->_select->order([$orderby => $d]);
		return $this;
	}
	
	protected function notIn($column, $values=[]){
		if(count($values) > 0){
			$value = "('" . implode("','",$values) . "')";
			$this->_select->where('`'.$column.'` NOT IN ' . $value);
		}
	}
	
	protected function in($column, $values=[], $tableName=null){
		if($tableName){
			$table = $this->getTablename($tableName);
		} else {
			$table = $this->getTablename();
		}
		if(count($values) > 0){
			$value = "('" . implode("','",$values) . "')";
			$this->_select->where('`'.$table.'`.`'.$column.'` IN ' . $value);
		}
	}
	
	
	/*
	*	this will validate required fields
	*	id the field is array will recursive check the array for validation
	*	using validateRequiredFieldHelper function
	*/
	protected function validateRequiredField($postFields=[], $requiredFields=[]){
		$result = [
			'error' => 0,
			'message' => 'Success',
		];
		
		foreach($requiredFields as $requiredField){
			$helper = $this->validateRequiredFieldHelper($postFields, $requiredField);
			if(!is_int($helper)){
				$result = [
					'error' => 1,
					'message' => $helper,
				];
				break;
			}
		}
		
		return $result;
	}
	
	protected function validateRequiredFieldHelper($postFields, $requiredField, $recursive=0){
		$result = 0;
		$matchFound = false;
		foreach($postFields as $key => $val){
			if($key == $requiredField){
				$matchFound = true;
				if($val == null || $val == ""){
					$result = 0;
				} else {
					$result++;
				}
				break;
			} else {
				if(is_array($val)){
					$recurse = $this->validateRequiredFieldHelper($val, $requiredField, 1);
					if($recurse > 0){
						$result = 1;
						$matchFound = true;
					}
				}
			}
		}
		
		if($result > 0 && $matchFound == true){
			$output = 1;
		} else {
			$output = $requiredField;
		}
		return $output;
	}
	
	
	protected function __join($prefix, $onEquals, $tableName=null, $tabePrefix='', $join='left', $tableColumn='id', $comlumns=[]){
		$tn = $this->getTableName($tableName);
		
		$col = [];
		foreach($comlumns as $comlumn){
			$col[$prefix.$comlumn] = $comlumn;
		}
		$this->_select->join(
			[$tabePrefix.$tn => $tn],
			$onEquals . ' = '.$tabePrefix.$tn.'.'.$tableColumn,
			$col,
			$join
		);
		return $this;
	}
	
	public function setData($key, $val){
		$this->datas[$key] = $val;
		return $this;
	}
	
	public function setDatas($datas = []){
		if(is_array($datas)){
			foreach($datas as $key => $val){
				$this->setData($key, $val);
			}
		}
		return $this;
	}
	
	public function getData($key=null){
		if($key){
			if(isset($this->datas[$key])){
				return $this->datas[$key];
			}
		} else {
			return $this->datas;
		}
	}
	
	public function resetDatas(){
		$this->datas = [];
	}
	
	public function __save($datas = null){
		$columns = get_class($this)::COLUMNS;
		$data = $this->datas;
		if($datas){
			$data = $datas;
		}
		$d = [];
		foreach($data as $key => $val){
			if(in_array($key, $columns)){
				$d[$key] = $val;
			}
		}
		if(count($d) > 0){
			if(isset($d[$this->primaryKey])){
				$this->updateById($d[$this->primaryKey], $d);  /* update on this part */
				return $d[$this->primaryKey];
			} else {
				$this->insert($d); /* insert new */
				return $this->getLastInsertValue();
			}
		}
	}
	public function delete($datas = null){
		$d = $this->datas;
		if($datas){
			$d = $datas;
		}
		if(isset($d[$this->primaryKey])){
			return parent::delete([$this->primaryKey => $d[$this->primaryKey]]);
		}
	}
}