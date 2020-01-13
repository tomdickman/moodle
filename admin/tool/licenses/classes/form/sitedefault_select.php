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
 * Form for setting the site default license.
 *
 * @package    tool_licenses
 * @copyright  2020 Tom Dickman <tom.dickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_licenses\form;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Form for selecting the site default license.
 *
 * @package    tool_licenses
 * @copyright  2020 Tom Dickman <tom.dickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sitedefault_select extends \moodleform {

    /**
     * Form definition for selecting the site default license.
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $options = \license_manager::get_active_licenses_as_array();

        if (!empty($CFG->sitedefaultlicense) and in_array($CFG->sitedefaultlicense, $options)) {
            $default = $CFG->sitedefaultlicense;
        } else {
            $default = reset($options);
        }

        $mform->addElement('select', 'sitedefault', get_string('sitedefaultlicense', 'tool_licenses'), $options);
        $mform->setDefault('sitedefault', $default);

        $this->add_action_buttons();
    }

}
