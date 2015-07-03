<?php

/**
*  Exporter of dashboard data snapshot
*
*
*/
// needs buffering for a really clean file output

require('../../../config.php');

$debug = optional_param('debug', false, PARAM_BOOL);
if (!$debug){
    ob_start();
} else {
    echo "<pre>Debugging mode\n";
}

$config = get_config('block_dahsboard');

$courseid = required_param('id', PARAM_INT); // the course ID
$instanceid = required_param('instance', PARAM_INT); // the block ID
$output = optional_param('output', 'csv', PARAM_ALPHA); // output format (csv)
$limit = optional_param('limit', '', PARAM_INT);
$offset = optional_param('offset', '', PARAM_INT); 

if (!$course = $DB->get_record('course', array('id' => "$courseid"))) {
    print_error('badcourseid');
}

require_login($course);

if (!$instance = $DB->get_record('block_instances', array('id' => "$instanceid"))) {
    print_error('badblockinstance', 'block_dashboard');
}

$theBlock = block_instance('dashboard', $instance);

// prepare data for tables

$theBlock->prepare_config();

if (!empty($theBlock->config->filters)){
    $theBlock->prepare_filters();
} else {
    $theBlock->filteredsql = str_replace('<%%FILTERS%%>', '', $theBlock->sql);
}
$theBlock->sql = str_replace('<%%FILTERS%%>', '', $theBlock->sql); // needed to prepare for filter range prefetch

if (!empty($theBlock->params)){
    $theBlock->prepare_params();
} else {
    $theBlock->filteredsql = str_replace('<%%PARAMS%%>', '', $theBlock->filteredsql);
}
$theBlock->sql = str_replace('<%%PARAMS%%>', '', $theBlock->sql); // needed to prepare for filter range prefetch

$sort = optional_param('tsort'.$theBlock->instance->id, '', PARAM_TEXT);
if (!empty($sort)) {
    // do not sort if already sorted in explained query
    if (!preg_match('/ORDER\s+BY/si', $theBlock->sql)) {
        $theBlock->filteredsql .= " ORDER BY $sort";
    }
}

$filteredsql = $theBlock->protect($theBlock->filteredsql);

$results = $theBlock->fetch_dashboard_data($filteredsql, '', '', true); // get all data

if ($results) {
    // Output csv file.
    $exportname = (!empty($theBlock->config->title)) ? clean_filename($theBlock->config->title) : 'dashboard_export' ;
    header("Content-Type:text/csv\n\n");
    header("Content-Disposition:filename={$exportname}.csv\n\n");

    $hcols = array();
    // print data
    foreach ($results as $r) {
        // this is a tabular table
        /* in a tabular table, data can be placed :
        * - in first columns in order of vertical keys
        * - in first columns in order of vertical keys
        * the results are grabbed sequentially and spread into the matrix 
        */
        $keystack = array();
        $matrix = array();

        foreach (array_keys($theBlock->vertkeys->formats) as $vkey) {
            if (empty($vkey)) {
                continue;
            }
            $vkeyvalue = $r->$vkey;
            $matrix[] = "['".addslashes($vkeyvalue)."']";
        }

        $hkey = $theBlock->config->horizkey;
        $hkeyvalue = (!empty($hkey)) ? $r->$hkey :  '' ;
        $matrix[] = "['".addslashes($hkeyvalue)."']";
        $matrixst = "\$m".implode($matrix);

        if (!in_array($hkeyvalue, $hcols)) {
            $hcols[] = $hkeyvalue;
        }

        // now put the cell value in it
        $outvalues = array();
        foreach (array_keys($theBlock->outputf) as $field) {

            // did we ask for cumulative results ? 
            $cumulativeix = null;
            if (preg_match('/S\((.+?)\)/', $field, $matches)) {
                $field = $matches[1];
                $cumulativeix = $theBlock->instance->id.'_'.$field;
            }

            if (!empty($theBlock->outputf[$field])) {
                $datum = dashboard_format_data($theBlock->outputf[$field], $r->$field, $cumulativeix);
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

    $str = print_cross_table_csv($theBlock, $m, $hcols, true);

    if ($theBlock->config->exportcharset == 'utf8'){
        $str = utf8_decode($str); 
    }

    dashboard_output_file($theBlock, $str);

} else {
    echo "No results. Empty file";
}
