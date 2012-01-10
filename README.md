iron_worker_php is PHP language binding for IronWorker.

IronWorker is a massively scalable background processing system.

# Getting Started


## Get credentials
To start using iron_worker_php, you need to sign up and get an oauth token.
* Go to http://iron.io/ and sign up.
* Get an Oauth Token at http://hud.iron.io/tokens
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
* Passing ini file name which store your configuration options. Rename sample_config.ini to config.ini and include your Iron.io credentials (token and project_id):

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


 