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
 *
 *  This generates the output file using output extraction parameters on filtered data
 */

require('../../../config.php');

$debug = optional_param('debug', false, PARAM_BOOL);

require_once($CFG->dirroot.'/blocks/dashboard/lib.php');

$courseid = required_param('id', PARAM_INT); // The course ID.
$instanceid = required_param('instance', PARAM_INT); // The block ID.

if (!$course = $DB->get_record('course', array('id' => "$courseid"))) {
    print_error('badcourseid');
}

require_login($course);

$context = context_block::instance($instanceid);

$PAGE->navbar->add(get_string('dashboards', 'block_dashboard'), null);
if (!empty($theblock->config->title)) {
    $PAGE->navbar->add($theblock->config->title, null);
}
$PAGE->set_url(new moodle_url('/blocks/dashboard/export/export_output_csv.php', array('id' => $courseid, 'instance' => $instanceid)));
$PAGE->set_title($SITE->shortname);
$PAGE->set_heading($SITE->shortname);

echo $OUTPUT->header();

if (!$instance = $DB->get_record('block_instances', array('id' => "$instanceid"))) {
    print_error('badblockinstance', 'block_dashboard');
}

$theblock = block_instance('dashboard', $instance);

$theblock->prepare_config();

// Fetch data.

if (!empty($theblock->config->filters)) {
    $theblock->prepare_filters();
} else {
    $theblock->filteredsql = str_replace('<%%FILTERS%%>', '', $theblock->sql);
}

if (!empty($theblock->params)) {
    $theblock->prepare_params();
} else {
    $theblock->filteredsql = str_replace('<%%PARAMS%%>', '', $theblock->filteredsql);
}

$sort = optional_param('tsort'.$theblock->instance->id, '', PARAM_TEXT);
if (!empty($sort)) {
    // Do not sort if already sorted in explained query.
    if (!preg_match('/ORDER\s+BY/si', $theblock->sql)) {
        $theblock->filteredsql .= " ORDER BY $sort";
    }
}

$filteredsql = $theblock->protect($theblock->filteredsql);

$results = $theblock->fetch_dashboard_data($filteredsql, '', '', true); // Get all data.

if ($results) {
    echo '<pre>';
    $theblock->generate_output_file($results);
    echo '</pre>';
    echo $OUTPUT->notification(get_string('filegenerated', 'block_dashboard'));
    if (empty($theblock->config->filepathadminoverride)) {
        $params = array('id' => $courseid, 'instance' => $instanceid);
        $buttonurl = new moodle_url('/blocks/dashboard/export/filearea.php', $params);
        echo $OUTPUT->single_button($buttonurl, get_string('filesview', 'block_dashboard'));
    }
    $buttonurl = new moodle_url('/course/view.php', array('id' => $courseid));
    echo $OUTPUT->single_button($buttonurl, get_string('backtocourse', 'block_dashboard'));
} else {
    echo "No results. Empty file";
}

echo $OUTPUT->footer();
