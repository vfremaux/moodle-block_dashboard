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
<<<<<<< HEAD
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

	if (!$course = $DB->get_record('course', array('id' => "$courseid"))){
		print_error('badcourseid');
	}
	
	require_login($course);

	if (!$instance = $DB->get_record('block_instances', array('id' => "$instanceid"))){
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

	// tabular params
	$horizkey = @$theBlock->config->horizkey;
	$hlabel = @$theBlock->config->horizlabel;
	$vkeys = explode(";", @$theBlock->config->verticalkeys);
	$vformats = explode(";", @$theBlock->config->verticalformats);
	$vlabels = explode(";", @$theBlock->config->verticallabels);
	$theBlock->normalize($vkeys, $vformats); // normalizes formats to keys
	$theBlock->normalize($vkeys, $vlabels); // normalizes labels to keys
	$vertkeys->formats = array_combine($vkeys, $vformats);
	$vertkeys->labels = array_combine($vkeys, $vlabels);

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

	if (!empty($theBlock->config->filters)){
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
				if (!array_key_exists('filter'.$instanceid.'_'.$canonicalfilter, $filterinputs)) $filterinputs['filter'.$instanceid.'_'.$canonicalfilter] = $default;
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
						$filters[] = " $sqlfiltername = '".$value."' ";
					} else {
						if (count($value) > 1 || $value[0] != 0){
							$filters[] = " $sqlfiltername IN ('".implode("','", $value)."') ";
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

		$hcols = array();
		// print data
		foreach($results as $r){
			// this is a tabular table
			/* in a tabular table, data can be placed :
			* - in first columns in order of vertical keys
			* - in first columns in order of vertical keys
			* the results are grabbed sequentially and spread into the matrix 
			*/
			$keystack = array();
			$matrix = array();
			foreach(array_keys($vertkeys->formats) as $vkey){
				if (empty($vkey)) continue;
				$vkeyvalue = $r->$vkey;
				$matrix[] = "['".addslashes($vkeyvalue)."']";
			}
			$hkey = $horizkey;
			$hkeyvalue = (!empty($hkey)) ? $r->$hkey :  '' ;
			$matrix[] = "['".addslashes($hkeyvalue)."']";
			$matrixst = "\$m".implode($matrix);
			if (!in_array($hkeyvalue, $hcols)) $hcols[] = $hkeyvalue;
			
			// now put the cell value in it
			$outvalues = array();
			foreach($outputfields as $field){

				// did we ask for cumulative results ? 
				$cumulativeix = null;
				if (preg_match('/S\((.+?)\)/', $field, $matches)){
					$field = $matches[1];
					$cumulativeix = $theBlock->instance->id.'_'.$field;
				}

				if (!empty($outputf[$field])){
					$datum = dashboard_format_data($outputf[$field], $r->$field, $cumulativeix);
				} else {
					$datum = dashboard_format_data(null, @$r->$field, $cumulativeix);
				}
				/*
				// no colour possible that way in excel
				if (!empty($theBlock->config->colorfield) && $theBlock->config->colorfield == $field){
					$datum = dashboard_colour_code($theBlock, $datum, $colorcoding);
				}
				*/
				$outvalues[] = str_replace('"', '\\"', $datum);
			}
			$matrixst .= ' = "'.implode(' ',$outvalues).'"';
			
			// make the matrix in memory
			eval($matrixst.";");
			
		}

		print_cross_table_csv($theBlock, $m, $hcols, $horizkey, $vertkeys, $hlabel, true);					
		
		echo $CFG->dashboard_csv_line_separator;
	} else {
		echo "No results. Empty file";
	}
?>
=======
 * @package    block_dashboard
 * @category   blocks
 * @author  Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *  Exporter of dashboard data snapshot
 */

require('../../../config.php');

$debug = optional_param('debug', false, PARAM_BOOL);
if (!$debug) {
    // Needs buffering for a really clean file output.
    ob_start();
} else {
    echo "<pre>Debugging mode\n";
}

$config = get_config('block_dashboard');

$courseid = required_param('id', PARAM_INT); // The course ID.
$instanceid = required_param('instance', PARAM_INT); // The block ID.
$output = optional_param('output', 'csv', PARAM_ALPHA); // Output format (csv).
$limit = optional_param('limit', '', PARAM_INT);
$offset = optional_param('offset', '', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => "$courseid"))) {
    print_error('badcourseid');
}

require_login($course);

if (!$instance = $DB->get_record('block_instances', array('id' => "$instanceid"))) {
    print_error('badblockinstance', 'block_dashboard');
}

$theblock = block_instance('dashboard', $instance);

// Prepare data for tables.

$theblock->prepare_config();

if (!empty($theblock->config->filters)) {
    $theblock->prepare_filters();
} else {
    $theblock->filteredsql = str_replace('<%%FILTERS%%>', '', $theblock->sql);
}

$theblock->sql = str_replace('<%%FILTERS%%>', '', $theblock->sql); // Needed to prepare for filter range prefetch.

if (!empty($theblock->params)) {
    $theblock->prepare_params();
} else {
    $theblock->filteredsql = str_replace('<%%PARAMS%%>', '', $theblock->filteredsql);
}
$theblock->sql = str_replace('<%%PARAMS%%>', '', $theblock->sql); // Needed to prepare for filter range prefetch.

$sort = optional_param('tsort'.$theblock->instance->id, '', PARAM_TEXT);

if (!empty($sort)) {
    // Do not sort if already sorted in explained query.
    if (!preg_match('/ORDER\s+BY/si', $theblock->sql)) {
        $theblock->filteredsql .= " ORDER BY $sort";
    }
}

$filteredsql = $theblock->protect($theblock->filteredsql);

$results = $theblock->fetch_dashboard_data($filteredsql, '', '', true); // get all data

if ($results) {
    // Output csv file.
    $exportname = (!empty($theblock->config->title)) ? clean_filename($theblock->config->title) : 'dashboard_export' ;
    header("Content-Type:text/csv\n\n");
    header("Content-Disposition:filename={$exportname}.csv\n\n");

    $hcols = array();
    // Print data.
    foreach ($results as $r) {
        // This is a tabular table.
        /*
         * in a tabular table, data can be placed :
         * - in first columns in order of vertical keys
         * - in first columns in order of vertical keys
         * the results are grabbed sequentially and spread into the matrix 
         */
        $keystack = array();
        $matrix = array();

        foreach (array_keys($theblock->vertkeys->formats) as $vkey) {
            if (empty($vkey)) {
                continue;
            }
            $vkeyvalue = $r->$vkey;
            $matrix[] = "['".addslashes($vkeyvalue)."']";
        }

        $hkey = $theblock->config->horizkey;
        $hkeyvalue = (!empty($hkey)) ? $r->$hkey :  '';
        $matrix[] = "['".addslashes($hkeyvalue)."']";
        $matrixst = "\$m".implode($matrix);

        if (!in_array($hkeyvalue, $hcols)) {
            $hcols[] = $hkeyvalue;
        }

        // Now put the cell value in it.
        $outvalues = array();
        foreach (array_keys($theblock->outputf) as $field) {

            // Did we ask for cumulative results ?
            $cumulativeix = null;
            if (preg_match('/S\((.+?)\)/', $field, $matches)) {
                $field = $matches[1];
                $cumulativeix = $theblock->instance->id.'_'.$field;
            }

            if (!empty($theblock->outputf[$field])){
                $datum = dashboard_format_data($theblock->outputf[$field], $r->$field, $cumulativeix);
            } else {
                $datum = dashboard_format_data(null, @$r->$field, $cumulativeix);
            }

            if (!empty($datum)) {
                $outvalues[] = str_replace('"', '\\"', $datum);
            }
        }
        $matrixst .= ' = "'.implode(' ',$outvalues).'"';

        // Make the matrix in memory.
        eval($matrixst.";");
    }

    $csvrenderer = $PAGE->get_renderer('block_dashboard', 'csv');
    $str = $csvrenderer->cross_table_csv($theblock, $m, $hcols);

    if ($theblock->config->exportcharset == 'utf8') {
        echo utf8_decode($str); 
    } else {
        echo $str;
    }

    echo $config->csv_line_separator;
} else {
    echo "No results. Empty file";
}
>>>>>>> MOODLE_33_STABLE
