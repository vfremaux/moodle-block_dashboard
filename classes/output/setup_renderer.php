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
namespace block_dashboard\output;

use \StdClass;
use \html_writer;
use \moodle_url;
use \tabobject;

defined('MOODLE_INTERNAL') || die();

class setup_renderer extends \plugin_renderer_base {

    protected $theblock;

    public function set_block($theblock) {
        $this->theblock = $theblock;
    }

    public function form_header() {

        $theblock = $this->theblock;

        $template = new StdClass();
        $template->strconfigtitle = get_string('configtitle', 'block_dashboard');
        $template->strconfighidetitle = get_string('confighidetitle', 'block_dashboard');

        $template->theblocktitle = '';
        if (isset($theblock->config) && isset($theblock->config->title)) {
            $template->theblocktitle = $theblock->config->title;
        }

        $template->hidetitlestate = '';
        if (isset($theblock->config) && isset($theblock->config->hidetitle)) {
            $template->hidetitlestate = ($theblock->config->hidetitle) ? ' checked="checked" ' : '';
        }
        $template->strchecktohide = get_string('checktohide', 'block_dashboard');

        return $this->render_from_template('block_dashboard/queryformheader', $template);
    }

    public function layout() {

        $theblock = $this->theblock;

        $template = new StdClass();

        $template->strdashboardlayout = get_string('dashboardlayout', 'block_dashboard');
        $template->strconfiglayout = get_string('configlayout', 'block_dashboard');
        $yesnoopts = array('0' => get_string('no'), '1' => get_string('yes'));
        $template->inblocklayoutselect = html_writer::select($yesnoopts, 'inblocklayout', $theblock->config->inblocklayout, '');

        return $this->render_from_template('block_dashboard/layout', $template);
    }

    /**
     * Print tabs for setup screens.
     * @param object $theblock a dashboard block instance
     */
    public function setup_tabs() {

        $config = $this->theblock->config;

        $cond1 = (!empty($config->tabletype)) && ($config->tabletype == 'tabular');
        $cond2 = (!empty($config->tabletype)) && ($config->tabletype == 'treeview');
        $cond3 = (!empty($config->graphtype)) && ($config->graphtype == 'googlemap');
        $cond4 = (!empty($config->graphtype)) && ($config->graphtype == 'timeline');

        $tabs = array();
        $tabs[] = array('querydesc', get_string('querydesc', 'block_dashboard'), true);
        $tabs[] = array('queryparams', get_string('queryparams', 'block_dashboard'), true);
        $tabs[] = array('outputparams', get_string('outputparams', 'block_dashboard'), true);
        $tabs[] = array('tabularparams', get_string('tabularparams', 'block_dashboard'), ($cond1) ? true : false);
        if (block_dashboard_supports_feature('data/treeview')) {
            $tabs[] = array('treeviewparams', get_string('treeviewparams', 'block_dashboard'), ($cond2) ? true : false);
        }
        $tabs[] = array('sumsandfiltersparams', get_string('sumsandfiltersparams', 'block_dashboard'), true);
        $tabs[] = array('graphparams', get_string('graphparams', 'block_dashboard'), true);
        if (block_dashboard_supports_feature('graph/google')) {
            $tabs[] = array('googleparams', get_string('googleparams', 'block_dashboard'), ($cond3) ? true : false);
        }
        if (block_dashboard_supports_feature('graph/timeline')) {
            $tabs[] = array('timelineparams', get_string('timelineparams', 'block_dashboard'), ($cond4) ? true : false);
        }
        if (block_dashboard_supports_feature('result/colouring')) {
            $tabs[] = array('tablecolormapping', get_string('tablecolormapping', 'block_dashboard'), true);
        }

        if (block_dashboard_supports_feature('result/export')) {
            $tabs[] = array('fileoutput', get_string('fileoutput', 'block_dashboard'), true);
            $tabs[] = array('datarefresh', get_string('datarefresh', 'block_dashboard'), true);
        }

        $template = new StdClass;

        if ($this->has_standard_tabs()) {
            $str = '<div class="tabtree">';
            $str .= '<ul class="tabrow0">';
            $template->tabsopening = $str;
        } else {
            $template->tabsopening = '<ul class="nav nav-tabs">';
        }

        foreach ($tabs as $tabarr) {
            $tab = new StdClass;
            list($tab->tabkey, $tab->tabname, $visible) = $tabarr;
            $tab->tabclass = ($tab->tabkey == 'querydesc') ? 'active ' : '';
            $tab->tabclass .= ($visible) ? 'on here' : 'off';
            $tab->tabname = str_replace(' ', '&nbsp;', $tab->tabname);
            $template->tabs[] = $tab;
        }

        if ($this->has_standard_tabs()) {
            $template->tabsclosing = '</ul>';
        } else {
            $template->tabsclosing = '</ul></div>';
        }

        return $this->render_from_template('block_dashboard/querysetuptabs', $template);
    }

    /**
     * Checks if we are using standard tabtree or essential altered nav divs.
     */
    protected function has_standard_tabs() {

        // Print a fake tab rendering and check what is inside.
        $tabrow[0][] = new tabobject('fake', 'http://foo.com', 'fake');
        $result = print_tabs($tabrow, null, null, null, true);

        return preg_match('/tabtree/', $result);
    }

    public function query_description() {

        $theblock = $this->theblock;

        $template = new StdClass;
        $template->strquerydesc = get_string('querydesc', 'block_dashboard');
        $template->strconfigtarget = get_string('configtarget', 'block_dashboard');
        $template->strconfigdisplay = get_string('configdisplay', 'block_dashboard');
        $template->strconfigshowdata = get_string('configshowdata', 'block_dashboard');
        $template->strconfigshowgraph = get_string('configshowgraph', 'block_dashboard');
        $template->strconfigshownumsums = get_string('configshownumsums', 'block_dashboard');
        $template->strconfigshowquery = get_string('configshowquery', 'block_dashboard');
        $template->strconfigshowfilterqueries = get_string('configshowfilterqueries', 'block_dashboard');
        $template->strconfigquery = get_string('configquery', 'block_dashboard');

        $template->stryes = get_string('yes');
        $template->strno = get_string('no');

        $targets = array('moodle' => 'Moodle', 'extra' => 'Extra DB');
        $template->targetselect = html_writer::select($targets, 'target', $theblock->config->target);

        if ($theblock->config->showdata) {
            $template->showdatachecked = 'checked="checked"';
        } else {
            $template->showdataunchecked = 'checked="checked"';
        }

        if ($theblock->config->showgraph) {
            $template->showgraphchecked = 'checked="checked"';
        } else {
            $template->showgraphunchecked = 'checked="checked"';
        }

        if ($theblock->config->shownumsums) {
            $template->shownumsumschecked = 'checked="checked"';
        } else {
            $template->shownumsumsunchecked = 'checked="checked"';
        }

        if ($theblock->config->showquery) {
            $template->showquerychecked = 'checked="checked"';
        } else {
            $template->showqueryunchecked = 'checked="checked"';
        }

        if ($theblock->config->showfilterqueries) {
            $template->showfilterquerieschecked = 'checked="checked"';
        } else {
            $template->showfilterqueriesunchecked = 'checked="checked"';
        }

        $template->helpiconconfigquery = $this->output->help_icon('configquery', 'block_dashboard');

        $template->theblocktitle = '';
        if (isset($theblock->config) && isset($theblock->config->query)) {
            $template->theblocktitle = $theblock->config->query;
        }

        if (block_dashboard_supports_feature('result/rotation')) {
            $template->resultrotate = true;

            $template->helpiconconfigqueryrotate = $this->output->help_icon('configqueryrotate', 'block_dashboard');

            $template->strconfigqueryrotate = get_string('configqueryrotate', 'block_dashboard');
            $template->strconfigrotatecolumns = get_string('configrotatecolumns', 'block_dashboard');
            $template->strconfigrotatepivot = get_string('configrotatepivot', 'block_dashboard');
            $template->strconfigrotatenewkeys = get_string('configrotatenewkeys', 'block_dashboard');

            $template->configqueryrotatecols = @$theblock->config->queryrotatecols;
            $template->configqueryrotatepivot = @$theblock->config->queryrotatepivot;
            $template->configqueryrotatenewkeys = @$theblock->config->queryrotatenewkeys;
        }

        $str = $this->render_from_template('block_dashboard/querydescription', $template);
        echo $str;
    }

    public function query_params() {

        $theblock = $this->theblock;

        $template = new StdClass;

        $template->strqueryparams = get_string('queryparams', 'block_dashboard');
        $template->strconfigparams = get_string('configparams', 'block_dashboard');

        $template->strsqlparamvar = get_string('sqlparamvar', 'block_dashboard');
        $template->strsqlparamlabel = get_string('sqlparamlabel', 'block_dashboard');
        $template->strsqlparamtype = get_string('sqlparamtype', 'block_dashboard');
        $template->strsqlparamvalues = get_string('sqlparamvalues', 'block_dashboard');
        $template->strsqlparamdefault = get_string('sqlparamdefault', 'block_dashboard');
        $template->strparamas = get_string('paramas', 'block_dashboard');
        $template->strassql = get_string('paramassql', 'block_dashboard');
        $template->strasvar = get_string('paramasvar', 'block_dashboard');
        $template->strascol = get_string('paramascol', 'block_dashboard');

        $typeopts = array(
            'choice' => get_string('choicevalue', 'block_dashboard'),
            'text' => get_string('textvalue', 'block_dashboard'),
            'select' => get_string('listvalue', 'block_dashboard'),
            'range' => get_string('rangevalue', 'block_dashboard'),
            'date' => get_string('datevalue', 'block_dashboard'),
            'daterange' => get_string('daterangevalue', 'block_dashboard'),
        );

        for ($i = 1; $i <= DASHBOARD_MAX_QUERY_PARAMS; $i ++) {
            $param = new StdClass;
            $param->asvarkey = 'paramasvar'.$i;
            $param->varkey = 'sqlparamvar'.$i;
            $param->labelkey = 'sqlparamlabel'.$i;
            $param->valueskey = 'sqlparamvalues'.$i;
            $param->defaultkey = 'sqlparamdefault'.$i;

            switch (@$theblock->config->{$param->asvarkey}) {
                case 'sql': {
                    $param->sqlchecked = 'checked="checked"';
                    break;
                }

                case 'variable': {
                    $param->varchecked = 'checked="checked"';
                    break;
                }

                case 'outputcol': {
                    $param->colchecked = 'checked="checked"';
                }

                default:
                    $param->sqlchecked = 'checked="checked"';
            }

            $param->varkeyvalue = @$theblock->config->{$param->varkey};
            $param->labelkeyvalue = @$theblock->config->{$param->labelkey};

            $typekey = 'sqlparamtype'.$i;
            $typevalue = @$theblock->config->{$typekey};
            $param->typeselect = html_writer::select($typeopts, $typekey, $typevalue);

            $param->valueskeyvalue = @$theblock->config->{$param->valueskey};
            $param->defaultkeyvalue = @$theblock->config->{$param->defaultkey};
            $template->params[] = $param;
        }

        return $this->render_from_template('block_dashboard/queryparams', $template);
    }

    public function output_params() {

        $theblock = $this->theblock;

        $template = new StdClass;
        $template->stryes = get_string('yes');
        $template->strno = get_string('no');
        $template->stroutputparams = get_string('outputparams', 'block_dashboard');
        $template->strconfigreminderonsep = get_string('configreminderonsep', 'block_dashboard');

        $template->strconfigoutputfields = get_string('configoutputfields', 'block_dashboard');
        $template->helpiconconfigoutputfields = $this->output->help_icon('configoutputfields', 'block_dashboard');

        if (isset($theblock->config) && isset($theblock->config->outputfields)) {
            $template->fields = $theblock->config->outputfields;
        }

        $template->helpiconconfigformatting = $this->output->help_icon('configformatting', 'block_dashboard');
        $template->strconfigoutputformats = get_string('configoutputformats', 'block_dashboard');

        if (isset($theblock->config) && isset($theblock->config->outputformats)) {
            $template->formats = $theblock->config->outputformats;
        }

        $template->helpiconconfigpagesize = $this->output->help_icon('configpagesize', 'block_dashboard');
        $template->strconfigpagesize = get_string('configpagesize', 'block_dashboard');

        if (isset($theblock->config) && isset($theblock->config->pagesize)) {
            $template->pagesize = $theblock->config->pagesize;
        }

        if (isset($theblock->config) && isset($theblock->config->bigresult)) {
            $template->bigresult = 'checked="checked"';
        }

        $template->strbigresult = get_string('bigresult', 'block_dashboard');
        $template->helpiconconfigbigresult = $this->output->help_icon('configbigresult', 'block_dashboard');

        $template->helpiconconfigcaching = $this->output->help_icon('configcaching', 'block_dashboard');
        $template->strconfigcaching = get_string('configcaching', 'block_dashboard');

        $template->uselocalcachingchecked = '';
        $template->uselocalcachingunchecked = '';
        if (isset($theblock->config) && isset($theblock->config->uselocalcaching)) {
            if ($theblock->config->uselocalcaching) {
                $template->uselocalcachingchecked = 'checked="checked"';
            } else {
                $template->uselocalcachingunchecked = 'checked="checked"';
            }
        }

        $template->strconfigcachingttl = get_string('configcachingttl', 'block_dashboard');

        if (isset($theblock->config) && isset($theblock->config->cachingttl)) {
            $template->cachingttl = $theblock->config->cachingttl;
        }

        $template->strminutes = get_string('minutes');
        $template->strconfigcleandisplay = get_string('configcleandisplay', 'block_dashboard');

        if (isset($theblock->config) && isset($theblock->config->cleandisplay)) {
            if ($theblock->config->cleandisplay) {
                $template->cleandisplaychecked = 'checked="checked"';
            } else {
                $template->cleandisplayunchecked = 'checked="checked"';
            }
        }
        $template->strcleandisplayuptocolumn = get_string('cleandisplayuptocolumn', 'block_dashboard');
        $template->cleandisplayuptocolumn = @$theblock->config->cleandisplayuptocolumn;

        $template->strconfigsortable = get_string('configsortable', 'block_dashboard');

        if (isset($theblock->config) && isset($theblock->config->sortable)) {
            if ($theblock->config->sortable){
                $template->sortablechecked = 'checked="checked"';
            } else {
                $template->sortableunchecked = 'checked="checked"';
            }
        }

        $template->helpiconconfigsplitsumsonsort = $this->output->help_icon('configsplitsumsonsort', 'block_dashboard');
        $template->strconfigsplitsumsonsort = get_string('configsplitsumsonsort', 'block_dashboard');

        if (isset($theblock->config) && isset($theblock->config->splitsumsonsort)) {
            $template->splitsumsonsort = $theblock->config->splitsumsonsort;
        }

        $template->strconfigoutputfieldslabels = get_string('configoutputfieldslabels', 'block_dashboard');

        if (isset($theblock->config) && isset($theblock->config->fieldlabels)) {
            $template->fieldlabels = $theblock->config->fieldlabels;
        }

        $template->helpiconconfigtabletype = $this->output->help_icon('configtabletype', 'block_dashboard');
        $template->strconfigtabletype = get_string('configtabletype', 'block_dashboard');

        $tabletype = '';
        if (isset($theblock->config) && isset($theblock->config->tabletype)) {
            $tabletype = $theblock->config->tabletype;
        }

        $tabletypeopts['linear'] = get_string('linear', 'block_dashboard');
        $tabletypeopts['tabular'] = get_string('tabular', 'block_dashboard');
        $tabletypeopts['treeview'] = get_string('treeview', 'block_dashboard');
        $template->tabletypeselect = html_writer::select($tabletypeopts, 'tabletype', $tabletype, array());

        return $this->render_from_template('block_dashboard/outputparams', $template);
    }

    public function tabular_params() {

        $theblock = $this->theblock;

        $template = new StdClass;

        $template->strtabularparams = get_string('tabularparams', 'block_dashboard');
        $template->helpiconconfigtabular = $this->output->help_icon('configtabular', 'block_dashboard');
        $template->strconfigverticalkeys = get_string('configverticalkeys', 'block_dashboard');

        $template->verticalkeys = '';
        if (isset($theblock->config) && isset($theblock->config->verticalkeys)) {
            $template->verticalkeys = $theblock->config->verticalkeys;
        }

        $template->strconfighorizkey = get_string('confighorizkey', 'block_dashboard');

        $template->horizkey = '';
        if (isset($theblock->config) && isset($theblock->config->horizkey)) {
            $template->horizkey = $theblock->config->horizkey;
        }

        $template->helpiconconfigformatting = $this->output->help_icon('configformatting', 'block_dashboard');
        $template->strconfigverticalformats = get_string('configverticalformats', 'block_dashboard');

        $template->verticalformats = '';
        if (isset($theblock->config) && isset($theblock->config->verticalformats)) {
            $template->verticalformats = htmlentities($theblock->config->verticalformats, ENT_QUOTES, 'UTF-8');
        }

        $template->strconfighorizformat = get_string('confighorizformat', 'block_dashboard');

        $template->horizformat = '';
        if (isset($theblock->config) && isset($theblock->config->horizformat)) {
            $template->horizformat = $theblock->config->horizformat;
        }
        $template->helpiconconfigtablesplit = $this->output->help_icon('configtablesplit', 'block_dashboard');
        $template->strconfigspliton = get_string('configspliton', 'block_dashboard');

        $template->spliton = '';
        if (isset($theblock->config) && isset($theblock->config->spliton)) {
            $template->spliton = $theblock->config->spliton;
        }

        $template->helpiconconfigsums = $this->output->help_icon('configsums', 'block_dashboard');
        $template->strconfigenablehorizsums = get_string('configenablehorizsums', 'block_dashboard');

        if (!empty($theblock->config->horizsums)) {
            $template->horizsumschecked = 'checked="checked"';
        } else {
            $template->horizsumsunchecked = 'checked="checked"';
        }

        $template->strconfigenablevertsums = get_string('configenablevertsums', 'block_dashboard');

        $vertsumsyeschecked = '';
        $vertsumsnochecked = ' checked=\"checked\" ';
        if (!empty($theblock->config->vertsums)) {
            $template->vertsumschecked = 'checked="checked"';
        } else {
            $template->vertsumsunchecked = 'checked="checked"';
        }

        $template->strconfigverticallabels = get_string('configverticallabels', 'block_dashboard');

        $template->verticallabels = '';
        if (isset($theblock->config) && isset($theblock->config->verticallabels)) {
            $template->verticallabels = $theblock->config->verticallabels;
        }

        $template->strconfighorizlabel = get_string('confighorizlabel', 'block_dashboard');

        $template->horizlabel = '';
        if (isset($theblock->config) && isset($theblock->config->horizlabel)) {
            $template->horizlabel = $theblock->config->horizlabel;
        }

        return $this->render_from_template('block_dashboard/tabularoutput', $template);
    }

    public function graph_params() {

        $theblock = $this->theblock;

        $template = new StdClass;

        $template->strgraphparams = get_string('graphparams', 'block_dashboard');
        $template->strconfigreminderonsep = get_string('configreminderonsep', 'block_dashboard');

        $template->helpiconconfigxaxis = $this->output->help_icon('configxaxis', 'block_dashboard');
        $template->strconfigxaxisfield = get_string('configxaxisfield', 'block_dashboard');

        $template->xaxisfield = '';
        if (isset($theblock->config) && isset($theblock->config->xaxisfield)) {
            $template->xaxisfield = $theblock->config->xaxisfield;
        }

        $template->strconfigxaxislabel = get_string('configxaxislabel', 'block_dashboard');

        $template->xaxislabel = '';
        if (isset($theblock->config) && isset($theblock->config->xaxislabel)) {
            $template->xaxislabel = $theblock->config->xaxislabel;
        }

        $template->helpiconconfigyseries = $this->output->help_icon('configyseries', 'block_dashboard');
        $template->strconfigyseries = get_string('configyseries', 'block_dashboard');

        $template->yseries = '';
        if (isset($theblock->config) && isset($theblock->config->yseries)) {
            $template->yseries = $theblock->config->yseries;
        }
        $template->helpiconconfigformatting = $this->output->help_icon('configformatting', 'block_dashboard');
        $template->strconfigyseriesformats = get_string('configyseriesformats', 'block_dashboard');

        $template->yseriesformats = '';
        if (isset($theblock->config) && isset($theblock->config->yseriesformats)) {
            $template->yseriesformats = $theblock->config->yseriesformats;
        }

        $template->helpiconconfigexplicitscaling = $this->output->help_icon('configexplicitscaling', 'block_dashboard');
        $template->strconfigymin = get_string('configymin', 'block_dashboard');

        $template->ymin = '';
        if (isset($theblock->config) && isset($theblock->config->ymin)) {
            $template->ymin = $theblock->config->ymin;
        }

        $template->strconfigymax = get_string('configymax', 'block_dashboard');

        $template->ymax = '';
        if (isset($theblock->config) && isset($theblock->config->ymax)) {
            $template->ymax = $theblock->config->ymax;
        }

        $template->strconfigtickspacing = get_string('configtickspacing', 'block_dashboard');

        $template->tickspacing = '';
        if (isset($theblock->config) && isset($theblock->config->tickspacing)) {
            $template->tickspacing = $theblock->config->tickspacing;
        }

        $template->strconfigyaxisscale = get_string('configyaxisscale', 'block_dashboard');

        $template->yaxisscale = '';
        if (isset($theblock->config) && isset($theblock->config->yaxisscale)) {
            $template->yaxisscale = $theblock->config->yaxisscale;
        }

        $template->linearchecked = ($template->yaxisscale == 'linear' ) ? 'checked ="checked"' : '';
        $template->logchecked = ($template->yaxisscale == 'log') ? 'checked ="checked"' : '';
        $template->strlinear = get_string('linear', 'block_dashboard');
        $template->strlog = get_string('log', 'block_dashboard');

        $template->helpiconconfigserieslabels = $this->output->help_icon('configserieslabels', 'block_dashboard');
        $template->strconfigserieslabels = get_string('configserieslabels', 'block_dashboard');

        $template->serieslabels = '';
        if (isset($theblock->config) && isset($theblock->config->serieslabels)) {
            $template->serieslabels = $theblock->config->serieslabels;
        }

        $template->strconfigyaxislabel = get_string('configyaxislabel', 'block_dashboard');

        $template->yaxislabel = '';
        if (isset($theblock->config) && isset($theblock->config->yaxislabel)) {
            $template->yaxislabel = $theblock->config->yaxislabel;
        }

        $template->strconfigyaxistickangle = get_string('configyaxistickangle', 'block_dashboard');

        $template->yaxistickangle = '';
        if (isset($theblock->config) && isset($theblock->config->yaxistickangle)) {
            $template->yaxistickangle = $theblock->config->yaxistickangle;
        }
        $template->yaxistickanglechecked1 = ($template->yaxistickangle == '-45') ? 'checked ="checked"' : '';
        $template->yaxistickanglechecked2 = ($template->yaxistickangle == '0') ? 'checked ="checked"' : '';
        $template->yaxistickanglechecked3 = ($template->yaxistickangle == '45') ? 'checked ="checked"' : '';
        $template->yaxistickanglechecked4 = ($template->yaxistickangle == '90') ? 'checked ="checked"' : '';

        $template->strconfiggraphtype = get_string('configgraphtype', 'block_dashboard');

        $graphtypes = array(
            'line' => get_string('line', 'block_dashboard'), 
            'bar' => get_string('bar', 'block_dashboard'), 
            'pie' => get_string('pie', 'block_dashboard'), 
            'donut' => get_string('donut', 'block_dashboard'),
            'timegraph' => get_string('timegraph', 'block_dashboard'),
            'timeline' => get_string('timeline', 'block_dashboard'),
        );

        if (block_dashboard_supports_feature('graph/google')) {
            $graphtypes['googlemap'] = get_string('googlemap', 'block_dashboard');
        }

        $graphtype = 0;
        if (isset($theblock->config) && isset($theblock->config->graphtype)) {
            $graphtype = $theblock->config->graphtype;
        }
        $template->graphtypeselect = html_writer::select($graphtypes, 'graphtype', $graphtype);

        $template->strconfiggraphwidth = get_string('configgraphwidth', 'block_dashboard');

        $template->graphwidth = 450;
        if (isset($theblock->config) && isset($theblock->config->graphwidth)) {
            $template->graphwidth = $theblock->config->graphwidth;
        }

        $template->strconfiggraphheight = get_string('configgraphheight', 'block_dashboard');

        $template->graphheight = 250;
        if (isset($theblock->config) && isset($theblock->config->graphheight)) {
            $template->graphheight = $theblock->config->graphheight;
        }

        $template->strconfigshowlegend = get_string('configshowlegend', 'block_dashboard');

        if (!empty($theblock->config->showlegend)) {
            $template->showlegendchecked = 'checked="checked"';
        } else {
            $template->showlegendunchecked = 'checked="checked"';
        }

        $template->stryes = get_string('yes');
        $template->strno = get_string('no');

        return $this->render_from_template('block_dashboard/graphparams', $template);
    }

    public function sums_and_filters() {

        $theblock = $this->theblock;

        $template = new StdClass;

        $template->strsummatorsparams = get_string('sumsandfiltersparams', 'block_dashboard');

        // Filters.

        $template->strfilters = get_string('filters', 'block_dashboard');
        $template->helpiconconfigfilters = $this->output->help_icon('configfilters', 'block_dashboard');
        $template->strconfigfilters = get_string('configfilters', 'block_dashboard');

        $filters = '';
        if (isset($theblock->config) && isset($theblock->config->filters)) {
            $filters = $theblock->config->filters;
        }
        $template->filters = $filters;

        $template->strconfigfilterlabels = get_string('configfilterlabels', 'block_dashboard');

        $filterlabels = '';
        if (isset($theblock->config) && isset($theblock->config->filterlabels)) {
            $filterlabels = $theblock->config->filterlabels;
        }
        $template->filterlabels = $filterlabels;

        $template->helpiconconfigfilterdefaults = $this->output->help_icon('configfilterdefaults', 'block_dashboard');
        $template->strconfigfilterdefaults = get_string('configfilterdefaults', 'block_dashboard');

        $filterdefaults = '';
        if (isset($theblock->config) && isset($theblock->config->filterdefaults)) {
            $filterdefaults = $theblock->config->filterdefaults;
        }
        $template->filterdefaults = $filterdefaults;

        $template->helpiconconfigfilteroptions = $this->output->help_icon('configfilteroptions', 'block_dashboard');
        $template->strconfigfilteroptions = get_string('configfilteroptions', 'block_dashboard');

        $filteroptions = '';
        if (isset($theblock->config) && isset($theblock->config->filteroptions)) {
            $filteroptions = $theblock->config->filteroptions;
        }
        $template->filteroptions = $filteroptions;

        // Sums.

        $template->strsums = get_string('sums', 'block_dashboard');
        $template->strconfigreminderonsep = get_string('configreminderonsep', 'block_dashboard');

        $template->helpiconconfigsummators = $this->output->help_icon('configsummators', 'block_dashboard');
        $template->strconfignumsums = get_string('confignumsums', 'block_dashboard');

        if (isset($theblock->config) && isset($theblock->config->numsums)) {
            $template->numsums = $theblock->config->numsums;
        }

        if (isset($theblock->config) && isset($theblock->config->numsumformats)) {
            $template->numsumformats = $theblock->config->numsumformats;
        }

        $template->helpiconconfigformatting = $this->output->help_icon('configformatting', 'block_dashboard');
        $template->strconfignumsumsformats = get_string('confignumsumsformats', 'block_dashboard');

        $template->strconfignumsumslabels = get_string('confignumsumslabels', 'block_dashboard');

        if (isset($theblock->config) && isset($theblock->config->numsumlabels)) {
            $template->numsumlabels = $theblock->config->numsumlabels;
        }

        return $this->render_from_template('block_dashboard/sumsandfilters', $template);
    }

    public function data_refresh() {

        $theblock = $this->theblock;

        $template = new StdClass;

        $template->strdatarefresh = get_string('datarefresh', 'block_dashboard');
        $template->helpiconconfigdelayedrefresh = $this->output->help_icon('configdelayedrefresh', 'block_dashboard');
        $template->strconfigcronmode = get_string('configcronmode', 'block_dashboard');

        if (isset($theblock->config) && isset($theblock->config->cronmode)) {
            $cronmode = $theblock->config->cronmode;
        } else {
            $cronmode = 'norefresh';
        }

        $modes['norefresh'] = get_string('norefresh', 'block_dashboard');
        $modes['global'] = get_string('globalcron', 'block_dashboard');
        $modes['instance'] = get_string('instancecron', 'block_dashboard');
        $template->cronmodeselect = html_writer::select($modes, 'cronmode', $cronmode);

        $template->strconfigcrontime = get_string('configcrontime', 'block_dashboard');

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
        $template->cronhourselect = html_writer::select($hours, 'cronhour', $cronhour);
        $template->strhours = get_string('hours', 'block_dashboard');

        $template->cronminselect = html_writer::select($mins, 'cronmin', $cronmin);
        $template->strmins = get_string('mins', 'block_dashboard');

        $template->strconfigsendadminnotification = get_string('configsendadminnotification', 'block_dashboard');

        if (!empty($theblock->config->cronadminnotifications)) {
            $template->cronadminnotificationschecled = 'checked="checked"';
        }

        $template->strenabled = get_string('enabled', 'block_dashboard');

        $template->strconfigcronfrequency = get_string('configcronfrequency', 'block_dashboard');

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

        $template->cronfrequencyselect = html_writer::select($freq, 'cronfrequency', $cronfrequency, '');

        return $this->render_from_template('block_dashboard/datarefresh', $template);
    }

    public function file_output() {

        $theblock = $this->theblock;

        $template = new StdClass;

        $template->strfileoutput = get_string('fileoutput', 'block_dashboard');
        $template->stryes = get_string('yes');
        $template->strno = get_string('no');

        $helpiconconfigmakefile = $this->output->help_icon('configmakefile', 'block_dashboard');
        $template->strconfigmakefile = get_string('configmakefile', 'block_dashboard');

        $template->makefilechecked = '';
        if (isset($theblock->config) && isset($theblock->config->makefile)) {
            $template->makefilechecked = 'checked="checked"';
            $theblock->config->makefile = true;
        } else {
            $template->makefileunchecked = 'checked="checked"';
            $theblock->config->makefile = false;
        }

        $template->helpiconconfigfileoutput = $this->output->help_icon('configfileoutput', 'block_dashboard');
        $template->strconfigfileoutput = get_string('configfileoutput', 'block_dashboard');

        $template->fileoutput = '';
        if (isset($theblock->config) && isset($theblock->config->fileoutput)) {
            $template->fileoutput = $theblock->config->fileoutput;
        }

        $template->helpiconconfigfileheaders = $this->output->help_icon('configfileheaders', 'block_dashboard');
        $template->strconfigfileheaders = get_string('configfileheaders', 'block_dashboard');

        $template->fileheaders = '';
        if (isset($theblock->config) && isset($theblock->config->fileheaders)) {
            $template->fileheaders = $theblock->config->fileheaders;
        }

        $template->helpiconconfigformatting = $this->output->help_icon('configformatting', 'block_dashboard');
        $template->strconfigfileoutputformats = get_string('configfileoutputformats', 'block_dashboard');

        $template->fileoutputformats = '';
        if (isset($theblock->config) && isset($theblock->config->fileoutputformats)) {
            $template->fileoutputformats = $theblock->config->fileoutputformats;
        }

        $template->strconfigfileformat = get_string('configfileformat', 'block_dashboard');

        $fileformat = 'CSV';
        if (isset($theblock->config) && isset($theblock->config->fileformat)) {
            $fileformat = $theblock->config->fileformat;
        }
        $fileformats = array(
            'CSV' => get_string('csv', 'block_dashboard'),
            'CSVWH' => get_string('csvwithoutheader', 'block_dashboard'),
            'SQL' => get_string('sqlinserts', 'block_dashboard'));

        $template->helpiconconfigfilesqlouttable = $this->output->help_icon('configfilesqlouttable', 'block_dashboard');
        $template->strconfigfilesqlouttable = get_string('configfilesqlouttable', 'block_dashboard');

        $template->selectfileformats = html_writer::select($fileformats, 'fileformat', $fileformat);

        $template->filesqlouttable = '';
        if (isset($theblock->config) && isset($theblock->config->filesqlouttable)) {
            $template->filesqlouttable = $theblock->config->filesqlouttable;
        }

        $template->helpiconconfigfilelocation = $this->output->help_icon('configfilelocation', 'block_dashboard');
        $template->strconfigfilelocation = get_string('configfilelocation', 'block_dashboard');

        $template->filelocation = '';
        if (isset($theblock->config) && isset($theblock->config->filelocation)) {
            $template->filelocation = $theblock->config->filelocation;
        }
        $template->horodatefileschecked = (@$theblock->config->horodatefiles) ? 'checked="checked"' : '' ;
        $template->helpiconconfighorodatefiles = $this->output->help_icon('confighorodatefiles', 'block_dashboard');
        $template->strconfighorodatefiles = get_string('confighorodatefiles', 'block_dashboard');

        if (has_capability('moodle/site:config', \context_system::instance())) {

            $template->filepathadminoverride = '';
            if (isset($theblock->config) && isset($theblock->config->filepathadminoverride)) {
                $template->filepathadminoverride = $theblock->config->filepathadminoverride;
            }

            $template->strconfigfilepathadminoverride = get_string('configfilepathadminoverride', 'block_dashboard'); 
            $template->helpiconconfigfilepathadminoverride = $this->output->help_icon('configfilepathadminoverride', 'block_dashboard');
        }

        $template->isadmin = has_capability('moodle/site:config', \context_system::instance());

        return $this->render_from_template('block_dashboard/fileoutputparams', $template);
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