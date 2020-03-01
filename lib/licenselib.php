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

    /**
     * License is a core license and can not be updated or deleted.
     */
    const CORE_LICENSE = 0;

    /**
     * License is a custom license and can be updated and/or deleted.
     */
    const CUSTOM_LICENSE = 1;

    /**
     * Integer representation of boolean for a license that is enabled.
     */
    const LICENSE_ENABLED = 1;

    /**
     * Integer representation of boolean for a license that is disabled.
     */
    const LICENSE_DISABLED = 0;

    /**
     * Adding a new license type
     * @param object $license {
     *            shortname => string a shortname of license, will be refered by files table[required]
     *            fullname  => string the fullname of the license [required]
     *            source => string the homepage of the license type[required]
     *            enabled => int is it enabled?
     *            version  => int a version number used by moodle [required]
     * }
     *
     * @throws \moodle_exception when attempting to amend a core license.
     */
    static public function add($license) {
        global $DB;
        if ($record = $DB->get_record('license', array('shortname'=>$license->shortname))) {
            // record exists
            if ($record->custom != self::CORE_LICENSE) {
                $license->enabled = $record->enabled;
                $license->id = $record->id;
                $DB->update_record('license', $license);
            } else {
                throw new moodle_exception('cannotupdatecorelicense', 'error');
            }
        } else {
            $DB->insert_record('license', $license);
        }
        // Add the new license to the end of order for licenses.
        $licenseorder = self::get_license_order();
        if (!in_array($license->shortname, $licenseorder)) {
            $licenseorder[] = $license->shortname;
            set_config('licenseorder', implode(',', $licenseorder));
        }

        self::reset_license_cache();

        return true;
    }

    /**
     * Get license records
     * @param mixed $param
     * @return array
     */
    static public function get_licenses($param = null) {
        global $DB;

        $cache = \cache::make('core', 'license');
        $licenses = $cache->get('licenses');

        if (empty($licenses)) {
            $licenses = $DB->get_records('license');
            $cache->set('licenses', $licenses);
        }

        // Apply condition here rather than in database query as we cache all licenses.
        if (!empty($param)) {
            $filteredlicenses = [];

            foreach ($licenses as $id => $license) {
                $filtermatch = true;
                foreach ($param as $key => $value) {
                    if ($license->$key != $value) {
                        $filtermatch = false;
                    }
                }
                if ($filtermatch) {
                    $filteredlicenses[$id] = $license;
                }
            }
            $licenses = $filteredlicenses;
        }

        return $licenses;
    }

    /**
     * Get an array of license shortnames in order.
     *
     * @return array string[] of license shortnames.
     */
    public static function get_license_order() {

        $licenses = self::get_licenses_in_order();
        $licenseorder = array_keys($licenses);

        return $licenseorder;
    }

    /**
     * Get all installed licenses in order.
     *
     * @return array $result of license objects.
     */
    static public function get_licenses_in_order() {
        global $CFG;

        $result = [];
        $licenses = self::get_licenses();
        $orderupdated = false;

        if (!empty($CFG->licenseorder)) {
            $order = explode(',', $CFG->licenseorder);

            foreach ($order as $licensename) {
                foreach ($licenses as $license) {
                    if ($licensename == $license->shortname) {
                        $result[$license->shortname] = $license;
                    }
                }
            }

            // We shouldn't be missing any licenses as order is created on install and amended on
            // license creation, but just in case, check for any licenses not in the licenseorder,
            // add them to the bottom of results order.
            foreach ($licenses as $license) {
                if (!in_array($license->shortname, array_keys($result))) {
                    $result[$license->shortname] = $license;
                    $orderupdated = true;
                }
            }

        } else {
            // There is no order set so get the licenses in any order.
            foreach ($licenses as $license) {
                $result[$license->shortname] = $license;
            }
            $orderupdated = true;
        }

        if ($orderupdated) {
            set_config('licenseorder', implode(',', array_keys($result)));
        }

        return $result;
    }

    /**
     * Get license record by shortname
     *
     * @param string $param the shortname of license
     * @return object|null the license or null if no license found.
     */
    static public function get_license_by_shortname(string $name) {
        $licenses = self::get_licenses(['shortname' => $name]);

        if (!empty($licenses)) {
            $license = reset($licenses);
        } else {
            $license = null;
        }

        return $license;
    }

    /**
     * Enable a license
     * @param string $license the shortname of license
     * @return boolean
     */
    static public function enable($license) {
        global $DB;
        if ($license = self::get_license_by_shortname($license)) {
            $license->enabled = self::LICENSE_ENABLED;
            $DB->update_record('license', $license);
        }
        self::set_active_licenses();

        return true;
    }

    /**
     * Disable a license
     * @param string $license the shortname of license
     * @return boolean
     */
    static public function disable($license) {
        global $DB, $CFG;
        // Site default license cannot be disabled!
        if ($license == $CFG->sitedefaultlicense) {
            print_error('error');
        }
        if ($license = self::get_license_by_shortname($license)) {
            $license->enabled = self::LICENSE_DISABLED;
            $DB->update_record('license', $license);
        }
        self::set_active_licenses();

        return true;
    }

    /**
     * Delete a custom license.
     *
     * @param string $licenseshortname the shortname of license.
     *
     * @throws \moodle_exception when attempting to delete a license you are not allowed to.
     */
    static public function delete($licenseshortname) {
        global $DB;

        if ($license = self::get_license_by_shortname($licenseshortname)) {
            if ($license->custom == self::CUSTOM_LICENSE) {
                // Check that the license is not in use by any files, if so it
                // cannot be deleted.
                $countfilesusinglicense = $DB->count_records('files', ['license' => $licenseshortname]);
                if ($countfilesusinglicense > 0) {
                    throw new moodle_exception('licensecantdeletelicenseinuse', 'tool_license',
                        \tool_license\helper::get_admin_setting_managelicenses_url());
                }
                $DB->delete_records('license', ['id' => $license->id]);

                // Remove the license from license order.
                $licenseorder = self::get_license_order();
                if ($index = array_search($licenseshortname, $licenseorder)) {
                    array_splice($licenseorder, $index, 1);
                    set_config('licenseorder', implode(',', $licenseorder));
                }

                self::reset_license_cache();

            } else {
                throw new moodle_exception('licensecantdeletecore', 'tool_license',
                    \tool_license\helper::get_admin_setting_managelicenses_url());
            }
        } else {
            throw new moodle_exception('licensenotfoundshortname', 'tool_license',
                \tool_license\helper::get_admin_setting_managelicenses_url(), $licenseshortname);
        }
    }

    /**
     * Store active licenses in global $CFG.
     */
    static private function set_active_licenses() {
        self::reset_license_cache();
        $licenses = self::get_licenses(array('enabled'=>1));
        $result = array();
        foreach ($licenses as $l) {
            $result[] = $l->shortname;
        }
        set_config('licenses', implode(',', $result));
    }

    /**
     * Get the globally configured active licenses.
     *
     * @return array of license objects.
     * @throws \coding_exception
     */
    static public function get_active_licenses() {
        global $CFG;

        $result = [];

        if (!empty($CFG->licenses)) {
            $activelicenses = explode(',', $CFG->licenses);
            $licenses = self::get_licenses_in_order();
            foreach ($licenses as $license) {
                if (in_array($license->shortname, $activelicenses)) {
                    // Interpret core license strings for internationalisation.
                    if (isset($license->custom) && $license->custom == self::CORE_LICENSE) {
                        $license->fullname = get_string($license->shortname, 'license');
                    }
                    $result[] = $license;
                }
            }
        }

        return $result;
    }

    /**
     * Get the globally configured active licenses as an array.
     *
     * @return array $licenses an associative array of licenses shaped as ['shortname' => 'fullname']
     */
    static public function get_active_licenses_as_array() {
        $activelicenses = self::get_active_licenses();

        $licenses = [];
        foreach ($activelicenses as $license) {
            $licenses[$license->shortname] = $license->fullname;
        }

        return $licenses;
    }

    /**
     * Install moodle built-in licenses.
     */
    static public function install_licenses() {
        $activelicenses = array();

        $license = new stdClass();

        $license->shortname = 'unknown';
        $license->fullname = 'Licence not specified';
        $license->source = '';
        $license->enabled = self::LICENSE_ENABLED;
        $license->version = '2010033100';
        $license->custom = self::CORE_LICENSE;
        $activelicenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'allrightsreserved';
        $license->fullname = 'All rights reserved';
        $license->source = 'https://en.wikipedia.org/wiki/All_rights_reserved';
        $license->enabled = self::LICENSE_ENABLED;
        $license->version = '2010033100';
        $license->custom = self::CORE_LICENSE;
        $activelicenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'public';
        $license->fullname = 'Public domain';
        $license->source = 'https://en.wikipedia.org/wiki/Public_domain';
        $license->enabled = self::LICENSE_ENABLED;
        $license->version = '2010033100';
        $license->custom = self::CORE_LICENSE;
        $activelicenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'cc';
        $license->fullname = 'Creative Commons';
        $license->source = 'https://creativecommons.org/licenses/by/3.0/';
        $license->enabled = self::LICENSE_ENABLED;
        $license->version = '2010033100';
        $license->custom = self::CORE_LICENSE;
        $activelicenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'cc-nd';
        $license->fullname = 'Creative Commons - NoDerivs';
        $license->source = 'https://creativecommons.org/licenses/by-nd/3.0/';
        $license->enabled = self::LICENSE_ENABLED;
        $license->version = '2010033100';
        $license->custom = self::CORE_LICENSE;
        $activelicenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'cc-nc-nd';
        $license->fullname = 'Creative Commons - No Commercial NoDerivs';
        $license->source = 'https://creativecommons.org/licenses/by-nc-nd/3.0/';
        $license->enabled = self::LICENSE_ENABLED;
        $license->version = '2010033100';
        $license->custom = self::CORE_LICENSE;
        $activelicenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'cc-nc';
        $license->fullname = 'Creative Commons - No Commercial';
        $license->source = 'https://creativecommons.org/licenses/by-nc/3.0/';
        $license->enabled = self::LICENSE_ENABLED;
        $license->version = '2010033100';
        $license->custom = self::CORE_LICENSE;
        $activelicenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'cc-nc-sa';
        $license->fullname = 'Creative Commons - No Commercial ShareAlike';
        $license->source = 'https://creativecommons.org/licenses/by-nc-sa/3.0/';
        $license->enabled = self::LICENSE_ENABLED;
        $license->version = '2010033100';
        $license->custom = self::CORE_LICENSE;
        $activelicenses[] = $license->shortname;
        self::add($license);

        $license->shortname = 'cc-sa';
        $license->fullname = 'Creative Commons - ShareAlike';
        $license->source = 'https://creativecommons.org/licenses/by-sa/3.0/';
        $license->enabled = self::LICENSE_ENABLED;
        $license->version = '2010033100';
        $license->custom = self::CORE_LICENSE;
        $activelicenses[] = $license->shortname;
        self::add($license);

        set_config('licenses', implode(',', $activelicenses));
        set_config('licenseorder', implode(',', $activelicenses));
        set_config('sitedefaultlicense', reset($activelicenses));
    }

    /**
     * Reset the license cache so it rebuilds next time licenses are fetched.
     */
    static public function reset_license_cache() {
        $cache = \cache::make('core', 'license');
        $cache->delete('licenses');
    }
}
