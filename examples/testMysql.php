<?php
include("../IronWorker.class.php");

$name = "testMysql.php-".microtime(true);

$iw = new IronWorker('config.ini');
$iw->debug_enabled = true;

$project_id = ""; # using default project_id from config
$zipName = "code/$name.zip";

$zipFile = IronWorker::zipDirectory(dirname(__FILE__)."/workers/mysql", $zipName, true);

$res = $iw->postCode($project_id, 'testMysql.php', $zipName, $name);
print_r($res);

$task_id = $iw->postTask($project_id, $name);
echo "task_id = $task_id \n";
sleep(10);
$details = $iw->getTaskDetails($project_id, $task_id);
print_r($details);

if ($details->status != 'queued'){
    $log = $iw->getLog($project_id, $task_id);
    print_r($log);
}

