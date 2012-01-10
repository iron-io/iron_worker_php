<?php
include("../IronWorker.class.php");

$name = "postToTwitter-php";

$iw = new IronWorker('config.ini');
$iw->debug_enabled = true;

$project_id = ""; # using default project_id from config
$zipName = "code/$name.zip";

$zipFile = IronWorker::zipDirectory(dirname(__FILE__)."/workers/post_to_twitter", $zipName, true);

$res = $iw->postCode($project_id, 'postToTwitter.php', $zipName, $name);

$payload = array(
    'message' => "Hello From PHPWorker at ".date('r')."!\n",
    'url'     => 'http://www.iron.io/'
);


$task_id = $iw->postTask($project_id, $name, $payload);
echo "task_id = $task_id \n";
sleep(15);
$details = $iw->getTaskDetails($project_id, $task_id);
print_r($details);

if ($details->status != 'queued'){
    $log = $iw->getLog($project_id, $task_id);
    print_r($log);
}

