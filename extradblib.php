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

/**
 * Software environement wrappers
 *
 * This set of functions define wrappers to environemental usefull utilities
 * such fetching central configuration values or giving error feedback to environment
 *
 * Implementation of these fucntion assume central libs of the applciation are loaded
 * and full generic API is available.
 */

// Protect eventually against unwanted accesses.
extra_db_protect();

function extra_db_protect() {
    // TODO : implement environment dependant code here.

    // Example : Moodle 1.9 environement : simplest way, non internationalised.
    if (!defined('MOODLE_INTERNAL')) {
        die ('You cannot use this library this way');
    }
}

function extra_db_debugging() {
    // TODO : implement environment dependant code here

    // Example : Moodle 1.9 environement : simplest way, non internationalised.
    return debugging();
}

/**
 * wraps to environement fatal error reporting function
 */
function extra_db_error($error, $return = false) {
    // TODO : implement environment dependant code here.

    // Example : Moodle 1.9 environement : simplest way, non internationalised.
    if ($return) {
        return '<span class="error">'.$error.'</span>';
    }
    echo($error);
}

/**
 * wraps to environement non fatal error reporting function (debugging)
 */
function extra_db_notify($error) {
    global $OUTPUT;

    // TODO : implement environment dependant code here

    // Example : Moodle 1.9 environement : simplest way, non internationalised.
    $OUTPUT->notification($error);
}

/**
 *
 */
function extra_db_get_config($configkey) {
    // TODO : implement environment dependant code here.

    // Example : Moodle 2 environement.
    $config = get_config('block_dashboard');

    return @$config->$configkey;
}

/**
 *
 */
function extra_db_set_config($configkey, $value) {
    // TODO : implement environment dependant code here.

    // Example : Moodle 2 environement.
    set_config($configkey, $value, 'block_dashboard');
}

// ########################

/**
 * connects to ERP database on a new postgre connection
 */
function extra_db_connect($return = false, &$error) {
    global $extradbcnx;

    $extra_dbport = extra_db_get_config('extra_db_port');
    if (empty($extra_dbport)) {
        extra_db_set_config('extra_db_port', 5432); // Default port for PostGre.
    }

    $extra_dbhost = extra_db_get_config('extra_dbhost');
    if (empty($extra_dbhost)) {
        extra_db_set_config('extra_db_host', 'localhost'); // Default host for PostGre.
    }

    $extra_dbdb = extra_db_get_config('extra_db_db');
    if (empty($extra_dbdb)) {
        extra_db_error("extra_db needs a DB"); // Default host for PostGre.
    }

    $cnxstring = ' host='.extra_db_get_config('extra_db_host').' port='.extra_db_get_config('extra_db_port').' ';
    $cnxstring .= 'dbname='.extra_db_get_config('extra_db_db').' user='.extra_db_get_config('extra_db_user').' ';
    $cnxstring .= 'password='.extra_db_get_config('extra_db_password');

    if (!$extradbcnx) {
        if (!$extradbcnx = pg_connect($cnxstring)) {
            $error = extra_db_error("Cannot connect to extra_db Database", $return);
            return false;
        }
    }
    return true;
}

/**
 * closes to ERP database on a new postgre connection
 */
function extra_db_close() {
    global $extradbcnx;

    if ($extradbcnx) {
        pg_close($extradbcnx);
    }
}

/**
 *
 */
function extra_db_query($sql, $renew = false, $return = false, &$error) {
    global $extradbcnx;
    static $querycache;

    if (!isset($querycache)) {
        $querycache = array();
    }

    if (!$extradbcnx) {
        if ($return) {
            $error = "Attempt to use extra_db database with an unset connexion";
            return false;
        }
        extra_db_error("Attempt to use extra_db database with an unset connexion");
    }

    $cachekey = hash('md5', $sql);

    if (array_key_exists($cachekey, $querycache) && !$renew) {
        return $querycache[$cachekey];
    } else {
        if ($res = pg_query($extradbcnx, $sql)) {
            while ($arr = pg_fetch_assoc($res)) {
                $keys = array_keys($arr);
                $key = $arr[$keys[0]];
                $querycache[$cachekey][$key] = (object)$arr;
            }
            pg_free_result($res);
            return $querycache[$cachekey];
        } else {
            if (extra_db_debugging()) {
                extra_db_notify($sql);
                extra_db_notify(pg_last_error($extradbcnx));
            }
            return false;
        }
    }
}
