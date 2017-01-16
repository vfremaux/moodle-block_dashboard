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
 */
defined('MOODLE_INTERNAL') || die();

$data = $_POST;

if (array_key_exists('submit', $_POST)) {

    unset($data['submit']);

    $theblock->config = (object) $data;
    $theblock->instance_config_save($theblock->config);

    redirect(new moodle_url('/course/view.php', array('id' => $COURSE->id)));
}

if ($save) {

    unset($data['save']);

    $theblock->config = (object) $data;
    $theblock->instance_config_save($theblock->config);

    redirect(new moodle_url('/blocks/dashboard/setup.php', array('id' => $COURSE->id, 'instance' => $blockid)));
}

if ($saveview) {

    unset($data['save']);

    $theblock->config = (object) $data;
    $theblock->instance_config_save($theblock->config);

    redirect(new moodle_url('/blocks/dashboard/view.php', array('id' => $COURSE->id, 'blockid' => $blockid)));
}