<?php
include("../IronWorker.class.php");

$name = "testMongo-php";

$config = parse_ini_file('../config.ini', true);

$iw = new IronWorker($config['iron_worker']);

$zipName = "code/$name.zip";
$files_to_zip = array('mongo.php');
$zipFile = IronWorker::createZip(dirname(__FILE__)."/workers/mongo", $files_to_zip, $zipName, true);
if (!$zipFile) die("Zip file $zipName was not created!");
$res = $iw->postCode('mongo.php', $zipName, $name);

$payload = array(
    'db' => $config['mongo']
);

$task_id = $iw->postTask($name, $payload);
echo "task_id = $task_id \n";
sleep(10);
$details = $iw->getTaskDetails($task_id);
print_r($details);
# Check log only if task finished.
if ($details->status != 'queued'){
    $log = $iw->getLog($task_id);
    print_r($log);
}



