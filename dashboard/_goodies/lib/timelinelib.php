<?php

/**
* an integration wrapper for timeline of SIMILE project
*
*/

function timeline_require_js($libroot){
	global $CFG, $PAGE;
	static $timelineloaded = false;

	if ($timelineloaded) return;
	
	$PAGE->requires->js($libroot.'/timeline_api_2.3.0/setup.php', true);
	$PAGE->requires->js($libroot.'/timeline_api_2.3.0/timeline_js/timeline-api.js', true);
	$timelineloaded = true;
}

function timeline_initialize($return = false){
	global $TIMELINEGRAPHS;

	$str = '';
	$strtmp = '';

	if(!empty($TIMELINEGRAPHS)){
		foreach($TIMELINEGRAPHS as $tgraph){
			$strtmp .= "timeline_initialize_$tgraph();\n";
		}
	}

	$str .= "<script type=\"text/javascript\">
		function timeline_initialize_all(){
			{$strtmp}
	    }
	    
	    // document.body.onload = timeline_initialize_all;
	    timeline_initialize_all();
	</script>\n";
	
	if ($return) return $str;
	echo $str;
}

function timeline_print_graph(&$theBlock, $htmlid, $width, $height, &$data, $return = false){
	global $TIMELINEGRAPHS, $CFG, $COURSE, $USER;

	if (!isset($TIMELINEGRAPHS)) $TIMELINEGRAPHS = array();
	
	// generate data on a tmp file
	timeline_XML_generate($theBlock, $htmlid, $data);

	$str = "<div id=\"timeline_{$htmlid}\" style=\"height:{$height}px; width:{$width}pxborder: 1px solid #aaa\"></div>\n";

	$genID = rand(1000, 100000);
	
	$str .= "<script type=\"text/javascript\">
		var tl;
		function timeline_initialize_{$htmlid}() {
		
		   // create bands
   		   var eventSource = new Timeline.DefaultEventSource();
		   var bandInfos = [
		     Timeline.createBandInfo({
		         eventSource:    eventSource, 
		         width:          \"70%\", 
		         intervalUnit:   Timeline.DateTime.{$theBlock->config->upperbandunit}, 
		         intervalPixels: 100
		     })
	";
	if ($theBlock->config->showlowerband){
		$str .= ",
		     Timeline.createBandInfo({
		         eventSource:    eventSource, 
		         width:          \"30%\", 
		         intervalUnit:   Timeline.DateTime.{$theBlock->config->lowerbandunit}, 
		         intervalPixels: 200
		     }) 
		 ";
	}
	$str .= "
		   ];
	";
	if ($theBlock->config->showlowerband){
		$str .= "
				bandInfos[1].syncWith = 0;
	   			bandInfos[1].highlight = true;
	   	";
	}

   	$str .= "
		    tl = Timeline.create(document.getElementById(\"timeline_{$htmlid}\"), bandInfos);
		    Timeline.loadXML(\"{$CFG->wwwroot}/file.php/{$COURSE->id}/blockdata/dashboard/timelineevents/{$htmlid}_{$USER->id}.xml?uniqueid={$genID}\", function(xml, url) { eventSource.loadXML(xml, url); });
		 }
		
		 var resizeTimerID = null;
		 function onResize() {
		     if (resizeTimerID == null) {
		         resizeTimerID = window.setTimeout(function() {
		             resizeTimerID = null;
		             tl.layout();
		         }, 500);
		     }
		 }
		</script>
	";

	$TIMELINEGRAPHS[] = $htmlid;
	
	if ($return) return $str;
	echo $str;
}

/**
* Generates a temp file that can be loaded into a timeline
* date formats : Standard UTF timestamps. Ex: May 28 2006 09:00:00 GMT
* Postgre to_char pattern : 'Mon DD YYYY HH24:MI:SS GMT'
* Mysql formatting using date_format :  '%b %d %Y %H:%i:%s GMT'
*
*/
function timeline_XML_generate(&$theBlock, $htmlid, &$data){
	global $CFG, $COURSE, $USER;
	
	if (!is_dir($CFG->dataroot.'/'.$COURSE->id.'/blockdata')){
		mkdir($CFG->dataroot.'/'.$COURSE->id.'/blockdata', 0777);
	}	
	
	if (!is_dir($CFG->dataroot.'/'.$COURSE->id.'/blockdata/dashboard')){
		mkdir($CFG->dataroot.'/'.$COURSE->id.'/blockdata/dashboard', 0777);
	}	
	
	if (!is_dir($CFG->dataroot.'/'.$COURSE->id.'/blockdata/dashboard/timelineevents')){
		mkdir($CFG->dataroot.'/'.$COURSE->id.'/blockdata/dashboard/timelineevents', 0777);
	}
	
	$tmpfile = $CFG->dataroot.'/'.$COURSE->id.'/blockdata/dashboard/timelineevents/'.$htmlid.'_'.$USER->id.'.xml';

	$tmp = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n\n";
	$tmp .= "<data>\n"; 
	$colors = preg_split("/\r?\n/",$theBlock->config->timelinecolors);
	$colorkeys = preg_split("/\r?\n/",$theBlock->config->timelinecolorkeys);
	$theBlock->normalize($colorkeys, $colors);
	$colouring = array_combine($colorkeys, $colors);
	if (!function_exists('mytrim')){
		function mytrim(&$data){
			$data = trim($data);
		}
	}
	array_walk($colorkeys, 'mytrim');
	
	$basefileurl = $CFG->wwwroot.'/file.php/'.$COURSE->id.'/blockdata/dashboard/all/';
	$basefilepath = $CFG->dataroot.'/'.$COURSE->id.'/blockdata/dashboard/all/';
	foreach($data as $d){
		$eventattrs = array();
		if (empty($d)) continue;		
		if (!empty($theBlock->config->timelineeventstart) && !empty($d->{$theBlock->config->timelineeventstart})) $eventattrs[] = "start=\"".timeline_date_convert($d->{$theBlock->config->timelineeventstart}, $theBlock)."\"";
		if (!empty($theBlock->config->timelineeventend) && !empty($d->{$theBlock->config->timelineeventend}) && $d->{$theBlock->config->timelineeventend} != "1 Jan 1970 01:00:00 GMT") $eventattrs[] = "end=\"".timeline_date_convert($d->{$theBlock->config->timelineeventend}, $theBlock)."\"";
		if (!empty($theBlock->config->timelineeventend) && !empty($d->{$theBlock->config->timelineeventend}) && $d->{$theBlock->config->timelineeventend} != "1 Jan 1970 01:00:00 GMT") $eventattrs[] = "isDuration=\"true\"";
		if (!empty($theBlock->config->timelineeventtitle) && !empty($d->{$theBlock->config->timelineeventtitle})) $eventattrs[] = "title=\"".str_replace('&', '&amp;', $d->{$theBlock->config->timelineeventtitle})."\"";
		if (!empty($theBlock->config->timelineeventlink) && !empty($d->{$theBlock->config->timelineeventlink})) $eventattrs[] = "link=\"".$d->{$theBlock->config->timelineeventlink}."\"";
		if (!empty($theBlock->config->timelinecolorfield) && !empty($d->{$theBlock->config->timelinecolorfield})) {
			if (array_key_exists($d->{$theBlock->config->timelinecolorfield}, $colouring)){
				$eventattrs[] = "color=\"".$colouring[$d->{$theBlock->config->timelinecolorfield}]."\"";
				if (file_exists($basefilepath.$d->{$theBlock->config->timelinecolorfield}.".png")){
					$eventattrs[] = "icon=\"".$basefileurl.$d->{$theBlock->config->timelinecolorfield}.".png\"";
				}
			}
			$eventattrs[] = "textColor=\"#505050\"";
		}
		if (!empty($theBlock->config->timelineeventdesc)) {
			// $tmp .= '<event '.implode(' ', $eventattrs)." >".htmlentities(@$d->{$theBlock->config->timelineeventdesc}, ENT_QUOTE, 'UTF-8')."</event>\n";
			$tmp .= '<event '.implode(' ', $eventattrs)." >".str_replace('&', '&amp;', $d->{$theBlock->config->timelineeventtitle})."</event>\n";
		}
	}
	$tmp .= "</data>\n";
	
	$FILE = fopen($tmpfile, 'w');
	fputs($FILE, $tmp);
	fclose($FILE);
/*
<data>
    <event 
        start="May 28 2006 09:00:00 GMT"
        end="Jun 15 2006 09:00:00 GMT"
        isDuration="true"
        title="Writing Timeline documentation"
        image="http://simile.mit.edu/images/csail-logo.gif"
        >
        A few days to write some documentation for <a href="http://simile.mit.edu/timeline/">Timeline</a>.
        </event>
        
    <event 
        start="Jun 16 2006 00:00:00 GMT"
        end="Jun 26 2006 00:00:00 GMT"
        title="Friend's wedding"
        >
        I'm not sure precisely when my friend's wedding is.
        </event>
        
    <event 
        start="Aug 02 2006 00:00:00 GMT"
        title="Trip to Beijing"
        link="http://travel.yahoo.com/"
        >
        Woohoo!
        </event>
 </data>
 */
}

function timeline_date_convert($date, $theBlock){

	// this might be for further needs
	if ($theBlock->config->target != 'moodle'){
		// we have an extra PostGre db, usually date are given in Postgre format
	}
	
	return $date;
}