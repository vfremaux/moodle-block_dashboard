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
require_once($CFG->dirroot.'/blocks/dashboard/lib.php');
require_once($CFG->dirroot.'/blocks/dashboard/classes/output/csv_renderer.php');

$debug = optional_param('debug', false, PARAM_BOOL);
if (!$debug) {
    // Needs buffering for a really clean file output.
    ob_start();
} else {
    echo "<pre>Debugging mode\n";
}

$config = get_config('block_dashboard');

require_once $CFG->dirroot.'/blocks/dashboard/lib.php';

$courseid = required_param('id', PARAM_INT); // The course ID.
$instanceid = required_param('instance', PARAM_INT); // The block ID.
$output = optional_param('output', 'csv', PARAM_ALPHA); // Output format (csv).
$limit = optional_param('limit', '', PARAM_INT);
$offset = optional_param('offset', '', PARAM_INT);

// Not directly used here. @see block_dashboard@prepare_filters().
$alldata = optional_param('alldata', '', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => "$courseid"))) {
    print_error('badcourseid');
}

if (!$instance = $DB->get_record('block_instances', array('id' => "$instanceid"))) {
    print_error('badblockinstance', 'block_dashboard');
}

$theblock = block_instance('dashboard', $instance);

// Security.

require_login($course);

$theblock->prepare_config();
if (!empty($theblock->params)) {
    $theblock->prepare_params();
} else {
    $theblock->filteredsql = str_replace('<%%PARAMS%%>', '', $theblock->sql);
    $theblock->sql = str_replace('<%%PARAMS%%>', '', $theblock->sql); // Needed to prepare for filter range prefetch.
}

// Fetch data.

if (!empty($theblock->config->filters)) {
    $theblock->prepare_filters($_POST);
} else {
    $theblock->filteredsql = str_replace('<%%FILTERS%%>', '', $theblock->sql);
}
$theblock->sql = str_replace('<%%FILTERS%%>', '', $theblock->sql); // Needed to prepare for filter range prefetch.

$sort = optional_param('tsort'.$theblock->instance->id, '', PARAM_TEXT);
if (!empty($sort)) {
    // Do not sort if already sorted in explained query.
    if (!preg_match('/ORDER\s+BY/si', $theblock->sql)) {
        $theblock->filteredsql .= " ORDER BY $sort";
    }
}

$filteredsql = $theblock->protect($theblock->filteredsql);
$theblock->fetch_dashboard_data($filteredsql, $theblock->results, '', '', true); // Get all data.

if ($theblock->results) {
    // Output csv file.
    $exportname = (!empty($theblock->config->title)) ? clean_filename($theblock->config->title) : 'dashboard_export' ;

    if (!$debug) {
        ob_end_clean();
    }
    header("Content-Type:text/csv\n\n");
    header("Content-Disposition:filename={$exportname}.csv\n\n");
    $csvrenderer = $PAGE->get_renderer('block_dashboard', 'csv');
    $csvdata = $csvrenderer->export($theblock);
    echo $csvdata;

} else {
    echo "No results. Empty file";
}
