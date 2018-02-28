<?php
	namespace AService\Config;

	Class CConfigJSON implements IConfig {
		public function getArray() {
			$jsonStr = file_get_contents("/usr/local/www/contacts.dgsh.local/dev/src/modules/config.json");
			return $config = json_decode($jsonStr, true);
		}
	}
?>