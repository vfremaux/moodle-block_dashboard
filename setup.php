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
 * @package block_dashboard
 * @category blocks
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
require('../../config.php');
require_once($CFG->dirroot.'/blocks/dashboard/block_dashboard.php');
require_once($CFG->dirroot.'/blocks/dashboard/lib.php');

$PAGE->requires->jquery();
$PAGE->requires->js('/blocks/dashboard/js/module.js', true);

$courseid = required_param('id', PARAM_INT);
$blockid = required_param('instance', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}

if (!$instance = $DB->get_record('block_instances', array('id' => $blockid))) {
    print_error('invalidblockid');
}

// Security.

require_login($course);
$theblock = block_instance('dashboard', $instance);
$context = context_block::instance($theblock->instance->id);
require_capability('block/dashboard:configure', $context);

if (($submit = optional_param('submit','', PARAM_TEXT)) ||
        ($save = optional_param('save','', PARAM_TEXT)) ||
                ($saveview = optional_param('saveview','', PARAM_TEXT))) {
    include($CFG->dirroot.'/blocks/dashboard/setup.controller.php');
}

$PAGE->navbar->add(get_string('dashboards', 'block_dashboard'), null);
$blocktitle = (empty($theblock->config->title)) ? get_string('pluginname', 'block_dashboard') : $theblock->config->title ;
$PAGE->navbar->add($blocktitle);
$PAGE->navbar->add(get_string('setup', 'block_dashboard'));
$PAGE->set_url(new moodle_url('/blocks/dashboard/view.php', array('id' => $courseid, 'blockid' => $blockid)));
$PAGE->set_title($SITE->shortname);
$PAGE->set_heading($SITE->shortname);
$PAGE->set_pagelayout('admin');
$PAGE->requires->js_call_amd('block_dashboard/setup', 'init');

if (block_dashboard_supports_feature('emulate/community') == 'pro') {
    include_once($CFG->dirroot.'/blocks/dashboard/pro/classes/output/setup_renderer.php');
    $renderer = new \block_dashboard\output\setup_pro_renderer($PAGE, 'html');
} else {
    $renderer = $PAGE->get_renderer('block_dashboard', 'setup');
}
$renderer->set_block($theblock);

echo $OUTPUT->header();

echo $OUTPUT->box_start();

echo '<form name="setup" action="#" method="post">';

if (!isset($theblock->config)) {
    $theblock->config = new StdClass();
}

if (!isset($theblock->config->target)) {
    $theblock->config->target = 'moodle';
}
if (!isset($theblock->config->hidetitle)) {
    $theblock->config->hidetitle = 0;
}
if (!isset($theblock->config->showdata)) {
    $theblock->config->showdata = 1;
}
if (!isset($theblock->config->showgraph)) {
    $theblock->config->showgraph = 1;
}
if (!isset($theblock->config->shownumsums)) {
    $theblock->config->shownumsums = 1;
}
if (!isset($theblock->config->showquery)) {
    $theblock->config->showquery = 0;
}
if (!isset($theblock->config->showfilterqueries)) {
    $theblock->config->showfilterqueries = 0;
}
if (!isset($theblock->config->inblocklayout)) {
    $theblock->config->inblocklayout = ($COURSE->format == 'page') ? 1 : 0;
}

echo $renderer->form_header();
echo $renderer->layout();
echo $renderer->setup_tabs();
echo $renderer->query_description();
echo $renderer->query_params();
echo $renderer->output_params();
echo $renderer->tabular_params();
if (block_dashboard_supports_feature('data/treeview')) {
    echo $renderer->treeview_params();
}
echo $renderer->sums_and_filters();
echo $renderer->graph_params();
if (block_dashboard_supports_feature('graph/google')) {
    echo $renderer->google_params();
}
if (block_dashboard_supports_feature('graph/timeline')) {
    echo $renderer->timeline_params();
}
if (block_dashboard_supports_feature('result/colouring')) {
    echo $renderer->tablecolor_mapping();
}
echo $renderer->data_refresh();
echo $renderer->file_output();
echo $renderer->setup_returns($theblock);

echo '</form>';

echo $OUTPUT->box_end();
echo $OUTPUT->footer();
