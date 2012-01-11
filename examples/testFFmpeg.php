<?php
include("../IronWorker.class.php");

$name = "testFFmpeg-php";

$iw = new IronWorker('config.ini');

# Creating zip package.
$zipName = "code/$name.zip";
IronWorker::zipDirectory(dirname(__FILE__)."/workers/ffmpeg", $zipName, true);

# Posting package.
$res = $iw->postCode('ffmpeg.php', $zipName, $name);

# Adding new task.
$task_id = $iw->postTask($name);
echo "task_id = $task_id \n";

sleep(10);

$details = $iw->getTaskDetails($task_id);
print_r($details);
$log = $iw->getLog($task_id);
print_r($log);

