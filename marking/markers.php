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
 * Page to send a new print order
 *
 * @package    mod
 * @subpackage emarking
 * @copyright  2014 Jorge Villalón
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once (dirname(dirname(dirname(dirname(__FILE__ )))) . '/config.php');
require_once ($CFG->dirroot . "/mod/emarking/locallib.php");
require_once ($CFG->dirroot . "/grade/grading/form/rubric/renderer.php");
require_once ("forms/markers_form.php");

global $DB, $USER;

// Obtain parameter from URL
$cmid = required_param ( 'id', PARAM_INT );

if(!$cm = get_coursemodule_from_id('emarking', $cmid)) {
	print_error ( get_string('invalidid','mod_emarking' ) . " id: $cmid" );
}

if(!$emarking = $DB->get_record('emarking', array('id'=>$cm->instance))) {
	print_error ( get_string('invalidid','mod_emarking' ) . " id: $cmid" );
}

// Validate that the parameter corresponds to a course
if (! $course = $DB->get_record ( 'course', array ('id' => $emarking->course))) {
	print_error ( get_string('invalidcourseid','mod_emarking' ) . " id: $courseid" );
}

$context = context_module::instance ( $cm->id );

$url = new moodle_url('/mod/emarking/marking/markers.php',array('id'=>$cmid));

// First check that the user is logged in
require_login($course->id);

if (isguestuser ()) {
	die ();
}

$PAGE->set_context ( $context );
$PAGE->set_course($course);
$PAGE->set_cm($cm);
$PAGE->set_url ( $url );
$PAGE->set_pagelayout ( 'incourse' );
$PAGE->set_title(get_string('emarking', 'mod_emarking'));
$PAGE->navbar->add(get_string('markers','mod_emarking'));

// Verify capability for security issues
if (! has_capability ( 'mod/emarking:assignmarkers', $context )) {
	$item = array (
			'context' => context_module::instance ( $cm->id ),
			'objectid' => $cm->id,
	);
	// Add to Moodle log so some auditing can be done
	\mod_emarking\event\markers_assigned::create ( $item )->trigger ();
	print_error ( get_string('invalidaccess','mod_emarking' ) );
}

echo $OUTPUT->header();
echo $OUTPUT->heading($emarking->name);

echo $OUTPUT->tabtree(emarking_tabs($context, $cm, $emarking), "markers" );

// Get rubric instance
list($gradingmanager, $gradingmethod) = emarking_validate_rubric($context);

$markercriteria = $DB->get_recordset_sql("
    SELECT mc.id, u.firstname, u.lastname, c.description, mc.block 
    FROM {emarking_marker_criterion} as mc
    INNER JOIN {user} as u ON (mc.emarking = :emarking AND mc.marker = u.id)
    INNER JOIN {gradingform_rubric_criteria} as c ON (c.id = mc.criterion)
    ORDER BY mc.block ASC, mc.criterion ASC", 
    array("emarking"=>$emarking->id));

if (count($markercriteria) == 0) {
    echo $OUTPUT->notification("No hay asignaciones de correctores a preguntas", "notifyproblem");
} else {
    $data = array();
    foreach($markercriteria as $d) {
        $data[] = array($d->description, $d->firstname . " " . $d->lastname, $d->block);
    }
    $table = new html_table();
    $table->head = array("Pregunta", "Corrector", "Bloque");
    $table->data = $data;
    echo html_writer::table($table);
}

echo $OUTPUT->single_button(new moodle_url("addmarkers.php", array("id"=>$cm->id, "action"=>"addmarker")), "Agregar", "GET");

$pagecriteria = $DB->get_recordset_sql("
    SELECT mc.id, mc.page, c.description, mc.block
    FROM {emarking_page_criterion} as mc
    INNER JOIN {gradingform_rubric_criteria} as c ON (mc.emarking = :emarking AND c.id = mc.criterion)
    ORDER BY mc.block ASC, mc.criterion ASC",
    array("emarking"=>$emarking->id));

if (count($pagecriteria) == 0) {
    echo $OUTPUT->notification("No hay asignaciones de páginas a preguntas", "notifyproblem");
} else {
    $data = array();
    foreach($pagecriteria as $d) {
        $data[] = array($d->description, $d->page, $d->block);
    }
    $table = new html_table();
    $table->head = array("Pregunta", "Página", "Bloque");
    $table->data = $data;
    echo html_writer::table($table);
}

echo $OUTPUT->single_button(new moodle_url("addmarkers.php", array("id"=>$cm->id, "action"=>"addpages")), "Agregar", "GET");

echo $OUTPUT->footer();
