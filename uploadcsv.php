<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details
 *
 * @package    block
 * @subpackage questionnaire manager
 * @copyright  2013 Learning Technology Services, www.lts.ie - Lead Developer: Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("$CFG->libdir/formslib.php");
require_once("$CFG->dirroot/blocks/questionnaire_manager/uploadcsv_form.php");
global $COURSE, $CFG, $DB;

$courseid  = optional_param('courseid', 0, PARAM_INT);
$contextid = optional_param('contextid', 0 , PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

require_course_login($course);

$context = get_context_instance_by_id($contextid);

require_capability('block/questionnaire_manager:uploadcsv', $context);

$data = new stdClass();
$data->coursename = $course->fullname;
$data->courseid = $course->id;

$options = array('subdirs' => 1,
                 'maxbytes' => $CFG->userquota,
                 'maxfiles' => 1,
                 'accepted_types' => '*.csv',
                 'return_types' => FILE_INTERNAL);

$mform = new questionnaire_manager_upload_form($CFG->wwwroot . '/blocks/questionnaire_manager/uploadcsv.php',
array('courseid' => $courseid));

$entry = new stdClass;
$entry->contextid = $context->id;
$entry->courseid = $course->id;

$draftitemid = file_get_submitted_draft_itemid('coursecsv');

file_prepare_draft_area($draftitemid, $context->id, 'block_questionnaire_manager', 'coursecsv', $course->id,
array('subdirs' => 0, 'maxbytes' => 10240000, 'maxfiles' => 2));
$entry->coursecsv = $draftitemid;

$mform->set_data($entry);
$result = '';

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', array('id' => $course->id)));

} else if ($data = $mform->get_data()) {
    file_save_draft_area_files($data->coursecsv, $context->id, 'block_questionnaire_manager', 'coursecsv',
        $course->id, array('subdirs' => 0, 'maxbytes' => 10240000, 'maxfiles' => 1));
    redirect(new moodle_url('/course/view.php', array('id' => $course->id)));
}

$PAGE->set_url('/blocks/questionnaire_manager/uploadcsv.php', array('courseid' => $course->id, 'contextid' => $contextid));
$PAGE->set_context($context);
$PAGE->set_title(get_string('upload_csv', 'block_questionnaire_manager'));
$PAGE->set_heading(get_string('upload_csv', 'block_questionnaire_manager'));

$PAGE->set_title(get_string('upload_csv', 'block_questionnaire_manager'));
$PAGE->navbar->add(get_string('uploadcsv', 'block_questionnaire_manager'), '', navigation_node::TYPE_CUSTOM);

echo $OUTPUT->header(get_string('upload_csv', 'block_questionnaire_manager'));

$mform->display();
echo $result;

echo $OUTPUT->single_button(new moodle_url('/course/view.php', array('id' => $course->id)), get_string('back'), '');
echo $OUTPUT->footer();
