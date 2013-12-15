<?php

require_once '../../tools/simpletest/unit_tester.php';
require_once '../../tools/simpletest/reporter.php';
require_once '../../tools/simpletest/autorun.php';
require_once '../../iron_core_php/IronCore.class.php';
require_once '../IronWorker.class.php';

class IronUnitTestCase extends UnitTestCase
{
    public $worker;

    public function setUp()
    {
        $ini_config = parse_ini_file('../config.ini', true);
        $config = $ini_config['iron_worker'];

        $this->writeJsonFile(array('iron_worker' => $config));
        $this->writeIniFile(array('iron_worker' => $config));
    }

    public function tearDown()
    {
        @unlink(dirname(__FILE__)."/_config.json");
        @unlink(dirname(__FILE__)."/_config.ini");
        @unlink(dirname(__FILE__)."/_worker.zip");
    }

    public function workerDir()
    {
        return dirname(__FILE__)."/worker/";
    }

    public function writeJsonFile($data)
    {
        file_put_contents(dirname(__FILE__)."/_config.json", json_encode($data));
    }

    public function writeIniFile($array, $quote_keys = true)
    {
        $file = dirname(__FILE__)."/_config.ini";
        $text = '';
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $text .= "[$key]\r\n";
                foreach ($value as $k => $v) {
                     $text .= (is_numeric($k) || ctype_xdigit($k) || !$quote_keys ? "$k=" : '"'.$k.'"=').
                        (is_numeric($v) || ctype_xdigit($v) ? "$v\r\n" : '"'.$v.'"'."\r\n");
                }
                $text .= "\r\n";
            } else {
                $text .= (is_numeric($key) || ctype_xdigit($key) || !$quote_keys ? "$key=" : '"'.$key.'"=').
                    (is_numeric($value) || ctype_xdigit($value) ? "$value\r\n" : '"'.$value.'"'."\r\n");
            }
        }
        return file_put_contents($file, $text);
    }
}


class AllTests extends TestSuite
{
    public function runAllTests()
    {
        $this->TestSuite('All tests');
        $this->addFile('test_uploading.php');
        $this->addFile('test_queuing.php');
        $this->addFile('test_scheduling.php');
    }
}
