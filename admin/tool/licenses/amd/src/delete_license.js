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
 * Contain the logic for the confirming deleting of a license.
 *
 * @module     tool_licenses/delete_licenses
 * @class      delete_license
 * @package    tool_licenses
 * @copyright  2019 Tom Dickman <tomdickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/modal_factory', 'core/modal_events'],
    function($, ModalFactory, ModalEvents) {

        var trigger = $('.delete-license');
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: 'Delete license',
            body: 'Are you sure you want to delete this license?',
            preShowCallback: function(triggerElement, modal) {
                modal.redirecturl = triggerElement[0].href;
            },
            large: true,
        }, trigger)
            .done(function(modal) {
                modal.getRoot().on(ModalEvents.save, function(e) {
                    // Stop the default save button behaviour which is to close the modal.
                    e.preventDefault();
                    // Redirect to delete url.
                    window.location.href = modal.redirecturl;
                });
            });
    });