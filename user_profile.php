<?php
/**
 * Custom user profile page for filtered participants
 *
 * Displays user profile information and link to blog articles
 *
 * @package    local_filteredparticipants
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

$userid = required_param('id', PARAM_INT);
$courseid = optional_param('course', 0, PARAM_INT);

require_login();

// Get user record.
$user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

// Check if user is deleted.
if ($user->deleted) {
  print_error('userdeleted');
}

// Set up page.
$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/filteredparticipants/user_profile.php', ['id' => $userid]));
$PAGE->set_title(fullname($user));
$PAGE->set_heading(fullname($user));
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();

// Display user picture and basic info.
echo html_writer::start_div('user-profile-header', ['style' => 'margin-bottom: 2rem;']);

// User picture.
echo html_writer::start_div('user-picture-container', ['style' => 'text-align: center; margin-bottom: 1.5rem;']);
echo $OUTPUT->user_picture($user, ['size' => 100, 'class' => 'user-profile-picture']);
echo html_writer::end_div();

// User details card.
echo html_writer::start_div('card');
echo html_writer::start_div('card-body');

echo html_writer::tag('h3', fullname($user), ['class' => 'card-title']);

// User details table.
$table = new html_table();
$table->attributes['class'] = 'generaltable table-sm';
$table->data = [];

// Email.
if (!empty($user->email)) {
  $table->data[] = [
    html_writer::tag('strong', get_string('email')),
    html_writer::link('mailto:' . $user->email, $user->email)
  ];
}

// Username.
$table->data[] = [
  html_writer::tag('strong', get_string('username')),
  s($user->username)
];

// First access.
if ($user->firstaccess) {
  $table->data[] = [
    html_writer::tag('strong', get_string('firstaccess')),
    userdate($user->firstaccess)
  ];
}

// Last access.
if ($user->lastaccess) {
  $table->data[] = [
    html_writer::tag('strong', get_string('lastaccess')),
    userdate($user->lastaccess)
  ];
}

// City/Country.
if (!empty($user->city) && !empty($user->country)) {
  $countries = get_string_manager()->get_list_of_countries();
  $country = isset($countries[$user->country]) ? $countries[$user->country] : $user->country;
  $table->data[] = [
    html_writer::tag('strong', get_string('location')),
    s($user->city) . ', ' . $country
  ];
}

echo html_writer::table($table);

echo html_writer::end_div(); // card-body
echo html_writer::end_div(); // card

echo html_writer::end_div(); // user-profile-header

// Blog and additional links section.
echo html_writer::start_div('user-additional-links', ['style' => 'margin-top: 2rem;']);

echo html_writer::tag('h4', get_string('additionallinks', 'local_filteredparticipants'));

// Create buttons for additional actions.
echo html_writer::start_div('btn-group', ['role' => 'group', 'style' => 'gap: 0.5rem; display: flex; flex-wrap: wrap;']);

// Blog link (no course context).
$blogurl = new moodle_url('/blog/index.php', ['userid' => $userid]);
echo html_writer::link(
  $blogurl,
  get_string('viewblog', 'local_filteredparticipants'),
  ['class' => 'btn btn-secondary']
);

// Full profile link.
$profileurl = new moodle_url('/user/profile.php', ['id' => $userid]);
echo html_writer::link(
  $profileurl,
  get_string('fullprofile', 'local_filteredparticipants'),
  ['class' => 'btn btn-primary']
);

// Messages link.
if ($CFG->messaging) {
  $messageurl = new moodle_url('/message/index.php', ['id' => $userid]);
  echo html_writer::link(
    $messageurl,
    get_string('sendmessage', 'message'),
    ['class' => 'btn btn-info']
  );
}

echo html_writer::end_div(); // btn-group

echo html_writer::end_div(); // user-additional-links

// Back to course link if course context provided.
if ($courseid > 0) {
  $backtocourse = new moodle_url('/local/filteredparticipants/index.php', ['id' => $courseid]);
  echo html_writer::div(
    html_writer::link($backtocourse, '← ' . get_string('backtocourse', 'local_filteredparticipants'), ['class' => 'btn btn-link']),
    '',
    ['style' => 'margin-top: 2rem;']
  );
}

echo $OUTPUT->footer();
