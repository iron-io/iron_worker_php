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

$connect_str = "{$payload->connection->driver}:host={$payload->connection->driver};dbname={$payload->connection->db}";
$db = new PDO($connect_str, $payload->connection->user, $payload->connection->password);


# Some hard work with db here.
$rows = $db->exec("SELECT NOW() AS `now`;");
print_r($rows);
