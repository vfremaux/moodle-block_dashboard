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
 * @author Valery Fremaux (valery@club-internet.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 */
defined('MOODLE_INTERNAL') || die();

define('DASHBOARD_MAX_QUERY_PARAMS', 10);

require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
require_once($CFG->dirroot.'/blocks/dashboard/lib.php');
require_once($CFG->dirroot.'/blocks/dashboard/classes/filter_query_exception.class.php');
require_once($CFG->dirroot.'/blocks/dashboard/classes/filter_query_cache_exception.class.php');
require_once($CFG->dirroot.'/blocks/dashboard/lib.php');
require_once($CFG->dirroot.'/blocks/dashboard/extradblib.php');
require_once($CFG->dirroot.'/local/vflibs/jqplotlib.php');
if (block_dashboard_supports_feature('result/rotate')) {
    include_once($CFG->dirroot.'/blocks/dashboard/pro/lib.php');
}
require_once($CFG->dirroot.'/blocks/dashboard/lib.php');

class block_dashboard extends block_base {

    protected $devmode = true; // Use local moodle database to develop virual tools.

    public $filtervalues; // Collects effective filter values set by user.

    public $paramvalues; // Collects effective param values set by user.

    public $filters; // Stores filter definitions.

    public $filteredsql; // Stores filter filtered sql.

    public $params; // Stores user parameter definitions.

    public $output; // Stores output definition from query.

    public $outputf; // Stores output formats specifiers from query.

    protected $benches; // Stores SQL bench info.

    public function init() {
        $this->title = get_string('blockname', 'block_dashboard');
        $this->version = 2013032600;

        $this->filtervalues = array();
        $this->paramvalues = array();
        $this->params = array();
        $this->filters = array();
        $this->output = array();
        $this->outputf = array();
        $this->filteredsql = '';
    }

    public function specialization() {
        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        }
    }

    public function hide_header() {
        if (!isset($this->config->hidetitle)) {
            return false;
        }
        return $this->config->hidetitle;
    }

    public function has_config() {
        return true;
    }

    public function instance_allow_multiple() {
        return true;
    }

    public function instance_allow_config() {
        return true;
    }

    public function instance_config_save($data, $nolongerused = false) {
        global $USER;

        /*
         * Check if current user forcing a filelocationadminoverride can really do it.
         * In case it seems to be forced, set it to empty anyway.
         */
        if (!has_capability('block/dashboard:systempathaccess', context_system::instance())) {
            $data->filepathadminoverride = '';
        }

        // Retrieve sql params directly from POST.
        $data->sqlparams = @$_POST['sqlparams'];

        // Reset cron activation internal switches.
        $data->isrunning = 0;
        $data->lastcron = 0;

        $config = clone($data);
        // Move embedded files into a proper filearea and adjust HTML links to match
        $config->description = file_save_draft_area_files(@$data->description['itemid'], $this->context->id, 'block_dashboard', 'description',
                                                   0, array('subdirs'=>true), @$data->description['text']);
        $config->descriptionformat = @$data->description['format'];
        return parent::instance_config_save($config, $nolongerused);
    }

    public function applicable_formats() {
        // Default case: the block can be used in all course types.
        return array('all' => true, 'site' => true);
    }

    public function get_content() {
        global $extradbcnx, $COURSE, $PAGE, $CFG;

        $this->get_required_javascript();

        $context = context_block::instance($this->instance->id);
        $renderer = $PAGE->get_renderer('block_dashboard');

        @raise_memory_limit('512M');

        if (!empty($this->config->query)) {
            preg_replace('/block_instance\b/', 'block_instances', $this->config->query);
            preg_replace('/\{(.*?)\}/', $CFG->prefix."\\1", $this->config->query);
        }

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new StdClass;

        if (@$this->config->inblocklayout) {
            $this->content->text = $renderer->render_dashboard($this);
        } else {
            $viewdashboardstr = get_string('viewdashboard', 'block_dashboard');
            $params = array('id' => $COURSE->id, 'blockid' => $this->instance->id);
            $dashboardviewurl = new moodle_url('/blocks/dashboard/view.php', $params);
            $this->content->text = '<a href="'.$dashboardviewurl.'">'.$viewdashboardstr.'</a>';
        }

        if (has_capability('block/dashboard:configure', $context) && $PAGE->user_is_editing()) {
            $params = array();
            $params['id'] = $COURSE->id;
            $params['instance'] = $this->instance->id;
            $options['href'] = new moodle_url('/blocks/dashboard/setup.php', $params);
            $options['class'] = 'smalltext';
            $editlink = html_writer::tag('a', get_string('configure', 'block_dashboard'), $options);
            $this->content->footer = $editlink;
        } else {
            $this->content->footer = '';
        }

        return $this->content;
    }

    function content_is_trusted() {
        global $SCRIPT;

        if (!$context = context::instance_by_id($this->instance->parentcontextid, IGNORE_MISSING)) {
            return false;
        }
        //find out if this block is on the profile page
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // this is exception - page is completely private, nobody else may see content there
                // that is why we allow JS here
                return true;
            } else {
                // no JS on public personal pages, it would be a big security issue
                return false;
            }
        }

        return true;
    }


    /**
     * build a graph descriptor, taking some defaults decisions
     *
     */
    public function dashboard_graph_properties() {

        $jqplot = array();

        $yserieslabels = array_values($this->yseries);

        $labelarray = array();
        foreach ($yserieslabels as $label) {
            $labelarray[] = array('label' => $label);
        }

        if ($this->config->graphtype == 'line') {

            $jqplot = array(
                'axesDefaults' => array(
                    'labelRenderer' => '$.jqplot.CanvasAxisLabelRenderer'
                ),
                'axes' => array(
                    'xaxis' => array(
                        'label' => @$this->config->xaxislabel,
                        'renderer' => '$.jqplot.CategoryAxisRenderer',
                        'tickRenderer' => '$.jqplot.CanvasAxisTickRenderer',
                         'tickOptions' => array(
                             'angle' => @$this->config->yaxistickangle
                         )
                     ),
                    'yaxis' => array(
                        'autoscale' => true,
                        'pad' => 0,
                        'tickOptions' => array('formatString' => '%2d'),
                        'label' => @$this->config->yaxislabel,
                        'labelRenderer' => '$.jqplot.CanvasAxisLabelRenderer',
                        'labelOptions' => array('angle' => 90)
                        )
                    ),
                );
            if (@$this->config->yaxisscale == 'log') {
                $jqplot['axes']['yaxis']['renderer'] = '$.jqplot.LogAxisRenderer';
                $jqplot['axes']['yaxis']['rendererOptions'] = array('base' => 10, 'tickDistribution' => 'even');
            }

        } else if ($this->config->graphtype == 'bar') {
            $jqplot = array(

                 'seriesDefaults' => array(
                     'renderer' => '$.jqplot.BarRenderer',
                     'rendererOptions' => array(
                         'fillToZero' => true
                         ),
                     ),
                 'series' => $labelarray,
                 'axes' => array(
                     'xaxis' => array(
                         'tickRenderer' => '$.jqplot.CanvasAxisTickRenderer',
                         'tickOptions' => array(
                             'angle' => @$this->config->yaxistickangle
                         ),
                         'renderer' => '$.jqplot.CategoryAxisRenderer',
                        'label' => @$this->config->xaxislabel,
                         'ticks' => '$$.ticks',
                     ),
                     'yaxis' => array(
                         'autoscale' => true,
                         'padMax' => 5,
                        'label' => @$this->config->yaxislabel,
                        'rendererOptions' => array('forceTickAt0' => true),
                         'tickOptions' => array('formatString' => '%2d'),
                        'labelRenderer' => '$.jqplot.CanvasAxisLabelRenderer',
                        'labelOptions' => array('angle' => 90),
                     ),
                 ),
            );
            if (@$this->config->yaxisscale == 'log') {
                $jqplot['axes']['yaxis']['renderer'] = '$.jqplot.LogAxisRenderer';
            }

        } else if ($this->config->graphtype == 'pie') {
            $jqplot = array(
                'seriesDefaults' => array(
                    'renderer' => '$.jqplot.PieRenderer',
                    'rendererOptions' => array(
                        'padding' => 2,
                        'sliceMargin' => 2,
                        'showDataLabels' => true
                    ),
                ),
                'cursor' => array(
                    'useAxesFormatters' => false,
                    'show' => false,
                ),
                'highlighter' => array(
                    'useAxesFormatters' => false,
                ),
            );
        } else if ($this->config->graphtype == 'donut') {
            $jqplot = array(
                'seriesDefaults' => array(
                    'renderer' => '$.jqplot.DonutRenderer',
                    'rendererOptions' => array(
                        'showDataLabels' => true
                    ),
                ),
                'cursor' => array(
                    'useAxesFormatters' => false,
                    'show' => false,
                ),
                'highlighter' => array(
                    'useAxesFormatters' => false,
                ),
            );
        } else if ($this->config->graphtype == 'timegraph') {
            $jqplot = array(
                'axesDefaults' => array(
                    'labelRenderer' => '$.jqplot.CanvasAxisLabelRenderer'
                ),
                'axes' => array(
                    'xaxis' => array(
                        'label' => @$this->config->xaxislabel,
                        'renderer' => '$.jqplot.DateAxisRenderer',
                         'tickRenderer' => '$.jqplot.CanvasAxisTickRenderer',
                         'tickOptions' => array(
                             'angle' => @$this->config->yaxistickangle
                         ),
                    ),
                    'yaxis' => array(
                        'autoscale' => true,
                        'pad' => 0,
                        'tickOptions' => array('formatString' => '%2d'),
                        'label' => @$this->config->yaxislabel,
                        'labelRenderer' => '$.jqplot.CanvasAxisLabelRenderer',
                        'labelOptions' => array('angle' => 90)
                        )
                    ),
                );
            if (@$this->config->yaxisscale == 'log') {
                $jqplot['axes']['yaxis']['renderer'] = '$.jqplot.LogAxisRenderer';
                $jqplot['axes']['yaxis']['rendererOptions'] = array('base' => 10, 'tickDistribution' => 'even');
            }
        } else if ($this->config->graphtype == 'googlemap') {
            if (empty($this->config->maptype)) {
                $this->config->maptype = 'ROADMAP';
            }
            if (empty($this->config->zoom)) {
                $this->config->zoom = 6;
            }
            $jqplot = array(
                'zoom' => $this->config->zoom,
                'center' => 'latlng',
                  'mapTypeId' => 'google.maps.MapTypeId.'.$this->config->maptype
            );
        }

        if (!empty($this->config->showlegend)) {
            $jqplot['legend'] = array(
                'show' => true, 
                'location' => 'e', 
                'placement' => 'outsideGrid',
                'showSwatch' => true,
                'marginLeft' => '10px',
                'border' => '1px solid #808080',
                'labels' => $yserieslabels,
            );
        }

        if (!empty($this->config->ymin) ||
                (@$this->config->ymin === 0)) {
            $jqplot['axes']['yaxis']['min'] = (integer)$this->config->ymin;
            $jqplot['axes']['yaxis']['autoscale'] = false;
        }
        if (!empty($this->config->ymax) ||
                (@$this->config->ymax === 0)) {
            $jqplot['axes']['yaxis']['max'] = (integer)$this->config->ymax;
            $jqplot['axes']['yaxis']['autoscale'] = false;
        }
        if (!empty($this->config->tickspacing)) {
            $jqplot['axes']['yaxis']['tickInterval'] = (integer)$this->config->tickspacing;
        }

        return $jqplot;
    }

    /**
     * this function protects the final queries against any harmfull 
     * attempt to change something in the database
     * 
     * rule 1 : avoiding any SQL words that refer to a change. Will resul in syntax error
     * rule 2 : avoiding closing char ";" to appear so a query cannot close to start a new one
     */
    public function protect($sql) {
        $sql = preg_replace('/\b(UPDATE|ALTER|DELETE|INSERT|DROP|CREATE|GRANT)\b/i', '', $sql);
        $sql = preg_replace('/;/', '', $sql);
        return $sql;
    }

    /**
     *
     *
     */
    public function get_count_records_sql($sql) {
        $sql = "
            SELECT
                COUNT(*)
            FROM
                ($sql) as fullrecs
        ";
        return $sql;
    }

    /**
     * provides constraint values from filters
     *
     */
    public function filter_get_results($fielddef, $fieldname, $specialvalue = '', $forcereload = false, &$printoutbuffer = null) {
        static $FILTERSET;
        global $CFG, $DB, $PAGE;

        $config = get_config('block_dashboard');

        $tracing = 0;

        // Computes filter query.

        if (empty($this->filterfields->queries[$fielddef])) {

            // If not explicit query, make an implicit one.

            $sql = preg_replace('/<%%FILTERS%%>|<%%PARAMS%%>/', '', $this->sql);

            if ($this->allow_filter_desaggregate($fielddef)) {
                // Try desagregate.
                $sql = preg_replace('/MAX\(([^\(]+)\)/si', '$1', $sql);
                $sql = preg_replace('/SUM\((.*?)\) AS/si', '$1 AS', $sql);
                $sql = preg_replace('/COUNT\((?:DISTINCT)?([^\(]+)\)/si', '$1', $sql);

                // Purge from unwanted clauses.
                if (preg_match('/\bGROUP BY\b/si', $sql)) {
                    $sql = preg_replace('/GROUP BY.*(?!GROUP BY).*$/si', '', $sql);
                }

                if (preg_match('/\bORDER BY\b/si', $sql)) {
                    $sql = preg_replace('/ORDER BY.*?$/si', '', $sql);
                }
            }

            $filtersql = 'SELECT DISTINCT '.$fieldname.' FROM ( '.$sql.' ) as subreq ';

            $filtersql .= " ORDER BY $fieldname ";
        } else {

            // Explicit query, manager will have to ensure consistency of output values to filter requirement.
            $filtersql = $this->filterfields->queries[$fielddef];
        }

        $filtersql = $this->protect($filtersql);
        $this->filteredsql = $filtersql;

        // Filter values return from cache.
        if (isset($FILTERSET) && array_key_exists($fielddef, $FILTERSET) && empty($specialvalue)) {
            if (!empty($this->config->showfilterqueries)) {
                if (!is_null($printoutbuffer)) {
                    $printoutbuffer .= '<div class="dashboard-filter-query"><b>STATIC CACHED DATA FILTER :</b> '.$filtersql.'</div>';
                }
            }
            return $FILTERSET[$fielddef];
        }

        // Check DB cache.
        $sqlkey = md5($filtersql);
        if (@$this->config->showbenches) {
            $bench = new StdClass;
            $bench->name = 'Filter cache prefetch '.$fielddef;
            $bench->start = time();
        }
        $params = array('querykey' => $sqlkey, 'access' => $this->config->target);
        $cachefootprint = $DB->get_record('block_dashboard_filter_cache', $params);
        if (@$this->config->showbenches) {
            $bench->end = time();
            $this->benches[] = $bench;
        }

        if ((!$PAGE->user_is_editing() ||
                !@$config->enable_isediting_security) &&
                        (!@$this->config->uselocalcaching ||
                                !$cachefootprint ||
                                        ($cachefootprint &&
                                                $cachefootprint->timereloaded < time() - @$this->config->cachingttl * 60) ||
                                                        $forcereload)) {
            $params = array('querykey' => $sqlkey, 'access' => $this->config->target);
            try {
                $DB->delete_records('block_dashboard_filter_cache', $params);
            } catch (Exception $e) {
                throw new \block_dashboard\filter_query_cache_exception($DB->get_last_error());
            }
            list($usec, $sec) = explode(' ', microtime());
            $t1 = (float)$usec + (float)$sec;

            if ($this->config->target == 'moodle') {
                if (@$this->config->showbenches) {
                    $bench = new StdClass;
                    $bench->name = 'Filter pre-query '.$fielddef;
                    $bench->start = time();
                }
                try {
                    $FILTERSET[$fielddef] = $DB->get_records_sql($filtersql);
                } catch (Exception $e) {
                    throw new \block_dashboard\filter_query_exception($DB->get_last_error());
                }
                if (@$this->config->showbenches) {
                    $bench->end = time();
                    $this->benches[] = $bench;
                }
            } else {
                if (!isediting() || empty($config->enable_isediting_security)) {
                    try {
                        $FILTERSET[$fielddef] = extra_db_query($filtersql, false, true, $error);
                    } catch (Exception $e) {
                        throw new \block_dashboard\filter_query_exception($DB->get_last_error());
                    }
                    if ($error) {
                        $this->content->text .= $error;
                    }
                } else {
                    $FILTERSET[$fielddef] = array();
                }
            }

            list($usec, $sec) = explode(' ', microtime());
            $t2 = (float)$usec + (float)$sec;
            // echo $t2 - $t1;  // benching

            // Make a footprint.
            if (!empty($this->config->uselocalcaching)) {
                $cacherec = new StdClass;
                $cacherec->access = $this->config->target;
                $cacherec->querykey = $sqlkey;
                $cacherec->filterrecord = base64_encode(serialize($FILTERSET[$fielddef]));
                $cacherec->timereloaded = time();
                if ($tracing) mtrace('Inserting filter cache');
                $DB->insert_record('block_dashboard_filter_cache', $cacherec);
            }

            if (!empty($this->config->showfilterqueries)) {
                if (!is_null($printoutbuffer)) {
                    $printoutbuffer .= '<div class="dashboard-filter-query"><b>FILTER :</b> '.$filtersql.'</div>';
                }
            }
        } else {
            if ($cachefootprint) {
                if ($tracing) {
                    mtrace('Getting filter data from cache');
                }

                list($usec, $sec) = explode(' ', microtime());
                $t1 = (float)$usec + (float)$sec;

                $FILTERSET[$fielddef] = unserialize(base64_decode($cachefootprint->filterrecord));

                list($usec, $sec) = explode(' ', microtime());
                $t2 = (float)$usec + (float)$sec;
            } else {
                $notretrievablestr = get_string('filternotretrievable', 'block_dashboard');
                $this->content->text .= "<div class=\"dashboard-special\">$notretrievablestr</div>";
            }

            if (!empty($this->config->showfilterqueries)) {
                if (!is_null($printoutbuffer)) {
                    $printoutbuffer .= '<div class="dashboard-filter-query"><b>DB CACHED FILTER :</b> '.$filtersql.'</div>';
                }
            }
        }

        $result = false;
        if (is_array($FILTERSET[$fielddef])) {
            switch ($specialvalue) {
                case 'LAST':
                    $values = array_values($FILTERSET[$fielddef]);
                    $lastvalue = end($values);
                    if ($lastvalue) {
                        $result = $lastvalue->$fieldname;
                    }
                    return $result;

                case 'FIRST':
                    $values = array_values($FILTERSET[$fielddef]);
                    $firstvalue = reset($values);
                    if ($firstvalue) {
                        $result = $firstvalue->$fieldname ;
                    }
                    return $result;

                default:
                    return $FILTERSET[$fielddef];
            }
        }
    }

    /**
     * fetches data and applies a cache strategy if required
     * The cache strategy will store complete "unlimited" results
     * in a local table as serialized records. Only one data set is stored
     * by SQL radical (i.e., removing LIMIT and OFFSET clauses
     * LIMIT and OFFSET are applied to the local proxy.
     * @param string $sql
     * @param arrayref &$results
     * @param int $limit
     * @param int $offset
     * @param bool $forcereload
     * @param bool $tracing
     *
     * @return a string status as complementary info on the data
     */
    public function fetch_dashboard_data($sql, &$results, $limit = '', $offset = '', $forcereload = false, $tracing = false) {
        global $extra_db_CNX, $CFG, $DB, $PAGE;

        $config = get_config('block_dashboard');

        $sqlrad = preg_replace('/LIMIT.*/si', '', $sql);
        $sqlkey = md5("$sql LIMIT $limit OFFSET $offset");

        $cachefootprint = $DB->get_record('block_dashboard_cache', array('querykey' => $sqlkey));

        $results = array();

        /*
         * we can get real data :
         * Only if we are NOT editing => secures acces in case of bad strangled query
         * If we have no cache footprint and are needing one (cache expired or using cache and having no footprint)
         * If reload is forced
         */
        if ((!$PAGE->user_is_editing() ||
                !@$config->enable_isediting_security) &&
                    (!@$this->config->uselocalcaching ||
                        !$cachefootprint ||
                            ($cachefootprint && $cachefootprint->timereloaded < time() - @$this->config->cachingttl * 60) ||
                                $forcereload)) {
            $params = array('querykey' => $sqlkey, 'access' => $this->config->target);
            $DB->delete_records('block_dashboard_cache', $params);
            $DB->delete_records('block_dashboard_cache_data', $params);

            list($usec, $sec) = explode(" ", microtime());
            $t1 = (float)$usec + (float)$sec;

            if ($this->config->target == 'moodle') {
                return $this->get_moodle_results($sql, $results, $limit, $offset);
            } else {
                // This is a pro feature.
                // TODO : enhance performance by using recordsets.

                if (empty($extra_db_CNX)) {
                    extra_db_connect(false, $error);
                }

                if ($allresults = extra_db_query($sql, false, true, $error)) {
                    foreach ($allresults as $reckey => $rec) {
                        if (!empty($this->config->uselocalcaching)) {
                            $cacherec = new StdClass;
                            $cacherec->access = $this->config->target;
                            $cacherec->querykey = $sqlkey;
                            $cacherec->recordid = $reckey; // Get first column in result as key.
                            $cacherec->record = base64_encode(serialize($rec));
                            $DB->insert_record('block_dashboard_cache_data', str_replace("'", "''", $cacherec));
                        }
                    }
                }
                if ($error) {
                    return '<span class="error">'.$error.'</span>';
                }

                if (!empty($limit)) {
                    $sqlpaged = $sql.' LIMIT '.$limit.' OFFSET '.$offset;
                    $results = extra_db_query($sqlpaged, false, true, $error);
                } else {
                    $results = $allresults;
                }

                if ($error) {
                    return '<span class="error">'.$error.'</span>';
                }
            }
            if (!empty($this->config->uselocalcaching) && empty($error)) {
                $timerec = new StdClass;
                $timerec->access = $this->config->target;
                $timerec->querykey = $sqlkey;
                $timerec->timereloaded = time();
                $DB->insert_record('block_dashboard_cache', $timerec);
            }

            list($usec, $sec) = explode(' ', microtime());
            $t2 = (float)$usec + (float)$sec;

            if (@$this->config->showbenches) {
                $this->benches[] = $$t2 - $t1;
            }
            $this->add_param_output_cols($results);

        } else {
            if ($cachefootprint) {
                $results = $this->get_data_from_cache($sqlkey, $limit, $offset);
                $this->add_param_output_cols($results);
                return '<div class="dashboard-special">'.get_string('cacheddata', 'block_dashboard').'</div>';
            } else {
                $notretrievablestr = get_string('notretrievable', 'block_dashboard');
                return '<div class="dashboard-special">'.$notretrievablestr.'</div>';
            }
        }
    }

    /**
     * Internally get results in the current moodle.
     * @param string $sql
     * @param arrayref $results
     * @param int $limit
     * @param int $offset
     */
    protected function get_moodle_results($sql, &$results, $limit, $offset) {
        global $DB;

        $sqlkey = md5("$sql LIMIT $limit OFFSET $offset");

        // Get all results for cache.
        $allresults = array();
        if (@$this->config->showbenches) {
            $bench = new StdClass;
            $bench->name = "main query";
            $bench->start = time();
        }

		if (!empty($limit)) {
			$sql = preg_replace('/LIMIT.*$/', '', $sql);
			$sql .= " LIMIT $limit OFFSET $offset ";
		}

        if ($rs = $DB->get_recordset_sql($sql)) {
            while ($rs->valid()) {
                $rec = $rs->current();
                $recarr = array_values((array)$rec);
                $allresults[$recarr[0]] = $rec;
                if (!empty($this->config->uselocalcaching)) {
                    $cacherec = new StdClass;
                    $cacherec->access = $this->config->target;
                    $cacherec->querykey = $sqlkey;
                    $cacherec->recordid = $recarr[0]; // Get first column in result as key.
                    $cacherec->record = base64_encode(serialize($rec));
                    $DB->insert_record('block_dashboard_cache_data', $cacherec);
                }
                $rs->next();
            }
            $rs->close();
        } else {
            $error = $DB->get_last_error();
            if ($error) {
                return '<span class="error">'.$error.'</span>';
            }
        }

        if ($limit) {
            if ($rs = $DB->get_recordset_sql($sql, $offset, $limit)) {
                while ($rs->valid()) {
                    $rec = $rs->current();
                    $recarr = array_values((array)$rec);
                    $results[$recarr[0]] = $rec;
                    $rs->next();
                }
                $rs->close();
            } else {
                $error = $DB->get_last_error();
                if ($error) {
                    return '<span class="error">'.$error.'</span>';
                }
            }
        } else {
            $results = $allresults;
        }

        if (@$this->config->showbenches) {
            $bench->end = time();
            $this->benches[] = $bench;
        }
        $this->add_param_output_cols($results);
    }

    /**
     * Internally read from dashboard DB cache
     */
    protected function get_data_from_cache($sqlkey, $limit, $offset) {
        global $DB;

        if (!isset($this->content)) {
            $this->content = new StdClass;
            $this->content->text = '';
        }

        $this->content->text .= '<div class="dashboard-special">Cache</div>';

        list($usec, $sec) = explode(' ', microtime());
        $t1 = (float)$usec + (float)$sec;

        $rs = $DB->get_recordset('block_dashboard_cache_data', array('querykey' => $sqlkey), 'id', '*', $offset, $limit);
        while ($rs->valid()) {
            $rec = $rs->current();
            $results[$rec->recordid] = unserialize(base64_decode($rec->record));
            $rs->next();
        }
        $rs->close();

        list($usec, $sec) = explode(' ', microtime());
        $t2 = (float)$usec + (float)$sec;

        if (@$this->config->showbenches) {
            $this->benches[] = $$t2 - $t1;
        }

        return $results;
    }

    /**
     * provides ability to defer cache update to croned delayed period.
     */
    static public function crontask() {
        global $CFG, $DB, $SITE, $PAGE;

        $config = get_config('block_dashboard');

        mtrace("\nDashboard cron...");
        if ($alldashboards = $DB->get_records('block_instances', array('blockname' => 'dashboard'))) {
            $dashtrace = "[".strftime('%Y-%m-%d %H:%M:%S', time())."] Processing dashboards\n";
            foreach ($alldashboards as $dsh) {
                $dashtrace .= "\tProcessing dashboard $dsh->id\n";
                $instance = block_instance('dashboard', $dsh);
                if (!$instance->prepare_config()) {
                    continue;
                }
                $context = context_block::instance($dsh->id);

                if (empty($instance->config->cronmode) || (@$instance->config->cronmode == 'norefresh')) {
                    $dashtrace .= "\tNo cron programmed for $dsh->id\n";
                    mtrace("$dsh->id Skipping norefresh");
                    continue;
                }
                if (!@$instance->config->uselocalcaching) {
                    $dashtrace .= "\tOutputting in files needs caching being enabled\n";
                    mtrace("$dsh->id Skipping as not cached ");
                    continue;
                }

                $needscron = false;
                if (@$instance->config->cronmode == 'global') {
                    $chour = 0 + @$config->cron_hour;
                    $cmin = 0 + @$config->cron_min;
                    $cfreq = @$config->cron_freq;
                } else {
                    $chour = 0 + @$instance->config->cronhour;
                    $cmin = 0 + @$instance->config->cronmin;
                    $cfreq = @$instance->config->cronfrequency;
                }
                $now = time();
                $nowdt = getdate($now);
                $lastdate = getdate(0 + @$instance->config->lastcron);
                $crondebug = optional_param('crondebug', false, PARAM_BOOL);

                // First check we did'nt already refreshed it today (or a new year is starting).
                if ($CFG->debug == DEBUG_DEVELOPER) {
                    mtrace("Day check : Now ".$nowdt['yday']." > Last ".$lastdate['yday'].' ');
                }
                if (($nowdt['yday'] > $lastdate['yday']) || ($lastdate['yday'] == 0) || $crondebug || ($nowdt['yday'] == 0)) {
                    // We wait the programmed time is passed, and check we are an allowed day to run and no query is already running.
                    if (($cfreq == 'daily') || ($nowdt['wday'] == $cfreq) || $crondebug || ($nowdt['yday'] == 0)) {
                        if (((($nowdt['hours'] * 60 + $nowdt['minutes']) >= ($chour * 60 + $cmin)) &&
                                !@$instance->config->isrunning) ||
                                        $crondebug) {
                            $instance->config->isrunning = true;
                            $instance->config->lastcron = $now;
                            $newval = base64_encode(serialize($instance->config));
                            $DB->set_field('block_instances', 'configdata', $newval, array('id' => $dsh->id)); // Save config.

                            // Process data caching.
                            $limit = '';
                            $offset = '';

                            // TODO : compute correct values for $limit and $offset.

                            // We cannot here rely on any filtering or params given by the interactive GUI.
                            $sql = str_replace('<%%FILTERS%%>', '', $instance->config->query);
                            $sql = str_replace('<%%PARAMS%%>', '', $sql);

                            $logbuf = "\t   ... refreshing for instance {$dsh->id} \n";
                            $status = $instance->fetch_dashboard_data($sql, $results, $limit, $offset, true, true /* with mtracing */);

                            if (empty($results)) {
                                $logbuf = "\tEmpty result on query : {$sql} \n";
                                $eventparams = array(
                                    'context' => context_block::instance($dsh->id),
                                    'other' => array(
                                        'blockid' => $dsh->id
                                    ),
                                );
                                $event = \block_dashboard\event\export_task_empty::create($eventparams);
                                $event->trigger();
                            } else {
                                // Generate output file if required.
                                $logbuf = "\tOutputting file for instance {$dsh->id} \n";
                                $csvrenderer = $PAGE->get_renderer('block_dashboard', 'csv');
                                $csvrenderer->generate_output_file($instance, $results);

                                $eventparams = array(
                                    'context' => context_block::instance($dsh->id),
                                    'other' => array(
                                        'blockid' => $dsh->id
                                    ),
                                );
                                $event = \block_dashboard\event\export_task_processed::create($eventparams);
                                $event->trigger();
                            }

                            // Ugly way to do it....
                            $blockconfig = unserialize(base64_decode($DB->get_field('block_instances', 'configdata', array('id' => $dsh->id))));
                            $blockconfig->isrunning = false;
                            $blockconfig->lastcron = time();
                            $DB->set_field('block_instances', 'configdata', base64_encode(serialize($blockconfig)), array('id' => $dsh->id)); // Save config

                            mtrace($logbuf);
                            $dashtrace .= $logbuf;

                            if (!empty($blockconfig->cronadminnotifications)) {
                                $admins = get_admins();
                                foreach ($admins as $admin) {
                                    email_to_user($admin, $admin, $SITE->fullname.': Dashboard export task '.$dsh->id, '', '');
                                }
                            }

                        } else {
                            $msg = '   waiting for valid time for instance '.$dsh->id;
                            mtrace($msg);
                            $dashtrace .= "\t".$msg;
                        }
                    } else {
                        $msg = '   waiting for valid day for instance '.$dsh->id;
                        mtrace($msg);
                        $dashtrace .= "\t".$msg;
                    }
                } else {
                    $msg = '   waiting for next unprocessed day for instance '.$dsh->id;
                    mtrace($msg);
                    $dashtrace .= "\t".$msg;
                }
            }

            if (!empty($config->cron_trace_on)) {
                if ($DASHTRACE = fopen($CFG->dataroot.'/dashboards.log', 'a')) {
                    fputs($DASHTRACE, $dashtrace);
                    fclose($DASHTRACE);
                }
            }
        } else {
            mtrace('no instances to process...');
        }

        return true;
    }

    /**
     * Add some additional virtual columns from user params. Additional "fixed value" output cols
     * may be defined as user params of 'outputcol' type. The param var key generates a new result column
     * in the output result. This may be usefull to produce static/parametric fields in an output CSV file.
     *
     * The post processing occurs in @see block_dashboard::fetch_dashboard_data()
     *
     * @param arrayref &$results
     * @return Void. the incomming results array is processed by reference.
     */
    protected function add_param_output_cols(&$results) {

        if (empty($results)) {
            return;
        }

        $addedcolumns = array();
        foreach ($this->params as $up) {
            if ($up->paramas == 'outputcol') {
                $addedcolumns[] = $up->key;
            }
        }

        if (!empty($addedcolumns)) {
            foreach ($results as $r) {
                foreach ($addedcolumns as $key) {
                    $r->$key = $this->params[$key]->value;
                }
            }
        }
    }

    /**
     * determines if filter is global
     * a global filter will be catched by all dashboard instances in the same page
     */
    public function is_filter_global($filterkey) {
        return strstr($this->filterfields->options[$filterkey], 'g') !== false;
    }

    /**
     * determines if filter is single
     * a single filter can only be constraint by a single value
     */
    public function is_filter_single($filterkey) {
        return strstr($this->filterfields->options[$filterkey], 's') !== false;
    }

    /**
     * determines if filter must desaggregate from original query
     */
    public function allow_filter_desaggregate($filterkey) {
        return strstr($this->filterfields->options[$filterkey], 'x') === false;
    }

    /**
     *
     */
    public function user_can_edit() {
        global $CFG, $COURSE;

        $context = context_course::instance($COURSE->id);

        if (has_capability('block/dashboard:configure', $context)) {
            return true;
        }

        return false;
    }

    /**
     * Decodes and prepare all config structures
     *
     */
    public function prepare_config() {

        // Not setup blocks should not run.
        if (empty($this->config)) {
            return false;
        }

        // No query blocks should not run.
        if (empty($this->config->query)) {
            return false;
        }

        $this->sql = $this->config->query;

        if (empty($this->config->exportcharset)) {
            $this->config->exportcharset = 'utf8';
        }

        // Output from query.
        $outputfields = explode(';', @$this->config->outputfields);
        $outputlabels = explode(';', @$this->config->fieldlabels);
        $outputformats = explode(';', @$this->config->outputformats);
        dashboard_normalize($outputfields, $outputlabels); // Normalizes labels to keys.
        dashboard_normalize($outputfields, $outputformats); // Normalizes labels to keys.
        $this->output = array_combine($outputfields, $outputlabels);
        $this->outputf = array_combine($outputfields, $outputformats);

        // Filtering query.
        $outputfilters = explode(';', @$this->config->filters);
        $this->filters = $outputfilters;
        $outputfilterlabels = explode(';', @$this->config->filterlabels);
        dashboard_normalize($outputfilters, $outputfilterlabels); // Normalizes labels to keys.
        $this->filterfields = new StdClass;
        $this->filterfields->labels = array_combine($outputfilters, $outputfilterlabels);
        $outputfilterdefaults = explode(';', @$this->config->filterdefaults);
        dashboard_normalize($outputfilters, $outputfilterdefaults); // Normalizes defaults to keys.
        $this->filterfields->defaults = array_combine($outputfilters, $outputfilterdefaults);
        $outputfilteroptions = explode(';', @$this->config->filteroptions);
        dashboard_normalize($outputfilters, $outputfilteroptions); // Normalizes options to keys.
        $this->filterfields->options = array_combine($outputfilters, $outputfilteroptions);
        $outputfilterqueries = explode(';', @$this->config->filterqueries);
        dashboard_normalize($outputfilters, $outputfilterqueries); // Normalizes options to keys.
        $this->filterfields->queries = array_combine($outputfilters, $outputfilterqueries);

        // Detect translated.
        $translatedfilters = array();
        $filterfields = array();
        foreach ($outputfilters as $f) {
            if (preg_match('/^(.*) as (.*)$/si', $f, $matches)) {
                $translatedfilters[$f] = $matches[2];
                $filterfields[$matches[2]] = $matches[1];
                $translatedfilters[$matches[2]] = $f;
            }
        }
        $this->filterfields->translations = $translatedfilters;
        $this->filterfields->filtercanonicalfield = $filterfields;

        // Tabular params.
        $vkeys = explode(";", @$this->config->verticalkeys);
        $vformats = explode(";", @$this->config->verticalformats);
        $vlabels = explode(";", @$this->config->verticallabels);
        dashboard_normalize($vkeys, $vformats); // Normalizes formats to keys.
        dashboard_normalize($vkeys, $vlabels); // Normalizes labels to keys.
        $this->vertkeys = new StdClass;
        $this->vertkeys->formats = array_combine($vkeys, $vformats);
        $this->vertkeys->labels = array_combine($vkeys, $vlabels);

        // Treeview params.
        $parentserie = @$this->config->parentserie;
        $treeoutputfields = explode(';', @$this->config->treeoutput);
        $treeoutputformats = explode(';', @$this->config->treeoutputformats);
        dashboard_normalize($treeoutputfields, $treeoutputformats); // Normailzes labels to keys.
        $this->treeoutput = array_combine($treeoutputfields, $treeoutputformats);

        // Summators.
        $numsums = explode(';', @$this->config->numsums);
        $numsumlabels = explode(';', @$this->config->numsumlabels);
        $numsumformats = explode(';', @$this->config->numsumformats);
        dashboard_normalize($numsums, $numsumlabels); // Normailzes labels to keys.
        dashboard_normalize($numsums, $numsumformats); // Normailzes labels to keys.
        $this->outputnumsums = array_combine($numsums, $numsumlabels);
        $this->numsumsf = array_combine($numsums, $numsumformats);

        // Graph params.
        $yseries = explode(';', @$this->config->yseries);
        $yseriesformats = explode(';', @$this->config->yseriesformats);
        $yserieslabels = explode(';', @$this->config->serieslabels);
        dashboard_normalize($yseries, $yseriesformats); // Normalizes formats to keys.
        dashboard_normalize($yseries, $yserieslabels); // Normalizes labels to keys.
        $this->yseries = array_combine($yseries, $yserieslabels);
        $this->yseriesf = array_combine($yseries, $yseriesformats);

        // Coloring params.
        $this->colourcoding = dashboard_prepare_colourcoding($this->config);

        // Prepare user params definitions.
        for ($i = 1 ; $i <= DASHBOARD_MAX_QUERY_PARAMS ; $i++) {
            $varkey = 'sqlparamvar'.$i;
            $paramaskey = 'paramasvar'.$i;
            $labelkey = 'sqlparamlabel'.$i;
            $typekey = 'sqlparamtype'.$i;
            $valueskey = 'sqlparamvalues'.$i;
            $defaultkey = 'sqlparamdefault'.$i;
            if (!empty($this->config->$varkey)) {
                $uparam = new StdClass;
                $uparam->key = $this->config->$varkey;
                $uparam->paramas = @$this->config->$paramaskey;
                $uparam->label = $this->config->$labelkey;
                $uparam->type = $this->config->$typekey;
                $uparam->values = $this->config->$valueskey;
                $uparam->default = @$this->config->$defaultkey;
                $uparam->ashaving = dashboard_guess_is_alias($this, $uparam->key);
                $this->params[$uparam->key] = $uparam;
            }
        }

        return true;
    }

    public function count_records(&$error) {
        global $DB;

        // Counting records to fetch.

        $countsql = $this->get_count_records_sql($this->filteredsql);
        if ($this->config->target == 'moodle') {
            $countres = $DB->count_records_sql($countsql);
        } else {
            $counts = extra_db_query($countsql, false, true, $error);
            if ($counts) {
                $countres = array_pop(array_keys($counts));
            } else {
                $countres = 0;
            }
        }
        return $countres;
    }

    /**
     * Get all params from request and prepare them
     *
     */
    public function prepare_params() {
        global $COURSE, $USER, $CFG;

        $paramsqlarr = array();
        $havingparamsqlarr = array();
        $paramsurlvalues = array();
        $this->queryvars = array();
        $emptyqueryvarkeys = array();

        foreach ($this->params as $key => $param) {
            $sqlkey = $key;
            $key = preg_replace('/[.() *]/', '', $key).'_'.$this->instance->id;
            switch ($param->type) {
                case ('choice'):
                case ('select'):
                case ('date'):
                    $paramvalue = optional_param(core_text::strtolower($key), @$param->default, PARAM_TEXT);
                    $paramvalue = trim($paramvalue); // In case of...
                    if ($param->type == 'date') {
                        $this->params[$sqlkey]->originalvalue = $paramvalue;
                        $paramvalue = strtotime($paramvalue);
                    }
                    $this->params[$sqlkey]->value = $paramvalue;
                    if ($paramvalue) {
                        if ($param->paramas == 'sql') {
                            if ($param->ashaving) {
                                $havingparamsqlarr[] = " {$sqlkey} = '{$paramvalue}' ";
                            } else {
                                $paramsqlarr[] = " {$sqlkey} = '{$paramvalue}' ";
                            }
                            // Collects for making a urlquerystring.
                            $paramsurlvalues[$key] = $paramvalue;
                        } else if ($param->paramas == 'variable') {
                            $this->queryvars[$param->key] = $paramvalue;
                        }
                    } else {
                        if ($param->paramas == 'variable') {
                            // collect all variable keys to remove them from SQL if empty.
                            $emptyqueryvarkeys[] = $param->key;
                        }
                    }
                    break;

                case ('text'):
                    $paramvalue = optional_param(core_text::strtolower($key), @$param->default, PARAM_TEXT);
                    $this->params[$sqlkey]->value = $paramvalue;
                    if ($paramvalue) {
                        if ($param->paramas == 'sql') {
                            if ($param->ashaving) {
                                $havingparamsqlarr[] = " {$sqlkey} LIKE '{$paramvalue}' ";
                            } else {
                                $paramsqlarr[] = " {$sqlkey} LIKE '{$paramvalue}' ";
                            }
                            $paramsurlvalues[$key] = $paramvalue;
                        } else if ($param->paramas == 'variable') {
                            $queryvars[$param->key] = $paramvalue;
                        }
                    } else {
                        if ($param->paramas == 'variable') {
                            // collect all variable keys to remove them from SQL if empty.
                            $emptyqueryvarkeys[] = $param->key;
                        }
                    }
                    break;

                case ('range'):
                case ('daterange'):
                    $valuefrom = optional_param(core_text::strtolower($key).'_from', @$param->default, PARAM_TEXT);
                    $valueto = optional_param(core_text::strtolower($key).'_to', '', PARAM_TEXT);
                    $this->params[$sqlkey]->originalvaluefrom = $valuefrom;
                    $this->params[$sqlkey]->originalvalueto = $valueto;
                    if ($param->type == 'daterange') {
                        if (!is_numeric($valuefrom)) {
                            $valuefrom = strtotime($valuefrom);
                        } else {
                            // Already in timestamp.
                        }
                        if (!is_numeric($valueto)) {
                            $valueto = strtotime($valueto);
                        } else {
                            // Already in timestamp.
                        }
                    }
                    if ($valuefrom || $valueto) {
                        $sqlarr = array();
                        if (!empty($valuefrom)) {
                            $sqlarr[] = " {$sqlkey} >= '{$valuefrom}' ";
                            $paramsurlvalues[$key.'_from'] = $valuefrom;
                        }
                        if (!empty($valueto)) {
                            $sqlarr[] = " {$sqlkey} <= '{$valueto}' ";
                            $paramsurlvalues[$key.'_to'] = $valueto;
                        }
                        if ($param->paramas == 'sql') {
                            if ($param->ashaving) {
                                $havingparamsqlarr[] = ' ('.implode(' AND ', $sqlarr).') ';
                            } else {
                                $paramsqlarr[] = ' ('.implode(' AND ', $sqlarr).') ';
                            }
                        }
                    }
                    $this->params[$sqlkey]->valuefrom = $valuefrom;
                    $this->params[$sqlkey]->valueto = $valueto;
                    break;
            }
        }

        // Integrates final having statement.
        $havingparamsql = implode(' AND ', $havingparamsqlarr);
        if (!preg_match('/\bHAVING\b/i', $this->sql)) {
            if (!empty($havingparamsql)) {
                $havingparamsql = " HAVING $havingparamsql "; // Post processing ?
            }
        } else {
            if (!empty($havingparamsql)) {
                $havingparamsql = " AND $havingparamsql "; // Post processing ?
            }
        }
        $this->sql .= $havingparamsql;
        $this->filteredsql .= $havingparamsql;

        $paramsql = '';
        $paramfilteredsql = '';
        // Integrates where filtering.
        if (!preg_match('/\bWHERE\b/si', $this->sql)) {
            $paramsql = ' WHERE 1=1 ';
        }
        if (!preg_match('/\bWHERE\b/si', $this->filteredsql)) {
            $paramfilteredsql = ' WHERE 1=1 ';
        }
        $paramsqlparts = implode(' AND ', $paramsqlarr);
        if (!empty($paramsqlparts)) {
            $paramsql .= " AND $paramsqlparts ";
            $paramfilteredsql .= " AND $paramsqlparts ";
        }
        $group = groups_get_course_group($COURSE);

        $this->sql = str_replace('<%%PARAMS%%>', $paramsql, $this->sql);
        $this->sql = str_replace('<%%COURSEID%%>', $COURSE->id, $this->sql);
        $this->sql = str_replace('<%%CATID%%>', $COURSE->category, $this->sql);
        $this->sql = str_replace('<%%USERID%%>', $USER->id, $this->sql);
        $this->sql = str_replace('<%%GROUPID%%>', $group, $this->sql);
        $this->sql = str_replace('<%%WWWROOT%%>', $CFG->wwwroot, $this->sql);
        $this->filteredsql = str_replace('<%%PARAMS%%>', $paramfilteredsql, $this->filteredsql);
        $this->filteredsql = str_replace('<%%COURSEID%%>', $COURSE->id, $this->filteredsql);
        $this->filteredsql = str_replace('<%%CATID%%>', $COURSE->category, $this->filteredsql);
        $this->filteredsql = str_replace('<%%USERID%%>', $USER->id, $this->filteredsql);
        $this->filteredsql = str_replace('<%%GROUPID%%>', $group, $this->filteredsql);
        $this->filteredsql = str_replace('<%%WWWROOT%%>', $CFG->wwwroot, $this->filteredsql);

        // Process all queryvars replacements.
        foreach ($this->queryvars as $key => $value) {
            $this->sql = str_replace('<%%'.core_text::strtoupper($key).'%%>', $value, $this->sql);
            $this->filteredsql = str_replace('<%%'.core_text::strtoupper($key).'%%>', $value, $this->filteredsql);
        }

        // Clean out empty variable keys.
        if (!empty($emptyqueryvarkeys)) {
            foreach ($emptyqueryvarkeys as $emptykey) {
                $this->sql = str_replace('<%%'.core_text::strtoupper($emptykey).'%%>', '', $this->sql);
                $this->filteredsql = str_replace('<%%'.core_text::strtoupper($emptykey).'%%>', '', $this->filteredsql);
            }
        }

        if (!empty($paramsurlvalues)) {
            foreach ($paramsurlvalues as $k => $v) {
                $pairs[] = "$k=".urlencode($v);
            }
            $urlquerystring = implode('&', $pairs);
            return $urlquerystring;
        }
        return '';
    }

    /**
     * This function prepares all data related to applying filters from a $filtersinputsource entry
     * and applying configured defaults. It processes blocks members in instance to
     * build the filtered SQL statement. directly gets filter states from $filtersinputsource input
     * @param array $filtersinputsource the filters value source. Defaults to $_GET.
     * @return a querystring segment that reproduces all relevant filter states
     * @todo : examine potential security gaps due to direct processing of $filtersinputsource inputs. check how to secure
     * from SQL injection.
     */
    public function prepare_filters($filtersinputsource = null) {

        if (is_null($filtersinputsource)) {
            $filtersinputsource = $_GET;
        }

        // Capture filters values in input.
        $filterclause = '';
        $filterkeys = preg_grep('/^filter'.$this->instance->id.'_/', array_keys($filtersinputsource));
        $globalfilterkeys = preg_grep('/^filter0_/', array_keys($filtersinputsource));
        $filters = array();
        $filterinputs = array();

        foreach ($filterkeys as $key) {
            $filterinputs[$key] = clean_param($filtersinputsource[$key], PARAM_TEXT);
        }

        foreach ($globalfilterkeys as $key) {
            $radical = str_replace('filter0_', '', $key);
            $cond = array_key_exists($radical, $this->filterfields->translations);
            $canonicalfilter = ($cond) ? $this->filterfields->translations[$radical] : $radical;
            if ($this->is_filter_global($canonicalfilter)) {
                $filterinputs[$key] = clean_param($filtersinputsource[$key], PARAM_TEXT);
            }
        }

        // Recombine filter values into a filtering querystring segment.
        $filterquerystringelms = array();
        foreach ($filterinputs as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $filterquerystringelms[] = "{$key}=".urlencode($v);
                }
            } else {
                $filterquerystringelms[] = "{$key}=".urlencode($value);
            }
        }
        $filterquerystring = implode('&', $filterquerystringelms);

        // Process defaults if setup, faking $filtersinputsource input but not when reporting all data!
        $alldata = optional_param('alldata', 0, PARAM_BOOL);
        if (!empty($this->filterfields->defaults) && !$alldata) {
            foreach ($this->filterfields->defaults as $filter => $default) {
                $cond = array_key_exists($filter, $this->filterfields->translations);
                $canonicalfilter = ($cond) ? $this->filterfields->translations[$filter] : $filter;
                $voidstr = null;
                $default = (preg_match('/LAST|FIRST/i', $default)) ? $this->filter_get_results($filter, $canonicalfilter, $default, false, $voidstr /* no print out */) : $default ;

                if ($this->is_filter_global($filter)) {
                    if (!array_key_exists('filter0_'.$canonicalfilter, $filterinputs)) {
                        $filterinputs['filter0_'.$canonicalfilter] = $default;
                    }
                } else {
                    if (!array_key_exists('filter'.$this->instance->id.'_'.$canonicalfilter, $filterinputs)) {
                        $filterinputs['filter'.$this->instance->id.'_'.$canonicalfilter] = $default;
                    }
                }
            }
        }

        if (!empty($filterinputs)) {
            foreach ($filterinputs as $key => $value) {

                if ($value == '*') {
                    // Strip out catch all options.
                    continue;
                }

                $radical = preg_replace('/filter\d+_/','', $key);
                $cond = isset($this->filterfields->filtercanonicalfield[$radical]);
                $sqlfiltername = ($cond) ? $this->filterfields->filtercanonicalfield[$radical] : $radical;
                if ($value !== '' && !is_null($value)) {
                    if (!is_array($value)) {
                        $filters[] = " $sqlfiltername = '".str_replace("'", "''", $value)."' ";
                    } else {
                        if (count($value) >= 1 || $value[0] != 0) {
                            $filters[] = " $sqlfiltername IN ('".implode("','", str_replace("'", "''", $value))."') ";
                        }
                    }
                    $this->filtervalues[$radical] = $value;
                }
            }
        }

        // Build filtering SQL clause and insert it at placeholder.
        if (!preg_match('/\bWHERE\b/si', $this->sql)) {
            $filterclause = ' WHERE 1=1 ';
        }
        if (!empty($filters)) {
            $filterclause .= ' AND '. implode('AND', $filters);
        }

        $this->filteredsql = str_replace('<%%FILTERS%%>', $filterclause, $this->sql);

        return $filterquerystring;
    }

    public function get_required_javascript() {
        global $CFG, $PAGE;

        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('jqplot', 'local_vflibs');
        $PAGE->requires->css('/local/vflibs/jquery/jqplot/jquery.jqplot.css');

        $PAGE->requires->js('/blocks/dashboard/js/module.js', true);
        $PAGE->requires->js('/blocks/dashboard/js/dhtmlxCalendar/codebase/dhtmlxcalendar.js', true);
        $PAGE->requires->js('/blocks/dashboard/js/dhtmlxCalendar/codebase/dhtmlxcalendar_locales.js', true);

        $graphlibs = $CFG->dirroot.'/local/vflibs';
        $graphwww = '/local/vflibs';

        require_once $CFG->libdir.'/tablelib.php';
        require_once $graphlibs.'/googleplotlib.php';
        require_once $graphlibs.'/timelinelib.php';
        timeline_require_js($graphwww);
        $PAGE->requires->js_call_amd('block_dashboard/dashboard', 'init');
    }

    public function prepare_linear_table(&$table, &$result, &$lastvalue) {

        $tabledata = array();

        $colcount = 0;
        foreach (array_keys($this->output) as $field) {
            if (empty($field)) {
                continue;
            }

            // Did we ask for cumulative results ?

            $cumulativeix = null;
            if (preg_match('/S\((.+?)\)/', $field, $matches)) {
                $field = $matches[1];
                $cumulativeix = $this->instance->id.'_'.$field;
            }

            if (!empty($this->outputf[$field])) {
                $datum = dashboard_format_data($this->outputf[$field], @$result->$field, $cumulativeix, $result);
            } else {
                $datum = dashboard_format_data(null, @$result->$field, $cumulativeix, $result);
            }

            // Process coloring if required.
            if (!empty($this->config->colorfield) &&
                    ($this->config->colorfield == $field)) {
                $datum = dashboard_colour_code($this, $datum, $this->colourcoding);
            }

            $cleanup = !empty($this->config->cleandisplay);
            if (!empty($this->config->cleandisplayuptocolumn)) {
                if ($colcount > $this->config->cleandisplayuptocolumn) {
                    $cleanup = false;
                }
            }

            if ($cleanup) {
                if (!array_key_exists($field, $lastvalue) ||
                        ($lastvalue[$field] != $datum)) {
                    $lastvalue[$field] = $datum;
                    $tabledata[] = $datum;
                } else {
                    $tabledata[] = ''; // If same as above, add blank.
                }
            } else {
                $tabledata[] = $datum;
            }
            $colcount++;
        }
        $table->data[] = $tabledata;
    }

}