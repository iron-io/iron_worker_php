iron_worker_php is PHP language binding for IronWorker.

IronWorker is a massively scalable background processing system.
[See How It Works](http://www.iron.io/products/worker/how)

# Getting Started


## Get credentials
To start using iron_worker_php, you need to sign up and get an oauth token.

1. Go to http://iron.io/ and sign up.
2. Get an Oauth Token at http://hud.iron.io/tokens

## Install iron_worker_php

There are two ways to use iron_worker_php:

#### Using precompiled phar archive:

Copy `iron_worker.phar` to target directory and include it:

```php
<?php
require_once "phar://iron_worker.phar";
```

Please note, [phar](http://php.net/manual/en/book.phar.php) extension available by default only from php 5.3.0
For php 5.2 you should install phar manually or use second option.

#### Using classes directly

1. Copy `IronWorker.class.php` to target directory
2. Grab `IronCore.class.php` [there](https://github.com/iron-io/iron_core_php) and copy to target directory
3. Include both of them:

```php
<?php
require_once "IronCore.class.php"
require_once "IronWorker.class.php"
```

#### Using Composer

Follow instructions at https://packagist.org/

[iron_worker package](https://packagist.org/packages/iron-io/iron_worker)

## Configure
Three ways to configure IronWorker:

* Passing array with options:

```php
<?php
$worker = new IronWorker(array(
    'token' => 'XXXXXXXXX',
    'project_id' => 'XXXXXXXXX'
));
```
* Passing ini file name which stores your configuration options. Rename sample_config.ini to config.ini and include your Iron.io credentials (`token` and `project_id`):

```php
<?php
$worker = new IronWorker('config.ini');
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
$task_id = $worker->postTask('HelloWorld');
```
Worker should start in a few seconds.

## Scheduling a Worker
If you want to run your code more than once or run it in regular intervals, you should schedule it:

```php
<?php
# 3 minutes from now
$start_at = time() + 3*60;

# Run task every 2 minutes, repeat 10 times
$worker->postScheduleAdvanced('HelloWorld', array(), $start_at, 2*60, null, 10);
```

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
$options = array('priority' => '1');
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

# Full Documentation

You can find more documentation here:

* http://dev.iron.io Full documetation for iron.io products.
* [IronWorker PHP reference](http://iron-io.github.com/iron_worker_php/).
* [IronWorker PHP Wiki pages](https://github.com/iron-io/iron_worker_php/wiki).
* [IronWorker PHP Examples](https://github.com/iron-io/iron_worker_examples/tree/master/php)
