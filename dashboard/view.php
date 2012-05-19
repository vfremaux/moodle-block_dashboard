<?php

	include '../../config.php';
	
	
	$courseid = required_param('id', PARAM_INT);
	$blockid = required_param('blockid', PARAM_INT);
	
	if (!$course = get_record('course', 'id', $courseid)){
		error('Bad course ID');
	}
	
	require_login($course);
	
    $pinned = optional_param('pinned', false, PARAM_INT);
    $blocktable = ($pinned) ? 'block_pinned' : 'block_instance' ;
    if (!$instance = get_record($blocktable, 'id', $blockid)){
        error('Invalid block');
    }
    $theBlock = block_instance('dashboard', $instance);
	$context = get_context_instance(CONTEXT_BLOCK, $theBlock->instance->id);	
	
	$navlinks = array(
			array('name' => $course->shortname,
			      'type' => 'url',
			      'link' => $CFG->wwwroot.'/cours/view.php?id='.$courseid),
			array('name' => get_string('dashboards', 'block_dashboard'),
			      'type' => 'title',
			      'link' => ''),
			array('name' => $theBlock->config->title,
			      'type' => 'title',
			      'link' => ''),
		);
	
	print_header($SITE->shortname, $SITE->shortname, build_navigation($navlinks));

	print_box_start();
	echo $theBlock->print_dashboard();	
	if (has_capability('block/dashboard:configure', $context)){
		$options = array();
		$options['id'] = $courseid;
		$options['instanceid'] = $blockid;
		$options['blockaction'] = 'config';
		$options['sesskey'] = sesskey();
		echo '<div style="float:right;width:100px;">';
		print_single_button($CFG->wwwroot.'/course/view.php', $options, get_string('configure', 'block_dashboard'));
		echo '</div>';
		echo "<br/>";
		echo "<br/>";
	}
	print_box_end();
	
	echo '<center>';
	$options = array();
	$options['id'] = $courseid;
	print_single_button($CFG->wwwroot.'/course/view.php', $options, get_string('backtocourse', 'block_dashboard'));
	echo '</center>';
	
	print_footer($course);