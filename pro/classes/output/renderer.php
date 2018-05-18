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

require_once($CFG->dirroot.'/blocks/dashboard/renderer.php');

class pro_renderer extends block_dashboard_renderer {

    /**
     * prints and format data for googlemap plotting.
     */
    public function googlemaps_data(&$theblock, &$data, &$graphdesc) {

        $str = '';

        if (!empty($config->datalocations)) {
            // Data comes from query and locating information from datalocations field mapping.
            $googlelocs = explode(";", $theblock->config->datalocations);
            if (!empty($data)) {
                foreach ($data as $d) {
                    $t = $d->{$theblock->config->datatitles};
                    if (count($googlelocs) == 1) {
                        list($lat,$lng) = explode(',', $d->{$theblock->config->datalocations});
                        $type = $d->{$theblock->config->datatypes};
                        $gmdata[] = array('title' => $t, 'lat' => 0 + $lat, 'lng' => 0 + $lng, 'markerclass' => $type);
                    } else if (count($googlelocs) == 4) {
                        // We expect an address,postcode,city,region field list. If some data is quoted, take it as "constant".
                        $addresselms = explode(';', $theblock->config->datalocations);
                        $addressfield = trim($addresselms[0]);
                        $postcodefield = trim($addresselms[1]);
                        $cityfield = trim($addresselms[2]);
                        $regionfield = trim($addresselms[3]);
                        $address = $d->{$addressfield};
                        if (preg_match('/^(?:\'|")([^\']*)(?:\'|")$/', $postcodefield, $matches)) {
                            $postcode = $matches[1];
                        } else {
                            $postcode = $d->{$postcodefield};
                        }
                        if (preg_match('/^(?:\'|")([^\']*)(?:\'|")$/', $cityfield, $matches)) {
                            $city = $matches[1];
                        } else {
                            $city = preg_replace('/cedex.*/i', '', $d->{$cityfield}); // remove postal alterations
                        }
                        if (preg_match('/^(?:\'|")([^\']*)(?:\'|")$/', $regionfield, $matches)) {
                            $region = $matches[1];
                        } else {
                            $region = $d->{$regionfield};
                        }
                        $googleerrors = array();
                        if ($location = googlemaps_get_geolocation($region, $address, $postcode, $city, $googleerrors)) {
                            list($lat,$lng) = explode(',', $location);
                            $type = $d->{$theblock->config->datatypes};
                            $gmdata[] = array('title' => $t, 'lat' => $lat, 'lng' => $lng, 'markerclass' => $type);
                        }
                    } else {
                        $str .= '<span class="error">'.get_string('googlelocationerror', 'block_dashboard').'</span>';
                        break;
                    }
                }
            }
        } else {
            $str .= " This is a demo set !! ";
            /**
             * demo
             */
            $gmdata = array(
                array('lat' => 48.020587,
                      'lng' => 0.151405,
                      'markerclass' => 'certiffoad',
                      'title' => 'Via formation'),
                array('lat' => 47.894823,
                      'lng' => 1.904798,
                      'markerclass' => 'certiffoad',
                      'title' => 'FormaSanté'),
                array('lat' => 48.091582,
                      'lng' => -1.789484,
                      'markerclass' => 'hq',
                      'title' => 'CLPS Siege'),
                array('lat' => 48.392852,
                      'lng' => -4.444313,
                      'markerclass' => 'fcfoad',
                      'title' => 'CLPS Brest'),
                array('lat' => 47.663075,
                      'lng' => -2.711906,
                      'markerclass' => 'fcfoad',
                      'title' => 'CLPS Vannes'),
                array('lat' => 47.093953,
                      'lng' => 5.497713,
                      'markerclass' => 'fcfoad',
                      'title' => 'INFA Franche-Comte'),
                array('lat' => 48.565703,
                      'lng' => 7.734375,
                      'markerclass' => 'fc',
                      'title' => 'INFA Alsace'),
                array('lat' => 49.274973,
                      'lng' => 2.444458,
                      'markerclass' => 'fc',
                      'title' => 'INFA Picardie'),
            );
        }

        $str .= googlemaps_embed_graph('dashboard'.$theblock->instance->id,
                                       @$theblock->config->lat,
                                       @$theblock->config->lng,
                                       @$theblock->config->graphwidth,
                                       $theblock->config->graphheight,
                                       $graphdesc,
                                       $gmdata,
                                       true);

        return $str;
    }

    public function tree_view(&$theblock, &$tree, &$treeoutput, &$outputfields, &$outputformats, &$colorcoding) {
        static $level = 1;

        $str = '';

        asort($tree);

        $str .= '<ul class="dashboard-tree'.$level.'">';
        $level++;
        foreach ($tree as $key => $node) {
            $nodestrs = array();
            foreach ($treeoutput as $field => $formatter) {
                if (empty($field)) {
                    continue;
                }
                if (!empty($formatter)) {
                    $datum = dashboard_format_data($formatter, $node->$field);
                } else {
                    $datum = $node->$field;
                }
                if (!empty($theblock->config->colorfield) && $theblock->config->colorfield == $field) {
                    // We probably prefer inline coloouring here, rather than div block.
                    $datum = dashboard_colour_code($theblock, $datum, $colorcoding, true);
                }
                $nodestrs[] = $datum;
            }
            $nodecontent = implode(' ', $nodestrs);
            $nodedata = array();
            foreach ($outputformats as $field => $formatter) {
                if (empty($field)) {
                    continue;
                }
                if (!empty($formatter)) {
                    $datum = dashboard_format_data($formatter, $node->$field);
                } else {
                    $datum = $node->$field;
                }
                if (!empty($theblock->config->colorfield) && $theblock->config->colorfield == $field) {
                    // We probably prefer inline coloouring here, rather than div block.
                    $datum = dashboard_colour_code($theblock, $datum, $colorcoding, true);
                }
                $nodedata[] = $datum;
            }
            $nodedatastr = implode(' ', $nodedata);
            $str .= "<li>$nodecontent <div style=\"float:right\">$nodedatastr</div></li>";
            if (!empty($node->childs)) {
                $str .= $this->tree_view($theblock, $node->childs, $treeoutput, $outputfields, $outputformats, $colorcoding);
            }
        }
        $level--;
        $str .= '</ul>';

        return $str;
    }

    public function tree_buttons($theblock, $filterquerystring, $renderer) {
        global $COURSE;

        // passed to each buttons.
        $renderer->sort = optional_param('tsort'.$theblock->instance->id, @$theblock->config->defaultsort, PARAM_TEXT);

        $str = '<div class="dashboard-table-buttons">';

        $str .= $renderer->allexport_button($theblock);
        if (empty($theblock->config->filepathadminoverride)) {
            $str .= $renderer->fileview_button($theblock);
        }
        $str .= $renderer->filteredoutput_button($theblock, $filterquerystring);

        $str .= '</div>';

        return $str;
    }

}