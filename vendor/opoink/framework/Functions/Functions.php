<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
$opoink_has_error = 0;
function opoink_log_error( $num, $str, $file, $line, $context = null ) {
    opoink_log_exception( new ErrorException( $str, 0, $num, $file, $line ) );
    opoink_renderError();
}

function opoink_log_exception($e){
	global $opoink_has_error;
	$opoink_has_error = $e;
	opoink_renderError();
}

function opoink_renderError(){
	global $opoink_has_error;
	$e = $opoink_has_error;


    $mode = \Of\Constants::MODE_DEV;
    if(file_exists(ROOT.DS.'etc'.DS.'Config.php')){
        $config = include(ROOT.DS.'etc'.DS.'Config.php');
        if(isset($config['mode'])){
            $mode = $config['mode'];
        }
    }
    
    header("HTTP/1.0 500 Internal Server Errorss");
	if($mode === \Of\Constants::MODE_DEV){
		$fileData = opoink_fileData($e->getFile());

		$firstLine = $e->getLine() - 3;
		$lastLine = $e->getLine() + 3;
		include(ROOT . '/vendor/opoink/framework/View/errortrace.phtml');
	} else {
		$dir = new \Of\File\Dirmanager();
		$logDir = ROOT . DS . 'Var' .DS . 'logs';
		$dir->createDir($logDir);
        $message = "Type: " . get_class( $e ) . "; Message: {$e->getMessage()}; File: {$e->getFile()}; Line: {$e->getLine()};";
        file_put_contents( $logDir . DS . "exceptions.log", $message . PHP_EOL, FILE_APPEND );
        echo "Error occured, logs has more info.";
	}
	die;
}

function opoink_hasError(){
	global $opoink_has_error;
	return $opoink_has_error;
}

function opoink_check_for_fatal() {
    $error = error_get_last();
    if ( $error["line"] > 0 ) {
        opoink_log_error( $error["type"], $error["message"], $error["file"], $error["line"] );
    }
}

function opoink_fileData($file) {
    $file = fopen($file, 'r');
    if (!$file) {
        die('file does not exist or cannot be opened');
    }
    while (($line = fgets($file)) !== false) {
        yield $line;
    }
    fclose($file);
};

function opoink_hash($dataEncoded, $algo="sha256", $secret="opoink"){
	return hash_hmac("sha256", utf8_encode($dataEncoded), utf8_encode($secret));
}

function opoink_b64encode($data){
    return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
}

function opoink_b64decode($data) {
    if ($remainder = strlen($data) % 4) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

register_shutdown_function( "opoink_check_for_fatal" );
set_error_handler( "opoink_log_error" );
set_exception_handler( "opoink_log_exception" );
ini_set( "display_errors", "off" );
error_reporting( E_ALL );
?>