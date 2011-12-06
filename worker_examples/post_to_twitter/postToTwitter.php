<?php

require_once dirname(__FILE__)."/lib/TwitterOAuth.php";

function shortenUrl($url){
    return file_get_contents("http://is.gd/create.php?format=simple&url=".urlencode($url));
}
function getPayload($argv){
    foreach($argv as $k => $v){
        if ($v == '-payload' && !empty($argv[$k+1]) && file_exists($argv[$k+1])){
            return json_decode(file_get_contents($argv[$k+1]));
        }
    }
    return array();
}

$config =  parse_ini_file('config.ini', true);
$payload = getPayload($argv);

$message  = $payload->message;
$message .= "\n";
$message .= shortenUrl($payload->url);

$connection = new TwitterOAuth( $config['twitter']['consumer_key'],
                                $config['twitter']['consumer_secret'],
                                $config['twitter']['oauth_token'],
                                $config['twitter']['oauth_secret']);

$content = $connection->get('account/verify_credentials');

$status = $connection->post('statuses/update', array('status' => $message));

print_r($status);

# You can see posted message at https://twitter.com/#!/WorkerPHP