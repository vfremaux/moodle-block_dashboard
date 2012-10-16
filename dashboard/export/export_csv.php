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

	if (!isset($CFG->dashboard_csv_field_separator)) $CFG->dashboard_csv_field_separator = ';';
	if (!isset($CFG->dashboard_csv_line_separator)) $CFG->dashboard_csv_line_separator = "\n";

	$courseid = required_param('id', PARAM_INT); // the course ID
	$instanceid = required_param('instance', PARAM_INT); // the block ID
	$output = optional_param('output', 'csv', PARAM_ALPHA); // output format (csv)
	$limit = optional_param('limit', '', PARAM_INT);
	$offset = optional_param('offset', '', PARAM_INT); 
	$alldata = optional_param('alldata', '', PARAM_INT); 
	
	if (!$course = get_record('course', 'id', "$courseid")){
		print_error('badcourseid');
	}
	
	require_login($course);

	if (!$instance = get_record('block_instance', 'id', "$instanceid")){
	    print_error('badblockinstance', 'block_dashboard');
	}
	
	$theBlock = block_instance('dashboard', $instance);
	
	// prepare data for tables

	$outputfields = explode(';', @$theBlock->config->outputfields);
	$outputlabels = explode(';', @$theBlock->config->fieldlabels);
	$outputformats = explode(';', @$theBlock->config->outputformats);
	$theBlock->normalize($outputfields, $outputlabels); // normalizes labels to keys
	$theBlock->normalize($outputfields, $outputformats); // normalizes labels to keys
	$output = array_combine($outputfields, $outputlabels);
	$outputf = array_combine($outputfields, $outputformats);

	// filtering query : we need this 
	$outputfilters = explode(';', @$theBlock->config->filters);
	$outputfilterlabels = explode(';', @$theBlock->config->filterlabels);
	$theBlock->normalize($outputfilters, $outputfilterlabels); // normailzes labels to keys
	$theBlock->filterfields->labels = array_combine($outputfilters, $outputfilterlabels);
	$outputfilterdefaults = explode(';', @$theBlock->config->filterdefaults);
	$theBlock->normalize($outputfilters, $outputfilterdefaults); // normailzes defaults to keys
	$theBlock->filterfields->defaults = array_combine($outputfilters, $outputfilterdefaults);
	$outputfilteroptions = explode(';', @$theBlock->config->filteroptions);
	$theBlock->normalize($outputfilters, $outputfilteroptions); // normailzes options to keys
	$theBlock->filterfields->options = array_combine($outputfilters, $outputfilteroptions);
	// Detect translated
	$translatedfilters = array();
	$filterfields = array();
	foreach($outputfilters as $f){
		if (preg_match('/^(.*) as (.*)$/si', $f, $matches)){
			$translatedfilters[$f] = $matches[2];
			$filterfields[$matches[2]] = $matches[1];
			$translatedfilters[$matches[2]] = $f;
		}
	}
	$theBlock->filterfields->translations = $translatedfilters;
	$theBlock->filterfields->filtercanonicalfield = $filterfields;

	$sql = $theBlock->config->query;

	/// fetch data

	if (!empty($theBlock->config->filters) && !$alldata){
		$filterclause = '';
		$filterkeys = preg_grep('/^filter'.$instanceid.'_/', array_keys($_GET));
		$globalfilterkeys = preg_grep('/^filter0_/', array_keys($_GET));
		$filtervalues = array();
		$filters = array();
		$filterinputs = array();
		
		foreach($filterkeys as $key){
			$filterinputs[$key] = $_GET[$key];
		}

		foreach($globalfilterkeys as $key){
			$radical = str_replace('filter0_', '', $key);
			$canonicalfilter = (array_key_exists($radical, $theBlock->filterfields->translations)) ? $theBlock->filterfields->translations[$radical] : $radical;
			if ($theBlock->is_filter_global($canonicalfilter)){
				$filterinputs[$key] = $_GET[$key];
			}
		}
		
		// process defaults if setup, faking $_GET input
		if (!empty($theBlock->filterfields->defaults)){
			foreach($theBlock->filterfields->defaults as $filter => $default){
				$canonicalfilter = (array_key_exists($filter, $theBlock->filterfields->translations)) ? $theBlock->filterfields->translations[$filter] : $filter;
				$default = (preg_match('/LAST|FIRST/i', $default)) ? $theBlock->filter_get_results(str_replace('<%%FILTERS%%>', '', $sql), $filter, $canonicalfilter, $default) : $default ;
				if (!$theBlock->is_filter_global($filter)){
					if (!array_key_exists('filter'.$instanceid.'_'.$canonicalfilter, $filterinputs)) $filterinputs['filter'.$instanceid.'_'.$canonicalfilter] = $default;
				}
			}
		}
		
		if (!empty($filterinputs)){
			foreach($filterinputs as $key => $value){
				if ($theBlock->is_filter_global($filter)){
					$radical = str_replace('filter0_', '', $key);
				} else {
					$radical = str_replace('filter'.$theBlock->instance->id.'_', '', $key);
				}
				$sqlfiltername = (isset($theBlock->filterfields->filtercanonicalfield[$radical])) ? $theBlock->filterfields->filtercanonicalfield[$radical] : $radical ;
				if (!empty($value)){
					if (!is_array($value)){
						$filters[] = " $radical = '".str_replace("'", "''", $value)."' ";
					} else {
						if (count($value) > 1 || $value[0] != 0){
							$filters[] = " $radical IN ('".implode("','", str_replace("'", "''", $value))."') ";
						}
					}
					$filtervalues[$radical] = $value;
				}
			}
		}
		
		if (!empty($filters)){
			if (!preg_match('/\bWHERE\b/si', $sql)){
				$filterclause = ' WHERE '.implode('AND', $filters);
			} else {
				$filterclause = ' AND '. implode('AND', $filters);
			}
		}
	    $filteredsql = str_replace('<%%FILTERS%%>', $filterclause, $sql);
	} else {
		$filteredsql = str_replace('<%%FILTERS%%>', '', $sql);
	}
	$sql = str_replace('<%%FILTERS%%>', '', $sql); // needed to prepare for filter range prefetch
	
	$sort = optional_param('tsort', '', PARAM_TEXT);
	if (!empty($sort)){
		// do not sort if already sorted in explained query
		if (!preg_match('/ORDER\s+BY/si', $sql))
		    $filteredsql .= " ORDER BY $sort";
	}
	
	$filteredsql = $theBlock->protect($filteredsql);

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
		header("Content-Type:text/raw\n\n");
		header("Content-Disposition:filename={$exportname}.csv\n\n");

		// print column names
		foreach($output as $field => $label){
			$headrow[] = $label;
		}

		if (!$debug) ob_end_clean();		
		echo utf8_decode(implode($CFG->dashboard_csv_field_separator, $headrow)); 
		echo $CFG->dashboard_csv_line_separator;

		// print data
		foreach($results as $r){
			$row = array();
			foreach($output as $field => $label){

				// did we ask for cumulative results ? 
				$cumulativeix = null;
				if (preg_match('/S\((.+?)\)/', $field, $matches)){
					$field = $matches[1];
					$cumulativeix = $theBlock->instance->id.'_'.$field;
				}

				if (!empty($outputf[$field])){
					$datum = dashboard_format_data($outputf[$field], @$r->$field, $cumulativeix);
				} else {
					$datum = dashboard_format_data(null, @$r->$field, $cumulativeix);
				}
				$row[] = $datum;
			}
			echo utf8_decode(implode($CFG->dashboard_csv_field_separator, $row)); 
			echo $CFG->dashboard_csv_line_separator;
		}
	} else {
		echo "No results. Empty file";
	}


?>