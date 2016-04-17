<?php
// This file keeps track of upgrades to 
// the dashboard block
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

/**
 * Definition of block dashboard scheduled tasks.
 *
 * @package   block_dashboard
 * @category  blocks
 * @author    Valery Fremaux <valery.fremaux@gmail.com>, <valery@edunao.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_block_dashboard_upgrade($oldversion=0) {
    global $CFG, $DB;

    $result = true;

    // Moodle 2.0 break line

    return $result;
}