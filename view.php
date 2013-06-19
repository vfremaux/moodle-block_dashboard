<?php

	include '../../config.php';
	
	$courseid = required_param('id', PARAM_INT);
	$blockid = required_param('blockid', PARAM_INT);

	if (!$course = $DB->get_record('course', array('id' => $courseid))){
		print_error('invalidcourseid');
	}
	
	require_login($course);
	
    if (!$instance = $DB->get_record('block_instances', array('id' => $blockid))){
        print_error('invalidblockid');
    }

    $theBlock = block_instance('dashboard', $instance);
	$context = context_block::instance($theBlock->instance->id);	

	$PAGE->navbar->add(get_string('dashboards', 'block_dashboard'), NULL);
	$PAGE->navbar->add(@$theBlock->config->title, NULL);
	$PAGE->set_url($CFG->wwwroot.'/bocks/dashboard/view.php?id='.$courseid.'&blockid='.$blockid);
	$PAGE->set_title($SITE->shortname);
	$PAGE->set_heading($SITE->shortname);
	echo $OUTPUT->header();

	echo $OUTPUT->box_start();

	echo $theBlock->print_dashboard();

	if (has_capability('block/dashboard:configure', $context) && $PAGE->user_is_editing()){
		$options = array();
		$options['id'] = $courseid;
		$options['bui_editid'] = $blockid;
		$options['sesskey'] = sesskey();
		echo '<div class="configure">';
		echo $OUTPUT->single_button(new moodle_url($CFG->wwwroot.'/course/view.php', $options), get_string('configure', 'block_dashboard'), 'get');
		echo '</div>';
		echo "<br/>";
	}
	echo $OUTPUT->box_end();
	echo "<br/>";
	echo '<center>';
	$options = array();
	$options['id'] = $courseid;
	echo $OUTPUT->single_button(new moodle_url($CFG->wwwroot.'/course/view.php', $options), get_string('backtocourse', 'block_dashboard'), 'get');
	echo '</center>';
	echo "<br/>";
	echo $OUTPUT->footer($course);