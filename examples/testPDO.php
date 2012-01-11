<?php
include("../IronWorker.class.php");

$name = "testPDO.php";

$config = parse_ini_file('config.ini', true);

# Passing array of options instead of config file.
$iw = new IronWorker($config['simple_worker']);
$iw->debug_enabled = true;

$zipName = "code/$name.zip";

$zipFile = IronWorker::zipDirectory(dirname(__FILE__)."/workers/PDO", $zipName, true);

$res = $iw->postCode('Pdo.php', $zipName, $name);
print_r($res);

$payload = array(
    'connection'  => $config['pdo'],
    'yet_another' => array('value', 'value #2')
);

$task_id = $iw->postTask($name, $payload);
echo "task_id = $task_id \n";
sleep(10);
$details = $iw->getTaskDetails($task_id);
print_r($details);

if ($details->status != 'queued'){
    $log = $iw->getLog($task_id);
    print_r($log);
}

