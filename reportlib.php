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

class questionnaire_custom extends questionnaire {

    public function survey_results($precision = 1, $showtotals = 1, $qid = '', $cids = '', $rid = '',
    $guicross='', $uid=false, $groupid='', $sort='') {

        // MDL 2.5 .
        // public function survey_results($precision = 1, $showtotals = 1, $qid = '', $cids = '', $rid = '', .
        // $uid=false, $groupid='', $sort='') { .
        // public function survey_results($precision = 1, $showtotals = 1, $qid = '', $cids = '', $rid = '',
        // $uid=false, $groupid='', $sort='') { .

        global $SESSION, $DB, $COURSE;

        if (empty($precision)) {
            $precision  = 1;
        }
        if ($showtotals === '') {
            $showtotals = 1;
        }

        if (is_int($cids)) {
            $cids = array($cids);
        }
        if (is_string($cids)) {
            $cids = preg_split("/ /", $cids);
        }

        $haschoices = array();
        $responsetable = array();
        if (!($types = $DB->get_records('questionnaire_question_type', array(), 'typeid', 'typeid, has_choices, response_table'))) {
            $errmsg = sprintf('%s [ %s: question_type ]',
            get_string('errortable', 'questionnaire'), 'Table');
            return($errmsg);
        }
        foreach ($types as $type) {
            $haschoices[$type->typeid] = $type->has_choices;
            $responsetable[$type->typeid] = $type->response_table;
        }

        if (empty($this->survey)) {
            $errmsg = get_string('erroropening', 'questionnaire') ." [ ID:${sid} R:";
            return($errmsg);
        }

        if (empty($this->questions)) {
            $errmsg = get_string('erroropening', 'questionnaire') .' '. 'No questions found.';
            return($errmsg);
        }

        // Find total number of survey responses and relevant response ID's.
        if (!empty($rid)) {
            $rids = $rid;
            if (is_array($rids)) {
                $navbar = false;
            } else {
                $navbar = true;
            }
            $total = 1;
        } else {
            $navbar = false;
            $sql = "";

            $sql = "SELECT R.id as id, Q.id as qinstance, Q.course qcourse, A.id as attemptid,
                            A.qid, A.userid,  R.survey_id, R.username
                              FROM {questionnaire_response} R,
                                  {questionnaire_attempts} A,
                                   {questionnaire} Q
                             WHERE R.survey_id='{$this->survey->id}' AND
                                   R.complete='y' AND
                                   A.rid = R.id AND
                                   A.userid = R.username AND
                                   A.qid = ".$this->id." AND
                                   Q.id = A.qid AND
                                   Q.course = ".$COURSE->id."
                             ORDER BY R.id";

            if (!($rows = $DB->get_records_sql($sql))) {
                echo (get_string('noresponses', 'questionnaire'));
                return;
            }

            switch ($groupid) {
                case -2:    // Remove non group members from list of all participants.
                    foreach ($rows as $row => $key) {
                        if (!groups_has_membership($this->cm, $key->userid)) {
                            unset($rows[$row]);
                        }
                    }
                    break;
                case -3:    // Remove group members from list of all participants.
                    foreach ($rows as $row => $key) {
                        if (groups_has_membership($this->cm, $key->userid)) {
                            unset($rows[$row]);
                        }
                    }
                    break;
            }

            $total = count($rows);
            echo (' '.get_string('responses', 'questionnaire').": <strong>$total</strong>");
            if (empty($rows)) {
                $errmsg = get_string('erroropening', 'questionnaire') .' '. get_string('noresponsedata', 'questionnaire');
                return($errmsg);
            }

            $rids = array();
            foreach ($rows as $row) {
                array_push($rids, $row->id);
            }
        }

        if ($navbar) {
            // Show response navigation bar.
            $this->survey_results_navbar($rid);
        }

        echo '<h3 class="surveyTitle">'.s($this->survey->title).'</h3>';
        if ($this->survey->subtitle) {
            echo('<h4>'.$this->survey->subtitle.'</h4>');
        }
        if ($this->survey->info) {
            $infotext = file_rewrite_pluginfile_urls($this->survey->info, 'pluginfile.php',
            $this->context->id, 'mod_questionnaire', 'info', $this->survey->id);
            echo '<div class="addInfo">'.format_text($infotext, FORMAT_HTML).'</div>';
        }

        $qnum = 0;

        foreach ($this->questions as $question) {
            if ($question->type_id == QUESPAGEBREAK) {
                continue;
            }
            echo html_writer::start_tag('div', array('class' => 'qn-container'));
            if ($question->type_id != QUESSECTIONTEXT) {
                $qnum++;
                echo html_writer::start_tag('div', array('class' => 'qn-info'));
                if ($question->type_id != QUESSECTIONTEXT) {
                    echo html_writer::tag('h2', $qnum, array('class' => 'qn-number'));
                }
                echo html_writer::end_tag('div'); // End qn-info.
            }
            echo html_writer::start_tag('div', array('class' => 'qn-content'));
            echo html_writer::start_tag('div', array('class' => 'qn-question'));
            echo format_text(file_rewrite_pluginfile_urls($question->content, 'pluginfile.php',
            $question->context->id, 'mod_questionnaire', 'question', $question->id), FORMAT_HTML);
            echo html_writer::end_tag('div'); // End qn-question.
            $question->display_results($rids, '', $sort);

            // Mdl 2.5 .
            // $question->display_results($rids, $sort); .
            echo html_writer::end_tag('div'); // End qn-content.
            echo html_writer::end_tag('div'); // End qn-container.
        }

        return;
    }
}
