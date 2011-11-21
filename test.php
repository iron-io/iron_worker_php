<?php
include("SimpleWorker.class.php");

//$token = "TSjcQAnNMZKWGdOyCJhxnN64CTk";
$token = "jTxYQDmMx5ZtVeZBT8jVx6oJDLw";
$test1 = new SimpleWorker($token);

$projects = $test1->getProjects();
//print_r($projects);
//exit;
//$test1->getTasks($projects[0]->id);

$zipName = 'test.zip';
$name = "helloPHP-".microtime(true);
//$name = "helloPHP-";
$files_to_zip = array(
  'testTask.php'
);
//if true, good; if false, zip creation failed
$zipFile = SimpleWorker::create_zip($files_to_zip,$zipName,true);
//print_r($zipFile) ; exit;
$res = $test1->postCode($projects[0]->id, 'testTask.php', $zipName, $name);
echo "results of postCode:  \n";
print_r($res);
exit;
/*

$files_to_zip = array(
  'preload-images/1.jpg',
  'preload-images/2.jpg',
  'preload-images/5.jpg',
  'kwicks/ringo.gif',
  'rod.jpg',
  'reddit.gif'
);
//if true, good; if false, zip creation failed
$zipFile = SimpleWorker::create_zip($files_to_zip,'my-archive.zip');

*/

