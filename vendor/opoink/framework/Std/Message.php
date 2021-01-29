<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Std;

class Message extends \Of\Session\Session {

	protected $message = [];

	/*
	*	type of message
	*	primary, secondary, success, danger, warning, info
	*	default success
	*/
	public function setMessage($message, $type='success'){
		$this->message[] = [
			'type' => $type,
			'message' => $message
		]; 
		
		$this->setData('__message', $this->message);
	}
	
	public function getMessage(){
		$message = $this->getData('__message');
		return $message;
	}
	
	public function toHtml(){
		$messages = $this->getMessage();
		if(!$messages){
			return '';
		}
		
		$m = "<div class='general_notification_container'>";
		$m .= "<ul class='general_notification'>";
			foreach($this->getMessage() as $mesKey => $mesVal){
				$type = $mesVal['type'];
				$m .= "
				<li class='general_notification_message alert alert-{$type}'>
					<p class='m-0'>{$mesVal['message']}</p>
					<button class='notificationMessageClose'>
						<i class='far fa-times-circle'></i>
					</button>
				</li>";
			}
		$m .= "</ul>";
		$m .= "</div>";
		$this->unsetData('__message');
		return $m;
	}
}
?>