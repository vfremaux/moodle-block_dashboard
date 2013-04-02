<?php

/**
 * 
 * @package block-dashboard
 * @category blocks
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @version Moodle 2.x
 */

/**
* A low level utility to format data in a cell
*
*/
function dashboard_format_data($format, $data, $cumulativeix = null){
	global $DASHBOARD_ACCUMULATORS;

	// cumulates curve	
	if (!empty($cumulativeix)){
		if (!isset($DASHBOARD_ACCUMULATORS)) $DASHBOARD_ACCUMULATORS = array();
		if (!array_key_exists($cumulativeix, $DASHBOARD_ACCUMULATORS)){
			$DASHBOARD_ACCUMULATORS[$cumulativeix] = $data;
		} else {
			$data = $data + $DASHBOARD_ACCUMULATORS[$cumulativeix];
			$DASHBOARD_ACCUMULATORS[$cumulativeix] = $data;
		}
	}

	$negativeenhance = false;

	if (!empty($format)){
		
		// these two special formats are for use for SQL outputs
		if ($format == 'NUMERIC'){
			return $data;
		}

		if ($format == 'TEXT'){
			return "'$data'";
		}
		
		if (preg_match('/^-/', $format)){
			$negativeenhance = true;
			$format = strstr($format, 1);
		}
		
		$data = sprintf($format, $data);

		if ($negativeenhance && $data < 0){
			$data = '<span style="color:red;font-weight:bold">'.$data.'</span>';
		}
	}
	
	return $data;
}

/**
* An HTML raster for a matrix cross table
* printing raster uses a recursive cell drilldown over dynamic matrix dimension
*/
function print_cross_table(&$theBlock, &$m, &$hcols, $hkey, &$vkeys, $hlabel, $return = false){

	$str = '';

	dashboard_print_table_header($str, $hcols, $vkeys, $hlabel, @$theBlock->config->horizsums);
	
	// print flipped array
	$path = array();

	$subsums = new StdClass;
	$subsums->subs = array();
	$subsums->all = array();

	table_explore_rec($theBlock, $str, $path, $hcols, $m, $vkeys, $hlabel, count($vkeys->formats), $subsums);

    if (!empty($theBlock->config->vertsums)){
		// if vertsums are enabled, print vertsubs
		$str .= '<tr>';
		$span = count($vkeys) + 1;
		$subtotalstr = get_string('subtotal', 'block_dashboard');
		$str .= "<td colspan=\"{$span}\">$subtotalstr</td>";
		foreach($hcols as $col){
			$str.= "<td class=\"coltotal\">{$subsums->subs[$col]}</td>";
		}
		if (!empty($theBlock->config->horizsums)){
			$str .= '<td></td>';
		}
		$str .= '</tr>';

		// print big total
		$str .= '<tr>';
		$span = count($vkeys) + 1;
		$subtotalstr = get_string('total', 'block_dashboard');
		$str .= "<td colspan=\"{$span}\">$subtotalstr</td>";
		foreach($hcols as $col){
			$str.= "<td class=\"coltotal\"><b>{$subsums->all[$col]}</b></td>";
		}
		if (!empty($theBlock->config->horizsums)){
			$str .= '<td></td>';
		}
		$str .= '</tr>';
	}
	
	$str .= '</table>';	
	
	if ($return) return $str;
	echo $str;
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
	
	foreach($t as $k => $v){
		$plittable = false;
		array_push($pathstack, $k);
		$level++;
		if ($level < $keydeepness){
			table_explore_rec($theBlock, $str, $pathstack, $hcols, $v, $vkeys, $hlabel, $keydeepness, $subsums);
		} else {
			$pre = "<tr class=\"row r{$r}\" >";
			$r = ($r + 1) % 2;
			$c = 0;
			foreach($pathstack as $pathelm){
				if (!empty($vformats[$c])){
					$pathelm = dashboard_format_data($vformats[$c], $pathelm);
				}
				if (!empty($theBlock->config->cleandisplay)){
					if ($pathelm != @$vkeys->mem[$c]){
						$pre .= "<td class=\"vkey c{$c}\">$pathelm</td>";
						if (isset($vkeys->mem[$c]) && @$theBlock->config->spliton == $vcolumns[$c]){
							// first split do not play
							
							// if vertsums are enabled, print vertsubs
							if ($theBlock->config->vertsums){
								$str .= '<tr>';
								$span = count($pathstack);
								$subtotalstr = get_string('subtotal', 'block_dashboard');
								$str .= "<td colspan=\"{$span}\" >$subtotalstr</td>";
								foreach($hcols as $col){
									$str.= "<td class=\"coltotal\">{$subsums->subs[$col]}</td>";
									$subsums->subs[$col] = 0;
								}
								if ($theBlock->config->horizsums){
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
			foreach($hcols as $col){
				if (array_key_exists($col, $v)){
					$str .= "<td class=\"data c{$c}\">{$v[$col]}</td>";
				} else {
					$str .= "<td class=\"data empty c{$c}\"></td>";
				}
				$sum += strip_tags(@$v[$col]);
				if (@$theBlock->config->vertsums){
					$subsums->subs[$col] = @$subsums->subs[$col] + strip_tags(@$v[$col]);
					$subsums->all[$col] = @$subsums->all[$col] + strip_tags(@$v[$col]);
				}
				$c++;
			}
			
			if (@$theBlock->config->horizsums){
				$str .= "<td class=\"data rowtotal c{$c}\">$sum</td>";
			}
			
			$str .= "</tr>";
		}
		$level--;
		array_pop($pathstack);
	}
}

function dashboard_print_table_header(&$str, &$hcols, &$vkeys, $hlabel, $horizsums = false){

	$str .= '<table width="100%" class="dashboard-table"><tr>';
	
	$vkc = 0;
	foreach($vkeys->labels as $vk){
		$vkc++;
	}
	$str .= '<td colspan="'.$vkc.'"></td>';
	$str .= '<td class="dashboard-horiz-serie" colspan="'.count($hcols).'">'.$hlabel.'</td>';

	$str .= '</tr>';
	$str .= '<tr>';

	$vlabels = array_values($vkeys->labels);

	foreach($vkeys->labels as $vk => $vlabel){
		$str .= '<td class="dashboard-vertical-series">'.$vlabel.'</td>';
	}

	foreach($hcols as $hc){
		$str .= "<td class=\"hkey\">$hc</td>";
	}
	
	if ($horizsums){
		$totalstr = get_string('total', 'block_dashboard');
		$str .= "<td class=\"hkeytotal\">$totalstr</td>";
	}
	
	// close title line
	$str .= '</tr>';
}

/**
* An HTML raster for a matrix cross table
* printing raster uses a recursive cell drilldown over dynamic matrix dimension
*/
function print_cross_table_csv(&$theBlock, &$m, &$hcols, $hkey, &$vkeys, $hlabel, $return = false){

	$str = '';

	dashboard_print_table_header_csv($str, $hcols, $vkeys, $hlabel, @$theBlock->config->horizsums);
	
	// print flipped array
	$path = array();

	$subsums = new StdClass;
	$subsums->subs = array();
	$subsums->all = array();

	table_explore_rec_csv($theBlock, $str, $path, $hcols, $m, $vkeys, $hlabel, count($vkeys->formats), $subsums);
	
	if ($return) return $str;
	echo $str;
}

/**
* Recursive worker for CSV table writing
*
*/
function table_explore_rec_csv(&$theBlock, &$str, &$pathstack, &$hcols, &$t, &$vkeys, $hlabel, $keydeepness, &$subsums = null){
	static $level = 0;
	static $r = 0;
	
	$vformats = array_values($vkeys->formats);
	$vcolumns = array_keys($vkeys->formats);
	
	foreach($t as $k => $v){
		$plittable = false;
		array_push($pathstack, $k);
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
						$pre .= "<td class=\"vkey c{$c}\">$pathelm</td>";
						if (isset($vkeys->mem[$c]) && @$theBlock->config->spliton == $vcolumns[$c]){
							// first split do not play
							
							// if vertsums are enabled, print vertsubs
							if ($theBlock->config->vertsums){
								$str .= '<tr>';
								$span = count($pathstack);
								$subtotalstr = get_string('subtotal', 'block_dashboard');
								$str .= "<td colspan=\"{$span}\" >$subtotalstr</td>";
								foreach($hcols as $col){
									$str.= "<td class=\"coltotal\">{$subsums->subs[$col]}</td>";
									$subsums->subs[$col] = 0;
								}
								if ($theBlock->config->horizsums){
									$str .= '<td></td>';
								}
								$str .= '</tr>';
							}
							
							// then close previous table
							$str .= '</table>';
							dashboard_print_table_header_csv($str, $hcols, $vkeys, $hlabel, $theBlock->config->horizsums);
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
			foreach($hcols as $col){
				if (array_key_exists($col, $v)){
					$str .= "<td class=\"data c{$c}\">{$v[$col]}</td>";
				} else {
					$str .= "<td class=\"data empty c{$c}\"></td>";
				}
				$sum += strip_tags(@$v[$col]);
				if (@$theBlock->config->vertsums){
					$subsums->subs[$col] = @$subsums->subs[$col] + strip_tags(@$v[$col]);
					$subsums->all[$col] = @$subsums->all[$col] + strip_tags(@$v[$col]);
				}
				$c++;
			}
			
			if (@$theBlock->config->horizsums){
				$str .= "<td class=\"data rowtotal c{$c}\">$sum</td>";
			}
			
			$str .= "</tr>";
		}
		$level--;
		array_pop($pathstack);
	}
}

/**
* prints the first line as column titles
*
*
*/
function dashboard_print_table_header_csv(&$str, &$hcols, &$vkeys, $hlabel, $horizsums = false){
	global $CFG;
		
	$vlabels = array_values($vkeys->labels);

	$row = array();
	foreach($vkeys->labels as $vk => $vlabel){
		$row[] = $vlabel;
	}

	foreach($hcols as $hc){
		$row[] = $hc;
	}
	
	if ($horizsums){
		$row[] = get_string('total', 'block_dashboard');
	}
	
	echo utf8_decode(implode($CFG->dashboard_csv_field_separator, $row)); 
	echo $CFG->dashboard_csv_line_separator;
}

/**
* prints a smart tree with data
* @param object $theBlock full block information
* @param struct $tree the tree organized representation of records
* @param array $treeoutput an array of pair column,format information for making the tree node name
* @param array $outputfields an array of fields for tree node value information
* @param array $outputformats formats for above
* @param array $colourcoding an array of colour coding rules issued from table scope colourcoding settings
*/
function dashboard_print_tree_view(&$theBlock, &$tree, &$treeoutput, &$outputfields, &$outputformats, &$colorcoding, $return = false){
	static $level = 1;
	
	$str = '';
	
	asort($tree);
	
	$str .= '<ul class="dashboard-tree'.$level.'">';	
	$level++;
	foreach($tree as $key => $node){
		$nodestrs = array();
		foreach($treeoutput as $field => $formatter){
			if (empty($field)) continue;
			if (!empty($formatter)){
				$datum = dashboard_format_data($formatter, $node->$field);
			} else {
				$datum = $node->$field;
			}
			if (!empty($theBlock->config->colorfield) && $theBlock->config->colorfield == $field){
				// we probably prefer inline coloouring here, rather than div block.
				$datum = dashboard_colour_code($theBlock, $datum, $colorcoding, true);
			}
			$nodestrs[] = $datum;
		}
		$nodecontent = implode(' ', $nodestrs);
		$nodedata = array();
		foreach($outputformats as $field => $formatter){
			if (empty($field)) continue;
			if (!empty($formatter)){
				$datum = dashboard_format_data($formatter, $node->$field);
			} else {
				$datum = $node->$field;
			}
			if (!empty($theBlock->config->colorfield) && $theBlock->config->colorfield == $field){
				// we probably prefer inline coloouring here, rather than div block.
				$datum = dashboard_colour_code($theBlock, $datum, $colorcoding, true);
			}
			$nodedata[] = $datum;
		}
		$nodedatastr = implode(' ', $nodedata);
		$str .= "<li>$nodecontent <div style=\"float:right\">$nodedatastr</div></li>";
		if (!empty($node->childs)){
			$str .= dashboard_print_tree_view($theBlock, $node->childs, $treeoutput, $outputfields, $outputformats, $colorcoding, true);
		}
	}
	$level--;
	$str .= '</ul>';	
	
	if ($return) return $str;
	echo $str;
}

/**
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
?>