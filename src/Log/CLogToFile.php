<?php 
namespace AService\Log;
/**
* log to file 
*/
use Exception;

class CLogToFile implements ILog
{
	private $file = __DIR__."/log";

	public function logExeption(Exception $e) {
		$this->logMessage($e->getMessage());
	}

	public function logMessage(string $str) {
		// $str = "str";
		$adr = $_SERVER['REMOTE_ADDR'];
		ini_set('date.timezone', 'Europe/Moscow');
		$date = date("Y-m-d H:i:s");
		$format = $adr."  [".$date."] - ".$str."\n" ;
		error_log($format, 3, $this->file);
	} 
}

?>