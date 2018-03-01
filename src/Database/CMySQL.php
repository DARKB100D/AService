<?php 
	namespace AService\Database;
	use SafeMysql;
	/**
	*  Класс для работы с базой данных токенов
	*/
	
	class CMySQL implements IBase
	{
		private $db;

		public function __construct($config) {
			$this->db = new SafeMysql($config);
		}

		public function __destruct(){
			$this->db = NULL;
		}

		public function insert($id, $aToken, $rToken, $key) {
			$data = array(
				"id" => $id,
				"aToken" => $aToken,
				"rToken" => $rToken,
				"sKey" => $key
			);
			return $this->db->query("REPLACE INTO ?n SET ?u","tokens", $data);
		}

		public function delete($id) {
			return $this->db->query("DELETE FROM `tokens` WHERE `id`= ?i", $id);
		}
		
		public function getKey($id) {
			$data = $this->db->getRow("SELECT `sKey` FROM `tokens` WHERE `id` = ?i", $id);
			return $data['sKey']; 
		}
		
	}
?>