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
 * Main renderer class for block dashboard
 */
class block_dashboard_renderer extends plugin_renderer_base {

    /**
     * An HTML raster for a matrix cross table
     * printing raster uses a recursive cell drilldown over dynamic matrix dimension
     *
     * @param ibject $theblock a dashboard block instance
     * @param arrayref &$m the data matrix
     * @param arrayref &$hcols an array f vertical columns descriptors
     * @param string $hkey the name of the dimension that deploys the horizontal columns
     * @param arrayref &$vkeys the dimension descriptors that will key vertically the results lines
     * @param string $hlabel the label for the horizontal dimension
     */
    public function cross_table(&$theblock, &$m, &$hcols, $hkey, &$vkeys, $hlabel, $return = false) {

        $str = '';

        $this->table_header($str, $hcols, $vkeys, $hlabel, @$theblock->config->horizsums);

        // Print flipped array.
        $path = array();

        $subsums = new StdClass;
        $subsums->subs = array();
        $subsums->all = array();

        dashboard_table_explore_rec($theblock, $str, $path, $hcols, $m, $vkeys, $hlabel, count($vkeys->formats), $subsums);

        if (!empty($theblock->config->vertsums)) {

            // If vertsums are enabled, print vertsubs.

            $str .= '<tr>';
            $span = count($vkeys->labels);
            $subtotalstr = get_string('subtotal', 'block_dashboard');
            $str .= "<td colspan=\"{$span}\">$subtotalstr</td>";
            foreach ($hcols as $col) {
                $str.= '<td class="coltotal">'.$subsums->subs[$col].'</td>';
            }
            if (!empty($theblock->config->horizsums)) {
                $str .= '<td></td>';
            }
            $str .= '</tr>';

            // Print big total.
            $str .= '<tr>';
            $span = count($vkeys->labels);
            $subtotalstr = get_string('total', 'block_dashboard');
            $str .= "<td colspan=\"{$span}\">$subtotalstr</td>";
            foreach ($hcols as $col) {
                $str.= '<td class="coltotal"><b>'.$subsums->all[$col].'</b></td>';
            }
            if (!empty($theblock->config->horizsums)) {
                $str .= '<td></td>';
            }
            $str .= '</tr>';
        }

        $str .= '</table>';

        if ($return) {
            return $str;
        }
        echo $str;
    }

    /**
     * prints the table header cleverly depending on hkey and options
     * @param stringref &$str the output buffer
     * @param arrayref &$hcols the array of horizontal column descriptors
     * @param arrayref &$vkeys the dimension descriptors that will key vertically the results lines
     * @param string $hlabel the label for the horizontal dimension
     * @param bool $horizsums do we display line summators at end of line ? 
     */
    public function table_header(&$str, &$hcols, &$vkeys, $hlabel, $horizsums = false) {

        $str .= '<table width="100%" class="dashboard-table">';

        $vkc = 0;
        foreach ($vkeys->labels as $vk) {
            $vkc++;
        }

        if (!empty($hlabel)) {
            $str .= '<tr valign="top" class="row">';
            if ($vkc > 1) {
                $str .= '<th colspan="'.$vkc.'">&nbsp;</th>';
            } else {
                $str .= '<th class="c0">&nbsp;</th>';
            }
            $str .= '<th class="dashboard-horiz-serie" colspan="'.count($hcols).'">'.$hlabel.'</th>';
            $str .= '</tr>';
        }

        $str .= '<tr class="row">';

        $vlabels = array_values($vkeys->labels);

        foreach ($vkeys->labels as $vk => $vlabel) {
            $str .= '<td class="dashboard-vertical-series">'.$vlabel.'</td>';
        }

        foreach ($hcols as $hc) {
            $str .= '<td class="hkey">'.$hc.'</td>';
        }

        if ($horizsums) {
            $totalstr = get_string('total', 'block_dashboard');
            $str .= '<td class="hkeytotal">'.$totalstr.'</td>';
        }

        // Close title line.
        $str .= '</tr>';
    }

    /**
     * prints a smart tree with data
     * @param objectref $theblock full block information
     * @param structref $tree the tree organized representation of records
     * @param arrayref $treeoutput an array of pair column,format information for making the tree node name
     * @param arrayref $outputfields an array of fields for tree node value information
     * @param arrayref $outputformats formats for above
     * @param arrayref $colourcoding an array of colour coding rules issued from table scope colourcoding settings
     */
    public function tree_view(&$theblock, &$tree, &$treeoutput, &$outputfields, &$outputformats, &$colorcoding) {
        static $level = 1;

        $str = '';

        asort($tree);

        $str .= '<ul class="dashboard-tree'.$level.'">';
        $level++;
        foreach ($tree as $key => $node) {
            $nodestrs = array();
            foreach ($treeoutput as $field => $formatter) {
                if (empty($field)) {
                    continue;
                }
                if (!empty($formatter)) {
                    $datum = dashboard_format_data($formatter, $node->$field);
                } else {
                    $datum = $node->$field;
                }
                if (!empty($theblock->config->colorfield) && $theblock->config->colorfield == $field) {
                    // We probably prefer inline coloouring here, rather than div block.
                    $datum = dashboard_colour_code($theblock, $datum, $colorcoding, true);
                }
                $nodestrs[] = $datum;
            }
            $nodecontent = implode(' ', $nodestrs);
            $nodedata = array();
            foreach ($outputformats as $field => $formatter) {
                if (empty($field)) {
                    continue;
                }
                if (!empty($formatter)) {
                    $datum = dashboard_format_data($formatter, $node->$field);
                } else {
                    $datum = $node->$field;
                }
                if (!empty($theblock->config->colorfield) && $theblock->config->colorfield == $field) {
                    // We probably prefer inline coloouring here, rather than div block.
                    $datum = dashboard_colour_code($theblock, $datum, $colorcoding, true);
                }
                $nodedata[] = $datum;
            }
            $nodedatastr = implode(' ', $nodedata);
            $str .= "<li>$nodecontent <div style=\"float:right\">$nodedatastr</div></li>";
            if (!empty($node->childs)) {
                $str .= $this->tree_view($theblock, $node->childs, $treeoutput, $outputfields, $outputformats, $colorcoding);
            }
        }
        $level--;
        $str .= '</ul>';

        return $str;
    }

    /**
     * prints and format data for googlemap plotting.
     */
    public function googlemaps_data(&$theblock, &$data, &$graphdesc) {

        $str = '';

        if (!empty($config->datalocations)) {
            // Data comes from query and locating information from datalocations field mapping.
            $googlelocs = explode(";", $theblock->config->datalocations);
            if (!empty($data)) {
                foreach ($data as $d) {
                    $t = $d->{$theblock->config->datatitles};
                    if (count($googlelocs) == 1) {
                        list($lat,$lng) = explode(',', $d->{$theblock->config->datalocations});
                        $type = $d->{$theblock->config->datatypes};
                        $gmdata[] = array('title' => $t, 'lat' => 0 + $lat, 'lng' => 0 + $lng, 'markerclass' => $type);
                    } else if (count($googlelocs) == 4) {
                        // We expect an address,postcode,city,region field list. If some data is quoted, take it as "constant".
                        $addresselms = explode(';', $theblock->config->datalocations);
                        $addressfield = trim($addresselms[0]);
                        $postcodefield = trim($addresselms[1]);
                        $cityfield = trim($addresselms[2]);
                        $regionfield = trim($addresselms[3]);
                        $address = $d->{$addressfield};
                        if (preg_match('/^(?:\'|")([^\']*)(?:\'|")$/', $postcodefield, $matches)) {
                            $postcode = $matches[1];
                        } else {
                            $postcode = $d->{$postcodefield};
                        }
                        if (preg_match('/^(?:\'|")([^\']*)(?:\'|")$/', $cityfield, $matches)) {
                            $city = $matches[1];
                        } else {
                            $city = preg_replace('/cedex.*/i', '', $d->{$cityfield}); // remove postal alterations
                        }
                        if (preg_match('/^(?:\'|")([^\']*)(?:\'|")$/', $regionfield, $matches)) {
                            $region = $matches[1];
                        } else {
                            $region = $d->{$regionfield};
                        }
                        $googleerrors = array();
                        if ($location = googlemaps_get_geolocation($region, $address, $postcode, $city, $googleerrors)) {
                            list($lat,$lng) = explode(',', $location);
                            $type = $d->{$theblock->config->datatypes};
                            $gmdata[] = array('title' => $t, 'lat' => $lat, 'lng' => $lng, 'markerclass' => $type);
                        }
                    } else {
                        $str .= '<span class="error">'.get_string('googlelocationerror', 'block_dashboard').'</span>';
                        break;
                    }
                }
            }
        } else {
            $text .= " This is a demo set !! ";
            /**
             * demo
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

        $str .= googlemaps_embed_graph('dashboard'.$theblock->instance->id, @$theblock->config->lat, @$theblock->config->lng, @$theblock->config->graphwidth, $this->config->graphheight, $graphdesc, $gmdata, true);

        return $str;
    }

    /**
     * print forms for filters and user defined live parameters
     * @param objectref &$theblock a dashboard block instance
     * @param string $sort name of the actual sorting column
     */
    public function filters_and_params_form(&$theblock, $sort) {
        global $COURSE;

        $text = '';

        if (!empty($theblock->config->filters) || !empty($theblock->params)) {
            $text .= '<form class="dashboard-filters" name="dashboardform'.$theblock->instance->id.'" method="GET">';
            $text .= '<input type="hidden" name="id" value="'.$COURSE->id.'" />';
            if (!@$theblock->config->inblocklayout) {
                $text .= '<input type="hidden" name="blockid" value="'.$theblock->instance->id.'" />';
            } else {
                $blockid = optional_param('blockid', 0, PARAM_INT);
                $text .= '<input type="hidden" name="blockid" value="'.$blockid.'" />';
            }
            if ($COURSE->format == 'page') {
                if (!empty($coursepage)) {
                    $text .= '<input type="hidden" name="page" value="'.$flexpage->id.'" />';
                }
            }
            if ($sort == 'id DESC') {
                $sort = '';
            }
            $text .= '<input type="hidden" name="tsort'.$theblock->instance->id.'" value="'.$sort.'" />';

            // TODO repair or remove
            // $autosubmit = (count(array_keys($theblock->filters)) + count(array_keys($theblock->params))) <= 1;
            $autosubmit = false;

            $javascripthandler = '';
            if ($autosubmit) {
                $javascripthandler = "submitdashboardfilter('dashboardform{$theblock->instance->id}')";
            }

            if (!empty($theblock->config->filters)) {
                $text .= $this->filters($theblock, $javascripthandler);
            }
            if (!empty($theblock->params)) {
                $text .= $this->params($theblock, $javascripthandler);
            }

            if (!$javascripthandler) {
                // Has been emptied, then no autocommit.
                $strdofilter = get_string('dofilter', 'block_dashboard');
                $jshandler = 'autosubmit = 1; submitdashboardfilter(\'dashboardform'.$theblock->instance->id.'\')';
                $text .= '&nbsp;&nbsp;<input type="button" onclick="'.$jshandler.'" value="'.$strdofilter.'" />';
                // Post inhibits the submit function as result of filtering construction.
                $text .= '<script type="text/javascript"> autosubmit = 0; </script>';
            }
            $text .= '</form>';
        }

        return $text;
    }

    /**
     * get value range, print and sets up data filters
     * @param objectref $theblock instance of a dashboard block
     * @param string $javascripthandler if empty, no onchange handler is required. Filter change
     * is triggered by an explicit button.
     *
     * Javascript handler is provided when preparing form overrounding.
     */
    public function filters(&$theblock, $javascripthandler) {

        $str = '';

        $alllabels = array_keys($theblock->filterfields->labels);

        foreach ($alllabels as $afield) {

            if (empty($afield)) {
                // Protects against empty filterset.
                continue;
            }

            $cond = isset($theblock->filterfields->translations[$afield]);
            $fieldname = ($cond) ? $theblock->filterfields->translations[$afield] : $afield;

            $filterresults = $theblock->filter_get_results($afield, $fieldname, false, false, $str);

            if ($filterresults) {
                $filterset = array();
                if (!$theblock->is_filter_single($afield)) {
                    $filterset['0'] = '*';
                }
                foreach (array_values($filterresults) as $value) {
                    // Removes table scope explicitators.
                    $radical = preg_replace('/^.*\./', '', $fieldname);
                    $filterset[$value->$radical] = $value->$radical;
                }
                $str .= '<span class="dashboard-filter">'.$theblock->filterfields->labels[$afield].':</span>';
                $multiple = (strstr($theblock->filterfields->options[$afield], 'm') === false) ? false : true;
                $arrayform = ($multiple) ? '[]' : '';

                if (!is_array(@$theblock->filtervalues[$radical])) {
                    $unslashedvalue = stripslashes(@$theblock->filtervalues[$radical]);
                } else {
                    $unslashedvalue = $theblock->filtervalues[$radical];
                }

                // Build the select options.
                $selectoptions = array();

                if (!empty($javascripthandler)) {
                    $selectoptions['onchange'] = $javascripthandler;
                }

                if ($multiple) {
                    $selectoptions['multiple'] = 1;
                    $selectoptions['size'] = 5;
                }

                if ($theblock->is_filter_global($afield)) {
                    $key = "filter0_{$radical}{$arrayform}";
                    $str .= html_writer::select($filterset, $key, $unslashedvalue, array('' => 'choosedots'), $selectoptions);
                } else {
                    $key = "filter{$theblock->instance->id}_{$radical}{$arrayform}";
                    $str .= html_writer::select($filterset, $key, $unslashedvalue, array('' => 'choosedots'), $selectoptions);
                }
                $str .= "&nbsp;&nbsp;";
            }
        }

        return $str;
    }

    /**
     * if there are some user params, print widgets for them. If one of them is a daterange, 
     * then cancel the javascripthandler as we will need to explictely submit.
     *
     * @param objectref $theblock a dashboard block instance
     * @param string $javascripthandler if empty, no onchange handler is required. Filter change
     * is triggered by an explicit button.
     */
    public function params(&$theblock, &$javascripthandler) {

        $str = '';

        $str .= '<div class="dashboard-sql-params">';
        foreach ($theblock->params as $key => $param) {
            $htmlkey = preg_replace('/[.() *]/', '', $key).'_'.$theblock->instance->id;
            switch ($param->type) {

                case 'choice':
                    $values = explode("\n", $param->values);
                    $option1checked = ($param->value == $values[0]) ? 'checked="checked"' : '';
                    $option2checked = ($param->value == $values[1]) ? 'checked="checked"' : '';
                    $str .= ' '.$param->label.': <input type="radio" name="'.$htmlkey.'" value="'.htmlentities($values[0], ENT_QUOTES, 'UTF-8').'" '.$option1checked.' onchange="'.$javascripthandler.'" /> '.$values[0];
                    $str .= ' - <input type="radio" name="'.$key.'" value="'.htmlentities($values[1], ENT_QUOTES, 'UTF-8').'" '.$option2checked.'  onchange="'.$javascripthandler.'"/> '.$values[1].' &nbsp;&nbsp;';
                    break;

                case 'text':
                    $str .= ' '.$param->label.': <input type="text" size="10" name="'.$htmlkey.'" value="'.htmlentities($param->value, ENT_QUOTES, 'UTF-8').'" onchange="'.$javascripthandler.'" /> ';
                    break;

                case 'list':
                    $str .= ' '.$param->label.': <select name="'.$htmlkey.'" >';
                    foreach($param->values as $v) {
                        $vselected = ($v == $param->value) ? ' selected="selected" ' : '';
                        $str .= '<option value="'.$v.'" '.$vselected.'>'.$v.'</option>';
                    }
                    break;

                case 'range':
                    $str .= ' '.$param->label.': '.get_string('from', 'block_dashboard').' <input type="text" size="10" name="'.$htmlkey.'_from" value="'.htmlentities($param->valuefrom, ENT_QUOTES, 'UTF-8').'"  /> ';
                    $str .= ' '.get_string('to', 'block_dashboard').' <input type="text" size="10" name="'.$htmlkey.'_to" value="'.htmlentities($param->valueto, ENT_QUOTES, 'UTF-8').'"  />';
                    $javascripthandler = '';  // Cancel the autosubmit possibility.
                    break;

                case 'date':
                    $str .= ' '.$param->label.': <input type="text" size="10"  id="date-'.$htmlkey.'" name="'.$htmlkey.'" value="'.$param->originalvalue.'"  onchange="'.$javascripthandler.'" />';
                    $str .= '<script type="text/javascript">'."\n";
                    $str .= 'var '.$htmlkey.'Cal = new dhtmlXCalendarObject(["date-'.$htmlkey.'"]);'."\n";
                    $str .= $htmlkey.'Cal.loadUserLanguage(\''.current_language().'_utf8\');'."\n";
                    $str .= '</script>'."\n";
                    break;

                case 'daterange':
                    $str .= ' '.$param->label.': '.get_string('from', 'block_dashboard').' <input type="text" size="10"  id="date-'.$htmlkey.'_from" name="'.$htmlkey.'_from" value="'.$param->originalvaluefrom.'" />';
                    $str .= ' '.get_string('to', 'block_dashboard').' <input type="text" size="10"  id="date-'.$htmlkey.'_to" name="'.$htmlkey.'_to" value="'.$param->originalvalueto.'" />';
                    $str .= '<script type="text/javascript">'."\n";
                    $str .= 'var '.$htmlkey.'fromCal = new dhtmlXCalendarObject([\'date-'.$htmlkey.'_from\', \'date-'.$htmlkey.'_to\']);'."\n";
                    $str .= $htmlkey.'fromCal.loadUserLanguage(\''.current_language().'_utf8\');'."\n";
                    $str .= $htmlkey.'fromCal.setSkin(\'dhx_web\');';
                    $str .= '</script>'."\n";
                    $javascripthandler = ''; // Cancel the autosubmit possibility.
                    break;
            }
        }

        $str .= '</div>';
        return $str;
    }

    /**
     * Renders each declared sum as HTML
     *
     */
    function numsums(&$theblock, &$aggr) {

        $str = '';

        $str .= $this->output->box_start('dashboard-sumative-box', '');
        foreach (array_keys($theblock->numsumsf) as $numsum) {
            if (!empty($theblock->numsumsf[$numsum])) {
                $formattedsum = dashboard_format_data($theblock->numsumsf[$numsum], @$aggr->$numsum);
            } else {
                $formattedsum = 0 + @$aggr->$numsum;
            }
            $str .= $theblock->outputnumsums[$numsum].' : <b>'.$formattedsum.'</b>&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        $str .= $this->output->box_end(true);

        return $str;
    }

    public function export_buttons(&$theblock, $filterquerystring) {
        global $COURSE;

        // Passed to each buttons.
        $this->sort = optional_param('tsort'.$theblock->instance->id, @$theblock->config->defaultsort, PARAM_TEXT);

        $tableexportstr = get_string('exportdataastable', 'block_dashboard');

        $str = '<div class="dashboard-table-buttons">';

        $str .= $this->allexport_button($theblock);
        if ($filterquerystring) {
            $str .= $this->filteredexport_button($theblock, $filterquerystring);
        }
        if (empty($theblock->config->filepathadminoverride)) {
            $str .= $this->fileview_button($theblock);
        }
        $str .= $this->filteredoutput_button($theblock, $filterquerystring);
        $str .= "</div>";

        return $str;
    }

    public function tabular_buttons($theblock, $filterquerystring) {
        global $COURSE;

        $this->sort = optional_param('tsort'.$theblock->instance->id, @$theblock->config->defaultsort, PARAM_TEXT);

        $tableexportstr = get_string('exportdataastable', 'block_dashboard');

        $str = '<div class="dashboard-table-buttons">';

        $str .= $this->allexport_button($theblock);

        $params = array('id' => $COURSE->id,
                        'instance' => $theblock->instance->id,
                        'tsort'.$theblock->instance->id => $this->sort);
        $exporturl = new moodle_url('/blocks/dashboard/export/export_csv_tabular.php', $params);
        $str .= $this->output->single_button($exporturl.$filterquerystring, $tableexportstr);

        if (empty($theblock->config->filepathadminoverride)) {
            $str .= $this->fileview_button($theblock);
        }
        $str .= $this->filteredoutput_button($theblock, $filterquerystring);

        $str .= '</div>';

        return $str;
    }

    public function tree_buttons($theblock, $filterquerystring) {
        global $COURSE;

        // passed to each buttons.
        $this->sort = optional_param('tsort'.$theblock->instance->id, @$theblock->config->defaultsort, PARAM_TEXT);

        $str = '<div class="dashboard-table-buttons">';

        $str .= $this->allexport_button($theblock);
        if (empty($theblock->config->filepathadminoverride)) {
            $str .= $this->fileview_button($theblock);
        }
        $str .= $this->filteredoutput_button($theblock, $filterquerystring);

        $str .= '</div>';

        return $str;
    }

    protected function allexport_button($theblock) {
        global $COURSE;

        $allexportstr = get_string('exportall', 'block_dashboard');
        $params = array('id' => $COURSE->id,
                        'instance' => $theblock->instance->id,
                        'tsort'.$theblock->instance->id => $this->sort,
                        'alldata' => 1);
        $exporturl = new moodle_url('/blocks/dashboard/export/export_csv.php', $params);
        return $this->output->single_button($exporturl, $allexportstr);
    }

    protected function filteredexport_button(&$theblock, $filterquerystring) {
        global $COURSE;

        $filteredexportstr = get_string('exportfiltered', 'block_dashboard');
        $params = array('id' => $COURSE->id,
                        'instance' => $theblock->instance->id,
                        'tsort'.$theblock->instance->id => $this->sort);
        $exporturl = new moodle_url('/blocks/dashboard/export/export_csv.php', $params);
        return $this->output->single_button($exporturl.$filterquerystring, $filteredexportstr);
    }

    protected function fileview_button(&$theblock) {
        global $COURSE;

        $filesviewstr = get_string('filesview', 'block_dashboard');
        $params = array('id' => $COURSE->id,
                        'instance' => $theblock->instance->id);
        $fileareaurl = new moodle_url('/blocks/dashboard/export/filearea.php', $params);
        return $this->output->single_button($fileareaurl, $filesviewstr);
    }

    protected function filteredoutput_button(&$theblock, $filterquerystring) {
        global $COURSE;

        $filteredoutputstr = get_string('outputfiltered', 'block_dashboard');
        $params = array('id' => $COURSE->id,
                        'instance' => $theblock->instance->id,
                        'tsort'.$theblock->instance->id => $this->sort);
        $exporturl = new moodle_url('/blocks/dashboard/export/export_output_csv.php', $params);
        return $this->output->single_button($exporturl.$filterquerystring, $filteredoutputstr);
    }

    /**
     * Builds column sorting controls
     * @param string $fieldname the fieldname represented by the current data column
     * @param string $sort the current sorting state
     * @todo : move to renderer
     */
    public function sort_controls($theblock, $fieldname, $sort) {

        $str = '';

        $baseurl = new moodle_url(me());
        $baseurl->remove_params('tsort');

        if (preg_match('/(\w*?) DESC/', $sort, $matches)) {
            $sortfield = $matches[1];
            $dir = 'DESC';
        } else {
            $sortfield = str_replace(' ASC', '', $sort);
            $dir = 'ASC';
        }

        if ($sortfield != $fieldname) {
            $pix = '<img src="'.$this->output->pix_url('sinactive', 'block_dashboard').'" />';
            $str .= '&nbsp;<a href="'.$baseurl.'&tsort'.$theblock->instance->id.'='.$fieldname.' ASC">'.$pix.'</a>';
        } else {
            if ($dir == 'DESC') {
                $pix = '<img src="'.$this->output->pix_url('sdesc', 'block_dashboard').'" />';
                $str .= '&nbsp;<a href="'.$baseurl.'&tsort'.$theblock->instance->id.'='.$fieldname.' ASC">'.$pix.'</a>';
            } else {
                $pix = '<img src="'.$this->output->pix_url('sasc', 'block_dashboard').'" />';
                $str .= '&nbsp;<a href="'.$baseurl.'&tsort'.$theblock->instance->id.'='.$fieldname.' DESC">'.$pix.'</a>';
            }
        }
        return $str;
    }

    public function dhtmlxcalendar_style() {
        global $CFG;

        $cssurl = $CFG->wwwroot.'/blocks/dashboard/js/dhtmlxCalendar/codebase/dhtmlxcalendar.css';
        $str = '<link type="text/css" rel="stylesheet" href="'.$cssurl.'" />';
        $cssurl = $CFG->wwwroot.'/blocks/dashboard/js/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_web.css';
        $str .= '<link type="text/css" rel="stylesheet" href="'.$cssurl.'" />';
        return $str;
    }
}