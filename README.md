iron_worker_php is PHP language binding for IronWorker.

IronWorker is a massively scalable background processing system.
[See How It Works](http://www.iron.io/worker/how-it-works)

# Getting Started

## Branches
* `1.*` - Laravel 4.0/4.1/4.2/5.0 compatible, PHP 5.2 compatible version. No namespaces.
* `2.*` - Laravel 5.1+ compatible, PSR-4 compatible version with namespaces.
* `master` branch - same as `2.*`

## Get credentials
To start using iron_worker_php, you need to sign up and get an oauth token.

1. Go to http://iron.io/ and sign up.
2. Get an Oauth Token at http://hud.iron.io/tokens

## Install iron_worker_php

There are two ways to use iron_worker_php:

##### Using composer

Create `composer.json` file in project directory:

```json
{
    "require": {
        "iron-io/iron_worker": "2.*"
    }
}
```

Do `composer install` (install it if needed: https://getcomposer.org/download/)

And use it:

```php
require __DIR__ . '/vendor/autoload.php';

$worker = new \IronWorker\IronWorker();
```


##### Using classes directly (strongly not recommended)

1. Copy classes from `src` to target directory
2. Grab IronCore classes [there](https://github.com/iron-io/iron_core_php) and copy to target directory
3. Include them all.

```php
require 'src/HttpException.php';
require 'src/JsonException.php';
require 'src/IronCore.php';
require 'src/IronWorker.php';
require 'src/IronWorkerException.php';

$worker = new \IronWorker\IronWorker();
```

## Configure
Three ways to configure IronWorker:

* Passing array with options:

```php
<?php
$worker = new \IronWorker\IronWorker(array(
    'token' => 'XXXXXXXXX',
    'project_id' => 'XXXXXXXXX'
));
```
* Passing ini file name which stores your configuration options. Rename sample_config.ini to config.ini and include your Iron.io credentials (`token` and `project_id`):

```php
<?php
$worker = new \IronWorker\IronWorker('iron.json');
```

* Automatic config search - pass zero arguments to constructor and library will try to find config file in following locations:

    * `iron.ini` in current directory
    * `iron.json` in current directory
    * `IRON_WORKER_TOKEN`, `IRON_WORKER_PROJECT_ID` and other environment variables
    * `IRON_TOKEN`, `IRON_PROJECT_ID` and other environment variables
    * `.iron.ini` in user's home directory
    * `.iron.json` in user's home directory

## Creating a Worker

Here's an example worker:

```php
<?php
echo "Hello PHP World!\n";
```

## Upload code to server

### Using CLI tool (preferred)

* Get [CLI](http://dev.iron.io/worker/reference/cli) tool
* Download or create `iron.json` config file with project_id/password
* Create `HelloWorld.worker` file, example:

```ruby
runtime 'php'
exec 'HelloWorld.php'
```
* Upload!

```sh
$ iron_worker upload HelloWorld
```

[.worker syntax reference](http://dev.iron.io/worker/reference/dotworker/)

## Worker examples

You can find plenty of good worker examples here: [iron_worker_examples](https://github.com/iron-io/iron_worker_examples/tree/master/php)

## Queueing a Worker

```php
<?php
$payload = array()
$options = array('label' => 'label_name', 'cluster' => 'dedicated')
$task_id = $worker->postTask('HelloWorld', $payload, $options);
```

#### queueing options
  - **priority**: The priority queue to run the task in. Valid values are 0, 1, and 2. 0 is the default.
  - **timeout**: The maximum runtime of your task in seconds. No task can exceed 3600 seconds (60 minutes). The default is 3600 but can be set to a shorter duration.
  - **delay**: The number of seconds to delay before actually queuing the task. Default is 0.
  - **label**: Optional text label for your task.
  - **cluster**: cluster name ex: "high-mem" or "dedicated".  This is a premium feature for customers to have access to more powerful or custom built worker solutions. Dedicated worker clusters exist for users who want to reserve a set number of workers just for their queued tasks. If not set default is set to  "default" which is the public IronWorker cluster.

## Scheduling a Worker
#### postScheduleAdvanced($name, $payload, $start_at, $label = null, $run_every = null, $end_at = null, $run_times = null, $priority = null, $cluster = null)

If you want to run worker tasks in specific time intervals, once at a particular time, or **n** number of things starting at a specific time you should schedule it:

```php
<?php
$options = array('label' => 'label_name', 'cluster' => 'default');
$task_id = $worker->postSchedule('HelloWorkerRuby', $options);
```

#### scheduling options
  - **run_every**: The amount of time, in seconds, between runs.  By default, the task will only run once. run_every will return a 400 error if it is set to less than 60.
  - **end_at**: The time tasks will stop being queued. Should be a time or datetime.
  - **run_times**: The number of times a task will run.
  - **priority**: The priority queue to run the job in. Valid values are 0, 1, and 2. The default is 0. Higher values means
  - **tasks** spend less time in the queue once they come off the schedule.
  - **start_at**: The time the scheduled task should first be run.
  - **label**: Optional label for adding custom labels to scheduled tasks.
  - **cluster**: cluster name ex: "high-mem" or "dedicated". If not set default is set to "default" which is the public IronWorker cluster.

## Status of a Worker
To get the status of a worker, you can use the ```getTaskDetails()``` method.

```php
<?php
$task_id = $worker->postTask('HelloWorld');
$details = $worker->getTaskDetails($task_id);

echo $details->status; # prints 'queued', 'complete', 'error' etc.
```

## Get Worker Log

Use any function that print text inside your worker to put messages to log.

```php
<?php
$task_id = $worker->postTask('HelloWorld');
sleep(10);
$details = $worker->getTaskDetails($task_id);
# Check log only if task is finished.
if ($details->status != 'queued') {
    $log = $worker->getLog($task_id);
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

$worker->postTask('HelloWorld', $payload);

$worker->postScheduleSimple('HelloWorld', $payload, 10)

$worker->postScheduleAdvanced('HelloWorld', $payload, time()+3*60, 2*60, null, 5);
```

When your code is executed, it will be passed four program arguments:

* **-id** - The task id.
* **-payload** - the filename containing the data payload for this particular task.
* **-d** - the user writable directory that can be used while running your job.
* **-config** - the filename containing config data (if available) for particular code.

IronWorker provide functions `getArgs()`, `getPayload()`, `getConfig()` in your worker to help you using payload:

```php
<?php
$args = getArgs();

echo "Hello PHP World!\n";

print_r($args);

```

## Setting Task Priority

You can specify priority of the task by setting the corresponding parameter.

```php
$options = array('priority' => 1);
# Run task with medium priority
$worker->postTask('HelloWorld', $payload, $options);
```

Value of priority parameter means the priority queue to run the task in. Valid values are 0, 1, and 2. 0 is the default.

## Setting progress status

To set current task progress, just call `setProgress($percent, $message)` inside your worker.

* percent - A percentage value that can be set to show how much progress a task is making
* msg - A human readable message string that can be used when showing the status of a task

To retrieve this data on client side, use `$worker->getTaskDetails($task_id);`

# Troubleshooting

### http error: 0

If you see  `Uncaught exception 'Http_Exception' with message 'http error: 0 | '`
it most likely caused by misconfigured cURL https certificates.
There are two ways to fix this error:

1. Disable SSL certificate verification - add this line after IronWorker initialization: `$worker->ssl_verifypeer = false;`
2. Switch to http protocol - add this to configuration options: `protocol = http` and `port = 80`
3. Fix the error! Recommended solution: download actual certificates - [cacert.pem](http://curl.haxx.se/docs/caextract.html) and add them to `php.ini`:

```
[PHP]

curl.cainfo = "path\to\cacert.pem"
```

# Full Documentation

You can find more documentation here:

* http://dev.iron.io Full documetation for iron.io products.
* [IronWorker PHP reference](http://iron-io.github.com/iron_worker_php/).
* [IronWorker PHP Wiki pages](https://github.com/iron-io/iron_worker_php/wiki).
* [IronWorker PHP Examples](https://github.com/iron-io/iron_worker_examples/tree/master/php)
