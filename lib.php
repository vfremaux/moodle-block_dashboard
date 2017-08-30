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
                'result' => array('rotation', 'export'),
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
 *
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
            $data = userdate($data);
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
                    $pathelm = dashboard_format_data($vformats[$c], $pathelm);
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
<<<<<<< HEAD
* Recursive worker for CSV table writing
*
*/
function table_explore_rec_csv(&$theBlock, &$str, &$pathstack, &$hcols, &$t, &$vkeys, $hlabel, $keydeepness, &$subsums = null){
	global $CFG;

	static $level = 0;
	static $r = 0;
	
	$vformats = array_values($vkeys->formats);
	$vcolumns = array_keys($vkeys->formats);
	
	foreach($t as $k => $v){
		$plittable = false;
		array_push($pathstack, $k);

		$str = '';
		$level++;
		if ($level < $keydeepness){
			table_explore_rec_csv($theBlock, $str, $pathstack, $hcols, $v, $vkeys, $hlabel, $keydeepness, $subsums);
		} else {
			$r = ($r + 1) % 2;
			$c = 0;
			$pre = '';
			foreach($pathstack as $pathelm){
				if (!empty($vformats[$c])){
					$pathelm = dashboard_format_data($vformats[$c], $pathelm);
				}
				if (!empty($theBlock->config->cleandisplay)){
					if ($pathelm != @$vkeys->mem[$c]){
						$pre .= "$pathelm".$CFG->dashboard_csv_field_separator;
						if (isset($vkeys->mem[$c]) && @$theBlock->config->spliton == $vcolumns[$c]){
							// first split do not play
							
							// if vertsums are enabled, print vertsubs
							if ($theBlock->config->vertsums){
								$span = count($pathstack);
								$subtotalstr = get_string('subtotal', 'block_dashboard');
								$str .= "$subtotalstr".$CFG->dashboard_csv_field_separator;
								foreach($hcols as $col){
									$str .= $theBlock->subsumsf->subs[$col].$CFG->dashboard_csv_field_separator;
									$subsums->subs[$col] = 0;
								}
								if ($theBlock->config->horizsums){
									$str .= $CFG->dashboard_csv_field_separator;
								}
								$str .= $CFG->dashboard_csv_line_separator;
							}
							
							// then close previous table
							dashboard_print_table_header_csv($str, $hcols, $vkeys, $hlabel, $theBlock->config->horizsums);
						}
						$vkeys->mem[$c] = $pathelm;
					} else {
						$pre .= $CFG->dashboard_csv_field_separator;
					}
				} else {
					$pre .= "$pathelm".$CFG->dashboard_csv_field_separator;
				}
				$c++;
			}
			
			$str .= $pre;
			
			$sum = 0;
			foreach($hcols as $col){
				if (array_key_exists($col, $v)){
					$str .= "{$v[$col]}".$CFG->dashboard_csv_field_separator;
				} else {
					$str .= ''.$CFG->dashboard_csv_field_separator;
				}
				$sum += strip_tags(@$v[$col]);
				if (@$theBlock->config->vertsums){
					$subsums->subs[$col] = @$subsums->subs[$col] + strip_tags(@$v[$col]);
					$subsums->all[$col] = @$subsums->all[$col] + strip_tags(@$v[$col]);
				}
				$c++;
			}
			
			if (@$theBlock->config->horizsums){
				$str .= $sum.$CFG->dashboard_csv_field_separator;
			}

			// chop last value
			
			$str = preg_replace("/{$CFG->dashboard_csv_field_separator}$/", '', $str);
			
			echo $str; 
			echo $CFG->dashboard_csv_line_separator;
		}
		$level--;
		array_pop($pathstack);
	}
=======
 * provides all internally used fileareas
 * @param object $course unused
 * @param object $instance unused
 * @param object $context unused
 * @todo : cleanup extra unused params
 */
function dashboard_get_file_areas($course, $instance, $context) {
    return array('generated' => get_string('generatedexports', 'block_dashboard'));
>>>>>>> MOODLE_33_STABLE
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

    if (!empty($theblock->config->filepathadminoverride)) {
        // An admin has configured, can be anywhere in moodledata so be carefull !
        $outputfile = $CFG->dataroot.'/'.$theblock->config->filepathadminoverride.'/'.$theblock->config->filelocation;
        mtrace("   ... writing to ".$outputpile."\n");
        mtrace("   ... Note that settings will not allow file being accessible in the dashbaord fielarea\n");
        if ($FILE = fopen($outputfile, 'wb')) {
            fputs($FILE, $str);
            fclose($FILE);
            return true;
        }
        return false;
    } else {
        $location = (empty($theblock->config->filelocation)) ? '/' : $theblock->config->filelocation;
        $cond = preg_match('/^\//', $theblock->config->filelocation);
        $location = ($cond) ? $theblock->config->filelocation : '/'.$theblock->config->filelocation ;

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
<<<<<<< HEAD
* processes given colour coding to a datum
* @param object $theBlock full block information
*
*/
function dashboard_colour_code(&$theBlock, $datum, &$colorcoding, $inline = false){

	if (empty($colorcoding)) return $datum;

	$neatvalue = preg_replace('/<.*?>/', '', $datum);
	foreach($colorcoding as $cond => $colour){
		if (is_numeric($neatvalue) || empty($neatvalue)){
			if (empty($neatvalue)) $neatvalue = 0;
			$cond = str_replace('%%', $neatvalue, $cond);
		} else {
			$cond = str_replace('%%', "'$neatvalue'", $cond);
		}
		if (!preg_match('[_{\}\(\)\"\$;.]', $cond)){
			// security check of given expression : no php code structure admitted
			$expression = "\$res = $cond; ";
			if (optional_param('debug', false, PARAM_BOOL)) echo $expression.' ';
			@eval($expression);
		} else {
			$res = false;
		}
		if (@$res){
			if ($inline){
				$datum = '<span style="background-color:'.$colour.'">'.$datum.'</span>';
			} else {
				$datum = '<div style="width:100%;background-color:'.$colour.'">'.$datum.'</div>';
			}
			break;
		}
	}
	return $datum;
} 

function dashboard_prepare_colourcoding(&$config){
	$colorcoding = array();
	if (!empty($config->colorfield)){
		$colors = explode("\n", @$config->colors);
		$colorvalues = explode("\n", @$config->coloredvalues);
		dashboard_normalize($colorvalues, $colors); // normailzes options to keys
		$colorcoding = array_combine($colorvalues, $colors);
	}
	return $colorcoding;
}

function dashboard_print_setting_tabs($config){
	
	$tabs = array(
		array('querydesc', get_string('querydesc', 'block_dashboard'), true),
		array('queryparams', get_string('queryparams', 'block_dashboard'), true),
		array('outputparams', get_string('outputparams', 'block_dashboard'), true),
		array('tabularparams', get_string('tabularparams', 'block_dashboard'), (!empty($config->tabletype) && $config->tabletype == 'tabular') ? true : false ),
		array('treeviewparams', get_string('treeviewparams', 'block_dashboard'), (!empty($config->graphtype) && $config->graphtype == 'treeview') ? true : false ),
		array('graphparams', get_string('graphparams', 'block_dashboard'), true),
		array('googleparams', get_string('googleparams', 'block_dashboard'), (!empty($config->graphtype) && $config->graphtype == 'googlemap') ? true : false ),
		array('timelineparams', get_string('timelineparams', 'block_dashboard'), (!empty($config->graphtype) && $config->graphtype == 'timeline') ? true : false ),
		array('summatorsparams', get_string('summatorsparams', 'block_dashboard'), true),
		array('tablecolormapping', get_string('tablecolormapping', 'block_dashboard'), true),
		array('datarefresh', get_string('datarefresh', 'block_dashboard'), true),
		array('fileoutput', get_string('fileoutput', 'block_dashboard'), true),
	);
	
	echo '<div id="dashboardsettings-menu" class="tabtree">';
	echo '<ul class="tabrow0">';
	foreach($tabs as $tabarr){
		list($tabkey, $tabname, $visible) = $tabarr;
		$class = ($tabkey == 'querydesc') ? 'here ' : '';
		$class .= ($visible) ? "on" : "off" ;
		$tabname = str_replace(' ', '&nbsp;', $tabname);
		echo '<li id="setting-tab-'.$tabkey.'" class="setting-tab '.$class.'"><a href="Javascript:open_panel(\''.$tabkey.'\')"><span>'.$tabname.'</span></a></li> ';
	}
	echo '</ul>';
}

function dashboard_render_googlemaps_data(&$theBlock, &$data, &$graphdesc){
	if (!empty($config->datalocations)){
		// data comes from query and locating information from datalocations field mapping
		$googlelocs = explode(";", $theBlock->config->datalocations);
		if (!empty($data)){
			foreach($data as $d){
				$t = $d->{$theBlock->config->datatitles};
				if (count($googlelocs) == 1){
					list($lat,$lng) = explode(',', $d->{$theBlock->config->datalocations});
					$type = $d->{$theBlock->config->datatypes};
					$gmdata[] = array('title' => $t, 'lat' => 0 + $lat, 'lng' => 0 + $lng, 'markerclass' => $type);
				} elseif (count($googlelocs) == 4) {
					// we expect an address,postcode,city,region field list. If some data is quoted, take it as "constant"
					$addresselms = explode(';', $theBlock->config->datalocations);
					$addressfield = trim($addresselms[0]);
					$postcodefield = trim($addresselms[1]);
					$cityfield = trim($addresselms[2]);
					$regionfield = trim($addresselms[3]);
					$address = $d->{$addressfield};
					if (preg_match('/^(?:\'|")([^\']*)(?:\'|")$/', $postcodefield, $matches)){
						$postcode = $matches[1];
					} else {
						$postcode = $d->{$postcodefield};
					}
					if (preg_match('/^(?:\'|")([^\']*)(?:\'|")$/', $cityfield, $matches)){
						$city = $matches[1];
					} else {
						$city = preg_replace('/cedex.*/i', '', $d->{$cityfield}); // remove postal alterations
					}
					if (preg_match('/^(?:\'|")([^\']*)(?:\'|")$/', $regionfield, $matches)){
						$region = $matches[1];
					} else {
						$region = $d->{$regionfield};
					}
					$googleerrors = array();
					if ($location = googlemaps_get_geolocation($region, $address, $postcode, $city, $googleerrors)){
						list($lat,$lng) = explode(',', $location);
						$type = $d->{$theBlock->config->datatypes};
						$gmdata[] = array('title' => $t, 'lat' => $lat, 'lng' => $lng, 'markerclass' => $type);
					}
				} else {
					$text .= '<span class="error">'.get_string('googlelocationerror', 'block_dashboard').'</span>';
					break;
				}
			}
		}
	} else {
		$text .= " This is a demo set !! ";				
		/**
		demo
		*/
		$gmdata = array(
			array('lat' => 48.020587, 
			      'lng' => 0.151405,
			      'markerclass' => 'certiffoad',
			      'title' => 'Via formation'),
			array('lat' => 47.894823, 
			      'lng' => 1.904798,
			      'markerclass' => 'certiffoad',
			      'title' => 'FormaSanté'),
			array('lat' => 48.091582, 
			      'lng' => -1.789484,
			      'markerclass' => 'hq',
			      'title' => 'CLPS Siege'),
			array('lat' => 48.392852, 
			      'lng' => -4.444313,
			      'markerclass' => 'fcfoad',
			      'title' => 'CLPS Brest'),
			array('lat' => 47.663075, 
			      'lng' => -2.711906,
			      'markerclass' => 'fcfoad',
			      'title' => 'CLPS Vannes'),
			array('lat' => 47.093953,
			      'lng' => 5.497713,
			      'markerclass' => 'fcfoad',
			      'title' => 'INFA Franche-Comte'),
			array('lat' => 48.565703,
			      'lng' => 7.734375,
			      'markerclass' => 'fc',
			      'title' => 'INFA Alsace'),
			array('lat' => 49.274973,
			      'lng' => 2.444458,
			      'markerclass' => 'fc',
			      'title' => 'INFA Picardie'),
		);
	}
	
	$text .= googlemaps_embed_graph('dashboard'.$theBlock->instance->id, @$theBlock->config->lat, @$theBlock->config->lng, @$theBlock->config->graphwidth, $this->config->graphheight, $graphdesc, $gmdata, true);
	
	if (!empty($googleerrors)){
		// print_object($googleerrors);
	}
}

/**
* Renders each declared sum as HTML
*
*/
function dashboard_render_numsums(&$theBlock, &$aggr){
	global $OUTPUT;
	
	$str = '';

	$str .= $OUTPUT->box_start('dashboard-sumative-box', '', true);
	foreach(array_keys($theBlock->numsumsf) as $numsum){
		if (!empty($theBlock->numsumsf[$numsum])){
			$formattedsum = dashboard_format_data($theBlock->numsumsf[$numsum], @$aggr->$numsum);
		} else {
			$formattedsum = 0 + @$aggr->$numsum;
		}
		$str .= $theBlock->outputnumsums[$numsum].' : <b>'.$formattedsum.'</b>&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	$str .= $OUTPUT->box_end(true);

	return $str;
}

/**
* get value range, print and sets up data filters
*
*/
function dashboard_render_filters(&$theBlock, $javascripthandler){

	$str = '';
	
	$alllabels = array_keys($theBlock->filterfields->labels);
	
	foreach($alllabels as $afield){

		if (empty($afield)) continue; // protects against empty filterset

		$fieldname = (isset($theBlock->filterfields->translations[$afield])) ? $theBlock->filterfields->translations[$afield] : $afield ;

		// $filterresults = $theBlock->filter_get_results($theBlock->sql, $afield, $fieldname, false, false, $str);
		$filterresults = $theBlock->filter_get_results($afield, $fieldname, false, false, $str);

		if ($filterresults){
			$filterset = array();
			if (!$theBlock->is_filter_single($afield)) $filterset['0'] = '*';
			foreach(array_values($filterresults) as $value){
				$radical = preg_replace('/^.*\./', '', $fieldname); // removes table scope explicitators
				$filterset[$value->$radical] = $value->$radical;
			}				
			$str .= '<span class="dashboard-filter">'.$theBlock->filterfields->labels[$afield].':</span>';
			$multiple = (strstr($theBlock->filterfields->options[$afield], 'm') === false) ? false : true ; 
			$arrayform = ($multiple) ? '[]' : '' ;

			if (!is_array(@$theBlock->filtervalues[$radical])){
				$unslashedvalue = stripslashes(@$theBlock->filtervalues[$radical]);
			} else {
				$unslashedvalue = $theBlock->filtervalues[$radical];
			}
			
			$selectoptions = array('onchange' => $javascripthandler);
			if ($multiple){
				$selectoptions['multiple'] = 1;
				$selectoptions['size'] = 5;
			}

			if ($theBlock->is_filter_global($afield)){
				$str .= html_writer::select($filterset, "filter0_{$radical}{$arrayform}", $unslashedvalue, array('' => 'choosedots'), $selectoptions);
			} else {
				$str .= html_writer::select($filterset, "filter{$theBlock->instance->id}_{$radical}{$arrayform}", $unslashedvalue, array('' => 'choosedots'), $selectoptions);
			}
			$str .= "&nbsp;&nbsp;";
		}
	}
		
	return $str;
}

/**
* if there are some user params, print widgets for them
*
*/
function dashboard_render_params(&$theBlock, &$javascripthandler){

	$str = '';
	
	$str .= '<div class="dashboard-sql-params">';
	foreach($theBlock->params as $key => $param){
		$htmlkey = preg_replace('/[.() *]/', '', $key);
		switch($param->type){
			case 'choice':
				$values = explode("\n", $param->values);
				$option1checked = ($param->value == $values[0]) ? 'checked="checked"' : '' ;
				$option2checked = ($param->value == $values[1]) ? 'checked="checked"' : '' ;
				$str .= ' '.$param->label.': <input type="radio" name="'.$htmlkey.'" value="'.htmlentities($values[0], ENT_QUOTES, 'UTF-8').'" '.$option1checked.' onchange="'.$javascripthandler.'" /> '.$values[0];
				$str .= ' - <input type="radio" name="'.$key.'" value="'.htmlentities($values[1], ENT_QUOTES, 'UTF-8').'" '.$option2checked.'  onchange="'.$javascripthandler.'"/> '.$values[1].' &nbsp;&nbsp;';
			break;
			case 'text':
				$str .= ' '.$param->label.': <input type="text" size="10" name="'.$htmlkey.'" value="'.htmlentities($param->value, ENT_QUOTES, 'UTF-8').'" onchange="'.$javascripthandler.'" /> ';
			break;
			case 'list':
				$str .= ' '.$param->label.': <select name="'.$htmlkey.'" >';
				foreach($param->values as $v){
					$vselected = ($v == $param->value) ? ' selected="selected" ' : '' ;
					$str .= '<option value="'.$v.'" '.$vselected.'>'.$v.'</option>';
				}
			break;
			case 'range':
				$str .= ' '.$param->label.': '.get_string('from', 'block_dashboard').' <input type="text" size="10" name="'.$htmlkey.'_from" value="'.htmlentities($param->valuefrom, ENT_QUOTES, 'UTF-8').'"  /> ';
				$str .= ' '.get_string('to', 'block_dashboard').' <input type="text" size="10" name="'.$htmlkey.'_to" value="'.htmlentities($param->valueto, ENT_QUOTES, 'UTF-8').'"  /> ';
				$javascripthandler = '';  // cancel the autosubmit possibility
			break;
			case 'date':
	            $str .= ' '.$param->label.': <input type="text" size="10"  id="date-'.$htmlkey.'" name="'.$htmlkey.'" value="'.$param->originalvalue.'"  onchange="'.$javascripthandler.'" />';
	            $str .= '<script type="text/javascript">'."\n";
	            $str .= 'var '.$htmlkey.'Cal = new dhtmlXCalendarObject(["date-'.$htmlkey.'"]);'."\n";
	            $str .= '</script>'."\n";
			break;
			case 'daterange':
	            $str .= ' '.$param->label.': '.get_string('from', 'block_dashboard').' <input type="text" size="10"  id="date-'.$htmlkey.'_from" name="'.$htmlkey.'_from" value="'.$param->originalvaluefrom.'" />';
	            $str .= ' '.get_string('to', 'block_dashboard').' <input type="text" size="10"  id="date-'.$htmlkey.'_to" name="'.$htmlkey.'_to" value="'.$param->originalvalueto.'" />';
	            $str .= '<script type="text/javascript">'."\n";
	            $str .= 'var '.$htmlkey.'fromCal = new dhtmlXCalendarObject([\'date-'.$htmlkey.'_from\', \'date-'.$htmlkey.'_to\']);'."\n";
	            $str .= $htmlkey.'fromCal.setSkin(\'dhx_web\');';
	            $str .= $htmlkey.'fromCal.loadUserLanguage(\''.current_language().'\');';
	            $str .= '</script>'."\n";
				$javascripthandler = ''; // cancel the autosubmit possibility
			break;
		}
	}

	$str .= '</div>';
	return $str;
}


/**
* utility to pad two distinct size arrays
*/
function dashboard_normalize(&$arr1, &$arr2){
	$size1 = count($arr1);
	$size2 = count($arr2);
	if ($size1 == $size2) return;
	if ($size1 > $size2){
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
function dashboard_guess_is_alias(&$theBlock, $key){
	
	$key = str_replace("'", "\'", $key);
	$key = preg_quote($key);
	
	return (preg_match('/\bas\s+$key(\s*,|[\s]*)FROM/is', $theBlock->sql));
}

/**
*
*
*
*/
function dashboard_render_filters_and_params_form(&$theBlock, $sort){
	global $COURSE;
	
	$text = '';
	
	if (!empty($theBlock->config->filters) || !empty($theBlock->params)){
		$text .= '<form class="dashboard-filters" name="dashboardform'.$theBlock->instance->id.'" method="GET">';
		$text .= '<input type="hidden" name="id" value="'.s($COURSE->id).'" />';
		if (!@$theBlock->config->inblocklayout){
			$text .= '<input type="hidden" name="blockid" value="'.s($theBlock->instance->id).'" />';
		}
		if ($COURSE->format == 'page'){
			if (!empty($coursepage)){
				$text .= '<input type="hidden" name="page" value="'.$flexpage->id.'" />';
			}
		}
		if ($sort == 'id DESC') $sort = '';
		$text .= '<input type="hidden" name="tsort'.$theBlock->instance->id.'" value="'.$sort.'" />';

		$autosubmit = (count(array_keys($theBlock->filters)) + count(array_keys($theBlock->params))) <= 1;
		$javascripthandler = '';
		if ($autosubmit){
			$javascripthandler = "submitdashboardfilter('dashboardform{$theBlock->instance->id}')";
		}

		if (!empty($theBlock->config->filters)){
			$text .= dashboard_render_filters($theBlock, $javascripthandler);
		}
		if (!empty($theBlock->params)){
			$text .= dashboard_render_params($theBlock, $javascripthandler);
		}

		if (!$javascripthandler){ // has been emptied, then no autocommit
			$strdofilter = get_string('dofilter', 'block_dashboard');
			$text .= "&nbsp;&nbsp;<input type=\"button\" onclick=\"autosubmit = 1; submitdashboardfilter('dashboardform{$theBlock->instance->id}')\" value=\"$strdofilter\" />";
			$text .= '<script type="text/javascript"> autosubmit = 0; </script>'; // post inhibits the submit function as result of filtering construction
		}
		$text .= '</form>';
	}
	
	return $text;
}
?>
=======
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
>>>>>>> MOODLE_33_STABLE
