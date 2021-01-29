<?php 
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Db\Entity;

class Extension extends \Of\Db\Entity {
	
	const COLUMNS = [
		'id',
		'vendor',
		'extension',
		'version',
		'created_at',
		'update_at',
		'status',
	];
	
	protected $tablename = 'extension';
	protected $primaryKey = 'id';
}