<?php

if (array_key_exists('submit', $_POST)){
	$data = $_POST;
	
	unset($data['submit']);
	
	$theBlock->config = (object) $data;
	$theBlock->instance_config_save($theBlock->config);
	
	redirect($CFG->wwwroot.'/course/view.php?id='.$COURSE->id);
}