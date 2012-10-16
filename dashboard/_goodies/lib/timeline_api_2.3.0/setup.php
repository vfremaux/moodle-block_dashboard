<?php
include '../../../../../config.php';
header("Content-Type:text/javascript\n\n");
?>

	Timeline_ajax_url = "<?php echo $CFG->wwwroot ?>/blocks/dashboard/_goodies/lib/timeline_api_2.3.0/timeline_ajax/simile-ajax-api.js";
	Timeline_urlPrefix = "<?php echo $CFG->wwwroot ?>/blocks/dashboard/_goodies/lib/timeline_api_2.3.0/timeline_js/";       
	Timeline_parameters = "bundle=true&defaultLocale=fr";
