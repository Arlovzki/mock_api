<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Db\Schema\Sys;

use Of\Std\Status;

class Extension extends \Of\Db\Createtable {
	
	public function createSchema(){
		$table = [
			'name' => 'extension',
			'primary_key' => 'id',
			'columns' => [
				[
					'name'=>'id',
					'type'=> self::_INT,
					'length' => 10,
					'ai' => self::_AI,
					'comment' => $this->escape('Id'),
				],
				[
					'name'=>'vendor',
					'type'=> self::_VARCHAR,
					'length' => 255,
					'nullable' => false,
					'comment' => $this->escape('vandor name'),
				],
				[
					'name'=>'extension',
					'type'=> self::_VARCHAR,
					'length' => 255,
					'nullable' => false,
					'comment' => $this->escape('extension name'),
				],
				[
					'name'=>'version',
					'type'=> self::_VARCHAR,
					'length' => 255,
					'nullable' => false,
					'comment' => $this->escape('extension version'),
				],
				[
					'name'=>'created_at',
					'type'=> self::_TIMESTAMP,
					'default'=> self::_CURRENT_TIMESTAMP,
					'nullable' => false,
					'comment' => $this->escape('date of creation'),
				],
				[
					'name'=>'update_at',
					'type'=> self::_TIMESTAMP,
					'default'=> self::_CURRENT_TIMESTAMP,
					'nullable' => false,
					'onupdate' => self::_CURRENT_TIMESTAMP,
					'comment' => $this->escape('time of update'),
				],
				[
					'name'=>'status',
					'type'=> self::_VARCHAR,
					'default' => Status::ENABLED,
					'length' => 1,
					'nullable' => false,
					'comment' => $this->escape('status'),
				],
			]
		];
		
		$this->setTablename($table['name']);
		$this->setPrimarykey($table['primary_key']);
		foreach($table['columns'] as $colKey => $colVal){
			$this->addColumn($colVal);
		}
		$this->save();
	}
}