<?php
// set_include_path("/usr/local/www/auth.saas.cf/");
// include_once "vendor/autoload.php";
// include_once "src/classes/SPayload.php";
// include_once "src/interfaces/IAuth.php";

namespace AService\Auth;

use Lcobucci\JWT;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;

use AService\Config\CConfigJSON;
use AService\Database\CMySQL;
use AService\Log\CLogToFile;
use AService\Payload\SPayload;

use Exception;


class CAuth implements IAuth
{

	private $config;
	private $db;
	private $signer;

	public function __construct() {
		$this->config = (new CConfigJSON())->getArray();
		$this->db = new CMySQL($this->config['Base']);
		$this->signer = new Sha256();
	}

	public function auth($login, $pass) {
		$payload = $this->LDAPData($login, $pass);
		$this->setTokens($payload);
	}

	// private function localData($login, $pass) {
	// 	$data = $this->db->getRow("SELECT id, name, surname, middlename, password FROM users WHERE login=?s",$login);
		
	// 	if ($data === NULL) return false;

	// 	if (!password_verify($pass, $data['password'])) return false;

	// 	$payload = new payload();	
	// 	$payload->id = (int)$data['id'];
	// 	$payload->login = $login;
	// 	$payload->displayname = $data['surname']." ".$data['name']{0}.". ".$data['middlename']{0}.".";
	// 	$payload->admin = true;

	// 	if (!$this->setTokens($payload)) return false;

	// 	return true;
	// }

	private function LDAPData($login, $pass) {
		// if (!$this->config['AService']['useLDAP']) return false;
		$ad = new \Adldap\Adldap();					////////////
		$ad->addProvider($this->config['LDAP']);
		$provider = $ad->connect();

		if (!$provider->auth()->attempt($login, $pass)) throw new Exception("Неверное имя/пароль");//print except
		
		$search = $provider->search();
		$data = $search->findBy('sAMAccountName', $login, ['USNCreated', 'sAMAccountName', 'displayname', 'memberof']);

		$payload = new SPayload();
		$payload->id = (int)$data['usncreated'][0];
		$payload->login = $login;
		$payload->displayname = $data['displayname'][0];
		$payload->admin = $data->inGroup($this->config['AService']['group']);

		return $payload;
	}

	private function LDAPDataFromId($id) {

		$ad = new \Adldap\Adldap();					///getprovider	
		$ad->addProvider($this->config['LDAP']);
		$provider = $ad->connect();
		
		$search = $provider->search();
		$data = $search->findBy('USNCreated', $id, ['USNCreated', 'sAMAccountName', 'displayname', 'memberof']);

		$payload = new SPayload();
		$payload->id = (int)$data['usncreated'][0];
		$payload->login = $login;
		$payload->displayname = $data['displayname'][0];
		$payload->admin = $data->inGroup('Операторы Сайта');

		return $payload;
	}

	public function checkTokens($aStr, $rStr) {
		$str = isset($astr) ? $aStr : $rStr;
		if (!isset($str)) throw new Exception("Token in not set");
		try {
			$token = (new Parser())->parse((string) $str);
			if (!$this->verifyToken($token)) throw new Exception("Token is not signed"); //not signed        //token error
			if (!$this->validateToken($token)) throw new Exception("Token is not valid"); //not valid
		} catch (Exception $e) {
			$this->logout();
		}

		if ($str === $rStr) $this->refreshTokens($token);
		return true;
	}

	private function verifyToken($token) {
		if (!$token->hasClaim("id")) return false;
		$id = $token->getClaim("id");  

		$key = $this->db->getKey($id);

		if ($key === NULL) return false;

		if (!$token->verify($this->signer, $key)) return false;

		return true;
	}

	private function validateToken($token) {
		$data = new ValidationData();
		if (!$token->validate($data)) return false;
		return true;
	}

	public function checkAccess($str) {
		$token = (new Parser())->parse((string) $str);
		if(!$token->hasClaim('admin'));
		return $token->getClaim('admin');
	}

	public function logout() {
		$this->db->delete($id);
		$this->unsetCookie();
		// header("Refresh:0", "auth".$this->config['AService']['domain']."/src/");
		header("HTTP/1.1 303 See Other");
		header("Location: /index.php");
		// echo "auth.".$this->config['AService']['domain']."/src/index.php";
	}

	private function setTokens(SPayload $payload){

		$key = md5(random_bytes(32));

		$aToken = (new Builder())->setIssuedAt(time())
								->setNotBefore(time()+1)
								->setExpiration(time()+60*60)
								->setIssuer('auth'.$this->config['AService']['domain'])
								->setAudience($this->config['AService']['domain'])
								->set("id", $payload->id)
								->set("login",$payload->login)
								->set("displayname",$payload->displayname)
								->set("admin",$payload->admin)
								->sign($this->signer, $key)
								->getToken();
		if (!setcookie("aToken", $aToken, time()+60*60, "/", $this->config['AService']['domain'], true, true)) throw new Exception("Куки не доступны для записи");
		


		$rToken = (new Builder())->setIssuedAt(time())
								->setNotBefore(time()+1)
								->setExpiration(time()+60*60*24*60)
								->setIssuer('auth'.$this->config['AService']['domain'])
								->setAudience($this->config['AService']['domain'])
								->set("id",$payload->id)
								->set("login",$payload->login)
								->set("displayname",$payload->displayname)
								->set("admin",$payload->admin)
								->sign($this->signer, $key)
								->getToken();

		if (!setcookie("rToken", $rToken, time()+60*60*24*60, "/", $this->config['AService']['domain'], true, true)) throw new Exception("Куки не доступны для записи");

		if (!$this->db->insert($payload->id, $aToken, $rToken, $key)) return false;

		return true;

	}

	private function refreshTokens($token) {
		$id = $token->getClaim("id");
		$payload = $this->LDAPDataFromId($id);
		$this->setTokens($payload);
	}

	private function unsetCookie() {
		setcookie("aToken", "", time()-3600,"/",$this->config['AService']['domain']);
		setcookie("rToken", "", time()-3600,"/",$this->config['AService']['domain']);
	}

	// private function reloadPage() {
	// 	header("Refresh:0");
	// }

}

?>