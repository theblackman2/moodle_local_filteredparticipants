<?php
defined('MOODLE_INTERNAL') || die();

$definitions = [
  'userlist' => [
    'mode' => cache_store::MODE_APPLICATION,
    'simplekeys' => true,
    'simpledata' => false,
    'staticacceleration' => true,
    'staticaccelerationsize' => 10,
  ],
];
