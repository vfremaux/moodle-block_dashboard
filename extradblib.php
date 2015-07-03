<?php

// ########################

/**
 * 
 * @package block-dashboard
 * @category blocks
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @version Moodle 2.0
 */

/**
* Software environement wrappers
*
* This set of functions define wrappers to environemental usefull utilities
* such fetching central configuration values or giving error feedback to environment
*
* Implementation of these fucntion assume central libs of the applciation are loaded
* and full generic API is available.
*
*/

// protect eventually against unwanted accesses
extra_db_protect();

function extra_db_protect(){
    // TODO : implement environment dependant code here
    
    // example : Moodle 1.9 environement : simplest way, non internationalised
    if (!defined('MOODLE_INTERNAL')) die ('You cannot use this library this way');    
}

function extra_db_debugging(){
    // TODO : implement environment dependant code here
    
    // example : Moodle 1.9 environement : simplest way, non internationalised
    return debugging();
}

/**
* wraps to environement fatal error reporting function
*/
function extra_db_error($error, $return = false){
    // TODO : implement environment dependant code here
    
    // example : Moodle 1.9 environement : simplest way, non internationalised
    if ($return) return '<span class="error">'.$error.'</span>';
    echo($error);
}

/*
* wraps to environement non fatal error reporting function (debugging)
*/
function extra_db_notify($error){
    global $OUTPUT;

    // TODO : implement environment dependant code here
    
    // example : Moodle 1.9 environement : simplest way, non internationalised
    $OUTPUT->notification($error);
}

function extra_db_get_config($configkey){
    // TODO : implement environment dependant code here

    // example : Moodle 2 environement
    $config = get_config('block_dashboard');
    
    return @$config->$configkey;
}

function extra_db_set_config($configkey, $value){
    // TODO : implement environment dependant code here

    // example : Moodle 1.9 environement

    set_config($configkey, $value, 'block_dashboard');
}

// ########################

/**
* connects to ERP database on a new postgre connection
*
*/
function extra_db_connect($return = false, &$error){
    global $extra_db_CNX;

    $extra_dbport = extra_db_get_config('extra_db_port');
    if (empty($extra_dbport)){
        extra_db_set_config('extra_db_port', 5432); // Default port for PostGre
    }

    $extra_dbhost = extra_db_get_config('extra_dbhost');
    if (empty($extra_dbhost)){
        extra_db_set_config('extra_db_host', 'localhost'); // Default host for PostGre
    }

    $extra_dbdb = extra_db_get_config('extra_db_db');
    if (empty($extra_dbdb)){
        extra_db_error("extra_db needs a DB"); // Default host for PostGre
    }

    $cnxstring = ' host='.extra_db_get_config('extra_db_host').' port='.extra_db_get_config('extra_db_port').' dbname='.extra_db_get_config('extra_db_db').' user='.extra_db_get_config('extra_db_user').' password='.extra_db_get_config('extra_db_password');
    
    if (!$extra_db_CNX){
        if (!$extra_db_CNX = pg_connect($cnxstring)){
            $error = extra_db_error("Cannot connect to extra_db Database", $return);
            return false;
        }
    }
    return true;
}

/**
* closes to ERP database on a new postgre connection
*
*/
function extra_db_close(){
    global $extra_db_CNX;
    
    if ($extra_db_CNX){
        pg_close($extra_db_CNX);
    }
    
}

/**
*
*
*/
function extra_db_query($sql, $renew = false, $return = false, &$error){
    global $extra_db_CNX;
    static $querycache;

    if (!isset($querycache)) {
        $querycache = array();
    }

    if (!$extra_db_CNX){
        if ($return){
            $error = "Attempt to use extra_db database with an unset connexion";
            return false;
        }
        extra_db_error("Attempt to use extra_db database with an unset connexion");
    }

    $cachekey = hash('md5', $sql);

    if (array_key_exists($cachekey, $querycache) && !$renew){
        return $querycache[$cachekey];
    } else {
        if ($res = pg_query($extra_db_CNX, $sql)){
            while($arr = pg_fetch_assoc($res)){
                $keys = array_keys($arr);
                $key = $arr[$keys[0]];
                $querycache[$cachekey][$key] = (object)$arr;
            }
            pg_free_result($res);
            return $querycache[$cachekey];
        } else {
            if (extra_db_debugging()){
                extra_db_notify($sql);
                extra_db_notify(pg_last_error($extra_db_CNX));
            }
            return false;
        }
    }
}
