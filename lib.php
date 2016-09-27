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

defined('MOODLE_INTERNAL') || die();

/**
 * @package block_dashboard
 * @category blocks
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @version Moodle 2.x
 */

/**
 * A low level utility to format data in a cell
 *
 */
function dashboard_format_data($format, $data, $cumulativeix = null, &$record = null) {
    global $DASHBOARD_ACCUMULATORS;

    // Cumulates curve.
    if (!empty($cumulativeix)) {
        if (!isset($DASHBOARD_ACCUMULATORS)) {
            $DASHBOARD_ACCUMULATORS = array();
        }
        if (!array_key_exists($cumulativeix, $DASHBOARD_ACCUMULATORS)) {
            $DASHBOARD_ACCUMULATORS[$cumulativeix] = $data;
        } else {
            $data = $data + $DASHBOARD_ACCUMULATORS[$cumulativeix];
            $DASHBOARD_ACCUMULATORS[$cumulativeix] = $data;
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
            $data = userdate($data);
            return $data;
        }

        if (preg_match('/^-/', $format)) {
            $negativeenhance = true;
            $format = strstr($format, 1);
        }

        // replace some other data members
        if (preg_match_all('/\\%\\{(.*?)\\}/', $format, $matches)) {
            foreach($matches[1] as $m) {
                if (isset($record->$m)) {
                    $format = str_replace('%{'.$m.'}', $record->$m, $format);
                }
            }
        }

        // All other cases fallback to sprintf.
        $data = sprintf($format, $data);

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
function table_explore_rec(&$theBlock, &$str, &$pathstack, &$hcols, &$t, &$vkeys, $hlabel, $keydeepness, &$subsums = null){
    static $level = 0;
    static $r = 0;

    $vformats = array_values($vkeys->formats);
    $vcolumns = array_keys($vkeys->formats);

    foreach ($t as $k => $v) {
        $plittable = false;
        array_push($pathstack, $k);
        $level++;
        if ($level < $keydeepness) {
            table_explore_rec($theBlock, $str, $pathstack, $hcols, $v, $vkeys, $hlabel, $keydeepness, $subsums);
        } else {
            $pre = "<tr class=\"row r{$r}\" >";
            $r = ($r + 1) % 2;
            $c = 0;
            foreach ($pathstack as $pathelm) {
                if (!empty($vformats[$c])) {
                    $pathelm = dashboard_format_data($vformats[$c], $pathelm);
                }
                if (!empty($theBlock->config->cleandisplay)) {
                    if ($pathelm != @$vkeys->mem[$c]){
                        $pre .= "<td class=\"vkey c{$c}\">$pathelm</td>";
                        if (isset($vkeys->mem[$c]) && @$theBlock->config->spliton == $vcolumns[$c]) {
                            // first split do not play

                            // if vertsums are enabled, print vertsubs
                            if ($theBlock->config->vertsums) {
                                $str .= '<tr>';
                                $span = count($vkeys->labels);
                                $subtotalstr = get_string('subtotal', 'block_dashboard');
                                $str .= "<td colspan=\"{$span}\" >$subtotalstr</td>";
                                foreach ($hcols as $col) {
                                    $str.= "<td class=\"coltotal\">{$subsums->subs[$col]}</td>";
                                    $subsums->subs[$col] = 0;
                                }
                                if ($theBlock->config->horizsums) {
                                    $str .= '<td></td>';
                                }
                                $str .= '</tr>';
                            }

                            // then close previous table
                            $str .= '</table>';
                            dashboard_print_table_header($str, $hcols, $vkeys, $hlabel, $theBlock->config->horizsums);
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
                if (@$theBlock->config->vertsums) {
                    $subsums->subs[$col] = dashboard_sum(@$subsums->subs[$col], strip_tags(@$v[$col]));
                    $subsums->all[$col] = dashboard_sum(@$subsums->all[$col], strip_tags(@$v[$col]));
                }
                $c++;
            }

            if (@$theBlock->config->horizsums) {
                $str .= "<td class=\"data rowtotal c{$c}\">$sum</td>";
            }

            $str .= "</tr>";
        }
        $level--;
        array_pop($pathstack);
    }
}

/**
 * An HTML raster for a matrix cross table
 * printing raster uses a recursive cell drilldown over dynamic matrix dimension
 */
function print_cross_table_csv(&$theBlock, &$m, &$hcols, $return = false) {

    $str = '';

    dashboard_print_table_header_csv($str, $theBlock, $hcols);
    // print flipped array
    $path = array();

    $theBlock->subsums = new StdClass;
    $theBlock->subsums->subs = array();
    $theBlock->subsums->all = array();

    table_explore_rec_csv($theBlock, $str, $path, $hcols, $m, $return);

    if ($return) return $str;
    echo $str;
}

/**
 * Recursive worker for CSV table writing
 *
 */
function table_explore_rec_csv(&$theBlock, &$str, &$pathstack, &$hcols, &$t, $return) {
    global $CFG;

    $config = get_config('block_dashboard');

    static $level = 0;
    static $r = 0;

    $keydeepness = count($theBlock->vertkeys->formats);
    $vformats = array_values($theBlock->vertkeys->formats);
    $vcolumns = array_keys($theBlock->vertkeys->formats);

    foreach($t as $k => $v) {
        $plittable = false;
        array_push($pathstack, $k);

        $level++;
        if ($level < $keydeepness) {
            table_explore_rec_csv($theBlock, $str, $pathstack, $hcols, $v, $return);
        } else {
            $r = ($r + 1) % 2;
            $c = 0;
            $pre = '';
            foreach ($pathstack as $pathelm) {
                if (!empty($vformats[$c])) {
                    $pathelm = dashboard_format_data($vformats[$c], $pathelm);
                }
                if (!empty($theBlock->config->cleandisplay)) {
                    if ($pathelm != @$vkeys->mem[$c]) {
                        $pre .= "$pathelm".$config->csv_field_separator;
                        if (isset($vkeys->mem[$c]) && @$theBlock->config->spliton == $vcolumns[$c]) {
                            // first split do not play

                            // if vertsums are enabled, print vertsubs
                            if ($theBlock->config->vertsums) {
                                $span = count($pathstack);
                                $subtotalstr = get_string('subtotal', 'block_dashboard');
                                $str .= "$subtotalstr".$config->csv_field_separator;
                                foreach($hcols as $col) {
                                    $str .= $theBlock->subsumsf->subs[$col].$config->csv_field_separator;
                                    $theBlock->subsums->subs[$col] = 0;
                                }
                                if ($theBlock->config->horizsums) {
                                    $str .= $config->csv_field_separator;
                                }
                                $str .= $config->csv_line_separator;
                            }

                            // then close previous table
                            dashboard_print_table_header_csv($str, $theBlock, $hcols);
                        }
                        $theBlock->vertkeys->mem[$c] = $pathelm;
                    } else {
                        $pre .= $config->csv_field_separator;
                    }
                } else {
                    $pre .= "$pathelm".$config->csv_field_separator;
                }
                $c++;
            }

            $str .= $pre;

            $sum = 0;
            foreach($hcols as $col) {
                if (array_key_exists($col, $v)) {
                    $str .= "{$v[$col]}".$config->csv_field_separator;
                } else {
                    $str .= ''.$config->csv_field_separator;
                }
                $sum = dashboard_sum($sum, strip_tags(@$v[$col]));
                if (@$theBlock->config->vertsums) {
                    $theBlock->subsums->subs[$col] = dashboard_sum(@$subsums->subs[$col], strip_tags(@$v[$col]));
                    $theBlock->subsums->all[$col] = dashboard_sum(@$subsums->all[$col], strip_tags(@$v[$col]));
                }
                $c++;
            }

            if (@$theBlock->config->horizsums) {
                $str .= $sum.$config->csv_field_separator;
            }

            // chop last value

            $str = preg_replace("/{$config->csv_field_separator}$/", '', $str);

            if (!$return) {
                if ($theBlock->config->exportcharset == 'utf8') {
                    echo utf8_decode($str); 
                } else {
                    echo $str; 
                }
                echo $config->csv_line_separator;
            } else {
                $str .= $config->csv_line_separator;
            }
        }
        $level--;
        array_pop($pathstack);
    }
}

/**
 * prints the first line as column titles
 */
function dashboard_print_table_header_csv(&$str, &$theBlock, &$hcols) {
    global $CFG;

    $config = get_config('block_dashboard');

    $vlabels = array_values($theBlock->vertkeys->labels);

    $row = array();
    foreach($theBlock->vertkeys->labels as $vk => $vlabel) {
        $row[] = $vlabel;
    }

    foreach($hcols as $hc) {
        $row[] = $hc;
    }

    if (isset($theBlock->config->horizsums)) {
        $row[] = get_string('total', 'block_dashboard');
    }

    if ($theBlock->config->exportcharset == 'utf8') {
        $str .= utf8_decode(implode($config->csv_field_separator, $row));
    } else {
        $str .= implode($config->csv_field_separator, $row);
    }
    $str .= $config->csv_line_separator;
}

/**
 * processes given colour coding to a datum
 * @param object $theBlock full block information
 *
 */
function dashboard_colour_code(&$theBlock, $datum, &$colorcoding, $inline = false) {

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
            // security check of given expression : no php code structure admitted
            $expression = "\$res = $cond; ";
            if (optional_param('debug', false, PARAM_BOOL)) {
                echo $expression.' ';
            }
            @eval($expression);
        } else {
            $res = false;
        }
        if (@$res){
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
        dashboard_normalize($colorvalues, $colors); // normailzes options to keys
        $colorcoding = array_combine($colorvalues, $colors);
    }
    return $colorcoding;
}


/**
 * Renders each declared sum as HTML
 *
 */
function dashboard_render_numsums(&$theBlock, &$aggr) {
    global $OUTPUT;

    $str = '';

    $str .= $OUTPUT->box_start('dashboard-sumative-box');
    foreach (array_keys($theBlock->numsumsf) as $numsum) {
        if (!empty($theBlock->numsumsf[$numsum])) {
            $formattedsum = dashboard_format_data($theBlock->numsumsf[$numsum], @$aggr->$numsum);
        } else {
            $formattedsum = 0 + @$aggr->$numsum;
        }
        $str .= $theBlock->outputnumsums[$numsum].' : <b>'.$formattedsum.'</b>&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    $str .= $OUTPUT->box_end();

    return $str;
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
function dashboard_guess_is_alias(&$theBlock, $key) {

    $key = str_replace("'", "\'", $key);
    $key = preg_quote($key);

    return (preg_match('/\bas\s+$key(\s*,|[\s]*)FROM/is', $theBlock->sql));
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
    send_stored_file($file, 0, 0, false); // download MUST be forced - security!
}

function dashboard_output_file(&$theBlock, $str) {
    global $CFG;

    if (!empty($theBlock->config->filepathadminoverride)) {
        // an admin has configured, can be anywhere in moodledata so be carefull !
        $outputfile = $CFG->dataroot.'/'.$theBlock->config->filepathadminoverride.'/'.$theBlock->config->filelocation;
        $FILE = fopen($outputfile, 'wb');
        fputs($FILE, $str);
        fclose($FILE);
    } else {
        // needs being in course files
        // this is obsolete..... !!

        $location = (empty($theBlock->config->filelocation)) ? '/' : $theBlock->config->filelocation;
        $location = (preg_match('/^\//', $theBlock->config->filelocation)) ? $theBlock->config->filelocation : '/'.$theBlock->config->filelocation ;

        $filerecord = new StdClass();
        $filerecord->component = 'block_dashboard';
        $filerecord->contextid = context_block::instance($theBlock->instance->id)->id;
        $filerecord->filearea = 'generated';
        $filerecord->itemid = $theBlock->instance->id;
        $parts = pathinfo($theBlock->config->filelocation);
        $filerecord->filepath = '/'.$parts['dirname'].'/';
        $filerecord->filepath = preg_replace('/\/\//', '/', $filerecord->filepath); // Normalise
        $filename = $parts['basename'];
        if (@$theBlock->config->horodatefiles) {
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
        $fs->create_file_from_string($filerecord, $str);
    }
}

/**
 * this utility function makes a "clever" sum, if it detects some time format in values
 *
 *
 */
function dashboard_sum($v1, $v2){
    if ((preg_match('/\d+:\d+:\d+/', $v1) || empty($v1)) && (preg_match('/\d+:\d+:\d+/', $v2) || empty($v2)) && !(empty($v1) && empty($v2))) {
        // %hms formatting
        // compatible time values
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
    } elseif ((preg_match('/\d+:\d+/', $v1) || empty($v1)) && (preg_match('/\d+:\d+/', $v2) || empty($v2)) && !(empty($v1) && empty($v2))) {
        // %hm formatting.
        // compatible time values.
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
        return $v1 + $v1;
    }
}
