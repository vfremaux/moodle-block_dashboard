<?php //$Id: block_dashboard.php,v 1.17 2011-11-02 23:03:48 vf Exp $

/**
 * 
 * @package block-dashboard
 * @category blocks
 * @author Valery Fremaux (valery@club-internet.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @version Moodle 1.9
 */


require_once $CFG->dirroot.'/blocks/dashboard/lib.php';
require_once $CFG->dirroot.'/blocks/dashboard/extradblib.php';
if (file_exists($CFG->libdir.'/jqplotlib.php')){
	$graphlibs = $CFG->libdir;
	$graphwww = '/lib';
} else {
	$graphlibs = '_goodies/lib';
	$graphwww = '/blocks/dashboard/_goodies/lib';
}
require_once $graphlibs.'/jqplotlib.php';
require_once $graphlibs.'/googleplotlib.php';
require_once $graphlibs.'/timelinelib.php';
include_once $CFG->libdir.'/tablelib.php';
require_jqplot_libs($graphwww);
timeline_require_js($graphwww);

class block_dashboard extends block_base {

	var $cachesignal = false; // marked by data fetch method if data comes from cache. 
	
	var $filterqueries = ''; // a temp buffer for writing filterquery 

    function init() {
        $this->title = get_string('blockname', 'block_dashboard');
        $this->version = 2012061500;
		$this->cron = 1;
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

    function instance_config_save($data, $pinned=false){
    	global $USER;
    	// check if curent user forcing a filelocationadminoverride can really do it
    	// in case it seems to be forced, set it to empty anyway.
		if (!has_capability('block/dashboard:systempathaccess', context_system::instance())){
			$data->filepathadminoverride = '';
		}
    	return parent::instance_config_save($data, $pinned);
    }

    function applicable_formats() {
        // Default case: the block can be used in all course types
        return array('all' => true,
                     'site' => true);
    }

    function get_content() {
        global $EXTRADBCONNECT, $CFG, $COURSE;
        @raise_memory_limit('512M');

        if ($this->content !== NULL) {
            return $this->content;
        }

		$this->content = new StdClass();
		if (@$this->config->inblocklayout){
	        $this->content->text = $this->print_dashboard();
	    } else {
	    	$viewdashboardstr = get_string('viewdashboard', 'block_dashboard');
	    	$this->content->text = "<a href=\"{$CFG->wwwroot}/blocks/dashboard/view.php?id={$COURSE->id}&amp;blockid={$this->instance->id}\">$viewdashboardstr</a>";
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
    	$text = '';
		$theBlock->config->limit = 20;

		$coursepage = '';
		if ($COURSE->format == 'page'){
			include_once($CFG->dirroot.'/course/format/page/lib.php');
			$pageid = optional_param('page', 0, PARAM_INT); // flexipage page number
			if (!$pageid){
				$flexpage = page_get_current_page();
			} else {
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

		// output from query
		$outputfields = explode(';', @$this->config->outputfields);
		$outputlabels = explode(';', @$this->config->fieldlabels);
		$outputformats = explode(';', @$this->config->outputformats);
		$this->normalize($outputfields, $outputlabels); // normalizes labels to keys
		$this->normalize($outputfields, $outputformats); // normalizes labels to keys
		$output = array_combine($outputfields, $outputlabels);
		$outputf = array_combine($outputfields, $outputformats);

		// filtering query
		$outputfilters = explode(';', @$this->config->filters);
		$outputfilterlabels = explode(';', @$this->config->filterlabels);
		$this->normalize($outputfilters, $outputfilterlabels); // normailzes labels to keys
		$this->filterfields->labels = array_combine($outputfilters, $outputfilterlabels);
		$outputfilterdefaults = explode(';', @$this->config->filterdefaults);
		$this->normalize($outputfilters, $outputfilterdefaults); // normailzes defaults to keys
		$this->filterfields->defaults = array_combine($outputfilters, $outputfilterdefaults);
		$outputfilteroptions = explode(';', @$this->config->filteroptions);
		$this->normalize($outputfilters, $outputfilteroptions); // normailzes options to keys
		$this->filterfields->options = array_combine($outputfilters, $outputfilteroptions);
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
		$horizkey = @$this->config->horizkey;
		$hlabel = @$this->config->horizlabel;
		$vkeys = explode(";", @$this->config->verticalkeys);
		$vformats = explode(";", @$this->config->verticalformats);
		$vlabels = explode(";", @$this->config->verticallabels);
		$this->normalize($vkeys, $vformats); // normalizes formats to keys
		$this->normalize($vkeys, $vlabels); // normalizes labels to keys
		$vertkeys->formats = array_combine($vkeys, $vformats);
		$vertkeys->labels = array_combine($vkeys, $vlabels);

		// treeview params
		$parentserie = @$this->config->parentserie;
		$treeoutputfields = explode(';', @$this->config->treeoutput);
		$treeoutputformats = explode(';', @$this->config->treeoutputformats);
		$this->normalize($treeoutputfields, $treeoutputformats); // normailzes labels to keys
		$treeoutput = array_combine($treeoutputfields, $treeoutputformats);
		// summators
		$numsums = explode(';', @$this->config->numsums);
		$numsumlabels = explode(';', @$this->config->numsumlabels);
		$numsumformats = explode(';', @$this->config->numsumformats);
		$this->normalize($numsums, $numsumlabels); // normailzes labels to keys
		$this->normalize($numsums, $numsumformats); // normailzes labels to keys
		$outputnumsums = array_combine($numsums, $numsumlabels);
		$numsumsf = array_combine($numsums, $numsumformats);
		// graph params
		$xaxisfield = $this->config->xaxisfield;
		$yseries = explode(';', @$this->config->yseries);
		$yseriesformats = explode(';', @$this->config->yseriesformats);
		$this->normalize($yseries, $yseriesformats); // normalizes labels to keys
		$yseriesf = array_combine($yseries, $yseriesformats);
		$graphdata = array();
		$ticks = array();
		// coloring params
		$colorcoding = array();
		if (!empty($this->config->colorfield)){
			$colors = explode("\n", @$this->config->colors);
			$colorvalues = explode("\n", @$this->config->coloredvalues);
			$this->normalize($colorvalues, $colors); // normailzes options to keys
			$colorcoding = array_combine($colorvalues, $colors);
		}
		// working with query
		$sql = $this->config->query;

		if (!empty($this->config->filters)){
			$filterclause = '';
			$filterkeys = preg_grep('/^filter'.$this->instance->id.'_/', array_keys($_GET));
			$globalfilterkeys = preg_grep('/^filter0_/', array_keys($_GET));
			$filtervalues = array();
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
					$default = (preg_match('/LAST|FIRST/i', $default)) ? $this->filter_get_results(str_replace('<%%FILTERS%%>', '', $sql), $filter, $canonicalfilter, $default) : $default ;
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
							if (count($value) > 1 || $value[0] != 0){
								$filters[] = " $sqlfiltername IN ('".implode("','", str_replace("'", "''", $value))."') ";
							}
						}
						$filtervalues[$radical] = $value;
					}
				}
			}

			if (!empty($filters)){
				if (!preg_match('/\bWHERE\b/si', $sql)){
					$filterclause = ' WHERE '.implode('AND', $filters);
				} else {
					$filterclause = ' AND '. implode('AND', $filters);
				}
			}
		    $filteredsql = str_replace('<%%FILTERS%%>', $filterclause, $sql);
		} else {
			$filteredsql = str_replace('<%%FILTERS%%>', '', $sql);
		}
		$sql = str_replace('<%%FILTERS%%>', '', $sql); // needed to prepare for filter range prefetch
		$sort = optional_param('tsort'.$this->instance->id, '', PARAM_TEXT);
		if (!empty($sort)){
			// do not sort if already sorted in explained query
			if (!preg_match('/ORDER\s+BY/si', $sql))
			    $filteredsql .= " ORDER BY $sort";
		}
		$filteredsql = $this->protect($filteredsql);
		// ######### GETTING RESULTS
		// counting records to fetch		
		$countsql = $this->get_count_records_sql($filteredsql);
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

		$results = $this->fetch_dashboard_data($filteredsql, @$this->config->pagesize, $offset);

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

			foreach($output as $field => $label){
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
			if (!empty($this->config->sortable)) $table->sortable(true, $xaxisfield, SORT_DESC); //sorted by xaxisfield by default
			$table->collapsible(true);
			$table->initialbars(true);

			$table->set_attribute('cellspacing', '0');
			$table->set_attribute('id', 'dashboard'.$this->instance->id);
			$table->set_attribute('class', 'dashboard');
			$table->set_attribute('width', '100%');
			foreach($output as $field => $label){
				$table->column_class($field, $field);
			}
			$table->setup();
			$where = $table->get_sql_where();
			$sort = $table->get_sql_sort();
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
					foreach($numsums as $numsum){
						if (empty($numsum)) continue;
						if (!isset($result->$numsum)) continue;
						// make subaggregates (only for linear tables and when sorting criteria is the split column)
						// post aggregate after table output
						$aggr->$numsum = 0 + (float)@$aggr->$numsum + (float)$result->$numsum;
						if (!empty($splitnumsonsort) && @$this->config->tabletype == 'linear' && (preg_match("/\\b$splitnumsonsort\\b/", $sort))){
							$subaggr[$orderkeyed]->$numsum = 0 + (float)@$subaggr[$orderkeyed]->$numsum + (float)$result->$numsum;
						}
					}
				}

				if (!empty($splitnumsonsort) && @$this->config->tabletype == 'linear' && (preg_match("/\\b$splitnumsonsort\\b/", $sort))){
					if ($orderkeyed != $oldorderkeyed){ // when range changes
						$k = 0;
						$tabledata = null;
						foreach($outputfields as $field){
							if (in_array($field, $numsums)){
								if (is_null($tabledata)){
									$tabledata = array();
									for ($j = 0 ; $j < $k ; $j++){
										$tabledata[$j] = '';
									}
								}
								$tabledata[$k] = '<b>Tot: '.$subaggr[$oldorderkeyed]->$field.'</b>';
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
						foreach($outputfields as $field){
							if (empty($field)) continue;
							// did we ask for cumulative results ? 
							$cumulativeix = null;
							if (preg_match('/S\((.+?)\)/', $field, $matches)){
								$field = $matches[1];
								$cumulativeix = $this->instance->id.'_'.$field;
							}
							if (!empty($outputf[$field])){
								$datum = dashboard_format_data($outputf[$field], $result->$field, $cumulativeix);
							} else {
								$datum = dashboard_format_data(null, @$result->$field, $cumulativeix);
							}
							// process coloring if required
							if (!empty($this->config->colorfield) && $this->config->colorfield == $field){
								$datum = dashboard_colour_code($this, $datum, $colorcoding);
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
						* - in first columns in order of vertical keys
						* the results are grabbed sequentially and spread into the matrix 
						*/
						$keystack = array();
						$matrix = array();
						foreach(array_keys($vertkeys->formats) as $vkey){
							if (empty($vkey)) continue;
							$vkeyvalue = $result->$vkey;
							$matrix[] = "['".addslashes($vkeyvalue)."']";
						}
						$hkey = $horizkey;
						$hkeyvalue = (!empty($hkey)) ? $result->$hkey :  '' ;
						$matrix[] = "['".addslashes($hkeyvalue)."']";
						$matrixst = "\$m".implode($matrix);
						if (!in_array($hkeyvalue, $hcols)) $hcols[] = $hkeyvalue;
						// now put the cell value in it
						$outvalues = array();
						foreach($outputfields as $field){

							// did we ask for cumulative results ? 
							$cumulativeix = null;
							if (preg_match('/S\((.+?)\)/', $field, $matches)){
								$field = $matches[1];
								$cumulativeix = $this->instance->id.'_'.$field;
							}

							if (!empty($outputf[$field])){
								$datum = dashboard_format_data($outputf[$field], $result->$field, $cumulativeix);
							} else {
								$datum = dashboard_format_data(null, @$result->$field, $cumulativeix);
							}
							if (!empty($this->config->colorfield) && $this->config->colorfield == $field){
								$datum = dashboard_colour_code($this, $datum, $colorcoding);
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
					if (!empty($xaxisfield)  && $this->config->graphtype != 'googlemap' && $this->config->graphtype != 'timeline'){
						if ($this->config->graphtype != 'pie'){
							// TODO : check if $xaxisfield exists really (misconfiguration) 
							$ticks[] = addslashes($result->$xaxisfield);
							$ys = 0;
							foreach($yseries as $yserie){
								if (!isset($result->$yserie)) continue;
								// did we ask for cumulative results ? 
								$cumulativeix = null;
								if (preg_match('/S\((.+?)\)/', $yserie, $matches)){
									$yserie = $matches[1];
									$cumulativeix = $this->instance->id.'_'.$yserie;
								}
								if ($this->config->graphtype != 'timegraph'){
									if (!empty($yseriesf[$yserie])){
										$graphseries[$yserie][] = dashboard_format_data($yseriesf[$yserie], $result->$yserie, $cumulativeix);
									} else {
										$graphseries[$yserie][] = dashboard_format_data(null, $result->$yserie, $cumulativeix);
									}
								} else {
									if (!empty($yseriesf[$yserie])){
										$timeelm = array($result->$xaxisfield, dashboard_format_data($yseriesf[$yserie], $result->$yserie, $cumulativeix)); 
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
								if (!empty($yseriesf[$field])){
									$graphseries[$yserie][] = array($result->$xaxisfield, dashboard_format_data($yseriesf[$field], $result->$yserie, false));
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
					foreach($outputfields as $field){
						if (in_array($field, $numsums)){
							if (is_null($tabledata)){
								$tabledata = array();
								for ($j = 0 ; $j < $k ; $j++){
									$tabledata[$j] = '';
								}
							}
							$tabledata[$k] = '<b>Tot: '.$subaggr[$orderkeyed]->$field.'</b>';
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

			if (!empty($debug)) print_object($treedata);

			if (@$this->config->inblocklayout){												
				$url = $CFG->wwwroot.'/course/view.php?id='.$COURSE->id.$coursepage.'&tsort'.$this->instance->id.'='.$sort;
			} else {
				$url = $CFG->wwwroot.'/blocks/dashboard/view.php?id='.$COURSE->id.'&blocksid='.$this->instance->id.$coursepage.'&tsort'.$this->instance->id.'='.$sort;
			}
			if (!empty($this->config->filters)){
				$text .= '<form class="dashboard-filters" name="dashboardform'.$this->instance->id.'" method="GET">';
				$text .= '<input type="hidden" name="id" value="'.s($COURSE->id).'" />';
				if (!@$this->config->inblocklayout){
					$text .= '<input type="hidden" name="blockid" value="'.s($this->instance->id).'" />';
				}
				if (!empty($coursepage)){
					$text .= '<input type="hidden" name="page" value="'.$flexpage->id.'" />';
				}
				if ($sort == 'id DESC') $sort = '';
				$text .= '<input type="hidden" name="tsort'.$this->instance->id.'" value="'.$sort.'" />';
				$text .= $this->dashboard_print_filters($filtervalues, $sql);
				$text .= '</form>';
			}

			if ($this->config->showdata){
				$allexportstr = get_string('exportall', 'block_dashboard');
				$tableexportstr = get_string('exportdataastable', 'block_dashboard');
				$filteredexportstr = get_string('exportfiltered', 'block_dashboard');
				$filterquerystring = (!empty($filterquerystring)) ? '&'.$filterquerystring : '' ;
				if (empty($this->config->tabletype) || @$this->config->tabletype == 'linear'){
					ob_start();
					$table->print_html();
					$text .= ob_get_clean();
					// $pageexportstr = get_string('pageexport', 'block_dashboard');
					// $this->content->text .= "<div style=\"text-align:right\"><a href=\"{$CFG->wwwroot}/blocks/dashboard/export/export_csv.php?id={$COURSE->id}&instance={$this->instance->id}&tsort{$this->instance->id}={$sort}{$filterquerystring}\">$pageexportstr</a> ";
					$text .= "<div style=\"text-align:right\">";
					$text .= "<a href=\"{$CFG->wwwroot}/blocks/dashboard/export/export_csv.php?id={$COURSE->id}&amp;instance={$this->instance->id}&amp;tsort{$this->instance->id}={$sort}&amp;alldata=1\">$allexportstr</a>";
					if ($filterquerystring){
						$text .= " - <a href=\"{$CFG->wwwroot}/blocks/dashboard/export/export_csv.php?id={$COURSE->id}&instance={$this->instance->id}&tsort{$this->instance->id}={$sort}{$filterquerystring}\">$filteredexportstr</a>";
					}
					$text .= "</div>";
				} elseif (@$this->config->tabletype == 'tabular') {
					// forget table and use $m matrix for making display
					$text .= print_cross_table($this, $m, $hcols, $horizkey, $vertkeys, $hlabel, true);					
					$text .= "<div style=\"text-align:right\"><a href=\"{$CFG->wwwroot}/blocks/dashboard/export/export_csv.php?id={$COURSE->id}&amp;instance={$this->instance->id}&amp;tsort{$this->instance->id}={$sort}&amp;alldata=1\">$allexportstr</a></div>";
					$text .= "<div style=\"text-align:right\"><a href=\"{$CFG->wwwroot}/blocks/dashboard/export/export_csv_tabular.php?id={$COURSE->id}&instance={$this->instance->id}&tsort{$this->instance->id}={$sort}{$filterquerystring}\">$tableexportstr</a></div>";
				} else {
					// treeview
					$text .= dashboard_print_tree_view($this, $treedata, $treeoutput, $output, $outputf, $colorcoding, true);					
					$text .= "<div style=\"text-align:right\"><a href=\"{$CFG->wwwroot}/blocks/dashboard/export/export_csv.php?id={$COURSE->id}&amp;instance={$this->instance->id}&amp;tsort{$this->instance->id}={$sort}&amp;alldata=1\">$allexportstr</a></div>";
				}
			} else {
				$text .= '';
			}
		}
		// showing graph
		if ($this->config->showgraph && !empty($this->config->graphtype)){
			$text .= $OUTPUT->box_start('dashboard-graph-box');
			$graphdesc = $this->dashboard_graph_properties();
			if ($this->config->graphtype != 'googlemap' && $this->config->graphtype != 'timeline'){
				$data = $graphdata;
				$graphdesc = $this->dashboard_graph_properties();
				$data = $graphdata;
				$text .= jqplot_print_graph('dashboard'.$this->instance->id, $graphdesc, $data, $this->config->graphwidth, $this->config->graphheight, '', true, $ticks);
			} elseif ($this->config->graphtype == 'googlemap') {
				if (!empty($this->config->datalocations)){
					// data comes from query and locating information from datalocations field mapping
					$googlelocs = explode(";", $this->config->datalocations);
					if (!empty($data)){
						foreach($data as $d){
							$t = $d->{$this->config->datatitles};
							if (count($googlelocs) == 1){
								list($lat,$lng) = explode(',', $d->{$this->config->datalocations});
								$type = $d->{$this->config->datatypes};
								$gmdata[] = array('title' => $t, 'lat' => 0 + $lat, 'lng' => 0 + $lng, 'markerclass' => $type);
							} elseif (count($googlelocs) == 4) {
								// we expect an address,postcode,city,region field list. If some data is quoted, take it as "constant"
								$addresselms = explode(';', $this->config->datalocations);
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
									$type = $d->{$this->config->datatypes};
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
				$text .= googlemaps_embed_graph('dashboard'.$this->instance->id, @$this->config->lat, @$this->config->lng, $this->config->graphwidth, $this->config->graphheight, $graphdesc, $gmdata, true);
				if (!empty($googleerrors)){
					// print_object($googleerrors);
				}
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
			$text .= $OUTPUT->box_start('dashboard-sumative-box');
			foreach($numsums as $numsum){
				if (!empty($numsumsf[$numsum])){
					$formattedsum = dashboard_format_data($numsumsf[$numsum], @$aggr->$numsum);
				} else {
					$formattedsum = 0 + @$aggr->$numsum;
				}
				$text .= $outputnumsums[$numsum].' : <b>'.$formattedsum.'</b>&nbsp;&nbsp;&nbsp;&nbsp;';
			}
			$text .= $OUTPUT->box_end();
		}

		// showing query
		if (@$this->config->showquery){
			$text .= '<div class="dashboard-query-box" style="padding:1px;border:1px solid #808080;margin:2px;font-size;0.75em;font-family:monospace">';
			$text .= '<pre>'.$filteredsql.'</pre>';
			$text .= '</div>';
		}
		
		$text .= $this->filterqueries;

		if ($this->cachesignal) $text = "<div class=\"dashboard-special\">Cache</div>\n".$text;
		return $text;
    }
    /**
    * get value range, print and sets up data filters
    *
    */
    function dashboard_print_filters($filtervalues, $sql){

    	$str = '';
    	$alllabels = array_keys($this->filterfields->labels);
    	$javascripthandler = '';
    	if (count($alllabels) <= 1){
    		$javascripthandler = 'document.forms[\'dashboardform'.$this->instance->id.'\'].submit();';
    	}
    	foreach($alllabels as $afield){
    		if (empty($afield)) continue; // protects against empty filterset

			$fieldname = (isset($this->filterfields->translations[$afield])) ? $this->filterfields->translations[$afield] : $afield ;

			$filterresults = $this->filter_get_results($sql, $afield, $fieldname);   		

			if ($filterresults){
				$filterset = array();
				if (!$this->is_filter_single($afield)) $filterset['0'] = '*';
				foreach(array_values($filterresults) as $value){
					$radical = preg_replace('/^.*\./', '', $fieldname); // removes table scope explicitators
					$filterset[$value->$radical] = $value->$radical;
				}				
				$str .= '<span class="dashboard-filter">'.$this->filterfields->labels[$afield].':</span>';
				$multiple = (strstr($this->filterfields->options[$afield], 'm') === false) ? false : true ; 
				$arrayform = ($multiple) ? '[]' : '' ;

				if (!is_array(@$filtervalues[$radical])){
					$unslashedvalue = stripslashes(@$filtervalues[$radical]);
				} else {
					$unslashedvalue = $filtervalues[$radical];
				}

				$parms = array();
				if ($multiple){
					$parms['multiple'] = 'multiple';
					$parms['size'] = $multiple * 5;
				}				
				$parms['onchange'] = $javascripthandler;

				if ($this->is_filter_global($afield)){
					$str .= html_writer::select($filterset, "filter0_{$radical}{$arrayform}", $unslashedvalue, '', $parms);
				} else {
					$str .= html_writer::select($filterset, "filter{$this->instance->id}_{$radical}{$arrayform}", $unslashedvalue, '', $parms);
				}
				$str .= "&nbsp;&nbsp;";
			}
    	}
    	if (count($alllabels) > 1){
    		$strdofilter = get_string('dofilter', 'block_dashboard');
    		$javascripthandler = 'document.forms[\'dashboardform'.$this->instance->id.'\'].submit();';
    		$str .= "&nbsp;&nbsp;<input type=\"button\" onclick=\"$javascripthandler\" value=\"$strdofilter\" />";
    	}
    	return $str;
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
    * utility to pad two distinct size arrays
    */
    function normalize(&$arr1, &$arr2){
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
    /**
    *
    *
    */
    function get_count_records_sql($sql){
    	$sql = "SELECT COUNT(*) FROM ($sql) as fullrecs ";
		// $sql = preg_replace('/^\s*SELECT(.*?)\sFROM\s/si', 'SELECT COUNT(*) FROM', $sql);
		// $sql = preg_replace('/\s*ORDER BY.*/si', '', $sql); // remove any ordering
		return $sql;
    }
    /**
    * provides constraint valuss from filters 
    *
    */
    function filter_get_results($sql, $fielddef, $fieldname, $specialvalue = ''){
    	static $FILTERSETS;
		global $CFG, $DB;

		// filter values caching
		if (isset($FILTERSETS) && array_key_exists($fielddef, $FILTERSETS) && empty($specialvalue)){
			return $FILTERSETS[$fielddef];
		}

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
    	$filtersql = $this->protect($filtersql);
    	if (!empty($this->config->showfilterqueries)){
    		$this->filterqueries = "<div class=\"dashboard-filter-query\" style=\"padding:1px;border:1px solid #808080;margin:2px;font-size;0.75em;font-family:monospace\"><b>FILTER :</b> $filtersql</div>";
    	}
		if ($this->config->target == 'moodle'){
			$FILTERSET[$fielddef] = $DB->get_records_sql($filtersql);
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
		if (is_array($FILTERSET[$fielddef])){
			switch ($specialvalue) {
				case 'LAST' :
					$result = end(array_values($FILTERSET[$fielddef]))->$fieldname;
					return (!empty($FILTERSET[$fielddef])) ? $result : false ;
				case 'FIRST' :
					$result = reset(array_values($FILTERSET[$fielddef]))->$fieldname ;
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
	        $DB->delete_records('block_dashboard_datacache', array('querykey' => $sqlkey, 'access' => $this->config->target));
	        list($usec, $sec) = explode(" ", microtime());
    		$t1 = (float)$usec + (float)$sec;
			if ($this->config->target == 'moodle'){

				// get all results for cache
				$allresults = array();
				$rss = $DB->get_records_sql($sql);
		        foreach($rss as $rec){
		        	$recarr = array_values((array)$rec);
					$allresults[$recarr[0]] = $rec;
					if(!empty($this->config->uselocalcaching)){
						$cacherec = new StdClass;
						$cacherec->access = $this->config->target;
						$cacherec->querykey = $sqlkey;
			            $cacherec->recordid = $recarr[0]; // get first column in result as key
			            $cacherec->record = base64_encode(serialize($rec));
			            $DB->insert_record('block_dashboard_datacache', $cacherec);
			        }
		        }

				if ($limit){
					$rss = $DB->get_records_sql($sql, null, $offset, $limit);
			        foreach($rss as $rs){
			        	$recarr = array_values((array)$rec);
						$results[$recarr[0]] = $rec;
			        }
			    } else {
			    	$results = $allresults;
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
				            $DB->insert_record('block_dashboard_datacache', str_replace("'", "''", $cacherec));
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
	    		$this->cachesignal = true;
	    		// we are caching and have a key
		        list($usec, $sec) = explode(' ', microtime());
	    		$t1 = (float)$usec + (float)$sec;
	    		$rss = $DB->get_records('block_dashboard_datacache', array('querykey' => $sqlkey), 'id', '*', $offset, $limit);
		        foreach($rss as $rec){
		            $results[$rec->recordid] = unserialize(base64_decode($rec->record));
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
    		if($alldashboards = $DB->get_records('block_instance', array('blockid' => $block->id))){
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
		    					$DB->set_field('block_instance', 'configdata', base64_encode(serialize($config)), array('id' => $dsh->id)); // Save config
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
									$dshobj->normalize($outputfields, $outputformats); // normalizes labels to keys
									$outputf = array_combine($outputfields, $outputformats);
		    						mtrace('   ... generating file for instance '.$dsh->id.' in format '.$config->fileformat);
		    						if (!empty($outputf)){
							    		$FILE = fopen($outputfile, 'wb');

										if ($config->fileformat == 'CSV'){
											// print col names
							    			$rarr = array();
							    			foreach($outputf as $key => $format){
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
								    			foreach($outputf as $key => $format){
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
							    			foreach($outputf as $key => $format){
							    				$colnames[] = $key;
							    			}

								    		foreach($results as $result){
								    			$values = array();
								    			foreach($outputf as $key => $format){
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
		    					$DB->set_field('block_instance', 'configdata', base64_encode(serialize($config)), array('id' => $dsh->id)); // Save config
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
    /**
    * determines if filter is global
    * a global filter will be catched by all dashboard instances in the same page
    */
    function is_filter_global($filterkey){
		return strstr($this->filterfields->options[$filterkey], 'g') !== false ;
    }

    /**
    * determines if filter is single
    * a single filter can only be constraint by a single value
    */
	function is_filter_single($filterkey){
		return strstr($this->filterfields->options[$filterkey], 's') !== false ;
	}

    /**
    * determines if filter must desaggregate from original query
    */
	function allow_filter_desaggregate($filterkey){
		return strstr($this->filterfields->options[$filterkey], 'x') === false ;
	}

    /**
    *
    */
    function user_can_addto($page) {
        global $CFG, $COURSE;

        $context = context_course::instance($COURSE->id);
        if (has_capability('block/dashboard:addtocourse', $context)){
        	return true;
        }
        return false;
    }

    /**
    *
    */
    function user_can_edit() {
        global $CFG, $COURSE;

        $context = context_course::instance($COURSE->id);
        if (has_capability('block/dashboard:configure', $context)){
 	       return true;
        }

		return false;
    }
}

?>