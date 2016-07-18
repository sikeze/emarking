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
require_once($CFG->dirroot . "/mod/emarking/lib.php");
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
// URL for adding a new print order.
$params = array(
		"course" => $course->id);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title(get_string("emarking", "mod_emarking"));
$PAGE->set_pagelayout("incourse");
$PAGE->navbar->add(get_string("cycle", "mod_emarking"));

define('EMARKING_TO_PRINT',0);
define('EMARKING_PRINTED',5);
define('EMARKING_STATUS_GRADED',18);
define('EMARKING_STATUS_FINAL_PUBLISHED',45);
define('EMARKING_STATUS_2DAYS_PUBLISHED',50);

echo $OUTPUT->header();

echo html_writer::tag('div','', array('id' => 'summarychart','style' => 'height: 600px;'));
$chartdata= json_encode(emarking_time_progression($course->id),null);
echo emarking_table_creator(null,emarking_time_progression($course->id,1),null);
echo $OUTPUT->footer();
?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
google.load("visualization", "1", {packages: ['corechart', 'bar']});
google.setOnLoadCallback(drawStacked);

function drawStacked() {

      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Nombre Prueba');
      data.addColumn('number', 'Días enviado a imprimir');
      data.addColumn('number', 'Días impreso');
      data.addColumn('number', 'Días digitalizado');
      data.addColumn('number', 'Días en conrreccion');
      data.addColumn('number', 'Días corregido');
      data.addColumn('number', 'Días publicado');
      data.addColumn('number', 'Días en recorreccion');
      data.addColumn('number', 'Días recorregido');
      data.addColumn('number', 'Días en publicacion final');
      data.addColumn('number', 'Días total(comentario)');
      data.addColumn({type: 'string', role: 'annotation'});
	 data.addRows(<?php echo $chartdata; ?>);
	  var view = new google.visualization.DataView(data);
	  view.setColumns([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]);
      var options = {
        title: 'EMarking summary',
        chartArea: {width: '50%'},
        isStacked: true,
        hAxis: {
          title: 'Days',
          viewWindow: {min: 0},
        },
        vAxis: {
          title: 'EMarking'
        }
      };
      var chart = new google.visualization.BarChart(document.getElementById('summarychart'));
      chart.draw(view, options);
    }
</script>
