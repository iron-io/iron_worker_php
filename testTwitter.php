<?php
include("SimpleWorker.class.php");

$name = "postToTwitter.php-".microtime(true);

$sw = new SimpleWorker('config_sw.ini');
$sw->debug_enabled = true;

$project_id = ""; # using default project_id from config
$zipName = "code/$name.zip";

$zipFile = SimpleWorker::zipDirectory(dirname(__FILE__)."/worker_examples/post_to_twitter", $zipName, true);

$res = $sw->postCode($project_id, 'postToTwitter.php', $zipName, $name);

$payload = array(
    'message' => "Hello From PHPWorker at ".date('r')."!\n",
    'url'     => 'http://www.iron.io/'
);


$task_id = $sw->postTask($project_id, $name, $payload);
echo "task_id = $task_id \n";
sleep(15);
$details = $sw->getTaskDetails($project_id, $task_id);
print_r($details);

if ($details->status != 'queued'){
    $log = $sw->getLog($project_id, $task_id);
    print_r($log);
}

