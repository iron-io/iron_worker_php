<?php
include("../IronWorker.class.php");

$name = "testPharZend.php";

$iw = new IronWorker('config.ini');
$iw->debug_enabled = true;

$project_id = ""; # using default project_id from config
$zipName = "code/$name.zip";
IronWorker::zipDirectory(dirname(__FILE__)."/workers/phar_zend_lib", $zipName, true);
$res = $iw->postCode($project_id, 'pharZend.php', $zipName, $name);

$task_id = $iw->postTask($project_id, $name);
echo "task_id = $task_id \n";
sleep(10);
$details = $iw->getTaskDetails($project_id, $task_id);
print_r($details);
# Check log only if task finished.
if ($details->status != 'queued'){
    $log = $iw->getLog($project_id, $task_id);
    print_r($log);
}



