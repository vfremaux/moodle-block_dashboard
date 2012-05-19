<?php

/**
*
*
*/
function require_jqplot_libs(){
	global $CFG;
	
	require_js($CFG->wwwroot.'/lib/jqplot/jquery-1.4.4.min.js');
	require_js($CFG->wwwroot.'/lib/jqplot/jquery.jqplot.js');
	require_js($CFG->wwwroot.'/lib/jqplot/excanvas.js');
	require_js($CFG->wwwroot.'/lib/jqplot/plugins/jqplot.dateAxisRenderer.js');
	require_js($CFG->wwwroot.'/lib/jqplot/plugins/jqplot.barRenderer.min.js');
	require_js($CFG->wwwroot.'/lib/jqplot/plugins/jqplot.highlighter.min.js');
	require_js($CFG->wwwroot.'/lib/jqplot/plugins/jqplot.canvasOverlay.min.js');
	require_js($CFG->wwwroot.'/lib/jqplot/plugins/jqplot.cursor.min.js');
	require_js($CFG->wwwroot.'/lib/jqplot/plugins/jqplot.categoryAxisRenderer.min.js');
	require_js($CFG->wwwroot.'/lib/jqplot/plugins/jqplot.pointLabels.min.js');
	require_js($CFG->wwwroot.'/lib/jqplot/plugins/jqplot.logAxisRenderer.min.js');
	require_js($CFG->wwwroot.'/lib/jqplot/plugins/jqplot.canvasTextRenderer.min.js');
	require_js($CFG->wwwroot.'/lib/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js');
	require_js($CFG->wwwroot.'/lib/jqplot/plugins/jqplot.canvasAxisLabelRenderer.min.js');
	require_js($CFG->wwwroot.'/lib/jqplot/plugins/jqplot.enhancedLegendRenderer.min.js');
	require_js($CFG->wwwroot.'/lib/jqplot/plugins/jqplot.pieRenderer.min.js');
	require_js($CFG->wwwroot.'/lib/jqplot/plugins/jqplot.donutRenderer.min.js');
}

/**
* prints any JQplot graph type given a php descriptor and dataset
*
*/
function jqplot_print_graph($htmlid, $graph, &$data, $width, $height, $addstyle = '', $return = false, $ticks = null){
	global $PLOTID;
	static $instance = 0;
	
	$htmlid = $htmlid.'_'.$instance;
	$instance++;

	$str = "<center><div id=\"$htmlid\" style=\"{$addstyle} width:{$width}px; height:{$height}px;\"></div></center>";
	$str .= "<script type=\"text/javascript\">\n";
	
	if (!is_null($ticks)){
		$ticksvalues = implode("','", $ticks);
		$str .= "var ticks = ['$ticksvalues']; \n";
	}

	$varsetlist = json_encode($data);
	$varsetlist = preg_replace('/"(\d+)\"/', "$1", $varsetlist);		
	$jsongraph = json_encode($graph);
	$jsongraph = preg_replace('/"\$\$\.(.*?)\"/', "$1", $jsongraph);
	$jsongraph = preg_replace('/"(\$\.jqplot.*?)\"/', "$1", $jsongraph);		

	$str .= "	
    $.jqplot.config.enablePlugins = true;

	plot{$PLOTID} = $.jqplot(
		'{$htmlid}', 
		$varsetlist, 
		{$jsongraph}
	);
 	";
	$str .= "</script>";
 	
 	$PLOTID++;
 	
 	if ($return) return $str;
 	echo $str;
}

/**
* TODO : unfinished
*
*/
function jqplot_print_vert_bar_attemptsgraph(&$data, $title, $htmlid){
	global $PLOTID;
	static $instance = 0;
	
	$htmlid = $htmlid.'_'.$instance;
	$instance++;

	echo "<div id=\"$htmlid\" style=\"margin-top:20px; margin-left:20px; width:700px; height:400px;\"></div>";
	echo "<script type=\"text/javascript\" language=\"javascript\">";
	echo "
		$.jqplot.config.enablePlugins = true;
	";
	
	$title = addslashes($title);

	$answeredarr = array($data->answered, $data->aanswered, $data->canswered);
	$matchedarr = array($data->matched, $data->amatched, $data->cmatched);
	$hitratioarr = array($data->hitratio * 100, $data->ahitratio * 100, $data->chitratio * 100);

	print_jqplot_barline('answered', $answeredarr);
	print_jqplot_barline('matched', $matchedarr);
	print_jqplot_barline('hitratio', $hitratioarr);
	echo "
		plot{$PLOTID} = $.jqplot(
			'$htmlid', 
			[$listattempts], 
			{ legend:{show:true, location:'ne'},
			title:'$title', 
			seriesDefaults:{ 
				renderer:$.jqplot.BarRenderer,
			  	rendererOptions:{barDirection:'vertical', barPadding: 6, barMargin:15}, 
			  	shadowAngle:135
			}, 
			series:[
			],   
			axesDefaults:{useSeriesColor: true},   
			axes:{ yaxis:{label:'Questions', min:0}, 
				   y2axis:{label:'Hit Ratio', min:0, max:100, tickOptions:{formatString:'%d\%'}}
			}
		});
	";

	echo "</script>";
	$PLOTID++;

}

/**
* 
*
*/
function jqplot_print_labelled_graph(&$data, $title, $htmlid){
	global $PLOTID;
	static $instance = 0;
	
	$htmlid = $htmlid.'_'.$instance;
	$instance++;

	echo "<center><div id=\"$htmlid\" style=\"margin-bottom:20px; margin-left:20px; width:480px; height:480px;\"></div></center>";
	echo "<script type=\"text/javascript\" language=\"javascript\">";
	echo "
		$.jqplot.config.enablePlugins = true;
	";
	
	$title = addslashes($title);
	$coveragestr = get_string('coverage', 'report_barchenamf3');
	$hitratiostr = get_string('hitratio', 'report_barchenamf3');
	
	print_jqplot_labelled_rawline($data, 'data_'.$htmlid);

	echo "
		plot{$PLOTID} = $.jqplot(
			'$htmlid', 
			[data_$htmlid], 
			{ 
			title:'$title', 
			seriesDefaults:{ 
				renderer:$.jqplot.LineRenderer,
			  	showLine:false,
			  	showMarker:true, 
			  	shadowAngle:135,
			  	markerOptions:{size:15, style:'circle'},
			  	shadowDepth:2
			}, 
			axes:{ xaxis:{label:'{$coveragestr}', min:0, max:100, numberTicks:11, tickOptions:{formatString:'%d\%'}}, 
				   yaxis:{label:'{$hitratiostr}', min:0, max:100, numberTicks:11, tickOptions:{formatString:'%d\%'}}
			},
			cursor:{zoom:true, showTooltip:false}
		});
	";

	echo "</script>";
	$PLOTID++;

}

/**
* 
*
*/
function jqplot_print_questionuse_graph(&$data, $title, $htmlid){
	global $PLOTID;
	static $instance = 0;
	
	$htmlid = $htmlid.'_'.$instance;
	$instance++;
	
	echo "<center><div id=\"$htmlid\" style=\"margin-bottom:20px; margin-left:20px; width:700px; height:500px;\"></div></center>";
	echo "<script type=\"text/javascript\" language=\"javascript\">";
	echo "
		$.jqplot.config.enablePlugins = true;
	";
	
	$title = addslashes($title);
	$usedstr = get_string('used', 'report_barchenamf3');
	$matchedstr = get_string('matched', 'report_barchenamf3');
	
	$maxscale = max($data[0][1]) + 100;
	
	print_jqplot_labelled_rawline($data[0], 'quse_'.$htmlid);
	print_jqplot_rawline($data[1], 'qmatched_'.$htmlid);
	print_jqplot_rawline($data[2], 'qhitratio_'.$htmlid);

	echo "
		plot{$PLOTID} = $.jqplot(
			'$htmlid', 
			[quse_$htmlid, qmatched_$htmlid, qhitratio_$htmlid], 
			{ 
			title:'$title', 
			seriesDefaults:{ 
				renderer:$.jqplot.LineRenderer,
			  	showLine:true,
			  	showMarker:false, 
			  	shadowAngle:135,
			  	shadowDepth:2,
			  	lineWidth:1
			}, 
			series:[
				{label:'Used'},
				{label:'Matched'},
				{label:'ErrorRatio', yaxis:'y2axis', lineWidth:1, color:'#FF0000'}
			],
			axes:{ xaxis:{autoscale:true, min:0, tickOptions:{formatString:'%d'}}, 
				   yaxis:{autoscale:true, min:0, max:{$maxscale}, tickOptions:{formatString:'%d'}},
				   y2axis:{min:0, max:100, tickOptions:{formatString:'%d\%'}}
			},
			cursor:{
      			showVerticalLine:true,
      			showHorizontalLine:false,
      			showCursorLegend:true,
      			showTooltip: false,
      			zoom:true
      		}
		});
	";

	echo "</script>";
	$PLOTID++;

}

/**
* 
*
*/
function jqplot_print_simple_bargraph(&$data, $title, $htmlid){
	global $PLOTID;
	static $instance = 0;
	
	$htmlid = $htmlid.'_'.$instance;
	$instance++;

	echo "<center><div id=\"$htmlid\" style=\"margin-bottom:20px; margin-left:20px; width:700px; height:480px;\"></div></center>";
	echo "<script type=\"text/javascript\" language=\"javascript\">";
	echo "
		$.jqplot.config.enablePlugins = true;
	";
	
	$title = addslashes($title);
	$errorstr = addslashes(get_string('errorrate', 'report_barchenamf3'));
	$numberstr = addslashes(get_string('quantity', 'report_barchenamf3'));
	
	print_jqplot_simplebarline('data_'.$htmlid, $data);

	echo "
	
		xticks = [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 80, 85, 90, 95, 100];
	
		plot{$PLOTID} = $.jqplot(
			'$htmlid', 
			[data_$htmlid], 
			{ 
			title:'$title', 
			seriesDefaults:{ 
				renderer:$.jqplot.BarRenderer,
        		rendererOptions:{barPadding: 6, barMargin:4}
        	}, 
			series:[
				{color:'#FF0000'}
			],
			axes:{ xaxis:{renderer:$.jqplot.CategoryAxisRenderer, label:'{$errorstr} (%)', ticks:xticks}, 
				   yaxis:{label:'{$numberstr}', autoscale:true}
			},
		});
	";

	echo "</script>";
	$PLOTID++;

}

/**
* 
*
*/
function jqplot_print_assiduity_bargraph(&$data, $ticks, $title, $htmlid){
	global $PLOTID;
	static $instance = 0;
	
	$htmlid = $htmlid.'_'.$instance;
	$instance++;
	
	$xticks = "'".implode("','", $ticks)."'";

	echo "<center><div id=\"$htmlid\" style=\"margin-bottom:20px; margin-left:20px; width:880px; height:320px;\"></div></center>";
	echo "<script type=\"text/javascript\" language=\"javascript\">";
	echo "
		$.jqplot.config.enablePlugins = true;
	";
	
	$title = addslashes($title);
	$qstr = addslashes(get_string('assiduity', 'report_barchenamf3'));
	$numberstr = addslashes(get_string('attempts', 'report_barchenamf3'));
	
	print_jqplot_simplebarline('data_'.$htmlid, $data);
	

	echo "
	
		xticks = [$xticks];
	
		plot{$PLOTID} = $.jqplot(
			'$htmlid', 
			[data_$htmlid], 
			{ 
			title:'$title', 
			seriesDefaults:{ 
				renderer:$.jqplot.BarRenderer,
        		rendererOptions:{barPadding: 6, barMargin:4}
        	}, 
			series:[
				{color:'#FF0000'}
			],
			highlighter: {
				show: false,
			},
			axes:{ 
				xaxis:{
					renderer:$.jqplot.CategoryAxisRenderer, 
					tickRenderer: $.jqplot.CanvasAxisTickRenderer,
	            	tickOptions:{angle: -45, fontSize: '8pt'},
					label:'{$qstr}',
					ticks:xticks
				}, 
				yaxis:{
					autoscale:true,
					label:'{$numberstr}'
				}
			},
		});
	";

	echo "</script>";
	$PLOTID++;

}

/**
* 
*
*/
function jqplot_print_modules_bargraph(&$data, $title, $htmlid){
	global $PLOTID;
	static $instance = 0;
	
	$htmlid = $htmlid.'_'.$instance;
	$instance++;
	
	// preformat data with empty values
	for($i = 1; $i <= 10; $i++){
		$data[$i*10] = 0 + @$data[$i*10];
	}
	ksort($data);

	$xticks = implode(',', array_keys($data));

	echo "<center><div id=\"$htmlid\" style=\"margin-bottom:20px; margin-left:20px; width:500px; height:320px;\"></div></center>";
	echo "<script type=\"text/javascript\" language=\"javascript\">";
	echo "
		$.jqplot.config.enablePlugins = true;
	";
	
	$title = addslashes($title);
	$qstr = addslashes(get_string('questions', 'report_barchenamf3'));
	$numberstr = addslashes(get_string('quantity', 'report_barchenamf3'));
	
	print_jqplot_simplebarline('data_'.$htmlid, $data);
	

	echo "
	
		xticks = [$xticks];
	
		plot{$PLOTID} = $.jqplot(
			'$htmlid', 
			[data_$htmlid], 
			{ 
			title:'$title', 
			seriesDefaults:{ 
				renderer:$.jqplot.BarRenderer,
        		rendererOptions:{barPadding: 6, barMargin:4}
        	}, 
			series:[
				{color:'#FF0000'}
			],
			highlighter: {
				show: false,
			},
			axes:{ xaxis:{renderer:$.jqplot.CategoryAxisRenderer, label:'{$qstr}', ticks:xticks}, 
				   yaxis:{label:'{$numberstr}', autoscale:true}
			},
		});
	";

	echo "</script>";
	$PLOTID++;

}
