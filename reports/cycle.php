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
*
* It can be reached from a block within a category or from an EMarking
* course module
*
* @package mod
* @subpackage emarking
* @copyright 2016 Benjamin Espinosa (beespinosa94@gmail.com)
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/config.php");
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/forms/cycle_form.php');

global $DB, $USER, $CFG, $OUTPUT;

// Course id, if the user comes from a course.
$courseid = required_param("course", PARAM_INT);
$emarkingid = optional_param("eid", -1, PARAM_INT);
$selectedcategory = optional_param("selectedcategory", "NULL", PARAM_TEXT);
$selectedcourse = optional_param("selectedcourse", "NULL", PARAM_TEXT);
$selectedsection = optional_param("selectedsection", -1, PARAM_INT);
$currenttab = optional_param("currenttab", 0, PARAM_INT);

// First check that the user is logged in.
require_login();

if (isguestuser()) {
	die();
}

// Validate that the parameter corresponds to a course.
if (! $course = $DB->get_record("course", array(
		"id" => $courseid))) {
		print_error(get_string("invalidcourseid", "mod_emarking"));
}

// Both contexts, from course and category, for permissions later.
$context = context_course::instance($course->id);

// URL for current page.
$url = new moodle_url("/mod/emarking/reports/cycle.php", array(
		"course" => $course->id, "emarking" => $emarkingid));

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title(get_string("emarking", "mod_emarking"));
$PAGE->set_pagelayout("incourse");
$PAGE->navbar->add(get_string("cycle", "mod_emarking"));

$formparameters = array($USER->id, $courseid);
$addform = new cycle_form(null, $formparameters);

	echo $OUTPUT->header();

	$out = html_writer::div('<h2>'.get_string('filters', 'mod_emarking').'</h2>');
	echo $out;

	$addform->display();
	
	$datas = $addform->get_data();
	
	if($datas || $selectedcategory != "NULL" && $selectedcourse != "NULL" && $selectedsection > -1){
		if($datas){
			$selectedcourse = $datas->courses;
			$selectedsection = $datas->section;
			$selectedcategory = $datas->category;
		}
		$emarkingtabs = emarking_cycle_tabs($selectedcourse, $selectedsection, $selectedcategory, $course);
		echo $OUTPUT->tabtree($emarkingtabs, $currenttab);

	}



	
	echo $OUTPUT->footer();
	
	
	
	
	