<?php
include("SimpleWorker.class.php");

$name = "testTaskSimple.php-".microtime(true);

$sw = new SimpleWorker('config_sw.ini');

# Creating zip package.
$zipName = "code/$name.zip";
SimpleWorker::zipDirectory(dirname(__FILE__)."/worker_examples/hello_world_simple", $zipName, true);

# Posting package.
$res = $sw->postCode('', 'testTaskSimple.php', $zipName, $name);

# Adding new task.
$task_id = $sw->postTask('', $name);
echo "task_id = $task_id \n";

sleep(10);

$details = $sw->getTaskDetails('', $task_id);
print_r($details);


