<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Db\Schema\Sys;

use Of\Std\Status;

class Admin extends \Of\Db\Createtable {
	
	public function createSchema(){
		$table = [
			'name' => 'system_admin',
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
					'name'=>'firstname',
					'type'=> self::_VARCHAR,
					'length' => 255,
					'nullable' => false,
					'comment' => $this->escape('firstname'),
				],
				[
					'name'=>'lastname',
					'type'=> self::_VARCHAR,
					'length' => 255,
					'nullable' => false,
					'comment' => $this->escape('last name'),
				],
				[
					'name'=>'email',
					'type'=> self::_VARCHAR,
					'length' => 255,
					'nullable' => false,
					'comment' => $this->escape('email'),
				],
				[
					'name'=>'password',
					'type'=> self::_VARCHAR,
					'length' => 255,
					'nullable' => false,
					'comment' => $this->escape('password'),
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
					'type'=> self::_INT,
					'default' => Status::ENABLED,
					'length' => 1,
					'nullable' => false,
					'comment' => $this->escape('status'),
				]
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