<?php

	include '../../../config.php';
	require_once($CFG->dirroot.'/lib/filelib.php');
	require_once($CFG->dirroot.'/blocks/dashboard/lib/file_browser.php');
	require_once($CFG->dirroot.'/repository/lib.php');

	$courseid = required_param('id', PARAM_INT);
	$instanceid = required_param('instance', PARAM_INT);
	$browsepath = optional_param('path', '/', PARAM_TEXT);
	$action = optional_param('what', '', PARAM_TEXT);

	if (!$course = $DB->get_record('course', array('id' => "$courseid"))){
		print_error('badcourseid');
	}
	
	require_login($course);
	
	if (!$instance = $DB->get_record('block_instances', array('id' => "$instanceid"))){
	    print_error('badblockinstance', 'block_dashboard');
	}
	
	$theBlock = block_instance('dashboard', $instance);
	
	$context = context_block::instance($instanceid);

	$fs = get_file_storage();

	if ($action == 'clear'){
		$fs->delete_area_files($context->id, 'block_dashboard', 'generated', $instanceid);
	}

	$PAGE->navbar->add(get_string('dashboards', 'block_dashboard'), NULL);
	if (!empty($theBlock->config->title)){
		$PAGE->navbar->add($theBlock->config->title, NULL);
	}

	$url = $CFG->wwwroot.'/blocks/dashboard/export/filearea.php?id='.$courseid.'&instance='.$instanceid;
	$PAGE->set_context($context);
	$PAGE->set_url($url);
	$PAGE->set_title($SITE->shortname);
	$PAGE->set_heading($SITE->shortname);

	echo $OUTPUT->header();

	echo $OUTPUT->heading(get_string('dashboardstoragearea', 'block_dashboard'));
	
	$browser = new dashboard_file_browser();
	
	$filearea = null;
	$itemid   = null;
	$filename = null;
	$path = array();
	$files = array();
	$dirs = array();
	if ($fileinfo = $browser->get_file_info($context, 'block_dashboard', 'generated', $instanceid, $browsepath, null)) {
	    // build a Breadcrumb trail
	    $level = $fileinfo->get_parent();
	    while ($level) {
	        $upper = $level->get_parent();
	        $path[] = ($upper) ? $level->get_visible_name() : '..' ;
	        $level = $upper;
	    }
	    $path = array_reverse($path);
	    $children = $fileinfo->get_children();
	    foreach ($children as $child) {
	        if ($child->is_directory()) {
	            // echo $child->get_visible_name();
	            // display contextid, itemid, component, filepath and filename
	            $dirs[] = $child;
	            // var_dump($child->get_params());
	        } else {
	            $files[] = $child;
	        }
	    }
	        
		echo '<div class="filebrowser">';

		echo '<div class="path">';
		$breadcrumb = '';
		if (!empty($path)){
			$fullpath = '/';
			foreach($path as $pel){
				$fullpath .= $pel.'/';
				$breadcrumb .= ' &gt; <a href="'.$url.'&path='.urlencode($fullpath).'">'.$pel.'</a>';
			}
			echo $breadcrumb;
		} else {
			echo '/';
		}
		echo '</div>';

		echo '<div class="block-dashboard-entrylist">';	
		echo '<table with="80%">';
		
		foreach($dirs as $dir){
			$dirinfo = $dir->get_params();
			echo '<tr>';
			echo '<td>';
			echo '<img src="'.$OUTPUT->pix_url('f/folder').'">';
			echo '</td>';
			echo '<td>';
				echo '<a href="'.$url.'&path='.$dirinfo['filepath'].'">'.$dir->get_visible_name().'</a>';
			echo '</td>';
			echo '</tr>';
		}		

		foreach($files as $file){
			$info = $file->get_params();				
			echo '<tr>';
			echo '<td>';
			echo '<img src="'.$OUTPUT->pix_url(file_mimetype_icon($file->get_mimetype())).'">';
			echo '</td>';
			echo '<td>';
			$pluginfileurl = moodle_url::make_pluginfile_url($info['contextid'], $info['component'], $info['filearea'], $info['itemid'], $info['filepath'], $info['filename']);
			echo '<a href="'.$pluginfileurl.'">'.$file->get_visible_name().'</a>';
			echo '</td>';
			echo '</tr>';
		}		

		echo '</table>';
		echo '</div>';

		echo '</div>';
	} else {
		echo "<div class=\"block-dashboard-entrylist\">No files</div>";
	}
	
	if (($browsepath != '/') || $fileinfo){
		echo $OUTPUT->single_button($url.'&what=clear', get_string('cleararea', 'block_dashboard'));
	}
	echo $OUTPUT->single_button($CFG->wwwroot.'/course/view.php?id='.$courseid, get_string('backtocourse', 'block_dashboard'));

	echo $OUTPUT->footer();
