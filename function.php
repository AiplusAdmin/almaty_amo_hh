<?

define('DOMAIN', 'qadam'); 
define('DOMAINHH', 'aiplus'); 

define('LOGFILE', __DIR__  .  DIRECTORY_SEPARATOR .'log.txt');  
define('AUTNFILE', __DIR__  .  DIRECTORY_SEPARATOR .'autn.php'); 

//hollihope
$link['key'] = 'VdqvXSXu/q1DWiLefLBUihGMn7MHlvSP59HIHoHH7+LEtHB5dtznB6sqyJIPjH5w';

$link['GetStudents'] = 'https://'.DOMAINHH.'.t8s.ru//Api/V2/GetStudents';
$link['AddStudent'] = 'https://'.DOMAINHH.'.t8s.ru//Api/V2/AddStudent';
$link['EditAgentContacts'] = 'https://'.DOMAINHH.'.t8s.ru//Api/V2/EditAgentContacts';
$link['EditUserExtraFields'] = 'https://'.DOMAINHH.'.t8s.ru//Api/V2/EditUserExtraFields';

//amo
$link['amo'] = 			'https://'.DOMAIN.'.amocrm.ru';
$link['leads'] = 		'https://'.DOMAIN.'.amocrm.ru/api/v2/leads';
$link['contacts'] = 	'https://'.DOMAIN.'.amocrm.ru/api/v2/contacts';
$link['companies'] = 	'https://'.DOMAIN.'.amocrm.ru/api/v2/companies';
$link['tasks'] = 		'https://'.DOMAIN.'.amocrm.ru/api/v2/tasks'; 
$link['notes'] = 		'https://'.DOMAIN.'.amocrm.ru/api/v2/notes';
$link['pipelines'] = 	'https://'.DOMAIN.'.amocrm.ru/api/v2/account?with=pipelines'; 
$link['users'] = 		'https://'.DOMAIN.'.amocrm.ru/api/v2/account?with=users'; 
$link['custom_fields'] = 'https://'.DOMAIN.'.amocrm.ru/api/v2/account?with=custom_fields'; 
$link['task_types'] = 	'https://'.DOMAIN.'.amocrm.ru/api/v2/account?with=task_types'; 
$link['note_types'] = 	'https://'.DOMAIN.'.amocrm.ru/api/v2/account?with=note_types'; 
$link['groups'] = 		'https://'.DOMAIN.'.amocrm.ru/api/v2/account?with=groups'; 



//write(LOGFILE, "w", 'Начали');
//write_mass(LOGFILE, 'a', au_now());
//write(LOGFILE, "a", 'Закончили');

//echo '<pre>';
//echo print_r(search($link['users'])); 
//echo '</pre>'; 



function au_now() { 
	
	$link = 'https://' . DOMAIN . '.amocrm.ru/oauth2/access_token'; //Формируем URL для запроса
	
	$data = array(
		'client_id' => '',
		'client_secret' => '',
		'grant_type' => 'authorization_code',
		'code' => '',
		'redirect_uri' => '',
	);
	
	$curl = curl_init(); 
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
	curl_setopt($curl,CURLOPT_URL, $link);
	curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type:application/json'));
	curl_setopt($curl,CURLOPT_HEADER, false);
	curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
	$out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	/** Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
	$code = (int)$code;
	$errors = array(
		400 => 'Bad request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not found',
		500 => 'Internal server error',
		502 => 'Bad gateway',
		503 => 'Service unavailable',
	);
	
	write(LOGFILE, "a", "code - ".$code);
	write_mass(LOGFILE, "a", json_decode($out, true));
	
	try
	{
		/** Если код ответа не успешный - возвращаем сообщение об ошибке  */
		if ($code < 200 || $code > 204) {
			throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
		}
	}
	catch(\Exception $e)
	{
		die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
	}
	
	$response = json_decode($out, true);
	
	//$access_token = $response['access_token']; //Access токен
	//$refresh_token = $response['refresh_token']; //Refresh токен
	//$token_type = $response['token_type']; //Тип токена
	//$expires_in = $response['expires_in']; //Через сколько действие токена истекает
	
	$response['time_out'] = $response['expires_in']+strtotime("now")-10;
	
	amo_token2file($response, AUTNFILE);
	
}

function au() {

	$token_array = amo_token_from_file(AUTNFILE);
	
	$link = 'https://' . DOMAIN . '.amocrm.ru/oauth2/access_token'; //Формируем URL для запроса
	
	$data = array(
		'client_id' => '',
		'client_secret' => '',
		'grant_type' => 'refresh_token',
		'refresh_token' => $token_array['refresh_token'],
		'redirect_uri' => '',
	);
	
	$curl = curl_init(); 
	curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
	curl_setopt($curl,CURLOPT_URL, $link);
	curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type:application/json'));
	curl_setopt($curl,CURLOPT_HEADER, false);
	curl_setopt($curl,CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($curl,CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
	$out = curl_exec($curl); //Инициируем запрос к API и сохраняем ответ в переменную
	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);
	
	$code = (int)$code;
	$errors = array(
		400 => 'Bad request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not found',
		500 => 'Internal server error',
		502 => 'Bad gateway',
		503 => 'Service unavailable',
	);
	
	write(LOGFILE, "a", "code - ".$code);
	//write_mass(LOGFILE, "a", json_decode($out, true));
	
	try
	{
		/** Если код ответа не успешный - возвращаем сообщение об ошибке  */
		if ($code < 200 || $code > 204) {
			throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undefined error', $code);
		}
	}
	catch(\Exception $e)
	{
		die('Ошибка: ' . $e->getMessage() . PHP_EOL . 'Код ошибки: ' . $e->getCode());
	}
	
	$response = json_decode($out, true);
		
	$response['time_out'] = $response['expires_in']+strtotime("now")-5;
		
	write(LOGFILE, "a", date("d.m.y H:i:s", $response['time_out']));
	
	amo_token2file($response, AUTNFILE);
	
	
	
}

//-----------------------------------------------------------

function post_send($link, $user) { //отправка в амо

	$token_array = amo_token_from_file(AUTNFILE);

	if (strtotime("now") > $token_array['time_out']){
		au();
		$token_array = amo_token_from_file(AUTNFILE);
	}
	
	$curl=curl_init(); 
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
	curl_setopt($curl,CURLOPT_URL,$link);
	curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
	curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($user));
	curl_setopt($curl,CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token_array['access_token']));
	curl_setopt($curl,CURLOPT_HEADER,false);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
	
	$out=curl_exec($curl); 
	$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
	
	$code=(int)$code;
	
	$errors=array(
		301=>'Moved permanently',
		400=>'Bad request',
		401=>'Unauthorized',
		403=>'Forbidden',
		404=>'Not found',
		500=>'Internal server error',
		502=>'Bad gateway',
		503=>'Service unavailable'
	);
	
	if($code!=200 && $code!=204 && $code!=401){
		write(LOGFILE, "a", '(post_send '.$link.' ) Код ошибки : '.$code );
		write_mass(LOGFILE, "a", json_decode($out,true));
		return $code;
	}
	
	try
	{
	if($code!=200 && $code!=204 && $code!=401)
		throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
	}
	catch(Exception $E)	{ die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode()); }
	
	
	$Response=json_decode($out,true);

	
	return $Response;
}

function search($link) { //поиск по амо

	$token_array = amo_token_from_file(AUTNFILE);
		
	if (strtotime("now") > $token_array['time_out']){
		au();
		$token_array = amo_token_from_file(AUTNFILE);
	}
	
	$curl=curl_init(); 
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
	curl_setopt($curl,CURLOPT_URL,$link);
	curl_setopt($curl,CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token_array['access_token']));
	curl_setopt($curl,CURLOPT_HEADER,false);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER, 1);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST, 2);
	
	$out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
	$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
	curl_close($curl);
	
	$code=(int)$code;
	$errors=array(
		301=>'Moved permanently',
		400=>'Bad request',
		401=>'Unauthorized',
		403=>'Forbidden',
		404=>'Not found',
		500=>'Internal server error',
		502=>'Bad gateway',
		503=>'Service unavailable'
	);
	
	if($code!=200 && $code!=204 && $code!=401){
		write(LOGFILE, "a", '(search '.$link.' ) Код ошибки : '.$code );
		write_mass(LOGFILE, "a", json_decode($out,true));
	}
		
	try
	{
	#Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
	if($code!=200 && $code!=204 && $code!=401)
		throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
	}
	catch(Exception $E)
	{
	die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
	}
	
	$Response=json_decode($out,true);
	
	return $Response;

}

function post_send_hh($link, $mass) {//отправка в hh

	$curl=curl_init();
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl,CURLOPT_URL,$link);
	curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
	curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($mass));
	curl_setopt($curl,CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($curl,CURLOPT_HEADER,false);
	curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
	curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
	$out_curl = curl_exec($curl); 
	curl_close($curl);
	
	$Response = json_decode($out_curl,true);

	return $Response;
}

//-----------------------------------------------------------

function object2file($value, $filename){
	$str_value = serialize($value);
	
	$f = fopen($filename, 'w');
	fwrite($f, $str_value);
	fclose($f);
}

function object_from_file($filename){
	$file = file_get_contents($filename);
	$value = unserialize($file);
	return $value;
}

//-----------------------------------------------------------

function write($name, $f, $text){
	
	$file = fopen($name, $f);
	fwrite($file, date("d-m-Y H:i:s")." => ".$text."\n");//log
	fwrite($file, " \n");//log
	
	//fwrite($file, $text."\n");//log
	fwrite($file, "----------------\n");//log
	fclose($file);
	
	
}

function write_mass($name, $f, $arr) { 

	$file = fopen($name, $f);
	fwrite($file, date("d-m-Y H:i:s")."\n");//log
	fwrite($file, "----------------\n");//log
	fwrite($file, " \n");//log
	
	trace($arr, $file, "    ");
		
	fwrite($file, "----------------\n");//log
	fclose($file);
}

function trace($arr, $file, $tab) { 

	foreach($arr as $key => $vol){
		if (is_array($vol)) {
			fwrite($file, $tab.$key ." => \n");
			trace($vol, $file, $tab."    ");
		}
		else 
		{
			fwrite($file, $tab.$key .' => '.$vol."\n");
		}
	
	}
}


function amo_token2file($value, $filename){
	
	$str_value = json_encode($value);
	
	$f = fopen($filename, 'w');
	fwrite($f, "<? exit;/*|*".$str_value."*|*/?>");
	fclose($f);
}

function amo_token_from_file($filename){
		
	$str = file_get_contents($filename);
	
	$tokens = explode("*|*", $str);
	
	$str = json_decode($tokens[1], true);
	
	return $str;
}

?>