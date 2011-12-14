<?php
include("SimpleWorker.class.php");


function tolog($name, $variable, $display = false){
    file_put_contents("log/$name.log", print_r($variable, true));
    if ($display){echo "{$name}: ".var_export($variable,true)."\n";}
}

$name = "testFull.php-".microtime(true);

$project_id = ''; # using default project_id from config

$sw = new SimpleWorker('config_sw.ini');
$sw->debug_enabled = true;


# ========================== Projects ===========================

echo "\n--Get Project List------------------------------------\n";
$projects = $sw->getProjects();
tolog('projects', $projects);

echo "\n--Get Project Details---------------------------------\n";
$project_details = $sw->getProjectDetails($project_id);
tolog('project_details', $project_details);

echo "\n--Posting Project-------------------------------------\n";
$post_project_id = $sw->postProject('TestNewProject');
tolog('post_project', $post_project_id, true);

/*
echo "\n--Deleting Project-------------------------------------\n";
# TODO: {"msg":"Method not allowed","status_code":405}
$res = $sw->deleteProject($project_id);
tolog('delete_project', $res, true);
*/

# =========================== Codes =============================

echo "\n--Posting Code----------------------------------------\n";
$zipName = "code/$name.zip";
$files_to_zip = array('testTask.php');
# if true, good; if false, zip creation failed
$zipFile = SimpleWorker::createZip(dirname(__FILE__)."/worker_examples/hello_world", $files_to_zip, $zipName, true);
if (!$zipFile) die("Zip file $zipName was not created!");

$res = $sw->postCode($project_id, 'testTask.php', $zipName, $name);
tolog('post_code', $res);

echo "\n--Get Codes-------------------------------------------\n";
$codes = $sw->getCodes($project_id);
tolog('codes', $codes);

echo "\n-Get Code Details--------------------------------------\n";
$code_details = $sw->getCodeDetails($codes[0]->id, $project_id);
tolog('get_code_details', $code_details);

# =========================== Tasks =============================

echo "\n--Get Tasks-------------------------------------------\n";
$tasks = $sw->getTasks($project_id);
tolog('tasks', $tasks);

echo "\n--Posting Task----------------------------------------\n";
$task_id = $sw->postTask($project_id, $name);
tolog('post_task', $task_id, true);

echo "\n--Get Task Details------------------------------------\n";
$details = $sw->getTaskDetails($project_id, $task_id);
tolog('task_details', $details, true);

echo "\n--Get Task Log----------------------------------------\n";
sleep(15);
# Check log only if task finished.
if ($details->status != 'queued'){
    $log = $sw->getLog($project_id, $task_id);
    tolog('task_log', $log, true);
}

echo "\n--Set Task Progress-----------------------------------\n";
$res = $sw->setTaskProgress($project_id, $task_id, 50, 'Job half-done');
tolog('set_task_progress', $res, true);

/*
echo "\n--Cancel Task-----------------------------------\n";
# TODO: returns {"msg":"Not found","status_code":404}
# or {"msg":"Method POST not allowed","status_code":405}
$res = $sw->cancelTask($project_id, $task_id);
tolog('cancel_task', $res, true);
*/

echo "\n--Deleting Task---------------------------------------\n";
$res = $sw->deleteTask($project_id, $task_id);
tolog('delete_task', $res, true);

# ========================== Schedules ==========================

echo "\n--Get Schedules----------------------------------------\n";
$schedules = $sw->getSchedules($project_id);
tolog('schedules', $schedules);

echo "\n--Posting Simple Shedule--------------------------------------\n";
$schedule_id = $sw->postScheduleSimple($project_id, $name, 10);
tolog('post_schedule_simple', $schedule_id, true);

echo "\n--Posting Advanced Shedule--------------------------------------\n";
$start_at = SimpleWorker::dateRfc3339(time());
$schedule_id = $sw->postScheduleAdvanced($project_id, $name, $start_at, 50, null, 4, 0);
tolog('post_schedule_advanced', $schedule_id, true);


echo "\n--Deleting Shedule-------------------------------------\n";
$res = $sw->deleteSchedule($project_id, $schedule_id);
tolog('delete_schedule', $res, true);









echo "\ndone\n";
