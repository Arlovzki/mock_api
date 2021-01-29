<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Session;

class FormSession extends Session {
	
	protected $_request;
	
	public function __construct(
		\Of\Http\Request $Request
	){
		$this->_request = $Request;
		parent::__construct();
	}
	
	public function generateFormKey(){
		$_SESSION['form_key'] = [
			'key' => \Of\Std\Password::generate(20),
			'exp' => time() + \Of\Constants::FORM_KEY_DURATION
		];
		return $this;
	}
	
	public function getFormKey(){
		if(isset($_SESSION['form_key'])){
			$formKey = $_SESSION['form_key']['key'];
			return $formKey;
		}
	}
	
	public function validateFormKey(){
		$postKey = $this->_request->getParam('form_key');
		$sesKey = null;
		if(isset($_SESSION['form_key'])){
			if(isset($_SESSION['form_key']['exp'])){
				$exp = $_SESSION['form_key']['exp'];
				if($exp >= time()){
					$sesKey = $_SESSION['form_key']['key'];
					return (bool)($postKey === $sesKey);
				}
			}
		}
	}
}
?>