<?php

if (!defined('MOODLE_INTERNAL')) {
    die ('Sorry, but you cannot use this script this way');
}

if (array_key_exists('submit', $_POST)){
    $data = $_POST;

    unset($data['submit']);

    $theBlock->config = (object) $data;
    $theBlock->instance_config_save($theBlock->config);

    redirect(new moodle_url('/course/view.php', array('id' => $COURSE->id)));
}