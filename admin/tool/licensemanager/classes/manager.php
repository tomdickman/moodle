<?php


class manager {

    /**
     * License is a core license and can not be updated or deleted.
     */
    const CORE_LICENSE = 0;

    /**
     * License is a custom license and can be updated and/or deleted.
     */
    const CUSTOM_LICENSE = 1;

    /**
     * Action for creating a new custom license.
     */
    const ACTION_CREATE = 'create';

    /**
     * Action for reading a license.
     */
    const ACTION_READ = 'read';

    /**
     * Action for updating a custom license's details.
     */
    const ACTION_UPDATE = 'update';

    /**
     * Action for deleting a custom license.
     */
    const ACTION_DELETE = 'delete';

    /**
     * Action for deleting a custom license.
     */
    const ACTION_DISABLE = 'disable';

    /**
     * Action for deleting a custom license.
     */
    const ACTION_ENABLE = 'enable';

    /**
     * Action for displaying the license list view.
     */
    const ACTION_VIEW_LICENSE_LIST = 'viewlicenselist';

    /**
     * Entry point for license manager api.
     *
     * @param $action
     * @param string|object|null $license
     */
    public function execute($action, $license = null) {
        admin_externalpage_setup('tool_licensemanager/manager');

        // Convert the license to an object if it isn't already.
        if (!is_object($license) && !empty($license) && !is_string($license)) {
            $licenseobject = new stdClass();
            $licenseobject->shortname = $license;
        }

        $return = true;

        switch ($action) {
            case self::ACTION_VIEW_LICENSE_LIST:
                $this->view_license_list();
                $return = false;
                break;

            case self::ACTION_DISABLE:
                $this->disable($licenseobject->shortname);
                break;

            case self::ACTION_ENABLE:
                $this->enable($licenseobject->shortname);
                break;

            case self::ACTION_DELETE:
                $this->delete($licenseobject->shortname);
                break;

            case self::ACTION_CREATE:
                $this->create($licenseobject);
                // If there is no license object, display create license form and don't return.
                if (empty($licenseobject)) {
                    $return = false;
                }
                break;

            default:
                break;
        }

        if ($return) {
            redirect(new moodle_url('/admin/tool/managelicenses/manager.php'));
        }
    }

    /**
     * Adding a new license type
     *
     * @param object $license {
     *            shortname => string a shortname of license, will be refered by files table[required]
     *            fullname  => string the fullname of the license [required]
     *            source => string the homepage of the license type[required]
     *            enabled => int is it enabled?
     *            version  => int a version number used by moodle [required]
     * }
     */
    private function add($license) {
        global $DB;
        if ($record = $DB->get_record('license', array('shortname' => $license->shortname))) {
            // record exists
            if ($record->version < $license->version) {
                // update license record
                $license->enabled = $record->enabled;
                $license->id = $record->id;
                $DB->update_record('license', $license);
            }
        } else {
            $DB->insert_record('license', $license);
        }
        return true;
    }

    private function create($license) {
        if (!empty($license)) {
            self::add($license);
        } else {
            // Display the form to create a new license.
        }
    }

    /**
     * Get license records
     *
     * @param mixed $param
     *
     * @return array
     */
    private function get_licenses($param = null) {
        global $DB;
        if (empty($param) || !is_array($param)) {
            $param = array();
        }
        // get licenses by conditions
        if ($records = $DB->get_records('license', $param)) {
            return $records;
        } else {
            return array();
        }
    }

    /**
     * Get license record by shortname
     *
     * @param mixed $param the shortname of license, or an array
     *
     * @return object
     */
    private function get_license_by_shortname($name) {
        global $DB;
        if ($record = $DB->get_record('license', array('shortname' => $name))) {
            return $record;
        } else {
            return null;
        }
    }

    /**
     * Enable a license
     *
     * @param string $license the shortname of license
     *
     * @return boolean
     */
    private function enable($licenseshortname) {
        global $DB;
        if ($license = self::get_license_by_shortname($licenseshortname)) {
            $license->enabled = 1;
            $DB->update_record('license', $license);
        }
        self::set_active_licenses();
        return true;
    }

    /**
     * Disable a license
     *
     * @param string $license the shortname of license
     *
     * @return boolean
     */
    private function disable($license) {
        global $DB, $CFG;
        // Site default license cannot be disabled!
        if ($license == $CFG->sitedefaultlicense) {
            print_error('error');
        }
        if ($license = self::get_license_by_shortname($license)) {
            $license->enabled = 0;
            $DB->update_record('license', $license);
        }
        self::set_active_licenses();
        return true;
    }

    /**
     * @param string $licenseshortname the shortname of license.
     *
     * @throws \dml_exception
     */
    private function delete($licenseshortname) {
        global $DB;

        $link = new moodle_url('/admin/settings.php', ['section' => 'managelicenses']);

        if ($license = self::get_license_by_shortname($licenseshortname)) {
            if ($license->custom == self::CUSTOM_LICENSE) {
                $DB->delete_records('license', ['id' => $license->id]);
            } else {
                print_error('licensecantdeletecore', 'error', $link);
            }
        } else {
            print_error('licensenotfoundshortname', 'error', $link, $licenseshortname);
        }
    }

    private function view_license_list() {

    }

    /**
     * Store active licenses in global $CFG
     */
    private function set_active_licenses() {
        // set to global $CFG
        $licenses = self::get_licenses(array('enabled' => 1));
        $result = array();
        foreach ($licenses as $l) {
            $result[] = $l->shortname;
        }
        set_config('licenses', implode(',', $result));
    }
}
