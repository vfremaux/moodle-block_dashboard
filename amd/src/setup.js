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
 * Javascript controller for controlling the sections.
 *
 * @module     block_dashboard/setup
 * @package    blocks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// jshint unused: true, undef:true
define(['jquery', 'core/log'], function ($, log) {

    return {

        init: function() {

            // Attach togglestate handler to all handles in page.
            $('.dashboard-setup-tablink').on('click', this.openpanel);

            $('.dashboard-setup #menutabletype').on('change', this.showtabletypes);

            $('.dashboard-setup #menugraphtype').on('change', this.showgraphtypes);

            log.debug('Block dashboard AMD setup initialized');

        },

        openpanel: function() {

            var that = $(this);
            var regex = /tablink-([a-z0-9]+)/;
            var matchs = regex.exec(that.attr('id'));
            var panelid = matchs[1];

            $('.dashboardsettings-panel').attr('class', 'dashboardsettings-panel off');
            $('#dashboardsettings-panel-' + panelid).attr('class', 'dashboardsettings-panel on');
            $('.setting-tab').removeClass('here');
            $('#setting-tab-' + panelid).addClass('here');
            $('.setting-tab').removeClass('active');
            $('#setting-tab-' + panelid).addClass('active');
        },

        showtabletypes: function () {
            var that = $(this);
            var option = $(that.attr('id') + ' options:selected').val();
            switch (option) {
                case 'linear': {
                    $('#setting-tab-tabularparams').attr('class', 'dashboardsettings-panel off');
                    $('#setting-tab-treeviewparams').attr('class', 'dashboardsettings-panel off');
                    break;
                }

                case 'tabular': {
                    $('#setting-teb-tabularparams').attr('class', 'dashboardsettings-panel on');
                    $('#setting-tab-treeviewparams').attr('class', 'dashboardsettings-panel off');
                    break;
                }

                case 'treeview': {
                    $('#setting-tab-tabularparams').attr('class', 'dashboardsettings-panel off');
                    $('#setting-tab-treeviewparams').attr('class', 'dashboardsettings-panel on');
                    break;
                }
            }
        },

        showgraphtypes: function () {
            var that = $(this);
            var option = $('#' + that.attr('id') + ' option:selected').attr('value');
            log.debug('Graph type' + option);
            switch (option) {

                case 'googlemap': {
                    $('#setting-tab-googleparams').attr('class', 'dashboardsettings-panel on');
                    $('#setting-tab-timelineparams').attr('class', 'dashboardsettings-panel off');
                    break;
                }

                case 'timeline': {
                    $('#setting-tab-googleparams').attr('class', 'dashboardsettings-panel off');
                    $('#setting-tab-timelineparams').attr('class', 'dashboardsettings-panel on');
                    break;
                }

                default:
                    $('#setting-tab-googleparams').attr('class', 'dashboardsettings-panel off');
                    $('#setting-tab-timelineparams').attr('class', 'dashboardsettings-panel off');
            }
        }

    };
});