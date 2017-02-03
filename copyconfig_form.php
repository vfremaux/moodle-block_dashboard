<?php

/**
 * @package block-dashboard
 * @category blocks
 * @author Valery Fremaux (valery@club-internet.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @version Moodle 2.2
*/

require_once $CFG->libdir.'/formslib.php';

class Copyconfig_Form extends moodleform {

    function __construct($action) {
        parent::__construct($action);
    }

    function definition() {

        $mform = $this->_form;

        $mform->addElement('textarea', 'configdata', get_string('dropconfig', 'block_dashboard'), array('rows' => 5, 'cols' => 100));

        $this->add_action_buttons();
    }
}