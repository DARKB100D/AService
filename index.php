<?php 
include_once __DIR__."/vendor/autoload.php";
use AService\Auth\CAuth;

switch ($_POST['action']) {
	case 'auth':
		if (isset($_POST['login']) && isset($_POST['pass'])) {
			$login = $_POST['login'];
			$password = $_POST['pass'];
			//фильтр

			$as = new CAuth();
			$as->auth($login, $password);
			//page exit or reload
		}
		else break;

	case 'checkToken':
		# code...
		break;
	case 'checkPremissions':
		# code...
		break;
	case 'logout':
		# code...
		break;

	default:
		# code...
		# show page
		break;
}
?>