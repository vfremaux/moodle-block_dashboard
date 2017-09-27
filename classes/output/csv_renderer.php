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
 *  Exporter of dashboard data snapshot
 */
namespace block_dashboard\output;

defined('MOODLE_INTERNAL') || die();

class csv_renderer extends \plugin_renderer_base {

    public function export($theblock) {

        $config = get_config('block_dashboard');

        $str = '';

        // Print column names.
        $headrow = array();
        foreach ($theblock->output as $field => $label) {
            $headrow[] = $label;
        }

        if ($theblock->config->exportcharset == 'utf8') {
            $str .= utf8_decode(implode($config->csv_field_separator, $headrow)); 
        } else {
            $str .= implode($config->csv_field_separator, $headrow); 
        }
        $str .=  $config->csv_line_separator;

        // Print data.
        foreach ($theblock->results as $r) {
            $row = array();
            foreach ($theblock->output as $field => $label) {

                // Did we ask for cumulative results ?
                $cumulativeix = null;
                if (preg_match('/S\((.+?)\)/', $field, $matches)) {
                    $field = $matches[1];
                    $cumulativeix = $theblock->instance->id.'_'.$field;
                }

                if (!empty($theblock->outputf[$field])) {
                    $datum = dashboard_format_data($theblock->outputf[$field], @$r->$field, $cumulativeix);
                } else {
                    $datum = dashboard_format_data(null, @$r->$field, $cumulativeix);
                }
                $row[] = $datum;
            }
            if ($theblock->config->exportcharset == 'utf8') {
                $str .= utf8_decode(implode($config->csv_field_separator, $row));
            } else {
                $str .= implode($config->csv_field_separator, $row);
            }
            $str .= $config->csv_line_separator;
        }
        return $str;
    }

    public function generate_output_file(&$theblock, &$results) {

        $config = get_config('block_dashboard');

        if (!empty($theblock->config->makefile) && !empty($results)) {

            // Output from query.
            if (!empty($theblock->config->fileoutput)) {
                if (function_exists('debug_trace')) {
                    debug_trace('Task generating '.$theblock->instance->id.' in format '.$theblock->config->fileformat.' using File Output Definitions');
                }
                $outputfields = explode(';', $theblock->config->fileoutput);
                $outputformats = explode(';', $theblock->config->fileoutputformats);
            } else {
                if (function_exists('debug_trace')) {
                    debug_trace('Task generating '.$theblock->instance->id.' in format '.$theblock->config->fileformat.' using Screen Output Definitions');
                }
                $outputfields = explode(';', $theblock->config->outputfields);
                $outputformats = explode(';', $theblock->config->outputformats);
            }
            dashboard_normalize($outputfields, $outputformats); // Normalizes labels to keys.
            $theblock->outputf = array_combine($outputfields, $outputformats);

            mtrace('   ... generating file for instance '.$theblock->instance->id.' in format '.$theblock->config->fileformat);
            if (!empty($theblock->outputf)) {

                $filestr = '';

                if ($theblock->config->fileformat == 'CSV') {
                    // Print col names.
                    $rarr = array();
                    foreach ($theblock->outputf as $key => $format) {
                        $rarr[] = $key;
                    }
                    $filestr .= implode($config->csv_field_separator, $rarr);
                    $filestr .= $config->csv_line_separator;
                }

                if (($theblock->config->fileformat == 'CSV') ||
                        ($theblock->config->fileformat == 'CSVWH')) {
                    // Print effective records.
                    $reccount = 0;
                    foreach ($results as $result) {
                        $rarr = array();
                        foreach ($theblock->outputf as $key => $format) {
                            if (empty($format)) {
                                $rarr[] = @$result->$key;
                            } else {
                                $rarr[] = dashboard_format_data($format, @$result->$key);
                            }
                        }
                        $filestr .= implode($config->csv_field_separator, $rarr);
                        $filestr .= $config->csv_line_separator;
                        $reccount++;
                    }
                    mtrace ($reccount.' processed');
                }

                if ($theblock->config->fileformat == 'SQL') {
                    if (empty($theblock->config->filesqlouttable)) {
                        mtrace('SQL required for output but no SQL table name given');
                        continue;
                    }
                    $colnames = array();
                    foreach($theblock->outputf as $key => $format) {
                        $colnames[] = $key;
                    }

                    $reccount = 0;
                    foreach ($results as $result) {
                        $values = array();
                        foreach ($theblock->outputf as $key => $format) {
                            if (empty($format)) {
                                $format = 'TEXT';
                            }
                            $values[] = dashboard_format_data($format, str_replace("'", "''", $result->$key));
                        }
                        $valuegroup = implode(",", $values);
                        $colgroup = implode(",", $colnames);
                        $statement = "INSERT INTO {$theblock->config->filesqlouttable}($colgroup) VALUES ($valuegroup);\n";
                        $filestr .= $statement;
                        $reccount++;
                    }
                    mtrace ($reccount.' processed');
                }

                return dashboard_output_file($theblock, $filestr);
            }
        }
    }

    /**
     * An HTML raster for a matrix cross table
     * printing raster uses a recursive cell drilldown over dynamic matrix dimension
     */
    function cross_table_csv(&$theblock, &$m, &$hcols) {

        $str = '';

        $str .= $this->table_header_csv($theblock, $hcols);
        // Print flipped array.
        $path = array();

        $theblock->subsums = new StdClass;
        $theblock->subsums->subs = array();
        $theblock->subsums->all = array();

        $this->table_explore_rec_csv($theblock, $path, $hcols, $m);

        return $str;
    }
    
    /**
     * Recursive worker for CSV table writing
     */
    function table_explore_rec_csv(&$theblock, &$pathstack, &$hcols, &$t) {
        global $CFG;

        $config = get_config('block_dashboard');

        static $level = 0;
        static $r = 0;

        $keydeepness = count($theblock->vertkeys->formats);
        $vformats = array_values($theblock->vertkeys->formats);
        $vcolumns = array_keys($theblock->vertkeys->formats);

        foreach ($t as $k => $v) {
            $plittable = false;
            array_push($pathstack, $k);

            $level++;
            if ($level < $keydeepness) {
                $this->table_explore_rec_csv($theblock, $pathstack, $hcols, $v, $return);
            } else {
                $r = ($r + 1) % 2;
                $c = 0;
                $pre = '';
                foreach ($pathstack as $pathelm) {
                    if (!empty($vformats[$c])) {
                        $pathelm = dashboard_format_data($vformats[$c], $pathelm);
                    }
                    if (!empty($theblock->config->cleandisplay)) {
                        if ($pathelm != @$vkeys->mem[$c]) {
                            $pre .= "$pathelm".$config->csv_field_separator;
                            if (isset($vkeys->mem[$c]) && @$theblock->config->spliton == $vcolumns[$c]) {
                                // First split do not play.
                                // If vertsums are enabled, print vertsubs.
                                if ($theblock->config->vertsums) {
                                    $span = count($pathstack);
                                    $subtotalstr = get_string('subtotal', 'block_dashboard');
                                    $str .= "$subtotalstr".$config->csv_field_separator;
                                    foreach ($hcols as $col) {
                                        $str .= $theblock->subsumsf->subs[$col].$config->csv_field_separator;
                                        $theblock->subsums->subs[$col] = 0;
                                    }
                                    if ($theblock->config->horizsums) {
                                        $str .= $config->csv_field_separator;
                                    }
                                    $str .= $config->csv_line_separator;
                                }

                                // Then close previous table.
                                $str .= $this->table_header_csv($theblock, $hcols);
                            }
                            $theblock->vertkeys->mem[$c] = $pathelm;
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
                foreach ($hcols as $col) {
                    if (array_key_exists($col, $v)) {
                        $str .= "{$v[$col]}".$config->csv_field_separator;
                    } else {
                        $str .= ''.$config->csv_field_separator;
                    }
                    $sum = dashboard_sum($sum, strip_tags(@$v[$col]));
                    if (@$theblock->config->vertsums) {
                        $theblock->subsums->subs[$col] = dashboard_sum(@$subsums->subs[$col], strip_tags(@$v[$col]));
                        $theblock->subsums->all[$col] = dashboard_sum(@$subsums->all[$col], strip_tags(@$v[$col]));
                    }
                    $c++;
                }

                if (@$theblock->config->horizsums) {
                    $str .= $sum.$config->csv_field_separator;
                }

                // Chop last value.

                $str = preg_replace("/{$config->csv_field_separator}$/", '', $str);

                $str .= $config->csv_line_separator;
            }
            $level--;
            array_pop($pathstack);
        }

        return $str;
    }

    /**
     * prints the first line as column titles
     */
    function dashboard_print_table_header_csv(&$str, &$theblock, &$hcols) {
        global $CFG;
    
        $config = get_config('block_dashboard');
    
        $vlabels = array_values($theblock->vertkeys->labels);
    
        $row = array();
        foreach ($theblock->vertkeys->labels as $vk => $vlabel) {
            $row[] = $vlabel;
        }
    
        foreach ($hcols as $hc) {
            $row[] = $hc;
        }
    
        if (isset($theblock->config->horizsums)) {
            $row[] = get_string('total', 'block_dashboard');
        }
    
        if ($theblock->config->exportcharset == 'utf8') {
            $str .= utf8_decode(implode($config->csv_field_separator, $row));
        } else {
            $str .= implode($config->csv_field_separator, $row);
        }
        $str .= $config->csv_line_separator;
    }

    public function render_filearea($template) {
        return $this->render_from_template('block_dashboard/filearea', $template);
    }
}