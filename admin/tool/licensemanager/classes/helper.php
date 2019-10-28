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
 * License manager.
 *
 * @package    tool_licensemanager
 * @copyright  2019 Tom Dickman <tomdickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_licensemanager;

use moodle_url;
use stdClass;
use tool_licensemanager\manager;

defined('MOODLE_INTERNAL') || die();

class helper {

    public static function get_select_site_default_url() {
        $url = new moodle_url('/admin/tool/licensemanager/manager.php', [
            'action' => manager::ACTION_SET_SITE_DEFAULT
        ]);

        return $url;
    }

    public static function get_view_license_manager_url() {
        $url = new moodle_url('/admin/tool/licensemanager/manager.php',
            ['action' => manager::ACTION_VIEW_LICENSE_MANAGER]);
        return $url;
    }

    public static function get_enable_license_url(string $licenseshortname) {
        $url = new moodle_url('/admin/tool/licensemanager/manager.php',
            ['action' => manager::ACTION_ENABLE, 'license' => $licenseshortname]);

        return $url;
    }

    public static function get_disable_license_url(string $licenseshortname) {
        $url = new moodle_url('/admin/tool/licensemanager/manager.php',
            ['action' => manager::ACTION_DISABLE, 'license' => $licenseshortname]);

        return $url;
    }

    public static function get_create_license_url() {
        $url = new moodle_url('/admin/tool/licensemanager/manager.php',
            ['action' => manager::ACTION_CREATE]);

        return $url;
    }

    public static function get_update_license_url(string $licenseshortname) {
        $url = new moodle_url('/admin/tool/licensemanager/manager.php',
            ['action' => manager::ACTION_UPDATE, 'license' => $licenseshortname]);

        return $url;
    }

}