<?php
	namespace AService\Config;
	/**
	 * 
	 */
	Class CConfigJSON implements IConfig {
		private $dir = __DIR__."/../Data/config.json";
		
		public function getArray() {
			$jsonStr = file_get_contents($this->dir);
			return $config = json_decode($jsonStr, true);
		}
	}
?>