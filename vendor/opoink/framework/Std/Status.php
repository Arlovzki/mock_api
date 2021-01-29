<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Std;
 
class Status {
	
	const DISABLED = 0;
	const ENABLED = 1;
	
	public function toOptionArray(){
		return [
			self::ENABLED => 'Yes',
			self::DISABLED => 'No',
		];
	}
}