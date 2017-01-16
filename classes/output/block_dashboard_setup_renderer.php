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
 * @package    block_dashboard
 * @category   blocks
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *  Exporter of dashboard data snapshot
 */

defined('MOODLE_INTERNAL') || die();

class block_dashboard_setup_renderer extends \plugin_renderer_base {

    protected $theblock;

    public function set_block($theblock) {
        $this->theblock = $theblock;
    }

    public function form_header() {

        $theblock = $this->theblock;

        $str = '';

        $str .= '<table class="dashboard-setup">';
        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em" >';
        $str .= '<b>'.get_string('configtitle', 'block_dashboard').':</b><br/>';
        $str .= '<b>'.get_string('confighidetitle', 'block_dashboard').':</b>';
        $str .= '</td>';
        $str .= '<td>';
        $theblocktitle = '';
        if (isset($theblock->config) && isset($theblock->config->title)) {
            $theblocktitle = $theblock->config->title;
        }
        $str .= '<input type="text" name="title" size="60" value="'.$theblocktitle.'" />';
        $hidetitle = '';
        if (isset($theblock->config) && isset($theblock->config->hidetitle)) {
            $hidetitle = ($theblock->config->hidetitle) ? ' checked="checked" ' : '';
        }
        $str .= '<br/><input type="checkbox" name="hidetitle" '.$hidetitle.' /> '.get_string('checktohide', 'block_dashboard');
        $str .=  '   </td>';
        $str .= '</tr>';
        $str .= '</table>';

        return $str;
    }

    public function layout() {

        $theblock = $this->theblock;

        $str = '';

        $str .= '<fieldset>';
        $str .= '<legend>'.get_string('dashboardlayout', 'block_dashboard').'</legend>';
        $str .= '<table class="dashboard-setup">';
        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em" >';
        $str .= '<b>'.get_string('configlayout', 'block_dashboard').':</b>';
        $str .= '</td>';
        $str .= '<td>';
        $yesnoopts = array('0' => get_string('no'), '1' => get_string('yes'));
        $str .= html_writer::select($yesnoopts, 'inblocklayout', $theblock->config->inblocklayout, '');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</fieldset>';

        return $str;
    }

    /**
     * Print tabs for setup screens.
     * @param object $theblock a dashboard block instance
     */
    public function setup_tabs() {

        $config = $this->theblock->config;

        $cond1 = (!empty($config->tabletype)) && ($config->tabletype == 'tabular');
        $cond2 = (!empty($config->graphtype)) && ($config->graphtype == 'treeview');
        $cond3 = (!empty($config->graphtype)) && ($config->graphtype == 'googlemap');
        $cond4 = (!empty($config->graphtype)) && ($config->graphtype == 'timeline');

        $tabs = array();
        $tabs[] = array('querydesc', get_string('querydesc', 'block_dashboard'), true);
        $tabs[] = array('queryparams', get_string('queryparams', 'block_dashboard'), true);
        $tabs[] = array('outputparams', get_string('outputparams', 'block_dashboard'), true);
        $tabs[] = array('tabularparams', get_string('tabularparams', 'block_dashboard'), ($cond1) ? true : false);
        $tabs[] = array('treeviewparams', get_string('treeviewparams', 'block_dashboard'), ($cond2) ? true : false);
        $tabs[] = array('graphparams', get_string('graphparams', 'block_dashboard'), true);
        $tabs[] = array('googleparams', get_string('googleparams', 'block_dashboard'), ($cond3) ? true : false);
        $tabs[] = array('timelineparams', get_string('timelineparams', 'block_dashboard'), ($cond4) ? true : false);
        $tabs[] = array('summatorsparams', get_string('summatorsparams', 'block_dashboard'), true);

        if (block_dashboard_supports_feature('result/colouring')) {
            $tabs[] = array('tablecolormapping', get_string('tablecolormapping', 'block_dashboard'), true);
        }

        if (block_dashboard_supports_feature('result/export')) {
            $tabs[] = array('datarefresh', get_string('datarefresh', 'block_dashboard'), true);
            $tabs[] = array('fileoutput', get_string('fileoutput', 'block_dashboard'), true);
        }

        $str = '<div id="dashboardsettings-menu" class="tabtree">';
        $str .= '<ul class="nav nav-tabs">';
        foreach ($tabs as $tabarr) {
            list($tabkey, $tabname, $visible) = $tabarr;
            $class = ($tabkey == 'querydesc') ? 'active ' : '';
            $class .= ($visible) ? 'on' : 'off';
            $tabname = str_replace(' ', '&nbsp;', $tabname);
            $link = '<a href="Javascript:open_panel(\''.$tabkey.'\')"><span>'.$tabname.'</span></a>';
            $str .= '<li id="setting-tab-'.$tabkey.'" class="setting-tab '.$class.'">'.$link.'</li>';
        }
        $str .= '</ul>';

        return $str;
    }

    public function query_description() {

        $theblock = $this->theblock;

        $str = '';

        $str .= '<fieldset id="dashboardsettings-panel-querydesc" class="dashboardsettings-panel on">';
        $str .= '<legend>'.get_string('querydesc', 'block_dashboard').'</legend>';
        $str .= '<table class="dashboard-setup">';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= '<b>'.get_string('configtarget', 'block_dashboard').':</b>';
        $str .= '</td>';
        $str .= '<td>';
        $targets = array('moodle' => 'Moodle', 'extra' => 'Extra DB');
        $str .= html_writer::select($targets, 'target', $theblock->config->target);
        $str .= '</td>';
        $str .= '<td align="right">';
        $str .= '<b>'.get_string('configdisplay', 'block_dashboard').':</b>';
        $str .= '</td>';
        $str .= '<td>';
        $str .= get_string('configshowdata', 'block_dashboard');
        $str .= '<input type="radio" name="showdata" value="1" '.(($theblock->config->showdata) ? 'checked="checked"' : '').' />';
        $str .= get_string('yes');
        $str .= '<input type="radio" name="showdata" value="0" '.((!$theblock->config->showdata) ? 'checked="checked"' : '').' />';
        $str .= get_string('no');

        $str .= '<br/>';

        $str .= get_string('configshowgraph', 'block_dashboard');
        $str .= '<input type="radio" name="showgraph" value="1" '.(($theblock->config->showgraph) ? 'checked="checked"' : '').' />';
        $str .= get_string('yes');
        $str .= '<input type="radio" name="showgraph" value="0" '.((!$theblock->config->showgraph) ? 'checked="checked"' : '').' />';
        $str .= get_string('no');

        $str .= '<br/>';

        $str .= get_string('configshownumsums', 'block_dashboard');
        $str .= '<input type="radio" name="shownumsums" value="1" '.(($theblock->config->shownumsums) ? 'checked="checked"' : '').' />';
        $str .= get_string('yes');
        $str .= '<input type="radio" name="shownumsums" value="0" '.((!$theblock->config->shownumsums) ? 'checked="checked"' : '').' />';
        $str .= get_string('no');

        $str .= '<br/>';

        $str .= get_string('configshowquery', 'block_dashboard');
        $str .= '<input type="radio" name="showquery" value="1" '.(($theblock->config->showquery) ? 'checked="checked"' : '').' />';
        $str .= get_string('yes');
        $str .= '<input type="radio" name="showquery" value="0" '.((!$theblock->config->showquery) ? 'checked="checked"' : '').' />';
        $str .= get_string('no');

        $str .= '<br/>';

        $str .= get_string('configshowfilterqueries', 'block_dashboard');
        $str .= '<input type="radio" name="showfilterqueries" value="1" '.(($theblock->config->showfilterqueries) ? 'checked="checked"' : '').' />';
        $str .= get_string('yes');
        $str .= '<input type="radio" name="showfilterqueries" value="0" '.((!$theblock->config->showfilterqueries) ? 'checked="checked"' : '').' />';
        $str .= get_string('no');

        $str .= '<br/>';

        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $helpicon = $this->output->help_icon('configquery', 'block_dashboard');
        $str .= '<b>'.get_string('configquery', 'block_dashboard').': '.$helpicon.'</b>';
        $str .= '</td>';
        $str .= '<td align="center" colspan="3">';

        $theblocktitle = '';
        if (isset($theblock->config) && isset($theblock->config->query)) {
            $theblocktitle = $theblock->config->query;
        }
        $str .= '<textarea name="query" cols="80" rows="10" >'.$theblocktitle.'</textarea>';
        $str .= '</td>';
        $str .= '</tr>';

        if (block_dashboard_supports_feature('result/rotation')) {
            $str .= '<tr valign="top">';
            $str .= '<td align="left" colspan="4">';
            $helpicon = $this->output->help_icon('configqueryrotate', 'block_dashboard');
            $str .= '<b>'.get_string('configqueryrotate', 'block_dashboard').': '.$helpicon.'</b>';
            $str .= '</td>';
            $str .= '</tr>';
    
            $str .= '<tr valign="top">';
            $str .= '<td align="right">';
            $str .= '</td>';
            $str .= '<td align="center">';
            $str .= '<b>'.get_string('configrotatecolumns', 'block_dashboard').': </b><br/>';
            $str .= '<input text name="queryrotatecols" size="30" value="'.@$theblock->config->queryrotatecols.'" />';
            $str .= '</td>';
            $str .= '<td align="right">';
            $str .= '<b>'.get_string('configrotatepivot', 'block_dashboard').': </b><br/>';
            $str .= '<input text name="queryrotatepivot" size="30" value="'.@$theblock->config->queryrotatepivot.'" />';
            $str .= '</td>';
            $str .= '<td align="center">';
            $str .= '<b>'.get_string('configrotatenewkeys', 'block_dashboard').': </b><br/>';
            $str .= '<input text name="queryrotatenewkeys" size="30" value="'.@$theblock->config->queryrotatenewkeys.'" />';
            $str .= '</td>';
            $str .= '</tr>';
        }

        $str .= '</table>';
        $str .= '</fieldset>';

        return $str;
    }

    public function query_params() {

        $theblock = $this->theblock;

        $str = '';

        $str .= '<fieldset id="dashboardsettings-panel-queryparams" class="dashboardsettings-panel off">';
        $str .= '<legend>'.get_string('queryparams', 'block_dashboard').'</legend>';
        $str .= '<table class="dashboard-setup">';

        $str .= '<tr valign="top">';
        $str .= '<td align="center" colspan="4">';
        $str .= get_string('configparams', 'block_dashboard');
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" width="25%" style="line-height:1.5em">';
        $str .= '<b>'.get_string('sqlparamvar', 'block_dashboard').'</b>';
        $str .= '</td>';
        $str .= '<td align="right" width="25%" style="line-height:1.5em">';
        $str .= '<b>'.get_string('sqlparamlabel', 'block_dashboard').'</b>';
        $str .= '</td>';
        $str .= '<td align="right" width="25%" style="line-height:1.5em">';
        $str .= '<b>'.get_string('sqlparamtype', 'block_dashboard').'</b>';
        $str .= '</td>';
        $str .= '<td align="right" width="25%" style="line-height:1.5em">';
        $str .= '<b>'.get_string('sqlparamvalues', 'block_dashboard').'</b>';
        $str .= '</td>';
        $str .= '</tr>';

        for ($i = 1 ; $i < 5 ; $i ++) {
            $varkey = 'sqlparamvar'.$i;
            $labelkey = 'sqlparamlabel'.$i;
            $typekey = 'sqlparamtype'.$i;
            $valueskey = 'sqlparamvalues'.$i;
            $str .= '<tr valign="top">';
            $str .= '<td align="right" width="25%" style="line-height:1.5em">';
            $str .= '<input type="text" size="20" name="'.$varkey.'" value="'.@$theblock->config->$varkey.'" />';
            $str .= '</td>';
            $str .= '<td align="right" width="25%" style="line-height:1.5em">';
            $str .= '<input type="text" size="20" name="'.$labelkey.'" value="'.@$theblock->config->$labelkey.'" />';
            $str .= '</td>';
            $str .= '<td align="right" width="25%" style="line-height:1.5em">';
            $typeopts = array('choice' => get_string('choicevalue', 'block_dashboard'),
                              'text' => get_string('textvalue', 'block_dashboard'),
                              'select' => get_string('listvalue', 'block_dashboard'),
                              'range' => get_string('rangevalue', 'block_dashboard'),
                              'date' => get_string('datevalue', 'block_dashboard'),
                              'daterange' => get_string('daterangevalue', 'block_dashboard'),
                              );
            $str .= html_writer::select($typeopts, $typekey, @$theblock->config->$typekey, '');
            $str .= '</td>';
            $str .= '<td align="right" width="25%" style="line-height:1.5em">';
            $str .= '<textarea name="'.$valueskey.'" rows="5" cols="30">'.@$theblock->config->$valueskey.'</textarea>';
            $str .= '</td>';
            $str .= '</tr>';
        }
        $str .= '</table>';
        $str .= '</fieldset>';

        return $str;
    }

    public function output_params() {

        $theblock = $this->theblock;

        $str = '';

        $str .= '<div id="dashboardsettings-panel-outputparams" class="dashboardsettings-panel off">';
        $str .= '<fieldset>';
        $str .= '<legend>'.get_string('outputparams', 'block_dashboard').'</legend>';
        $str .= '<table class="dashboard-setup">';
        $str .= '<tr valign="top">';
        $str .= '<td align="center" colspan="4">';
        $str .= get_string('configreminderonsep', 'block_dashboard');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right" width="50%" style="line-height:1.5em" colspan="2">';
        $str .= '<table width="100%">';
        $str .= '<tr valign="top">';
        $helpicon = $this->output->help_icon('configoutputfields', 'block_dashboard');
        $str .= '<td width="50%" align="right"><b>'.get_string('configoutputfields', 'block_dashboard').': '.$helpicon.'</b> </td>';
        $str .= '<td width="50%" align="left">';
        $fields = '';
        if (isset($theblock->config) && isset($theblock->config->outputfields)) {
            $fields = $theblock->config->outputfields;
        }
        $str .= '<input type="text" name="outputfields" size="30" value="'.$fields.'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $helpicon = $this->output->help_icon('configformatting', 'block_dashboard');
        $str .= '<td align="right"><b>'.get_string('configoutputformats', 'block_dashboard').': '.$helpicon.'</b> </td>';
        $str .= '<td align="left">';
        $formats = '';
        if (isset($theblock->config) && isset($theblock->config->outputformats)) {
            $formats = htmlentities($theblock->config->outputformats, ENT_QUOTES, 'UTF-8');
        }
        $str .= '<input type="text" name="outputformats" size="30" value="'.$formats.'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $helpicon = $this->output->help_icon('configpagesize', 'block_dashboard');
        $str .= '<td align="right"><b>'.get_string('configpagesize', 'block_dashboard').': '.$helpicon.'</b></td>';
        $str .= '<td align="left">';
        $pagesize = '';
        if (isset($theblock->config) && isset($theblock->config->pagesize)) {
            $pagesize = $theblock->config->pagesize;
        }
        $bigresult = '';
        if (isset($theblock->config) && isset($theblock->config->bigresult)) {
            $bigresult = 'checked="checked"';
        }

        $str .= '<input type="text" name="pagesize" size="10" value="'.p($pagesize).'" />';
        $str .= ' <input type="checkbox" name="bigresult" value="1" '.$bigresult.' />'.get_string('bigresult', 'block_dashboard').' ';
        $str .= $this->output->help_icon('configbigresult', 'block_dashboard');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $helpicon = $this->output->help_icon('configcaching', 'block_dashboard');
        $str .= '<td align="right"><b>'.get_string('configcaching', 'block_dashboard').': '.$helpicon.'</b></td>';
        $str .= '<td align="left">';
        $uselocalcachingyeschecked = '';
        $uselocalcachingnochecked = ' checked=\"checked\" ';
        if (isset($theblock->config) && isset($theblock->config->uselocalcaching)) {
            $uselocalcachingyeschecked = ($theblock->config->uselocalcaching) ? ' checked=\"checked\" ' : '' ;
            $uselocalcachingnochecked = ($theblock->config->uselocalcaching) ? ' checked=\"checked\" ' : '' ;
        }
        $str .= '<input type="radio" name="uselocalcaching" value="0" '.$uselocalcachingnochecked.' /> '.get_string('no');
        $str .= ' - <input type="radio" name="uselocalcaching" value="1" '.$uselocalcachingyeschecked.' /> '.get_string('yes');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td align="right"><b>'.get_string('configcachingttl', 'block_dashboard').':</b></td>';
        $str .= '<td align="left">';
        $cachingttl = '';
        if (isset($theblock->config) && isset($theblock->config->cachingttl)) {
            $cachingttl = $theblock->config->cachingttl;
        }
        $str .= '<input type="text" name="cachingttl" size="10" value="'.$cachingttl.'" /> '.get_string('minutes');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td align="right"><b>'.get_string('configcleandisplay', 'block_dashboard').':</b></td>';
        $str .= '<td align="left">';
        $cleandisplayyes = 'checked="checked"';
        $cleandisplayno = '';
        if (isset($theblock->config) && isset($theblock->config->cleandisplay)) {
            if ($theblock->config->cleandisplay){
                $cleandisplayyes = 'checked="checked"';
                $cleandisplayno = '';
            } else {
                $cleandisplayyes = '';
                $cleandisplayno = 'checked="checked"';
            }
        }
        $str .= '<input type="radio" name="cleandisplay" value="1" '.$cleandisplayyes.' /> '.get_string('yes');
        $str .= ' <input type="radio" name="cleandisplay" value="0" '.$cleandisplayno.' /> '.get_string('no');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td align="right"><b>'.get_string('configsortable', 'block_dashboard').':</b></td>';
        $str .= '<td align="left">';
        $sortableyes = 'checked="checked"';
        $sortableno = '';
        if (isset($theblock->config) && isset($theblock->config->sortable)) {
            if ($theblock->config->sortable){
                $sortableyes = 'checked="checked"';
                $sortableno = '';
            } else {
                $sortableyes = '';
                $sortableno = 'checked="checked"';
            }
        }
        $str .= '<input type="radio" name="sortable" value="1" '.$sortableyes.' /> '.get_string('yes');
        $str .= ' <input type="radio" name="sortable" value="0" '.$sortableno.' /> '.get_string('no');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $helpicon = $this->output->help_icon('configsplitsumsonsort', 'block_dashboard');
        $str .= '<td align="right"><b>'.get_string('configsplitsumsonsort', 'block_dashboard').': '.$helpicon.'</b></td>';
        $str .= '<td align="left">';
        $splitsumsonsort = '';
        if (isset($theblock->config) && isset($theblock->config->splitsumsonsort)) {
            $splitsumsonsort = $theblock->config->splitsumsonsort;
        }
        $str .= '<input type="text" name="splitsumsonsort" value="'.$splitsumsonsort.'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</td>';
        $str .= '<td align="right" style="font-size:0.9em" colspan="2">';
        $str .= '<table width="100%">';
        $str .= '<tr valign="top">';
        $str .= '<td width="50%" align="right"><b>'.get_string('configoutputfieldslabels', 'block_dashboard').':</b></td>';
        $str .= '<td width="50%" align="left">';
        $fieldlabels = '';
        if (isset($theblock->config) && isset($theblock->config->fieldlabels)) {
            $fieldlabels = $theblock->config->fieldlabels;
        }
        $str .= '<textarea name="fieldlabels" cols="30" rows="5" >'.$fieldlabels.'</textarea>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $helpicon = $this->output->help_icon('configtabletype', 'block_dashboard');
        $str .= '<b>'.get_string('configtabletype', 'block_dashboard').': '.$helpicon.'</b>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $tabletype = '';
        if (isset($theblock->config) && isset($theblock->config->tabletype)) {
            $tabletype = $theblock->config->tabletype;
        }

        $tabletypeopts['linear'] = get_string('linear', 'block_dashboard');
        $tabletypeopts['tabular'] = get_string('tabular', 'block_dashboard');
        $tabletypeopts['treeview'] = get_string('treeview', 'block_dashboard');

        $str .= html_writer::select($tabletypeopts, 'tabletype', $tabletype, array(), array('onchange' => 'showmoreoptions(this)'));
        $str .= '</td>';
        $str .= '<td align="right">';
        $str .= '</td>';
        $str .= '<td>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</fieldset>';
        $str .= $this->output_filters();
        $str .= '<br/>';
        $str .= '</div>';

        return $str;
    }

    public function output_filters() {

        $theblock = $this->theblock;

        $str = '';

        $str .= '<fieldset>';
        $str .= '<legend>'.get_string('filters', 'block_dashboard').'</legend>';
        $str .= '<table>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $helpicon = $this->output->help_icon('configfilters', 'block_dashboard');
        $str .= '<b>'.get_string('configfilters', 'block_dashboard').': '.$helpicon.'</b>';
        $str .= '<br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $filters = '';
        if (isset($theblock->config) && isset($theblock->config->filters)) {
            $filters = $theblock->config->filters;
        }
        $str .= '<input type="text" name="filters" size="40" value="'.htmlentities($filters, ENT_QUOTES, 'UTF-8').'" />';
        $str .= '</td>';
        $str .= '<td align="right" rowspan="3">';
        $str .= '<b>'.get_string('configfilterlabels', 'block_dashboard').':</b>';
        $str .= '</td>';
        $str .= '<td rowspan="3">';
        $filterlabels = '';
        if (isset($theblock->config) && isset($theblock->config->filterlabels)) {
            $filterlabels = $theblock->config->filterlabels;
        }
        $str .= '<textarea name="filterlabels" cols="30" rows="5" >'.$filterlabels.'</textarea>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $helpicon = $this->output->help_icon('configfilterdefaults', 'block_dashboard');
        $str .= '<b>'.get_string('configfilterdefaults', 'block_dashboard').': '.$helpicon.'</b><br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $filterdefaults = '';
        if (isset($theblock->config) && isset($theblock->config->filterdefaults)) {
            $filterdefaults = $theblock->config->filterdefaults;
        }
        $str .= '<input type="text" name="filterdefaults" size="30" value="'.htmlentities($filterdefaults, ENT_QUOTES, 'UTF-8').'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $helpicon = $this->output->help_icon('configfilteroptions', 'block_dashboard');
        $str .= '<b>'.get_string('configfilteroptions', 'block_dashboard').': '.$helpicon.'</b>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $filteroptions = '';
        if (isset($theblock->config) && isset($theblock->config->filteroptions)) {
            $filteroptions = $theblock->config->filteroptions;
        }
        $str .= '<input type="text" name="filteroptions" size="30" value="'.htmlentities($filteroptions, ENT_QUOTES, 'UTF-8').'" />';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</fieldset>';

        return $str;
    }

    public function tabular_params() {

        $theblock = $this->theblock;

        $str = '';

        $str .= '<fieldset id="dashboardsettings-panel-tabularparams" class="dashboardsettings-panel off">';
        $str .= '<legend>'.get_string('tabularparams', 'block_dashboard').'</legend>';
        $str .= '<table class="dashboard-setup">';

        $str .= '<tr valign="top" height="16">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $helpicon = $this->output->help_icon('configtabular', 'block_dashboard');
        $str .= '<b>'.get_string('configverticalkeys', 'block_dashboard').': '.$helpicon.'</b><br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $verticalkeys = '';
        if (isset($theblock->config) && isset($theblock->config->verticalkeys)) {
            $verticalkeys = $theblock->config->verticalkeys;
        }
        $str .= '<input type="text" name="verticalkeys" size="20" value="'.$verticalkeys.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $helpicon = $this->output->help_icon('configtabular', 'block_dashboard');
        $str .= '<b>'.get_string('confighorizkey', 'block_dashboard').': '.$helpicon.'</b><br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $horizkey = '';
        if (isset($theblock->config) && isset($theblock->config->horizkey)) {
            $horizkey = $theblock->config->horizkey;
        }
        $str .= '<br/><input type="text" name="horizkey" size="20" value="'.$horizkey.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $helpicon = $this->output->help_icon('configformatting', 'block_dashboard');
        $str .= '<b>'.get_string('configverticalformats', 'block_dashboard').': '.$helpicon.'</b>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $verticalformats = '';
        if (isset($theblock->config) && isset($theblock->config->verticalformats)) {
            $verticalformats = htmlentities($theblock->config->verticalformats, ENT_NOQUOTES, 'UTF-8');
        }
        $str .= '<br/><input type="text" name="verticalformats" size="20" value="'.$verticalformats.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td style="line-height:1.5em">';
        $helpicon = $this->output->help_icon('configformatting', 'block_dashboard');
        $str .= '<b>'.get_string('confighorizformat', 'block_dashboard').': '.$helpicon.'</b>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $horizformat = '';
        if (isset($theblock->config) && isset($theblock->config->horizformat)) {
            $horizformat = htmlentities($theblock->config->horizformat, ENT_NOQUOTES, 'UTF-8');
        }
        $str .= '<br/><input type="text" name="horizformat" size="20" value="'.$horizformat.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td style="line-height:1.5em">';
        $helpicon = $this->output->help_icon('configtablesplit', 'block_dashboard');
        $str .= '<b>'.get_string('configspliton', 'block_dashboard').': '.$helpicon.'</b>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $spliton = '';
        if (isset($theblock->config) && isset($theblock->config->spliton)) {
            $spliton = $theblock->config->spliton;
        }
        $str .= '<input type="text" name="spliton" size="20" value="'.$spliton.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td class="conflabels">';
        $helpicon = $this->output->help_icon('configsums', 'block_dashboard');
        $str .= '<b>'.get_string('configenablehorizsums', 'block_dashboard').': '.$helpicon.' </b>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $horizsumsyeschecked = '';
        $horizsumsnochecked = ' checked=\"checked\" ';
        if (isset($theblock->config) && isset($theblock->config->horizsums)) {
            $horizsumsyeschecked = ($theblock->config->horizsums) ? ' checked=\"checked\" ' : '';
            $horizsumsnochecked = ($theblock->config->horizsums) ? ' checked=\"checked\" ' : '';
        }
        $str .= '<input type="radio" name="horizsums" value="0" '.$horizsumsnochecked.' /> '.get_string('no');
        $str .= ' - <input type="radio" name="horizsums" value="1" '.$horizsumsyeschecked.' /> '.get_string('yes');
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td style="line-height:1.5em">';
        $helpicon = $this->output->help_icon('configsums', 'block_dashboard');
        $str .= '<b>'.get_string('configenablevertsums', 'block_dashboard').': '.$helpicon.'</b>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $vertsumsyeschecked = '';
        $vertsumsnochecked = ' checked=\"checked\" ';
        if (isset($theblock->config) && isset($theblock->config->vertsums)) {
            $vertsumsyeschecked = ($theblock->config->vertsums) ? ' checked=\"checked\" ' : '' ;
            $vertsumsnochecked = ($theblock->config->vertsums) ? ' checked=\"checked\" ' : '' ;
        }
        $str .= '<input type="radio" name="vertsums" value="0" '.$vertsumsnochecked.' /> '.get_string('no');
        $str .= ' - <input type="radio" name="vertsums" value="1" '.$vertsumsyeschecked.' /> '.get_string('yes');
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td style="line-height:1.5em">';
        $str .= '<b>'.get_string('configverticallabels', 'block_dashboard').':</b>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $verticallabels = '';
        if (isset($theblock->config) && isset($theblock->config->verticallabels)) {
            $verticallabels = htmlentities($theblock->config->verticallabels, ENT_NOQUOTES, 'UTF-8');
        }
        $str .= '<input type="text" name="verticallabels" size="30" value="'.$verticallabels.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td style="line-height:1.5em">';
        $str .= '<b>'.get_string('confighorizlabel', 'block_dashboard').':</b>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $horizlabel = '';
        if (isset($theblock->config) && isset($theblock->config->horizlabel)) {
            $horizlabel = htmlentities($theblock->config->horizlabel, ENT_NOQUOTES, 'UTF-8');
        }
        $str .= '<br/><input type="text" name="horizlabel" size="20" value="'.$horizlabel.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '</table>';
        $str .= '</fieldset>';

        return $str;
    }

    public function treeview_params() {

        $theblock = $this->theblock;

        $str = '';

        $str .= '<fieldset id="dashboardsettings-panel-treeviewparams" class="dashboardsettings-panel off">';
        $str .= '<legend>'.get_string('treeviewparams', 'block_dashboard').'</legend>';
        $str .= '<table class="dashboard-setup">';

        $str .= '<tr valign="top">';
        $str .= '<td>';
        $helpicon = $this->output->help_icon('confighierarchic', 'block_dashboard');
        $str .= '<b>'.get_string('configparent', 'block_dashboard').': '.$helpicon.'</b><br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $parentserie = '';
        if (isset($theblock->config) && isset($theblock->config->parentserie)) {
            $parentserie = $theblock->config->parentserie;
        }
        $str .= '<input type="text" name="parentserie" size="20" value="'.$parentserie.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td style="line-height:1.5em">';
        $helpicon = $this->output->help_icon('configtreeoutput', 'block_dashboard');
        $str .= '<b>'.get_string('configtreeoutput', 'block_dashboard').': '.$helpicon.'</b><br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $treeoutput = '';
        if (isset($theblock->config) && isset($theblock->config->treeoutput)) {
            $treeoutput = $theblock->config->treeoutput;
        }
        $str .= '<br/><input type="text" name="treeoutput" size="30" value="'.$treeoutput.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td style="line-height:1.5em">';
        $helpicon = $this->output->help_icon('configformatting', 'block_dashboard');
        $str .= '<b>'.get_string('configtreeoutputformats', 'block_dashboard').': '.$helpicon.'</b><br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $treeoutputformats = '';
        if (isset($theblock->config) && isset($theblock->config->treeoutputformats)) {
            $treeoutputformats = $theblock->config->treeoutputformats;
        }
        $str .= '<br/><input type="text" name="treeoutputformats" size="30" value="'.$treeoutputformats.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '</table>';
        $str .= '</fieldset>';

        return $str;
    }

    public function graph_params() {

        $theblock = $this->theblock;
        
        $str = '';

        $str .= '<fieldset id="dashboardsettings-panel-graphparams" class="dashboardsettings-panel off">';
        $str .= '<legend>'.get_string('graphparams', 'block_dashboard').'</legend>';
        $str .= '<table class="dashboard-setup">';

        $str .= '<tr valign="top">';
        $str .= '<td align="center" colspan="4">';
        $str .= get_string('configreminderonsep', 'block_dashboard');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $helpicon = $this->output->help_icon('configxaxis', 'block_dashboard');
        $str .= '<b>'.get_string('configxaxisfield', 'block_dashboard').': '.$helpicon.'</b>';
        $str .= '</td>';
        $str .= '<td>';
        $xaxisfield = '';
        if (isset($theblock->config) && isset($theblock->config->xaxisfield)) {
            $xaxisfield = $theblock->config->xaxisfield;
        }
        $str .= '<input type="text" name="xaxisfield" size="30" value="'.$xaxisfield.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= '<b>'.get_string('configxaxislabel', 'block_dashboard').':</b>';
        $str .= '</td>';
        $str .= '<td>';
        $xaxislabel = '';
        if (isset($theblock->config) && isset($theblock->config->xaxislabel)) {
            $xaxislabel = $theblock->config->xaxislabel;
        }
        $str .= '<input type="text" name="xaxislabel" value="'.$xaxislabel.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5">';
        $helpicon = $this->output->help_icon('configyseries', 'block_dashboard');
        $str .= '<b>'.get_string('configyseries', 'block_dashboard').': '.$helpicon.'</b><br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5">';
        $yseries = '';
        if (isset($theblock->config) && isset($theblock->config->yseries)) {
            $yseries = $theblock->config->yseries;
        }
        $str .= '<input type="text" name="yseries" size="30" value="'.$yseries.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5">';
        $helpicon = $this->output->help_icon('configformatting', 'block_dashboard');
        $str .= '<b>'.get_string('configyseriesformats', 'block_dashboard').': '.$helpicon.'</b><br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5">';
        $yseriesformats = '';
        if (isset($theblock->config) && isset($theblock->config->yseriesformats)) {
            $yseriesformats = htmlentities($theblock->config->yseriesformats, ENT_NOQUOTES, 'UTF-8');
        }
        $str .= '<br/><input type="text" name="yseriesformats" size="30" value="'.$yseriesformats.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5">';
        $helpicon = $this->output->help_icon('configexplicitscaling', 'block_dashboard');
        $str .= '<b>'.get_string('configymin', 'block_dashboard').': '.$helpicon.'</b><br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5">';
        $ymin = '';
        if (isset($theblock->config) && isset($theblock->config->ymin)) {
            $ymin = $theblock->config->ymin;
        }
        $str .= '<br/><input type="text" name="ymin" size="10" value="'.$ymin.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5">';
        $str .= '<b>'.get_string('configymax', 'block_dashboard').':</b><br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5">';
        $ymax = '';
        if (isset($theblock->config) && isset($theblock->config->ymax)) {
            $ymax = $theblock->config->ymax;
        }
        $str .= '<br/><input type="text" name="ymax" size="10" value="'.$ymax.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5">';
        $str .= '<b>'.get_string('configtickspacing', 'block_dashboard').':</b><br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5">';
        $tickspacing = '';
        if (isset($theblock->config) && isset($theblock->config->tickspacing)) {
            $tickspacing = $theblock->config->tickspacing;
        }
        $str .= '<br/><input type="text" name="tickspacing" size="10" value="'.$tickspacing.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5">';
        $str .= '<b>'.get_string('configyaxisscale', 'block_dashboard').':</b>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5">';
        $yaxisscale = '';
        if (isset($theblock->config) && isset($theblock->config->yaxisscale)) {
            $yaxisscale = $theblock->config->yaxisscale;
        }
        $checked1 = ( $yaxisscale == 'linear' ) ? 'checked ="checked"' : '' ;
        $checked2 = ( $yaxisscale == 'log' ) ? 'checked ="checked"' : '' ;
        $str .= '<br/><input type="radio" name="yaxisscale" value="linear" '.$checked1.' /> '.get_string('linear', 'block_dashboard').' - ';
        $str .= '<input type="radio" name="yaxisscale" value="log" '.$checked2.' /> '.get_string('log', 'block_dashboard');
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $str .= '<b>'.get_string('configserieslabels', 'block_dashboard').':</b><br/><br/><br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $serieslabels = '';
        if (isset($theblock->config) && isset($theblock->config->serieslabels)) {
            $serieslabels = htmlentities($theblock->config->serieslabels, ENT_NOQUOTES, 'UTF-8');
        }
        $str .= '<textarea name="serieslabels" cols="30" rows="4" >'.$serieslabels.'</textarea>';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $str .= '<b>'.get_string('configyaxislabel', 'block_dashboard').':</b><br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $yaxislabel = '';
        if (isset($theblock->config) && isset($theblock->config->yaxislabel)) {
            $yaxislabel = $theblock->config->yaxislabel;
        }
        $str .= '<br/><input type="text" name="yaxislabel" size="30" value="'.$yaxislabel.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $str .= '<b>'.get_string('configyaxistickangle', 'block_dashboard').':</b><br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $yaxistickangle = '';
        if (isset($theblock->config) && isset($theblock->config->yaxistickangle)) {
            $yaxistickangle = $theblock->config->yaxistickangle;
        }
        $checked1 = ( $yaxistickangle == '-45' ) ? 'checked ="checked"' : '' ;
        $checked2 = ( $yaxistickangle == '0' ) ? 'checked ="checked"' : '' ;
        $checked3 = ( $yaxistickangle == '45' ) ? 'checked ="checked"' : '' ;
        $checked4 = ( $yaxistickangle == '90' ) ? 'checked ="checked"' : '' ;
        $str .= '<br/><input type="radio" name="yaxistickangle" value="-45" '.$checked1.' /> -45 - ';
        $str .= '<input type="radio" name="yaxistickangle" value="0" '.$checked2.' /> 0 - ';
        $str .= '<input type="radio" name="yaxistickangle" value="45" '.$checked3.' /> 45 - ';
        $str .= '<input type="radio" name="yaxistickangle" value="90" '.$checked4.' /> 90 ';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $str .= '<b>'.get_string('configgraphtype', 'block_dashboard').':</b><br/>';
        $str .= '</td>';
        $str .= '<td align="right" style="line-height:1.5em">';
        $graphtypes = array(
            'line' => get_string('line', 'block_dashboard'), 
            'bar' => get_string('bar', 'block_dashboard'), 
            'pie' => get_string('pie', 'block_dashboard'), 
            'donut' => get_string('donut', 'block_dashboard'),
            'timegraph' => get_string('timegraph', 'block_dashboard'),
            'timeline' => get_string('timeline', 'block_dashboard'),
            'googlemap' => get_string('googlemap', 'block_dashboard'),
        );
        $graphtype = 0;
        if (isset($theblock->config) && isset($theblock->config->graphtype)) {
            $graphtype = $theblock->config->graphtype;
        }
        $str .= html_writer::select($graphtypes, 'graphtype', $graphtype, array(), array('onchange' => 'showmoreoptions(this);'));
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $str .= '<b>'.get_string('configgraphwidth', 'block_dashboard').':</b><br/>';
        $str .= '</td>';
        $str .= '<td align="right" style="line-height:1.5em">';
        $graphwidth = 450;
        if (isset($theblock->config) && isset($theblock->config->graphwidth)) {
            $graphwidth = $theblock->config->graphwidth;
        }
        $str .= '<input type="text" name="graphwidth" value="'.$graphwidth.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $str .= '<b>'.get_string('configgraphheight', 'block_dashboard').':</b>';
        $str .= '</td>';
        $str .= '<td align="right" style="line-height:1.5em">';
        $graphheight = 250;
        if (isset($theblock->config) && isset($theblock->config->graphheight)) {
            $graphheight = $theblock->config->graphheight;
        }
        $str .= '<input type="text" name="graphheight" value="'.$graphheight.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= '<b>'.get_string('configshowlegend', 'block_dashboard').':</b>';
        $str .= '</td>';
        $str .= '<td>';
        $str .= get_string('configshowlegend', 'block_dashboard');
        $str .= '<input type="radio" name="showlegend" value="1" '.((@$theblock->config->showlegend) ? 'checked="checked"' : '').' />';
        $str .= get_string('yes');
        $str .= '<input type="radio" name="showlegend" value="0" '. ((!@$theblock->config->showlegend) ? 'checked="checked"' : '').' />';
        $str .= get_string('no');
        $str .= '<br/>';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '</table>';
        $str .= '</fieldset>';

        return $str;
    }

    public function google_params() {

        $theblock = $this->theblock;

        $str = '';

        $str .= '<fieldset id="dashboardsettings-panel-googleparams" class="dashboardsettings-panel off">';
        $str .= '<legend>'.get_string('googleparams', 'block_dashboard').'</legend>';
        $str .= '<table class="dashboard-setup">';
        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line_height:1.5em">';
        $helpicon = $this->output->help_icon('configmaptype', 'block_dashboard');
        $str .= '<b>'.get_string('configmaptype', 'block_dashboard').': '.$helpicon.'</b>';
        $str .= '</td>';
        $str .= '<td align="right" style="line_height:1.5em">';
        $helpicon = $this->output->help_icon('configzoom', 'block_dashboard');
        $str .= '<b>'.get_string('configzoom', 'block_dashboard').': '.$helpicon.' </b>';
        $str .= '</td>';
        $str .= '<td align="right" style="line_height:1.5em">';
        $helpicon = $this->output->help_icon('configgmdata', 'block_dashboard');
        $str .= '<b>'.get_string('configdata', 'block_dashboard').': '.$helpicon.' </b>';
        $str .= '</td>';
        $str .= '<td>';

        $maptypeopts = array(
            'ROADMAP' => get_string('maptyperoadmap', 'block_dashboard'),
            'SATELLITE' => get_string('maptypesatellite', 'block_dashboard'),
            'HYBRID' => get_string('maptypehybrid', 'block_dashboard'),
            'TERRAIN' => get_string('maptypeterrain', 'block_dashboard'),
        );
        $maptype = 'ROADMAP';
        if (isset($theblock->config) && isset($theblock->config->maptype)) {
            $maptype = $theblock->config->maptype;
        }
        $str .= html_writer::select($maptypeopts, 'maptype', $maptype);

        $str .= '</td>';
        $str .= '<td align="right" style="line_height:1.5em">';
        $zoom = '6';
        if (isset($theblock->config) && isset($theblock->config->zoom)) {
            $zoom = $theblock->config->zoom;
        }
        $str .= '<input type="text" name="zoom" size="30" value="'.$zoom.'" />';
        $str .= '</td>';
        $str .= '<td align="right" style="line_height:1.5em">';
        $datatitles = '';
        if (isset($theblock->config) && isset($theblock->config->datatitles)) {
            $datatitles = $theblock->config->datatitles;
        }
        $datalocations = '';
        if (isset($theblock->config) && isset($theblock->config->datalocations)) {
            $datalocations = $theblock->config->datalocations;
        }
        $datatypes = '';
        if (isset($theblock->config) && isset($theblock->config->datatypes)) {
            $datatypes = $theblock->config->datatypes;
        }
        $str .= '<br/><br/>';
        $str .= '<table cellpadding="2">';

        $str .= '<tr>';
        $str .= '<td>'.get_string('datatitles', 'block_dashboard').'</td>';
        $str .= '<td>'.get_string('datalocations', 'block_dashboard').'</td>';
        $str .= '<td>'.get_string('datatypes', 'block_dashboard').'</td>';
        $str .= '</tr>';
        $str .= '<tr>';
        $str .= '<td><input type="text" name="datatitles" size="12" value="'.$datatitles.'" /></td>';
        $str .= '<td><input type="text" name="datalocations" size="35" value="'.$datalocations.'" /></td>';
        $str .= '<td><input type="text" name="datatypes" size="12" value="'.$datatypes.'" /></td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line_height:1.5em">';
        $str .= '<b>'.get_string('configlocation', 'block_dashboard').':</b><br/>';
        $str .= '</td>';
        $str .= '<td>';
        $lat = '0';
        $lng = '0';
        if (isset($theblock->config) && isset($theblock->config->lat)) {
            $lat = $theblock->config->lat;
        }
        if (isset($theblock->config) && isset($theblock->config->lng)) {
            $lng = $theblock->config->lng;
        }
        $str .= '(lat : <input type="text" size="8" name="lat" value="'.$lat.'" />, long : <input type="text"  size="8" name="lng" value="'.$lng.'" />)';

        $str .= '</td>';
        $str .= '</tr>';

        $str .= '</table>';
        $str .= '</fieldset>';

        return $str;
    }

    public function timeline_params() {

        $theblock = $this->theblock;

        $str = '';

        $str .= '<fieldset id="dashboardsettings-panel-timelineparams" class="dashboardsettings-panel off">';
        $str .= '<legend>'.get_string('timelineparams', 'block_dashboard').'</legend>';
        $str .= '<table class="dashboard-setup">';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $helpicon = $this->output->help_icon('configbands', 'block_dashboard');
        $str .= '<b>'.get_string('configshowlowerband', 'block_dashboard').': '.$helpicon.'</b><br/>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $showlowerbandyes = 'checked="checked"';
        $showlowerbandno = '';
        if (isset($theblock->config) && isset($theblock->config->showlowerband)) {
            $showlowerbandyes = ($theblock->config->showlowerband) ? ' checked="checked" ' : '';
            $showlowerbandno = (!$theblock->config->showlowerband) ? ' checked="checked" ' : '';
        }
        $str .= '<input type="radio" name="showlowerband" value="1" '.$showlowerbandyes.' > '.get_string('yes');
        $str .= ' - <input type="radio" name="showlowerband" value="0" '.$showlowerbandno.' > '.get_string('no');
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.8em">';
        $helpicon = $this->output->help_icon('configtimeunits', 'block_dashboard');
        $str .= '<b>'.get_string('configupperbandunit', 'block_dashboard').': '.$helpicon.'</b><br/>';
        $str .= '</td>';
        $str .= '<td align="right" style="line-height:1.8em">';
        $upperbandunit = 'MONTH';
        if (isset($theblock->config) && isset($theblock->config->upperbandunit)) {
            $upperbandunit = $theblock->config->upperbandunit;
        }
        $upperunits = array('MONTH' => get_string('month', 'block_dashboard'),
            'WEEK' => get_string('week', 'block_dashboard'),
            'DAY' => get_string('day', 'block_dashboard'),
            'HOUR' => get_string('hour', 'block_dashboard'));
        $str .= html_writer::select($upperunits, 'upperbandunit', $upperbandunit);
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.8em">';
        $helpicon = $this->output->help_icon('configtimeunits', 'block_dashboard');
        $str .= '<b>'.get_string('configlowerbandunit', 'block_dashboard').': '.$helpicon.' </b>';
        $str .= '</td>';
        $str .= '<td style="line-height:1.5em">';
        $lowerbandunit = 'YEAR';
        if (isset($theblock->config) && isset($theblock->config->lowerbandunit)) {
            $lowerbandunit = $theblock->config->lowerbandunit;
        }
        $lowerunits = array('YEAR' => get_string('year', 'block_dashboard'),
            'MONTH' => get_string('month', 'block_dashboard'),
            'WEEK' => get_string('week', 'block_dashboard'),
            'DAY' => get_string('day', 'block_dashboard'));
        $str .= '<br/>';
        $str .= html_writer::select($lowerunits, 'lowerbandunit', $lowerbandunit);
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $helpicon = $this->output->help_icon('configeventmapping', 'block_dashboard');
        $str .= '<b>'.get_string('configeventmapping', 'block_dashboard').': '.$helpicon.'</b><br/>';
        $str .= '</td>';
        $str .= '<td colspan="3" style="line-height:1.5em">';

        $eventtitle = '';
        if (isset($theblock->config) && isset($theblock->config->timelineeventtitle)) {
            $eventtitle = $theblock->config->timelineeventtitle;
        }

        $eventstart = '';
        if (isset($theblock->config) && isset($theblock->config->timelineeventstart)) {
            $eventstart = $theblock->config->timelineeventstart;
        }

        $eventend = '';
        if (isset($theblock->config) && isset($theblock->config->timelineeventend)) {
            $eventend = $theblock->config->timelineeventend;
        }

        $eventlink = '';
        if (isset($theblock->config) && isset($theblock->config->timelineeventlink)) {
            $eventlink = $theblock->config->timelineeventlink;
        }

        $eventdesc = '';
        if (isset($theblock->config) && isset($theblock->config->timelineeventdesc)) {
            $eventdesc = $theblock->config->timelineeventdesc;
        }

        $str .= '<br/><br/>';
        $str .= '<table cellpadding="2">';
        $str .= '<tr>';
        $str .= '<td>'.get_string('eventtitles', 'block_dashboard').'</td>';
        $str .= '<td>'.get_string('eventstart', 'block_dashboard').'</td>';
        $str .= '<td>'.get_string('eventend', 'block_dashboard').'</td>';
        $str .= '<td>'.get_string('eventlink', 'block_dashboard').'</td>';
        $str .= '<td>'.get_string('eventdesc', 'block_dashboard').'</td>';
        $str .= '</tr>';

        $str .= '<tr>';
        $str .= '<td><input type="text" name="timelineeventtitle" size="12" value="'.$eventtitle.'" /></td>';
        $str .= '<td><input type="text" name="timelineeventstart" size="12" value="'.$eventstart.'" /></td>';
        $str .= '<td><input type="text" name="timelineeventend" size="12" value="'.$eventend.'" /></td>';
        $str .= '<td><input type="text" name="timelineeventlink" size="12" value="'.$eventlink.'" /></td>';
        $str .= '<td><input type="text" name="timelineeventdesc" size="12" value="'.$eventdesc.'" /></td>';
        $str .= '</tr>';
        $str .= '</table>';

        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="center">';
        $str .= get_string('configcolors', 'block_dashboard');
        $str .= '<br/><textarea cols="20" rows="10" name="timelinecolors" >'.@$theblock->config->timelinecolors.'</textarea>';
        $str .= '</td>';
        $str .= '<td align="center">';
        $str .= '<b>'.get_string('configcolorfield', 'block_dashboard').':</b>';
        $str .= '<br/><input type="text" name="timelinecolorfield" value="'.@$theblock->config->timelinecolorfield.'" size="20" />';
        $str .= '</td>';
        $str .= '<td align="center">';
        $str .= get_string('configcoloredvalues', 'block_dashboard');
        $str .= '<br/>';
        $str .= '<textarea cols="20" rows="10" name="timelinecolorkeys" >'.@$theblock->config->timelinecolorkeys.'</textarea>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</fieldset>';

        return $str;
    }

    public function summators() {

        $theblock = $this->theblock;

        $str = '';

        $str .= '<fieldset id="dashboardsettings-panel-summatorsparams" class="dashboardsettings-panel off">';
        $str .= '<legend>'.get_string('summatorsparams', 'block_dashboard').'</legend>';
        $str .= '<table class="dashboard-setup">';

        $str .= '<tr valign="top">';
        $str .= '<td align="center" colspan="4">';
        $str .= get_string('configreminderonsep', 'block_dashboard');
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $helpicon = $this->output->help_icon('configsummators', 'block_dashboard');
        $str .= '<b>'.get_string('confignumsums', 'block_dashboard').': '.$helpicon.'</b>';
        $str .= '</td>';
        $str .= '<td>';

        $numsums = '';
        if (isset($theblock->config) && isset($theblock->config->numsums)) {
            $numsums = $theblock->config->numsums;
        }
        $str .= '<input type="text" name="numsums" size="30" value="'.$numsums.'" />';
        $numsumformats = '';
        if (isset($theblock->config) && isset($theblock->config->numsumformats)) {
            $numsumformats = htmlentities($theblock->config->numsumformats, ENT_NOQUOTES, 'UTF-8');
        }

        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $helpicon = $this->output->help_icon('configformatting', 'block_dashboard');
        $str .= '<b>'.get_string('confignumsumsformats', 'block_dashboard').': '.$helpicon.' </b>';
        $str .= '</td>';
        $str .= '<td>';
        $str.= '<input type="text" name="numsumformats" size="30" value="'.$numsumformats.'" />';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= '<b>'.get_string('confignumsumslabels', 'block_dashboard').':</b>';
        $str .= '</td>';
        $str .= '<td>';

        $numsumlabels = '';
        if (isset($theblock->config) && isset($theblock->config->numsumlabels)) {
            $numsumlabels = $theblock->config->numsumlabels;
        }
        $str .= '<textarea name="numsumlabels" cols="30" rows="5" >'.$numsumlabels.'</textarea>';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '</table>';
        $str .= '</fieldset>';

        return $str;
    }

    public function tablecolor_mapping() {

        $theblock = $this->theblock;

        $str = '';

        $str .= '<fieldset id="dashboardsettings-panel-tablecolormapping" class="dashboardsettings-panel off">';
        $str .= '<legend>'.get_string('tablecolormapping', 'block_dashboard').'</legend>';
        $str .= '<table class="dashboard-setup">';
        $str .= '<tr valign="top">';
        $str .= '<td align="center">';
        $str .= get_string('configcolors', 'block_dashboard');
        $str .= '<br/><textarea cols="20" rows="10" name="colors" >'.@$theblock->config->colors.'</textarea>';
        $str .= '</td>';
        $str .= '<td align="center">';
        $str .= '<b>'.get_string('configcolorfield', 'block_dashboard').':</b>';
        $str .= '<br/><input type="text" name="colorfield" value="'.@$theblock->config->colorfield.'" size="20" />';
        $str .= '</td>';
        $str .= '<td align="center">';
        $str .= get_string('configcoloredvalues', 'block_dashboard');
        $str .= '<br/><textarea cols="20" rows="10" name="coloredvalues" >'.@$theblock->config->coloredvalues.'</textarea>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</fieldset>';

        return $str;
    }

    public function data_refresh() {

        $theblock = $this->theblock;

        $str = '';

        $str .= '<fieldset id="dashboardsettings-panel-datarefresh" class="dashboardsettings-panel off">';
        $str .= '<legend>'.get_string('datarefresh', 'block_dashboard').'</legend>';
        $str .= '<table class="dashboard-setup">';
        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $helpicon = $this->output->help_icon('configdelayedrefresh', 'block_dashboard');
        $str .= '<b>'.get_string('configcronmode', 'block_dashboard').': '.$helpicon.'</b>';
        $str .= '</td>';
        $str .= '<td>';
        $cronmode = '';
        if (isset($theblock->config) && isset($theblock->config->cronmode)) {
            $cronmode = $theblock->config->cronmode;
        } else {
            $cronmode = 'norefresh';
        }

        $modes['norefresh'] = get_string('norefresh', 'block_dashboard');
        $modes['global'] = get_string('globalcron', 'block_dashboard');
        $modes['instance'] = get_string('instancecron', 'block_dashboard');
        $str .= html_writer::select($modes, 'cronmode', $cronmode);

        $str .= '</td>';
        $str .= '<td align="right" style="line-height:1.5em">';
        $str .= '<b>'.get_string('configcrontime', 'block_dashboard').' : </b><br/>';
        $str .= '</td>';
        $str .= '<td>';
        $str .= '<td>';

        $cronhour = '';
        if (isset($theblock->config) && isset($theblock->config->cronhour)) {
            $cronhour = $theblock->config->cronhour;
        }
        $cronmin = '';
        if (isset($theblock->config) && isset($theblock->config->cronmin)) {
            $cronmin = $theblock->config->cronmin;
        }
        for ($i = 0; $i < 24; $i++){
            $hours["$i"] = sprintf('%02d', $i);
        }
        $mins['00'] = '00';
        $mins['15'] = '15';
        $mins['30'] = '30';
        $mins['45'] = '45';
        $str .= html_writer::select($hours, 'cronhour', $cronhour);
        $str .= get_string('hours', 'block_dashboard');
        $str .= ' ';
        $str .= html_writer::select($mins, 'cronmin', $cronmin);
        $str .= get_string('mins', 'block_dashboard');
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right" style="line-height:1.5em">';
        $str .= '<b>'.get_string('configsendadminnotification', 'block_dashboard').'</b>';
        $str .= '</td>';
        $str .= '<td align="left" style="line-height:1.5em">';
        $checked = (!empty($theblock->config->cronadminnotifications)) ? 'checked="checked"' : '';
        $str .= '<input type="checkbox" name="cronadminnotifications" value="1" '.$checked.' /> '.get_string('enabled', 'block_dashboard');
        $str .= '</td>';
        $str .= '<td align="right" style="line-height:1.5em">';
        $str .= '<b>'.get_string('configcronfrequency', 'block_dashboard').': </b>';
        $str .= '</td>';
        $str .= '<td align="right" style="line-height:1.5em">';
        $cronfrequency = '';
        if (isset($theblock->config) && isset($theblock->config->cronfrequency)) {
            $cronfrequency = $theblock->config->cronfrequency;
        } else {
            $cronfrequency = 'daily';
        }

        $freq['daily'] = get_string('daily', 'block_dashboard');
        $freq['0'] = get_string('sunday', 'block_dashboard');
        $freq['1'] = get_string('monday', 'block_dashboard');
        $freq['2'] = get_string('tuesday', 'block_dashboard');
        $freq['3'] = get_string('wednesday', 'block_dashboard');
        $freq['4'] = get_string('thursday', 'block_dashboard');
        $freq['5'] = get_string('friday', 'block_dashboard');
        $freq['6'] = get_string('saturday', 'block_dashboard');
        $str .= '<br/>';
        $str .= html_writer::select($freq, 'cronfrequency', $cronfrequency, '');

        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</fieldset>';

        return $str;
    }

    public function file_output() {

        $theblock = $this->theblock;

        $str = '';

        $str .= '<fieldset id="dashboardsettings-panel-fileoutput" class="dashboardsettings-panel off">';
        $str .= '<legend>'.get_string('fileoutput', 'block_dashboard').'</legend>';
        $str .= '<table width="100%" cellpadding="5" cellspacing="0">';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $helpicon = $this->output->help_icon('configmakefile', 'block_dashboard');
        $str .= '<b>'.get_string('configmakefile', 'block_dashboard').': '.$helpicon.'</b>';
        $str .= '</td>';
        $str .= '<td align="left">';

        $makefile = '';
        if (isset($theblock->config) && isset($theblock->config->makefile)) {
            $makefile = $theblock->config->makefile;
        } else {
            $theblock->config->makefile = 0;
        }
        $checked1 = ( $makefile == 0 ) ? 'checked ="checked"' : '' ;
        $checked2 = ( $makefile == 1 ) ? 'checked ="checked"' : '' ;
        $str .= '<input type="radio" name="makefile" value="0" '.$checked1.' /> '.get_string('no');
        $str .= ' - <input type="radio" name="makefile" value="1" '.$checked2.' />  '.get_string('yes');

        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= $this->output->help_icon('configfileoutput', 'block_dashboard');
        $str .= '<b>'.get_string('configfileoutput', 'block_dashboard').': '.$helpicon.' </b>';
        $str .= '</td>';
        $str .= '<td align="left">';

        $fileoutput = '';
        if (isset($theblock->config) && isset($theblock->config->fileoutput)) {
            $fileoutput = $theblock->config->fileoutput;
        }
        $str.= '<input type="text" name="fileoutput" value="'.$fileoutput.'" />';

        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $helpicon = $this->output->help_icon('configformatting', 'block_dashboard');
        $str .= '<b>'.get_string('configfileoutputformats', 'block_dashboard').': '.$helpicon.' </b>';
        $str .= '</td>';
        $str .= '<td align="left">';

        $fileoutputformats = '';
        if (isset($theblock->config) && isset($theblock->config->fileoutputformats)) {
            $fileoutputformats = $theblock->config->fileoutputformats;
        }
        $str .= '<input type="text" name="fileoutputformats" value="'.$fileoutputformats.'" />';

        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $str .= '<b>'.get_string('configfileformat', 'block_dashboard').':</b>';
        $str .= '</td>';
        $str .= '<td align="left">';

        $fileformat = 'CSV';
        if (isset($theblock->config) && isset($theblock->config->fileformat)) {
            $fileformat = $theblock->config->fileformat;
        }
        $fileformats = array(
            'CSV' => get_string('csv', 'block_dashboard'),
            'CSVWH' => get_string('csvwithoutheader', 'block_dashboard'),
            'SQL' => get_string('sqlinserts', 'block_dashboard'));
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $helpicon = $this->output->help_icon('configfilesqlouttable', 'block_dashboard');
        $str .= '<b>'.get_string('configfilesqlouttable', 'block_dashboard').': '.$helpicon.'</b>';
        $str .= '</td>';
        $str .= '<td align="left">';

        $str .= html_writer::select($fileformats, 'fileformat', $fileformat);

        $filesqlouttable = '';
        if (isset($theblock->config) && isset($theblock->config->filesqlouttable)) {
            $filesqlouttable = $theblock->config->filesqlouttable;
        }
        $str .= '<input type="text" name="filesqlouttable" value="'.$filesqlouttable.'" />';

        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';

        $str .= '<table width="100%" cellpadding="5" cellspacing="0">';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $helpicon = $this->output->help_icon('configfilelocation', 'block_dashboard');
        $str .= '<b>'.get_string('configfilelocation', 'block_dashboard').': '.$helpicon.'</b>';
        $str .= '</td>';
        $str .= '<td align="left">';

        $filelocation = '';
        if (isset($theblock->config) && isset($theblock->config->filelocation)) {
            $filelocation = $theblock->config->filelocation;
        }
        $str .= '<input type="text" name="filelocation" size="30" value="'.$filelocation.'" />';
        $checked = (@$theblock->config->horodatefiles) ? 'checked="checked"' : '' ;
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td align="right">';
        $helpicon = $this->output->help_icon('confighorodatefiles', 'block_dashboard');
        $str .= '<b>'.get_string('confighorodatefiles', 'block_dashboard').': '.$helpicon.' </b>';
        $str .= '</td>';
        $str .= '<td align="left">';
        $str .= '<input type="checkbox" name="horodatefiles" value="1" '.$checked.' />';
        $str .= '</td>';
        $str .= '</tr>';

        if (has_capability('moodle/site:config', context_system::instance())) {

            $filepathadminoverride = '';
            if (isset($theblock->config) && isset($theblock->config->filepathadminoverride)) {
                $filepathadminoverride = $theblock->config->filepathadminoverride;
            }

            $str .= '<tr valign="top">';
            $str .= '<td align="right">';
            $str .= '<b>';
            $str .= get_string('configfilepathadminoverride', 'block_dashboard'); 
            $str .= ' : ';
            $str .= $this->output->help_icon('configfilepathadminoverride', 'block_dashboard');
            $str .= '</b>';
            $str .= '</td>';
            $str .= '<td align="left">';
            $str .= '<input type="text" name="filepathadminoverride" size="30" value="'.$filepathadminoverride.'" />';
            $str .= '</td>';
            $str .= '</tr>';
        } 
        $str .= '</table>';
        $str .= '</fieldset>';

        return $str;

    }


    /**
     *
     * @param object $theblock a dashboard block instance
     */
    public function setup_returns() {
        global $COURSE;

        $str = '<div class="dashboard-table-buttons">';

        if (block_dashboard_supports_feature('config/importexport')) {
            $params = array('id' => $COURSE->id, 'instance' => $this->theblock->instance->id, 'what' => 'upload');
            $copyurl = new moodle_url('/blocks/dashboard/pro/copyconfig.php', $params);
            $button = '<input type="button" name="go_import" value="'.get_string('importconfig', 'block_dashboard').'">';
            $str .= '<a href="'.$copyurl.'">'.$button.'</a>&nbsp;';
    
            $params = array('id' => $COURSE->id, 'instance' => $this->theblock->instance->id, 'what' => 'get');
            $exporturl = new moodle_url('/blocks/dashboard/pro/copyconfig.php', $params);
            $button = '<input type="button" name="go_export" value="'.get_string('exportconfig', 'block_dashboard').'">';
            $str .= '<a href="'.$exporturl.'" target="_blank">'.$button.'</a>&nbsp;';
        }

        $str .= '<input type="submit" name="submit" value="'.get_string('savechanges').'" />&nbsp;';

        $str .= '<input type="submit" name="save" value="'.get_string('savechangesandconfig', 'block_dashboard').'" />';

        $str .= '<input type="submit" name="saveview" value="'.get_string('savechangesandview', 'block_dashboard').'" />';

        $str .= '</div>';

        return $str;
    }
}