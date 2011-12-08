<?php
include("SimpleWorker.class.php");

$name = "sendEmail.php-".microtime(true);

$sw = new SimpleWorker('config_sw.ini');
$sw->debug_enabled = true;

$project_id = ""; # using default project_id from config
$zipName = "code/$name.zip";

$zipFile = SimpleWorker::zipDirectory(dirname(__FILE__)."/worker_examples/batch_emailer", $zipName, true);

$res = $sw->postCode($project_id, 'sendEmail.php', $zipName, $name);
print_r($res);

$payload = array(
    'address' => "",
    'name'    => "Dear Friend",
    'subject' => 'PHPMailer Test Subject via mail(), basic',
    'reply_to' => array(
        'address' => "name@example.com",
        'name'    => "First Last"
    ),
    'from'     =>  array(
        'address' => "me@example.com",
        'name'    => "First Last"
    ),
);

# Send 5 different mails
for ($i = 1; $i <= 5;$i++){
    $payload['address'] = "name_$i@example.com";
    $payload['name']    = "Dear Friend $i";

    $task_id = $sw->postTask($project_id, $name, $payload);
    echo "task_id = $task_id \n";
    sleep(20);
    $details = $sw->getTaskDetails($project_id, $task_id);
    print_r($details);

    if ($details->status != 'queued'){
        $log = $sw->getLog($project_id, $task_id);
        echo "log: \n";
        print_r($log);
    }
}


