<?php
include("../IronWorker.class.php");

$name = "testFFmpeg-flv-php";

$iw = new IronWorker('config.ini');

# Creating zip package.
$zipName = "code/$name.zip";
IronWorker::zipDirectory(dirname(__FILE__)."/workers/ffmpeg_flv", $zipName, true);

# Posting package.
$res = $iw->postCode('ffmpeg.php', $zipName, $name);

# Adding new task.
$payload = array(
    'input_file' => 'https://s3.amazonaws.com/iron-examples/video/iron_man_2_trailer_official.flv'
);
$task_id = $iw->postTask($name, $payload);
echo "task_id = $task_id \n";


# Wait for task finish
for ($i = 0; $i < 100; $i++){
    sleep(5);
    $details = $iw->getTaskDetails($task_id);
    echo "Status: {$details->status}\n";

    if ($details->status != 'queued' && $details->status != 'running'){
        $log = $iw->getLog($task_id);
        print_r($log);
        break;
    }
}


