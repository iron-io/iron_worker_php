<?php
include("SimpleWorker.class.php");

$name = "testBasic.php";

$sw = new SimpleWorker('config_sw.ini');
$sw->debug_enabled = true;

$project_id = ""; # using default project_id from config
$zipName = "code/$name.zip";
$files_to_zip = array('testTask.php');
$zipFile = SimpleWorker::createZip(dirname(__FILE__)."/worker_examples/hello_world", $files_to_zip, $zipName, true);
if (!$zipFile) die("Zip file $zipName was not created!");
$res = $sw->postCode($project_id, 'testTask.php', $zipName, $name);

$payload = array(
    'key_one' => 'Payload',
    'key_two' => 2
);

# 3 minutes later
$start_at = time()+3*60;

# Run task every 2 minutes 10 times
$sw->postScheduleAdvanced($project_id, $name, $payload, $start_at, 2*60, null, 10);

echo "\ndone\n";



