<?php 
include_once "./src/modules/auth.php";
if (isset($_POST['login']) && isset($_POST['pass'])) {
	$login = $_POST['login'];
	$password = $_POST['pass'];
	$as = new AService();
	$as->auth($login, $password);
	//page exit or reload
}

if (isset($_POST['token'])) {

}

?>