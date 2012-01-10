iron_worker_php is PHP language binding for IronWorker.

IronWorker is a massively scalable background processing system.

# Getting Started


## Get credentials
To start using iron_worker_php, you need to sign up and get an oauth token.

1. Go to http://iron.io/ and sign up.
2. Get an Oauth Token at http://hud.iron.io/tokens

## Install iron_worker_php
Just copy ```IronWorker.class.php``` and include it in your script:

```php
<?php
require_once "IronWorker.class.php"
```
## Configure
Two ways to configure IronWorker:

* Passing array with options:

```php
<?php
$iw = new IronWorker(array(
    'token' => 'XXXXXXXXX',
    'project_id' => 'XXXXXXXXX'
));
```
* Passing ini file name which store your configuration options. Rename sample_config.ini to config.ini and include your Iron.io credentials (`token` and `project_id`):

`HelloWorld.php`:

```php
<?php
$iw = new IronWorker('config.ini');
```

## Creating a Worker

Here's an example worker:

```php
<?php
echo "Hello PHP World!\n";
```
## Upload code to server

* Zip worker:

```php
<?php
# Zip single file:
IronWorker::createZip(dirname(__FILE__), array('HelloWorld.php'), 'worker.zip', true);
# OR
# Zip whole directory:
IronWorker::zipDirectory(dirname(__FILE__)."/hello_world/", 'worker.zip', true);
```
* Submit worker:

```php
<?php
$res = $iw->postCode($project_id, 'HelloWorld.php', 'worker.zip', 'HelloWorld');
```
Where 'HelloWorld' is a worker name which should be used later for queueing and sheduling.

## Queueing a Worker

```php
<?php
$task_id = $iw->postTask($project_id, 'HelloWorld');
```
Worker should start in a few seconds.

## Scheduling a Worker
If you want to run your code more than once or run it in regular intervals, you should schedule it:

```php
<?php
# 3 minutes from now
$start_at = time() + 3*60;

# Run task every 2 minutes, repeat 10 times
$iw->postScheduleAdvanced($project_id, 'HelloWorld', array(), $start_at, 2*60, null, 10);
```

## Status of a Worker
To get the status of a worker, you can use the ```getTaskDetails()``` method.

```php
<?php
$task_id = $iw->postTask($project_id, 'HelloWorld');
$details = $iw->getTaskDetails($project_id, $task_id);

echo $details->status; # prints 'queued', 'complete' or 'error'
```

## Get Worker Log

Use any function that print text inside your worker to put messages to log.

```php
<?php
$task_id = $iw->postTask($project_id, 'HelloWorld');
sleep(10);
$details = $iw->getTaskDetails($project_id, $task_id);
# Check log only if task is finished.
if ($details->status != 'queued'){
    $log = $iw->getLog($project_id, $task_id);
    echo $log; # prints "Hello PHP World!"
}
```

## Loading the Task Data Payload

To provide Payload to your worker simply put an array with any content you want.

```php
<?php
$payload = array(
    'key_one' => 'Helpful text',
    'key_two' => 2,
    'options' => array(
        'option 1',
        'option 2'
    )
);

$iw->postTask($project_id, 'HelloWorld', $payload);

$iw->postScheduleSimple($project_id, 'HelloWorld', $payload, 10)

$iw->postScheduleAdvanced($project_id, 'HelloWorld', $payload, time()+3*60, 2*60, null, 5);
```

When your code is executed, it will be passed three program arguments:

* **-id** - The task id.
* **-payload** - the filename containing the data payload for this particular task.
* **-d** - the user writable directory that can be used while running your job.

Copy this code to a worker to use program arguments:

```php
<?php
function getArgs(){
    global $argv;
    $args = array('task_id' => null, 'dir' => null, 'payload' => array());
    foreach($argv as $k => $v){
        if (empty($argv[$k+1])) continue;
        if ($v == '-id') $args['task_id'] = $argv[$k+1];
        if ($v == '-d')  $args['dir']     = $argv[$k+1];
        if ($v == '-payload' && file_exists($argv[$k+1])){
            $args['payload'] = json_decode(file_get_contents($argv[$k+1]));
        }
    }
    return $args;
}

$args = getArgs();

echo "Hello PHP World!\n";

print_r($args);

```

