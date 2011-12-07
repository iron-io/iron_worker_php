<?php
include("SimpleWorker.class.php");

$name = "testBasic.php-helloPHP-".microtime(true);

$sw = new SimpleWorker('config_sw.ini');
$sw->debug_enabled = true;

$project_id = ""; # using default project_id from config
$zipName = "code/$name.zip";
$files_to_zip = array('testTask.php');
$zipFile = SimpleWorker::createZip(dirname(__FILE__)."/worker_examples/hello_world", $files_to_zip, $zipName, true);
$res = $sw->postCode($project_id, 'testTask.php', $zipName, $name);

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

$task_id = $sw->postTask($project_id, $name, $payload);
echo "task_id = $task_id \n";
sleep(10);
$details = $sw->getTaskDetails($project_id, $task_id);
print_r($details);

if ($details->status != 'queued'){
    $log = $sw->getLog($project_id, $task_id);
    print_r($log);
}



