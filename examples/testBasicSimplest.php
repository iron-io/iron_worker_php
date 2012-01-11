<?php
include("../IronWorker.class.php");

$name = "testTaskSimple-php";

$iw = new IronWorker('config.ini');

# Creating zip package.
$zipName = "code/$name.zip";
IronWorker::zipDirectory(dirname(__FILE__)."/workers/hello_world_simple", $zipName, true);

# Posting package.
$res = $iw->postCode('testTaskSimple.php', $zipName, $name);

# Adding new task.
$task_id = $iw->postTask($name);
echo "task_id = $task_id \n";

sleep(10);

$details = $iw->getTaskDetails($task_id);
print_r($details);


