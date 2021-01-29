<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemCacheIndex extends Sys {
	
	protected $pageTitle = 'Opoink Cache Management';
	protected $cacheManager;
	protected $cacheStatus;

	public function __construct(
		\Of\Session\SystemSession $SystemSession,
		\Of\Session\FormSession $FormSession,
		\Of\Http\Request $Request,
		\Of\Http\Url $Url,
		\Of\Std\Message $Message,
		\Of\CacheManager\CacheManager $CacheManager
	){
		parent::__construct($SystemSession,$FormSession,$Request,$Url,$Message);
		$this->cacheManager = $CacheManager;
	}

	public function run(){
		$this->requireInstalled();
		$this->requireLogin();

		$this->addInlineJs();
		return $this->renderHtml();
	}
}