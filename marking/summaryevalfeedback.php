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
 *
 * @package mod
 * @subpackage emarking
 * @copyright 2017 Hans Jeria <hansjeria@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once ($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/mod/emarking/locallib.php');
global $CFG, $OUTPUT, $PAGE, $DB;
// Obtains basic data from cm id.
list($cm, $emarking, $course, $context) = emarking_get_cm_course_instance();
require_login($course, true);
if (isguestuser()) {
	die();
}
if ($emarking->type != EMARKING_TYPE_ON_SCREEN_MARKING) {
	print_error('You can only have enhanced feedback in a normal emarking type');
}

// Check if user has an editingteacher role.
$issupervisor = has_capability('mod/emarking:supervisegrading', $context);
if (! $issupervisor) {
	print_error("Invalid access!");
}
$url = new moodle_url('/mod/emarking/marking/summaryevalfeedback.php', array(
		'id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->set_pagelayout('incourse');
$PAGE->set_url($url);
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);
echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), 'evalfeedback');

$studentstable = new html_table();
$studentstable->head = array(
		get_string('names', 'mod_emarking'),
		'Tiempo en revisión (Hora:Minutos)',
		'Preguntas contestadas',
		'Ultima edición'
);
$studentstable->size = array(
		'25%',
		'25%',
		'25%',
		'25%'
);
$data = array();
$allstudents = emarking_get_students_for_printing($cm->course);
foreach ($allstudents as $student) { 
	$formresults = 'SELECT
			eval.id,
			eval.complexity,
			eval.relevant,
			eval.personalization,
			eval.lastmodified,
			eval.optionalcomment
			FROM {emarking} AS e INNER JOIN {emarking_draft} AS draft ON (e.id = ? AND e.id = draft.emarkingid)
			INNER JOIN {emarking_evaluatefeedback} AS eval ON (eval.submissionid = draft.id AND userid = ?)';
	if ( !$answersform = $DB->get_record_sql($formresults, array($emarking->id, $student->id)) ) {
		$answersform = "No ha respondido";
		$lasttime = " - ";
	}else {
		$lasttime = date('Y-m-d h:i', $answersform->lastmodified);
		$answersform = "3/3";
	}
	$sessionresults = 'SELECT
			sess.id,
			SUM(sess.endtime - sess.starttime) AS time
			FROM {emarking} AS e INNER JOIN {emarking_draft} AS draft ON (e.id = ? AND e.id = draft.emarkingid)
			INNER JOIN {emarking_session} AS sess ON (sess.draftid = draft.id AND sess.userid = ?)
			GROUP BY sess.userid';
	if(!$sessionsinfo = $DB->get_record_sql($sessionresults, array($emarking->id, $student->id)) ) {
		$time = gmdate('H:i', 0);
	}else {
		$time = gmdate('H:i', $sessionsinfo->time);
	}
	
	$userdata = array(
			$student->lastname.", ".$student->firstname,
			$time,
			$answersform,
			$lasttime
	);
	
	$studentstable->data [] = $userdata;
}
echo html_writer::table($studentstable);
echo $OUTPUT->footer();