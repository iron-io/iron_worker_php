<?php

function getArgs(){
    global $argv;
    $args = array('task_id' => null, 'dir' => null, 'payload' => array());
    foreach($argv as $k => $v){
        if (empty($argv[$k+1])) continue;
        if ($v == '-id') $args['task_id'] = $argv[$k+1];
        if ($v == '-d')  $args['dir']     = $argv[$k+1];
        if ($v == '-payload' && file_exists($argv[$k+1])){
            $args['payload'] = json_decode(file_get_contents($argv[$k+1]));
        }
    }
    return $args;
}

$args = getArgs();


echo "Hello PHP World!!!\n";
echo "at " . date('r') . "\n";

print_r($args);
