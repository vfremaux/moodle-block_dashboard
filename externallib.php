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
 * External block dashboard API
 *
 * @package    block_dashboard
 * @category   external
 * @copyright  2016 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->libdir.'/blocklib.php');
require_once($CFG->dirroot.'/blocks/dashboard/classes/output/csv_renderer.php');

/**
 * Dasboard block external functions
 *
 * @package    block_dashboard
 * @category   external
 * @copyright  2016 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class block_dashboard_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function get_data_parameters() {
        return new external_function_parameters(
                array('blockid' => new external_value(PARAM_INT, 'block instance id'),
                      'filters' => new external_multiple_structure(new external_single_structure(
                            array(
                                'filterkey' => new external_value(PARAM_TEXT, 'a filter input key', VALUE_REQUIRED),
                                'fitlervalue' => new external_value(PARAM_TEXT, 'a filter input value', VALUE_REQUIRED)
                            )
                      ), 'Array filter key values', VALUE_DEFAULT, array())
            )
        );
    }

    /**
     * Get blocks data as a csv file content.
     *
     * @param int $courseid course id
     * @param array $options These options are not used yet, might be used in later version
     * @return array
     * @since Moodle 2.2
     */
    public static function get_data($blockid, $filters = null) {
        global $CFG, $DB, $PAGE;

        include_once($CFG->dirroot.'/blocks/dashboard/lib.php');

        // Validate parameters.
        $params = self::validate_parameters(self::get_data_parameters(),
                        array('blockid' => $blockid, 'filters' => $filters));

        // Retrieve the block instance.
        $blockrecord = $DB->get_record('block_instances', array('id' => $params['blockid']), '*', MUST_EXIST);
        $theblock = block_instance('dashboard', $blockrecord);

        $csvrenderer = $PAGE->get_renderer('block_dashboard', 'csv');

        $theblock->prepare_config();
        $theblock->prepare_params();
        // Fetch data.

        // Reassemble filters from WS input.
        $filterinput = null;
        if (!empty($filters)) {
            foreach ($filters as $filter) {
                $filterinput[$filter['filterkey']] = $filter['filtervalue'];
            }
        }

        if (!empty($theblock->config->filters)) {
            $theblock->prepare_filters($filterinput);
        } else {
            $theblock->filteredsql = str_replace('<%%FILTERS%%>', '', $theblock->sql);
        }
        // Needed to prepare for filter range prefetch.
        $theblock->sql = str_replace('<%%FILTERS%%>', '', $theblock->sql);

        if (!empty($theblock->params)) {
            $theblock->prepare_params();
        }

        $sort = optional_param('tsort'.$theblock->instance->id, '', PARAM_TEXT);
        if (!empty($sort)) {
            // Do not sort if already sorted in explained query.
            if (!preg_match('/ORDER\s+BY/si', $theblock->sql)) {
                $theblock->filteredsql .= " ORDER BY $sort";
            }
        }

        $filteredsql = $theblock->protect($theblock->filteredsql);

        $theblock->results = $theblock->fetch_dashboard_data($filteredsql, '', '', true); // Get all data.

        $data = array();
        $data['id'] = $theblock->instance->id;
        $data['data'] = $csvrenderer->export($theblock);
        return $data;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function get_data_returns() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Block ID'),
                'data' => new external_value(PARAM_TEXT, 'CSV data'),
            )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function get_raw_data_parameters() {
        return new external_function_parameters(
                array('blockid' => new external_value(PARAM_INT, 'block instance id')
                )
        );
    }

    /**
     * Get query result data as raw data in a single value.
     *
     * @param int $courseid course id
     * @param array $options These options are not used yet, might be used in later version
     * @return array
     * @since Moodle 2.2
     */
    public static function get_raw_data($blockid) {
        global $CFG, $DB, $PAGE;

        include_once($CFG->dirroot.'/blocks/dashboard/lib.php');

        // Validate parameters.
        $params = self::validate_parameters(self::get_data_parameters(),
                        array('blockid' => $blockid));

        // Retrieve the block instance.
        $blockrecord = $DB->get_record('block_instances', array('id' => $params['blockid']), '*', MUST_EXIST);
        $theblock = block_instance('dashboard', $blockrecord);

        $csvrenderer = $PAGE->get_renderer('block_dashboard', 'csv');

        $theblock->prepare_config();
        $theblock->prepare_params();
        // Fetch data.

        if (!empty($theblock->config->filters)) {
            $theblock->prepare_filters();
        } else {
            $theblock->filteredsql = str_replace('<%%FILTERS%%>', '', $theblock->sql);
        }
        // Needed to prepare for filter range prefetch.
        $theblock->sql = str_replace('<%%FILTERS%%>', '', $theblock->sql);

        if (!empty($theblock->params)) {
            $theblock->prepare_params();
        }

        $sort = optional_param('tsort'.$theblock->instance->id, '', PARAM_TEXT);
        if (!empty($sort)) {
            // Do not sort if already sorted in explained query.
            if (!preg_match('/ORDER\s+BY/si', $theblock->sql)) {
                $theblock->filteredsql .= " ORDER BY $sort";
            }
        }

        $filteredsql = $theblock->protect($theblock->filteredsql);

        $theblock->results = $theblock->fetch_dashboard_data($filteredsql, '', '', true); // Get all data.

        return $csvrenderer->export($theblock);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function get_raw_data_returns() {
        return new external_value(PARAM_TEXT, 'CSV data');
    }
}
