<?php
/**
* Copyright 2018 Opoink Framework (http://opoink.com/)
* Licensed under MIT, see LICENSE.md
*/
namespace Of\Controller\Sys;

class SystemInstallDatabase extends Sys {
	
	protected $pageTitle = 'Database Installation';
	
	public function run(){
		$this->requireNotInstalled();
		$response = [
			'error' => 1,
			'message' => ''
		];
		
		$validate = $this->validateFormKey();
		
		if($this->validateFormKey()){
			$postFields = $this->getParam();
			$requiredFields = ['host', 'user', 'name'];
			$validateFields = $this->validateRequiredField($postFields, $requiredFields);
			if($validateFields['error'] == 0){
				$host = $this->getParam('host');
				$database = $this->getParam('name');
				$username = $this->getParam('user');
				$password = $this->getParam('password');
				$prefix = $this->getParam('prefix');
				
				if(strlen($prefix) <= 5) {
					$adapter = new \Zend\Db\Adapter\Adapter(array(
						'driver' => 'Pdo_Mysql',
						'host' => $host,
						'database' => $database,
						'username' => $username,
						'password' => $password
					));
					try {
						$currentSchema = $adapter->getCurrentSchema();
						if($currentSchema){
							$response['error'] = 0;
							$response['message'] = 'Database information successfully saved';
							$this->saveDbConfig($username, $password, $database, $host, $prefix);
							
							try {
								$this->_di->get('Of\Db\Schema\Sys\Admin')->setAdapter($adapter)->createSchema();
								$this->_di->get('Of\Db\Schema\Sys\Extension')->setAdapter($adapter)->createSchema();
							} catch (\Exception $e) {
								$response['message'] .= $e->getMessage();
							}
						}
					} catch (\Exception $e) {
						$response['message'] = 'Caught exception: ' . $e->getMessage();
					}
				} else {
					$response['message'] = 'Table prefix should be 5 characters long only';
				}
			} else {
				$response['message'] = $validateFields['message'] . ' field is required.';
			}
		} else {
			$response['message'] = 'Invalid request';
		}
		$this->jsonEncode($response);
	}
	
	private function saveDbConfig($user, $pass, $database, $host, $prefix){
		$config = "<?php".PHP_EOL;
		$config .= "\treturn [".PHP_EOL;
		$config .= "\t\t'driver' => 'Pdo_Mysql',".PHP_EOL;
		$config .= "\t\t'username' => '".$user."',".PHP_EOL;
		$config .= "\t\t'password' => '".$pass."',".PHP_EOL;
		$config .= "\t\t'database' => '".$database."',".PHP_EOL;
		$config .= "\t\t'host' => '".$host."',".PHP_EOL;
		$config .= "\t\t'table_prefix' => '".$prefix."',".PHP_EOL;
		$config .= "\t];".PHP_EOL;
		$config .= "?>";
		
		$_writer = new \Of\File\Writer();
		$_writer->setDirPath(ROOT . DS . 'etc')
		->setData($config)
		->setFilename('database')
		->setFileextension('php')
		->write();
	}
}