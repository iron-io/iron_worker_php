<?php

function getPayload($argv){
    foreach($argv as $k => $v){
        if ($v == '-payload' && !empty($argv[$k+1]) && file_exists($argv[$k+1])){
            return json_decode(file_get_contents($argv[$k+1]));
        }
    }
    return array();
}

$payload = getPayload($argv);

require_once(dirname(__FILE__).'/lib/class.phpmailer.php');

$mail  = new PHPMailer(); // defaults to using php "mail()"

$body  = file_get_contents(dirname(__FILE__).'/contents.html');

$mail->AddReplyTo($payload->reply_to->address, $payload->reply_to->name);

$mail->SetFrom($payload->from->address, $payload->from->name);

$mail->AddAddress($payload->address, $payload->name);

$mail->Subject    = $payload->subject;

$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

$mail->MsgHTML($body);

$mail->AddAttachment(dirname(__FILE__)."/images/phpmailer.gif", "images/phpmailer.gif");
$mail->AddAttachment(dirname(__FILE__)."/images/phpmailer_mini.gif", "images/phpmailer_mini.gif");

if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}




