<?php

require_once "lib/TwitterOAuth.php";

function shortenUrl($url){
    return file_get_contents("http://is.gd/create.php?format=simple&url=".urlencode($url));
}

$config =  parse_ini_file('config.ini', true);


$message  = "Hello From PHPWorker at ".date('r')."!\n";
$message .= shortenUrl("http://www.iron.io/");

$connection = new TwitterOAuth( $config['twitter']['consumer_key'],
                                $config['twitter']['consumer_secret'],
                                $config['twitter']['oauth_token'],
                                $config['twitter']['oauth_secret']);

$content = $connection->get('account/verify_credentials');

$status = $connection->post('statuses/update', array('status' => $message));

print_r($status);

# You can see posted message at https://twitter.com/#!/WorkerPHP