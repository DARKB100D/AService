<?php
	namespace AService\Auth;
	
	interface iAuth {
		public function __construct();
		public function auth($login, $pass);
		public function checkToken($sToken);
		public function checkAccess($sToken);
		public function logout();
	}
?>