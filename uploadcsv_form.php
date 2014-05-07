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

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class questionnaire_manager_upload_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', null, get_string('csv', 'block_questionnaire_manager'));
        $mform->addElement('hidden', 'courseid', '');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'contextid', '');
        $mform->setType('contextid', PARAM_INT);

        $mform->addElement('filemanager', 'coursecsv', '', null, null);

        $draftitemid = file_get_submitted_draft_itemid('coursecsv');

        $this->add_action_buttons(true, get_string('savechanges'));
    }
}
