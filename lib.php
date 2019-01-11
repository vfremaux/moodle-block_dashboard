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
 * Tells wether a feature is supported or not. Gives back the 
 * implementation path where to fetch resources.
 * @param string $feature a feature key to be tested.
 */
function block_dashboard_supports_feature($feature) {
    global $CFG;
    static $supports;

    if (!isset($supports)) {
        $supports = array(
            'pro' => array(
                'format' => array('xls', 'csv'),
                'config' => array('importexport'),
                'graph' => array('google', 'timeline'),
                'data' => array('treeview', 'extrapostgresource'),
                'result' => array('rotation', 'export', 'colouring'),
            ),
            'community' => array(
                'format' => array('csv'),
            ),
        );
    }

    // Check existance of the 'pro' dir in plugin.
    if (is_dir(__DIR__.'/pro')) {
        if ($feature == 'emulate/community') {
            return 'pro';
        }
        if (empty($config->emulatecommunity)) {
            $versionkey = 'pro';
        } else {
            $versionkey = 'community';
        }
    } else {
        $versionkey = 'community';
    }

    list($feat, $subfeat) = explode('/', $feature);

    if (!array_key_exists($feat, $supports[$versionkey])) {
        return false;
    }

    if (!in_array($subfeat, $supports[$versionkey][$feat])) {
        return false;
    }

    return $versionkey;
}

/**
 * A low level utility to format data in a cell
 * @param string $format the formating text formula
 * @param string $data the data value to be formated
 * @param int $cumulativeix index of the accumulator when using line accumulation format
 * @param array $record the full result record, used for inserting other named field values in the formula using %{fieldname}
 * @return the formated data.
 */
function dashboard_format_data($format, $data, $cumulativeix = null, &$record = null) {
    global $dashboardaccumulatorcache;

    // Cumulates curve.
    if (!empty($cumulativeix)) {
        if (!isset($dashboardaccumulatorcache)) {
            $dashboardaccumulatorcache = array();
        }
        if (!array_key_exists($cumulativeix, $dashboardaccumulatorcache)) {
            $dashboardaccumulatorcache[$cumulativeix] = $data;
        } else {
            $data = $data + $dashboardaccumulatorcache[$cumulativeix];
            $dashboardaccumulatorcache[$cumulativeix] = $data;
        }
    }

    $negativeenhance = false;

    if (!empty($format)) {

        // These two special formats are for use for SQL outputs.
        if ($format == 'NUMERIC') {
            return $data;
        }

        if ($format == 'TEXT') {
            return "'$data'";
        }

        // Hide value format.
        if ($format == '%0') {
            $data = '';
            return $data;
        }

        // Regexpfilter format.
        if (preg_match('/%[^\{\}]+%/', $format)) {
            preg_match($format, $data, $matches);
            if (count($matches) == 1) {
                $data = $matches[0];
            } elseif (count($matches) == 2) {
                $data = $matches[1];
            } // else let data as is

            return $data;
        }

        // Time value format from secs.
        if ($format == '%hms') {

            $hours = floor($data / 3600);
            $m = $data - $hours * 3600;
            $mins = floor($m / 60);
            $secs = $m - $mins * 60;
            $data = "$hours:$mins:$secs";
            return $data;
        }

        // Time value format from secs.
        if ($format == '%hm') {

            $hours = floor($data / 3600);
            $m = $data - $hours * 3600;
            $mins = floor($m / 60);
            $data = "{$hours}h{$mins}";
            return $data;
        }

        // Date value format.
        if ($format == '%D') {
            if ($data == 0) {
                $data = get_string('nulldate', 'block_dashboard');
            } else {
                $data = userdate($data);
            }
            return $data;
        }

        if (preg_match('/^-/', $format)) {
            $negativeenhance = true;
            $format = strstr($format, 1);
        }

        // Replace some other data members.
        if (preg_match_all('/\\%\\{(.*?)\\}/', $format, $matches)) {
            foreach ($matches[1] as $m) {
                if (isset($record->$m)) {
                    $format = str_replace('%{'.$m.'}', $record->$m, $format);
                }
            }
        }

        // All other cases fallback to sprintf.
        $data = @sprintf($format, $data);

        if ($negativeenhance && $data < 0) {
            $data = '<span style="color:red;font-weight:bold">'.$data.'</span>';
        }
    }

    return $data;
}


/**
 * Recursive worker for printing bidimensional table
 *
 */
function dashboard_table_explore_rec(&$theblock, &$str, &$pathstack, &$hcols, &$t, &$vkeys, $hlabel, $keydeepness, &$subsums = null) {
    static $level = 0;
    static $r = 0;

    $vformats = array_values($vkeys->formats);
    $vcolumns = array_keys($vkeys->formats);

    foreach ($t as $k => $v) {
        $plittable = false;
        array_push($pathstack, $k);
        $level++;
        if ($level < $keydeepness) {
            dashboard_table_explore_rec($theblock, $str, $pathstack, $hcols, $v, $vkeys, $hlabel, $keydeepness, $subsums);
        } else {
            $pre = "<tr class=\"row r{$r}\" >";
            $r = ($r + 1) % 2;
            $c = 0;
            foreach ($pathstack as $pathelm) {
                if (!empty($vformats[$c])) {
                    // $pathelm = dashboard_format_data($vformats[$c], $pathelm);
                }
                if (!empty($theblock->config->cleandisplay)) {
                    if ($pathelm != @$vkeys->mem[$c]) {
                        $pre .= "<td class=\"vkey c{$c}\">$pathelm</td>";
                        if (isset($vkeys->mem[$c]) && @$theblock->config->spliton == $vcolumns[$c]) {
                            // First split do not play.

                            // If vertsums are enabled, print vertsubs.
                            if ($theblock->config->vertsums) {
                                $str .= '<tr>';
                                $span = count($vkeys->labels);
                                $subtotalstr = get_string('subtotal', 'block_dashboard');
                                $str .= "<td colspan=\"{$span}\" >$subtotalstr</td>";
                                foreach ($hcols as $col) {
                                    $str.= "<td class=\"coltotal\">{$subsums->subs[$col]}</td>";
                                    $subsums->subs[$col] = 0;
                                }
                                if ($theblock->config->horizsums) {
                                    $str .= '<td></td>';
                                }
                                $str .= '</tr>';
                            }

                            // Then close previous table.
                            $str .= '</table>';
                            dashboard_print_table_header($str, $hcols, $vkeys, $hlabel, $theblock->config->horizsums);
                        }
                        $vkeys->mem[$c] = $pathelm;
                    } else {
                        $pre .= "<td class=\"vkey c{$c}\"></td>";
                    }
                } else {
                    $pre .= "<td class=\"vkey c{$c}\">$pathelm</td>";
                }
                $c++;
            }

            $str .= $pre;

            $sum = 0;
            foreach ($hcols as $col) {
                if (array_key_exists($col, $v)) {

                    $datum = $v[$col];
                    $str .= "<td class=\"data c{$c}\">{$datum}</td>";
                } else {
                    $str .= "<td class=\"data empty c{$c}\"></td>";
                }
                $sum = dashboard_sum($sum, strip_tags(@$v[$col]));
                if (@$theblock->config->vertsums) {
                    $subsums->subs[$col] = dashboard_sum(@$subsums->subs[$col], strip_tags(@$v[$col]));
                    $subsums->all[$col] = dashboard_sum(@$subsums->all[$col], strip_tags(@$v[$col]));
                }
                $c++;
            }

            if (@$theblock->config->horizsums) {
                $str .= "<td class=\"data rowtotal c{$c}\">$sum</td>";
            }

            $str .= "</tr>";
        }
        $level--;
        array_pop($pathstack);
    }
}

/**
 * processes given colour coding to a datum
 * @param object $theblock full block information
 *
 */
function dashboard_colour_code(&$theblock, $datum, &$colorcoding, $inline = false) {

    if (empty($colorcoding)) {
        return $datum;
    }

    $neatvalue = preg_replace('/<.*?>/', '', $datum);
    foreach ($colorcoding as $cond => $colour) {
        if (is_numeric($neatvalue) || empty($neatvalue)) {
            if (empty($neatvalue)) {
                $neatvalue = 0;
            }
            $cond = str_replace('%%', $neatvalue, $cond);
        } else {
            $cond = str_replace('%%', "'$neatvalue'", $cond);
        }
        if (!preg_match('[_{\}\(\)\"\$;.]', $cond)) {
            // Security check of given expression : no php code structure admitted.
            $expression = "\$res = $cond; ";
            if (optional_param('debug', false, PARAM_BOOL)) {
                echo $expression.' ';
            }
            @eval($expression);
        } else {
            $res = false;
        }
        if (@$res) {
            if ($inline) {
                $datum = '<span style="background-color:'.$colour.'">'.$datum.'</span>';
            } else {
                $datum = '<div style="width:100%;background-color:'.$colour.'">'.$datum.'</div>';
            }
            break;
        }
    }
    return $datum;
}

function dashboard_prepare_colourcoding(&$config) {
    $colorcoding = array();
    if (!empty($config->colorfield)) {
        $colors = explode("\n", @$config->colors);
        $colorvalues = explode("\n", @$config->coloredvalues);
        dashboard_normalize($colorvalues, $colors); // Normailzes options to keys.
        $colorcoding = array_combine($colorvalues, $colors);
    }
    return $colorcoding;
}

/**
 * utility to pad two distinct size arrays. Smaller array is padded 
 * with empty string elements to reach latter size.
 */
function dashboard_normalize(&$arr1, &$arr2) {
    $size1 = count($arr1);
    $size2 = count($arr2);
    if ($size1 == $size2) return;
    if ($size1 > $size2) {
        $arr2 = array_pad($arr2, $size1, '');
    } else {
        $arr2 = array_slice($arr2, 0, $size1);
    }
}

/**
 * given a query and a variable expression, tries to guess
 * it is a field aliased name
 * Matches 'as $key,' or 'as $key...FROM
 *
 */
function dashboard_guess_is_alias(&$theblock, $key) {

    $key = str_replace("'", "\'", $key);
    $key = preg_quote($key);

    return (preg_match('/\bas\s+$key(\s*,|[\s]*)FROM/is', $theblock->sql));
}

/**
 * provides all internally used fileareas
 * @param object $course unused
 * @param object $instance unused
 * @param object $context unused
 * @todo : cleanup extra unused params
 */
function dashboard_get_file_areas($course, $instance, $context) {
    return array('generated' => get_string('generatedexports', 'block_dashboard'));
}

/**
 * File browser support for block Dashboard
 * @see Beware this browser support is obtained from special 
 *
 */
function dashboard_get_file_info($browser, $areas, $course, $instance, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG, $DB, $USER;

    if ($context->contextlevel != CONTEXT_BLOCK) {
        return null;
    }

    // Filearea must contain a real area.
    if (!isset($areas[$filearea])) {
        return null;
    }

    static $cached = array();
    // Is cleared between unit tests we check if this is the same session.
    if (!isset($cached['sesskey']) || $cached['sesskey'] != sesskey()) {
        $cached = array('sesskey' => sesskey());
    }

    $fs = get_file_storage();
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;
    if (!($storedfile = $fs->get_file($context->id, 'block_dashboard', $filearea, $itemid, $filepath, $filename))) {
        return null;
    }

    $urlbase = $CFG->wwwroot.'/pluginfile.php';
    return new file_info_stored($browser, $context, $storedfile, $urlbase, $itemid, true, true, false, false);
}

function block_dashboard_pluginfile($course, $instance, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_BLOCK) {
        return false;
    }

    require_course_login($course);

    $fileareas = array('generated');
    if (!in_array($filearea, $fileareas)) {
        return false;
    }

    $itemid = (int)array_shift($args);

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/block_dashboard/$filearea/$itemid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }

    // Finally send the file.
    send_stored_file($file, 0, 0, false); // Download MUST be forced - security!
}

/**
 * Creates an output file somewhere and fills it with output content.
 */
function dashboard_output_file(&$theblock, $str) {
    global $CFG;

    if ($theblock->config->exportcharset == 'iso') {
        $str = utf8_decode($str);
    }

    if (!empty($theblock->config->filepathadminoverride)) {
        // An admin has configured, can be anywhere in moodledata so be carefull !
        $outputfile = $CFG->dataroot.'/'.$theblock->config->filepathadminoverride.'/'.$theblock->config->filelocation;
        mtrace("   ... writing to ".$outputfile."\n");
        mtrace("   ... Note that settings will not allow file being accessible in the dashbaord filearea\n");
        if ($FILE = fopen($outputfile, 'wb')) {
            fputs($FILE, $str);
            fclose($FILE);
            return true;
        }
        return false;
    } else {
        $location = (empty($theblock->config->filelocation)) ? '/' : $theblock->config->filelocation;
        $cond = preg_match('/^\//', $theblock->config->filelocation);
        $location = ($cond) ? $theblock->config->filelocation : '/'.$theblock->config->filelocation;

        $filerecord = new StdClass();
        $filerecord->component = 'block_dashboard';
        $filerecord->contextid = context_block::instance($theblock->instance->id)->id;
        $filerecord->filearea = 'generated';
        $filerecord->itemid = $theblock->instance->id;
        if (empty($theblock->config->filelocation)) {
            $filerecord->filepath = '/';
            $filename = 'csv_report_dashboard_'.$theblock->instance->id.'.csv';
        } else {
            $parts = pathinfo($theblock->config->filelocation);
            $filerecord->filepath = '/'.@$parts['dirname'].'/';
            $filerecord->filepath = preg_replace('#//#', '/', $filerecord->filepath); // Normalise.
            $filename = $parts['basename'];
        }
        if (@$theblock->config->horodatefiles) {
            $filename = $parts['filename'].'_'.strftime("%Y%m%d-%H:%M", time()).'.'.$parts['extension'];
        }
        $filerecord->filename = $filename;
        $fs = get_file_storage();

        // Get file and deletes if exists.
        $file = $fs->get_file($filerecord->contextid, $filerecord->component, $filerecord->filearea,
                              $filerecord->itemid, $filerecord->filepath, $filerecord->filename);

        // Delete it if it exists.
        if ($file) {
            $file->delete();
        }

        // Create new one.
        return $fs->create_file_from_string($filerecord, $str);
    }
}

/**
 * this utility function makes a "clever" sum, if it detects some time format in values
 *
 *
 */
function dashboard_sum($v1, $v2) {
    if ((preg_match('/\d+:\d+:\d+/', $v1) || empty($v1)) && (preg_match('/\d+:\d+:\d+/', $v2) || empty($v2)) && !(empty($v1) && empty($v2))) {

        // Compatible time values.
        if (empty($v1)) {
            $T1 = array(0,0,0);
        } else {
            $T1 = explode(':', $v1);
        }
        if (empty($v2)) {
            $T2 = array(0,0,0);
        } else {
            $T2 = explode(':', $v2);
        }
        $secs = $T1[2] + $T2[2];
        $mins = $T1[1] + $T2[1] + floor($secs / 60);
        $secs = $secs % 60;
        $hours = $T1[0] + $T2[0] + floor($mins / 60);
        $mins = $mins % 60;
        return "$hours:$mins:$secs";
    } else if ((preg_match('/\d+:\d+/', $v1) ||
            empty($v1)) &&
                    (preg_match('/\d+:\d+/', $v2) ||
                            empty($v2)) &&
                                    !(empty($v1) &&
                                            empty($v2))) {

        // Compatible time values.
        if (empty($v1)) {
            $T1 = array(0,0);
        } else {
            $T1 = explode(':', $v1);
        }
        if (empty($v2)) {
            $T2 = array(0,0);
        } else {
            $T2 = explode(':', $v2);
        }
        $mins = $T1[1] + $T2[1];
        $hours = $T1[0] + $T2[0] + floor($mins / 60);
        $mins = $mins % 60;
        return "$hours:$mins";
    } else {
        return $v1 + $v2;
    }
}
