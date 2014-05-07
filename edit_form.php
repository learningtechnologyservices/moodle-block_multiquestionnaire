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
 * Form for editing questionnaire_manager block instances.
 *
 * @package   block_questionnaire_manager
 * @copyright 2013 Learning Technology Services, www.lts.ie - Lead Developer: Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_questionnaire_manager_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG, $DB, $COURSE;

        // Fields for editing questionnaire_manager block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $context = context_course::instance($COURSE->id);

        if (has_capability('block/questionnaire_manager:uploadcsv', $context)) {
            $addquestionnaires = $DB->get_records('questionnaire', array('course' => $COURSE->id));
            $questionnaires = array();
            foreach ($addquestionnaires as $aq) {
                $questionnaires[$aq->sid] = $aq->name;
            }

            if (count($questionnaires > 0)) {
                $mform->addElement('select', 'config_sel_questionnaire',
                    get_string('sel_questionnaire', 'block_questionnaire_manager'), $questionnaires);
            }
        }

    }
}
