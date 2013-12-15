<?php

class TestScheduling extends IronUnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->worker = new IronWorker('_config.json');
        $this->worker->ssl_verifypeer = false;
        $this->worker->upload($this->workerDir(), 'worker.php', 'TestWorker');
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function postScheduleAdvanced()
    {
        $schedule_id = $this->worker->postScheduleAdvanced('TestWorker', array(), time(), 60, null, 2);
        $this->assertTrue(is_string($schedule_id));
    }


    public function testGetSchedules()
    {
        $this->worker->postScheduleAdvanced('TestWorker', array(), time()+60, 60, null, 2);
        $schedules = $this->worker->getSchedules();
        $this->assertTrue(is_array($schedules));
        $this->assertTrue(strlen($schedules[0]->id) > 0);
    }


    public function testGetSchedule()
    {
        $schedule_id = $this->worker->postScheduleAdvanced('TestWorker', array(), time()+60, 60, null, 1);
        $schedule    = $this->worker->getSchedule($schedule_id);
        $this->assertEqual($schedule->code_name, 'TestWorker');
        $this->assertEqual($schedule->status, 'scheduled');
        $this->assertEqual($schedule->id, $schedule_id);
    }

    public function testDeleteSchedule()
    {
        $schedule_id = $this->worker->postScheduleAdvanced('TestWorker', array(), time()+60, 60, null, 1);
        $res = $this->worker->deleteSchedule($schedule_id);
        $this->assertEqual($res->msg, 'Cancelled');
    }
}
