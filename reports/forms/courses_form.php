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
 * @copyright 2016 Benjamin Espinosa (beespinosa94@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/config.php");
require_once ($CFG->libdir . "/formslib.php");

class courses_form extends moodleform {

	function definition() {
		global $DB, $USER;
		
		$mform = $this->_form;
		$instance = $this->_customdata;
		
		$userid = $instance['0'];
		$category = $instance['1'];
		$cid = $instance['2'];
//		var_dump($category);
		$teachercoursessql = "SELECT c.id AS course_id,
				cc.name AS category_name,
				c.shortname AS course_name,
				CONCAT (u.firstname, ' ', u.lastname)AS name
				FROM {user} u
				INNER JOIN {role_assignments} ra ON (ra.userid = u.id AND u.id=?)
				INNER JOIN {context} ct ON (ct.id = ra.contextid)
				INNER JOIN {course} c ON (c.id = ct.instanceid)
				INNER JOIN {course_categories} cc ON (cc.id = c.category AND cc.name = ?)
				INNER JOIN {role} r ON (r.id = ra.roleid AND r.shortname IN ('teacher', 'editingteacher', 'manager'))
                GROUP BY course_id";
		
		$teachercourses = $DB->get_records_sql($teachercoursessql, array($USER->id, $category));
		
//		$categories = array();
//		$shortname = array();
//		$courseparameters = array();
//		$sections = array();
		
		foreach($teachercourses as $coursedata){
			
			$shortname[$coursedata->course_name] = $coursedata->course_name;
//			$courseparameters[] = array(explode('-', $coursedata->course_name), $coursedata->course_id);
			
// 			foreach($courseparameters as $key => $parameters){
				
// 				$shortname[$parameters[0][2]] = $parameters[0][2];
// 				$sections[$parameters[0][3]] = $parameters[0][3];
// 			}
 		}

//		$category[] = array_unique($categories);
		$courses = array_unique($shortname);
//		var_dump($courses);
//		$sections = array_unique($sections);
		
//		$out = html_writer::div('<h2>'.get_string('filters','mod_emarking').'</h2>');
//		echo $out;
		
//		$mform->addElement('select', 'category', get_string('category','mod_emarking'), $categories);
//		$mform->setType( 'category', PARAM_TEXT);
		
		$mform->addElement('select', 'courses', get_string('course','mod_emarking'), $courses);
		$mform->setType('courses' , PARAM_TEXT);
		
//		$mform->addElement('select', 'section', get_string('section','mod_emarking'), $sections);
//		$mform->setType('section' , PARAM_INT);

		$mform->addElement("hidden", "category", $category);
		$mform->setType( "category", PARAM_INT);
		
		$mform->addElement("hidden", "course", $cid);
		$mform->setType( "course", PARAM_INT);
		
		$this->add_action_buttons(false, get_string('search', 'mod_emarking'));
		
	}
}
