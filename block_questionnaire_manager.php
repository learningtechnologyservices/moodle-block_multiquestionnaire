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
 * @copyright  2013 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_questionnaire_manager extends block_base {

    public function init() {
        $this->title = get_string('blockname', 'block_questionnaire_manager');
    }

    public function applicable_formats() {
        return array('all' => true);
    }

    public function specialization() {
        $this->title = get_string('blockname', 'block_questionnaire_manager');
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function get_content() {
        global $CFG, $COURSE, $USER, $OUTPUT, $DB;

        $course = $this->page->course;
        $context = context_course::instance($course->id);

        if (!has_capability('moodle/grade:viewall', $context)) {
            return false;
        }

        require_once($CFG->dirroot . "/blocks/questionnaire_manager/lib.php");

        if ($this->content !== null) {
            return $this->content;
        }

        if (isset($this->config)) {
            $config = $this->config;
        } else {
            $config = get_config('blocks/questionnaire_manager');
        }

        $this->content = new stdClass;

        $questionnaire = false;
        $owner = false;
        if (empty($config->sel_questionnaire)) {
            $this->content->text = 'First select a parent questionnaire in this blocks settings';
            return $this->content;
        } else {
            $questionnaire = $DB->get_record('questionnaire', array('sid' => $config->sel_questionnaire, 'course' => $course->id));
        }

        if ($questionnaire) {
            $owner = $DB->get_record('questionnaire_survey', array('id' => $config->sel_questionnaire, 'owner' => $course->id));
        }

        if (has_capability('block/questionnaire_manager:uploadcsv', $context) && $owner) {
            $course = $this->page->course;
            $fs = get_file_storage();
            $files = $fs->get_area_files($this->context->id, 'block_questionnaire_manager', 'coursecsv', $course->id);

            $fileurl = false;
            foreach ($files as $file) {
                $url = "{$CFG->wwwroot}/pluginfile.php/{$file->get_contextid()}/block_questionnaire_manager/coursecsv/";
                $filename = $file->get_filename();

                if ($filename == ".") {
                    continue;
                }

                $this->content->text .= get_string('file'). '<br>';
                $fileurl = $url.$file->get_filepath().$file->get_itemid().'/'.$filename;
                $this->content->text .= html_writer::link($fileurl, $filename) . '<br>';
            }

            // Link to upload csv.
            $url = new moodle_url('/blocks/questionnaire_manager/uploadcsv.php',
                array('courseid' => $course->id, 'contextid' => $this->context->id));
            $this->content->text .= html_writer::link($url, get_string('uploadcsv', 'block_questionnaire_manager') . '<br>');
            $this->content->text .= '<br>';

            // Link to chosen Questionnaire.
            $this->content->text .= get_string('selected', 'block_questionnaire_manager') . '<br>';
            $url = new moodle_url('/mod/questionnaire/view.php', array('a' => $config->sel_questionnaire));
            $this->content->text .= html_writer::link($url, $questionnaire->name);
            $this->content->text .= '<br>';

            // Admin actions.
            if ($fileurl) {
                $this->content->text .= 'Admin options<br>';
                $actions = array('duplicate', 'hide', 'show', 'copyblock');
                foreach ($actions as $action) {
                    $link = new moodle_url('/blocks/questionnaire_manager/questionnaireadd.php',
                        array(
                              'courseid' => $course->id,
                              'action' => $action,
                              'q' => $config->sel_questionnaire,
                              'contextid' => $this->context->id,
                              'b' => $this->instance->id
                        ));
                    $actionlink = html_writer::link($link, get_string($action, 'block_questionnaire_manager'));
                    $this->content->text .= html_writer::tag('div', $actionlink);
                }
            }
        }
        $this->content->text .= '<br>';

        // Link to custom Questionnaire Report for teachers.
        if (has_capability('moodle/grade:viewall', $context)) {
            $questionnaireinstances = questionnaire_manager_get_course_questionnaires($COURSE->id);
            $this->content->text .= get_string('show_responses', 'block_questionnaire_manager') . '<br>';
            foreach ($questionnaireinstances as $qi) {
                $reporturl = new moodle_url('/blocks/questionnaire_manager/report.php',
                    array('instance' => $qi->id, 'sid' => $config->sel_questionnaire, 'action' => 'vall'));
                $this->content->text .= html_writer::link($reporturl, $qi->name . '<br>');
            }
        }

        $this->content->footer = '';

        unset($filteropt);

        return $this->content;
    }
}
