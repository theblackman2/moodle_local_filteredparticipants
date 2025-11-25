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

// User profile container with modern design.
echo html_writer::start_div('container-fluid', ['style' => 'max-width: 1000px; margin: 0 auto;']);

// User header section with picture and name.
echo html_writer::start_div('row mb-4');
echo html_writer::start_div('col-12 text-center');
echo html_writer::div(
  $OUTPUT->user_picture($user, ['size' => 120, 'class' => 'rounded-circle']),
  'mb-3'
);
echo html_writer::tag('h2', fullname($user), ['class' => 'mb-1']);
if (!empty($user->email)) {
  echo html_writer::tag('p', html_writer::link('mailto:' . $user->email, $user->email), ['class' => 'text-muted']);
}
echo html_writer::end_div(); // col-12
echo html_writer::end_div(); // row

// User description if available.
if (!empty($user->description)) {
  echo html_writer::start_div('row mb-4');
  echo html_writer::start_div('col-12');
  echo html_writer::start_div('card');
  echo html_writer::start_div('card-body');
  echo html_writer::tag('h5', get_string('description'), ['class' => 'card-title']);
  echo html_writer::div(format_text($user->description, $user->descriptionformat), 'card-text');
  echo html_writer::end_div(); // card-body
  echo html_writer::end_div(); // card
  echo html_writer::end_div(); // col-12
  echo html_writer::end_div(); // row
}

// User information cards.
echo html_writer::start_div('row mb-4');

// Basic info card.
echo html_writer::start_div('col-md-6 mb-3');
echo html_writer::start_div('card h-100');
echo html_writer::start_div('card-body');
echo html_writer::tag('h5', get_string('userdetails'), ['class' => 'card-title mb-3']);

// Username.
echo html_writer::start_div('mb-2');
echo html_writer::tag('strong', get_string('username') . ': ', ['class' => 'text-muted']);
echo html_writer::tag('span', s($user->username));
echo html_writer::end_div();

// Email (if not already shown in header).
if (empty($user->email)) {
  echo html_writer::start_div('mb-2');
  echo html_writer::tag('strong', get_string('email') . ': ', ['class' => 'text-muted']);
  echo html_writer::tag('span', get_string('none'));
  echo html_writer::end_div();
}

echo html_writer::end_div(); // card-body
echo html_writer::end_div(); // card
echo html_writer::end_div(); // col-md-6

// Location card.
if (!empty($user->city) || !empty($user->country)) {
  echo html_writer::start_div('col-md-6 mb-3');
  echo html_writer::start_div('card h-100');
  echo html_writer::start_div('card-body');
  echo html_writer::tag('h5', get_string('location'), ['class' => 'card-title mb-3']);

  if (!empty($user->city)) {
    echo html_writer::start_div('mb-2');
    echo html_writer::tag('strong', get_string('city') . ': ', ['class' => 'text-muted']);
    echo html_writer::tag('span', s($user->city));
    echo html_writer::end_div();
  }

  if (!empty($user->country)) {
    $countries = get_string_manager()->get_list_of_countries();
    $country = isset($countries[$user->country]) ? $countries[$user->country] : $user->country;
    echo html_writer::start_div('mb-2');
    echo html_writer::tag('strong', get_string('country') . ': ', ['class' => 'text-muted']);
    echo html_writer::tag('span', $country);
    echo html_writer::end_div();
  }

  echo html_writer::end_div(); // card-body
  echo html_writer::end_div(); // card
  echo html_writer::end_div(); // col-md-6
}

echo html_writer::end_div(); // row

// Action buttons section.
echo html_writer::start_div('row mb-4');
echo html_writer::start_div('col-12');

echo html_writer::tag('h5', get_string('additionallinks', 'local_filteredparticipants'), ['class' => 'mb-3']);

echo html_writer::start_div('d-flex flex-wrap gap-2');

// Blog link (no course context).
$blogurl = new moodle_url('/blog/index.php', ['userid' => $userid]);
echo html_writer::link(
  $blogurl,
  html_writer::tag('i', '', ['class' => 'fa fa-book mr-1']) . get_string('viewblog', 'local_filteredparticipants'),
  ['class' => 'btn btn-outline-secondary']
);

// Full profile link.
$profileurl = new moodle_url('/user/profile.php', ['id' => $userid]);
echo html_writer::link(
  $profileurl,
  html_writer::tag('i', '', ['class' => 'fa fa-user mr-1']) . get_string('fullprofile', 'local_filteredparticipants'),
  ['class' => 'btn btn-outline-primary']
);

// Messages link.
if ($CFG->messaging) {
  $messageurl = new moodle_url('/message/index.php', ['id' => $userid]);
  echo html_writer::link(
    $messageurl,
    html_writer::tag('i', '', ['class' => 'fa fa-envelope mr-1']) . get_string('sendmessage', 'message'),
    ['class' => 'btn btn-outline-info']
  );
}

echo html_writer::end_div(); // d-flex

echo html_writer::end_div(); // col-12
echo html_writer::end_div(); // row

// Back to course link if course context provided.
if ($courseid > 0) {
  $backtocourse = new moodle_url('/local/filteredparticipants/index.php', ['id' => $courseid]);
  echo html_writer::div(
    html_writer::link(
      $backtocourse,
      html_writer::tag('i', '', ['class' => 'fa fa-arrow-left mr-1']) . get_string('backtocourse', 'local_filteredparticipants'),
      ['class' => 'btn btn-link']
    ),
    'row',
    ['style' => 'margin-top: 1rem;']
  );
}

echo html_writer::end_div(); // container-fluid

echo $OUTPUT->footer();
