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
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *  Exporter of dashboard data snapshot
 */

require('../../../config.php');

$debug = optional_param('debug', false, PARAM_BOOL);
if (!$debug){
    // needs buffering for a really clean file output
    ob_start();
} else {
    echo "<pre>Debugging mode\n";
}

$config = get_config('block_dashboard');

require_once $CFG->dirroot.'/blocks/dashboard/lib.php';

$courseid = required_param('id', PARAM_INT); // the course ID
$instanceid = required_param('instance', PARAM_INT); // the block ID
$output = optional_param('output', 'csv', PARAM_ALPHA); // output format (csv)
$limit = optional_param('limit', '', PARAM_INT);
$offset = optional_param('offset', '', PARAM_INT); 
$alldata = optional_param('alldata', '', PARAM_INT); 

if (!$course = $DB->get_record('course', array('id' => "$courseid"))) {
    print_error('badcourseid');
}

// Security.

require_login($course);

if (!$instance = $DB->get_record('block_instances', array('id' => "$instanceid"))){
    print_error('badblockinstance', 'block_dashboard');
}

$theBlock = block_instance('dashboard', $instance);

$theBlock->prepare_config();
// $theBlock->prepare_filters();
$theBlock->prepare_params();

/// fetch data

if (!empty($theBlock->config->filters)) {
    $theBlock->prepare_filters();
} else {
    $theBlock->filteredsql = str_replace('<%%FILTERS%%>', '', $theBlock->sql);
}
$theBlock->sql = str_replace('<%%FILTERS%%>', '', $theBlock->sql); // needed to prepare for filter range prefetch

if (!empty($theBlock->params)){
    $theBlock->prepare_params();
}

$sort = optional_param('tsort'.$theBlock->instance->id, '', PARAM_TEXT);
if (!empty($sort)) {
    // do not sort if already sorted in explained query
    if (!preg_match('/ORDER\s+BY/si', $theBlock->sql))
        $theBlock->filteredsql .= " ORDER BY $sort";
}

$filteredsql = $theBlock->protect($theBlock->filteredsql);

$results = $theBlock->fetch_dashboard_data($filteredsql, '', '', true); // get all data

if ($results) {
    // Output csv file
    $exportname = (!empty($theBlock->config->title)) ? clean_filename($theBlock->config->title) : 'dashboard_export' ;
    header("Content-Type:text/csv\n\n");
    header("Content-Disposition:filename={$exportname}.csv\n\n");

    // Print column names.
    $headrow = array();
    foreach($theBlock->output as $field => $label){
        $headrow[] = $label;
    }

    if (!$debug) ob_end_clean();
    if ($theBlock->config->exportcharset == 'utf8') {
        echo utf8_decode(implode($config->csv_field_separator, $headrow)); 
    } else {
        echo implode($config->csv_field_separator, $headrow); 
    }
    echo $config->csv_line_separator;

    // print data
    foreach ($results as $r) {
        $row = array();
        foreach ($theBlock->output as $field => $label) {

            // did we ask for cumulative results ? 
            $cumulativeix = null;
            if (preg_match('/S\((.+?)\)/', $field, $matches)) {
                $field = $matches[1];
                $cumulativeix = $theBlock->instance->id.'_'.$field;
            }

            if (!empty($theBlock->outputf[$field])) {
                $datum = dashboard_format_data($theBlock->outputf[$field], @$r->$field, $cumulativeix);
            } else {
                $datum = dashboard_format_data(null, @$r->$field, $cumulativeix);
            }
            $row[] = $datum;
        }
        if ($theBlock->config->exportcharset == 'utf8') {
            echo utf8_decode(implode($config->csv_field_separator, $row));
        } else {
            echo implode($config->csv_field_separator, $row);
        }
        echo $config->csv_line_separator;
    }
} else {
    echo "No results. Empty file";
}
