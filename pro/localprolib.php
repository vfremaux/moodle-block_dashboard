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
 * @package     block_dashboard
 * @categroy    blocks
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_dashboard;
require_once($CFG->dirroot.'/blocks/dashboard/lib.php');

use \admin_setting_configtext;
use \admin_setting_heading;

defined('MOODLE_INTERNAL') || die();

final class local_pro_manager {

    private static $component = 'block_dashboard';
    private static $componentpath = 'blocks/dashboard';

    /**
     * this adds additional settings to the component settings (generic part of the prolib system).
     * @param objectref &$admin
     * @param objectref &$settings
     */
    public static function add_settings(&$admin, &$settings) {
        global $CFG, $PAGE;

        $yesnooptions[0] = get_string('no');
        $yesnooptions[1] = get_string('yes');

        if (block_dashboard_supports_feature('data/extrapostgresource')) {

            $settings->add(new admin_setting_heading('extradbparams', get_string('extradbparams', 'block_dashboard'), ''));

            $key = 'block_dashboard/extra_db_host';
            $label = get_string('dashboard_extra_db_host', 'block_dashboard');
            $desc = get_string('dashboard_extra_db_host_desc', 'block_dashboard');
            $settings->add(new admin_setting_configtext($key, $label, $desc, @$CFG->dashboard_extra_db_host));

            $key = 'block_dashboard/extra_db_port';
            $label = get_string('dashboard_extra_db_port', 'block_dashboard');
            $desc = get_string('dashboard_extra_db_port_desc', 'block_dashboard');
            $settings->add(new admin_setting_configtext($key, $label, $desc, @$CFG->dashboard_extra_db_port));

            $key = 'block_dashboard/extra_db_db';
            $label = get_string('dashboard_extra_db_db', 'block_dashboard');
            $desc = get_string('dashboard_extra_db_db_desc', 'block_dashboard');
            $settings->add(new admin_setting_configtext($key, $label, $desc, ''));

            $key = 'block_dashboard/extra_db_user';
            $label = get_string('dashboard_extra_db_user', 'block_dashboard');
            $desc = get_string('dashboard_extra_db_user_desc', 'block_dashboard');
            $settings->add(new admin_setting_configtext($key, $label, $desc, ''));

            $key = 'block_dashboard/extra_db_password';
            $label = get_string('dashboard_extra_db_password', 'block_dashboard');
            $desc = get_string('dashboard_extra_db_password_desc', 'block_dashboard');
            $settings->add(new admin_setting_configtext($key, $label, $desc, ''));
        }

    }
}