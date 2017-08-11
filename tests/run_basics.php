<?php

define('CLI_SCRIPT', true);

require('../../../config.php');

require_once($CFG->dirroot.'/blocks/dashboard/tests/lib_test.php');
require_once($CFG->dirroot.'/blocks/dashboard/lib.php');

$test = new block_dashboard_lib_testcase();
$test->test_rotate();