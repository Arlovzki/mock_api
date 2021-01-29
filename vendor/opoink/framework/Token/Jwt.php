<?php 
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Token; 

class Jwt {

	protected $token;
	
	protected $secret = '';
	protected $header = [
		'alg' => 'HS256',
		'typ' => 'JWT',
	];
	
	protected $payload = [];
	protected $validationMessage = '';
	
	/*
	*	set the algo to use
	*/
	public function setAlgo($algo = 'HS256'){
		$this->header['alg'] = $algo;
		return $this;
	}
	
	/*
	*	set claim for payload
	*/
	public function setClaim($key, $value){
		$this->payload[$key] = $value;
		return $this;
	}
	
	/*
	*	set issuer for payload
	*/
	public function setIssuer($issuer){
		return $this->setClaim('iss', $issuer);
	}
	
	/*
	*	set audience for payload
	*/
	public function setAudience($audience){
		return $this->setClaim('aud', $audience);
    }
	
	/*
	*	set expiration for payload
	*/
	public function setExpiration($expiration){
		return $this->setClaim('exp', (int)$expiration);
    }
	
	/*
	*	set id for payload
	*/
	public function setId($id){
		return $this->setClaim('jti', $id);
    }
	
	/*
	*	set the time of token issue
	*/
	public function setIssuedAt($issuedAt = null){
		if(!$issuedAt){
			$issuedAt = time();
		} else {
			$issuedAt = (int)$issuedAt;
		}
        return $this->setClaim('iat', $issuedAt);
    }
	
	/*
	*	set the actual validity time if this token is not valid now
	*/
	public function setNotBefore($notBefore){
		return $this->setClaim('nbf', $notBefore);
	}
	
	/*
	*	set the subject of this token
	*/
	public function setSubject($subject){
		return $this->setClaim('sub', $subject);
	}
	
	/*
	*	set the secret for this token
	*/
	public function setSecret($secret){
		$this->secret = $secret;
		return $this;
	}
	
	/*
	*	return b64 encoder header of this token
	*/
	public function getHeader(){
		return $this->base64UrlEncode(json_encode($this->header));
	}
	
	/*
	*	return b64 encoder payload of this token
	*/
	public function getPayload(){
		return $this->base64UrlEncode(json_encode($this->payload));
	}
	
	/*
	*	return the hashed signature of the token
	*/
	public function getSignature(){
		$string = $this->getHeader() . '.' . $this->getPayload();
		$hash = $this->getHash($string);
		return $hash;
	}
	
	/*
	*	return generated token
	*/
	public function getToken(){
		return $this->getHeader() . '.' . $this->getPayload() . '.' . $this->getSignature();
	}
	
	
	/*
	*	return bool false if not valid
	*	return payload if valid
	*/
	public function validateToken($token){
		$this->token = $token;
		
		list($header, $payload, $signature) = explode('.', $this->token);

		$dataEncoded = $header . '.' . $payload;
		$rawSignature = $this->getHash($dataEncoded);
		
		$isValid = hash_equals($rawSignature, $signature);
		
		if($isValid){
			$this->payload = json_decode($this->base64UrlDecode($payload), true);
			
			if(isset($this->payload['nbf'])){
				if($this->payload['nbf'] > time()){
					$this->validationMessage = 'To early for this token.';
					return false;
				}
			}
			if(isset($this->payload['exp'])){
				if($this->payload['exp'] < time()){
					$this->validationMessage = 'Token expired.';
					return false;
				}
			}
			
			return $this->payload;
		}
		
		return $isValid;
	}
	
	/*
	*	return string of validation message
	*/
	public function getValidationMessage(){
		return $this->validationMessage;
	}
	/*
	*	return hash string
	*/
	protected function getHash($dataEncoded){
		return hash_hmac("sha256", utf8_encode($dataEncoded), utf8_encode($this->secret));
	}
	
	public function base64UrlEncode($data){
        return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
    }
	
	public function base64UrlDecode($data) {
        if ($remainder = strlen($data) % 4) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }
}