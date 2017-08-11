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
<<<<<<< HEAD
    	global $CFG, $COURSE, $OUTPUT;
=======
        global $CFG, $COURSE, $OUTPUT;
>>>>>>> MOODLE_33_STABLE

        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_dashboard'));
        $mform->setType('config_title', PARAM_MULTILANG);
        $mform->setDefault('config_title', get_string('newdashboard', 'block_dashboard'));

        $mform->addElement('checkbox', 'config_hidetitle', '', get_string('checktohide', 'block_dashboard'));

        // Layout settings ---------------------------------------------------------------.

        $mform->addElement('header', 'configheader1', get_string('dashboardlayout', 'block_dashboard'));

        $layoutopts[0] = get_string('publishinpage', 'block_dashboard');
        $layoutopts[1] = get_string('publishinblock', 'block_dashboard');
        $mform->addElement('select', 'config_inblocklayout', get_string('configlayout', 'block_dashboard'), $layoutopts);

<<<<<<< HEAD
		/** Display settings **

        $mform->addElement('header', 'configheader2', get_string('configdisplay', 'block_dashboard'));

        $mform->addElement('selectyesno', 'config_showdata', get_string('configshowdata', 'block_dashboard'));
        $mform->setDefault('config_showdata', 1);
        $mform->setType('config_showdata', PARAM_BOOL);
        $mform->addElement('selectyesno', 'config_showgraph', get_string('configshowgraph', 'block_dashboard'));
        $mform->setDefault('config_showgraph', 1);
        $mform->setType('config_showgraph', PARAM_BOOL);
        $mform->addElement('selectyesno', 'config_shownumsums', get_string('configshownumsums', 'block_dashboard'));
        $mform->setDefault('config_shownumsums', 1);
        $mform->setType('config_shownumsums', PARAM_BOOL);
        $mform->addElement('selectyesno', 'config_showquery', get_string('configshowquery', 'block_dashboard'));
        $mform->setType('config_showquery', PARAM_BOOL);
        $mform->addElement('selectyesno', 'config_showfilterqueries', get_string('configshowfilterqueries', 'block_dashboard'));
        $mform->setType('config_showfilterqueries', PARAM_BOOL);

		/** Query definition **

        $mform->addElement('header', 'configheader3', get_string('querydesc', 'block_dashboard'));
        
		$targets = array('moodle' => 'Moodle', 'extra' => 'Extra DB');
        $mform->addElement('select', 'config_target', get_string('configtarget', 'block_dashboard'), $targets);

        $mform->addElement('textarea', 'config_query', get_string('configquery', 'block_dashboard'), array('cols' => 100, 'rows' => 20));
		$mform->addHelpButton('config_query', 'configquery', 'block_dashboard');

        $mform->addElement('header', 'configheader4', get_string('outputparams', 'block_dashboard'));

        $mform->addElement('html', get_string('configreminderonsep', 'block_dashboard'));

        $mform->addElement('text', 'config_outputfields', get_string('configoutputfields', 'block_dashboard'), array('size' => 60));
		$mform->addHelpButton('config_outputfields', 'configoutputfields', 'block_dashboard');
        $mform->addElement('text', 'config_outputformats', get_string('configoutputformats', 'block_dashboard'), array('size' => 60));
		$mform->addHelpButton('config_outputformats', 'configformatting', 'block_dashboard');
        $mform->addElement('text', 'config_outputfieldlabels', get_string('configoutputfieldslabels', 'block_dashboard'), array('size' => 60));
        
        $group = array();
        $group[0] = $mform->createElement('text', 'config_pagesize', get_string('configpagesize', 'block_dashboard'), array('size' => 8));
        $group[1] = $mform->createElement('checkbox', 'config_bigresult', '', get_string('configbigresult', 'block_dashboard'));
        $mform->addGroup($group, 'group1', get_string('configpagesize', 'block_dashboard'), '&nbsp;', false);
		$mform->addHelpButton('group1', 'configbigresult', 'block_dashboard');

		/** Query params options **

        $mform->addElement('header', 'configheader45', get_string('queryparams', 'block_dashboard'));

        $headgroup = array();
        $headgroup[0] = &$mform->createElement('html', '<img src="'.$OUTPUT->pix_url('spacer').'" style="width:120px;height:1px" /><b>'.get_string('sqlparamvar', 'block_dashboard').'</b>');
        $headgroup[1] = &$mform->createElement('html', '<img src="'.$OUTPUT->pix_url('spacer').'" style="width:100px;height:1px" /><b>'.get_string('sqlparamlabel', 'block_dashboard').'</b>');
        $headgroup[2] = &$mform->createElement('html', '<img src="'.$OUTPUT->pix_url('spacer').'" style="width:100px;height:1px" /><b>'.get_string('sqlparamtype', 'block_dashboard').'</b>');
        $headgroup[3] = &$mform->createElement('html', '<img src="'.$OUTPUT->pix_url('spacer').'" style="width:100px;height:1px" /><b>'.get_string('sqlparamvalues', 'block_dashboard').'</b>');
        $mform->addGroup($headgroup, 'group15', '', '', false);

		$typeopts = array('choice' => get_string('choicevalue', 'block_dashboard'),
					  'text' => get_string('textvalue', 'block_dashboard'),
					  'select' => get_string('listvalue', 'block_dashboard'),
					  'range' => get_string('rangevalue', 'block_dashboard'),
					  'date' => get_string('datevalue', 'block_dashboard'),
					  'daterange' => get_string('daterangevalue', 'block_dashboard'),
					  );

        $groupitems = array();
        $groupitems[0] = &$mform->createElement('text', 'sqlparamvar', get_string('sqlparamvar', 'block_dashboard'), '', array('size' => 15));
        $groupitems[1] = &$mform->createElement('text', 'sqlparamlabel', get_string('sqlparamlabel', 'block_dashboard'), array('size' => 15));
        $groupitems[2] = &$mform->createElement('select', 'sqlparamtype', get_string('sqlparamtype', 'block_dashboard'), $typeopts);
        $groupitems[3] = &$mform->createElement('textarea', 'sqlparamvalues', '', array('rows' => 5, 'cols' => '15'));
		$repeatarray[] = &$mform->createElement('group', 'sqlparams', '', $groupitems, false);

		$mform->setType('sqlparamvar', PARAM_ALPHANUM);    	
		$mform->setType('sqlparamlabel', PARAM_TEXT);    	
		$mform->setType('sqlparamvar', PARAM_ALPHANUM);    	

		$repeateloptions = array();
		$this->repeat_elements($repeatarray, 5, $repeateloptions, 'option_repeats', 'option_add_fields', 1);        

		/** Data filtering options **

        $mform->addElement('header', 'configheader5', get_string('configfilters', 'block_dashboard'));

        $mform->addElement('text', 'config_filters', get_string('configfilters', 'block_dashboard'), array('size' => 60));
		$mform->addHelpButton('config_filters', 'configfilters', 'block_dashboard');
        $mform->addElement('text', 'config_filterlabels', get_string('configfilterlabels', 'block_dashboard'), array('size' => 60));
        $mform->addElement('text', 'config_filterdefaults', get_string('configfilterdefaults', 'block_dashboard'), array('size' => 60));
		$mform->addHelpButton('config_filterdefaults', 'configfilterdefaults', 'block_dashboard');
        $mform->addElement('text', 'config_filteroptions', get_string('configfilteroptions', 'block_dashboard'), array('size' => 60));
		$mform->addHelpButton('config_filteroptions', 'configfilteroptions', 'block_dashboard');

        $mform->addElement('header', 'configheader6', get_string('configcache', 'block_dashboard'));

        $mform->addElement('selectyesno', 'config_uselocalcaching', get_string('configcaching', 'block_dashboard'));
		$mform->addHelpButton('config_uselocalcaching', 'configcaching', 'block_dashboard');
        $mform->addElement('text', 'config_cachingttl', get_string('configcachingttl', 'block_dashboard'), array('size' => 10));

		/** General table options **

        $mform->addElement('header', 'configheader7', get_string('configtable', 'block_dashboard'));
        $tabletypeopts['linear'] = get_string('linear', 'block_dashboard');
        $tabletypeopts['tabular'] = get_string('tabular', 'block_dashboard');
        $tabletypeopts['treeview'] = get_string('treeview', 'block_dashboard');
        $mform->addElement('select', 'config_tabletype', get_string('configtabletype', 'block_dashboard'), $tabletypeopts);
		$mform->addHelpButton('config_tabletype', 'configtabletype', 'block_dashboard');
		
		/** Linear table options **

        $mform->addElement('header', 'configheader8', get_string('configlineartable', 'block_dashboard'));
        $mform->addElement('selectyesno', 'config_cleandisplay', get_string('configcleandisplay', 'block_dashboard'));
        $mform->addElement('selectyesno', 'config_sortable', get_string('configsortable', 'block_dashboard'));
        $mform->addElement('text', 'config_splitsumonsort', get_string('configsplitsumonsort', 'block_dashboard'));
		$mform->addHelpButton('config_splitsumonsort', 'configsplitsumonsort', 'block_dashboard');

		/** Tabular table options **

        $mform->addElement('header', 'configheader9', get_string('configtabulartable', 'block_dashboard'));
		$mform->addHelpButton('configheader9', 'configtabular', 'block_dashboard');
        $mform->addElement('text', 'config_verticalkeys', get_string('configverticalkeys', 'block_dashboard'), array('size' => 60));
        $mform->addElement('text', 'config_verticallabels', get_string('configverticallabels', 'block_dashboard'), array('size' => 60));
        $mform->addElement('text', 'config_verticalformats', get_string('configverticalformats', 'block_dashboard'), array('size' => 60));
		$mform->addHelpButton('config_verticalformats', 'configformatting', 'block_dashboard');
        $mform->addElement('text', 'config_horizkey', get_string('confighorizkey', 'block_dashboard'), array('size' => 10));
        $mform->addElement('text', 'config_horizlabel', get_string('confighorizlabel', 'block_dashboard'), array('size' => 20));
        $mform->addElement('text', 'config_horizformat', get_string('confighorizformat', 'block_dashboard'), array('size' => 10));
		$mform->addHelpButton('config_horizformat', 'configformatting', 'block_dashboard');
        $mform->addElement('text', 'config_spliton', get_string('configspliton', 'block_dashboard'), array('size' => 10));
		$mform->addHelpButton('config_spliton', 'configspliton', 'block_dashboard');
        $mform->addElement('selectyesno', 'config_horizsums', get_string('configenablehorizsums', 'block_dashboard'));
		$mform->addHelpButton('config_horizsums', 'configsums', 'block_dashboard');
        $mform->addElement('selectyesno', 'config_vertsums', get_string('configenablevertsums', 'block_dashboard'));
		$mform->addHelpButton('config_vertsums', 'configsums', 'block_dashboard');

        $mform->addElement('header', 'configheader10', get_string('configtreeview', 'block_dashboard'));
        $mform->addElement('text', 'config_parentserie', get_string('configparentserie', 'block_dashboard'), array('size' => 10));
		$mform->addHelpButton('config_parentserie', 'configparentserie', 'block_dashboard');
        $mform->addElement('text', 'config_treeoutput', get_string('configtreeoutput', 'block_dashboard'), array('size' => 60));
		$mform->addHelpButton('config_treeoutput', 'configtreeoutput', 'block_dashboard');
        $mform->addElement('text', 'config_treeoutputformats', get_string('configtreeoutputformats', 'block_dashboard'), array('size' => 60));
		$mform->addHelpButton('config_treeoutputformats', 'configformatting', 'block_dashboard');

        $mform->addElement('header', 'configheader16', get_string('tablecolormapping', 'block_dashboard'));
		$group3[] = $mform->createElement('textarea', 'config_colors', '', ' rows="10" cols="20" ');
		$group3[] = $mform->createElement('text', 'config_colorfield', get_string('timelinecolorfield', 'block_dashboard'), array('size' => 15));
		$group3[] = $mform->createElement('textarea', 'config_coloredfield', '', ' rows="10" cols="20" ');
		$mform->addGroup($group3, 'group3', get_string('tablecolormapping', 'block_dashboard'), '&nbsp;&nbsp;', false);
		$mform->addHelpButton('group3', 'configcolouring', 'block_dashboard');

        $mform->addElement('header', 'configheader11', get_string('graphparams', 'block_dashboard'));
        $mform->addElement('html', get_string('configreminderonsep', 'block_dashboard'));

        $graphtypes = array(
        	'line' => get_string('line', 'block_dashboard'), 
        	'bar' => get_string('bar', 'block_dashboard'), 
        	'pie' => get_string('pie', 'block_dashboard'), 
        	'donut' => get_string('donut', 'block_dashboard'),
        	'timegraph' => get_string('timegraph', 'block_dashboard'),
        	'timeline' => get_string('timeline', 'block_dashboard'),
        	'googlemap' => get_string('googlemap', 'block_dashboard'),
        );
        $mform->addElement('select', 'config_graphtype', get_string('configgraphtype', 'block_dashboard'), $graphtypes);
        $mform->addElement('text', 'config_graphheight', get_string('configgraphheight', 'block_dashboard'), array('size' => 10));
        $mform->addElement('text', 'config_graphwidth', get_string('configgraphwidth', 'block_dashboard'), array('size' => 10));

		/** JQPlotted graphs settings **

        $mform->addElement('header', 'configheader12', get_string('plotgraphparams', 'block_dashboard'));
        $mform->addElement('text', 'config_xaxisfield', get_string('configxaxisfield', 'block_dashboard'), array('size' => 10));
		$mform->addHelpButton('config_xaxisfield', 'configxaxisfield', 'block_dashboard');
        $mform->addElement('text', 'config_xaxislabel', get_string('configxaxislabel', 'block_dashboard'), array('size' => 10));
		$yaxistickangle[-45] = '-45';
		$yaxistickangle[0] = '-45';
		$yaxistickangle[45] = '45';
		$yaxistickangle[90] = '90';
        $mform->addElement('select', 'config_yaxistickangle', get_string('configyaxistickangle', 'block_dashboard'), $yaxistickangle);
        $mform->addElement('text', 'config_yseries', get_string('configyseries', 'block_dashboard'), array('size' => 60));
        $mform->addElement('text', 'config_serieslabels', get_string('configserieslabels', 'block_dashboard'), array('size' => 60));
        $mform->addElement('text', 'config_yseriesformats', get_string('configyseriesformats', 'block_dashboard'), array('size' => 60));
        $mform->addElement('text', 'config_yaxislabel', get_string('configyaxislabel', 'block_dashboard'), array('size' => 10));
		$mform->addHelpButton('config_yseriesformats', 'configformatting', 'block_dashboard');
        $group1[] = $mform->createElement('text', 'config_ymin', get_string('configymin', 'block_dashboard'), array('size' => 6));
        $group1[] = $mform->createElement('text', 'config_ymax', get_string('configymax', 'block_dashboard'), array('size' => 6));
        $mform->addGroup($group1, 'group1', get_string('configyaxisbounds', 'block_dashboard'), '&nbsp;', false);
		$mform->addHelpButton('group1', 'configexplicitscaling', 'block_dashboard');
        $mform->addElement('text', 'config_tickspacing', get_string('configtickspacing', 'block_dashboard'), array('size' => 6));
        $mform->addElement('selectyesno', 'config_showlegend', get_string('configshowlegend', 'block_dashboard'));

		/** Google map graphs **

        $mform->addElement('header', 'configheader13', get_string('googleparams', 'block_dashboard'));
		$mform->addHelpButton('configheader13', 'configgmdata', 'block_dashboard');

    	$maptypeopts = array(
    		'ROADMAP' => get_string('maptyperoadmap', 'block_dashboard'),
    		'SATELLITE' => get_string('maptypesatellite', 'block_dashboard'),
    		'HYBRID' => get_string('maptypehybrid', 'block_dashboard'),
    		'TERRAIN' => get_string('maptypeterrain', 'block_dashboard'),
    	);
        $mform->addElement('select', 'config_maptype', get_string('configmaptype', 'block_dashboard'), $maptypeopts);
        $mform->addElement('text', 'config_datatitles', get_string('configdatatitles', 'block_dashboard'), array('size' => 40));
        $mform->addElement('text', 'config_datalocations', get_string('configdata', 'block_dashboard'), array('size' => 40));
        $mform->addElement('text', 'config_datatypes', get_string('configdatatypes', 'block_dashboard'), array('size' => 40));
        $mform->addElement('text', 'config_zoom', get_string('configzoom', 'block_dashboard'), array('size' => 10));
		$mform->addHelpButton('config_zoom', 'configzoom', 'block_dashboard');
		$group2[] = $mform->createElement('text', 'config_lat', get_string('configlat', 'block_dashboard'), array('size' => 6));
		$group2[] = $mform->createElement('text', 'config_lng', get_string('configlng', 'block_dashboard'), array('size' => 6));
		$mform->addGroup($group2, 'locationgroup', get_string('configlocation', 'block_dashboard'), '&nbsp;', false);
		$mform->addHelpButton('locationgroup', 'configlocation', 'block_dashboard');

		/** Timeline plotted graphs **

        $mform->addElement('header', 'configheader14', get_string('timelineparams', 'block_dashboard'));
        $mform->addElement('selectyesno', 'config_showlowerband', get_string('configshowlowerband', 'block_dashboard'));

        $upperunits = array('MONTH' => get_string('month', 'block_dashboard'),
        	'WEEK' => get_string('week', 'block_dashboard'),
        	'DAY' => get_string('day', 'block_dashboard'),
        	'HOUR' => get_string('hour', 'block_dashboard'));
        $mform->addElement('select', 'config_upperbandunit', get_string('configupperbandunit', 'block_dashboard'), $upperunits);

        $lowerunits = array('YEAR' => get_string('year', 'block_dashboard'),
        	'MONTH' => get_string('month', 'block_dashboard'),
        	'WEEK' => get_string('week', 'block_dashboard'),
        	'DAY' => get_string('day', 'block_dashboard'));
        $mform->addElement('select', 'config_lowerbandunit', get_string('configlowerbandunit', 'block_dashboard'), $lowerunits);

		$mform->addElement('text', 'config_eventtitles', get_string('eventtitles', 'block_dashboard'), array('size' => 15));
		$mform->addElement('text', 'config_eventstart', get_string('eventstart', 'block_dashboard'), array('size' => 15));
		$mform->addElement('text', 'config_eventend', get_string('eventend', 'block_dashboard'), array('size' => 15));
		$mform->addElement('text', 'config_eventlink', get_string('eventlink', 'block_dashboard'), array('size' => 15));
		$mform->addElement('text', 'config_eventdesc', get_string('eventdesc', 'block_dashboard'), array('size' => 15));

		$group4[] = $mform->createElement('textarea', 'config_timelinecolors', '', ' rows="10", cols="20" ');
		$group4[] = $mform->createElement('text', 'config_timelinecolorfield', get_string('timelinecolorfield', 'block_dashboard'), array('size' => 15));
		$group4[] = $mform->createElement('textarea', 'config_timelinecolorfield', '', ' rows="10", cols="20" ');
		$mform->addGroup($group4, 'group4', get_string('configtimelinecolouring', 'block_dashboard'), '&nbsp;&nbsp;', false);
		$mform->addHelpButton('group4', 'configcolouring', 'block_dashboard');

		/** Global summators settins **

        $mform->addElement('header', 'configheader14', get_string('summatorsparams', 'block_dashboard'));

		$mform->addElement('text', 'config_numsums', get_string('confignumsums', 'block_dashboard'), array('size' => 40));
		$mform->addElement('text', 'config_numsumsformats', get_string('confignumsumsformats', 'block_dashboard'), array('size' => 40));
		$mform->addHelpButton('config_numsumsformats', 'configformatting', 'block_dashboard');
		$mform->addElement('text', 'config_numsumslabels', get_string('confignumsumslabels', 'block_dashboard'), array('size' => 40));

        $mform->addElement('header', 'configheader17', get_string('datarefresh', 'block_dashboard'));

        $cronmodes['norefresh'] = get_string('norefresh', 'block_dashboard');
        $cronmodes['global'] = get_string('globalcron', 'block_dashboard');
        $cronmodes['instance'] = get_string('instancecron', 'block_dashboard');
        $mform->addElement('select', 'config_cronmode', get_string('configcronmode', 'block_dashboard'), $cronmodes);

        $mform->addElement('text', 'config_cronhour', get_string('configcronhour', 'block_dashboard'), array('size' => 4));
        $mform->addElement('text', 'config_cronmin', get_string('configcronmin', 'block_dashboard'), array('size' => 4));

		$freq['daily'] = get_string('daily', 'block_dashboard');
		$freq['0'] = get_string('sunday', 'block_dashboard');
		$freq['1'] = get_string('monday', 'block_dashboard');
		$freq['2'] = get_string('tuesday', 'block_dashboard');
		$freq['3'] = get_string('wednesday', 'block_dashboard');
		$freq['4'] = get_string('thursday', 'block_dashboard');
		$freq['5'] = get_string('friday', 'block_dashboard');
		$freq['6'] = get_string('saturday', 'block_dashboard');
        $mform->addElement('select', 'config_cronfrequency', get_string('configcronfrequency', 'block_dashboard'), $freq);

		/** File output settings **

        $mform->addElement('header', 'configheader18', get_string('fileoutput', 'block_dashboard'));

        $mform->addElement('selectyesno', 'config_makefile', get_string('configmakefile', 'block_dashboard'));
		$mform->addElement('text', 'config_fileoutput', get_string('configfileoutput', 'block_dashboard'), array('size' => 40));
		$mform->addElement('text', 'config_fileoutputformat', get_string('configfileoutputformats', 'block_dashboard'), array('size' => 40));
		$mform->addHelpButton('config_fileoutputformat', 'configformatting', 'block_dashboard');

        $fileformats = array(
        	'CSV' => get_string('csv', 'block_dashboard'),
        	'CSVWH' => get_string('csvwithoutheader', 'block_dashboard'),
        	'SQL' => get_string('sqlinserts', 'block_dashboard'));
        $mform->addElement('select', 'config_fileformat', get_string('configfileformat', 'block_dashboard'), $fileformats);
		$mform->addElement('text', 'config_filesqlouttable', get_string('configfilesqlouttable', 'block_dashboard'), array('size' => 20));

		$mform->addElement('text', 'config_filelocation', get_string('configfilelocation', 'block_dashboard'), array('size' => 20));
		$mform->addHelpButton('config_filelocation', 'configfilelocation', 'block_dashboard');
		if (has_capability('block/dashboard:systempathaccess', context_system::instance())){
			$mform->addElement('text', 'config_filepathadminoverride', get_string('configfilepathadminoverride', 'block_dashboard'), array('size' => 20));
			$mform->addHelpButton('config_filepathadminoverride', 'configfilelocationadmin', 'block_dashboard');
		}
		
		*/

        $mform->addElement('header', 'configheader20', get_string('configdahsboardparams', 'block_dashboard'));
		$generalparamsconfigstr = get_string('generalparams', 'block_dashboard');
		$generalparamslink = "<a href=\"{$CFG->wwwroot}/blocks/dashboard/setup.php?id={$COURSE->id}&amp;instance={$this->block->instance->id}\">$generalparamsconfigstr</a>";

		$mform->addElement('static', '', '', $generalparamslink);

        $mform->addElement('header', 'configheader19', get_string('configimportexport', 'block_dashboard'));
		$importconfigstr = get_string('importconfig', 'block_dashboard');
		$exportconfigstr = get_string('exportconfig', 'block_dashboard');
		$import_export = "<a href=\"{$CFG->wwwroot}/blocks/dashboard/copyconfig.php?id={$COURSE->id}&amp;instance={$this->block->instance->id}&amp;what=upload\">$importconfigstr</a> - 
      	<a href=\"{$CFG->wwwroot}/blocks/dashboard/copyconfig.php?id={$COURSE->id}&amp;instance={$this->block->instance->id}&amp;what=get\" target=\"_blank\">$exportconfigstr</a>";

		$mform->addElement('static', '', '', $import_export);
	}	
	
	function set_data($defaults){
		if (!empty($this->block->config)){
			if (!empty($this->block->config->sqlparams)){
				foreach($this->block->config->sqlparams as $paramid => $paramdef){
					print_object($paramdef);
					$varkey = "config_sqlparams[$paramid][sqlparamvar]";
					$defaults->$varkey = $paramdef['sqlparamvar'];
				}
			}
		}
		parent::set_data($defaults);
	}
=======
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
        }
        parent::set_data($defaults);
    }
>>>>>>> MOODLE_33_STABLE
}