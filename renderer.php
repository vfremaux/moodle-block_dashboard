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
     * Real raster that prints graphs and data
     */
    public function render_dashboard($theblock) {
        global $CFG, $extradbcnx, $COURSE, $DB, $OUTPUT, $PAGE;

        $config = get_config('block_dashboard');
        $sort = optional_param('tsort'.$theblock->instance->id, @$theblock->config->defaultsort, PARAM_TEXT);

        $template = new StdClass;

        $template->dhtmlxcalendarstyle = $this->dhtmlxcalendar_style();

        if (!isset($theblock->config)) {
            $theblock->config = new StdClass;
        }
        $theblock->config->limit = 20;

        $coursepage = '';
        if ($COURSE->format == 'page') {
            include_once($CFG->dirroot.'/course/format/lib.php');
            $pageid = optional_param('page', 0, PARAM_INT); // Flexipage page number.
            if (!$pageid) {
                $flexpage = course_page::get_current_page($COURSE->id);
            } else {
                $flexpage = new StdClass;
                $flexpage->id = $pageid;
            }
            $coursepage = "&page=".$flexpage->id;
        }

        $rpage = optional_param('rpage'.$theblock->instance->id, 0, PARAM_INT); // Result page.

        if ($rpage < 0) {
            $rpage = 0;
        }

        // Editing security to avoid blocked dashboards blocking the ocurse page when displayed in block space.
        if ($PAGE->user_is_editing() && !empty($config->enable_isediting_security) && $theblock->config->inblocklayout == 1) {
            $template->errormsg = $OUTPUT->notification(get_string('editingnoexecute', 'block_dashboard'));
            return $this->render_from_template('block_dashboard/dashboard', $template);
        }

        // Unlogged people cannot see their status.
        if ((!isloggedin() || isguestuser()) && @$theblock->config->guestsallowed) {

            $template->errormsg = $OUTPUT->notification(get_string('guestsnotallowed', 'block_dashboard'));

            $loginstr = get_string('login');
            $loginurl = new moodle_url('/login/index.php');
            $template->errormsg .= '<a href="'.$loginurl.'">'.$loginstr.'</a>';
            return $this->render_from_template('block_dashboard/dashboard', $template);
        }

        if (!isset($theblock->config) || empty($theblock->config->query)) {
            $template->errormsg = $OUTPUT->notification(get_string('noquerystored', 'block_dashboard'));
            return $this->render_from_template('block_dashboard/dashboard', $template);
        }

        if (!isset($config->big_result_threshold)) {
            set_config('big_result_threshold', 500, 'block_dashboard');
            $config->big_result_threshold = 500;
        }

        // Connecting.
        if ($theblock->config->target == 'moodle') {
            // Already connected.
        } else {
            $error = '';
            if (!isset($extradbcnx)) {
                $extradbcnx = extra_db_connect(true, $error);
            }
            if ($error) {
                $template->errormsg = $error;
                return $this->render_from_template('block_dashboard/dashboard', $template);
            }
        }

        // Prepare all params from config.

        $theblock->prepare_config();

        $graphdata = array();
        $ticks = array();
        $filterquerystring = '';

        if (!empty($theblock->config->filters)) {
            try {
                $filterquerystring = $theblock->prepare_filters();
<<<<<<< HEAD
            } catch (Exception $e) {
=======
            } catch (\block_dashboard\filter_query_exception $e) {
>>>>>>> MOODLE_34_STABLE
                $filtersql = $theblock->filteredsql;
                $template->errormsg = '<div class="dashboard-query-box">';
                $template->errormsg .= '<pre>FILTER: '.$filtersql.'</pre>';
                $template->errormsg .= $DB->get_last_error();
                $template->errormsg .= '</div>';
                $template->errormsg .= $OUTPUT->notification(get_string('invalidorobsoletefilterquery', 'block_dashboard'));
                return $this->render_from_template('block_dashboard/dashboard', $template);
<<<<<<< HEAD
=======
            } catch (\block_dashboard\filter_query_cache_exception $e) {
                $filtersql = $theblock->filteredsql;
                $template->errormsg = '<div class="dashboard-query-box">';
                $template->errormsg .= '<pre>FILTER: '.$filtersql.'</pre>';
                $template->errormsg .= $DB->get_last_error();
                $template->errormsg .= '</div>';
                $template->errormsg .= $OUTPUT->notification(get_string('cachefilterqueryerror', 'block_dashboard'));
>>>>>>> MOODLE_34_STABLE
            }
        } else {
            $theblock->filteredsql = str_replace('<%%FILTERS%%>', '', $theblock->sql);
        }

        // Needed to prepare for filter range prefetch.
        $theblock->sql = str_replace('<%%FILTERS%%>', '', $theblock->sql);

        if (!empty($theblock->params)) {
            $filterquerystring = ($filterquerystring) ? $filterquerystring.'&'.$theblock->prepare_params() : $theblock->prepare_params();
        } else {
            $theblock->sql = str_replace('<%%PARAMS%%>', '', $theblock->sql); // Needed to prepare for filter range prefetch.
            $theblock->filteredsql = str_replace('<%%PARAMS%%>', '', $theblock->filteredsql); // Needed to prepare for filter range prefetch.
        }

        if (!empty($sort)) {
            // Do not sort if already sorted in explained query.
            if (!preg_match('/ORDER\s+BY/si', $theblock->sql)) {
                $theblock->filteredsql .= " ORDER BY $sort";
            }
        }

        $theblock->filteredsql = $theblock->protect($theblock->filteredsql);

        // GETTING RESULTS ---------------------------------------------------.

        try {
            $countres = $theblock->count_records($error);
        } catch (Exception $e) {
            $countres = 0;
        }

        // If too many results, we force paging mode.
        if (empty($theblock->config->pagesize) &&
                ($countres > $config->big_result_threshold) &&
                        !empty($theblock->config->bigresult)) {
            $template->warningmsg = $OUTPUT->notification(get_string('toomanyrecordsusepaging', 'block_dashboard'));
            $theblock->config->pagesize = $config->big_result_threshold;
            $rpage = 0;
        }

        // Getting real results including page and offset.

        if (!empty($theblock->config->pagesize)) {
            $offset = $rpage * $theblock->config->pagesize;
        } else {
            $offset = '';
        }

        try {
            $status = @$theblock->fetch_dashboard_data($theblock->filteredsql, $results, @$theblock->config->pagesize, $offset);
        } catch (Exception $e) {
            // Showing query.
            $template->errormsg = '<div class="dashboard-query-box">';
            $template->errormsg .= '<pre>'.$theblock->filteredsql.'</pre>';
            $template->errormsg .= $DB->get_last_error();
            $template->errormsg .= '</div>';
            $template->errormsg .= $OUTPUT->notification(get_string('invalidorobsoletequery', 'block_dashboard'));
            return $this->render_from_template('block_dashboard/dashboard', $template);
        }

        // Rotate results if required ---------------------------.

        /*
         * rotation has a strong effect on data structure, transforming flat recors
         * into a data matrix that may be used to feed multiple graph series.
         */
        if (block_dashboard_supports_feature('result/rotate')) {
            if (!empty($theblock->config->queryrotatecols) &&
                    !empty($theblock->config->queryrotatenewkeys) &&
                            !empty($theblock->config->queryrotatepivot)) {

                $results = dashboard_rotate_result($theblock, $results);
            }
        }

        if (!$results) {
            // No data, but render filters anyway.
            $template->filterandparams = $this->filters_and_params_form($theblock, $sort);
            $template->data = $OUTPUT->notification(get_string('nodata', 'block_dashboard'));

            // Showing query.
            if (@$theblock->config->showquery) {
                $template->query = $theblock->filteredsql;
            }

            return $this->render_from_template('block_dashboard/dashboard', $template);
        }

        // process results -----------------------------------.

        $filterquerystringadd = (isset($filterquerystring)) ? "&amp;$filterquerystring" : '';

        if (@$theblock->config->inblocklayout) {
            $baseurl = $CFG->wwwroot.'/course/view.php?id='.$COURSE->id.$coursepage.$filterquerystringadd;
        } else {
            $baseurl = $CFG->wwwroot.'/blocks/dashboard/view.php?id='.$COURSE->id.'&amp;blockid='.$theblock->instance->id.$coursepage.$filterquerystringadd;
        }

        // Start prepare output table.

        $table = new html_table();
        $table->id = 'mod-dashboard'.$theblock->instance->id;
        $table->class = 'dashboard';
        $table->width = '100%';
        $table->head = array();

        $numcols = count($theblock->output);

        foreach ($theblock->output as $field => $label) {
            if (!empty($theblock->config->sortable)) {
                $label .= $this->sort_controls($theblock, $field, $sort);
            }
            $table->head[$field] = $label;
            $table->size[$field] = (100 / $numcols).'%';
        }

        foreach ($theblock->output as $field => $label) {
            $table->colclasses[$field] = "$field";
        }

        if (!empty($theblock->config->pagesize)) {
            $table->pagesize = min($theblock->config->pagesize, $countres); // No paginating at start.
        }

        $graphseries = array();
        $treedata = array();
        $treekeys = array();
        $lastvalue = array();
        $hcols = array();
        $splitnumsonsort = @$theblock->config->splitsumsonsort;

        foreach ($results as $result) {

            // Prepare for subsums.
            if (!empty($splitnumsonsort)) {
                $orderkeyed = strtoupper($result->$splitnumsonsort);
                if (!isset($oldorderkeyed)) {
                    $oldorderkeyed = $orderkeyed; // First time.
                }
            }

            // Pre-aggregates sums.
            if (!empty($theblock->config->shownumsums)) {
                foreach (array_keys($theblock->numsumsf) as $numsum) {
                    if (empty($numsum)) {
                        continue;
                    }
                    if (!isset($result->$numsum)) {
                        continue;
                    }

                    /*
                     * make subaggregates (only for linear tables and when sorting criteria is the split column)
                     * post aggregate after table output
                     */
                    if (!isset($theblock->aggr)) {
                        $theblock->aggr = new StdClass;
                    }

                    $theblock->aggr->$numsum = 0 + (float)@$theblock->aggr->$numsum + (float)$result->$numsum;

                    if (!empty($splitnumsonsort) && @$theblock->config->tabletype == 'linear' &&
                            (preg_match("/\\b$splitnumsonsort\\b/", $sort))) {
                        $theblock->subaggr[$orderkeyed]->$numsum = 0 + (float)@$theblock->subaggr[$orderkeyed]->$numsum + (float)$result->$numsum;
                    }
                }
            }

            if (!empty($splitnumsonsort) &&
                (@$theblock->config->tabletype == 'linear') &&
                        (preg_match("/\\b$splitnumsonsort\\b/", $sort))) {
                if ($orderkeyed != $oldorderkeyed) {
                    // When range changes.
                    $k = 0;
                    $tabledata = null;
                    foreach (array_keys($theblock->output) as $field) {
                        if (in_array($field, array_keys($theblock->numsumsf))) {
                            if (is_null($tabledata)) {
                                $tabledata = array();
                                for ($j = 0; $j < $k; $j++) {
                                    $tabledata[$j] = '';
                                }
                            }
                            $tabledata[$k] = '<b>Tot: '.@$theblock->subaggr[$oldorderkeyed]->$field.'</b>';
                        }
                        $k++;
                    }
                    if (!is_null($tabledata)) {
                        $table->data[] = $tabledata;
                    }
                    $oldorderkeyed = $orderkeyed;
                }
            }

            // Print data in results.
            if (!empty($theblock->config->showdata)) {
                /*
                 * this is the most common case of a linear table
                 */
                if (empty($theblock->config->tabletype) ||
                        ($theblock->config->tabletype == 'linear')) {
                    $theblock->prepare_linear_table($table, $result, $lastvalue);
                } else if ($theblock->config->tabletype == 'tabular') {

                    // This is a tabular table.

                    /* in a tabular table, data can be placed :
                     * - in first columns in order of vertical keys
                     * the results are grabbed sequentially and distributed into the matrix columns
                     */

                    $keystack = array();
                    $matrix = array();
                    foreach (array_keys($theblock->vertkeys->formats) as $vkey) {
                        if (empty($vkey)) {
                            continue;
                        }
                        $vkeyvalue = $result->$vkey;
                        $matrix[] = "['".addslashes($vkeyvalue)."']";
                    }
                    $hkey = $theblock->config->horizkey;
                    $hkeyvalue = (!empty($hkey)) ? $result->$hkey : '';
                    $matrix[] = "['".addslashes($hkeyvalue)."']";
                    $matrixst = "\$m".implode($matrix);
                    if (!in_array($hkeyvalue, $hcols)) {
                        $hcols[] = $hkeyvalue;
                    }

                    // Now put the cell value in it.
                    $outvalues = array();
                    foreach (array_keys($theblock->output) as $field) {

                        // Did we ask for cumulative results ?
                        $cumulativeix = null;
                        if (preg_match('/S\((.+?)\)/', $field, $matches)) {
                            $field = $matches[1];
                            $cumulativeix = $theblock->instance->id.'_'.$field;
                        }

                        // Try to defer this formating as post formatting in cross table print.
                        if (!empty($theblock->outputf[$field])) {
                            $datum = dashboard_format_data($theblock->outputf[$field], $result->$field, $cumulativeix, $result);
                        } else {
                            $datum = dashboard_format_data(null, @$result->$field, $cumulativeix, $result);
                        }
                        if (!empty($theblock->config->colorfield) &&
                                ($theblock->config->colorfield == $field)) {
                            $datum = dashboard_colour_code($theblock, $datum, $theblock->colourcoding);
                        }
                        $outvalues[] = str_replace("\"", "\\\"", $datum);
                    }
                    $matrixst .= ' = "'.implode(' ',$outvalues).'";';
                    // Make the matrix in memory.
                    eval($matrixst.";");
                } else {

                    $debug = optional_param('debug', false, PARAM_BOOL);
                    $template->debug = '';

                    // Treeview.
                    $resultarr = array_values((array)$result);
                    $resultid = $resultarr[0];
                    if (!empty($parentserie)) {
                        if (!empty($result->$parentserie)) {
                            // Non root node, attache to his parent if we found it.
                            if (array_key_exists($result->$parentserie, $treekeys)) {
                                if (!empty($debug)) {
                                    $template->debug .= 'binding to '. $result->$parentserie.'. ';
                                }
                                $treekeys[$result->$parentserie]->childs[$resultid] = $result;
                                if (!array_key_exists($resultid, $treekeys)) {
                                    $treekeys[$resultid] = $result;
                                }
                            } else {
                                // In case nodes do not come in correct order, do not connect but register only.
                                if (!empty($debug)) {
                                    $template->debug .= 'waiting for '. $result->$parentserie.'. ';
                                }
                                $waitingnodes[$resultid] = $result;
                                if (!array_key_exists($resultid, $treekeys)) {
                                    $treekeys[$resultid] = $result;
                                }
                            }
                        } else {
                            // Root node.
                            if (!empty($debug)) {
                                $template->debug .= 'root as '. $resultid.'. ';
                            }
                            if (!array_key_exists($resultid, $treekeys)) {
                                $treekeys[$resultid] = $result;
                            }
                            $treedata[$resultid] = &$treekeys[$resultid];
                        }
                    } else {
                        if (!array_key_exists($resultid, $treekeys)) {
                            $treekeys[$resultid] = $result;
                        }
                    }
                }
            }

            // Prepare data for graphs.
            if (!empty($theblock->config->showgraph)) {
                if (!empty($theblock->config->xaxisfield) &&
                        $theblock->config->graphtype != 'googlemap' &&
                                $theblock->config->graphtype != 'timeline') {
                    $xaxisfield = $theblock->config->xaxisfield;
                    if ($theblock->config->graphtype != 'pie') {
                        // Linear, bars.
                        // TODO : check if $theblock->config->xaxisfield exists really (misconfiguration).
                        $ticks[] = addslashes($result->$xaxisfield);
                        $ys = 0;

                        foreach (array_keys($theblock->yseries) as $yserie) {

                            // Did we ask for cumulative results ?
                            $cumulativeix = null;
                            if (preg_match('/S\((.+?)\)/', $yserie, $matches)) {
                                $yserie = $matches[1];
                                $cumulativeix = $theblock->instance->id.'_'.$yserie;
                            }

                            if (!isset($result->$yserie)) {
                                continue;
                            }

                            if ($theblock->config->graphtype != 'timegraph') {
                                if (!empty($theblock->yseriesf[$yserie])) {
                                    $graphseries[$yserie][] = dashboard_format_data($theblock->yseriesf[$yserie], $result->$yserie, $cumulativeix, $result);
                                } else {
                                    $graphseries[$yserie][] = dashboard_format_data(null, $result->$yserie, $cumulativeix, $result);
                                }
                            } else {
                                if (!empty($theblock->yseriesf[$yserie])) {
                                    $timeelm = array($result->$xaxisfield, dashboard_format_data($theblock->yseriesf[$yserie], $result->$yserie, $cumulativeix, $result)); 
                                    $graphseries[$ys][] = $timeelm;
                                } else {
                                    $timeelm = array($result->$xaxisfield, dashboard_format_data(null, $result->$yserie, $cumulativeix, $result));
                                    $graphseries[$ys][] = $timeelm;
                                }
                            }
                            $ys++;
                        }
                    } else if ($theblock->config->graphtype == 'pie') {
                        foreach (array_keys($theblock->yseriesf) as $yserie) {
                            if (empty($result->$xaxisfield)) {
                                $result->$xaxisfield = 'N.C.';
                            }
                            if (!empty($theblock->yseriesf[$field])) {
                                $graphseries[$yserie][] = array($result->$xaxisfield, dashboard_format_data($theblock->yseriesf[$field], $result->$yserie, false, $result));
                            } else {
                                $graphseries[$yserie][] = array($result->$xaxisfield, $result->$yserie);
                            }
                        }
                    }
                } else {
                    $data[] = $result;
                }
            }
        }

        $graphdata = array_values($graphseries);

        // Post aggregating last subtotal ----------------------------------------.

        if (!empty($theblock->config->shownumsums) && $results) {
            if (!empty($splitnumsonsort) &&
                    (@$theblock->config->tabletype == 'linear') &&
                            (preg_match("/\\b$splitnumsonsort\\b/", $sort))) {
                $k = 0;
                $tabledata = null;
                foreach (array_keys($theblock->output) as $field) {
                    if (in_array($field, array_keys($theblock->numsumsf))) {
                        if (is_null($tabledata)) {
                            $tabledata = array();
                            for ($j = 0; $j < $k; $j++) {
                                $tabledata[$j] = '';
                            }
                        }
                        $tabledata[$k] = '<b>Tot: '.@$theblock->subaggr[$orderkeyed]->$field.'</b>';
                    }
                    $k++;
                }
                $oldorderkeyed = $orderkeyed;
                if (!is_null($tabledata)) {
                    $table->data[] = $tabledata;
                }
            }
        }

        // Starting outputing data -------------------------------------------------.

        // If treeview, need to post process waiting nodes.
        if (@$theblock->config->tabletype == 'treeview') {
            if (!empty($waitingnodes)) {
                foreach ($waitingnodes as $wnid => $wn) {
                    if (array_key_exists($wn->$parentserie, $treekeys)) {
                        if (!empty($debug)) {
                            $template->debug .= ' postbinding to '. $wn->$parentserie.'. ';
                        }
                        $treekeys[$wn->$parentserie]->childs[$wnid] = $wn;
                        unset($waitingnodes[$wnid]); // Free some stuff.
                    }
                }
            }
        }

        if (@$theblock->config->inblocklayout) {
            $params = array('id' => $COURSE->id.$coursepage, 'tsort'.$theblock->instance->id => $sort);
            $url = new moodle_url('/course/view.php', $params);
        } else {
            $params = array('id' => $COURSE->id,
                            'blockid' => $theblock->instance->id.$coursepage,
                            'tsort'.$theblock->instance->id => $sort);
            $url = new moodle_url('/blocks/dashboard/view.php', $params);
        }

        $template->filterandparams = $this->filters_and_params_form($theblock, $sort);

        if ($theblock->config->showdata) {
            $filterquerystring = (!empty($filterquerystring)) ? '&'.$filterquerystring : '';
            if (empty($theblock->config->tabletype) ||
                    @$theblock->config->tabletype == 'linear') {

                $template->data = html_writer::table($table);
                $template->controlbuttons = $this->export_buttons($theblock, $filterquerystring);

            } else if (@$theblock->config->tabletype == 'tabular') {
                // Forget table and use $m matrix for making display.
                $template->data = $this->cross_table($theblock, $m, $hcols, $theblock->config->horizkey, $theblock->vertkeys, $theblock->config->horizlabel, true);
                $template->controlbuttons = $this->tabular_buttons($theblock, $filterquerystring);
            } else {
                $template->data = $this->tree_view($theblock, $treedata, $theblock->treeoutput, $theblock->output, $theblock->outputf, $theblock->colourcoding, true);
                $template->controlbuttons = $this->tree_buttons($theblock, $filterquerystring);
            }
        }

        // Showing graph.
<<<<<<< HEAD
        if ($theblock->config->showgraph && !empty($theblock->config->graphtype)) {
=======
        if (!empty($theblock->config->showgraph) && !empty($theblock->config->graphtype)) {
>>>>>>> MOODLE_34_STABLE
            $graphdesc = $theblock->dashboard_graph_properties();

            if ($theblock->config->graphtype != 'googlemap' && $theblock->config->graphtype != 'timeline') {
                $data = $graphdata;
                $template->graph = local_vflibs_jqplot_print_graph('dashboard'.$theblock->instance->id, $graphdesc, $data,
                                                         $theblock->config->graphwidth, $theblock->config->graphheight,
                                                         '', true, $ticks);
            } else if ($theblock->config->graphtype == 'googlemap') {
                $template->graph = $this->googlemaps_data($theblock, $data, $graphdesc);
            } else {

                // Timeline graph.
                if (empty($theblock->config->timelineeventstart) || empty($theblock->config->timelineeventend)) {
                    $template->graph = $OUTPUT->notification("Missing mappings (start or titles)", 'notifyproblem');
                } else {
                    $template->graph = timeline_print_graph($theblock, 'dashboard'.$theblock->instance->id, $theblock->config->graphwidth,
                                                  $theblock->config->graphheight, $data, true);
                }
            }
        }

        // Showing bottom summators.
        if ($theblock->config->numsums) {
            $template->summators = $this->numsums($theblock, $theblock->aggr);
        }

        // Showing query.
        if (@$theblock->config->showquery) {
            $template->query = $theblock->filteredsql;
        }

        // Showing SQL benches.
        if (@$theblock->config->showbenches) {
            $rows = array();
            foreach ($theblock->benches as $bench) {
                $row = new StdClass;
                $row->name = $bench->name;
                $row->value = $bench->end - $bench->start;
                $rows[] = $row;
            }
            $template->benchrows = $rows;
        }

        return $this->render_from_template('block_dashboard/dashboard', $template);
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
            $str .= " This is a demo set !! ";
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

        $str .= googlemaps_embed_graph('dashboard'.$theblock->instance->id, @$theblock->config->lat, @$theblock->config->lng, @$theblock->config->graphwidth, $theblock->config->graphheight, $graphdesc, $gmdata, true);

        return $str;
    }

    /**
     * print forms for filters and user defined live parameters
     * @param objectref &$theblock a dashboard block instance
     * @param string $sort name of the actual sorting column
     */
    public function filters_and_params_form(&$theblock, $sort) {
        global $COURSE, $CFG;

        $text = '';

        if (empty($theblock->config->filters) && empty($theblock->params)) {
            return;
        }

        $template = new StdClass;

        $template->blockid = $theblock->instance->id;
        $template->courseid = $COURSE->id;

        $template->inblocklayout = @$theblock->config->inblocklayout;
        $template->blockidparam = optional_param('blockid', 0, PARAM_INT);
<<<<<<< HEAD

        if ($COURSE->format == 'page') {
            require_once($CFG->dirroot.'/course/format/page/classes/page.class.php');
            $pageid = optional_param('page', false, PARAM_INT);
            $template->ispageformatpage = !empty($pageid);
            if ($page = course_page::get_current_page($COURSE->id)) {
                $template->pageid = $page->id;
            }
        }

        if ($sort == 'id DESC') {
            $sort = '';
        }
        $template->sort = $sort;

        $template->strdofilter = get_string('dofilter', 'block_dashboard');

        $template->autosubmit = (count(array_keys($theblock->filters)) + count(array_keys($theblock->params))) <= 1;

        if (!empty($theblock->config->filters)) {
            $template->filters = $this->filters($theblock);
        }
        if (!empty($theblock->params)) {
            $template->params = $this->params($theblock);
        }

=======

        if ($COURSE->format == 'page') {
            require_once($CFG->dirroot.'/course/format/page/classes/page.class.php');
            $pageid = optional_param('page', false, PARAM_INT);
            $template->ispageformatpage = !empty($pageid);
            if ($page = course_page::get_current_page($COURSE->id)) {
                $template->pageid = $page->id;
            }
        }

        if ($sort == 'id DESC') {
            $sort = '';
        }
        $template->sort = $sort;

        $template->strdofilter = get_string('dofilter', 'block_dashboard');

        $template->autosubmit = (count(array_keys($theblock->filters)) + count(array_keys($theblock->params))) <= 1;

        if (!empty($theblock->config->filters)) {
            // Fill template with filters.
            $this->filters($theblock, $template);
            if (!empty($template->hasmultiple)) {
                $template->autosubmit = false;
            }
        }
        if (!empty($theblock->params)) {
            $template->params = $this->params($theblock);
        }

>>>>>>> MOODLE_34_STABLE
        return $this->render_from_template('block_dashboard/filterandparamsform', $template);
    }

    /**
     * get value range, print and sets up data filters
     * @param objectref $theblock instance of a dashboard block
     * @param string $javascripthandler if empty, no onchange handler is required. Filter change
     * is triggered by an explicit button.
     *
     * Javascript handler is provided when preparing form overrounding.
     */
<<<<<<< HEAD
    public function filters(&$theblock) {

        $str = '';
=======
    public function filters(&$theblock, &$template) {
>>>>>>> MOODLE_34_STABLE

        $alllabels = array_keys($theblock->filterfields->labels);

        foreach ($alllabels as $afield) {

            if (empty($afield)) {
                // Protects against empty filterset.
                continue;
            }

            $filtertpl = new StdClass;

            $cond = isset($theblock->filterfields->translations[$afield]);
            $filtertpl->fieldname = ($cond) ? $theblock->filterfields->translations[$afield] : $afield;

            $filterresults = $theblock->filter_get_results($afield, $filtertpl->fieldname, false, false, $str);

            if ($filterresults) {
                $filteropts = array();
                if (!$theblock->is_filter_single($afield)) {
                    $filteropts['*'] = '*';
                }

                foreach (array_values($filterresults) as $value) {
                    // Removes table scope explicitators.
<<<<<<< HEAD
                    $radical = preg_replace('/^.*\./', '', $fieldname);
=======
                    $radical = preg_replace('/^.*\./', '', $filtertpl->fieldname);
>>>>>>> MOODLE_34_STABLE
                    $filteropts[$value->$radical] = $value->$radical;
                }
                $filtertpl->multiple = (strstr($theblock->filterfields->options[$afield], 'm') === false) ? false : true;
                $arrayform = ($filtertpl->multiple) ? '[]' : '';

                if (!is_array(@$theblock->filtervalues[$radical])) {
                    $unslashedvalue = stripslashes(@$theblock->filtervalues[$radical]);
                } else {
                    $unslashedvalue = $theblock->filtervalues[$radical];
                }

                // Build the select options.
                $attrs = array();

<<<<<<< HEAD
                if ($multiple) {
                    $attrs['multiple'] = 1;
                    $attrs['size'] = 5;
=======
                if ($filtertpl->multiple) {
                    $template->hasmultiple = true;
                    $filtertpl->multiple = true;
                    $attrs['multiple'] = 1;
                    $attrs['size'] = 8;
>>>>>>> MOODLE_34_STABLE
                }

                if ($theblock->is_filter_global($afield)) {
                    $key = "filter0_{$radical}{$arrayform}";
                    $attrs['class'] = 'dashboard-filter-element-'.$theblock->instance->id;
<<<<<<< HEAD
                    $str .= html_writer::select($filteropts, $key, $unslashedvalue, null, $attrs);
                } else {
                    $key = "filter{$theblock->instance->id}_{$radical}{$arrayform}";
                    $attrs['class'] = 'dashboard-filter-element-'.$theblock->instance->id;
                    $str .= html_writer::select($filteropts, $key, $unslashedvalue, null, $attrs);
=======
                    $filtertpl->filterselect = html_writer::select($filteropts, $key, $unslashedvalue, null, $attrs);
                } else {
                    $key = "filter{$theblock->instance->id}_{$radical}{$arrayform}";
                    $attrs['class'] = 'dashboard-filter-element-'.$theblock->instance->id;
                    $filtertpl->filterselect = html_writer::select($filteropts, $key, $unslashedvalue, null, $attrs);
>>>>>>> MOODLE_34_STABLE
                }
            }
            $template->filters[] = $filtertpl;
        }
    }

    /**
     * if there are some user params, print widgets for them. If one of them is a daterange, 
     * then cancel the javascripthandler as we will need to explictely submit.
     *
     * @param objectref $theblock a dashboard block instance
     * @param string $javascripthandler if empty, no onchange handler is required. Filter change
     * is triggered by an explicit button.
     */
    public function params(&$theblock) {

        $template = new Stdclass;
        $template->strfrom = get_string('from', 'block_dashboard');
        $template->strto = get_string('to', 'block_dashboard');

        foreach ($theblock->params as $key => $param) {

            $param->paramkey = preg_replace('/[.() *]/', '', $key).'_'.$theblock->instance->id;

            switch ($param->type) {

                case 'choice':
                    $param->choice = true;
                    $values = explode("\n", $param->values);
                    $param->value0checked = ($param->value == $values[0]) ? 'checked="checked"' : '';
                    $param->value1checked = ($param->value == $values[1]) ? 'checked="checked"' : '';
                    $param->quotedvalue0 = htmlentities($values[0], ENT_QUOTES, 'UTF-8');
                    $param->value0 = $values[0];
                    $param->quotedvalue1 = htmlentities($values[1], ENT_QUOTES, 'UTF-8');
                    $param->value1 = $values[1];
                    break;

                case 'text':
                    $param->text = true;
                    $param->quotedvalue = htmlentities($param->value, ENT_QUOTES, 'UTF-8');
                    break;

                case 'list':
                    $param->list = true;
                    $param->options = array();
                    foreach ($param->values as $v) {
                        $option = new Stdclass;
                        $option->selected = ($v == $param->value) ? ' selected="selected" ' : '';
                        $option->value = htmlentities($v, ENT_QUOTES, 'UTF-8');
                        $option->label = $v;
                        $param->options[] = $options;
                    }
                    break;

                case 'range':
                    $param->range = true;
                    $param->quotedvaluefrom = htmlentities($param->valuefrom, ENT_QUOTES, 'UTF-8');
                    $param->quotedvalueto = htmlentities($param->valueto, ENT_QUOTES, 'UTF-8');
                    break;

                case 'date':
                    $param->date = true;
                    break;

                case 'daterange':
                    $param->daterange = true;
                    break;
            }

            $template->params[] = $param;
        }

        return $this->render_from_template('block_dashboard/param', $template);
    }

    /**
     * if there are some user params, print widgets for them. If one of them is a daterange, 
     * then cancel the javascripthandler as we will need to explictely submit.
     *
     * @param objectref $theblock a dashboard block instance
     * @param string $javascripthandler if empty, no onchange handler is required. Filter change
     * is triggered by an explicit button.
     */
<<<<<<< HEAD
    public function params(&$theblock) {

        $template = new Stdclass;
        $template->strfrom = get_string('from', 'block_dashboard');
        $template->strto = get_string('to', 'block_dashboard');

        foreach ($theblock->params as $key => $param) {

            $param->paramkey = preg_replace('/[.() *]/', '', $key).'_'.$theblock->instance->id;

            switch ($param->type) {

                case 'choice':
                    $param->choice = true;
                    $values = explode("\n", $param->values);
                    $param->value0checked = ($param->value == $values[0]) ? 'checked="checked"' : '';
                    $param->value1checked = ($param->value == $values[1]) ? 'checked="checked"' : '';
                    $param->quotedvalue0 = htmlentities($values[0], ENT_QUOTES, 'UTF-8');
                    $param->value0 = $values[0];
                    $param->quotedvalue1 = htmlentities($values[1], ENT_QUOTES, 'UTF-8');
                    $param->value1 = $values[1];
                    break;

                case 'text':
                    $param->text = true;
                    $param->quotedvalue = htmlentities($param->value, ENT_QUOTES, 'UTF-8');
                    break;

                case 'list':
                    $param->list = true;
                    $param->options = array();
                    foreach ($param->values as $v) {
                        $option = new Stdclass;
                        $option->selected = ($v == $param->value) ? ' selected="selected" ' : '';
                        $option->value = htmlentities($v, ENT_QUOTES, 'UTF-8');
                        $option->label = $v;
                        $param->options[] = $options;
                    }
                    break;

                case 'range':
                    $param->range = true;
                    $param->quotedvaluefrom = htmlentities($param->valuefrom, ENT_QUOTES, 'UTF-8');
                    $param->quotedvalueto = htmlentities($param->valueto, ENT_QUOTES, 'UTF-8');
                    break;

                case 'date':
                    $param->date = true;
                    break;

                case 'daterange':
                    $param->daterange = true;
                    break;
            }

            $template->params[] = $param;
        }

        return $this->render_from_template('block_dashboard/param', $template);
    }

    /**
     * if there are some user params, print widgets for them. If one of them is a daterange, 
     * then cancel the javascripthandler as we will need to explictely submit.
     *
     * @param objectref $theblock a dashboard block instance
     * @param string $javascripthandler if empty, no onchange handler is required. Filter change
     * is triggered by an explicit button.
     */
=======
>>>>>>> MOODLE_34_STABLE
    public function old_params(&$theblock) {

        $str = '';

        $str .= '<div class="dashboard-sql-params">';
        foreach ($theblock->params as $key => $param) {
            $htmlkey = preg_replace('/[.() *]/', '', $key).'_'.$theblock->instance->id;
            switch ($param->type) {

                case 'choice':
                    $values = explode("\n", $param->values);
                    $option1checked = ($param->value == $values[0]) ? 'checked="checked"' : '';
                    $option2checked = ($param->value == $values[1]) ? 'checked="checked"' : '';
                    $str .= ' '.$param->label.': <input type="radio" name="'.$htmlkey.'" value="'.htmlentities($values[0], ENT_QUOTES, 'UTF-8').'" '.$option1checked.' /> '.$values[0];
                    $str .= ' - <input type="radio" name="'.$key.'" value="'.htmlentities($values[1], ENT_QUOTES, 'UTF-8').'" '.$option2checked.' "/> '.$values[1].' &nbsp;&nbsp;';
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
                    $str .= ' '.$param->label.': <input type="text" size="10"  id="date-'.$htmlkey.'" name="'.$htmlkey.'" value="'.$param->originalvalue.'" />';
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
            $pix = $this->output->pix_icon('sinactive', '', 'block_dashboard');
            $str .= '&nbsp;<a href="'.$baseurl.'&tsort'.$theblock->instance->id.'='.$fieldname.' ASC">'.$pix.'</a>';
        } else {
            if ($dir == 'DESC') {
                $pix = $this->output->pix_icon('sdesc', '', 'block_dashboard');
                $str .= '&nbsp;<a href="'.$baseurl.'&tsort'.$theblock->instance->id.'='.$fieldname.' ASC">'.$pix.'</a>';
            } else {
                $pix = $this->output->pix_icon('sasc', '', 'block_dashboard');
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