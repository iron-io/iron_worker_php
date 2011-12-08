<?php
include("SimpleWorker.class.php");

$name = "testMysql.php-".microtime(true);

$sw = new SimpleWorker('config_sw.ini');
$sw->debug_enabled = true;

$project_id = ""; # using default project_id from config
$zipName = "code/$name.zip";

$zipFile = SimpleWorker::zipDirectory(dirname(__FILE__)."/worker_examples/mysql", $zipName, true);

$res = $sw->postCode($project_id, 'testMysql.php', $zipName, $name);
print_r($res);

$task_id = $sw->postTask($project_id, $name);
echo "task_id = $task_id \n";
sleep(10);
$details = $sw->getTaskDetails($project_id, $task_id);
print_r($details);

if ($details->status != 'queued'){
    $log = $sw->getLog($project_id, $task_id);
    print_r($log);
}

