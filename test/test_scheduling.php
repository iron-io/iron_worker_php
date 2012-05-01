<?php

class TestScheduling extends IronUnitTestCase {
    function setUp() {
        parent::setUp();
        $this->worker = new IronWorker('_config.json');
        $this->worker->upload($this->workerDir(), 'worker.php', 'TestWorker');
    }

    function tearDown() {
        parent::tearDown();
    }

    function postScheduleAdvanced(){
        $schedule_id = $this->worker->postScheduleAdvanced('TestWorker', array(), time(), 2, null, 2);
        $this->assertTrue(is_string($schedule_id));
        $this->assertTrue(strlen($schedule_id) > 0);
    }


    function testGetSchedules(){
        $schedules = $this->worker->getSchedules();
        $this->assertTrue(is_array($schedules));
        $this->assertTrue(strlen($schedules[0]->id) > 0);
    }


    function testGetSchedule(){
        $schedule_id = $this->worker->postScheduleAdvanced('TestWorker', array(), time()+60, 2, null, 1);
        $schedule    = $this->worker->getSchedule($schedule_id);
        $this->assertEqual($schedule->code_name, 'TestWorker');
        $this->assertEqual($schedule->status,    'scheduled');
        $this->assertEqual($schedule->id,        $schedule_id);
    }


    function testDeleteSchedule(){
        $schedule_id = $this->worker->postScheduleAdvanced('TestWorker', array(), time()+60, 2, null, 1);
        $res = $this->worker->deleteSchedule($schedule_id);
        $this->assertEqual($res->status_code, 200);
    }

}
