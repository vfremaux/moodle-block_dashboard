<?php

/**
 * 
 * @package block-dashboard
 * @category blocks
 * @author Valery Fremaux (valery@club-internet.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @version Moodle 1.9
 */
ob_start();
include '../../config.php';

$id = required_param('id', PARAM_INT);
$instanceid = required_param('instance', PARAM_INT);
$action = optional_param('what', '', PARAM_TEXT);

if (!$course = get_record('course', 'id', "$id")){
	error("Bad course ID");
}

require_login($course);

if (!$instance = get_record('block_instance', 'id', "$instanceid")){
    print_error('badblockinstance', 'block_dashboard');
}

$theBlock = block_instance('dashboard', $instance);


switch($action){
	case 'get' :
	    ob_end_clean();
		header("Content-Type:text/raw\n\n");
		echo $instance->configdata;
		die;
	break;
	case 'upload' :
	print_header_simple($SITE->fullname, $SITE->fullname, build_navigation(array()));
	
	print_heading(get_string('configcopy', 'block_dashboard'));
	
	echo '<center><form name="setconfigform" action="#" method="post">';
	echo '<input type="hidden" name="what" value="" />';
	echo get_string('dropconfig', 'block_dashboard').':<br/>';
	echo '<textarea name="configdata" cols="30" rows="5"></textarea><br/>';
	echo '<input type="button" name="go_set" value="'.get_string('update').'" onclick="document.forms[\'setconfigform\'].what.value = \'set\';document.forms[\'setconfigform\'].submit();" />';
	echo '<input type="button" name="cancel_set" value="'.get_string('cancel').'" onclick="document.forms[\'setconfigform\'].what.value = \'cancel\';document.forms[\'setconfigform\'].submit();" />';
	echo '</form></center>';
	print_footer($course);	
	break;
	case 'set' :
		$configdata = optional_param('configdata', '', PARAM_TEXT);
		set_field('block_instance', 'configdata', $configdata, 'id', "$instanceid");
		redirect($CFG->wwwroot."/course/view.php?id={$id}&instanceid={$instanceid}&blockaction=config&sesskey=".sesskey());
	break;
	default:
		redirect($CFG->wwwroot."/course/view.php?id={$id}&instanceid={$instanceid}&blockaction=config&sesskey=".sesskey());
}

?>