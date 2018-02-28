<?php 
namespace AService\Classes;

/**
* log to file 
*/

class CLogToFile implements ILog
{
	
	function __construct(argument)
	{
		# code...
	}
	
	public function logExeption(Exception $e) {
		logMessage($e->getMessage());
	}

	public function logMessage(string $str) {
		$adr = $_SERVER['REMOTE_ADDR'];
		ini_set('date.timezone', 'Europe/Moscow');
		$date = date("Y-m-d H:i:s");
		$format = $adr."  [".$date."] - ".$str."\n" ;
		$file = "/usr/local/www/contacts.dgsh.local/dev/log/log.txt";
		error_log($format, 3, $file);
	} 
}

?>