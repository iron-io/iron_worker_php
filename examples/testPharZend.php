<?php
include("../IronWorker.class.php");

$name = "testPharZend.php";

$iw = new IronWorker('config.ini');
$iw->debug_enabled = true;

$zipName = "code/$name.zip";
IronWorker::zipDirectory(dirname(__FILE__)."/workers/phar_zend_lib", $zipName, true);
$res = $iw->postCode('pharZend.php', $zipName, $name);

$task_id = $iw->postTask($name);
echo "task_id = $task_id \n";
sleep(10);
$details = $iw->getTaskDetails($task_id);
print_r($details);
# Check log only if task finished.
if ($details->status != 'queued'){
    $log = $iw->getLog($task_id);
    print_r($log);
}



