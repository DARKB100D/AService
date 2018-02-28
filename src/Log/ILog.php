<?php
namespace AService\Interfaces;

/**
 *
 *
 * Interface ILog
 */

interface ILog {
	public function logExeption(Exception $e);
	public function logMessage(string $str);
}

?>