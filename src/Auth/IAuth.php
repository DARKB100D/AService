<?php
	namespace AService\Auth;
	
	interface IAuth {
		public function __construct();
		public function auth($login, $pass);
		public function checkTokens($aStr, $rStr);
		public function checkAccess($sToken);
		public function logout();
	}
?>