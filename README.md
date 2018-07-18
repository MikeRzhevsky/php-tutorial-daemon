# php-tutorial-daemon
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


1)make it executable
chmod a+x Daemon.php
2)test it.
 ./Daemon.php
3)Now that the script is running let’s check the log file
tail -f /var/log/Daemon.log
