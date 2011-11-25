<?php
include("SimpleWorker.class.php");


function tolog($name, $variable){
    file_put_contents("log/$name.txt", print_r($variable, true));
}

#$sw = new SimpleWorker('config_sw.ini');
$sw = new SimpleWorker('config_production.ini');
$sw->debug_enabled = true;

echo "\n--Get Project List------------------------------------\n";
$projects = $sw->getProjects();
tolog('projects', $projects);


echo "\n--Get Tasks-------------------------------------------\n";
$tasks = $sw->getTasks($projects[0]->id);
tolog('tasks', $tasks);

echo "\n--Get Task Log----------------------------------------\n";
$log = $sw->getLog($projects[0]->id, $tasks[0]->id);
tolog('task_log', $log);

echo "\n--Get Schedules---------------------------------------\n";
$schedules = $sw->getSchedules($projects[0]->id);
tolog('schedules', $schedules);

echo "\n--Get Project Details---------------------------------\n";
$project_details = $sw->getProjectDetails($projects[0]->id);
tolog('project_details', $project_details);

echo "\n--Uploading task--------------------------------------\n";
$name = "helloPHP-".date('Y-m-d');
$zipName = $name.'.zip';
$files_to_zip = array('testTask.php');
# if true, good; if false, zip creation failed
$zipFile = SimpleWorker::createZip($files_to_zip, $zipName, true);
$res = $sw->postCode($projects[0]->id, 'testTask.php', $zipName, $name);
tolog('post_code', $res);

echo "\n--Get Codes-------------------------------------------\n";
$codes = $sw->getCodes($projects[0]->id);
tolog('codes', $codes);

echo "\n--Posting Shedule-------------------------------------\n";
$res = $sw->postSchedule($projects[0]->id, $name, 10);
tolog('post_schedule', $res);


echo "\n--Posting Task-------------------------------------\n";
$res = $sw->postTask($projects[0]->id, $name);
tolog('post_task', $res);




echo "\ndone\n";