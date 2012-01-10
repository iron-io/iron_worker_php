<?php
include("../IronWorker.class.php");

$name = "sendEmail.php-".microtime(true);

$iw = new IronWorker('config.ini');
$iw->debug_enabled = true;

$project_id = ""; # using default project_id from config
$zipName = "code/$name.zip";

$zipFile = IronWorker::zipDirectory(dirname(__FILE__)."/workers/batch_emailer", $zipName, true);

$res = $iw->postCode($project_id, 'sendEmail.php', $zipName, $name);
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

    $task_id = $iw->postTask($project_id, $name, $payload);
    echo "task_id = $task_id \n";
    sleep(20);
    $details = $iw->getTaskDetails($project_id, $task_id);
    print_r($details);

    if ($details->status != 'queued'){
        $log = $iw->getLog($project_id, $task_id);
        echo "log: \n";
        print_r($log);
    }
}


