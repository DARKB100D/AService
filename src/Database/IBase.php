<?php 
	namespace AService\Database;
	interface IBase {
		public function insert($id, $aToken, $rToken, $key);
		public function delete($id);
		public function getKey($id);
	}
?>