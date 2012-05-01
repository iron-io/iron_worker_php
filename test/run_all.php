<?php

require_once '../../tools/simpletest/unit_tester.php';
require_once '../../tools/simpletest/reporter.php';
require_once '../../tools/simpletest/autorun.php';
require_once '../IronCore.class.php';
require_once '../IronWorker.class.php';

class IronUnitTestCase extends UnitTestCase {
    public $worker;

    function setUp() {
        $ini_config = parse_ini_file('../config.ini', true);
        $config = $ini_config['iron_worker'];

        $this->write_json_file(array('iron_worker' => $config));
    }

    function tearDown() {
        @unlink(dirname(__FILE__)."/_config.json");
        @unlink(dirname(__FILE__)."/_config.ini");
        @unlink(dirname(__FILE__)."/_worker.zip");
    }

    function workerDir(){
        return dirname(__FILE__)."/worker/";
    }

    function write_json_file($data){
        file_put_contents(dirname(__FILE__)."/_config.json", json_encode($data));
    }

    function write_ini_file($data){
        #TODO
    }
}


class AllTests extends TestSuite {
    function AllTests() {
        $this->TestSuite('All tests');
        $this->addFile('test_uploading.php');
        $this->addFile('test_queuing.php');
        $this->addFile('test_scheduling.php');
    }
}