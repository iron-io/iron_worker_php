<?php
include("../IronWorker.class.php");

$name = "testMysql.php";

$iw = new IronWorker('config.ini');
$iw->debug_enabled = true;

$zipName = "code/$name.zip";

$zipFile = IronWorker::zipDirectory(dirname(__FILE__)."/workers/mysql", $zipName, true);

$res = $iw->postCode('testMysql.php', $zipName, $name);
print_r($res);

$task_id = $iw->postTask($name);
echo "task_id = $task_id \n";
sleep(10);
$details = $iw->getTaskDetails($task_id);
print_r($details);

if ($details->status != 'queued'){
    $log = $iw->getLog($task_id);
    print_r($log);
}

