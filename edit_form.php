<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Form for editing HTML block instances.
 *
 * @package   block_dashboard
 * @category  blocks
 * @copyright 2012 Valery Fremaux
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/dashboard/lib.php');

/**
 * Form for editing dashboard block instances.
 *
 */
class block_dashboard_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $CFG, $COURSE, $OUTPUT;

        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_dashboard'));
        $mform->setType('config_title', PARAM_MULTILANG);
        $mform->setDefault('config_title', get_string('newdashboard', 'block_dashboard'));

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $this->block->context);
        $mform->addElement('editor', 'config_description', get_string('configdescription', 'block_dashboard'), null, $editoroptions);
        $mform->setType('config_description', PARAM_CLEANHTML);

        $mform->addElement('checkbox', 'config_hidetitle', '', get_string('checktohide', 'block_dashboard'));

        // Layout settings ---------------------------------------------------------------.

        $mform->addElement('header', 'configheader1', get_string('dashboardlayout', 'block_dashboard'));

        $layoutopts[0] = get_string('publishinpage', 'block_dashboard');
        $layoutopts[1] = get_string('publishinblock', 'block_dashboard');
        $mform->addElement('select', 'config_inblocklayout', get_string('configlayout', 'block_dashboard'), $layoutopts);

        $mform->addElement('header', 'configheader20', get_string('configdashboardparams', 'block_dashboard'));
        $generalparamsconfigstr = get_string('generalparams', 'block_dashboard');
        $params = array('id' => $COURSE->id, 'instance' => $this->block->instance->id);
        $generalparmsurl = new moodle_url('/blocks/dashboard/setup.php', $params);
        $generalparamslink = '<a href="'.$generalparmsurl.'">'.$generalparamsconfigstr.'</a>';

        $mform->addElement('static', '', '', $generalparamslink);
        $mform->setExpanded('configheader20');

        if (block_dashboard_supports_feature('config/importexport')) {
            $mform->addElement('header', 'configheader19', get_string('configimportexport', 'block_dashboard'));
            $importconfigstr = get_string('importconfig', 'block_dashboard');
            $exportconfigstr = get_string('exportconfig', 'block_dashboard');
            $params = array('id' => $COURSE->id, 'instance' => $this->block->instance->id, 'what' => 'upload');
            $copyconfigurl = new moodle_url('/blocks/dashboard/pro/copyconfig.php', $params);
            $import_export = '<a href="'.$copyconfigurl.'">'.$importconfigstr.'</a> - ';
            $params = array('id' => $COURSE->id, 'instance' => $this->block->instance->id, 'what' => 'get');
            $exportconfigurl = new moodle_url('/blocks/dashboard/pro/copyconfig.php', $params);
            $import_export .= '<a href="'.$exportconfigurl.'" target="_blank">'.$exportconfigstr.'</a>';

            $mform->addElement('static', '', '', $import_export);
        }

        $mform->addElement('header', 'configheader195', get_string('configcharset', 'block_dashboard'));
        $charsetopt['utf8'] = 'Utf-8';
        $charsetopt['iso'] = 'ISO 8859-1';
        $mform->addElement('select', 'config_exportcharset', get_string('configexportcharset', 'block_dashboard'), $charsetopt);

        if (debugging()) {
            $mform->addElement('header', 'configheader20', get_string('configdashboardcron', 'block_dashboard'));

            $mform->addElement('text', 'config_lastcron', get_string('configlastcron', 'block_dashboard'));
            $mform->setType('config_lastcron', PARAM_INT);

            $mform->addElement('checkbox', 'config_isrunning', get_string('configisrunning', 'block_dashboard'));
        }
    }

    public function set_data($defaults) {

        if (!empty($this->block->config)) {
            if (!empty($this->block->config->sqlparams)) {
                foreach ($this->block->config->sqlparams as $paramid => $paramdef) {
                    $varkey = "config_sqlparams[$paramid][sqlparamvar]";
                    $defaults->$varkey = $paramdef['sqlparamvar'];
                }
            }

            if (!empty($this->block->config->description)) {
                $text = $this->block->config->description;
                $draftid_editor = file_get_submitted_draft_itemid('config_description');
                if (empty($text)) {
                    $currenttext = '';
                } else {
                    $currenttext = $text;
                }
                $defaults->config_description['text'] = file_prepare_draft_area($draftid_editor, $this->block->context->id,
                                                                                'block_dashboard', 'description', 0,
                                                                                array('subdirs' => true), $currenttext);
                $defaults->config_description['itemid'] = $draftid_editor;
                $defaults->config_description['format'] = $this->block->config->descriptionformat;

                // have to delete text here, otherwise parent::set_data will empty content
                // of editor
                unset($this->block->config->description);
            }
        }

        parent::set_data($defaults);
    }
}