<?php

/**
*  This generates the output file using output extraction parameters on filtered data
*
*
*/
	// needs buffering for a really clean file output
	
	include '../../../config.php';
	
	$debug = optional_param('debug', false, PARAM_BOOL);

	require_once $CFG->dirroot.'/blocks/dashboard/lib.php';

	$courseid = required_param('id', PARAM_INT); // the course ID
	$instanceid = required_param('instance', PARAM_INT); // the block ID

	if (!$course = $DB->get_record('course', array('id' => "$courseid"))){
		print_error('badcourseid');
	}
	
	require_login($course);
	
	$context = context_block::instance($instanceid);

	$PAGE->navbar->add(get_string('dashboards', 'block_dashboard'), NULL);
	if (!empty($theBlock->config->title)){
		$PAGE->navbar->add($theBlock->config->title, NULL);
	}
	$PAGE->set_url($CFG->wwwroot.'/bocks/dashboard/export/export_output_csv.php?id='.$courseid.'&instance='.$instanceid);
	$PAGE->set_title($SITE->shortname);
	$PAGE->set_heading($SITE->shortname);

	echo $OUTPUT->header();

	if (!$instance = $DB->get_record('block_instances', array('id' => "$instanceid"))){
	    print_error('badblockinstance', 'block_dashboard');
	}
	
	$theBlock = block_instance('dashboard', $instance);

	$theBlock->prepare_config();

	/// fetch data
	
	if (!empty($theBlock->config->filters)){
		$theBlock->prepare_filters();
	} else {
		$theBlock->filteredsql = str_replace('<%%FILTERS%%>', '', $theBlock->sql);
	}

	if (!empty($theBlock->params)){
		$theBlock->prepare_params();
	} else {
		$theBlock->filteredsql = str_replace('<%%PARAMS%%>', '', $theBlock->filteredsql);
	}

	$sort = optional_param('tsort'.$theBlock->instance->id, '', PARAM_TEXT);
	if (!empty($sort)){
		// do not sort if already sorted in explained query
		if (!preg_match('/ORDER\s+BY/si', $theBlock->sql))
		    $theBlock->filteredsql .= " ORDER BY $sort";
	}
			
	$filteredsql = $theBlock->protect($theBlock->filteredsql);

	/*
	if (!empty($theBlock->config->pagesize)){
		$offset = $rpage * $theBlock->config->pagesize;
	} else {
		$offset = '';
	}*/
	
	$results = $theBlock->fetch_dashboard_data($filteredsql, '', '', true); // get all data

	if ($results){
		echo '<pre>';
		$theBlock->generate_output_file($results);
		echo '</pre>';
		echo $OUTPUT->notification(get_string('filegenerated', 'block_dashboard'));
		echo $OUTPUT->single_button($CFG->wwwroot.'/blocks/dashboard/export/filearea.php?id='.$courseid.'&instance='.$instanceid, get_string('filesview', 'block_dashboard'));
		echo $OUTPUT->single_button($CFG->wwwroot.'/course/view.php?id='.$courseid, get_string('backtocourse', 'block_dashboard'));
	} else {
		echo "No results. Empty file";
	}

	echo $OUTPUT->footer();
