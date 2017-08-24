<<<<<<< HEAD
<?php //$Id: block_dashboard.php,v 1.2 2012-09-12 20:07:31 vf Exp $
=======
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
>>>>>>> MOODLE_33_STABLE

/**
 * @package block_dashboard
 * @category blocks
 * @author Valery Fremaux (valery@club-internet.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
<<<<<<< HEAD
 * @version Moodle 2.x
=======
>>>>>>> MOODLE_33_STABLE
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
require_once($CFG->dirroot.'/blocks/dashboard/lib.php');
require_once($CFG->dirroot.'/blocks/dashboard/extradblib.php');
require_once($CFG->dirroot.'/local/vflibs/jqplotlib.php');
if (block_dashboard_supports_feature('result/rotate')) {
    include_once($CFG->dirroot.'/blocks/dashboard/pro/lib.php');
}
require_once($CFG->dirroot.'/blocks/dashboard/lib.php');

global $PAGE;
$PAGE->requires->js('/blocks/dashboard/js/module.js');
$PAGE->requires->js('/blocks/dashboard/js/dhtmlxCalendar/codebase/dhtmlxcalendar.css');
$PAGE->requires->js('/blocks/dashboard/js/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_web.css');

<<<<<<< HEAD
class block_dashboard extends block_base {
	
	var $devmode = true; // use local moodle database to develop virual tools. 
	
	var $filtervalues; // collects effective filter values set by user

	var $paramvalues; // collects effective param values set by user

	var $filters; // stores filter definitions

	var $params; // stores user parameter definitions

	var $output; // stores output definition from query

	var $outputf; // stores output formats specifiers from query

	var $benches; // stores SQL bench info
=======
    protected $devmode = true; // Use local moodle database to develop virual tools.

    public $filtervalues; // Collects effective filter values set by user.

    public $paramvalues; // Collects effective param values set by user.

    public $filters; // Stores filter definitions.
>>>>>>> MOODLE_33_STABLE

    public $params; // Stores user parameter definitions.

    public $output; // Stores output definition from query.

    public $outputf; // Stores output formats specifiers from query.

    protected $benches; // Stores SQL bench info.

    public function init() {
        $this->title = get_string('blockname', 'block_dashboard');
        $this->version = 2013032600;

<<<<<<< HEAD
		$this->filtervalues = array();
		$this->paramvalues = array();
		$this->params = array();
		$this->filters = array();
		$this->output = array();
		$this->outputf = array();

    }
    
    function specialization(){
    	if (!empty($this->config->title)) {
    		$this->title = $this->config->title;
    	}
    }
    
    function hide_header(){
    	if (!isset($this->config->hidetitle)) return false;
        return $this->config->hidetitle;
    }

    function has_config() {
	    return true;
	}

    function instance_allow_multiple() {
	    return true;
	}

    function instance_allow_config() {
        return true;
    }
    
    function instance_config_save($data, $notused = false){
    	global $USER;
    	
    	// check if curent user forcing a filelocationadminoverride can really do it
    	// in case it seems to be forced, set it to empty anyway.
		if (!has_capability('block/dashboard:systempathaccess', context_system::instance())){
			$data->filepathadminoverride = '';
		}
		
		// retrieve sql params directly from POST
		$data->sqlparams = @$_POST['sqlparams'];
		
		// print_object($data);
				
    	return parent::instance_config_save($data, $notused);
=======
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

    public function instance_config_save($data, $notused = false) {
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

        return parent::instance_config_save($data, $notused);
>>>>>>> MOODLE_33_STABLE
    }

    public function applicable_formats() {
        // Default case: the block can be used in all course types.
        return array('all' => true, 'site' => true);
    }

<<<<<<< HEAD
    function get_content() {
        global $EXTRADBCONNECT, $CFG, $COURSE;
                
        @raise_memory_limit('256M');

		// special patch for 1.9 queries
		if (preg_match('/block_instance\b/', @$this->config->query)){
			$this->content = new StdClass;
			$this->content->text = get_string('obsoletequery', 'block_dashboard');
			$this->content->footer = '';
			return $this->content;
		}
=======
    public function get_content() {
        global $extradbcnx, $COURSE, $PAGE, $CFG;

        $this->get_required_javascript();

        $context = context_block::instance($this->instance->id);
        $renderer = $PAGE->get_renderer('block_dashboard');

        @raise_memory_limit('512M');
>>>>>>> MOODLE_33_STABLE

        if (!empty($this->config->query)) {
            preg_replace('/block_instance\b/', 'block_instances', $this->config->query);
            preg_replace('/\{(.*?)\}/', $CFG->prefix."\\1", $this->config->query);
        }

        if ($this->content !== null) {
            return $this->content;
        }

<<<<<<< HEAD
		$this->content = new StdClass;

		if (@$this->config->inblocklayout){
	        $this->content->text = $this->print_dashboard();
	        $this->content->text = '';
	    } else {
	    	$viewdashboardstr = get_string('viewdashboard', 'block_dashboard');
	    	$this->content->text .= "<a href=\"{$CFG->wwwroot}/blocks/dashboard/view.php?id={$COURSE->id}&amp;blockid={$this->instance->id}\">$viewdashboardstr</a>";
	    }
	    $this->content->footer = '';
	    
	    return $this->content;
    }
    
    /**
    * Real raster that prints graphs and data
    *
    */
    function print_dashboard(){
    	global $CFG, $EXTRADBCONNECT, $COURSE, $DB, $OUTPUT;

		/*
		$text = '<link type="text/css" rel="stylesheet" href="'.$CFG->wwwroot.'/blocks/dashboard/js/dhtmlxCalendar/codebase/dhtmlxcalendar.css" />';
		$text .= '<link type="text/css" rel="stylesheet" href="'.$CFG->wwwroot.'/blocks/dashboard/js/dhtmlxCalendar/codebase/skins/dhtmlxcalendar_dhx_web.css" />';
		*/
		$text = '';

		if (!isset($this->config)){
	    	$this->config = new StdClass;
	    }
		$this->config->limit = 20;

		$coursepage = '';
		if ($COURSE->format == 'page'){
			include_once($CFG->dirroot.'/course/format/page/lib.php');
			$pageid = optional_param('page', 0, PARAM_INT); // flexipage page number
			if (!$pageid){
				$flexpage = course_page::get_current_page($COURSE->id);
			} else {
				$flexpage = new StdClass;
				$flexpage->id = $pageid;
			}
			$coursepage = "&page=".$flexpage->id;
		}
		
		$rpage = optional_param('rpage'.$this->instance->id, 0, PARAM_INT); // result page
		
		if ($rpage < 0){
		    $rpage = 0;
		}
        
        // unlogged people cannot see their status 
        if ((!isloggedin() || isguestuser()) && @$this->config->guestsallowed){

    		$text = get_string('guestsnotallowed', 'block_dashboard');

			$loginstr = get_string('login');
        	$text .= "<a href=\"{$wwwroot}/login/index.php\">$loginstr</a>";
            return $text;
        }
        
        if (!isset($this->config) || empty($this->config->query)){
			$noquerystr = get_string('noquerystored', 'block_dashboard');
        	$text = $noquerystr;
            return $text;
        }
        
        if (!isset($CFG->block_dashboard_big_result_threshold)) $CFG->block_dashboard_big_result_threshold = 500;

		// connecting
		if ($this->config->target == 'moodle'){
			// already connected
		} else {
			$error = '';
			if (!isset($EXTRADBCONNECT)) $EXTRADBCONNECT = extra_db_connect(true, $error);
			if ($error){
				$text = $error;
				return $text;
			}
		}

		// prepare all params from config
		
		$this->prepare_config();

		$graphdata = array();
		$ticks = array();
		$filterquerystring = '';

		if (!empty($this->config->filters)){
			try {
				$filterquerystring = $this->prepare_filters();
			} catch (Exception $e) {
				if (debugging()){
					echo $e->error;
				}
				return get_string('invalidorobsoletefilterquery', 'block_dashboard');
			}
		} else {
			$this->filteredsql = str_replace('<%%FILTERS%%>', '', $this->sql);
		}
		$this->sql = str_replace('<%%FILTERS%%>', '', $this->sql); // needed to prepare for filter range prefetch

		if (!empty($this->params)){
			$filterquerystring = ($filterquerystring) ? $filterquerystring.'&'.$this->prepare_params() : $this->prepare_params() ;
		} else {
			$this->sql = str_replace('<%%PARAMS%%>', '', $this->sql); // needed to prepare for filter range prefetch
			$this->filteredsql = str_replace('<%%PARAMS%%>', '', $this->filteredsql); // needed to prepare for filter range prefetch
		}

		$sort = optional_param('tsort'.$this->instance->id, @$this->config->defaultsort, PARAM_TEXT);
		if (!empty($sort)){
			// do not sort if already sorted in explained query
			if (!preg_match('/ORDER\s+BY/si', $this->sql))
			    $this->filteredsql .= " ORDER BY $sort";
		}
		
		$this->filteredsql = $this->protect($this->filteredsql);
		
		// ######### GETTING RESULTS
				
		$countres = $this->count_records($error);
		
		// if too many results, we force paging mode
		if (empty($this->config->pagesize) && ($countres > $CFG->block_dashboard_big_result_threshold) && !empty($this->config->bigresult)){
			$text .= '<span class="error">'.get_string('toomanyrecordsusepaging', 'block_dashboard').'</span><br/>';
			$this->config->pagesize = $CFG->block_dashboard_big_result_threshold;
			$rpage = 0;
		}

		// getting real results including page and offset

		if (!empty($this->config->pagesize)){
			$offset = $rpage * $this->config->pagesize;
		} else {
			$offset = '';
		}

		try {
			$results = $this->fetch_dashboard_data($this->filteredsql, @$this->config->pagesize, $offset);
		} catch (Exception $e){
			return get_string('invalidorobsoletequery', 'block_dashboard', $this->config->query);
		}
		
		if ($results){
			
			$table = new flexible_table('mod-dashboard'.$this->instance->id);

			$instancecontrolvars = array(
				TABLE_VAR_PAGE => 'rpage'.$this->instance->id, 
				TABLE_VAR_SORT => 'tsort'.$this->instance->id,
				TABLE_VAR_HIDE => 'thide'.$this->instance->id,
				TABLE_VAR_SHOW => 'tshow'.$this->instance->id,
			);

			$table->set_control_variables($instancecontrolvars); // use full not to collide with flexipage paging
			
			$tablecolumns = array();
			$tableheaders = array();

			foreach($this->output as $field => $label){
				$tablecolumns[] = $field;
				$tableheaders[] = $label;
			}

			$table->define_columns($tablecolumns);
			$table->define_headers($tableheaders);
			
			$filterquerystringadd = (isset($filterquerystring)) ? "&amp;$filterquerystring" : '' ;

			if (@$this->config->inblocklayout){
				$table->define_baseurl($CFG->wwwroot.'/course/view.php?id='.$COURSE->id.$coursepage.$filterquerystringadd);
			} else {
				$table->define_baseurl($CFG->wwwroot.'/blocks/dashboard/view.php?id='.$COURSE->id.'&amp;blockid='.$this->instance->id.$coursepage.$filterquerystringadd);
			}
			
			if (!empty($this->config->sortable)) $table->sortable(true, $this->config->xaxisfield, SORT_DESC); //sorted by xaxisfield by default
			$table->collapsible(true);
			$table->initialbars(true);

			$table->set_attribute('cellspacing', '0');
			$table->set_attribute('id', 'dashboard'.$this->instance->id);
			$table->set_attribute('class', 'dashboard');
			$table->set_attribute('width', '100%');
			
			foreach($this->output as $field => $label){
				$table->column_class($field, $field);
			}
							
			$table->setup();

			/*			
			$where = $table->get_sql_where();
			$sortsql = $table->get_sql_sort();
			*/
			if (!empty($this->config->pagesize)){
				$table->pagesize($this->config->pagesize, $countres); // no paginating at start
			}
			
			$graphseries = array();

			$treedata = array();
			$treekeys = array();
			
			$lastvalue = array();
			$hcols = array();
			$splitnumsonsort = @$this->config->splitsumsonsort;
			foreach($results as $result){

				// prepare for subsums
				if (!empty($splitnumsonsort)){
					$orderkeyed = strtoupper($result->$splitnumsonsort);
					if (!isset($oldorderkeyed)) $oldorderkeyed = $orderkeyed; // first time
				}

				// pre-aggregates sums
				if (!empty($this->config->shownumsums)){
					foreach(array_keys($this->numsumsf) as $numsum){
						if (empty($numsum)) continue;
						if (!isset($result->$numsum)) continue;
						// make subaggregates (only for linear tables and when sorting criteria is the split column)
						// post aggregate after table output
						if (!isset($aggr)) $aggr = new StdClass;
						$aggr->$numsum = 0 + (float)@$aggr->$numsum + (float)$result->$numsum;
						if (!empty($splitnumsonsort) && @$this->config->tabletype == 'linear' && (preg_match("/\\b$splitnumsonsort\\b/", $sort))){
							$this->subaggr[$orderkeyed]->$numsum = 0 + (float)@$this->subaggr[$orderkeyed]->$numsum + (float)$result->$numsum;
						}
					}
				}

				if (!empty($splitnumsonsort) && @$this->config->tabletype == 'linear' && (preg_match("/\\b$splitnumsonsort\\b/", $sort))){
					if ($orderkeyed != $oldorderkeyed){ // when range changes
						$k = 0;
						$tabledata = null;
						foreach(array_keys($this->output) as $field){
							if (in_array($field, array_keys($this->numsumsf))){
								if (is_null($tabledata)){
									$tabledata = array();
									for ($j = 0 ; $j < $k ; $j++){
										$tabledata[$j] = '';
									}
								}
								$tabledata[$k] = '<b>Tot: '.@$this->subaggr[$oldorderkeyed]->$field.'</b>';
							}
							$k++;
						}
						if (!is_null($tabledata)){
							$table->add_data($tabledata);
						}
						$oldorderkeyed = $orderkeyed;
					}
				}
				
				// Print data in results
				if (!empty($this->config->showdata)){
					if (empty($this->config->tabletype) || $this->config->tabletype == 'linear'){
						$tabledata = array();
						foreach(array_keys($this->output) as $field){
							if (empty($field)) continue;
	
							// did we ask for cumulative results ? 
							$cumulativeix = null;
							if (preg_match('/S\((.+?)\)/', $field, $matches)){
								$field = $matches[1];
								$cumulativeix = $this->instance->id.'_'.$field;
							}
	
							if (!empty($this->outputf[$field])){
								$datum = dashboard_format_data($this->outputf[$field], $result->$field, $cumulativeix);
							} else {
								$datum = dashboard_format_data(null, @$result->$field, $cumulativeix);
							}
	
							// process coloring if required
							if (!empty($this->config->colorfield) && $this->config->colorfield == $field){
								$datum = dashboard_colour_code($this, $datum, $this->colourcoding);
							}
	
							if (!empty($this->config->cleandisplay)){
								if (!array_key_exists($field, $lastvalue) || ($lastvalue[$field] != $datum)){
									$lastvalue[$field] = $datum;
									$tabledata[] = $datum;
								} else {
									$tabledata[] = ''; // if same as above, add blanck
								}
							} else {
								$tabledata[] = $datum;
							}
						}
						$table->add_data($tabledata);
					} else if ($this->config->tabletype == 'tabular') {
						// this is a tabular table
						/* in a tabular table, data can be placed :
						* - in first columns in order of vertical keys
						* the results are grabbed sequentially and spread into the matrix 
						*/
						$keystack = array();
						$matrix = array();
						foreach(array_keys($this->vertkeys->formats) as $vkey){
							if (empty($vkey)) continue;
							$vkeyvalue = $result->$vkey;
							$matrix[] = "['".addslashes($vkeyvalue)."']";
						}
						$hkey = $this->config->horizkey;
						$hkeyvalue = (!empty($hkey)) ? $result->$hkey :  '' ;
						$matrix[] = "['".addslashes($hkeyvalue)."']";
						$matrixst = "\$m".implode($matrix);
						if (!in_array($hkeyvalue, $hcols)) $hcols[] = $hkeyvalue;
						
						// now put the cell value in it
						$outvalues = array();
						foreach(array_keys($this->output) as $field){

							// did we ask for cumulative results ? 
							$cumulativeix = null;
							if (preg_match('/S\((.+?)\)/', $field, $matches)){
								$field = $matches[1];
								$cumulativeix = $this->instance->id.'_'.$field;
							}

							if (!empty($this->outputf[$field])){
								$datum = dashboard_format_data($this->outputf[$field], $result->$field, $cumulativeix);
							} else {
								$datum = dashboard_format_data(null, @$result->$field, $cumulativeix);
							}
							if (!empty($this->config->colorfield) && $this->config->colorfield == $field){
								$datum = dashboard_colour_code($this, $datum, $this->colourcoding);
							}
							$outvalues[] = str_replace("\"", "\\\"", $datum);
						}
						$matrixst .= ' = "'.implode(' ',$outvalues).'"';
						
						// make the matrix in memory
						eval($matrixst.";");
					} else {
						
						$debug = optional_param('debug', false, PARAM_BOOL);
						
						// treeview
						$resultarr = array_values((array)$result);
						$resultid = $resultarr[0];
						if (!empty($parentserie)){
							if (!empty($result->$parentserie)){
								// non root node, attache to his parent if we found it
								if (array_key_exists($result->$parentserie, $treekeys)){
									if (!empty($debug)) echo 'binding to '. $result->$parentserie.'. ';
									$treekeys[$result->$parentserie]->childs[$resultid] = $result;
									if (!array_key_exists($resultid, $treekeys)){
										$treekeys[$resultid] = $result;
									}
								} else {
									// in case nodes do not come in correct order, do not connect but register only
									if (!empty($debug)) echo 'waiting for '. $result->$parentserie.'. ';
									$waitingnodes[$resultid] = $result;
									if (!array_key_exists($resultid, $treekeys)){
										$treekeys[$resultid] = $result;
									}
								}
							} else {
								// root node
								if (!empty($debug)) echo 'root as '. $resultid.'. ';
								if (!array_key_exists($resultid, $treekeys)){
									$treekeys[$resultid] = $result;
								}
								$treedata[$resultid] = &$treekeys[$resultid];
							}
						} else {
							if (!array_key_exists($resultid, $treekeys)){
								$treekeys[$resultid] = $result;
							}
						}
					}
				}
				
				// Prepare data for graphs 
				if (!empty($this->config->showgraph)){
					if (!empty($this->config->xaxisfield)  && $this->config->graphtype != 'googlemap' && $this->config->graphtype != 'timeline'){
						$xaxisfield = $this->config->xaxisfield;
						if ($this->config->graphtype != 'pie'){
							// TODO : check if $this->config->xaxisfield exists really (misconfiguration) 
							$ticks[] = addslashes($result->$xaxisfield);
							$ys = 0;
							foreach(array_keys($this->yseriesf) as $yserie){
								if (!isset($result->$yserie)) continue;
								
								// did we ask for cumulative results ? 
								$cumulativeix = null;
								if (preg_match('/S\((.+?)\)/', $yserie, $matches)){
									$yserie = $matches[1];
									$cumulativeix = $this->instance->id.'_'.$yserie;
								}
								
								if ($this->config->graphtype != 'timegraph'){
									if (!empty($this->yseriesf[$yserie])){
										$graphseries[$yserie][] = dashboard_format_data($this->yseriesf[$yserie], $result->$yserie, $cumulativeix);
									} else {
										$graphseries[$yserie][] = dashboard_format_data(null, $result->$yserie, $cumulativeix);
									}
								} else {
									if (!empty($this->yseriesf[$yserie])){
										$timeelm = array($result->$xaxisfield, dashboard_format_data($this->yseriesf[$yserie], $result->$yserie, $cumulativeix)); 
										$graphseries[$ys][] = $timeelm;
									} else {
										$timeelm = array($result->$xaxisfield, dashboard_format_data(null, $result->$yserie, $cumulativeix));
										$graphseries[$ys][] = $timeelm;
									}
								}
								$ys++;
							}
						} elseif ($this->config->graphtype == 'pie') {
							foreach($yseries as $yserie){
								if (empty($result->$xaxisfield)) $result->$xaxisfield = 'N.C.';
								if (!empty($this->yseriesf[$field])){
									$graphseries[$yserie][] = array($result->$xaxisfield, dashboard_format_data($this->yseriesf[$field], $result->$yserie, false));
								} else {
									$graphseries[$yserie][] = array($result->$xaxisfield, $result->$yserie);
								}
							}
						}
					} else {
						$data[] = $result;
					}
				}
				$graphdata = array_values($graphseries);
			}
			
			//************ post aggregating last subtotal *************//

			if (!empty($this->config->shownumsums) && $results){
				if (!empty($splitnumsonsort) && @$this->config->tabletype == 'linear' && (preg_match("/\\b$splitnumsonsort\\b/", $sort))){
					$k = 0;
					$tabledata = null;
					foreach(array_keys($this->output) as $field){
						if (in_array($field, array_keys($this->numsumsf))){
							if (is_null($tabledata)){
								$tabledata = array();
								for ($j = 0 ; $j < $k ; $j++){
									$tabledata[$j] = '';
								}
							}
							$tabledata[$k] = '<b>Tot: '.@$this->subaggr[$orderkeyed]->$field.'</b>';
						}
						$k++;
					}
					$oldorderkeyed = $orderkeyed;
					if (!is_null($tabledata)){
						$table->add_data($tabledata);
					}
				}
			}

			//************ Starting outputing data ************************//

			// if treeview, need to post process waiting nodes
			if (@$this->config->tabletype == 'treeview'){				
				if(!empty($waitingnodes)){
					foreach($waitingnodes as $wnid => $wn){
						if (array_key_exists($wn->$parentserie, $treekeys)){
							if (!empty($debug)) echo ' postbinding to '. $wn->$parentserie.'. ';
							$treekeys[$wn->$parentserie]->childs[$wnid] = $wn;
							unset($waitingnodes[$wnid]); // free some stuff
						}
					}
				}
			}

			if (@$this->config->inblocklayout){												
				$url = $CFG->wwwroot.'/course/view.php?id='.$COURSE->id.$coursepage.'&tsort'.$this->instance->id.'='.$sort;
			} else {
				$url = $CFG->wwwroot.'/blocks/dashboard/view.php?id='.$COURSE->id.'&blocksid='.$this->instance->id.$coursepage.'&tsort'.$this->instance->id.'='.$sort;
			}
			
			$text .= dashboard_render_filters_and_params_form($this, $sort);

			if ($this->config->showdata){
				$allexportstr = get_string('exportall', 'block_dashboard');
				$tableexportstr = get_string('exportdataastable', 'block_dashboard');
				$filteredexportstr = get_string('exportfiltered', 'block_dashboard');
				$filterquerystring = (!empty($filterquerystring)) ? '&'.$filterquerystring : '' ;
				if (empty($this->config->tabletype) || @$this->config->tabletype == 'linear'){
					ob_start();
					$table->print_html();
					$text .= ob_get_clean();
	
					$text .= "<div style=\"text-align:right\">";
					$text .= "<a href=\"{$CFG->wwwroot}/blocks/dashboard/export/export_csv.php?id={$COURSE->id}&amp;instance={$this->instance->id}&amp;tsort{$this->instance->id}={$sort}&amp;alldata=1\">$allexportstr</a>";
					if ($filterquerystring){
						$text .= " - <a href=\"{$CFG->wwwroot}/blocks/dashboard/export/export_csv.php?id={$COURSE->id}&instance={$this->instance->id}&tsort{$this->instance->id}={$sort}{$filterquerystring}\">$filteredexportstr</a>";
					}
					$text .= "</div>";
				} elseif (@$this->config->tabletype == 'tabular') {
					// forget table and use $m matrix for making display
					$text .= print_cross_table($this, $m, $hcols, $this->config->horizkey, $this->vertkeys, $this->config->horizlabel, true);					
					$text .= "<div style=\"text-align:right\"><a href=\"{$CFG->wwwroot}/blocks/dashboard/export/export_csv.php?id={$COURSE->id}&amp;instance={$this->instance->id}&amp;tsort{$this->instance->id}={$sort}&amp;alldata=1\">$allexportstr</a></div>";
					$text .= "<div style=\"text-align:right\"><a href=\"{$CFG->wwwroot}/blocks/dashboard/export/export_csv_tabular.php?id={$COURSE->id}&instance={$this->instance->id}&tsort{$this->instance->id}={$sort}{$filterquerystring}\">$tableexportstr</a></div>";
				} else {
					$text .= dashboard_print_tree_view($this, $treedata, $this->treeoutput, $this->output, $this->outputf, $this->colourcoding, true);					
					$text .= "<div style=\"text-align:right\"><a href=\"{$CFG->wwwroot}/blocks/dashboard/export/export_csv.php?id={$COURSE->id}&amp;instance={$this->instance->id}&amp;tsort{$this->instance->id}={$sort}&amp;alldata=1\">$allexportstr</a></div>";
				}
			} else {
				$text .= '';
			}
		} else {
			// no data, but render filters anyway
			$text .= dashboard_render_filters_and_params_form($this, $sort);
		}
		
		// showing graph
		if ($this->config->showgraph && !empty($this->config->graphtype)){
			$text .= $OUTPUT->box_start('dashboard-graph-box');
			$graphdesc = $this->dashboard_graph_properties();

			if ($this->config->graphtype != 'googlemap' && $this->config->graphtype != 'timeline'){

				$data = $graphdata;				
				$text .= jqplot_print_graph('dashboard'.$this->instance->id, $graphdesc, $data, $this->config->graphwidth, $this->config->graphheight, '', true, $ticks);

			} elseif ($this->config->graphtype == 'googlemap') {

				$text .= dashboard_render_googlemaps_data($this, $data, $graphdesc);				

			} else {

				// timeline graph
				if (empty($this->config->timelineeventstart) || empty($this->config->timelineeventend)){
					$text .= $OUTPUT->notification("Missing mappings (start or titles)", 'notifyproblem');
				} else {
					$text .= timeline_print_graph($this, 'dashboard'.$this->instance->id, $this->config->graphwidth, $this->config->graphheight, $data, true);
				}
			}
			$text .= $OUTPUT->box_end();
		}
		
		// showing bottom summators
		if ($this->config->numsums){
			$text .= dashboard_render_numsums($this, $aggr);
		}

		// showing query
		if (@$this->config->showquery){
			$text .= '<div class="dashboard-query-box" style="padding:1px;border:1px solid #808080;margin:2px;font-size:0.75em;font-family:monospace">';
			$text .= '<pre>'.$this->filteredsql.'</pre>';
			$text .= '</div>';
		}

		// showing SQL benches
		if (@$this->config->showbenches){
			$text .= '<div class="dashboard-benches-box" style="padding:1px;border:1px solid #808080;margin:2px;font-size:0.75em;font-family:monospace">';
			$text .= '<table width="100%">';
			foreach($this->benches as $bench){
				$value = $bench->end - $bench->start;
				$text .= "<tr><td>{$bench->name}</td><td>{$value} sec.</td></tr>";
			}
			$text .= '</table>';
			$text .= '</div>';
		}
		
		return $text;
    }
    
    /**
    * build a graph descriptor, taking some defaults decisions
    *
    */
    function dashboard_graph_properties(){
    	
    	$jqplot = array();
    	
    	$yserieslabels = explode(';', $this->config->serieslabels);
    	
    	$labelarray = array();
    	foreach($yserieslabels as $label){
			$labelarray[] = array('label' => $label);
		}
	
    	if ($this->config->graphtype == 'line'){
    	
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
			if (@$this->config->yaxisscale == 'log'){
				$jqplot['axes']['yaxis']['renderer'] = '$.jqplot.LogAxisRenderer';
				$jqplot['axes']['yaxis']['rendererOptions'] = array('base' => 10, 'tickDistribution' => 'even');
			}
				
		} elseif($this->config->graphtype == 'bar') {
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
			if (@$this->config->yaxisscale == 'log'){
				$jqplot['axes']['yaxis']['renderer'] = '$.jqplot.LogAxisRenderer';
			}

		} elseif($this->config->graphtype == 'pie') {
			$jqplot = array(
				'seriesDefaults' => array(
					'renderer' => '$.jqplot.PieRenderer',
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
		} elseif($this->config->graphtype == 'donut') {
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
		} elseif($this->config->graphtype == 'timegraph') {
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
			if (@$this->config->yaxisscale == 'log'){
				$jqplot['axes']['yaxis']['renderer'] = '$.jqplot.LogAxisRenderer';
				$jqplot['axes']['yaxis']['rendererOptions'] = array('base' => 10, 'tickDistribution' => 'even');
			}
		} elseif($this->config->graphtype == 'googlemap') {
			if (empty($this->config->maptype)) $this->config->maptype = 'ROADMAP';
			if (empty($this->config->zoom)) $this->config->zoom = 6;
			$jqplot = array(
				'zoom' => $this->config->zoom,
				'center' => 'latlng',
      			'mapTypeId' => 'google.maps.MapTypeId.'.$this->config->maptype
			);
		}

		if (!empty($this->config->showlegend)){
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

		if (!empty($this->config->ymin) || @$this->config->ymin === 0){
			$jqplot['axes']['yaxis']['min'] = (integer)$this->config->ymin;
			$jqplot['axes']['yaxis']['autoscale'] = false;
		}
		if (!empty($this->config->ymax) || @$this->config->ymax === 0){
			$jqplot['axes']['yaxis']['max'] = (integer)$this->config->ymax;
			$jqplot['axes']['yaxis']['autoscale'] = false;
		}
		if (!empty($this->config->tickspacing)){
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
    function protect($sql){
    	
    	$sql = preg_replace('/\b(UPDATE|ALTER|DELETE|INSERT|DROP|CREATE)\b/i', '', $sql);
    	$sql = preg_replace('/;/', '', $sql);
    	return $sql;
    }
    
=======
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
            $DB->delete_records('block_dashboard_filter_cache', $params);
            list($usec, $sec) = explode(' ', microtime());
            $t1 = (float)$usec + (float)$sec;

            if ($this->config->target == 'moodle') {
                if (@$this->config->showbenches) {
                    $bench = new StdClass;
                    $bench->name = 'Filter pre-query '.$fielddef;
                    $bench->start = time();
                }
                $FILTERSET[$fielddef] = $DB->get_records_sql($filtersql);
                if (@$this->config->showbenches) {
                    $bench->end = time();
                    $this->benches[] = $bench;
                }
            } else {
                if (!isediting() || empty($config->enable_isediting_security)) {
                    $FILTERSET[$fielddef] = extra_db_query($filtersql, false, true, $error);
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

        } else {
            if ($cachefootprint) {
                $results = $this->get_data_from_cache($sqlkey, $limit, $offset);
                return '<div class="dashboard-special">'.get_string('cacheddata', 'block_dashboard').'</div>';
            } else {
                $notretrievablestr = get_string('notretrievable', 'block_dashboard');
                return '<div class="dashboard-special">'.$notretrievablestr.'</div>';
            }
        }
    }

>>>>>>> MOODLE_33_STABLE
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
    }
<<<<<<< HEAD
    
    /**
    * provides constraint values from filters 
    *
    */
    function filter_get_results($fielddef, $fieldname, $specialvalue = '', $forcereload = false, &$printoutbuffer = null){
    	static $FILTERSETS;
		global $CFG, $DB, $PAGE;

		$tracing = 0;

		// computes filter query
		
		if (empty($this->filterfields->queries[$fielddef])){
			
			// if not explicit query, make an implicit one
			
			$sql = preg_replace('/<%%FILTERS%%>|<%%PARAMS%%>/', '', $this->sql);

			if ($this->allow_filter_desaggregate($fielddef)){
				// try desagregate
				$sql = preg_replace('/MAX\(([^\(]+)\)/si', '$1', $sql);
				$sql = preg_replace('/SUM\((.*?)\) AS/si', '$1 AS', $sql);
				$sql = preg_replace('/COUNT\((?:DISTINCT)?([^\(]+)\)/si', '$1', $sql);
		
				// purge from unwanted clauses
				if (preg_match('/\bGROUP BY\b/si', $sql)){
		    		$sql = preg_replace('/GROUP BY.*(?!GROUP BY).*$/si', '', $sql);
		    	}
		
				if (preg_match('/\bORDER BY\b/si', $sql)){
		    		$sql = preg_replace('/ORDER BY.*?$/si', '', $sql);
		    	}
		    }
	
			$filtersql = 'SELECT DISTINCT '.$fieldname.' FROM ( '.$sql.' ) as subreq ';
	
			$filtersql .= " ORDER BY $fieldname ";
		} else {
			
			// explicit query, manager will have to ensure consistency of output values to filter requirement
			$filtersql = $this->filterfields->queries[$fielddef];
		}

    	$filtersql = $this->protect($filtersql);

		// filter values return from cache
		if (isset($FILTERSETS) && array_key_exists($fielddef, $FILTERSETS) && empty($specialvalue)){
	    	if (!empty($this->config->showfilterqueries)){
	    		if (!is_null($printoutbuffer))
	    		$printoutbuffer .= "<div class=\"dashboard-filter-query\" style=\"padding:1px;border:1px solid #808080;margin:2px;font-size;0.75em;font-family:monospace\"><b>STATIC CACHED DATA FILTER :</b> $filtersql</div>";
	    	}
			return $FILTERSETS[$fielddef];
		}
		
		// check DB cache
    	$sqlkey = md5($filtersql);
		if (@$this->config->showbenches){
			$bench = new StdClass;
			$bench->name = 'Filter cache prefetch '.$fielddef;
			$bench->start = time();
		}
    	$cachefootprint = $DB->get_record('block_dashboard_filter_cache', array('querykey' => $sqlkey, 'access' => $this->config->target));
		if (@$this->config->showbenches){
			$bench->end = time();
			$this->benches[] = $bench;
		}

    	if ((!$PAGE->user_is_editing() || !@$CFG->block_dashboard_enable_isediting_security) && (!@$this->config->uselocalcaching || !$cachefootprint || ($cachefootprint && $cachefootprint->timereloaded < time() - @$this->config->cachingttl * 60) || $forcereload)){
	        $DB->delete_records('block_dashboard_filter_cache', array('querykey' => $sqlkey, 'access' => $this->config->target));
	
	        list($usec, $sec) = explode(' ', microtime());
    		$t1 = (float)$usec + (float)$sec;

			if ($this->config->target == 'moodle'){
				if (@$this->config->showbenches){
					$bench = new StdClass;
					$bench->name = 'Filter pre-query '.$fielddef;
					$bench->start = time();
				}
				$FILTERSET[$fielddef] = $DB->get_records_sql($filtersql);
				if (@$this->config->showbenches){
					$bench->end = time();
					$this->benches[] = $bench;
				}
			} else {
				if (!isediting() || !@$CFG->block_dashboard_enable_isediting_security){
					$FILTERSET[$fielddef] = extra_db_query($filtersql, false, true, $error);
					if ($error){
						$this->content->text .= $error;
					}
				} else {
					$FILTERSET[$fielddef] = array();
				}
			}

	        list($usec, $sec) = explode(' ', microtime());
    		$t2 = (float)$usec + (float)$sec;
    		// echo $t2 - $t1;  // benching
			
			// make a footprint
			if(!empty($this->config->uselocalcaching)){
				$cacherec = new StdClass;
				$cacherec->access = $this->config->target;
				$cacherec->querykey = $sqlkey;
	            $cacherec->filterrecord = base64_encode(serialize($FILTERSET[$fielddef]));
				$cacherec->timereloaded = time();
				if ($tracing) mtrace('Inserting filter cache');
	            $DB->insert_record('block_dashboard_filter_cache', $cacherec);
	        }			

	    	if (!empty($this->config->showfilterqueries)){
	    		if (!is_null($printoutbuffer)){
		    		$printoutbuffer .= "<div class=\"dashboard-filter-query\" style=\"padding:1px;border:1px solid #808080;margin:2px;font-size;0.75em;font-family:monospace\"><b>FILTER :</b> $filtersql</div>";
		    	}
	    	}
		} else {
			if ($cachefootprint){
				if ($tracing) mtrace('Getting filter data from cache');
	
		        list($usec, $sec) = explode(' ', microtime());
	    		$t1 = (float)$usec + (float)$sec;
	    		
	    		$FILTERSET[$fielddef] = unserialize(base64_decode($cachefootprint->filterrecord));
	
		        list($usec, $sec) = explode(' ', microtime());
	    		$t2 = (float)$usec + (float)$sec;
	    		// echo $t2 - $t1;  // benching
			} else {
				$notretrievablestr = get_string('filternotretrievable', 'block_dashboard');
				$this->content->text .= "<div class=\"dashboard-special\">$notretrievablestr</div>";
			}

	    	if (!empty($this->config->showfilterqueries)){
	    		if (!is_null($printoutbuffer)){
		    		$printoutbuffer .= "<div class=\"dashboard-filter-query\" style=\"padding:1px;border:1px solid #808080;margin:2px;font-size;0.75em;font-family:monospace\"><b>DB CACHED FILTER :</b> $filtersql</div>";
		    	}
	    	}
		}
		
		if (is_array($FILTERSET[$fielddef])){
			switch ($specialvalue) {
				case 'LAST' :
				    $values = array_values($FILTERSET[$fielddef]);
					$result = end()->$fieldname;
					return (!empty($FILTERSET[$fielddef])) ? $result : false ;
				case 'FIRST' :
					$values = array_values($FILTERSET[$fielddef]);
					$result = reset($values)->$fieldname ;
					return (!empty($FILTERSET[$fielddef])) ? $result : false ;
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
	*/    
    function fetch_dashboard_data($sql, $limit = '', $offset = '', $forcereload = false, $tracing = false){
    	global $extra_db_CNX, $CFG, $DB, $PAGE;
    	$sqlrad = preg_replace('/LIMIT.*/si', '', $sql);
    	$sqlkey = md5($sql);
		
    	$cachefootprint = $DB->get_record('block_dashboard_cache', array('querykey' => $sqlkey));
		
    	$results = array();
    	
    	/* 
    	* we can get real data : 
    	* Only if we are NOT editing => secures acces in case of bad strangled query
    	* If we have no cache footprint and are needing one (cache expired or using cache and having no footprint)
    	* If reload is forced
    	*/
    	if ((!$PAGE->user_is_editing() || !@$CFG->block_dashboard_enable_isediting_security) && (!@$this->config->uselocalcaching || !$cachefootprint || ($cachefootprint && $cachefootprint->timereloaded < time() - @$this->config->cachingttl * 60) || $forcereload)){
	        $DB->delete_records('block_dashboard_cache', array('querykey' => $sqlkey, 'access' => $this->config->target));
	        $DB->delete_records('block_dashboard_cache_data', array('querykey' => $sqlkey, 'access' => $this->config->target));
			
	        list($usec, $sec) = explode(" ", microtime());
    		$t1 = (float)$usec + (float)$sec;
			
			if ($this->config->target == 'moodle'){

				// get all results for cache
				$allresults = array();
				if (@$this->config->showbenches){
					$bench = new StdClass;
					$bench->name = "main query";
					$bench->start = time();
				}
				$rs = $DB->get_recordset_sql($sql);
		        while($rs->valid()){
					$rec = $rs->current();
		        	$recarr = array_values((array)$rec);
					$allresults[$recarr[0]] = $rec;
					if(!empty($this->config->uselocalcaching)){
						$cacherec = new StdClass;
						$cacherec->access = $this->config->target;
						$cacherec->querykey = $sqlkey;
			            $cacherec->recordid = $recarr[0]; // get first column in result as key
			            $cacherec->record = base64_encode(serialize($rec));
			            $DB->insert_record('block_dashboard_cache_data', $cacherec);
			        }
					$rs->next();
		        }

				if ($limit){
					$rs = get_recordset_sql($sql, $offset, $limit);
			        while($rec = rs_fetch_next_record($rs)){
			        	$recarr = array_values((array)$rec);
						$results[$recarr[0]] = $rec;
			        }
			    } else {
			    	$results = $allresults;
			    }

				$rs->close();
				if (@$this->config->showbenches){
					$bench->end = time();
					$this->benches[] = $bench;
				}
			} else {
				// TODO : enhance performance by using recordsets
				
				if (empty($extra_db_CNX)){
					extra_db_connect(false, $error);
				}
				
				if ($tracing) mtrace('Getting data from DB');

				if($allresults = extra_db_query($sql, false, true, $error)){
					foreach($allresults as $reckey => $rec){
						// $recarr = (array)$rec;
						if(!empty($this->config->uselocalcaching)){
							$cacherec = new StdClass;
							$cacherec->access = $this->config->target;
							$cacherec->querykey = $sqlkey;
				            $cacherec->recordid = $reckey; // get first column in result as key
				            $cacherec->record = base64_encode(serialize($rec));
				            $DB->insert_record('block_dashboard_cache_data', str_replace("'", "''", $cacherec));
				        }
				    }
				}
				if ($error){
					$this->content->text .= '<span class="error">'.$error.'</span>';
					return array();
				}

				if (!empty($limit)){ 
					$sqlpaged = $sql.' LIMIT '.$limit.' OFFSET '.$offset;
					$results = extra_db_query($sqlpaged, false, true, $error);
				} else {
					$results = $allresults;
				}

				if ($error){
					$this->content->text .= '<span class="error">'.$error.'</span>';
					return array();
				}
			}
			if(!empty($this->config->uselocalcaching) && empty($error)){
				$timerec = new StdClass;
				$timerec->access = $this->config->target;
				$timerec->querykey = $sqlkey;
				$timerec->timereloaded = time();
	            $DB->insert_record('block_dashboard_cache', $timerec);
	        }
			
	        list($usec, $sec) = explode(' ', microtime());
    		$t2 = (float)$usec + (float)$sec;
    		// echo $t2 - $t1; // benching

    	} else {
			if ($cachefootprint){
				if ($tracing) mtrace('Getting data from cache');
				if (!isset($this->content)) $this->content = new StdClass;
	    		$this->content->text .= "<div class=\"dashboard-special\">Cache</div>";
	    		// we are caching and have a key
	
		        list($usec, $sec) = explode(' ', microtime());
	    		$t1 = (float)$usec + (float)$sec;
	    		
				if (@$this->config->showbenches){
					$bench = new StdClass;
					$bench->name = "cache query";
					$bench->start = time();
				}
	    		$rs = $DB->get_recordset('block_dashboard_cache_data', array('querykey' => $sqlkey), 'id', '*', $offset, $limit);
		        while($rs->valid()){
					$rec = $rs->current();
		            $results[$rec->recordid] = unserialize(base64_decode($rec->record));
					$rs->next();
		        }
				$rs->close();
				if (@$this->config->showbenches){
					$bench->end = time();
					$this->benches[] = $bench;
				}
				
		        list($usec, $sec) = explode(' ', microtime());
	    		$t2 = (float)$usec + (float)$sec;
	    		// echo $t2 - $t1;  // benching
			} else {
				$notretrievablestr = get_string('notretrievable', 'block_dashboard');
				$this->content->text .= "<div class=\"dashboard-special\">$notretrievablestr</div>";
			}
    	}

		return $results;
    }

	/**
	* provides ability to defer cache update to croned delayed period     
	*/
    function cron(){
    	global $CFG, $DB;

		mtrace('Dashboard cron...');
    	
    	if (!empty($CFG->block_dashboard_cron_enabled)){
    		$block = $DB->get_record('block', array('name' => 'dashboard'));
    		if($alldashboards = $DB->get_records('block_instances', array('blockid' => $block->id))){
	    		foreach($alldashboards as $dsh){
	    			$config = unserialize(base64_decode($dsh->configdata));

	    			if (empty($config->cronmode) or (@$config->cronmode == 'norefresh')) continue;
	    			if (!@$config->uselocalcaching) continue;

					$needscron = false;
					if ($config->cronmode == 'global'){
						$chour = 0 + @$CFG->block_dashboard_cron_hour;
						$cmin = 0 + @$CFG->block_dashboard_cron_min;
						$cfreq = @$CFG->block_dashboard_cron_freq;
					} else {
						$chour = 0 + @$config->cronhour;
						$cmin = 0 + @$config->cronmin;
						$cfreq = @$config->cronfrequency;
					}
    				$now = time();
    				$nowdt = getdate($now);
    				$lastdate = getdate(0 + @$config->lastcron);
    				$crondebug = optional_param('crondebug', false, PARAM_BOOL);
    				// first check we did nt already refreshed it today (or a new year is starting)
    				if (($nowdt['yday'] > $lastdate['yday']) || ($nowdt['yday'] == 0) || $crondebug){
    					// we wait the programmed time is passed, and check we are an allowed day to run and no query is already running
    					if ($cfreq == 'daily' || ($nowdt['wday'] == $cfreq)){
		    				if (($nowdt['hours'] >= $chour && $nowdt['minutes'] > $cmin && !@$config->isrunning) || $crondebug){
		    					$config->isrunning = true;
		    					$config->lastcron = $now;
		    					$DB->set_field('block_instances', 'configdata', base64_encode(serialize($config)), array('id' => $dsh->id)); // Save config
								
		    					// process data caching
		    					$dshobj = new block_dashboard();
		    					$dshobj->config = $config;
		    					$limit = '';
		    					$offset = '';
		    					
		    					// TODO : compute correct values for $limit and $offset
		    					$sql = str_replace('<%%FILTERS%%>', '', $config->query);
		    					mtrace('   ... refreshing for instance '.$dsh->id);
		    					$results = $dshobj->fetch_dashboard_data($sql, $limit, $offset, true, true /* with mtracing */);
		    					
		    					if (empty($results)){
		    						mtrace('Empty result on query : '.$sql);
		    					}
	
								// generate output file if required    	
						    	if (!empty($config->makefile) && !empty($results)){
	
									if (!empty($config->filepathadminoverride)){
										// an admin has configured, can be anywhere in moodledata
							    		$outputfile = $CFG->dataroot.'/'.$config->filepathadminoverride.'/'.$config->filelocation;
									} else {
										// needs being in course files
							    		$outputfile = $CFG->dataroot.'/'.$dsh->pageid.'/'.$config->filelocation;
									}
						    		
						    		if (!isset($CFG->block_dashboard_output_field_separator)) $CFG->block_dashboard_output_field_separator = ';';
						    		if (!isset($CFG->block_dashboard_output_line_separator)) $CFG->block_dashboard_output_line_separator = 'LF';
						    		$FIELDSEPARATORS = array(':' => ':', ";" => ";", "TAB" => "\t");
						    		$LINESEPARATORS = array('LF' => "\n", 'CR' => "\r", "CRLF" => "\n\r");

									// output from query
									if (!empty($config->fileoutput)){
										$outputfields = explode(';', $config->fileoutput);
										$outputformats = explode(';', $config->fileoutputformats);
									} else {
										$outputfields = explode(';', $config->outputfields);
										$outputformats = explode(';', $config->outputformats);
									}
									dashboard_normalize($outputfields, $outputformats); // normalizes labels to keys
									$this->outputf = array_combine($outputfields, $outputformats);
									
		    						mtrace('   ... generating file for instance '.$dsh->id.' in format '.$config->fileformat);
		    						if (!empty($this->outputf)){
							    		$FILE = fopen($outputfile, 'wb');

										if ($config->fileformat == 'CSV'){
											// print col names
							    			$rarr = array();
							    			foreach($this->outputf as $key => $format){
								    			$rarr[] = $key;
							    			}
							    			fputs($FILE, implode($FIELDSEPARATORS[$CFG->block_dashboard_output_field_separator], $rarr));
							    			fputs($FILE, $LINESEPARATORS[$CFG->block_dashboard_output_line_separator]);
							    		}

										if ($config->fileformat == 'CSV' || $config->fileformat == 'CSVWH'){
											// print effective records	
											$reccount = 0;
								    		foreach($results as $result){
								    			$rarr = array();
								    			foreach($this->outputf as $key => $format){
								    				if (empty($format)){
										    			$rarr[] = @$result->$key;
										    		} else {
										    			$rarr[] = dashboard_format_data($format, @$result->$key);
										    		}
								    			}
								    			fputs($FILE, implode($FIELDSEPARATORS[$CFG->block_dashboard_output_field_separator], $rarr));
								    			fputs($FILE, $LINESEPARATORS[$CFG->block_dashboard_output_line_separator]);
								    			$reccount++;
								    		}
								    		mtrace ($reccount.' processed');
								    	}

										if ($config->fileformat == 'SQL'){
											if (empty($config->filesqlouttable)){
												mtrace('SQL required for output but no SQL table name given');
												continue;
											}
							    			$colnames = array();
							    			foreach($this->outputf as $key => $format){
							    				$colnames[] = $key;
							    			}

								    		foreach($results as $result){
								    			$values = array();
								    			foreach($this->outputf as $key => $format){
								    				if (empty($format)){
								    					$format = 'TEXT';
										    		}
									    			$values[] = dashboard_format_data($format, str_replace("'", "''", $result->$key));
								    			}
									    		$valuegroup = implode(",", $values);
									    		$colgroup = implode(",", $colnames);
									    		$statement = "INSERT INTO {$config->filesqlouttable}($colgroup) VALUES ($valuegroup);\n";										    		
							    				fputs($FILE, $statement);
								    		}
										}
								    	
							    		fclose($FILE);
							    	}
						    	}
	
		    					$config->isrunning = false;
		    					$DB->set_field('block_instances', 'configdata', base64_encode(serialize($config)), array('id' => $dsh->id)); // Save config
		    				} else {
								mtrace('   waiting for valid time for instance '.$dsh->id);
		    				}
		    			} else {
							mtrace('   waiting for valid day for instance '.$dsh->id);
		    			}
	    			} else {
						mtrace('   waiting for next unprocessed day for instance '.$dsh->id);
	    			}
	    		}
	    	} else {
	    		mtrace('no instances to process...');
	    	}
    	} else {
    		mtrace('dashboard cron disabled.');    		
    	}

    	return true;
    }
    
=======

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
            foreach ($alldashboards as $dsh) {
                $instance = block_instance('dashboard', $dsh);
                if (!$instance->prepare_config()) {
                    continue;
                }
                $context = context_block::instance($dsh->id);

                if (empty($instance->config->cronmode) or (@$instance->config->cronmode == 'norefresh')) {
                    mtrace("$dsh->id Skipping norefresh");
                    continue;
                }
                if (!@$instance->config->uselocalcaching) {
                    mtrace("$dsh->id Skipping no cache ");
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
                        if (($nowdt['hours'] * 60 + $nowdt['minutes'] >= $chour *60 + $cmin &&
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

                            mtrace('   ... refreshing for instance '.$dsh->id);
                            $status = $instance->fetch_dashboard_data($sql, $results, $limit, $offset, true, true /* with mtracing */);

                            if (empty($results)) {
                                mtrace('Empty result on query : '.$sql);
                            } else {
                                // Generate output file if required.
                                $csvrenderer = $PAGE->get_renderer('block_dashboard', 'csv');
                                $csvrenderer->generate_output_file($instance, $results);
                            }

                            // Ugly way to do it....
                            $blockconfig = unserialize(base64_decode($DB->get_field('block_instances', 'configdata', array('id' => $dsh->id))));
                            $blockconfig->isrunning = false;
                            $DB->set_field('block_instances', 'configdata', base64_encode(serialize($blockconfig)), array('id' => $dsh->id)); // Save config

                            $eventparams = array(
                                'context' => context_block::instance($dsh->id),
                                'other' => array(
                                    'blockid' => $dsh->id
                                ),
                            );
                            $event = \block_dashboard\event\export_task_processed::create($eventparams);
                            $event->trigger();

                            if (!empty($blockconfig->cronadminnotifications)) {
                                $admins = get_admins();
                                foreach($admins as $admin) {
                                    email_to_user($admin, $admin, $SITE->fullname.': Dashboard export task '.$dsh->id, '', '');
                                }
                            }

                        } else {
                            mtrace('   waiting for valid time for instance '.$dsh->id);
                        }
                    } else {
                        mtrace('   waiting for valid day for instance '.$dsh->id);
                    }
                } else {
                    mtrace('   waiting for next unprocessed day for instance '.$dsh->id);
                }
            }
        } else {
            mtrace('no instances to process...');
        }

        return true;
    }

>>>>>>> MOODLE_33_STABLE
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
<<<<<<< HEAD
    *
    */
    function user_can_edit() {
        global $CFG, $COURSE;

        $context = context_course::instance($COURSE->id);
		
        if (has_capability('block/dashboard:configure', $context)){
 	       return true;
        }
=======
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
>>>>>>> MOODLE_33_STABLE

		return false;
    }
    
    /**
<<<<<<< HEAD
    * Decodes and prepare all config structures
    *
    */
    function prepare_config(){

		$this->sql = $this->config->query;
    	
		// output from query
		$outputfields = explode(';', @$this->config->outputfields);
		$outputlabels = explode(';', @$this->config->fieldlabels);
		$outputformats = explode(';', @$this->config->outputformats);
		dashboard_normalize($outputfields, $outputlabels); // normalizes labels to keys
		dashboard_normalize($outputfields, $outputformats); // normalizes labels to keys
		$this->output = array_combine($outputfields, $outputlabels);
		$this->outputf = array_combine($outputfields, $outputformats);

		// filtering query
		$outputfilters = explode(';', @$this->config->filters);
		$outputfilterlabels = explode(';', @$this->config->filterlabels);
		dashboard_normalize($outputfilters, $outputfilterlabels); // normalizes labels to keys
		$this->filterfields = new StdClass;
		$this->filterfields->labels = array_combine($outputfilters, $outputfilterlabels);
		$outputfilterdefaults = explode(';', @$this->config->filterdefaults);
		dashboard_normalize($outputfilters, $outputfilterdefaults); // normalizes defaults to keys
		$this->filterfields->defaults = array_combine($outputfilters, $outputfilterdefaults);
		$outputfilteroptions = explode(';', @$this->config->filteroptions);
		dashboard_normalize($outputfilters, $outputfilteroptions); // normalizes options to keys
		$this->filterfields->options = array_combine($outputfilters, $outputfilteroptions);
		$outputfilterqueries = explode(';', @$this->config->filterqueries);
		dashboard_normalize($outputfilters, $outputfilterqueries); // normalizes options to keys
		$this->filterfields->queries = array_combine($outputfilters, $outputfilterqueries);

		// Detect translated
		$translatedfilters = array();
		$filterfields = array();
		foreach($outputfilters as $f){
    		if (preg_match('/^(.*) as (.*)$/si', $f, $matches)){
    			$translatedfilters[$f] = $matches[2];
    			$filterfields[$matches[2]] = $matches[1];
    			$translatedfilters[$matches[2]] = $f;
    		}
		}
		$this->filterfields->translations = $translatedfilters;
		$this->filterfields->filtercanonicalfield = $filterfields;
		
		// tabular params
		$vkeys = explode(";", @$this->config->verticalkeys);
		$vformats = explode(";", @$this->config->verticalformats);
		$vlabels = explode(";", @$this->config->verticallabels);
		dashboard_normalize($vkeys, $vformats); // normalizes formats to keys
		dashboard_normalize($vkeys, $vlabels); // normalizes labels to keys
		$this->vertkeys = new StdClass;
		$this->vertkeys->formats = array_combine($vkeys, $vformats);
		$this->vertkeys->labels = array_combine($vkeys, $vlabels);

		// treeview params
		$parentserie = @$this->config->parentserie;
		$treeoutputfields = explode(';', @$this->config->treeoutput);
		$treeoutputformats = explode(';', @$this->config->treeoutputformats);
		dashboard_normalize($treeoutputfields, $treeoutputformats); // normailzes labels to keys
		$this->treeoutput = array_combine($treeoutputfields, $treeoutputformats);
		
		// summators
		$numsums = explode(';', @$this->config->numsums);
		$numsumlabels = explode(';', @$this->config->numsumlabels);
		$numsumformats = explode(';', @$this->config->numsumformats);
		dashboard_normalize($numsums, $numsumlabels); // normailzes labels to keys
		dashboard_normalize($numsums, $numsumformats); // normailzes labels to keys
		$this->outputnumsums = array_combine($numsums, $numsumlabels);
		$this->numsumsf = array_combine($numsums, $numsumformats);
		
		// graph params
		$yseries = explode(';', @$this->config->yseries);
		$yseriesformats = explode(';', @$this->config->yseriesformats);
		dashboard_normalize($yseries, $yseriesformats); // normalizes labels to keys
		$this->yseriesf = array_combine($yseries, $yseriesformats);
		
		// coloring params
		$this->colourcoding = dashboard_prepare_colourcoding($this->config);
		
		// prepare user params definitions
		for($i = 1 ; $i < 5 ; $i++){			
			$varkey = 'sqlparamvar'.$i;
			$labelkey = 'sqlparamlabel'.$i;
			$typekey = 'sqlparamtype'.$i;
			$valueskey = 'sqlparamvalues'.$i;
			if (!empty($this->config->$varkey)){
				$uparam = new StdClass;
				$uparam->key = $this->config->$varkey;
				$uparam->label = $this->config->$labelkey;
				$uparam->type = $this->config->$typekey;
				$uparam->values = $this->config->$valueskey;
				$uparam->ashaving = dashboard_guess_is_alias($this, $uparam->key);
				$this->params[$uparam->key] = $uparam;
			}
		}
    }
    
    function count_records(&$error){
    	global $DB;
    	
		// counting records to fetch		
				
		$countsql = $this->get_count_records_sql($this->filteredsql);
		if ($this->config->target == 'moodle'){
			$countres = $DB->count_records_sql($countsql);
		} else {			
			$counts = extra_db_query($countsql, false, true, $error);
			if ($counts){
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
    function prepare_params(){

    	$paramsqlarr = array();
    	$havingparamsqlarr = array();
    	$paramsurlvalues = array();
    	foreach($this->params as $key => $param){
    		$sqlkey = $key;
			$key = preg_replace('/[.() *]/', '', $key);
    		switch($param->type){
    			case ('choice'):
    			case ('list'):
    			case ('date'):
		    		$paramvalue = optional_param($key, '', PARAM_TEXT);
					$paramvalue = trim($paramvalue); // in case of...
		    		if ($param->type == 'date'){
		    			$this->params[$sqlkey]->originalvalue = $paramvalue;
		    			$paramvalue = strtotime($paramvalue);
		    		}
		    		$this->params[$sqlkey]->value = $paramvalue;
		    		if ($paramvalue){
				    	if ($param->ashaving){
				    		$havingparamsqlarr[] = " {$sqlkey} = '{$paramvalue}' ";
				    	} else {
				    		$paramsqlarr[] = " {$sqlkey} = '{$paramvalue}' ";
				    	}
				    	// collects for making a urlquerystring
				    	$paramsurlvalues[$key] = $paramvalue;
				    }
		    		break;
    			case ('text'):
		    		$paramvalue = optional_param($key, '', PARAM_TEXT);
		    		$this->params[$sqlkey]->value = $paramvalue;
		    		if ($paramvalue){
				    	if ($param->ashaving){
				    		$havingparamsqlarr[] = " {$sqlkey} LIKE '{$paramvalue}' ";
				    	} else {
				    		$paramsqlarr[] = " {$sqlkey} LIKE '{$paramvalue}' ";
				    	}
				    	$paramsurlvalues[$key] = $paramvalue;
			    	}
    				break;
    			case ('range'):
    			case ('daterange'):
		    		$valuefrom = optional_param($key.'_from', '', PARAM_TEXT);
		    		$valueto = optional_param($key.'_to', '', PARAM_TEXT);
		    		$this->params[$sqlkey]->originalvaluefrom = $valuefrom;
		    		$this->params[$sqlkey]->originalvalueto = $valueto;
		    		if ($param->type == 'daterange'){
		    			$valuefrom = strtotime($valuefrom);
		    			$valueto = strtotime($valueto);
		    		}
		    		if ($valuefrom || $valueto){
			    		$sqlarr = array();
			    		if (!empty($valuefrom)){
				    		$sqlarr[] = " {$sqlkey} >= '{$valuefrom}' ";
				    		$paramsurlvalues[$key.'_from'] = $valuefrom;
				    	}
			    		if (!empty($valueto)){
				    		$sqlarr[] = " {$sqlkey} <= '{$valueto}' ";
				    		$paramsurlvalues[$key.'_to'] = $valueto;
				    	}
				    	if ($param->ashaving){
				    		$havingparamsqlarr[] = ' ('.implode(' AND ', $sqlarr).') ';
				    	} else {
				    		$paramsqlarr[] = ' ('.implode(' AND ', $sqlarr).') ';
				    	}
			    	}
		    		$this->params[$sqlkey]->valuefrom = $valuefrom;
		    		$this->params[$sqlkey]->valueto = $valueto;
		    		break;
			}
    	}

		// integrates final having statement
    	$havingparamsql = implode(' AND ', $havingparamsqlarr);
    	if (!preg_match('/\bHAVING\b/i', $this->sql)){
    		if (!empty($havingparamsql)) $havingparamsql = " HAVING $havingparamsql "; // post processing ?
    	} else {
    		if (!empty($havingparamsql)) $havingparamsql = " AND $havingparamsql "; // post processing ?
    	}
    	$this->sql .= $havingparamsql;
    	$this->filteredsql .= $havingparamsql;

		// integrates where filtering
    	$paramsql = implode(' AND ', $paramsqlarr);
    	if (!empty($paramsql)) $paramsql = " AND $paramsql ";
		$this->sql = str_replace('<%%PARAMS%%>', $paramsql, $this->sql);
		$this->filteredsql = str_replace('<%%PARAMS%%>', $paramsql, $this->filteredsql);

		// echo $this->sql;

    	// print_object($this->params);
    	
    	if (!empty($paramsurlvalues)){
    		foreach($paramsurlvalues as $k => $v){
    			$pairs[] = "$k=".urlencode($v);
    		}
    		$urlquerystring = implode('&', $pairs);
    		return $urlquerystring;
    	}
    	return '';
    }
    
    function prepare_filters(){

    	// capture filters
		$filterclause = '';
		$filterkeys = preg_grep('/^filter'.$this->instance->id.'_/', array_keys($_GET));
		$globalfilterkeys = preg_grep('/^filter0_/', array_keys($_GET));
		$filters = array();
		$filterinputs = array();
		
		foreach($filterkeys as $key){
			$filterinputs[$key] = $_GET[$key];
		}

		foreach($globalfilterkeys as $key){
			$radical = str_replace('filter0_', '', $key);
			$canonicalfilter = (array_key_exists($radical, $this->filterfields->translations)) ? $this->filterfields->translations[$radical] : $radical;
			if ($this->is_filter_global($canonicalfilter)){
				$filterinputs[$key] = $_GET[$key];
			}
		}
		
		$filterquerystringelms = array();
		foreach($filterinputs as $key => $value){
			if (is_array($value)){
				foreach($value as $v){
					$filterquerystringelms[] = "{$key}=".urlencode($v);
				}
			} else {
				$filterquerystringelms[] = "{$key}=".urlencode($value);
			}
		}
		$filterquerystring = implode('&', $filterquerystringelms);
		
		// process defaults if setup, faking $_GET input
		if (!empty($this->filterfields->defaults)){
			foreach($this->filterfields->defaults as $filter => $default){
				$canonicalfilter = (array_key_exists($filter, $this->filterfields->translations)) ? $this->filterfields->translations[$filter] : $filter;
				$voidstr = null;
				$default = (preg_match('/LAST|FIRST/i', $default)) ? $this->filter_get_results($filter, $canonicalfilter, $default, false, $voidstr /* no print out */) : $default ;
				if ($this->is_filter_global($filter)){
					if (!array_key_exists('filter0_'.$canonicalfilter, $filterinputs)) $filterinputs['filter0_'.$canonicalfilter] = $default;
				} else {
					if (!array_key_exists('filter'.$this->instance->id.'_'.$canonicalfilter, $filterinputs)) $filterinputs['filter'.$this->instance->id.'_'.$canonicalfilter] = $default;
				}
			}
		}
		
		if (!empty($filterinputs)){
			foreach($filterinputs as $key => $value){
				$radical = preg_replace('/filter\d+_/','', $key);
				$sqlfiltername = (isset($this->filterfields->filtercanonicalfield[$radical])) ? $this->filterfields->filtercanonicalfield[$radical] : $radical ;
				if (!empty($value)){
					if (!is_array($value)){
						$filters[] = " $sqlfiltername = '".str_replace("'", "''", $value)."' ";
					} else {
						if (count($value) >= 1 || $value[0] != 0){
							$filters[] = " $sqlfiltername IN ('".implode("','", str_replace("'", "''", $value))."') ";
						}
					}
					$this->filtervalues[$radical] = $value;
				}
			}
		}

		if (!empty($filters)){
			if (!preg_match('/\bWHERE\b/si', $this->sql)){
				$filterclause = ' WHERE '.implode('AND', $filters);
			} else {
				$filterclause = ' AND '. implode('AND', $filters);
			}
		}
	    $this->filteredsql = str_replace('<%%FILTERS%%>', $filterclause, $this->sql); 
	    
	    return $filterquerystring;   
	}
}
=======
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
        for ($i = 1 ; $i < 5 ; $i++) {
            $varkey = 'sqlparamvar'.$i;
            $labelkey = 'sqlparamlabel'.$i;
            $typekey = 'sqlparamtype'.$i;
            $valueskey = 'sqlparamvalues'.$i;
            if (!empty($this->config->$varkey)) {
                $uparam = new StdClass;
                $uparam->key = $this->config->$varkey;
                $uparam->label = $this->config->$labelkey;
                $uparam->type = $this->config->$typekey;
                $uparam->values = $this->config->$valueskey;
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
        foreach ($this->params as $key => $param) {
            $sqlkey = $key;
            $key = preg_replace('/[.() *]/', '', $key).'_'.$this->instance->id;
            switch ($param->type) {
                case ('choice'):
                case ('list'):
                case ('date'):
                    $paramvalue = optional_param($key, '', PARAM_TEXT);
                    $paramvalue = trim($paramvalue); // In case of...
                    if ($param->type == 'date') {
                        $this->params[$sqlkey]->originalvalue = $paramvalue;
                        $paramvalue = strtotime($paramvalue);
                    }
                    $this->params[$sqlkey]->value = $paramvalue;
                    if ($paramvalue) {
                        if ($param->ashaving) {
                            $havingparamsqlarr[] = " {$sqlkey} = '{$paramvalue}' ";
                        } else {
                            $paramsqlarr[] = " {$sqlkey} = '{$paramvalue}' ";
                        }
                        // Collects for making a urlquerystring.
                        $paramsurlvalues[$key] = $paramvalue;
                    }
                    break;

                case ('text'):
                    $paramvalue = optional_param($key, '', PARAM_TEXT);
                    $this->params[$sqlkey]->value = $paramvalue;
                    if ($paramvalue) {
                        if ($param->ashaving) {
                            $havingparamsqlarr[] = " {$sqlkey} LIKE '{$paramvalue}' ";
                        } else {
                            $paramsqlarr[] = " {$sqlkey} LIKE '{$paramvalue}' ";
                        }
                        $paramsurlvalues[$key] = $paramvalue;
                    }
                    break;

                case ('range'):
                case ('daterange'):
                    $valuefrom = optional_param($key.'_from', '', PARAM_TEXT);
                    $valueto = optional_param($key.'_to', '', PARAM_TEXT);
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
                        if ($param->ashaving) {
                            $havingparamsqlarr[] = ' ('.implode(' AND ', $sqlarr).') ';
                        } else {
                            $paramsqlarr[] = ' ('.implode(' AND ', $sqlarr).') ';
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
            $filterinputs[$key] = $filtersinputsource[$key];
        }

        foreach ($globalfilterkeys as $key) {
            $radical = str_replace('filter0_', '', $key);
            $cond = array_key_exists($radical, $this->filterfields->translations);
            $canonicalfilter = ($cond) ? $this->filterfields->translations[$radical] : $radical;
            if ($this->is_filter_global($canonicalfilter)) {
                $filterinputs[$key] = $filtersinputsource[$key];
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

        // Process defaults if setup, faking $filtersinputsource input.
        if (!empty($this->filterfields->defaults)) {
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
>>>>>>> MOODLE_33_STABLE

}