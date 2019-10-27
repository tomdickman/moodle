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

use lang_string;
use tool_licensemanager\helper;
use tool_licensemanager\manager;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

/**
 * Form for editing the default site license.
 *
 * @copyright  2019 Tom Dickman <tomdickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class select_site_default extends \moodleform {

    /**
     * @var \tool_licensemanager\manager
     */
    private $manager;

    /**
     * select_site_default constructor.
     *
     * @param string|null $licenseshortname
     * @param \tool_licensemanager\manager $manager
     * @param null $customdata
     */
    public function __construct(manager $manager, $customdata = null) {
        $this->manager = $manager;
        parent::__construct(helper::get_select_site_default_url(), $customdata);
    }

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;
        
        $options = [];
        $licenses = $this->manager->get_licenses();
        foreach ($licenses as $license) {
            if (!$license->custom) {
                // Core licenses have hard coded strings in Moodle core.
                $options[$license->shortname] = new lang_string($license->shortname, 'license');
            } else {
                $options[$license->shortname] = $license->fullname;
            }
        }

        $mform->addElement('select', 'sitedefaultlicense', get_string('sitedefaultlicense', 'tool_licensemanager'), $options);

        // Add save button only, nowhere to go if we cancel this.
        $this->add_action_buttons('false');
    }
}
