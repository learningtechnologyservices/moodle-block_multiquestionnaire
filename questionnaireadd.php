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
 * @package    block_multiquestionnaire
 * @copyright  2013 Learning Technology Services, www.lts.ie - Lead Developer: Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once($CFG->dirroot . "/mod/questionnaire/lib.php");
require_once($CFG->dirroot . "/blocks/multiquestionnaire/lib.php");
require_once($CFG->dirroot . "/course/lib.php");
require_once($CFG->libdir.'/csvlib.class.php');

if (!is_siteadmin($USER->id)) {
    echo 'Sorry, you are not allowed to run this script';
    exit(0);
}

global $DB;

$questionnairecourses = array();
$courseid = required_param('courseid', PARAM_INT);
$contextid = required_param('contextid', PARAM_INT);
$action = required_param('action', PARAM_TEXT);
$questionnaire = optional_param('q', 0, PARAM_INT);
$blockid = optional_param('b', 0, PARAM_INT);
$questionnairerecord = $DB->get_record("questionnaire_survey", array('id' => $questionnaire));

$context = get_context_instance_by_id($contextid);
require_capability('block/multiquestionnaire:uploadcsv', $context);
$course = $DB->get_record('course', array('id' => $courseid));

$fs = get_file_storage();
$files = $fs->get_area_files($contextid, 'block_multiquestionnaire', 'coursecsv', $courseid);

$filecontent = false;
foreach ($files as $file) {
    $url = "{$CFG->wwwroot}/pluginfile.php/{$file->get_contextid()}/block_multiquestionnaire/coursecsv/";
    $filename = $file->get_filename();

    if ($filename == ".") {
        continue;
    }
    $filecontent = $contents = $file->get_content();
}

require_login($course);

$PAGE->set_url('/blocks/multiquestionnaire/questionnaireadd.php', array('id' => $courseid));
$PAGE->set_title(format_string('Questionnaire Manager'));
$PAGE->set_heading(format_string('Questionnaire Manager'));
$PAGE->set_context($context);

echo $OUTPUT->header();

$message = "Results for questionnaire manager action \"$action:\" <br>";

$iid = csv_import_reader::get_new_iid('questionnairemanager');
$cir = new csv_import_reader($iid, 'questionnairemanager');
$readcount = $cir->load_csv_content($filecontent, mb_detect_encoding($filecontent), ',');

$filecontentarray = array();

if ($readcount) {
    $cir->init();
    $columns = $cir->get_columns();
    while ($line = $cir->next()) {
        $filecontentarray[] = $line;
    }
    $cir->close();
    $cir->cleanup();
    $usefield = '';
    $questionnairecourses = array();

    $hvalue = $columns[0];
    if ($hvalue == 'courseid') {
        $usefield = $hvalue;
    } else if ($hvalue == 'courseid') {
        $usefield = $hvalue;
    } else if ($hvalue == 'shortname') {
        $usefield = $hvalue;
    } else if ($hvalue == 'fullname') {
        $usefield = $hvalue;
    }

    if (empty($usefield)) {
        echo get_string('novalidfields', 'block_multiquestionnaire');
        echo html_writer::link(new moodle_url('/course/view.php', array('id' => $courseid)), get_string('back'));
        echo $OUTPUT->footer();
        exit(0);
    }
    if ($usefield == 'courseid') {
        foreach ($filecontentarray as $course) {
            $questionnairecourses[] = $data[$course[0]];
        }
    } else {
        array_shift($filecontentarray);
        foreach ($filecontentarray as $course) {
            if ($qcourse = $DB->get_record('course', array($usefield => $course[0]))) {
                $questionnairecourses[] = $qcourse->id;
            } else {
                echo "Course with " . $usefield . " " . $course[0] . " not found<br>";
            }
        }

    }

} else {
    $message .= get_string('nocsv', 'block_multiquestionnaire');
}

switch ($action) {
    case 'duplicate':
        multiquestionnaire_duplicate_questionnaires($questionnairecourses);
        break;
    case 'hide':
        multiquestionnaire_hide_questionnaires($questionnairecourses, 0);
        break;
    case 'show':
        multiquestionnaire_hide_questionnaires($questionnairecourses, 1);
        break;
    case 'copyblock':
        multiquestionnaire_copy_multiquestionnaire($questionnairecourses, $blockid);
        break;
    case 'delete':
        multiquestionnaire_remove_questionnaires($questionnairecourses);
        break;
}

if (isset($course->id)) {
    rebuild_course_cache($course->id);
}
echo $message;

echo html_writer::link(new moodle_url('/course/view.php', array('id' => $courseid)), get_string('back'));

echo $OUTPUT->footer();
