<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Std;
class Pagination {
	
	protected $current_page;
	protected $per_page = 20;
	protected $total_count;
	protected $param;

	public function setParam($param){
		$this->param = $param;
		return $this;
	}
	
	public function set($page=1, $total_count=0, $per_page=20){
		$this->current_page = 1;
		if (!empty($page)) {
			$this->current_page = (int)$page;
		}
		$this->total_count = (int)$total_count;
		$this->per_page = (int)$per_page;
	}
	
	public function setCurrentPage($current_page){
		$this->current_page = $current_page;
	}
	
	public function offset() {
		// Assuming 20 items per page:
		// page 1 has an offset of 0    (1-1) * 20
		// page 2 has an offset of 20   (2-1) * 20
		// in other words, page 2 starts with item 21
		return ($this->current_page - 1) * $this->per_page;
	}

	public function total_pages() {
		return ceil($this->total_count/$this->per_page);
	}
	
	public function currentPage() {
		return $this->current_page;
	}

	public function nextp($next="") {
		$n = $this->current_page + $next;
		if ($n <= $this->total_pages()) {
			return $n;
		}
	}
	public function prevp($prev="") {
		$n = $this->current_page - $prev;
		if ($n >= 1) {
			return $n;
		}
	}
	
	public function pages(){
		$o = new \stdClass();
		$o->p200 	= $this->prevp(200);
		$o->p50 	= $this->prevp(50);
		$o->p10 	= $this->prevp(10);
		$o->p3		= $this->prevp(3);
		$o->p2		= $this->prevp(2);
		$o->p1		= $this->prevp(1);
		$o->cp		= $this->current_page;
		$o->n1		= $this->nextp(1);	
		$o->n2		= $this->nextp(2);	
		$o->n3		= $this->nextp(3);	
		$o->n10		= $this->nextp(10);	
		$o->n50		= $this->nextp(50);	
		$o->n200	= $this->nextp(200);
		return $o;
	}
}
?>