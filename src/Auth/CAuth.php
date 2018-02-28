<?php
set_include_path("/usr/local/www/auth.saas.cf/");
include_once "vendor/autoload.php";
// include_once "src/classes/SPayload.php";
// include_once "src/interfaces/IAuth.php";

namespace AService;

use Lcobucci\JWT;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class AService implements IAuth
{

	private $config;
	private $db;
	private $signer;

	public function __construct() {
		$this->config = $this->config();
		$this->db = new SafeMysql($this->config['Base']);
		$this->signer = new Sha256();
	}

	public function auth($login, $pass) {
		try {
			if($this->LDAPData($login, $pass)) return true;	
		} catch (Exception $e) {
			
		} 

		try {
			if ($this->localdata($login, $pass)) return true;
		} catch (Exception $e) {
			
		}
		return false;
	}

	private function localData($login, $pass) {
		$data = $this->db->getRow("SELECT id, name, surname, middlename, password FROM users WHERE login=?s",$login);
		
		if ($data === NULL) return false;

		if (!password_verify($pass, $data['password'])) return false;

		$payload = new payload();	
		$payload->id = (int)$data['id'];
		$payload->login = $login;
		$payload->displayname = $data['surname']." ".$data['name']{0}.". ".$data['middlename']{0}.".";
		$payload->admin = true;

		if (!$this->setTokens($payload)) return false;

		return true;
	}

	private function LDAPData($login, $pass){
		if (!$this->config['AService']['useLDAP']) return false;
		$ad = new \Adldap\Adldap();
		$ad->addProvider($this->config['LDAP']);
		$provider = $ad->connect();

		if (!$provider->auth()->attempt($login, $pass)) return false;
		
		$search = $provider->search();
		$data = $search->findBy('sAMAccountName',$login, ['USNCreated', 'sAMAccountName', 'displayname', 'memberof']);
		// var_dump($data);
		$payload = new payload();
		$payload->id = (int)$data['usncreated'][0];
		$payload->login = $login;
		$payload->displayname = $data['displayname'][0];
		$payload->admin = $data->inGroup('Операторы Сайта');

		if (!$this->setTokens($payload)) return false;

		return true;
	}

	public function checkToken($str) {
		$token = (new Parser())->parse((string) $str);

		if (!$token->hasClaim("id")) return false;
		$id = $token->getClaim("id");  

		$data = $this->db->getRow("SELECT `sKey` FROM `tokens` WHERE `id` = ?i", $id);

		if ($data === NULL) return false;
		// var_dump($id);////////////////////
		// var_dump($data['sKey']);/////////////////////
		if (!$token->verify($this->signer, $data['sKey'])) return false;
		
		$data = new ValidationData();
		if (!$token->validate($data)) return false;

		return true;
	}

	public function checkAccess($str) {
		$token = (new Parser())->parse((string) $str);
		if(!$token->hasClaim('admin')) return false;
		return $token->getClaim('admin');
	}

	public function logout() {

	}


//////////////////////////////////////////////////////////////////////////////////////////////////////////////	

	private function config() {
		$jsonStr = file_get_contents("/usr/local/www/contacts.dgsh.local/dev/src/modules/config.json");
		return $config = json_decode($jsonStr, true);
	}

	private function setTokens(payload $payload){

		$key = md5(random_bytes(32));

		$aToken = (new Builder())->setIssuedAt(time())
								->setNotBefore(time()+1)
								->setExpiration(time()+60*60)
								->setIssuer('auth.dgsh.local')
								->setAudience('dgsh.local')
								->set("id", $payload->id)
								->set("login",$payload->login)
								->set("displayname",$payload->displayname)
								->set("admin",$payload->admin)
								->sign($this->signer, $key)
								->getToken();
		if (!setcookie("aToken", $aToken, time()+60*60, "/", "dgsh.local", true, true)) return false;



		$rToken = (new Builder())->setIssuedAt(time())
								->setNotBefore(time()+1)
								->setExpiration(time()+60*60*24*60)
								->setIssuer('auth.dgsh.local')
								->setAudience('dgsh.local')
								->set("id",$payload->id)
								->set("login",$payload->login)
								->set("displayname",$payload->displayname)
								->set("admin",$payload->admin)
								->sign($this->signer, $key)
								->getToken();

		if (!setcookie("rToken", $rToken, time()+60*60*24*60, "/", "dgsh.local", true, true)) return false;

		if (!$this->insertToDB($payload->id, $aToken, $rToken, $key)) return false;

		return true;

	}

	private function insertToDB($id, $aToken, $rToken, $key) {
		// header("Refresh:0");
		// echo "<pre>";
		// echo $aToken;
		// var_dump($id);///////////////
		// var_dump($key);///////////////
		// echo "</pre>";
		$this->clearTokens($id);

		$data = array(
			"id" => $id,
			"aToken" => $aToken,
			"rToken" => $rToken,
			"sKey" => $key
		);
		return $this->db->query("INSERT INTO ?n SET ?u","tokens", $data);
	}

	private function refreshTokens($rtoken) {

	}

	// public function isEmpty() {
	// 	return $this->empty;
	// }

	private function clearTokens($id) {
		$this->db->query("DELETE FROM `tokens` WHERE `id`= ?i", $id);
	}


}

?>