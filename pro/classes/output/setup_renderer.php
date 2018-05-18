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

require_once($CFG->dirroot.'/blocks/dashboard/classes/output/setup_renderer.php');

use \StdClass;
use \html_writer;
use \moodle_url;
use \tabobject;

defined('MOODLE_INTERNAL') || die();

class setup_pro_renderer extends setup_renderer {

    public function google_params() {

        $theblock = $this->theblock;

        $template = new StdClass();

        $template->strgoogleparams = get_string('googleparams', 'block_dashboard');

        $template->helpiconconfigmaptype = $this->output->help_icon('configmaptype', 'block_dashboard');
        $template->strconfigmaptype = get_string('configmaptype', 'block_dashboard');

        $template->helpiconconfigzoom = $this->output->help_icon('configzoom', 'block_dashboard');
        $template->strconfigzoom = get_string('configzoom', 'block_dashboard');

        $template->helpiconconfiggmdata = $this->output->help_icon('configgmdata', 'block_dashboard');
        $template->strconfigdata = get_string('configdata', 'block_dashboard');

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
        $template->maptypeselect = html_writer::select($maptypeopts, 'maptype', $maptype);

        $template->zoom = '6';
        if (isset($theblock->config) && isset($theblock->config->zoom)) {
            $template->zoom = $theblock->config->zoom;
        }

        if (isset($theblock->config) && isset($theblock->config->datatitles)) {
            $template->datatitles = $theblock->config->datatitles;
        }

        if (isset($theblock->config) && isset($theblock->config->datalocations)) {
            $template->datalocations = $theblock->config->datalocations;
        }

        if (isset($theblock->config) && isset($theblock->config->datatypes)) {
            $template->datatypes = $theblock->config->datatypes;
        }

        $template->strdatatitles = get_string('datatitles', 'block_dashboard');
        $template->strdatalocations = get_string('datalocations', 'block_dashboard');
        $template->strdatatypes = get_string('datatypes', 'block_dashboard');

        $template->strconfiglocation = get_string('configlocation', 'block_dashboard');
        $template->lat = '0';
        $template->lng = '0';
        if (isset($theblock->config) && isset($theblock->config->lat)) {
            $template->lat = $theblock->config->lat;
        }
        if (isset($theblock->config) && isset($theblock->config->lng)) {
            $template->lng = $theblock->config->lng;
        }

        return $this->render_from_template('block_dashboard/googleparams', $template);
    }

    public function treeview_params() {

        $theblock = $this->theblock;

        $template = new StdClass();

        $template->strtreeviewparams = get_string('treeviewparams', 'block_dashboard');

        $template->helpiconconfighierarchic = $this->output->help_icon('confighierarchic', 'block_dashboard');
        $template->strconfigparent = get_string('configparent', 'block_dashboard');

        $template->parentserie = '';
        if (isset($theblock->config) && isset($theblock->config->parentserie)) {
            $template->parentserie = $theblock->config->parentserie;
        }

        $template->helpiconconfigtreeoutput = $this->output->help_icon('configtreeoutput', 'block_dashboard');
        $template->strconfigtreeoutput = get_string('configtreeoutput', 'block_dashboard');

        $template->treeoutput = '';
        if (isset($theblock->config) && isset($theblock->config->treeoutput)) {
            $template->treeoutput = $theblock->config->treeoutput;
        }

        $template->helpiconconfigformatting = $this->output->help_icon('configformatting', 'block_dashboard');
        $template->strconfigtreeoutputformats = get_string('configtreeoutputformats', 'block_dashboard');

        $template->treeoutputformats = '';
        if (isset($theblock->config) && isset($theblock->config->treeoutputformats)) {
            $template->treeoutputformats = $theblock->config->treeoutputformats;
        }

        return $this->render_from_template('block_dashboard/treeoutput', $template);
    }

    public function timeline_params() {

        $theblock = $this->theblock;

        $template = new StdClass();

        $template->str = get_string('timelineparams', 'block_dashboard');
        $template->helpiconconfigbands = $this->output->help_icon('configbands', 'block_dashboard');
        $template->strconfigshowlowerband = get_string('configshowlowerband', 'block_dashboard');

        if (!empty($theblock->config->showlowerband)) {
            $template->showlowerbandchecked = 'checked="checked"';
        } else {
            $template->showlowerbandunchecked = 'checked="checked"';
        }
        $template->stryes = get_string('yes');
        $template->strno = get_string('no');

        $template->helpiconconfigtimeunits = $this->output->help_icon('configtimeunits', 'block_dashboard');
        $template->strconfigupperbandunit = get_string('configupperbandunit', 'block_dashboard');

        $upperbandunit = 'MONTH';
        if (isset($theblock->config) && isset($theblock->config->upperbandunit)) {
            $upperbandunit = $theblock->config->upperbandunit;
        }
        $upperunits = array('MONTH' => get_string('month', 'block_dashboard'),
            'WEEK' => get_string('week', 'block_dashboard'),
            'DAY' => get_string('day', 'block_dashboard'),
            'HOUR' => get_string('hour', 'block_dashboard'));
        $template->upperbandunitselect = html_writer::select($upperunits, 'upperbandunit', $upperbandunit);

        $template->helpiconconfigtimeunits = $this->output->help_icon('configtimeunits', 'block_dashboard');
        $template->strconfiglowerbandunit = get_string('configlowerbandunit', 'block_dashboard');

        $lowerbandunit = 'YEAR';
        if (isset($theblock->config) && isset($theblock->config->lowerbandunit)) {
            $lowerbandunit = $theblock->config->lowerbandunit;
        }
        $lowerunits = array('YEAR' => get_string('year', 'block_dashboard'),
            'MONTH' => get_string('month', 'block_dashboard'),
            'WEEK' => get_string('week', 'block_dashboard'),
            'DAY' => get_string('day', 'block_dashboard'));
        $template->lowerbandunitselect = html_writer::select($lowerunits, 'lowerbandunit', $lowerbandunit);

        $template->helpiconconfigeventmapping = $this->output->help_icon('configeventmapping', 'block_dashboard');
        $template->strconfigeventmapping = get_string('configeventmapping', 'block_dashboard');

        if (isset($theblock->config) && isset($theblock->config->timelineeventtitle)) {
            $template->eventtitle = $theblock->config->timelineeventtitle;
        }

        if (isset($theblock->config) && isset($theblock->config->timelineeventstart)) {
            $template->eventstart = $theblock->config->timelineeventstart;
        }

        if (isset($theblock->config) && isset($theblock->config->timelineeventend)) {
            $template->eventend = $theblock->config->timelineeventend;
        }

        if (isset($theblock->config) && isset($theblock->config->timelineeventlink)) {
            $template->eventlink = $theblock->config->timelineeventlink;
        }

        if (isset($theblock->config) && isset($theblock->config->timelineeventdesc)) {
            $template->eventdesc = $theblock->config->timelineeventdesc;
        }

        $template->streventtitles = get_string('eventtitles', 'block_dashboard');
        $template->streventstart = get_string('eventstart', 'block_dashboard');
        $template->streventend = get_string('eventend', 'block_dashboard');
        $template->streventlink = get_string('eventlink', 'block_dashboard');
        $template->streventdesc = get_string('eventdesc', 'block_dashboard');

        $template->strconfigcolors = get_string('configcolors', 'block_dashboard');
        $template->strconfigcolorfield = get_string('configcolorfield', 'block_dashboard');

        $template->strconfigcoloredvalues = get_string('configcoloredvalues', 'block_dashboard');

        return $this->render_from_template('block_dashboard/timelineparams', $template);
    }

    public function tablecolor_mapping() {
        global $OUTPUT;

        $theblock = $this->theblock;

        $template = new StdClass();

        $template->strtablecolormapping = get_string('tablecolormapping', 'block_dashboard');
        $template->tablecolormappinghelp = $OUTPUT->help_icon('tablecolormapping', 'block_dashboard');
        $template->strconfigcolors = get_string('configcolors', 'block_dashboard');
        $template->strconfigcolorfield = get_string('configcolorfield', 'block_dashboard');
        $template->strconfigcoloredvalues = get_string('configcoloredvalues', 'block_dashboard');

        return $this->render_from_template('block_dashboard/colormapping', $template);
    }

}