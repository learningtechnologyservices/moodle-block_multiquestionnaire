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

require_once("../../config.php");

global $CFG, $COURSE, $USER;

require_once($CFG->dirroot.'/mod/questionnaire/lib.php');
require_once($CFG->dirroot.'/mod/questionnaire/questionnaire.class.php');
require_once($CFG->dirroot.'/blocks/questionnaire_manager/reportlib.php');

$instance = optional_param('instance', false, PARAM_INT);
$action = optional_param('action', 'vall', PARAM_ALPHA);
$sid = optional_param('sid', null, PARAM_INT);
$rid = optional_param('rid', false, PARAM_INT);
$type = optional_param('type', '', PARAM_ALPHA);
$byresponse = optional_param('byresponse', false, PARAM_INT);
$currentgroupid = optional_param('currentgroupid', -1, PARAM_INT);
$user = optional_param('user', '', PARAM_INT);

global $DB;

$userid = $USER->id;

switch ($action) {
    case 'vallasort':
        $sort = 'ascending';
        break;
    case 'vallarsort':
        $sort = 'descending';
        break;
    default:
        $sort = 'default';
}

if ($instance === false) {
    if (!empty($SESSION->instance)) {
        $instance = $SESSION->instance;
    } else {
        error(get_string('requiredparameter', 'questionnaire'));
    }
}

if (! $questionnaire = $DB->get_record("questionnaire", array('id' => $instance))) {
    error(get_string('incorrectquestionnaire', 'questionnaire'));
}
if (! $course = $DB->get_record("course", array('id' => $questionnaire->course))) {
    error("get_string('misconfigured', 'questionnaire')");
}
if (! $cm = get_coursemodule_from_instance("questionnaire", $questionnaire->id, $course->id)) {
        error(get_string('incorrectmodule', 'questionnaire'));
}

$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);

if (!has_capability('moodle/grade:viewall', $coursecontext)) {
    redirect (new moodle_url('/course/view.php', array('id' => $course->id)));
}

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

$questionnaire = new questionnaire_custom(0, $questionnaire, $course, $cm);

$questionnaire->canviewallgroups = has_capability('moodle/site:accessallgroups', $context, null, false);

$sid = $questionnaire->survey->id;

$formdata = data_submitted();

$strdeleteallresponses = get_string('deleteallresponses', 'questionnaire');
$strdeleteresp = get_string('deleteresp', 'questionnaire');
$strdownloadcsv = get_string('downloadtext');
$strviewallresponses = get_string('viewallresponses', 'questionnaire');
$strsummary = get_string('summary', 'questionnaire');
$strviewbyresponse = get_string('viewbyresponse', 'questionnaire');
$strquestionnaires = get_string('modulenameplural', 'questionnaire');

$groupmode = groups_get_activity_groupmode($cm, $course);
$questionnairegroups = '';
$groupscount = 0;
$currentsessiongroupid = -1;

require_login($course);
$context = get_context_instance_by_id($instance);

$PAGE->set_url('/blocks/questionnaire_manager/report.php');
$PAGE->set_title(format_string('Questionnaire Manager Report'));
$PAGE->set_heading(format_string('Questionnaire Manager Report'));
// Moodle 2.5.
// $PAGE->set_context($context); .
echo $OUTPUT->header('Questionnaire Report');

switch ($action) {

    case 'vall':
    case 'vallasort':
    case 'vallarsort':

        // Moodle 2.5.
        // $extranav = array(); .
        // $extranav[] = array('name' => get_string('questionnairereport', 'questionnaire'), 'link' => '', 'type' => 'activity'); .
        // $extranav[] = array('name' => $strviewallresponses, 'link' => "", 'type' => 'activity'); .
        // $navigation = build_navigation($extranav, $questionnaire->cm); .
        $PAGE->navbar->add(get_string('questionnairereport', 'questionnaire'));

        if (!empty($questionnaire->survey->theme)) {
            $href = $CFG->wwwroot.'/mod/questionnaire/css/'.$questionnaire->survey->theme;
            echo '<script type="text/javascript">
                //<![CDATA[
                document.write("<link rel=\"stylesheet\" type=\"text/css\" href=\"'.$href.'\">")
                //]]>
                </script>';
        }

        echo ('<br />');
        echo ('<br />');

        echo'<div class = "generalbox">';
        echo (get_string('viewallresponses', 'questionnaire'));

        echo $OUTPUT->help_icon('orderresponses', 'questionnaire');

        $ret = $questionnaire->survey_results(1, 1, '', '', '', '', $uid = false, $currentgroupid, $sort);
        // Moodle 2.5: .
        // $ret = $questionnaire->survey_results(1, 1, '', '', '', $uid=false, $currentgroupid, $sort); .
        echo '</div>';
        echo $OUTPUT->footer($course);
        break;
}
