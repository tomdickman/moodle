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
 * Form for editing tours.
 *
 * @package    tool_licensemanager
 * @copyright  2019 Tom Dickman <tom.dickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_licensemanager\forms;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

class edit_license extends \moodleform {

    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('hidden', 'action', 'update');

        $mform->addElement('text', 'shortname', get_string('shortname', 'tool_licensemanager'));
        $mform->setType('shortname', PARAM_ALPHANUMEXT);

        $mform->addElement('text', 'fullname', get_string('fullname', 'tool_licensemanager'));
        $mform->setType('shortname', PARAM_ALPHANUMEXT);

    }

    protected function validatation() {
        // Don't allow custom licenses to use existing shortname as this is the natural language unique key.
    }
}

