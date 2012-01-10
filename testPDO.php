<?php
include("SimpleWorker.class.php");

$name = "testPDO.php-".microtime(true);

$config = parse_ini_file('config_sw.ini', true);

# Passing array of options instead of config file.
$sw = new SimpleWorker($config['simple_worker']);
$sw->debug_enabled = true;

$project_id = ""; # using default project_id from config
$zipName = "code/$name.zip";

$zipFile = SimpleWorker::zipDirectory(dirname(__FILE__)."/worker_examples/PDO", $zipName, true);

$res = $sw->postCode($project_id, 'Pdo.php', $zipName, $name);
print_r($res);

$payload = array(
    'connection'  => $config['pdo'],
    'yet_another' => array('value', 'value #2')
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

