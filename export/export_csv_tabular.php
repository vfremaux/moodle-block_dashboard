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

$results = [];
$theblock->fetch_dashboard_data($filteredsql, $results, '', true); // get all data

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
        $matrixst .= ' = "'.implode(' ', $outvalues).'"';

        // Add a matrix cell in php memory.
		if (function_exists('debug_trace')) {
	        debug_trace($matrixst, TRACE_DEBUG);
	    }
        eval($matrixst.";");
    }

	if (function_exists('debug_trace')) {
	    debug_trace("Final Matrix", TRACE_DATA);
	    debug_trace($m, TRACE_DATA);
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
