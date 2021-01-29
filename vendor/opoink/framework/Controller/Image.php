<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller;

use Of\Constants;
use Of\File\ImageResize;

class Image extends Filecontroller {
	
	protected $data;
	protected $file;

	public function run($file){
		$path = $this->getPath($file, false);
		if($path){
			$this->file = $file;
			$realPath = $this->getRealPath($path);
			$targetFile = $realPath.$file;
			$destinationFile = ROOT.$path.DS.$file;

			if(file_exists($targetFile)){
				if(isset($this->data->resize)){
					$this->doResize($targetFile, $destinationFile, $path);
				}

				/*
				*	if the resize is not set in data
				*	it wil continue here
				*/
				if($this->_config->getConfig('mode') === Constants::MODE_PROD){
					$this->makeDir(ROOT.$path);
					copy($targetFile, $destinationFile);
				}
				
				$this->renderImage($targetFile, $file);
			}
		}
	}

	protected function doResize($targetFile, $destinationFile, $path){

		$resize = new ImageResize($targetFile);
		
		$ae = (int)$this->getResizeParam('allow_enlarge', false);
		if($ae == 1){
			$allow_enlarge = true;
		} else {
			$allow_enlarge = false;
		}
		$type = strtolower($this->getResizeParam('type'));
		if($type === 'toshortside'){
			$max_short = $this->getResizeParam('max_short', 20);
			$resize->resizeToShortSide($max_short, $allow_enlarge);
		}
		if($type === 'tolongside'){
			$max_long = $this->getResizeParam('max_long', 20);
			$resize->resizeToLongSide($max_long, $allow_enlarge);
		}
		if($type === 'toheight'){
			$height = $this->getResizeParam('height', 20);
			$resize->resizeToHeight($height, $allow_enlarge);
		}
		if($type === 'towidth'){
			$width = $this->getResizeParam('width', 20);
			$resize->resizeToWidth($width, $allow_enlarge);
		}
		if($type === 'tobestfit'){
			$max_width = $this->getResizeParam('max_width', 20);
			$max_height = $this->getResizeParam('max_height', 20);
			$resize->resizeToBestFit($max_width, $max_height, $allow_enlarge);
		}
		if($type === 'crop'){
			$width = $this->getResizeParam('width', 20);
			$height = $this->getResizeParam('height', 20);
			$position = $this->getResizeParam('position', $resize::CROPCENTER);
			$resize->crop($width, $height, $allow_enlarge, $position);
		}

		if($this->_config->getConfig('mode') != Constants::MODE_PROD){
			$resize->output();
			die;
		} else {
			$this->makeDir(ROOT.$path);
			$resize->save($destinationFile);
			$this->renderImage($destinationFile, $this->data->filename);
		}
	}

	protected function getResizeParam($key, $default=null){
		if(isset($this->data->resize->$key)){
			return $this->data->resize->$key;
		} else {
			return $default;
		}
	}

	public function getRealPath($path){
		$targetFile = ROOT.DS.'public'.DS.'generation.php';
		$deployPath = '/public/deploy' . include($targetFile);
		$deployPath .= '/images';
		$path = ltrim(str_replace($deployPath, '', $path), '/');

		$info = explode('/', $path);

		if(count($info) >= 3){
			$this->setData($info);
			$vendor = $this->getVendor($path);
			if($vendor) {
				$module = $this->getModule($path);
				if($module) {
					$pathFromUrl = ltrim(opoink_b64decode($info[2]), '/');
					$realPath = ROOT.DS.'App'.DS.'Ext'.DS.$vendor.DS.$module.DS.'View'.DS.$pathFromUrl.DS;
					return $realPath;
				}
			}
		}
	}

	private function setData($info){
		$data = [
		    'vendor'=> $info[0],
			'module'=> $info[1],
			'path' => $info[2],
			'filename' => $this->file,
		];

		$k = $v = null;
		foreach($info as $key => $val){
			if($key <= 2){
				continue;
			}
			if($k == null && $v == null){
				$k = $val;
			}
			elseif($k != null && $v == null){
				if($val != '' || $val != null){
					$data ['resize'][$k] = $val;
					$k = $v = null;
				}
			}
		}

		$this->data = json_decode(json_encode($data));
	}

	public function base64UrlDecode($data) {
        if ($remainder = strlen($data) % 4) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }
	
	protected function renderImage($targetFile="", $imgname="") {
		$imageinfo = getimagesize($targetFile);
		$etag = sha1($imgname);
		$lifetime = 60*60*24*60; // 60 days only - the revision may get incremented quite often
		header('Content-Disposition: inline; filename="'.basename($imgname).'"');
		header('Last-Modified: '. gmdate('D, d M Y H:i:s', filemtime($targetFile)) .' GMT');
		header('Expires: '. gmdate('D, d M Y H:i:s', time() + $lifetime) .' GMT');
		header('Pragma: ');
		header('Cache-Control: public, max-age='.$lifetime.', no-transform');
		header('Accept-Ranges: none');
		header('Content-type: ' . $imageinfo['mime']);
		header('Content-Length: ' . filesize($targetFile));
		@readfile($targetFile);
		exit;
		die;
	}
}