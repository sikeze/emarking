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

global $DB, $USER, $CFG, $OUTPUT, $COURSE;

// Course id, if the user comes from a course.
$courseid = required_param("course", PARAM_INT);
$emarkingid = optional_param("emarking", -1, PARAM_INT);
$selectedcategory = optional_param("selectedcategory", "NULL", PARAM_TEXT);
$selectedcourse = optional_param("selectedcourse", "NULL", PARAM_TEXT);
$selectedsection = optional_param("selectedsection", -1, PARAM_INT);
$currenttab = optional_param("currenttab", 0, PARAM_INT);

// First check that the user is logged in.
require_login();

if (isguestuser()) {
	die();
}
if (! has_capability('mod/emarking:viewemarkingcycle', $context)) {
	print_error(get_string("notallowed", "mod_emarking"));
}
// Validate that the parameter corresponds to a course.
if (! $course = $DB->get_record("course", array(
		"id" => $courseid))) {
		print_error(get_string("invalidcourseid", "mod_emarking"));
}
if (!$isemarking = $DB->get_records("emarking", array(
		"course" => $courseid))) {
		print_error(get_string("invalidemarkingcourse", "mod_emarking"));
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
	// data of the current course
	$currentcategory = $DB-> get_record('course_categories', array('id' => $COURSE->category), 'name');
	$curretcourse = explode('-', $COURSE->shortname);
	
	if($selectedcategory == "NULL" && $selectedcourse == "NULL" && $selectedsection == -1){
		$selectedcategory = $currentcategory->name;
		$selectedcourse = $curretcourse[2];
		$selectedsection = $curretcourse[3];
	}

	echo html_writer::div('<h2>'.get_string('filters', 'mod_emarking').'</h2>');;

	$addform->display();
	
	if($datas = $addform->get_data()){
			$selectedcourse = $datas->courses;
			$selectedsection = $datas->section;
			$selectedcategory = $datas->category;
	}
	$emarkingtabs = emarking_cycle_tabs($selectedcourse, $selectedsection, $selectedcategory, $course);
	
	echo $OUTPUT->tabtree($emarkingtabs, $currenttab);
	
	$summarychartdata = json_encode([[0,0]]);
  	if($currenttab == 0){
  		define('EMARKING_TO_PRINT',0);
  		define('EMARKING_PRINTED',5);
  		define('EMARKING_STATUS_GRADED',18);
  		define('EMARKING_STATUS_FINAL_PUBLISHED',45);
  		define('EMARKING_STATUS_2DAYS_PUBLISHED',50);
  		
  		echo html_writer::tag('div','', array('id' => 'summarychart','style' => 'height: 600px;'));
  		$summarychartdata= json_encode(emarking_time_progression($course->id),null);
  		echo html_writer::start_tag('div');
  		echo emarking_table_creator(null,emarking_time_progression($course->id,1),null);
   	}else{
   		emarking_markers_corrections($emarkingid);
   		echo html_writer::div('','', array('id' => 'ganttchart','style' => 'height: 400px;'));
   		echo html_writer::div('','', array('id' => 'areachart','style' => 'height: 600px;'));
   	}
echo $OUTPUT->footer();
  		
?>
  		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  		<script type="text/javascript">
  		google.charts.load('current', {'packages':['corechart', 'gantt']});
  		if (<?php echo $currenttab;?> == 0){
  		google.charts.setOnLoadCallback(drawStacked);
  		}
  		function drawStacked() {
  		
  		      var data = new google.visualization.DataTable();
  		      data.addColumn('string', '<?php echo get_string("emarkingname", "mod_emarking"); ?>');
  		      data.addColumn('number', '<?php echo get_string("dayssenttoprint", "mod_emarking"); ?>');
  		      data.addColumn('number', '<?php echo get_string("printeddays", "mod_emarking"); ?>');
  		      data.addColumn('number', '<?php echo get_string("digitalizeddays", "mod_emarking"); ?>');
  		      data.addColumn('number', '<?php echo get_string("daysincorrection", "mod_emarking"); ?>');
  		      data.addColumn('number', '<?php echo get_string("gradeddays", "mod_emarking"); ?>');
  		      data.addColumn('number', '<?php echo get_string("publisheddays", "mod_emarking"); ?>');
  		      data.addColumn('number', '<?php echo get_string("daysinregrading", "mod_emarking"); ?>');
  		      data.addColumn('number', '<?php echo get_string("regradeddays", "mod_emarking"); ?>');
  		      data.addColumn('number', '<?php echo get_string("finalpublicationdays", "mod_emarking"); ?>');
  		      data.addColumn('number', '<?php echo get_string("totaldays", "mod_emarking"); ?>');
  		      data.addColumn({type: 'string', role: 'annotation'});
  			  data.addRows([["Mi primera prueba :3",0,3,1,2,6,2,6,7,-18,0,"9 Days"],["123",3,0,0,0,0,0,0,0,0,0,"3 Days"]]);
  			  var view = new google.visualization.DataView(data);
  			  view.setColumns([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]);
  		      var options = {
  		        title: '<?php echo get_string("emarkingsummary", "mod_emarking");?>',
  		        chartArea: {width: '50%'},
  		        isStacked: true,
  		      	bar: {groupWidth: "50%"},
  		        hAxis: {
  		          title: '<?php echo get_string("days", "mod_emarking");?>',
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
  		<script>
  		if(<?php echo $currenttab;?> != 0){
  		google.charts.setOnLoadCallback(drawganttChart);
  		}
  		
    function drawganttChart() {

    	var dataarray = <?php echo  json_encode(emarking_gantt_data($emarkingid));?>;
    	var arraylength = dataarray.length;
    	var startdate = 0;
		var enddate = 0;
    	for (var i = 0; i < arraylength; i++) {
    		startdate = dataarray[i][3]
    		enddate = dataarray[i][4]
			dataarray[i][3] = new Date(startdate);
			dataarray[i][4] = new Date(enddate);
    	}
    	
      var data = new google.visualization.DataTable();
      data.addColumn('string', 'Task ID');
      data.addColumn('string', 'Task Name');
      data.addColumn('string', 'Resource');
      data.addColumn('date', 'Start Date');
      data.addColumn('date', 'End Date');
      data.addColumn('number', 'Duration');
      data.addColumn('number', 'Percent Complete');
      data.addColumn('string', 'Dependencies');

      data.addRows(dataarray);

      var options = {
        height: 400,
        gantt: {
          trackHeight: 30
        }
      };

      var chart = new google.visualization.Gantt(document.getElementById('ganttchart'));

      chart.draw(data, options);
    }
    </script>
    <script>
    if(<?php echo $currenttab;?> != 0){
    google.charts.setOnLoadCallback(drawareaChart);
    }
      function drawareaChart() {
 
        var data = google.visualization.arrayToDataTable(<?php echo  json_encode(emarking_area_chart($emarkingid));?>);

        var options = {
          title: 'Company Performance',
          isStacked: true,
          hAxis: {title: 'Year',  titleTextStyle: {color: '#333'}},
          vAxis: {minValue: 0}
        };

        var areachart = new google.visualization.AreaChart(document.getElementById('areachart'));
        areachart.draw(data, options);
      }
    </script>
