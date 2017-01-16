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
defined('MOODLE_INTERNAL') || die();

$functions = array(

    'block_dashboard_get_data' => array(
        'classname' => 'block_dashboard_external',
        'methodname' => 'get_data',
        'classpath' => 'blocks/dashboard/externallib.php',
        'description' => 'Returns the extracted data from the query associated to the block instance.',
        'type' => 'read',
        'capabilities' => 'block/dashboard:export'
    ),

    'block_dashboard_get_raw_data' => array(
        'classname' => 'block_dashboard_external',
        'methodname' => 'get_raw_data',
        'classpath' => 'blocks/dashboard/externallib.php',
        'description' => 'Returns the extracted data from the query associated to the block instance in a raw scalar form.',
        'type' => 'read',
        'capabilities' => 'block/dashboard:export'
    ),

);
