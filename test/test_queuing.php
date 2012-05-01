<?php

class TestQueueing extends IronUnitTestCase {

    function setUp() {
        parent::setUp();
        $this->worker = new IronWorker('_config.json');
        $this->worker->upload($this->workerDir(), 'worker.php', 'TestWorker');
    }

    function tearDown() {
        parent::tearDown();
    }

    function testPostTask(){
        $task_id = $this->worker->postTask('TestWorker');
        $this->assertTrue(is_string($task_id));
        $this->assertTrue(strlen($task_id) > 0);
    }

    function testWaitFor(){
        $task_id = $this->worker->postTask('TestWorker');
        $details = $this->worker->waitFor($task_id);
        $this->assertTrue($details->id        == $task_id);
        $this->assertTrue($details->code_name == 'TestWorker');
        $this->assertTrue($details->status    == 'complete');
    }

    function testTaskDetails(){
        $task_id = $this->worker->postTask('TestWorker');
        $details = $this->worker->getTaskDetails($task_id);
        $this->assertTrue($details->id        == $task_id);
        $this->assertTrue($details->code_name == 'TestWorker');
    }

    function testTaskLog(){
        $task_id = $this->worker->postTask('TestWorker', array('test' => 'search_string'));
        $this->worker->waitFor($task_id);
        $log = $this->worker->getLog($task_id);

        $this->assertTrue(strlen($log) > 0);
        $this->assertTrue(strpos($log, 'Hello PHP') !== false);
        $this->assertTrue(strpos($log, 'search_string') !== false);
    }

    function testTaskProgress(){
        $task_id = $this->worker->postTask('TestWorker');
        $res = $this->worker->setTaskProgress($task_id, 50, 'Job half-done');
        $this->assertTrue($res->status_code == '200');
    }

}