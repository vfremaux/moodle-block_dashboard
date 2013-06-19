<?php

	include '../../config.php';
	
	$PAGE->requires->js('/blocks/dashboard/js/jquery-1.8.2.min.js', true);
	$PAGE->requires->js('/blocks/dashboard/js/module.js', true);

	$courseid = required_param('id', PARAM_INT);
	$blockid = required_param('instance', PARAM_INT);

	if (!$course = $DB->get_record('course', array('id' => $courseid))){
		print_error('invalidcourseid');
	}
	
	require_login($course);
	
    if (!$instance = $DB->get_record('block_instances', array('id' => $blockid))){
        print_error('invalidblockid');
    }

    $theBlock = block_instance('dashboard', $instance);
	$context = context_block::instance($theBlock->instance->id);	

	require_capability('block/dashboard:configure', $context);

	if (($submit = optional_param('submit','', PARAM_TEXT)) || ($submitandreturn = optional_param('submitandreturn','', PARAM_TEXT))){
		include 'setup.controller.php';
	}
	
	$PAGE->navbar->add(get_string('dashboards', 'block_dashboard'), NULL);
	$PAGE->navbar->add(@$theBlock->config->title, NULL);
	$PAGE->set_url($CFG->wwwroot.'/bocks/dashboard/view.php?id='.$courseid.'&blockid='.$blockid);
	$PAGE->set_title($SITE->shortname);
	$PAGE->set_heading($SITE->shortname);
	echo $OUTPUT->header();

	echo $OUTPUT->box_start();


	echo '<form name="setup" action="#" method="post">';
	
	include 'setup_instance.html';
	
	echo '</form>';

	echo $OUTPUT->box_end();
	echo $OUTPUT->footer();
