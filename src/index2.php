<?php 
	function res(bool $bool) {
		ob_end_clean();
		header('Content-Type', 'application/json');
		echo json_encode(array('result' => $bool));
		exit();
	}
	res(true);
?>