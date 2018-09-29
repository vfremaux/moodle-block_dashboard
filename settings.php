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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/dashboard/lib.php');

if ($ADMIN->fulltree) {

    $yesnooptions[0] = get_string('no');
    $yesnooptions[1] = get_string('yes');

    $settings->add(new admin_setting_heading('securityparams', get_string('securityparams', 'block_dashboard'),''));

    $key = 'block_dashboard/big_result_threshold';
    $label = get_string('dashboard_big_result_threshold', 'block_dashboard');
    $desc = get_string('dashboard_big_result_threshold_desc', 'block_dashboard');
    $settings->add(new admin_setting_configtext($key, $label, $desc, 500));

    $key = 'block_dashboard/enable_isediting_security';
    $label = get_string('dashboard_enable_isediting_security', 'block_dashboard');
    $desc = get_string('dashboard_enable_isediting_security_desc', 'block_dashboard');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, true));

    $settings->add(new admin_setting_heading('datarefresh', get_string('datarefresh', 'block_dashboard'), ''));

    $key = 'block_dashboard/cron_enabled';
    $label = get_string('dashboard_cron_enabled', 'block_dashboard');
    $desc = get_string('dashboard_cron_enabled_desc', 'block_dashboard');
    $settings->add(new admin_setting_configselect($key, $label, $desc, 1, $yesnooptions));

    $hkey = 'block_dashboard/cron_hour';
    $mkey = 'block_dashboard_cron_min';
    $label = get_string('dashboard_cron_hour', 'block_dashboard');
    $settings->add(new admin_setting_configtime($hkey, $mkey, $label, '', array('h' => 4, 'm' => 0)));

    $freq['daily'] = get_string('daily', 'block_dashboard');
    $freq['0'] = get_string('sunday', 'block_dashboard');
    $freq['1'] = get_string('monday', 'block_dashboard');
    $freq['2'] = get_string('tuesday', 'block_dashboard');
    $freq['3'] = get_string('wednesday', 'block_dashboard');
    $freq['4'] = get_string('thursday', 'block_dashboard');
    $freq['5'] = get_string('friday', 'block_dashboard');
    $freq['6'] = get_string('saturday', 'block_dashboard');

    $key = 'block_dashboard/cron_freq';
    $label = get_string('dashboard_cron_freq', 'block_dashboard');
    $desc = get_string('dashboard_cron_freq_desc', 'block_dashboard');
    $settings->add(new admin_setting_configselect($key, $label, $desc, 0, $freq));

    $sepoptions = array(',' => '"," '.get_string('coma', 'block_dashboard'),
                        ':' => '":" '.get_string('colon', 'block_dashboard'),
                        ';' => '";" '.get_string('semicolon', 'block_dashboard'),
                        "\t" => '[TAB] '.get_string('tab', 'block_dashboard'));
    $key = 'block_dashboard/csv_field_separator';
    $label = get_string('csvfieldseparator', 'block_dashboard');
    $desc = get_string('csvfieldseparator_desc', 'block_dashboard');
    $settings->add(new admin_setting_configselect($key, $label, $desc, ';', $sepoptions));

    $seplineoptions = array("\n" => 'Linux [LF]', "\r\n" => 'Windows [CRLF]', "\r" => 'MACOS [CR]');
    $key = 'block_dashboard/csv_line_separator';
    $label = get_string('csvlineseparator', 'block_dashboard');
    $desc = get_string('csvfieldseparator_desc', 'block_dashboard');
    $settings->add(new admin_setting_configselect($key, $label, $desc, "\n", $seplineoptions));

    $key = 'block_dashboard/cron_trace_on';
    $label = get_string('crontraceon', 'block_dashboard');
    $desc = get_string('crontraceon_desc', 'block_dashboard');
    $settings->add(new admin_setting_configcheckbox($key, $label, $desc, 0));

    if (block_dashboard_supports_feature('emulate/community') == 'pro') {
        // This will accept any.
        include_once($CFG->dirroot.'/blocks/dashboard/pro/prolib.php');
        \block_dashboard\pro_manager::add_settings($ADMIN, $settings);
    } else {
        $label = get_string('plugindist', 'block_dashboard');
        $desc = get_string('plugindist_desc', 'block_dashboard');
        $settings->add(new admin_setting_heading('plugindisthdr', $label, $desc));
    }
}