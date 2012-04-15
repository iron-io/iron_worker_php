iron_worker_php is PHP language binding for IronWorker.

IronWorker is a massively scalable background processing system.
[See How It Works](http://www.iron.io/products/worker/how)

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
* Passing ini file name which stores your configuration options. Rename sample_config.ini to config.ini and include your Iron.io credentials (`token` and `project_id`):

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

You can upload worker in one step:

```php
<?php
# 1. Directory where worker files lies
# 2. This file will be launched as worker
# 3. Referenceable (unique) name for your worker
$iw->upload(dirname(__FILE__)."/hello_world/", 'HelloWorld.php', 'HelloWorld');

```
OR zip and upload separately:

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
$res = $iw->postCode('HelloWorld.php', 'worker.zip', 'HelloWorld');
```
Where 'HelloWorld' is a worker name which should be used later for queueing and scheduling.

## Worker examples

You can find plenty of good worker examples here: [iron_worker_examples](https://github.com/iron-io/iron_worker_examples/tree/master/php)

## Queueing a Worker

```php
<?php
$task_id = $iw->postTask('HelloWorld');
```
Worker should start in a few seconds.

## Scheduling a Worker
If you want to run your code more than once or run it in regular intervals, you should schedule it:

```php
<?php
# 3 minutes from now
$start_at = time() + 3*60;

# Run task every 2 minutes, repeat 10 times
$iw->postScheduleAdvanced('HelloWorld', array(), $start_at, 2*60, null, 10);
```

## Status of a Worker
To get the status of a worker, you can use the ```getTaskDetails()``` method.

```php
<?php
$task_id = $iw->postTask('HelloWorld');
$details = $iw->getTaskDetails($task_id);

echo $details->status; # prints 'queued', 'complete', 'error' etc.
```

## Get Worker Log

Use any function that print text inside your worker to put messages to log.

```php
<?php
$task_id = $iw->postTask('HelloWorld');
sleep(10);
$details = $iw->getTaskDetails($task_id);
# Check log only if task is finished.
if ($details->status != 'queued'){
    $log = $iw->getLog($task_id);
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

$iw->postTask('HelloWorld', $payload);

$iw->postScheduleSimple('HelloWorld', $payload, 10)

$iw->postScheduleAdvanced('HelloWorld', $payload, time()+3*60, 2*60, null, 5);
```

When your code is executed, it will be passed three program arguments:

* **-id** - The task id.
* **-payload** - the filename containing the data payload for this particular task.
* **-d** - the user writable directory that can be used while running your job.

IronWorker provide functions `getArgs()` and `getPayload()` in your worker to help you using payload:

```php
<?php
$args = getArgs();

echo "Hello PHP World!\n";

print_r($args);

```

## Setting progress status

To set current task progress, just call `setProgress($percent, $message)` inside your worker.

* percent - A percentage value that can be set to show how much progress a task is making
* msg - A human readable message string that can be used when showing the status of a task

To retrieve this data on client side, use `$iw->getTaskDetails($task_id);`

# Full Documentation

You can find more documentation here:

* http://dev.iron.io Full documetation for iron.io products.
* [IronWorker PHP reference](http://iron-io.github.com/iron_worker_php/).
* [IronWorker PHP Wiki pages](https://github.com/iron-io/iron_worker_php/wiki).
* [IronWorker PHP Examples](https://github.com/iron-io/iron_worker_examples/tree/master/php)