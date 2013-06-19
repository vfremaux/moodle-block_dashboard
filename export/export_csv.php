<?php

/**
*  Exporter of dashboard data snapshot
*
*
*/
	// needs buffering for a really clean file output
	
	include '../../../config.php';
	
	$debug = optional_param('debug', false, PARAM_BOOL);
	if (!$debug){
		ob_start();
	} else {
		echo "<pre>Debugging mode\n";
	}

	require_once $CFG->dirroot.'/blocks/dashboard/lib.php';

	if (!isset($CFG->dashboard_csv_field_separator)) $CFG->dashboard_csv_field_separator = ';';
	if (!isset($CFG->dashboard_csv_line_separator)) $CFG->dashboard_csv_line_separator = "\r\n";

	$courseid = required_param('id', PARAM_INT); // the course ID
	$instanceid = required_param('instance', PARAM_INT); // the block ID
	$output = optional_param('output', 'csv', PARAM_ALPHA); // output format (csv)
	$limit = optional_param('limit', '', PARAM_INT);
	$offset = optional_param('offset', '', PARAM_INT); 
	$alldata = optional_param('alldata', '', PARAM_INT); 

	if (!$course = $DB->get_record('course', array('id' => "$courseid"))){
		print_error('badcourseid');
	}
	
	require_login($course);

	if (!$instance = $DB->get_record('block_instances', array('id' => "$instanceid"))){
	    print_error('badblockinstance', 'block_dashboard');
	}
	
	$theBlock = block_instance('dashboard', $instance);

	$theBlock->prepare_config();
	$theBlock->prepare_filters();
	$theBlock->prepare_params();

	/// fetch data

	if (!empty($theBlock->config->filters)){
		$theBlock->prepare_filters();
	} else {
		$theBlock->filteredsql = str_replace('<%%FILTERS%%>', '', $theBlock->sql);
	}
	$theBlock->sql = str_replace('<%%FILTERS%%>', '', $theBlock->sql); // needed to prepare for filter range prefetch

	if (!empty($theBlock->params)){
		$theBlock->prepare_params();
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
		// output csv file		
		$exportname = (!empty($theBlock->config->title)) ? clean_filename($theBlock->config->title) : 'dashboard_export' ;
		header("Content-Type:text/csv\n\n");
		header("Content-Disposition:filename={$exportname}.csv\n\n");

		// print column names
		$headrow = array();
		foreach($theBlock->output as $field => $label){
			$headrow[] = $label;
		}

		if (!$debug) ob_end_clean();		
		if (!empty($CFG->latinexcelexport)){
			echo utf8_decode(implode($CFG->dashboard_csv_field_separator, $headrow)); 
		} else {
			echo implode($CFG->dashboard_csv_field_separator, $headrow); 
		}
		echo $CFG->dashboard_csv_line_separator;

		// print data
		foreach($results as $r){
			$row = array();
			foreach($theBlock->output as $field => $label){

				// did we ask for cumulative results ? 
				$cumulativeix = null;
				if (preg_match('/S\((.+?)\)/', $field, $matches)){
					$field = $matches[1];
					$cumulativeix = $theBlock->instance->id.'_'.$field;
				}

				if (!empty($theBlock->outputf[$field])){
					$datum = dashboard_format_data($theBlock->outputf[$field], @$r->$field, $cumulativeix);
				} else {
					$datum = dashboard_format_data(null, @$r->$field, $cumulativeix);
				}
				$row[] = $datum;
			}
			if (!empty($CFG->latinexcelexport)){
				echo utf8_decode(implode($CFG->dashboard_csv_field_separator, $row)); 
			} else {
				echo implode($CFG->dashboard_csv_field_separator, $row); 
			}
			echo $CFG->dashboard_csv_line_separator;
		}
	} else {
		echo "No results. Empty file";
	}


?>