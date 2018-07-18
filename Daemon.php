<?php
/*
@MikeRzhevsky miker.ru@gmail.com
 */
//base logic here
/*
Необходимо написать php-демон. Со следующими функционалом:
Через cURL необходимо обратиться сюда https://syn.su/testwork.php , со следующими параметрами 
(method=get) методом POST
Из JSON ответа необходимо каждый час отправлять сюда https://syn.su/testwork.php cURL`ом ,
 со следующими параметрами (method=UPDATE&message=) методом POST значения параметра message взять 
  из параметра message response зашифрованного методом XOR, 
  к результату шифрования применить base64_encode. 
  В случае успешного запроса в качестве результата придет JSON
{
    "errorCode": null,
    "response": "Success"
}
 
Если произошла ошибка, необходимо остановить выполнения демона и отправить сообщение на почту 
(почта для ошибок должна быть указана в качестве константы в демоне).

Добавлено логирование, если не работает почта
*/

// for logging
$log = '/var/log/Daemon.log';
define('EMAIL','miker8@yandex.ru');
$urlreceiver = 'https://syn.su/testwork.php';
$messege = array();
$isProcess=true;
function xor_bytes($data , $key){
	$l = strlen($data);
	$k = strlen($key);
	$r = '';
	for($i = 0; $i < $l; $i++){
		$r .= $data[$i] ^ $key[$i % $k];
	}
	return $r;
}
//fork the process to work in a daemonized environment
file_put_contents($log, "Daemon status: starting up.n", FILE_APPEND);
$pid = pcntl_fork();
if($pid == -1){
	file_put_contents($log, "Error: could not daemonize process.n", FILE_APPEND);
	return 1; //error
}
else if($pid){
	return 0; //success
}
else{
    //the main process
    while($isProcess){
        $messege = getSource($url,'method=get');
        $key = base64_encode(xor_bytes($messege['response']['message'], $messege['response']['key']));
        $messege = getSource($url, 'method=update&message='.$key);
        if($messege['response']=='Success'){
            sleep(3600);//каждый час
            $isProcess= true;
        }
            
    }
}


function getSource($url,$parms)
{
    // создание нового cURL ресурса
    if ($curl = curl_init())
    {
        // установка URL и других необходимых параметров
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parms);
        $out = curl_exec($curl);
        // завершение сеанса и освобождение ресурсов
        curl_close($curl);
        $out = json_decode($out, true);
    }
    else
    {
		$out = array(
			'response' => Null,
			'ErrorMessage' => 'Fail curl_init');
    }
    if($out['response']!='Success')
    {
        mail(EMAIL, $out['ErrorMessage'], $out['ErrorMessage']);
        file_put_contents($log, $out['ErrorMessage'], FILE_APPEND);
		$isProcess = false;
    }
    return $out;
    
}
?>