<?php
/**
 * PHPUnit tests for local_filteredparticipants plugin
 *
 * @package    local_filteredparticipants
 * @category   test
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_filteredparticipants;

use advanced_testcase;
use context_course;

/**
 * Test cases for filtered participants functionality
 *
 * @package    local_filteredparticipants
 * @category   test
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_filteredparticipants_test extends advanced_testcase
{

  /**
   * Test that users are correctly filtered by role
   */
  public function test_role_filtering()
  {
    global $DB;

    $this->resetAfterTest(true);

    // Create a course.
    $course = $this->getDataGenerator()->create_course();
    $context = context_course::instance($course->id);

    // Create users.
    $teacher1 = $this->getDataGenerator()->create_user(['firstname' => 'Teacher', 'lastname' => 'One']);
    $teacher2 = $this->getDataGenerator()->create_user(['firstname' => 'Teacher', 'lastname' => 'Two']);
    $student1 = $this->getDataGenerator()->create_user(['firstname' => 'Student', 'lastname' => 'One']);
    $student2 = $this->getDataGenerator()->create_user(['firstname' => 'Student', 'lastname' => 'Two']);

    // Get role IDs.
    $teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
    $studentrole = $DB->get_record('role', ['shortname' => 'student']);

    // Enrol users.
    $this->getDataGenerator()->enrol_user($teacher1->id, $course->id, $teacherrole->id);
    $this->getDataGenerator()->enrol_user($teacher2->id, $course->id, $teacherrole->id);
    $this->getDataGenerator()->enrol_user($student1->id, $course->id, $studentrole->id);
    $this->getDataGenerator()->enrol_user($student2->id, $course->id, $studentrole->id);

    // Test query for teachers only.
    list($insql, $params) = $DB->get_in_or_equal([$teacherrole->id], SQL_PARAMS_NAMED, 'role');
    $params['contextid'] = $context->id;

    $sql = "
            SELECT DISTINCT u.*
            FROM {role_assignments} ra
            JOIN {user} u ON u.id = ra.userid
            WHERE ra.contextid = :contextid
            AND ra.roleid $insql
            AND u.deleted = 0
            ORDER BY u.lastname, u.firstname
        ";

    $teachers = $DB->get_records_sql($sql, $params);

    // Assert we got 2 teachers.
    $this->assertCount(2, $teachers);

    // Verify they are the correct users.
    $teacherids = array_keys($teachers);
    $this->assertContains($teacher1->id, $teacherids);
    $this->assertContains($teacher2->id, $teacherids);
    $this->assertNotContains($student1->id, $teacherids);
    $this->assertNotContains($student2->id, $teacherids);
  }

  /**
   * Test pagination functionality
   */
  public function test_pagination()
  {
    global $DB;

    $this->resetAfterTest(true);

    // Create a course.
    $course = $this->getDataGenerator()->create_course();
    $context = context_course::instance($course->id);

    // Get student role.
    $studentrole = $DB->get_record('role', ['shortname' => 'student']);

    // Create 75 students (should span 2 pages with perpage=50).
    $students = [];
    for ($i = 1; $i <= 75; $i++) {
      $user = $this->getDataGenerator()->create_user([
        'firstname' => 'Student',
        'lastname' => sprintf('User%03d', $i)
      ]);
      $this->getDataGenerator()->enrol_user($user->id, $course->id, $studentrole->id);
      $students[] = $user;
    }

    // Test first page (limit 50, offset 0).
    list($insql, $params) = $DB->get_in_or_equal([$studentrole->id], SQL_PARAMS_NAMED, 'role');
    $params['contextid'] = $context->id;

    $sql = "
            SELECT DISTINCT u.*
            FROM {role_assignments} ra
            JOIN {user} u ON u.id = ra.userid
            WHERE ra.contextid = :contextid
            AND ra.roleid $insql
            AND u.deleted = 0
            ORDER BY u.lastname, u.firstname
        ";

    $page1users = $DB->get_records_sql($sql, $params, 0, 50);
    $this->assertCount(50, $page1users);

    // Test second page (limit 50, offset 50).
    $page2users = $DB->get_records_sql($sql, $params, 50, 50);
    $this->assertCount(25, $page2users);

    // Ensure no overlap.
    $page1ids = array_keys($page1users);
    $page2ids = array_keys($page2users);
    $this->assertEmpty(array_intersect($page1ids, $page2ids));
  }

  /**
   * Test caching functionality
   */
  public function test_caching()
  {
    $this->resetAfterTest(true);

    $cache = \cache::make('local_filteredparticipants', 'userlist');

    // Test cache set and get.
    $testdata = [
      'users' => ['user1', 'user2', 'user3'],
      'totalcount' => 3
    ];

    $cachekey = 'test_key_123';
    $cache->set($cachekey, $testdata);

    $retrieved = $cache->get($cachekey);

    $this->assertEquals($testdata, $retrieved);

    // Test cache purge.
    $cache->delete($cachekey);
    $this->assertFalse($cache->get($cachekey));
  }
}
