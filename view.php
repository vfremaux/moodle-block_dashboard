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
 */

require('../../config.php');

$courseid = required_param('id', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}

if (!$instance = $DB->get_record('block_instances', array('id' => $blockid))) {
    print_error('invalidblockid');
}

// Security.

require_login($course);
$theblock = block_instance('dashboard', $instance);
$theblock->get_required_javascript();
$renderer = $PAGE->get_renderer('block_dashboard');
$context = context_block::instance($theblock->instance->id);

$PAGE->navbar->add(get_string('dashboards', 'block_dashboard'), null);

if (!empty($theblock->config->title)) {
    $PAGE->navbar->add($theblock->config->title, null);
}

$PAGE->set_url(new moodle_url('/blocks/dashboard/view.php', array('id' => $courseid, 'blockid' => $blockid)));
$PAGE->set_title($SITE->shortname);
$PAGE->set_heading($SITE->shortname);
echo $OUTPUT->header();

echo $OUTPUT->heading($theblock->get_title());

echo $OUTPUT->box_start();

echo $renderer->render_dashboard($theblock);

if (has_capability('block/dashboard:configure', $context) && $PAGE->user_is_editing()) {
    $options = array();
    $options['id'] = $courseid;
    $options['instance'] = $blockid;
    echo '<div class="configure">';
    $buttonurl = new moodle_url('/blocks/dashboard/setup.php', $options);
    echo $OUTPUT->single_button($buttonurl, get_string('configure', 'block_dashboard'), 'get');
    echo '</div>';
    echo "<br/>";
}

echo $OUTPUT->box_end();
echo "<br/>";
echo '<center>';
$options = array();
$options['id'] = $courseid;
$buttonurl = new moodle_url('/course/view.php', $options);
echo $OUTPUT->single_button($buttonurl, get_string('backtocourse', 'block_dashboard'), 'get');
echo '</center>';
echo "<br/>";
echo $OUTPUT->footer($course);