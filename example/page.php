<?php
include_once __DIR__."/../vendor/autoload.php";

function AService(string $action) {
	$myCurl = curl_init();
	curl_setopt_array($myCurl, array(
		// CURLOPT_URL => 'https://auth.saas.cf/src/index2.php',
	    CURLOPT_URL => 'https://auth.saas.cf/src/index.php',
	    CURLOPT_RETURNTRANSFER => true,
	    CURLOPT_POST => true,
	    CURLOPT_POSTFIELDS => http_build_query(array('action' => $action))
	));
	$response = curl_exec($myCurl);
	curl_close($myCurl);
	$arr = json_decode($response);
	isset ($arr->result) ? $arr->result : false;
	echo $response;
}
function Test(string $action) {
	header("HTTP/1.1 303 See Other");
	header("Location: https://auth.saas.cf/src/index.php?action=".$action);
}

// var_dump(AService("checkTokens"));
Test("checkTokens");
?>
