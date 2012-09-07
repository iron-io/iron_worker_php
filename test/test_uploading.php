<?php

class TestUploading extends IronUnitTestCase {

    function setUp() {
        parent::setUp();
        $this->worker = new IronWorker('_config.json');
    }

    function tearDown() {
        parent::tearDown();
    }

    function testBasicUploading() {
        $this->assertTrue($this->worker->upload($this->workerDir(), 'worker.php', 'TestWorker'));
    }

    function testZipCreation() {
        $this->assertTrue(
            IronWorker::createZip($this->workerDir(), array('worker.php'), '_worker.zip', true)
        );
        $this->assertFalse(
            IronWorker::createZip($this->workerDir(), array('not_exist.php'), '_worker.zip', true)
        );
    }

    function testZipUploading(){
        IronWorker::createZip($this->workerDir(), array('worker.php'), '_worker.zip', true);
        $res = $this->worker->postCode('worker.php', '_worker.zip', 'TestWorker');
        $this->assertEqual($res->msg, 'Upload successful.');
    }

    function testGetCodesList(){
        $codes = $this->worker->getCodes();
        $this->assertTrue(is_array($codes));
        $this->assertTrue(strlen($codes[0]->id) > 0);
    }

    function testGetCodeDetails(){
        $codes   = $this->worker->getCodes();
        $details = $this->worker->getCodeDetails($codes[0]->id);
        $this->assertEqual($details->id,   $codes[0]->id);
        $this->assertEqual($details->name, $codes[0]->name);
    }


}