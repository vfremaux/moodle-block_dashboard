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
 * Main renderer class for block dashboard
 */
class block_dashboard_renderer extends plugin_renderer_base {

    /**
     * Print tabs for setup screens.
     * @param object $theblock a dashboard block instance
     */
    function setup_tabs($theblock) {

        $config = $theblock->config;

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

        $str = '<div id="dashboardsettings-menu" class="tabtree">';
        $str .= '<ul class="nav nav-tabs">';
        foreach ($tabs as $tabarr) {
            list($tabkey, $tabname, $visible) = $tabarr;
            $class = ($tabkey == 'querydesc') ? 'active ' : '';
            $class .= ($visible) ? "on" : "off" ;
            $tabname = str_replace(' ', '&nbsp;', $tabname);
            $str .= '<li id="setting-tab-'.$tabkey.'" class="setting-tab '.$class.'"><a href="Javascript:open_panel(\''.$tabkey.'\')"><span>'.$tabname.'</span></a></li> ';
        }
        $str .= '</ul>';

        return $str;
    }

    /**
     *
     * @param object $theblock a dashboard block instance
     */
    function setup_returns($theblock) {
        global $COURSE;

        $str = '<table width="100%" cellpadding="5" cellspacing="0">';
        $str .= '<tr>';
        $str .= '<td colspan="3" align="center">';

        $params = array('id' => $COURSE->id, 'instance' => $theblock->instance->id, 'what' => 'upload');
        $copyurl = new moodle_url('/blocks/dashboard/copyconfig.php', $params);
        $str .= '<a href="'.$copyurl.'"><input type="button" name="go_import" value="'.get_string('importconfig', 'block_dashboard').'"></a>&nbsp;';

        $params = array('id' => $COURSE->id, 'instance' => $theblock->instance->id, 'what' => 'get');
        $exporturl = new moodle_url('/blocks/dashboard/copyconfig.php', $params);
        $str .= '<a href="'.$exporturl.'" target="_blank"><input type="button" name="go_export" value="'.get_string('exportconfig', 'block_dashboard').'"></a>&nbsp;';
        $str .= '<input type="submit" name="submit" value="'.get_string('savechanges').'" />&nbsp;';
        $str .= '<input type="submit" name="save" value="'.get_string('savechangesandconfig', 'block_dashboard').'" />';
        $str .= '<input type="submit" name="saveview" value="'.get_string('savechangesandview', 'block_dashboard').'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';

        return $str;
    }

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
    function cross_table(&$theblock, &$m, &$hcols, $hkey, &$vkeys, $hlabel, $return = false) {

        $str = '';

        $this->table_header($str, $hcols, $vkeys, $hlabel, @$theblock->config->horizsums);

        // Print flipped array.
        $path = array();

        $subsums = new StdClass;
        $subsums->subs = array();
        $subsums->all = array();

        table_explore_rec($theblock, $str, $path, $hcols, $m, $vkeys, $hlabel, count($vkeys->formats), $subsums);

        if (!empty($theblock->config->vertsums)) {

            // If vertsums are enabled, print vertsubs.

            $str .= '<tr>';
            $span = count($vkeys->labels);
            $subtotalstr = get_string('subtotal', 'block_dashboard');
            $str .= "<td colspan=\"{$span}\">$subtotalstr</td>";
            foreach ($hcols as $col) {
                $str.= "<td class=\"coltotal\">{$subsums->subs[$col]}</td>";
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
                $str.= "<td class=\"coltotal\"><b>{$subsums->all[$col]}</b></td>";
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
    function table_header(&$str, &$hcols, &$vkeys, $hlabel, $horizsums = false) {

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
    
        // close title line
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
    function tree_view(&$theblock, &$tree, &$treeoutput, &$outputfields, &$outputformats, &$colorcoding) {
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
                    // we probably prefer inline coloouring here, rather than div block.
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
                    // we probably prefer inline coloouring here, rather than div block.
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
    function googlemaps_data(&$theblock, &$data, &$graphdesc) {

        $str = '';

        if (!empty($config->datalocations)) {
            // data comes from query and locating information from datalocations field mapping
            $googlelocs = explode(";", $theblock->config->datalocations);
            if (!empty($data)) {
                foreach ($data as $d) {
                    $t = $d->{$theblock->config->datatitles};
                    if (count($googlelocs) == 1) {
                        list($lat,$lng) = explode(',', $d->{$theblock->config->datalocations});
                        $type = $d->{$theblock->config->datatypes};
                        $gmdata[] = array('title' => $t, 'lat' => 0 + $lat, 'lng' => 0 + $lng, 'markerclass' => $type);
                    } elseif (count($googlelocs) == 4) {
                        // we expect an address,postcode,city,region field list. If some data is quoted, take it as "constant"
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

        $str .= googlemaps_embed_graph('dashboard'.$theblock->instance->id, @$theblock->config->lat, @$theblock->config->lng, @$theblock->config->graphwidth, $this->config->graphheight, $graphdesc, $gmdata, true);

        return $str;
    }

    /**
     * print forms for filters and user defined live parameters
     * @param objectref &$theblock a dashboard block instance
     * @param string $sort name of the actual sorting column
     */
    function filters_and_params_form(&$theblock, $sort) {
        global $COURSE;

        $text = '';

        if (!empty($theblock->config->filters) || !empty($theblock->params)) {
            $text .= '<form class="dashboard-filters" name="dashboardform'.$theblock->instance->id.'" method="GET">';
            $text .= '<input type="hidden" name="id" value="'.s($COURSE->id).'" />';
            if (!@$theblock->config->inblocklayout){
                $text .= '<input type="hidden" name="blockid" value="'.s($theblock->instance->id).'" />';
            }
            if ($COURSE->format == 'page') {
                if (!empty($coursepage)){
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
                // has been emptied, then no autocommit
                $strdofilter = get_string('dofilter', 'block_dashboard');
                $text .= "&nbsp;&nbsp;<input type=\"button\" onclick=\"autosubmit = 1; submitdashboardfilter('dashboardform{$theblock->instance->id}')\" value=\"$strdofilter\" />";
                $text .= '<script type="text/javascript"> autosubmit = 0; </script>'; // post inhibits the submit function as result of filtering construction
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
    function filters(&$theblock, $javascripthandler) {

        $str = '';

        $alllabels = array_keys($theblock->filterfields->labels);

        foreach ($alllabels as $afield) {

            if (empty($afield)) {
                // protects against empty filterset;
                continue;
            }

            $fieldname = (isset($theblock->filterfields->translations[$afield])) ? $theblock->filterfields->translations[$afield] : $afield ;

            // $filterresults = $theblock->filter_get_results($theblock->sql, $afield, $fieldname, false, false, $str);
            $filterresults = $theblock->filter_get_results($afield, $fieldname, false, false, $str);

            if ($filterresults) {
                $filterset = array();
                if (!$theblock->is_filter_single($afield)) {
                    $filterset['0'] = '*';
                }
                foreach (array_values($filterresults) as $value) {
                    $radical = preg_replace('/^.*\./', '', $fieldname); // removes table scope explicitators
                    $filterset[$value->$radical] = $value->$radical;
                }
                $str .= '<span class="dashboard-filter">'.$theblock->filterfields->labels[$afield].':</span>';
                $multiple = (strstr($theblock->filterfields->options[$afield], 'm') === false) ? false : true ; 
                $arrayform = ($multiple) ? '[]' : '' ;

                if (!is_array(@$theblock->filtervalues[$radical])) {
                    $unslashedvalue = stripslashes(@$theblock->filtervalues[$radical]);
                } else {
                    $unslashedvalue = $theblock->filtervalues[$radical];
                }

                // build the select options
                $selectoptions = array();

                if (!empty($javascripthandler)) {
                    $selectoptions['onchange'] = $javascripthandler;
                }

                if ($multiple) {
                    $selectoptions['multiple'] = 1;
                    $selectoptions['size'] = 5;
                }

                if ($theblock->is_filter_global($afield)) {
                    $str .= html_writer::select($filterset, "filter0_{$radical}{$arrayform}", $unslashedvalue, array('' => 'choosedots'), $selectoptions);
                } else {
                    $str .= html_writer::select($filterset, "filter{$theblock->instance->id}_{$radical}{$arrayform}", $unslashedvalue, array('' => 'choosedots'), $selectoptions);
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
    function params(&$theblock, &$javascripthandler) {

        $str = '';

        $str .= '<div class="dashboard-sql-params">';
        foreach ($theblock->params as $key => $param) {
            $htmlkey = preg_replace('/[.() *]/', '', $key).'_'.$theblock->instance->id;
            switch ($param->type) {

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
                    foreach($param->values as $v) {
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
                    // $str .= ''.$htmlkey.'Cal.attachEvent(\'ontimeChange\', function() {'.$javascripthandler.';});'."\n";
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
                    $javascripthandler = ''; // cancel the autosubmit possibility
                    break;
            }
        }

        $str .= '</div>';
        return $str;
    }
}