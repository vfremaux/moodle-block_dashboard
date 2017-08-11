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

    var autosubmit = 1;

    return {

        init: function(args) {

            // Attach tabs handler to all tabs in page.
            $('.dashboard-setup-tablink').on('click', this.openpanel);

            $('.dashboard-setup #menutabletype').on('change', this.showtabletypes);

            $('.dashboard-setup #menugraphtype').on('change', this.showgraphtypes);

            log.debug('Block dashboard AMD initialized');

        },

        openpanel: function(e) {

            that = $(this);
            regex = /tablink-([a-z0-9]+)/;
            matchs = regex.exec(that.attr('id'));
            panelid = parseInt(matchs[1]);

            $('.dashboardsettings-panel').attr('class', 'dashboardsettings-panel off');
            $('#dashboardsettings-panel-' + panelid).attr('class', 'dashboardsettings-panel on');
            $('.setting-tab').removeClass('here');
            $('#setting-tab-' + panelid).addClass('here');
            $('.setting-tab').removeClass('active');
            $('#setting-tab-' + panelid).addClass('active');
        },

        showtabletypes: function (e) {
            that = $(this);
            option = $(that.attr('id') + ' options:selected').val();
            switch (option) {
                case 'linear': {
                    $('#dashboardsettings-panel-tabularparams').attr('class', 'dashboardsettings-panel off');
                    $('#dashboardsettings-panel-treeviewparams').attr('class', 'dashboardsettings-panel off');
                    break;
                }

                case 'tabular': {
                    $('#dashboardsettings-panel-tabularparams').attr('class', 'dashboardsettings-panel on');
                    $('#dashboardsettings-panel-treeviewparams').attr('class', 'dashboardsettings-panel off');
                    break;
                }

                case 'treeview': {
                    $('#dashboardsettings-panel-tabularparams').attr('class', 'dashboardsettings-panel off');
                    $('#dashboardsettings-panel-treeviewparams').attr('class', 'dashboardsettings-panel on');
                    break;
                }
            }
        },

        showgraphtypes: function (e) {
            that = $(this);
            option = $(that.attr('id') + ' options:selected').val();
            switch (option) {

                case 'googlemap': {
                    $('#dashboardsettings-panel-googleparams').attr('class', 'dashboardsettings-panel on');
                    $('#dashboardsettings-panel-timelineparams').attr('class', 'dashboardsettings-panel off');
                    break;
                }

                case 'timeline': {
                    $('#dashboardsettings-panel-googleparams').attr('class', 'dashboardsettings-panel off');
                    $('#dashboardsettings-panel-timelineparams').attr('class', 'dashboardsettings-panel on');
                    break;
                }

                default:
                    $('#dashboardsettings-panel-googleparams').attr('class', 'dashboardsettings-panel off');
                    $('#dashboardsettings-panel-timelineparams').attr('class', 'dashboardsettings-panel off');
            }
        },

    };
});