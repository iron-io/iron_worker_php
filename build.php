<?php
/**
 * This script creates  .phar archive with all required dependencies.
 * Archive usage:
 * include("phar://iron_worker.phar");
 * or
 * include("phar://".dirname(__FILE__)."/iron_worker.phar");
 */
 
@unlink('iron_worker.phar');

$phar = new Phar('iron_worker.phar');

# Loader
$phar->setStub('<?php
Phar::mapPhar("iron_worker.phar");
if (!class_exists("IronCore")) {
    require "phar://iron_worker.phar/IronCore.class.php";
}
require "phar://iron_worker.phar/IronWorker.class.php";
__HALT_COMPILER(); ?>');

# Files
$phar->addFile('../iron_core_php/IronCore.class.php', 'IronCore.class.php');
$phar->addFile('IronWorker.class.php');
$phar->addFile('LICENSE', 'LICENSE');

echo "\ndone - ".(round(filesize('iron_worker.phar')/1024, 2))." KB\n";

# Verification
require "phar://iron_worker.phar";
$worker = new IronWorker('config.ini');

echo "Build finished successfully\n";
