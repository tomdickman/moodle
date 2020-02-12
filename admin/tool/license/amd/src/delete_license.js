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
 * Contains the logic for confirming deletion of a custom license.
 *
 * @module     tool_license/delete_license
 * @class      delete_license
 * @package    tool_license
 * @copyright  2019 Tom Dickman <tomdickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/modal_factory', 'core/modal_events', 'core/config', 'core/str'],
    function($, ModalFactory, ModalEvents, Config, String) {

        var trigger = $('.delete-license');
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: String.get_string('deletelicense', 'tool_license'),
            body: String.get_string('deletelicenseconfirmmessage', 'tool_license'),
            preShowCallback: function(triggerElement, modal) {
                triggerElement = $(triggerElement);
                let action = 'delete';
                let license = triggerElement.data('license');
                modal.deleteURL = `/admin/tool/license/index.php?action=${action}&license=${license}&sesskey=${Config.sesskey}`;
            },
            large: true,
        }, trigger)
            .done(function(modal) {
                modal.getRoot().on(ModalEvents.save, function(e) {
                    // Stop the default save button behaviour which is to close the modal.
                    e.preventDefault();
                    // Redirect to delete url.
                    window.location.href = modal.deleteURL;
                });
            });
    });
