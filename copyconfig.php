<?php

/**
 * 
 * @package block-dashboard
 * @category blocks
 * @author Valery Fremaux (valery@club-internet.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @version Moodle 2.2
 */
	ob_start();
	include '../../config.php';

/// setting contexts
	
	$id = required_param('id', PARAM_INT); // course id
	$instanceid = required_param('instance', PARAM_INT); // block instance id
	$action = optional_param('what', '', PARAM_TEXT);
	
	if (!$course = $DB->get_record('course', array('id' => "$id"))){
		print_error('invalidcourseid');
	}

	if (!$instance = $DB->get_record('block_instances', array('id' => "$instanceid"))){
	    print_error('badblockinstance', 'block_dashboard');
	}
	
	$theBlock = block_instance('dashboard', $instance);
	
// security 
	
	require_login($course);
	require_capability('block/dashboard:configure', context_system::instance(0));
	
// get a copy of block configuration

	if ($action == 'get'){
		    ob_end_clean();
			header("Content-Type:text/raw\n\n");
			echo $instance->configdata;
			die;
	}

// process form 
	
	include_once 'copyconfig_form.php';

	$url = $CFG->wwwroot.'/blocks/dashboard/copyconfig.php?id='.$course->id.'&instance='.$instanceid;
	
	$mform = new CopyConfig_Form($url);
	
	if ($mform->is_cancelled()){
		redirect($CFG->wwwroot."/course/view.php?id={$id}&bui_edit={$instanceid}&sesskey=".sesskey());
	}
	
	if ($data = $mform->get_data()){
		$DB->set_field('block_instance', 'configdata', $data->configdata, array('id' => "$instanceid"));
		redirect($CFG->wwwroot."/course/view.php?id={$id}&bui_edit={$instanceid}&sesskey=".sesskey());
	}
	
	$PAGE->set_url($url);
	$PAGE->set_title($SITE->shortname);
	$PAGE->set_heading($SITE->shortname);
	$OUTPUT->header();
	
	echo $OUTPUT->heading(get_string('configcopy', 'block_dashboard'));
	
	$mform->display();
	
	echo $OUTPUT->footer($course);	

?>