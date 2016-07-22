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
function emarking_get_google_chart($divid, array $labels, array $data, $title, $xtitle = null, $ytitle = null) {
    // DIV for displaying.
    $html = '<div id="' . $divid . '" style="width: 100%; height: 500px;"></div>';
    // Headers.
    $labelsjs = "['" . implode("', '", $labels) . "']";
    // Data JS.
    $datajs = "";
    for ($i = 0; $i < count($data); $i ++) {
        $datajs .= "[";
        for ($j = 0; $j < count($data [$i]); $j ++) {
            $datacell = $data [$i] [$j];
            if ($j == 0) {
                $datacell = "'" . $datacell . "'";
            }
            if ($j < count($data [$i]) - 1) {
                $datacell = $datacell . ",";
            }
            $datajs .= $datacell;
        }
        $datajs .= "],";
    }
    // The required JS to display the chart.
    $js = "
        google.setOnLoadCallback(drawChart$divid);
        // Chart function for $divid.
        function drawChart$divid() {
        var data = google.visualization.arrayToDataTable([
            $labelsjs,
            $datajs
            ]);
        var options = {
                        animation: {duration: 500},
                        title: '$title',
                        hAxis: {title: '$xtitle', titleTextStyle: {color: 'black'}, format:'#'},
                        vAxis: {title: '$ytitle', titleTextStyle: {color: 'black'}, format:'#'},
                        legend: 'top',
                        vAxes: {
                                0: {
                                    gridlines: {color: '#ddd'},
                                    format:'#'
                                   },
                                1: {
                                    gridlines: {color: '#ddd'},
                                    format:'#'
                                   },
                                },
                       series: {
                                0:{targetAxisIndex:0},
                                1:{targetAxisIndex:1},
                                2:{targetAxisIndex:1},
}
                      };
        var chart = new google.visualization.LineChart(document.getElementById('$divid'));
        chart.draw(data, options);
       }";
    return array(
        $html,
        $js);
}
/**
 * Navigation tabs for reports
 *
 * @param int $category
 *            The category id
 * @return multitype:tabobject array of tabobjects
 */
function emarking_reports_tabs($category) {
    $tabs = array();
    // Statistics.
    $statstab = new tabobject("statistics",
            new moodle_url("/mod/emarking/reports/print.php", array(
                "category" => $category->id)), get_string("statistics", 'mod_emarking'));
    // Print statistics.
    $statstab->subtree [] = new tabobject("printstatistics",
            new moodle_url("/mod/emarking/reports/print.php", array(
                "category" => $category->id)), get_string("statistics", 'mod_emarking'));
    // Print statistics.
    $statstab->subtree [] = new tabobject("printdetails",
            new moodle_url("/mod/emarking/reports/printdetails.php", array(
                "category" => $category->id)), get_string("printdetails", 'mod_emarking'));
    $tabs [] = $statstab;
    return $tabs;
}
/**
 * Navigation tabs for cost configuration
 *
 * @param int $category
 *            The category id
 * @return multitype:tabobject array of tabobjects
 */
function emarking_costconfig_tabs($category) {
    $tabs = array();
    // Print orders.
    $tabs [] = new tabobject(get_string("costconfigtab", 'mod_emarking'),
            new moodle_url("/mod/emarking/reports/costconfig.php", array(
                "category" => $category->id)), get_string("costconfigtab", 'mod_emarking'));
    // Print orders history.
    $tabs [] = new tabobject(get_string("costcategorytable", 'mod_emarking'),
            new moodle_url("/mod/emarking/reports/categorycosttable.php", array(
                "category" => $category->id)), get_string("costcategorytable", 'mod_emarking'));
    return $tabs;
}
/**
 * Navigation tabs for reports
 *
 * @param string $string
 *            The text you want in the button
 * @param string $id
 * 			  The id you want the button to have
 * @param string $class
 * 			  The class you want the button to have           
 * @return button object
 */
function emarking_buttons_creator($string, $id = null, $class = null) {
	$button = html_writer::tag('button', $string,
			array(
					'id' => $id,
					'class' => $class));
	return $button;
	}
/**
 * Navigation tabs for reports
 *
 * @param array $head
 *            array with the headers of the table
 * @param 2 levels array $data
 * 			  array with each column as an array 
 * @param array $size
 * 			  array with the % of each column
 * @return table object
 */
function emarking_table_creator($head, $data, $size){
	$buttonstable = new html_table();
	$buttonstable->head = $head;
	$buttonstable->data = $data;
	$buttonstable->size = $size;	
	return html_writer::table($buttonstable);
}
function emarking_get_subcategories($category){
	global $DB;
	$arraysubcategory = array();
	$subcategoryquery = "SELECT cc.id as id, cc.name as name FROM {course_categories} as cc
						INNER JOIN {course} c ON (cc.id = c.category)
						INNER JOIN {emarking_exams} eexam ON (c.id = eexam.course)
						INNER JOIN {emarking} e ON (e.id = eexam.emarking)
					    WHERE ".$DB->sql_like('path', ':path')."GROUP BY cc.id";
	if($subcategories = $DB->get_records_sql($subcategoryquery, array( "path" => "%/$category/%"))){
		foreach ($subcategories as $subcategory) {
			$arraysubcategory [$subcategory->id] = $subcategory->name;
		}
	}	
	return $arraysubcategory;
}
function emarking_get_category_cost_table_data($category) {
    global $DB, $OUTPUT;
    // Gets the information of the above query.
    $sqlactivities = "
            SELECT cc.id as id,
            cc.name as name,
            ecc.printingcost as printingcost,
            ecc.costcenter as costcenter
			FROM {course_categories} cc
			LEFT JOIN {emarking_category_cost} ecc ON (ecc.category = cc.id)";
    if ($categorycost = $DB->get_records_sql($sqlactivities)) {
        $arraycategorycost = array();
        foreach ($categorycost as $categorycostdata) {
            $arraycategorycost [$categorycostdata->id] [0] = $categorycostdata->name;
            if (! $categorycostdata->costcenter == null) {
                $arraycategorycost [$categorycostdata->id] [1] = $categorycostdata->printingcost;
                $arraycategorycost [$categorycostdata->id] [2] = $categorycostdata->costcenter;
            } else {
                $arraycategorycost [$categorycostdata->id] [1] = 'NULL';
                $arraycategorycost [$categorycostdata->id] [2] = 'NULL';
            }
            $editicontable = new pix_icon("t/editstring", "edit");
            $arraycategorycost [$categorycostdata->id] [3] = $OUTPUT->action_icon(
                    new moodle_url("/mod/emarking/reports/costconfig.php",
                            array(
                                "category" => $categorycostdata->id)), $editicontable);
        }
    }
    return $arraycategorycost;
}
function emarking_get_activities($category) {
    global $DB;
    $activitiesparams = array(
        "%/$category/%",
        $category);
    // Sql that counts all the resourses since the last time the app was used.
    $sqlactivities = "SELECT count(e.id) AS activities
							   FROM {emarking} e
   							   INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
							   INNER JOIN {course} c ON (c.id = eexam.course)
							   INNER JOIN {course_categories} cc ON (cc.id = c.category)
						       WHERE (cc.path like ? OR cc.id = ?)";
    // Gets the information of the above query.
    if ($activities = $DB->get_record_sql($sqlactivities, $activitiesparams)) {
		return $activities->activities;
    } else {
        return 0;
    }
}
function emarking_get_teacher_ranking($category, $limit = null) {
    global $DB;
    $teacherrankingparams = array(
        "%/$category/%",
        $category);
    // Sql that counts all the resourses since the last time the app was used.
    $sqlteacherranking = "
            SELECT u.id AS id,
            u.firstname AS firstname,
            u.lastname AS lastname,
            count(e.id) AS activities
			FROM {emarking} e
  			INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
			INNER JOIN {user} u ON (u.id = eexam.requestedby)
			INNER JOIN {course} c ON (c.id = eexam.course)
			INNER JOIN {course_categories} cc ON (cc.id = c.category)
			WHERE (cc.path like ? OR cc.id = ?)
    		GROUP BY id
   			ORDER BY activities DESC
			";
    if (! $limit == null) {
        $sqlteacherranking = $sqlteacherranking . "LIMIT $limit";
    }
    // Gets the information of the above query.
    $arrayteacherranking = array();
    if ($teacherranking = $DB->get_records_sql($sqlteacherranking, $teacherrankingparams)) {
        foreach ($teacherranking as $teachersrankings) {
            $arrayteacherranking [$teachersrankings->id] [] = $teachersrankings->firstname . " " . $teachersrankings->lastname;
            $arrayteacherranking [$teachersrankings->id] [] = $teachersrankings->activities;
        }
    }
    return $arrayteacherranking;
}
function emarking_get_original_pages($category) {
    global $DB;
    $originalpagesparams = array(
        "%/$category/%",
        $category,
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT);
    // Sql that counts all the resourses since the last time the app was used.
    $sqloriginalpages = "SELECT AVG((eexam.totalpages+eexam.extrasheets)) AS pages
							   FROM {emarking} e
   							   INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                               INNER JOIN {course} c ON (c.id = eexam.course)
                               INNER JOIN {course_categories} cc ON (cc.id = c.category)
							   WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)";
    // Gets the information of the above query.
    if ($originalpages = $DB->get_record_sql($sqloriginalpages, $originalpagesparams)) {
          return round((int) $originalpages->pages);
    }else {
          return 0;            
    }
}
function emarking_get_total_pages_by_course($category, $limit = null) {
    global $DB;
    $totalpagesbycourseparams = array(
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT,
        "%/$category/%",
        $category);
    // Sql that counts all the resourses since the last time the app was used.
    $sqltotalpagesbycourse = "
            SELECT courseid,
            coursename,
            SUM(pages) AS totalpages
            FROM (
                SELECT c.id AS courseid,
                c.fullname AS coursename,
                eexam.id AS examid,
                ((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE eexam.status IN (?,?) AND (cc.path like ? OR cc.id = ?)
                GROUP BY eexam.id
                ORDER BY pages DESC) AS pagestotal
            GROUP BY courseid
            ORDER BY totalpages DESC
			";
    if (! $limit == null) {
        $sqltotalpagesbycourse = $sqltotalpagesbycourse . "LIMIT $limit";
    }
    // Gets the information of the above query.
    $arraytotalpages = array();
    if ($totalpagesbycourse = $DB->get_records_sql($sqltotalpagesbycourse, $totalpagesbycourseparams)) {
        foreach ($totalpagesbycourse as $pagesbycourse) {
            if (! $pagesbycourse->totalpages == null) {
                $arraytotalpages [$pagesbycourse->courseid] [] = $pagesbycourse->coursename;
                $arraytotalpages [$pagesbycourse->courseid] [] = $pagesbycourse->totalpages;
            }
        }
    }
    return $arraytotalpages;
}
function emarking_get_total_pages($category) {
    global $DB;
    $pageparams = array(
        "%/$category/%",
        $category,
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT);
    // Sql that counts all the resourses since the last time the app was used.
    $sqlpage = "SELECT SUM(pages) AS totalpages
            FROM (SELECT c.id AS courseid,
                    eexam.id AS examid,
                    (eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams) AS pages
					FROM {emarking} e
   					INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                    INNER JOIN {course} c ON (c.id = eexam.course)
                    INNER JOIN {course_categories} cc ON (cc.id = c.category)
					WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)
                    GROUP BY eexam.id
                    ORDER BY pages DESC) AS pagestotal";
    // Gets the information of the above query.
    if ($pages = $DB->get_record_sql($sqlpage, $pageparams)) {
    	if ($pages->totalpages != null){
          return $pages->totalpages;
    	} 
          return 0;
	}
}
function emarking_get_emarking_courses($category) {
    global $DB;
    $emarkingcoursesparams = array(
        "%/$category/%",
        $category);
    // Sql that counts all the resourses since the last time the app was used.
    $sqlemarkingcourses = "
            SELECT COUNT(course) AS courses
            FROM(
                SELECT e.course AS course
				FROM {emarking} e
				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = e.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE (cc.path like ? OR cc.id = ?)
                GROUP BY e.course) courses";
    // Gets the information of the above query.
    if ($emarkingcourses = $DB->get_record_sql($sqlemarkingcourses, $emarkingcoursesparams)) {
            return $emarkingcourses->courses;
    } else {
        return 0;	
	}
}
function emarking_get_students($category) {
    global $DB;
    $studentsparams = array(
        '5',
        "%/$category/%",
        $category);
    // Sql that counts all the resourses since the last time the app was used.
    $sqlstudents = "SELECT count(u.id) AS user
					FROM {user} u
					INNER JOIN {role_assignments} ra ON (ra.userid = u.id)
					INNER JOIN {context} ct ON (ct.id = ra.contextid)
					INNER JOIN {course} c ON (c.id = ct.instanceid)
					INNER JOIN {role} r ON (r.id = ra.roleid)
					INNER JOIN {course_categories} cc ON (cc.id = c.category)
					WHERE ra.roleid=? AND (cc.path like ? OR cc.id = ?)
	";
    // Gets the information of the above query.
    if ($students = $DB->get_record_sql($sqlstudents, $studentsparams)) {
		return $students->user;
    } else {
    return 0;
    }
}
function emarking_get_total_cost_for_table($category, $isyears) {
    global $DB, $CFG;
    $data = "MONTH";
    if($isyears == 1){
    	$data = "YEAR";
    }
    $totalpagesbydateparams = array(
        "%/$category/%",
        $category,
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT);
    // Sql that counts all the resourses since the last time the app was used.
    $sqltotalpagesbydate = "
            SELECT printdate,
            SUM(pages) AS totalcost
            FROM (
                SELECT c.id AS courseid,
                eexam.id AS examid,
                ".$data."(FROM_UNIXTIME(eexam.printdate)) as printdate,
                eexam.printingcost*((eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams)) AS pages
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)
                GROUP BY eexam.id
                ORDER BY pages DESC) AS pagestotal
            GROUP BY printdate";
    // Gets the information of the above query.
    if ($totalpagesbydate = $DB->get_records_sql($sqltotalpagesbydate, $totalpagesbydateparams)) {
    	$arraytotalpagesbydate = array();
    	if($isyears == 1){
    		$yearcount=1;
    		foreach ($totalpagesbydate as $costbydate) {
    			$arraytotalpagesbydate [$yearcount][0] = $costbydate->printdate;
    			$arraytotalpagesbydate [$yearcount][1] = '$' . " " . number_format((int) $costbydate->totalcost);
    			$yearcount++;
    		}
    	}
     	else{
	        for ($contadormes = 1; $contadormes <= 12; $contadormes ++) {
	            if (! isset($arraytotalpagesbydate [$contadormes])) {
	                $arraytotalpagesbydate [$contadormes] = [
	                    date("F", mktime(0, 0, 0, $contadormes, 10)),
	                    '$' . " " . "0"];
	            }
	        }
	        foreach ($totalpagesbydate as $costbydate) {
	            $arraytotalpagesbydate [$costbydate->printdate] [0] = date("F", mktime(0, 0, 0, $costbydate->printdate, 10));
	            $arraytotalpagesbydate [$costbydate->printdate] [1] = '$' . " " . number_format((int) $costbydate->totalcost);
	        }
     	}
    } else {
        $arraytotalpagesbydate = [];
    }
    return $arraytotalpagesbydate;
}
function emarking_get_printing_cost($category) {
    global $DB;
    $totalpritningcost = array(
        "%/$category/%",
        $category,
        EMARKING_EXAM_PRINTED,
        EMARKING_EXAM_SENT_TO_PRINT);
    // Sql that counts all the resourses since the last time the app was used.
    $sqlprintingcost = "
            SELECT SUM(pages) as totalcost
            FROM (
                SELECT c.id AS courseid,
                eexam.id AS examid,
                eexam.printingcost*(eexam.totalpages+eexam.extrasheets)*(eexam.totalstudents+eexam.extraexams) AS pages
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)
				WHERE (cc.path like ? OR cc.id = ?) AND eexam.status IN (?,?)
                GROUP BY eexam.id
                ORDER BY pages DESC) AS pagestotal";
    // Gets the information of the above query.
    if ($printingcost = $DB->get_record_sql($sqlprintingcost, $totalpritningcost)) {
    	return $printingcost->totalcost;
    } else{
    	return 0;
    }
}
function emarking_years_or_months($category){
	global $DB;
	$isyears=0;
	$activitiesbydateparams = array(
			"%/$category/%",
			$category);
	// Sql that counts all the resourses since the last time the app was used.
	$sqlactivitiesbydate = "SELECT COUNT(printyear) as isyears,
							printyear
							FROM
								(SELECT YEAR(FROM_UNIXTIME(eexam.printdate)) AS printyear
								FROM {emarking} e
								INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                				INNER JOIN {course} c ON (c.id = e.course)
                				INNER JOIN {course_categories} cc ON (cc.id = c.category)
			    				WHERE (cc.path like ? OR cc.id = ?)
                				GROUP BY printyear) as y
								";
	// Gets the information of the above query.
	if ($activitiesbydate = $DB->get_record_sql($sqlactivitiesbydate, $activitiesbydateparams)) {
			if($activitiesbydate->isyears >= 2){
				$isyears=1;
			}else{
				$actualyear = $activitiesbydate->printyear;
			}	
	}
	if($isyears == 0){
	$yearormonth= array($isyears, $actualyear);
	}else{
	$yearormonth= array($isyears);
	}
	return $yearormonth;
}
function emarking_download_excel_teacher_ranking($category) {
    global $DB;
    $teacherrankingdata = emarking_get_teacher_ranking($category);
    $headers = [
        get_string('teachername', 'mod_emarking'),
        get_string('activities', 'mod_emarking')];
    $excelfilename = clean_filename("CourseRankCategory" . $category);
    emarking_save_data_to_excel($headers, $teacherrankingdata, $excelfilename, 2);
}
function emarking_download_excel_course_ranking($category) {
    global $DB;
    $courserankingdata = emarking_get_total_pages_by_course($category);
    $headers = [
        get_string('coursename', 'mod_emarking'),
        get_string('totalprintedpages', 'mod_emarking')];
    $excelfilename = clean_filename("CourseRankCategory" . $category);
    emarking_save_data_to_excel($headers, $courserankingdata, $excelfilename, 2);
}
function emarking_download_excel_monthly_cost($category, $totalcostdata) {
    global $DB;
    $headers = [
        get_string('costbydate', 'mod_emarking')];
    $excelfilename = clean_filename("Costes" . $category);
    emarking_save_data_to_excel($headers, $totalcostdata, $excelfilename, 2);
}
function emarking_get_query($params, $SELECT, $SUBSELECT = null, $SUBWHERE = null, $SUBGROUPBY = null, $SUBORDERBY = null, $WHERE = null, $GROUPBY = null, $ORDERBY = null) {
	global $DB;
	// Sql that counts all the resourses since the last time the app was used.
	$query = "SELECT ".$SELECT." FROM";
	if($SUBSELECT != null){
		$query = $query." (SELECT ".$SUBSELECT." 
				FROM {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)";	
     if($SUBWHERE != null){
				$query = $query." WHERE 	".$SUBWHERE;
     }
	 if($SUBGROUPBY != null){
	 			$query = $query." GROUP BY ".$SUBGROUPBY;
	 }
	 if($SUBORDERBY != null){
	 			$query = $query." ORDER BY ".$SUBORDERBY;
	 }

	 			$query = $query." ) AS subquery";
	 }else{
	 	$query = $query." {emarking} e
   				INNER JOIN {emarking_exams} eexam ON (e.id = eexam.emarking)
                INNER JOIN {course} c ON (c.id = eexam.course)
                INNER JOIN {course_categories} cc ON (cc.id = c.category)";	
	 }
	 if($WHERE != null){
	 			$query= $query." WHERE ".$WHERE;
	 }
	 if($GROUPBY != null){
            	$query = $query. " GROUP BY ".$GROUPBY;
            }
     if($ORDERBY != null){
            $query = $query." ORDER BY ".$ORDERBY;
     }
	if ($result = $DB->get_records_sql($query, $params)){
		return $result;
	}else {
		return 0;
	}	
}
function emarking_array_column_chart($queryresult, $arraytitles, $queryvalue1, $queryvalue2){
	$array = array();
	$i = 1;
	$value1 = str_replace('"','',$queryvalue1);
	$value2 = str_replace('"','',$queryvalue2);
	if(!$queryresult){
		$array = [['nodata','nodata'],[0,0],[0,0]];
		return $array;
	}
	
	$array [0] = $arraytitles;
	foreach ($queryresult as $results) {
		if (! $results->$value1 == null) {
			$array [$i] [0] = $results->$value2;
			$array [$i] [1] = round((int) $results->$value1);
		} else {
			$array [$i] [0] = $results->$value2;
			$array [$i] [1] = 0;
		}
		$i ++;
	}
	
	return $array;
	}
function emarking_array_by_date($isyears, $queryresult, $secondarraytitle, $querydatevalue, $queryvalue){
	$array = array();
	$secondtitle = str_replace('"','',$secondarraytitle);
	$date = str_replace('"','',$querydatevalue);
	$value = str_replace('"','',$queryvalue);
	if(!$queryresult){
		$array = [
				['nodata','nodata'],[0,0],[0,0]];
		return $array;
	}
	if($isyears == 1){
		$array [0] = [
				get_string('year', 'mod_emarking'),
				$secondtitle			
		];
		$yearcount=1;
		foreach ($queryresult as $results) {
			$array [$yearcount] [0] = $results->$date;
			$array [$yearcount] [1] = (int) $results->$value;
			$yearcount++;
		}
	}
	if($isyears == 0){
	$array [0] = [
			get_string('month', 'mod_emarking'),
			$secondtitle
	];
	for ($contadormes = 1; $contadormes <= 12; $contadormes ++) {
		if (! isset($array [$contadormes])) {
			$array [$contadormes] = [
					date("F", mktime(0, 0, 0, $contadormes, 10)),
					0];
		}
	}
	foreach ($queryresult as $results) {
		$array [$results->$date] [0] = date("F", mktime(0, 0, 0, $results->$date, 10));
		$array [$results->$date] [1] = (int) $results->$value;
	}
	}
	return $array;
}
/**
 * EMarking time progression
 *
 * @param array $emarkingid
 *            The emarking ids
 * @return multitype: array 
 */
function emarking_time_progression($course, $fortable = null){
	global $DB;
	// EMarking cycle	
	$sqlemarking = "SELECT e.id AS id, e.name as name, eexam.timecreated AS printorder, eexam.printdate AS printdate, MIN(d.timecreated) AS digitalized,
							MIN(d.timecorrectionstarted) AS correctionstarted, MAX(d.timecorrectionended) AS corrected, MIN(d.timefirstpublished) AS firstpublished,
							MIN(d.timeregradingstarted) AS regradingstarted, MAX(d.timeregradingended) AS regraded, MAX(d.timelastpublished) AS lastpublished, 
							d.status as status
							FROM mdl_emarking_exams AS eexam
                            INNER JOIN mdl_emarking AS e ON (e.id = eexam.emarking)
							LEFT JOIN mdl_emarking_draft AS d ON (e.id = d.emarkingid)
							WHERE e.course= ?
							GROUP BY e.id";
	// Gets the information of the above query.
	if ($emarkings = $DB->get_records_sql($sqlemarking,array($course))) {
		$position=0;
		if($fortable == 1){
			$emarkingarray[0] =[get_string('emarkingname', 'mod_emarking'),get_string('dayssenttoprint', 'mod_emarking'),
					get_string('printeddays', 'mod_emarking'),get_string('digitalizeddays', 'mod_emarking'),
					get_string('daysincorrection', 'mod_emarking'),get_string('gradeddays', 'mod_emarking'),
					get_string('publisheddays', 'mod_emarking'),get_string('daysinregrading', 'mod_emarking'),
					get_string('regradeddays', 'mod_emarking'),get_string('finalpublicationdays', 'mod_emarking'),
					get_string('totaldays', 'mod_emarking')];
			$position++;
		}
			foreach($emarkings as $emarking){
				$cmdst = get_coursemodule_from_instance('emarking', $emarking->id);
				$contextdst = context_module::instance($cmdst->id);
				list($gradingmanager, $gradingmethod, $definition, $rubriccontroller) = emarking_validate_rubric($contextdst, false, false);
				$numcriteria = count($definition);
				$marksql="SELECT count(graded) as graded FROM (SELECT d.id as id, CASE WHEN d.status > 10 AND d.status < 20 AND COUNT(DISTINCT c.id) = $numcriteria THEN 1 ELSE 0 END AS graded
				FROM {emarking}  nm
				INNER JOIN {emarking_submission}  s ON (nm.id = :emarkingid AND s.emarking = nm.id)
				INNER JOIN {emarking_page}  p ON (p.submission = s.id)
				INNER JOIN {emarking_draft}  d ON (d.submissionid = s.id AND d.qualitycontrol=0 AND d.emarkingid = $emarking->id)
				LEFT JOIN {emarking_comment}  c on (c.page = p.id AND c.draft = d.id AND c.levelid > 0)
				LEFT JOIN {gradingform_rubric_levels}  l ON (c.levelid = l.id)
				LEFT JOIN {emarking_regrade}  r ON (r.draft = d.id AND r.criterion = l.criterionid AND r.accepted = 0)
				GROUP BY nm.id, s.student) as y";
				$mark = $DB->get_record_sql($marksql,array('emarkingid' => $emarking->id));
				
				
				if($emarking->printdate == 0){
					$status = EMARKING_TO_PRINT;
				
				}elseif(is_null($emarking->digitalized)){
					$status = EMARKING_PRINTED;
				
				}elseif(is_null($emarking->correctionstarted) ){
					$status = EMARKING_STATUS_SUBMITTED;
				
				}elseif(is_null($emarking->corrected)|| $mark->graded == null && $emarking->status == EMARKING_STATUS_GRADING ){
					$status = EMARKING_STATUS_GRADING;
				
				}elseif(is_null($emarking->firstpublished || $mark->graded == 1 && $emarking->status == EMARKING_STATUS_GRADING)){
					$status = EMARKING_STATUS_GRADED;
				
				}elseif(is_null($emarking->regradingstarted) && $emarking->status ==EMARKING_STATUS_PUBLISHED ){
					$status = EMARKING_STATUS_PUBLISHED;
				
				}elseif(is_null($emarking->regraded) || $emarking->status ==EMARKING_STATUS_REGRADING){
					$status = EMARKING_STATUS_REGRADING;
					
				}elseif(is_null($emarking->lastpublished) || $emarking->lastpublished < $emarking->regraded || $emarking->status ==EMARKING_STATUS_REGRADING_RESPONDED){
					$status = EMARKING_STATUS_REGRADING_RESPONDED;
					
				}elseif((round((time()- $emarking->lastpublished)/86400)) >2 && $emarking->status ==EMARKING_STATUS_PUBLISHED){
					$status = EMARKING_STATUS_2DAYS_PUBLISHED;
					
				}elseif($emarking->status ==EMARKING_STATUS_PUBLISHED){
					$status = EMARKING_STATUS_FINAL_PUBLISHED;
					
				}
				
				switch ($status) {
					
					case EMARKING_TO_PRINT:
						$emarkingarray[$position]= array(
							$emarking->name,
							(round((time() - $emarking->printorder)/86400)),
							0,0,0,0,0,0,0,0,0,
							(round((time() - $emarking->printorder)/86400))." Days");
						if($fortable == 1){
							$emarkingarray[$position][11] = (round((time() - $emarking->printorder)/86400));
							unset($emarkingarray[$position][10]);
						}
						$position++;
						break;
						
					case EMARKING_PRINTED:
						$emarkingarray[$position]= array(
								$emarking->name,
								(round(($emarking->printdate - $emarking->printorder)/86400)),
								(round((time() - $emarking->printdate)/86400)),
								0,0,0,0,0,0,0,0,
								(round((time() - $emarking->printorder)/86400))." Days");
						if($fortable == 1){
							$emarkingarray[$position][11] = (round((time() - $emarking->printorder)/86400));
							unset($emarkingarray[$position][10]);
						}
						$position++;
						break;
						
					case EMARKING_STATUS_SUBMITTED:
						$emarkingarray[$position]= array(
								$emarking->name,
								(round(($emarking->printdate - $emarking->printorder)/86400)),
								(round(($emarking->digitalized - $emarking->printdate)/86400)),
								(round((time() - $emarking->digitalized)/86400)),
								0,0,0,0,0,0,0,
								(round((time() - $emarking->printorder)/86400))." Days");
						if($fortable == 1){
							$emarkingarray[$position][11] = (round((time() - $emarking->printorder)/86400));
							unset($emarkingarray[$position][10]);
						}
						$position++;
						break;
						
					case EMARKING_STATUS_GRADING:
						$emarkingarray[$position]= array(
								$emarking->name,
								(round(($emarking->printdate - $emarking->printorder)/86400)),
								(round(($emarking->digitalized - $emarking->printdate)/86400)),
								(round(($emarking->correctionstarted - $emarking->digitalized)/86400)),
								(round((time() - $emarking->correctionstarted)/86400)),
								0,0,0,0,0,0,
								(round((time() - $emarking->printorder)/86400))." Days");
						if($fortable == 1){
							$emarkingarray[$position][11] = (round((time() - $emarking->printorder)/86400));
							unset($emarkingarray[$position][10]);
						}
						$position++;
						break;
						
					case EMARKING_STATUS_GRADED:
						$emarkingarray[$position]= array(
								$emarking->name,
								(round(($emarking->printdate - $emarking->printorder)/86400)),
								(round(($emarking->digitalized - $emarking->printdate)/86400)),
								(round(($emarking->correctionstarted - $emarking->digitalized)/86400)),
								(round(($emarking->corrected - $emarking->correctionstarted)/86400)),
								(round((time() - $emarking->corrected)/86400)),
								0,0,0,0,0,
								(round((time() - $emarking->printorder)/86400))." Days");
						if($fortable == 1){
							$emarkingarray[$position][11] = (round((time() - $emarking->printorder)/86400));
							unset($emarkingarray[$position][10]);
						}
						$position++;
						break;
						
					case EMARKING_STATUS_PUBLISHED:
						$emarkingarray[$position]= array(
								$emarking->name,
								(round(($emarking->printdate - $emarking->printorder)/86400)),
								(round(($emarking->digitalized - $emarking->printdate)/86400)),
								(round(($emarking->correctionstarted - $emarking->digitalized)/86400)),
								(round(($emarking->corrected - $emarking->correctionstarted)/86400)),
								(round(($emarking->firstpublished - $emarking->corrected)/86400)),
								(round((time() - $emarking->firstpublished)/86400)),
								0,0,0,0,
								(round((time() - $emarking->printorder)/86400))." Days");
						if($fortable == 1){
							$emarkingarray[$position][11] = (round((time() - $emarking->printorder)/86400));
							unset($emarkingarray[$position][10]);
						}
						$position++;
						break;
						
					case EMARKING_STATUS_REGRADING:
						$emarkingarray[$position]= array(
								$emarking->name,
								(round(($emarking->printdate - $emarking->printorder)/86400)),
								(round(($emarking->digitalized - $emarking->printdate)/86400)),
								(round(($emarking->correctionstarted - $emarking->digitalized)/86400)),
								(round(($emarking->corrected - $emarking->correctionstarted)/86400)),
								(round(($emarking->firstpublished - $emarking->corrected)/86400)),
								(round(($emarking->regradingstarted - $emarking->firstpublished)/86400)),
								(round((time() - $emarking->regradingstarted)/86400)),
								0,0,0,
								(round((time() - $emarking->printorder)/86400))." Days");
						if($fortable == 1){
							$emarkingarray[$position][11] = (round((time() - $emarking->printorder)/86400));
							unset($emarkingarray[$position][10]);
						}
						$position++;
						break;
						
					case EMARKING_STATUS_REGRADING_RESPONDED:
						$emarkingarray[$position]= array(
								$emarking->name,
								(round(($emarking->printdate - $emarking->printorder)/86400)),
								(round(($emarking->digitalized - $emarking->printdate)/86400)),
								(round(($emarking->correctionstarted - $emarking->digitalized)/86400)),
								(round(($emarking->corrected - $emarking->correctionstarted)/86400)),
								(round(($emarking->firstpublished - $emarking->corrected)/86400)),
								(round(($emarking->regradingstarted - $emarking->firstpublished)/86400)),
								(round(($emarking->regraded - $emarking->regradingstarted)/86400)),
								(round((time() - $emarking->regraded)/86400)),
								0,0,
								(round((time() - $emarking->printorder)/86400))." Days");
						if($fortable == 1){
							$emarkingarray[$position][11] = (round((time() - $emarking->printorder)/86400));
							unset($emarkingarray[$position][10]);
						}
						$position++;
						break;
						
					case EMARKING_STATUS_FINAL_PUBLISHED:
						$emarkingarray[$position]= array(
						$emarking->name,
						(round(($emarking->printdate - $emarking->printorder)/86400)),
						(round(($emarking->digitalized - $emarking->printdate)/86400)),
						(round(($emarking->correctionstarted - $emarking->digitalized)/86400)),
						(round(($emarking->corrected - $emarking->correctionstarted)/86400)),
						(round(($emarking->firstpublished - $emarking->corrected)/86400)),
						(round(($emarking->regradingstarted - $emarking->firstpublished)/86400)),
						(round(($emarking->regraded - $emarking->regradingstarted)/86400)),
						(round(($emarking->lastpublished - $emarking->regraded)/86400)),
						(round((time()- $emarking->lastpublished)/86400)),
						0,
						(round((time() - $emarking->printorder)/86400))." Days");
						if($fortable == 1){
							$emarkingarray[$position][11] = (round((time() - $emarking->printorder)/86400));
							unset($emarkingarray[$position][10]);
						}
						$position++;
						break;
						
					case EMARKING_STATUS_2DAYS_PUBLISHED:
						$emarkingarray[$position]= array(
								$emarking->name,
								(round(($emarking->printdate - $emarking->printorder)/86400)),
								(round(($emarking->digitalized - $emarking->printdate)/86400)),
								(round(($emarking->correctionstarted - $emarking->digitalized)/86400)),
								(round(($emarking->corrected - $emarking->correctionstarted)/86400)),
								(round(($emarking->firstpublished - $emarking->corrected)/86400)),
								(round(($emarking->regradingstarted - $emarking->firstpublished)/86400)),
								(round(($emarking->regraded - $emarking->regradingstarted)/86400)),
								(round(($emarking->lastpublished - $emarking->regraded)/86400)),
								2,
								0,
								(round((time() - $emarking->printorder)/86400))." Days");
						if($fortable == 1){
							$emarkingarray[$position][11] = (round((time() - $emarking->printorder)/86400));
							unset($emarkingarray[$position][10]);
						}
						$position++;
						break;

				}
			}
			return $emarkingarray;
	}else{
		return 0;
	}
}
function emarking_cycle_tabs($selectedcourse, $selectedsection, $selectedcategory, $course){
	global $DB;

	$getemarkingssql = 'SELECT ee.id AS id,
				ee.name AS name
				FROM {emarking_exams} AS ee
				INNER JOIN {course} AS c ON (ee.course = c.id AND c.shortname LIKE "%'.$selectedcourse.'-'.$selectedsection.'%")
				INNER JOIN {course_categories} AS cc ON (c.category = cc.id AND cc.name = "'.$selectedcategory.'")';

	$getemarkings = $DB->get_records_sql($getemarkingssql);

	$emarkingtabs = array();

	$emarkingtabs[] = new tabobject(0,
			new moodle_url("/mod/emarking/reports/cycle.php", array(
					"course" => $course->id, "emarking" => 0,
					"selectedcourse" => $selectedcourse, "selectedsection" => $selectedsection,
					"selectedcategory" => $selectedcategory,
					"currenttab" => 0
			)),
			get_string('summary', 'mod_emarking'));

	$tabid = 1;
	foreach($getemarkings as $emarkings){

		$emarkingtabs[] = new tabobject($tabid,
				new moodle_url("/mod/emarking/reports/cycle.php", array(
						"course" => $course->id, "emarking" => $emarkings->id,
						"selectedcourse" => $selectedcourse, "selectedsection" => $selectedsection,
						"selectedcategory" => $selectedcategory,
						"currenttab" => $tabid
				)),
				$emarkings->name);

		$tabid = $tabid + 1;
	}
	return $emarkingtabs;
}
function emarking_gantt_data($emarkingid){
	global $DB;
	$emarkingdatasql = 'SELECT e.id AS id,
			e.name AS name,
			ee.timecreated AS printorder,
			ee.printdate AS printdate,
			MIN(ed.timecreated) AS digitalized,
			MIN(ed.timecorrectionstarted) AS correctionstarted,
			MAX(ed.timecorrectionended) AS corrected,
			MIN(ed.timefirstpublished) AS firstpublished,
			MIN(ed.timeregradingstarted) AS regradingstarted,
			MAX(ed.timeregradingended) AS regraded,
			MAX(ed.timelastpublished) AS lastpublished
			FROM {emarking_exams} AS ee
            INNER JOIN {emarking} AS e ON (e.id = ee.emarking AND ee.id = ?)
			LEFT JOIN {emarking_draft} AS ed ON (e.id = ed.emarkingid)';

	$emarkingdata = $DB->get_record_sql($emarkingdatasql, array($emarkingid));

	if((time() - $emarkingdata->lastpublished) >2){
		$finaltime = $emarkingdata->lastpublished + 172800;
	}elseif((time() - $emarkingdata->lastpublished) <2){
		$finaltime = time();
	}
	
	
	$chartdefaultdata = array(
			array('1', 'enviado a imprimir', 'Impresión', $emarkingdata->printorder*1000, $emarkingdata->printdate*1000, null, 100, null),
			array('2', 'impreso', 'Impresión', $emarkingdata->printdate*1000, $emarkingdata->digitalized*1000, null, 100, '1'),
			array('3', 'digitalizado', 'Digitalización', $emarkingdata->digitalized*1000, $emarkingdata->correctionstarted*1000, null, 100, '2'),
			array('4', 'en correccion', 'Corrección', $emarkingdata->correctionstarted*1000, $emarkingdata->corrected*1000, null, 100, '3'),
			array('5', 'corregido', 'Corrección', $emarkingdata->corrected*1000, $emarkingdata->firstpublished*1000, null, 100, '4'),
			array('6', 'publicado', 'Publicación', $emarkingdata->firstpublished*1000, $emarkingdata->regradingstarted*1000, null, 100, '5'),
			array('7', 'en recorreccion', 'Recorrección', $emarkingdata->regradingstarted*1000, $emarkingdata->regraded*1000, null, 100, '6'),
			array('8', 'recorregido', 'Recorrección', $emarkingdata->regraded*1000, $emarkingdata->lastpublished*1000, null, 100, '7'),
			array('9', 'publicado final', 'Publicación', $emarkingdata->lastpublished*1000, $finaltime*1000, null, 100, '8')
	);
	return $chartdefaultdata;
}
function emarking_area_chart($emarkingid){
	global $DB;

	$draftsdatasql = 'SELECT ed.id AS draftid,
				ed.timecorrectionstarted AS correctionstarted,
				ed.timecorrectionended AS correctionended,
				ed.timefirstpublished AS publicationstarted,
				ed.timelastpublished AS publicationended,
				ed.timeregradingstarted AS regradingstarted,
				ed.timeregradingended AS regradingended
				FROM mdl_emarking_draft AS ed
				WHERE ed.emarkingid = ?';
	
	$draftsdata = $DB->get_records_sql($draftsdatasql, array($emarkingid));
	
	$chartparameterssql = 'SELECT COUNT(ed.id) AS quantity,
				MIN(ed.timecorrectionstarted) AS startdate,
				MAX(ed.timelastpublished) AS enddate
				FROM mdl_emarking_draft AS ed
				WHERE ed.emarkingid = ?';
	
	$chartparameters = $DB->get_record_sql($chartparameterssql, array($emarkingid));
	
	//var_dump($chartparameters);
	
	$startdate = (floor($chartparameters->startdate / 86400) * 86400) + 14400; // 14400 por la diferencia horaria en chile con la utc
	$enddate = (ceil($chartparameters->enddate / 86400) * 86400) + 14400;
	$unixday = 86400;
	
//echo $startdate;
//echo $enddate;
// var_dump($draftsdata);
 
 $currentdata = array();
 
	foreach($draftsdata as $draftdates){
		$currentdata[$draftdates->draftid] = 'Digitalized';
	}
//	var_dump($currentdata);
	
	$areachart = array(array('Date', 'Digitalized', 'Graded', 'Publicated', 'Regraded', 'Repiblished')); 
	$aux = array();
	
	for ($date = $startdate; $date < $enddate; $date += $unixday){
		$digitalized = 0;
		$graded = 0;
		$publicated = 0;
		$regraded = 0;
		$republished = 0;
		
//		echo $date.'<br>';
		
		foreach($draftsdata as $draftdates){
			
			if($draftdates->publicationended < $date + 86400){
				$currentdata[$draftdates->draftid] = 'republished';
				$republished = $republished + 1;
			}elseif($draftdates->regradingended > $date && $draftdates->regradingended < $date + 86400){
				$currentdata[$draftdates->draftid] = 'regraded';
				$regraded = $regraded + 1;
			}elseif($draftdates->publicationstarted > $date && $draftdates->publicationstarted < $date + 86400){
				$currentdata[$draftdates->draftid] = 'publicated';
				$publicated = $publicated + 1;
			}elseif($draftdates->correctionended > $date && $draftdates->correctionended < $date + 86400){
				$currentdata[$draftdates->draftid] = 'graded';
				$graded = $graded + 1;
			}else{
				$currentdata[$draftdates->draftid] = 'digitalized';
				$digitalized = $digitalized + 1;
			}
		}
		$aux = array(gmdate("Y-m-d", $date), $digitalized, $graded, $publicated, $regraded, $republished);
		$areachart[] = $aux;
	}
	
	return $areachart;
}
function emarking_markers_corrections($emarkingid){
	global $DB;
	$commentssql = "SELECT c.id as comment, u.firstname as name, u.lastname as lastname
					FROM {emarking} AS e
				   INNER JOIN {emarking_submission} AS s ON (s.emarking = e.id AND emarking = ?)
				   INNER JOIN {emarking_draft} AS d ON (s.id = d.submissionid)
				   INNER JOIN {emarking_comment} AS c ON (c.draft = d.id)
				   INNER JOIN {user} AS u ON (u.id = c.markerid)";
	if($comments = $DB->get_records_sql($commentssql, array($emarkingid))){
		$commentsarray=array();
		foreach($comments as $comment)
			$commentarray = get_object_vars($comment);
			array_push($commentsarray,$commentarray);
	}
	print_r($commentsarray);

	$regradedcommentssql = "SELECT c.id, r.id, r.markerid
					FROM {emarking} AS e
				   INNER JOIN {emarking_submission} AS s ON (s.emarking = e.id AND emarking = ?)
				   INNER JOIN {emarking_draft} AS d ON (s.id = d.submissionid)
				   INNER JOIN {emarking_comment} AS c ON (c.draft = d.id)
			       LEFT JOIN mdl_emarking_regrade AS r ON (r.criterion = c.criterionid AND c.draft = r.draft)
				   INNER JOIN {user} AS u ON (u.id = r.markerid)";
	$regradedcomments = $DB->get_records_sql($regradedcommentssql, array($emarkingid));

}

