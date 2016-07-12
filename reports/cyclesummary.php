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
 * This page shows a list of exams sent for printing.
* It can be reached from a block within a category or from an EMarking
* course module
*
* @package mod
* @subpackage emarking
* @copyright 2012-2015 Jorge Villalon <jorge.villalon@uai.cl>
* @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/config.php");
require_once($CFG->dirroot . "/mod/emarking/locallib.php");
require_once($CFG->dirroot . "/mod/emarking/reports/locallib.php");
global $DB, $USER, $CFG, $OUTPUT;
// Course id, if the user comes from a course.
$courseid = required_param("course", PARAM_INT);
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
$url = new moodle_url("/mod/emarking/reports/cyclesummary.php", array(
		"course" => $course->id));

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string("emarking", "mod_emarking"));
$PAGE->navbar->add(get_string("process", "mod_emarking"));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

echo $OUTPUT->header();

echo html_writer::tag('div', '', array('id' => 'chart_div','style' => 'width:100%; height: 400px;'));

echo $OUTPUT->footer();
?>
<html>
<head>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
google.load("visualization", "1",  {packages: ['corechart', 'bar']});
google.setOnLoadCallback(drawStacked);

function drawStacked() {
      var data = google.visualization.arrayToDataTable([
        ['City', '2010 Population', '2000 Population'],
        ['New York City, NY', 8175000, 8008000],
        ['Los Angeles, CA', 3792000, 3694000],
        ['Chicago, IL', 2695000, 2896000],
        ['Houston, TX', 2099000, 1953000],
        ['Philadelphia, PA', 1526000, 1517000]
      ]);

      var options = {
        title: 'Resumen pruebas XXXX',
        chartArea: {width: '50%'},
        isStacked: true,
        hAxis: {
          title: 'Días',
          minValue: 0,
        },
        vAxis: {
          title: 'Pruebas'
        }
      };
      var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
      chart.draw(data, options);
    }
    </script>
</head>
</html>