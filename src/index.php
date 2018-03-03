<?php
	namespace AService; 
	
	include_once __DIR__."/../vendor/autoload.php";
	
	use AService\Auth\CAuth;
	use AService\Log\CLogToFile;
	use Exception;
	
	try {

		$as = new CAuth();

		switch ($_GET['action']) {
			case 'auth':
				$login = $_POST['login'];
				$password = $_POST['password'];
				$as->auth($login, $password);
				break;

			case 'checkTokens':
				$aToken = $_COOKIE['aToken'];
				$rToken = $_COOKIE['rToken']; 
				if ($as->checkTokens($aToken, $rToken)) res(true); 
				break;

			case 'checkAccess':
				$aToken = $_COOKIE['aToken'];
				$rToken = $_COOKIE['rToken']; 			
				if ($as->checkAccess($aToken, $rToken)) res(true);
				break;

			case 'logout':
				$as->logout();
				break;

			default:
				throw new Exception("No action");
				break;
		}

	} catch (Exception $e) {
		(new CLogToFile)->logExeption($e);
	} //catch (ExceptionPrint $e) {
	// 	$message = $e->getMessage();
	// }
	

	function res(bool $bool) {
		ob_end_clean();
		header('Content-Type', 'application/json');
		echo json_encode(array('result' => $bool));
		exit();
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Вход</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
	<!-- <link rel="stylesheet" type="text/css" href="../css/style.min.css"/> -->
	<style type="text/css">
	.logincard {
		max-width: 350px;
	}
</style>
</head>
<body>
	<div class="mdl-layout mdl-js-layout">
		<main class="mdl-layout__content">
			<div class="page-content">
				<div class="mdl-grid">
					<div class="mdl-layout-spacer"></div>
					<div class="mdl-card mdl-shadow--8dp mdl-cell logincard">
						<form method="post" action="?action=auth">
							<div class="mdl-grid">
								<div class="mdl-cell mdl-cell--12-col">
									<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label mdl-textfield--full-width">
										<input class="mdl-textfield__input" type="text" name="login" value="">
										<label class="mdl-textfield__label" for="login">Имя пользователя</label>
									</div>
								</div>
								<div class="mdl-cell mdl-cell--12-col">
									<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label mdl-textfield--full-width">
										<input class="mdl-textfield__input" type="password" name="password" value="">
										<label class="mdl-textfield__label" for="password">Пароль</label>
									</div>
								</div>
								<?php if (!empty($message)){
									echo '<div class="mdl-cell mdl-cell--12-col mdl-cell--middle mdl-typography--caption mdl-typography--text-center">'.$message.'</div>';
								}
								?>
								<div class="mdl-cell mdl-cell--12-col mdl-typography--text-center mdl-cell--middle">
									<button type="submit" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored" name="submit">Войти</button>
								</div>
							</div>
							<!-- <input type="text" name="action" hidden="true" value="auth">  -->
						</form>
					</div>
					<div class="mdl-layout-spacer"></div>
				</div>
			</div>
		</main>
	</div>
</body>
<!-- <script type="text/javascript" src="../js/material.min.js" defer></script> -->
<script defer src="https://code.getmdl.io/1.3.0/material.min.js"></script>
</html>
<?php

?>