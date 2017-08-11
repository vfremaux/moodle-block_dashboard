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
 * @module     block_dashboard/dashboard
 * @package    blocks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// jshint unused: true, undef:true
define(['jquery', 'core/log'], function ($, log) {

    var autosubmit = 1;

    return {

        init: function(args) {

            // Attach filter handles if autosubmit.
            $('.dashboard-filter-autosubmit').each( function(i, spanobj) {
                regex = /dashboard-auto-([0-9]+)/;
                matchs = regex.exec(buttonobj.attr('id'));
                blockid = matchs[1];

                $('.dashboard-filter-element').bind('onchange', this.submitdashboardfilter);
            })
            log.debug('Block dashboard initialized');

        },

        submitdashboardfilter: function submitdashboardfilter(e) {

            // That is the Jquery representant of the changed form element.
            that = $(this);
            regex = /filter([0-9]+)_/;
            matchs = regex.exec(that.attr('name'));
            if (matchs) {
                blockid = matchs[1];
                $('#dashboard-form-' + blockid).submit();
            }
        }
    };
});