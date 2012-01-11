<?php
include("../IronWorker.class.php");

$name = "testBasic-php";

$iw = new IronWorker('config.ini');
$iw->debug_enabled = true;

$zipName = "code/$name.zip";
$files_to_zip = array('testTask.php');
$zipFile = IronWorker::createZip(dirname(__FILE__)."/workers/hello_world", $files_to_zip, $zipName, true);
if (!$zipFile) die("Zip file $zipName was not created!");
$res = $iw->postCode('testTask.php', $zipName, $name);

$payload = array(
    'key_one' => 'Helpful text',
    'key_two' => 2,
    'options' => array(
        'option 1',
        'option 2',
        'option 3',
        'option 4',
        'option five'
    )
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



