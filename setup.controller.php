<?php

	if (!defined('MOODLE_INTERNAL')) die ('Sorry, but you cannot use this script this way');

	$data = $_POST;
	
	unset($data['submit']);
	
	$theBlock->config = (object) $data;
	$theBlock->instance_config_save($theBlock->config);
	
	if (!empty($submitandreturn)){
		redirect($CFG->wwwroot.'/course/view.php?id='.$courseid);
	}
