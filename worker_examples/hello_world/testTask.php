<?php

function getPayload($argv){
    foreach($argv as $k => $v){
        if ($v == '-payload' && !empty($argv[$k+1]) && file_exists($argv[$k+1])){
            return json_decode(file_get_contents($argv[$k+1]));
        }
    }
    return array();
}

echo "Hello PHP World!!!\n";
echo "at " . date('r') . "\n";

print_r($argv);

print_r(getPayload($argv));