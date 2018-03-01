<?php
namespace AService\Log;

/**
 *
 *
 * Interface ILog
 */
use Exception;

interface ILog {
	public function logExeption(Exception $e);
	public function logMessage(string $str);
}

?>