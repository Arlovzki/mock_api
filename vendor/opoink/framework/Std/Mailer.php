<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Std;

class Mailer {
	
	/* url class */
	protected $_url;
	
	/*
	*	holds an array of to or Cc or Bcc
	*	for later bulk mail
	*/
	protected $To = [];
	protected $Cc = [];
	protected $Bcc = [];
	
	/*
	*	sender email and name
	*/
	protected $from = ['name'=>'', 'email'=>''];
	
	/*
	*	email subject
	*/
	protected $subject = "Opoink say's hello";
	protected $tamplatePath;
	protected $message = "";
	
	/*
	*	the header of the email
	*/
	protected $headers = [
		'mime' 			=> 'MIME-Version: 1.0',
		'contentType' 	=> 'Content-type: text/html; charset=iso-8859-1',
		'xPriority' 	=> 'X-Priority: 3 (Normal)',
		'To' 			=> '',
		'From'			=> '',
		'Cc'			=> '',
		'Bcc'			=> '',
	];
	
	public function __construct(
		\Of\Http\Url $Url
	){
		$this->_url = $Url;
	}
	
	/*
	*	set the email mime version
	*/
	public function setMime($mime=null){
		if($mime){
			$this->headers['mime'] = $mime;
		}
		return $this;
	}
	
	/*
	*	set the email Content-type
	*/
	public function setContentType($contentType=null){
		if($contentType){
			$this->headers['contentType'] = $contentType;
		}
		return $this;
	}
	
	/*
	*	add a recipient name and email
	*	@param email | the email address of recipient
	*	@param name | the name of recipient
	*	@param addressType | the type of address where to add 
	*			to or Cc or Bcc
	*/
	public function addAddress($email, $name='', $addressType='To'){
		$email = [
			'name' => $name,
			'email' => $email,
		];
		
		$this->$addressType[] = $email;
		return $this;
	}
	
	/*
	*	setsender name and email
	*	@param email | the email address of sender
	*	@param name | the name of sender
	*/
	public function setFrom($email, $name=''){
		$this->from['name'] = $name;
		$this->from['email'] = $email;
		return $this;
	}
	
	/*
	*	set the header To:
	*	return an array of email to use on mail($to)
	*/
	protected function setHeaderTo(){
		if(count($this->To) > 0){
			$headerTo = [];
			$To = [];
			foreach($this->To as $toVal){
				$headerTo[] = $toVal['name'] . " <" . $toVal['email'] . ">";
				$To[] = $toVal['email'];
			}
			$this->headers['To'] = 'To: ' . implode(",", $headerTo);
			return $To;
		} else {
			throw new \Exception('There is no recipient email address defined');
		}
	}
	
	/*
	*	set the header Cc or Bcc:
	*	@param type | Cc or Bcc
	*/
	protected function setHeaderCcOrBcc($type = 'Cc'){
		if(count($this->$type) > 0){
			$header = [];
			foreach($this->$type as $val){
				$header[] = $val['email'];
			}
			$this->headers[$type] = $type.': ' . implode(",", $header);
		} else {
			if(isset($this->headers[$type])){
				unset($this->headers[$type]);
			}
		}
	}
	
	/*
	*	set the subject of the email
	*/
	public function setSubject($subject){
		$this->subject = $subject;
		return $this;
	}
	
	/*
	*	@param | path to template
	*/
	public function setTemplatePath($path){
		$this->tamplatePath = $path;
		return $this;
	}
	
	/*
	*	set the message of the email
	*/
	public function setMessage($message){
		$this->message = $message;
		return $this;
	}
	
	public function getMessage(){
		if($this->tamplatePath && file_exists($this->tamplatePath)){
			ob_start();
				$content = $this->message;
				include($this->tamplatePath);
				$messageTemplate = ob_get_contents();
			ob_end_clean();
			
			return $messageTemplate;
		} else {
			return $this->message;
		}
	}
	
	/*
	*	send the email to recipient
	*	return bool 
	*/
	public function send(){
		$to = $this->setHeaderTo();
		$this->setHeaderCcOrBcc('Cc');
		$this->setHeaderCcOrBcc('Bcc');
		if($this->from['email'] != ''){
			$this->headers['From'] = 'From: '.$this->from['name'].' <'.$this->from['email'].'>' ;
		}
			
		$to = implode(",", $to);
		$head = implode("\r\n", $this->headers);
		
		return mail($to, $this->subject, $this->getMessage(), $head);
	}
}