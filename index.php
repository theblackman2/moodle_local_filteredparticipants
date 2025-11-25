<?php
/**
 * Filtered participants report page
 *
 * Displays a list of users enrolled in a course filtered by role IDs.
 *
 * @package    local_filteredparticipants
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir . '/tablelib.php');

$courseid = required_param('id', PARAM_INT);
$rolesparam = optional_param('roles', '', PARAM_SEQUENCE);
$page = optional_param('page', 0, PARAM_INT);

// Validate course.
try {
  $course = get_course($courseid);
} catch (Exception $e) {
  print_error('invalid_course', 'local_filteredparticipants');
}

require_login($course);

$context = context_course::instance($course->id);

require_capability('local/filteredparticipants:view', $context);

$PAGE->set_url(new moodle_url('/local/filteredparticipants/index.php', [
  'id' => $courseid,
  'roles' => $rolesparam,
  'page' => $page
]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('title', 'local_filteredparticipants'));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('title', 'local_filteredparticipants'));

// Parse role IDs.
$roleids = [];
if (!empty($rolesparam)) {
  foreach (explode(',', $rolesparam) as $rid) {
    $rid = (int) trim($rid);
    if ($rid > 0) {
      $roleids[] = $rid;
    }
  }
}

// Validate that we have role IDs.
if (empty($roleids)) {
  echo $OUTPUT->notification(get_string('no_roles_selected', 'local_filteredparticipants'), 'error');
  echo $OUTPUT->footer();
  exit;
}

// Validate that role IDs exist in the system.
global $DB;
if (!empty($roleids)) {
  list($rolesql, $roleparams) = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED, 'roleid');
  $existingroles = $DB->get_records_select('role', "id $rolesql", $roleparams, '', 'id');
  $validroleids = array_keys($existingroles);
  $roleids = array_intersect($roleids, $validroleids);
  
  // DEBUG: Show what roles were validated
  if (debugging('', DEBUG_DEVELOPER)) {
    echo html_writer::div('DEBUG - Parsed role IDs: ' . implode(', ', array_keys($existingroles)), 'alert alert-info');
    echo html_writer::div('DEBUG - Valid role IDs after validation: ' . implode(', ', $roleids), 'alert alert-info');
  }
}

if (empty($roleids)) {
  echo $OUTPUT->notification(get_string('no_roles_selected', 'local_filteredparticipants'), 'error');
  echo $OUTPUT->footer();
  exit;
}

// Set up caching.
$cache = cache::make('local_filteredparticipants', 'userlist');
$cachekey = 'users_' . $courseid . '_' . implode('_', $roleids) . '_' . $page;

// Try to get cached data.
$cacheddata = $cache->get($cachekey);

if ($cacheddata !== false) {
  $users = $cacheddata['users'];
  $totalcount = $cacheddata['totalcount'];
} else {
  // Get context path to include parent contexts (system, category).
  $contextids = array_map(function($ctx) { return $ctx->id; }, $context->get_parent_contexts(true));
  
  // Build SQL to get total count.
  list($insql, $params) = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED, 'role');
  list($contextsql, $contextparams) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'ctx');
  $params = array_merge($params, $contextparams);

  $countsql = "
        SELECT COUNT(DISTINCT u.id)
        FROM {role_assignments} ra
        JOIN {user} u ON u.id = ra.userid
        WHERE ra.contextid $contextsql
        AND ra.roleid $insql
        AND u.deleted = 0
    ";

  $totalcount = $DB->count_records_sql($countsql, $params);

  if ($totalcount == 0) {
    echo $OUTPUT->notification(get_string('no_users', 'local_filteredparticipants'), 'info');
    echo $OUTPUT->footer();
    exit;
  }

  // Pagination settings.
  $perpage = 50;
  $offset = $page * $perpage;

  // Build SQL with pagination.
  $sql = "
        SELECT DISTINCT u.*
        FROM {role_assignments} ra
        JOIN {user} u ON u.id = ra.userid
        WHERE ra.contextid $contextsql
        AND ra.roleid $insql
        AND u.deleted = 0
        ORDER BY u.lastname, u.firstname
    ";

  $users = $DB->get_records_sql($sql, $params, $offset, $perpage);

  // Cache the results.
  $cache->set($cachekey, [
    'users' => $users,
    'totalcount' => $totalcount
  ]);
}

// Set up table with sortable columns.
$table = new html_table();
$table->head = [
  get_string('fullname', 'local_filteredparticipants'),
  get_string('useremail', 'local_filteredparticipants'),
  get_string('username', 'local_filteredparticipants')
];
$table->attributes['class'] = 'generaltable';

foreach ($users as $user) {
  $profileurl = new moodle_url('/user/view.php', ['id' => $user->id, 'course' => $course->id]);
  $row = [];
  $row[] = html_writer::link($profileurl, fullname($user, true));
  $row[] = s($user->email);
  $row[] = s($user->username);
  $table->data[] = $row;
}

echo html_writer::table($table);

// Add pagination bar.
$perpage = 50;
$baseurl = new moodle_url('/local/filteredparticipants/index.php', [
  'id' => $courseid,
  'roles' => $rolesparam
]);
echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);

echo $OUTPUT->footer();

