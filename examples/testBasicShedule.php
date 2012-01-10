<?php
include("../IronWorker.class.php");

$name = "testBasic.php";

$iw = new IronWorker('config.ini');
$iw->debug_enabled = true;

$project_id = ""; # using default project_id from config
$zipName = "code/$name.zip";
$files_to_zip = array('testTask.php');
$zipFile = IronWorker::createZip(dirname(__FILE__)."/workers/hello_world", $files_to_zip, $zipName, true);
if (!$zipFile) die("Zip file $zipName was not created!");
$res = $iw->postCode($project_id, 'testTask.php', $zipName, $name);

$payload = array(
    'key_one' => 'Payload',
    'key_two' => 2
);

# 3 minutes later
$start_at = time()+3*60;

# Run task every 2 minutes 10 times
$iw->postScheduleAdvanced($project_id, $name, $payload, $start_at, 2*60, null, 10);

echo "\ndone\n";



