<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
  'local/filteredparticipants:view' => [
    'captype' => 'read',
    'contextlevel' => CONTEXT_COURSE,
    'archetypes' => [
      'manager' => CAP_ALLOW,
      'editingteacher' => CAP_ALLOW,
    ],
  ],
  'local/filteredparticipants:manage' => [
    'captype' => 'write',
    'contextlevel' => CONTEXT_COURSE,
    'archetypes' => [
      'manager' => CAP_ALLOW,
    ],
  ],
];
