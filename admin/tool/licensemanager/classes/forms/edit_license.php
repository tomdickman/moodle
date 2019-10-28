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

use tool_licensemanager\helper;
use tool_licensemanager\manager;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

class edit_license extends \moodleform {

    private $manager;

    public function __construct(string $action, $licenseshortname, manager $manager) {
        $this->manager = $manager;
        if ($action == manager::ACTION_UPDATE && !empty($licenseshortname)) {
            parent::__construct(helper::get_update_license_url($licenseshortname));
        } else {
            parent::__construct(helper::get_create_license_url());
        }
    }

    /**
     * Form definition for creation and editing of licenses.
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('text', 'shortname', get_string('shortname', 'tool_licensemanager'));
        $mform->setType('shortname', PARAM_ALPHANUMEXT);
        $mform->addRule('shortname', get_string('shortname_empty', 'tool_licensemanager'), 'required', null, 'client');

        $mform->addElement('text', 'fullname', get_string('fullname', 'tool_licensemanager'));
        $mform->setType('fullname', PARAM_ALPHANUMEXT);
        $mform->addRule('fullname', get_string('fullname_empty', 'tool_licensemanager'), 'required', null, 'client');

        $mform->addElement('text', 'source', get_string('source', 'tool_licensemanager'));
        $mform->setType('source', PARAM_URL);
        $mform->addRule('source', get_string('source_empty', 'tool_licensemanager'), 'required', null, 'client');
        $mform->addHelpButton('source', 'source', 'tool_licensemanager');

        $mform->addElement('date_selector', 'version', get_string('version', 'tool_licensemanager'), get_string('from'));
        $mform->addRule('version', get_string('version_empty', 'tool_licensemanager'), 'required', null, 'client');
        $mform->addHelpButton('version', 'version', 'tool_licensemanager');

        $this->add_action_buttons();
    }

//    public function validation($data, $files) {
//        $errors = parent::validation($data, $files);
//
//        // Don't allow custom licenses to use existing shortname as this is the natural language unique key.
//        if ($data['action'] == manager::ACTION_CREATE) {
//            $existing = $this->manager->get_licenses(['shortname' => $data['shortname']]);
//            if (!empty($existing)) {
//                $errors['licensealreadyexists'] =
//            }
//        }
//
//        return $errors;
//    }
}

