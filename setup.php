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
 * @author Valery Fremaux (valery@club-internet.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
require('../../config.php');
require_once($CFG->dirroot.'/blocks/dashboard/block_dashboard.php');

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
$theBlock = block_instance('dashboard', $instance);
$context = context_block::instance($theBlock->instance->id);
require_capability('block/dashboard:configure', $context);

if (($submit = optional_param('submit','', PARAM_TEXT)) || ($save = optional_param('save','', PARAM_TEXT)) || ($saveview = optional_param('saveview','', PARAM_TEXT))) {
    include $CFG->dirroot.'/blocks/dashboard/setup.controller.php';
}

$PAGE->navbar->add(get_string('dashboards', 'block_dashboard'), NULL);
$blocktitle = (empty($theBlock->config->title)) ? get_string('pluginname', 'block_dashboard') : $theBlock->config->title ;
$PAGE->navbar->add($blocktitle);
$PAGE->navbar->add(get_string('setup', 'block_dashboard'));
$PAGE->set_url(new moodle_url('/blocks/dashboard/view.php', array('id' => $courseid, 'blockid' => $blockid)));
$PAGE->set_title($SITE->shortname);
$PAGE->set_heading($SITE->shortname);

$renderer = $PAGE->get_renderer('block_dashboard');

echo $OUTPUT->header();

echo $OUTPUT->box_start();

echo '<form name="setup" action="#" method="post">';

include $CFG->dirroot.'/blocks/dashboard/setup_instance.html';

echo '</form>';

echo $OUTPUT->box_end();
echo $OUTPUT->footer();
