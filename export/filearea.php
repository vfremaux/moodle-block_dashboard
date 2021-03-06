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
require_once($CFG->dirroot.'/lib/filelib.php');
require_once($CFG->dirroot.'/blocks/dashboard/lib/file_browser.php');
require_once($CFG->dirroot.'/repository/lib.php');

$courseid = required_param('id', PARAM_INT);
$instanceid = required_param('instance', PARAM_INT);
$browsepath = optional_param('path', '/', PARAM_TEXT);
$action = optional_param('what', '', PARAM_TEXT);

if (!$course = $DB->get_record('course', array('id' => "$courseid"))) {
    print_error('badcourseid');
}

require_login($course);

if (!$instance = $DB->get_record('block_instances', array('id' => "$instanceid"))) {
    print_error('badblockinstance', 'block_dashboard');
}

$theblock = block_instance('dashboard', $instance);

$context = context_block::instance($instanceid);

$fs = get_file_storage();

if ($action == 'clear') {
    $fs->delete_area_files($context->id, 'block_dashboard', 'generated', $instanceid);
}

$PAGE->navbar->add(get_string('dashboards', 'block_dashboard'), null);
if (!empty($theblock->config->title)) {
    $PAGE->navbar->add($theblock->config->title, null);
}

$params = array('id' => $courseid, 'instance' => $instanceid);
$url = new moodle_url('/blocks/dashboard/export/filearea.php', $params);
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title($SITE->shortname);
$PAGE->set_heading($SITE->shortname);
$renderer = $PAGE->get_renderer('block_dashboard', 'csv');

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('dashboardstoragearea', 'block_dashboard'));

$browser = new dashboard_file_browser();

$filearea = null;
$itemid   = null;
$filename = null;
$path = array();
$files = array();
$dirs = array();
$template = new StdClass;

if ($fileinfo = $browser->get_file_info($context, 'block_dashboard', 'generated', $instanceid, $browsepath, null)) {

    // Build a Breadcrumb trail.
    $level = $fileinfo->get_parent();

    while ($level) {
        $upper = $level->get_parent();
        $path[] = ($upper) ? $level->get_visible_name() : '..';
        $level = $upper;
    }

    $path = array_reverse($path);
    $children = $fileinfo->get_children();

    foreach ($children as $child) {
        if ($child->is_directory()) {
            $dirs[] = $child;
        } else {
            $files[] = $child;
        }
    }

    $template->breadcrumb = '';
    if (!empty($path)) {
        $fullpath = '/';
        foreach ($path as $pel) {
            $fullpath .= $pel.'/';
            $template->breadcrumb .= ' &gt; <a href="'.$url.'&path='.urlencode($fullpath).'">'.$pel.'</a>';
        }
    } else {
        $template->breadcrumb = '/';
    }

    $template->exportdirs = array();
    foreach ($dirs as $dir) {
        $exportdir = new StdClass;
        $dirinfo = $dir->get_params();
        $exportdir->nodeiconurl = $OUTPUT->image_url('f/folder');
        $exportdir->url = $url.'&path='.$dirinfo['filepath'];
        $exportdir->name = $dir->get_visible_name();
        $template->exportdirs[] = $exportdir;
    }

    $template->exportfiles = array();
    foreach ($files as $file) {
        $info = $file->get_params();
        $exportfiletpl = new StdClass;
        $pluginfileurl = moodle_url::make_pluginfile_url($info['contextid'], $info['component'], $info['filearea'],
                                                         $info['itemid'], $info['filepath'], $info['filename']);
        $exportfiletpl->nodeiconurl = $OUTPUT->image_url(file_mimetype_icon($file->get_mimetype()));
        $exportfiletpl->url = $pluginfileurl;
        $exportfiletpl->name = $file->get_visible_name();
        // $exportfiletpl->filedate = strftime('%Y-%m-%d %H:%i:%s', $file->get_timecreated());
        $exportfiletpl->filedate = date('Y-m-d H:i:s', $file->get_timecreated());
        $template->exportfiles[] = $exportfiletpl;
    }
} else {
    $template->strnofiles = $OUTPUT->notification(get_string('nofiles', 'block_dashboard'));
}

echo $renderer->render_filearea($template);

if (($browsepath != '/') || $fileinfo){
    echo $OUTPUT->single_button($url.'&what=clear', get_string('cleararea', 'block_dashboard'));
}

$buttonurl = new moodle_url('/blocks/dashboard/view.php', array('id' => $courseid, 'blockid' => $instanceid));
echo $OUTPUT->single_button($buttonurl, get_string('backtoview', 'block_dashboard'));

$buttonurl = new moodle_url('/course/view.php', array('id' => $courseid));
echo $OUTPUT->single_button($buttonurl, get_string('backtocourse', 'block_dashboard'));

echo $OUTPUT->footer();
