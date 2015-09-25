<?php

namespace IronWorker;

use IronCore\IronCore;

/**
 * PHP runtime library for IronWorker
 * IronWorker is a massively scalable background processing system.
 * Use this class to access common functionality from inside an IronWorker task
 *
 * @link https://github.com/iron-io/iron_worker_php
 * @link http://www.iron.io/
 * @link http://dev.iron.io/
 * @package IronWorker
 * @copyright Feel free to copy, steal, take credit for, or whatever you feel like doing with this code. ;)
 */
class Runtime extends IronCore {

  /**
   * getConfig gets the configuration from the input to this worker
   */
  public static function getConfig($assoc = true)
  {
    $args = self::getArgs($assoc);
    return $args['config'];
  }

  /**
   * getArgs gets the arguments from the input to this worker
   */
  public static function getArgs($assoc = true)
  {
    global $argv;

    $args = array('task_id' => null, 'dir' => null, 'payload' => array(), 'config' => null);

    foreach ($argv as $k => $v)
    {
      if (empty($argv[$k + 1]))
      {
        continue;
      }

      if ($v == '-id') $args['task_id'] = $argv[$k + 1];
      if ($v == '-d') $args['dir'] = $argv[$k + 1];
      if ($v == '-payload') $args['payload_file'] = $argv[$k + 1];
      if ($v == '-config') $args['config_file'] = $argv[$k + 1];
    }

    if (getenv('TASK_ID')) $args['task_id'] = getenv('TASK_ID');
    if (getenv('TASK_DIR')) $args['dir'] = getenv('TASK_DIR');
    if (getenv('PAYLOAD_FILE')) $args['payload_file'] = getenv('PAYLOAD_FILE');
    if (getenv('CONFIG_FILE')) $args['config_file'] = getenv('CONFIG_FILE');

    if (array_key_exists('payload_file',$args) && file_exists($args['payload_file']))
    {
      $args['payload'] = file_get_contents($args['payload_file']);

      $parsed_payload = json_decode($args['payload'], $assoc);

      if ($parsed_payload != null)
      {
        $args['payload'] = $parsed_payload;
      }
    }

    if (array_key_exists('config_file', $args) && file_exists($args['config_file']))
    {
      $args['config'] = file_get_contents($args['config_file']);

      $parsed_config = json_decode($args['config'], $assoc);

      if ($parsed_config != null)
      {
        $args['config'] = $parsed_config;
      }
    }

    return $args;
  }

  /**
   * getPayload gets the payload from the input to this worker
   */
  public static function getPayload($assoc = false)
  {
    $args = self::getArgs($assoc);
    return $args['payload'];
  }
}
