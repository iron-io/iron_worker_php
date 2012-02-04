<?php



echo "Hello PHP World!!!\n";
echo "at " . date('r') . "\n";

$is_ok = setProgress(50, "Task is half-done");
$args  = getArgs();

print_r($is_ok);
print_r($args);
