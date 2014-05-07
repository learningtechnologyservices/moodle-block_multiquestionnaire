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

global $DB;


if (!$module = $DB->get_record("modules", array('name' => 'questionnaire'))) {
    error("This module type doesn't exist");
}

/**
 * Get a list of Questionnaires from the current course
 *
 * @param int $courseid, id of current course
 */

function questionnaire_manager_get_course_questionnaires($courseid) {
    global $module, $DB;
    if (!$module = $DB->get_record("modules", array('name' => 'questionnaire'))) {
        error("This module type doesn't exist");
    }
    $coursequestionnaires = array();
    if ($instances = $DB->get_records_select("course_modules", 'module = '. $module->id .' AND course = '. $courseid)) {
        foreach ($instances as $instance) {
            if ($questionnaireref = $DB->get_record('questionnaire', array('id' => $instance->instance))) {
                $coursequestionnaires[] = $questionnaireref;
            }
        }
    }
    return $coursequestionnaires;
}

/**
 * Creates the file area for the CSV file storage
 * @param object $course
 * @param object $cm
 * @param object $context
 */

function block_questionnaire_manager_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['coursecsv'] = 'coursecsv';
    return $areas;
}

/**
 * Gets the Questionnaire manager CSV file
 *
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param object $filearea
 * @param object $args
 * @param object $forcedownload
 * @param object $options
 */
function block_questionnaire_manager_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload,
array $options=array()) {
    global $DB, $CFG, $USER;

    if ($context->contextlevel != CONTEXT_BLOCK) {
        return false;
    }

    require_login($course);

    if ($filearea === 'coursecsv') {

        $fullpath = "/$context->id/block_questionnaire_manager/$filearea/$course->id/$args[1]";

        $fs = get_file_storage();

        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            send_file_not_found();
        }

        $lifetime = isset($CFG->filelifetime) ? $CFG->filelifetime : 86400;
        send_stored_file($file, $lifetime, 0, $forcedownload, $options);
    }
}

/**
 * Hide or Show all child questionnaires found in the array
 *
 * @param array $questionnairecourses
 * @param int $hide
 */
function questionnaire_manager_hide_questionnaires($questionnairecourses, $hide) {
    global $module, $questionnaire, $message, $DB;

    $count = '';
    foreach ($questionnairecourses as $qcourse) {
        if ($qcourse == 1 || $qcourse == 0) {
            continue;
        }
        $course = $DB->get_record("course", array('id' => $qcourse));
        if ($instances = $DB->get_records_select("course_modules", 'module = '. $module->id .' AND course = ' .$qcourse)) {
            foreach ($instances as $instance) {
                if ($questionnaireref = $DB->get_record('questionnaire', array('id' => $instance->instance))) {
                    if ($questionnaireref->sid == $questionnaire) {
                        $message .= "Changed state for questionnaire in course:  $course->fullname <br>";
                        set_coursemodule_visible($instance->id, $hide);
                    }
                }
            }
        }
        rebuild_course_cache($course->id);
    }

}

/**
 * Copies the current questionnaire manager block into all courses found in CSV
 *
 * @param array $questionnairecourses
 * @param int $blockid
 */
function questionnaire_manager_copy_questionnaire_manager($questionnairecourses, $blockid) {
    global $message, $DB;

    if (!$sourceblock = $DB->get_record("block_instances", array("id" => $blockid))) {
        echo "Could not find source block";
        return false;
    }

    foreach ($questionnairecourses as $qcourse) {
        if ($qcourse == 1) {
            continue;
        }

        $course = $DB->get_record("course", array("id" => $qcourse));
        $coursecontext = context_course::instance($course->id);

        if ($DB->get_records('block_instances', array('blockname' => 'questionnaire_manager',
          'parentcontextid' => $coursecontext->id))) {
            $message .= "$course->fullname has this block already<br>";
            continue;
        }

        $blockrecord = $sourceblock;
        $blockrecord->parentcontextid = $coursecontext->id;
        unset($blockrecord->id);
        $DB->insert_record('block_instances', $blockrecord);
        echo "added block to $course->fullname <br>";
    }
}

/**
 * Check if the questionnaire had already been added in another run
 *
 * @param $course, id of course
 */
function questionnaire_manager_questionnaire_already_added($course) {
    global $module, $questionnaire, $DB;

    if ($instances = $DB->get_records_select("course_modules", 'module = '. $module->id .' AND course = ' .$course)) {
        foreach ($instances as $instance) {
            if ($questionnaireref = $DB->get_record('questionnaire', array('id' => $instance->instance))) {
                if ($questionnaireref->sid == $questionnaire) {
                    return true;
                }
            }
        }
    }
    return false;
}

/**
 * Create a new questionnaire instance in each of the courses found in the CSV file
 *
 * @param array $questionnairecourses
 */
function questionnaire_manager_duplicate_questionnaires($questionnairecourses) {
    global $module, $questionnaire, $questionnairerecord, $message, $DB;

    $coursequestionnaire = $DB->get_record('questionnaire',
    array('course' => $questionnairerecord->owner, 'sid' => $questionnairerecord->id));

    if (empty($coursequestionnaire)) {
        print_error('course questionnaire not found');
    }
    foreach ($questionnairecourses as $qcourse) {
        if ($qcourse == 1) {
            continue;
        }

        $course = $DB->get_record("course", array("id" => $qcourse));

        if (questionnaire_manager_questionnaire_already_added($qcourse)) {
            $message .= get_string('isadded', 'block_questionnaire_manager', $course->fullname);
            continue;
        }

        $message .= "Adding child questionnaire to course: $course->fullname<br> ";

        $fromform = new object();
        $fromform->name = $questionnairerecord->title;
        $fromform->summary = $questionnairerecord->title;
        $fromform->intro = $coursequestionnaire->intro;
        $fromform->introformat = 1;
        $fromform->opendate = $coursequestionnaire->opendate;
        $fromform->closedate = $coursequestionnaire->closedate;
        if ($coursequestionnaire->closedate && $coursequestionnaire->opendate) {
            $fromform->useopendate = 1;
            $fromform->useclosedate = 1;
        }
        $fromform->qtype = $coursequestionnaire->qtype;
        $fromform->cannotchangerespondenttype = 0;
        $fromform->respondenttype = 'anonymous';
        $fromform->resp_view = 0;
        $fromform->resume = 0;
        $fromform->grade = '';
        $fromform->create = 'public-' . $questionnaire;
        $fromform->groupmode = 0;
        $fromform->visible = 0;
        $fromform->cmidnumber = '';
        $fromform->gradecat = 7;
        $fromform->course = $qcourse;
        $fromform->section = 0;
        $fromform->module = $module->id;
        $fromform->modulename = $module->name;
        $fromform->add = 'questionnaire';
        $fromform->update = 0;
        $fromform->return = 0;
        $fromform->submitbutton2 = 'Save and return to course';
        $fromform->groupingid = 0;
        $fromform->groupmembersonly = 0;
        $fromform->sid = $questionnaire;
        $fromform->timemodified = time();
        $fromform->navigate = 1;
        $fromform->added = time();

        if (!empty($course->groupmodeforce) or !isset($fromform->groupmode)) {
            $fromform->groupmode = 0;
        }

        if (!course_allowed_module($course, $fromform->modulename)) {
            error("This module ($fromform->modulename) has been disabled for this particular course");
        }
        $addinstancefunction = $fromform->modulename."_add_instance";
        $returnfromfunc = $addinstancefunction($fromform);
        if (!$returnfromfunc) {
            error("Could not add a new instance of $fromform->modulename", "view.php?id=$course->id");
        }
        if (is_string($returnfromfunc)) {
            error($returnfromfunc, "view.php?id=$course->id");
        }

        $fromform->instance = $returnfromfunc;

        if (! $fromform->coursemodule = add_course_module($fromform) ) {
            error("Could not add a new course module");
        }
        if (! $sectionid = course_add_cm_to_section($fromform->course, $fromform->coursemodule, $fromform->section) ) {
            error("Could not add the new course module to that section");
        }

        if (! $DB->set_field('course_modules', 'section', $sectionid, array('id' => $fromform->coursemodule))) {
            error("Could not update the course module with the correct section");
        }

        set_coursemodule_visible($fromform->coursemodule, $fromform->visible);

        if (isset($fromform->cmidnumber)) {
            set_coursemodule_idnumber($fromform->coursemodule, $fromform->cmidnumber);
        }

        add_to_log($course->id, "course", "add mod",
                       "../mod/$fromform->modulename/view.php?id=$fromform->coursemodule",
                       "$fromform->modulename $fromform->instance");
        add_to_log($course->id, $fromform->modulename, "add",
                       "view.php?id=$fromform->coursemodule",
                       "$fromform->instance", $fromform->coursemodule);
    }
}


function questionnaire_manager_remove_questionnaires($questionnairecourses) {
    // Delete disabled for now.
    return true;
    global $CFG, $module, $questionnaire, $DB, $message;

    foreach ($questionnairecourses as $qcourse) {
        if ($qcourse == 1) {
            continue;
        }
        $course = $DB->get_record('course', array('id' => $qcourse));

        if ($instances = $DB->get_records_select("course_modules", 'module = '. $module->id .' AND course = ' .$qcourse)) {
            foreach ($instances as $instance) {
                if ($questionnaireref = $DB->get_record('questionnaire', array('id' => $instance->instance))) {
                    if ($questionnaireref->sid == $questionnaire) {
                        if (delete_course_module($instance->id) && questionnaire_delete_instance($questionnaireref->id)) {
                            $message .= get_string('deleted', 'block_questionnaire_manager', $course->fullname);
                            add_to_log($course->id, $questionnaireref->name, "deleted",
                              "", $questionnaireref->id, $instance->instance);
                        }
                    }
                }
            }
        }
    }
}
