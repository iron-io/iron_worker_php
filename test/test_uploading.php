<?php

class TestUploading extends IronUnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->worker = new IronWorker('_config.json');
        $this->worker->ssl_verifypeer = false;
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testBasicUploading()
    {
        $this->assertTrue($this->worker->upload($this->workerDir(), 'worker.php', 'TestWorker'));
    }

    public function testZipCreation()
    {
        $this->assertTrue(
            IronWorker::createZip($this->workerDir(), array('worker.php'), '_worker.zip', true)
        );
        $this->assertFalse(
            IronWorker::createZip($this->workerDir(), array('not_exist.php'), '_worker.zip', true)
        );
    }

    public function testZipUploading()
    {
        IronWorker::createZip($this->workerDir(), array('worker.php'), '_worker.zip', true);
        $res = $this->worker->postCode('worker.php', '_worker.zip', 'TestWorker');
        $this->assertEqual($res->msg, 'Upload successful.');
    }

    public function testGetCodesList()
    {
        $codes = $this->worker->getCodes();
        $this->assertTrue(is_array($codes));
        $this->assertTrue(strlen($codes[0]->id) > 0);
    }

    public function testUploadingWithOptions()
    {
        $options = array(
            'max_concurrency' => 10,
            'retries' => 5,
            'retries_delay' => 20
        );
        $this->assertTrue($this->worker->upload($this->workerDir(), 'worker.php', 'TestWorkerOptions', $options));

        $codes = $this->worker->getCodes();

        $is_worker_present = false;
        foreach ($codes as $code) {
            if ($code->name == 'TestWorkerOptions') {
                $this->assertEqual($code->max_concurrency, 10);
                $this->assertEqual($code->retries, 5);
                $this->assertEqual($code->retries_delay, 20);
                $is_worker_present = true;
            }
        }
        $this->assertTrue($is_worker_present);
    }

    public function testGetCodeDetails()
    {
        $codes   = $this->worker->getCodes();
        $details = $this->worker->getCodeDetails($codes[0]->id);
        $this->assertEqual($details->id, $codes[0]->id);
        $this->assertEqual($details->name, $codes[0]->name);
    }
}
