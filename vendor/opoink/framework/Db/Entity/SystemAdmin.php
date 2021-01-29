<?php 
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Db\Entity;

class SystemAdmin extends \Of\Db\Entity {
	
	const COLUMNS = [
		'id',
		'firstname',
		'lastname',
		'email',
		'password',
		'created_at',
		'update_at',
		'status',
	];
	
	protected $tablename = 'system_admin';
	protected $primaryKey = 'id';
}