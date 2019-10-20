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
 * A namespace contains license specific functions
 *
 * @since      Moodle 2.0
 * @package    core
 * @subpackage lib
 * @copyright  2010 Dongsheng Cai <dongsheng@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class license_manager {

    static public function install_licenses() {

        $licensemanager = new \tool_licensemanager\manager();

        $active_licenses = [];

        $license = new stdClass();

        $license->shortname = 'unknown';
        $license->fullname = 'Unknown license';
        $license->source = '';
        $license->enabled = 1;
        $license->version = '1269993600';
        $license->custom = \tool_licensemanager\manager::CORE_LICENSE;
        $active_licenses[$license->shortname] = $license->fullname;
        $licensemanager->add($license);

        $license->shortname = 'allrightsreserved';
        $license->fullname = 'All rights reserved';
        $license->source = 'http://en.wikipedia.org/wiki/All_rights_reserved';
        $license->enabled = 1;
        $license->version = '1269993600';
        $license->custom = \tool_licensemanager\manager::CORE_LICENSE;
        $active_licenses[$license->shortname] = $license->fullname;
        $licensemanager->add($license);

        $license->shortname = 'public';
        $license->fullname = 'Public Domain';
        $license->source = 'http://creativecommons.org/licenses/publicdomain/';
        $license->enabled = 1;
        $license->version = '1269993600';
        $license->custom = \tool_licensemanager\manager::CORE_LICENSE;
        $active_licenses[$license->shortname] = $license->fullname;
        $licensemanager->add($license);

        $license->shortname = 'cc';
        $license->fullname = 'Creative Commons';
        $license->source = 'http://creativecommons.org/licenses/by/3.0/';
        $license->enabled = 1;
        $license->version = '1269993600';
        $license->custom = \tool_licensemanager\manager::CORE_LICENSE;
        $active_licenses[$license->shortname] = $license->fullname;
        $licensemanager->add($license);

        $license->shortname = 'cc-nd';
        $license->fullname = 'Creative Commons - NoDerivs';
        $license->source = 'http://creativecommons.org/licenses/by-nd/3.0/';
        $license->enabled = 1;
        $license->version = '1269993600';
        $license->custom = \tool_licensemanager\manager::CORE_LICENSE;
        $active_licenses[$license->shortname] = $license->fullname;
        $licensemanager->add($license);

        $license->shortname = 'cc-nc-nd';
        $license->fullname = 'Creative Commons - No Commercial NoDerivs';
        $license->source = 'http://creativecommons.org/licenses/by-nc-nd/3.0/';
        $license->enabled = 1;
        $license->version = '1269993600';
        $license->custom = \tool_licensemanager\manager::CORE_LICENSE;
        $active_licenses[$license->shortname] = $license->fullname;
        $licensemanager->add($license);

        $license->shortname = 'cc-nc';
        $license->fullname = 'Creative Commons - No Commercial';
        $license->source = 'http://creativecommons.org/licenses/by-nc/3.0/';
        $license->enabled = 1;
        $license->version = '1269993600';
        $license->custom = \tool_licensemanager\manager::CORE_LICENSE;
        $active_licenses[$license->shortname] = $license->fullname;
        $licensemanager->add($license);

        $license->shortname = 'cc-nc-sa';
        $license->fullname = 'Creative Commons - No Commercial ShareAlike';
        $license->source = 'http://creativecommons.org/licenses/by-nc-sa/3.0/';
        $license->enabled = 1;
        $license->version = '1269993600';
        $license->custom = \tool_licensemanager\manager::CORE_LICENSE;
        $active_licenses[$license->shortname] = $license->fullname;
        $licensemanager->add($license);

        $license->shortname = 'cc-sa';
        $license->fullname = 'Creative Commons - ShareAlike';
        $license->source = 'http://creativecommons.org/licenses/by-sa/3.0/';
        $license->enabled = 1;
        $license->version = '1269993600';
        $license->custom = \tool_licensemanager\manager::CORE_LICENSE;
        $active_licenses[$license->shortname] = $license->fullname;
        $licensemanager->add($license);

        set_config('licenses', implode(',', $active_licenses));
        set_config('sitedefaultlicense', reset($active_licenses));
    }
}