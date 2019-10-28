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

use html_table;
use html_writer;
use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die();

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
     * Action for setting the site default license.
     */
    const ACTION_SET_SITE_DEFAULT = 'setsitedefault';

    /**
     * Action for displaying the license list view.
     */
    const ACTION_VIEW_LICENSE_MANAGER = 'viewlicensemanager';

    /**
     * Entry point for internal license manager api.
     *
     * @param $action
     * @param string|object|null $license
     */
    public function execute($action, $license) {
        admin_externalpage_setup('tool_licensemanager/manager');

        // Convert license to a string if it's a full license object.
        if (is_object($license)) {
            $license = $license->shortname;
        }

        $return = true;

        switch ($action) {
            case self::ACTION_SET_SITE_DEFAULT:
                $this->set_site_default();
                break;

            case self::ACTION_DISABLE:
                $this->disable($license);
                break;

            case self::ACTION_ENABLE:
                $this->enable($license);
                break;

            case self::ACTION_DELETE:
                $this->delete($license);
                break;

            case self::ACTION_CREATE:
            case self::ACTION_UPDATE:
                $this->edit($action, $license);
                $return = false;
                break;

            case self::ACTION_VIEW_LICENSE_MANAGER:
            default:
                $this->view_license_manager();
                $return = false;
                break;
        }

        if ($return) {
            redirect(new moodle_url('/admin/tool/licensemanager/manager.php'));
        }
    }

    /**
     * Adding a new license type.
     *
     * @param object $license {
     *            shortname => string a shortname of license, will be refered by files table[required]
     *            fullname  => string the fullname of the license [required]
     *            source => string the homepage of the license type[required]
     *            enabled => int is it enabled?
     *            version  => int a version number (epoch date) used by moodle [required]
     *            custom => int is this a custom license?
     * }
     */
    private function add($license) {
        global $DB;
        if ($record = $DB->get_record('license', array('shortname' => $license->shortname))) {
            // record exists
            $license->enabled = $record->enabled;
            $license->id = $record->id;
            $DB->update_record('license', $license);
        } else {
            $DB->insert_record('license', $license);
        }
        return true;
    }

    /**
     * Edit an existing license or create a new license.
     *
     * @param string $action the form action to carry out.
     * @param string $licenseshortname the shortname of the license to edit.
     *
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    private function edit(string $action, string $licenseshortname) {
        global $PAGE;

        $form = new forms\edit_license($action, $licenseshortname, $this);

        if ($form->is_cancelled()) {
            redirect(helper::get_view_license_manager_url());
        } elseif ($data = $form->get_data()) {
            $license = new stdClass();
            $license->shortname = $data->shortname;
            $license->fullname = $data->fullname;
            $license->source = $data->source;
            $license->version = $data->version;
            $license->custom = self::CUSTOM_LICENSE;
            $license->enabled = 1;  // Default to enabled.
            $this->add($license);
            redirect(helper::get_view_license_manager_url());
        } else {
            $renderer = $PAGE->get_renderer('tool_licensemanager');
            $return = $renderer->header();
            if ($action == self::ACTION_CREATE) {
                $return .= $renderer->heading(get_string('createlicense', 'tool_licensemanager'));
            } elseif ($action == self::ACTION_UPDATE) {
                $return .= $renderer->heading(get_string('editlicense', 'tool_licensemanager'));

                $license = $this->get_license_by_shortname($licenseshortname);
                if (!is_null($license)) {
                    $form->set_data(['shortname' => $license->shortname]);
                    $form->set_data(['fullname' => $license->fullname]);
                    $form->set_data(['source' => $license->source]);
                    $form->set_data(['version' => $license->version]);
                } else {
                    // There is no license to update, so redirect to creation url.
                    redirect(helper::get_create_license_url());
                }
            }
            $return .= $form->render();
            $return .= $renderer->footer();

            echo $return;
        }
    }

    /**
     * Get license records.
     *
     * @param array|null $param array of filters to apply to results.
     *
     * @return array
     */
    public function get_licenses($param = null) {
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
     * Get license record by shortname.
     *
     * @param string $name the shortname of license.
     *
     * @return object|null license object or null if no license found.
     */
    public function get_license_by_shortname($name) {
        global $DB;
        if ($record = $DB->get_record('license', array('shortname' => $name))) {
            return $record;
        } else {
            return null;
        }
    }

    /**
     * Enable a license.
     *
     * @param string $licenseshortname the shortname of license to enable.
     *
     * @return boolean true if successful, false otherwise.
     */
    private function enable($licenseshortname) {
        global $DB;
        if ($license = $this->get_license_by_shortname($licenseshortname)) {
            $license->enabled = 1;
            $DB->update_record('license', $license);
        }
        $this->set_active_licenses();
        return true;
    }

    /**
     * Disable a license
     *
     * @param string $licenseshortname the shortname of license
     *
     * @return boolean true if successful, false otherwise.
     */
    private function disable($licenseshortname) {
        global $DB, $CFG;
        // Site default license cannot be disabled!
        if ($licenseshortname == $CFG->sitedefaultlicense) {
            print_error('Site default license cannot be disabled.');
        }
        if ($license = $this->get_license_by_shortname($licenseshortname)) {
            $license->enabled = 0;
            $DB->update_record('license', $license);
        }
        $this->set_active_licenses();
        return true;
    }

    /**
     * Delete a custom license.
     *
     * @param string $licenseshortname the shortname of license.
     *
     * @throws \dml_exception
     */
    private function delete($licenseshortname) {
        global $DB;

        if ($license = $this->get_license_by_shortname($licenseshortname)) {
            if ($license->custom == self::CUSTOM_LICENSE) {
                $DB->delete_records('license', ['id' => $license->id]);
            } else {
                print_error('licensecantdeletecore', 'error', helper::get_view_license_manager_url());
            }
        } else {
            print_error('licensenotfoundshortname', 'error', helper::get_view_license_manager_url(), $licenseshortname);
        }
    }

    /**
     * Display the main license manager view.
     *
     * @throws \coding_exception
     * @throws \moodle_exception
     */
    private function view_license_manager() {
        global $PAGE, $CFG;

        // Add the settings form for setting site default license.
        $form = new forms\select_site_default($this);

        if ($form->is_cancelled()) {
            // We shouldn't get here as there is no cancel button, but just in case.
            redirect(helper::get_view_license_manager_url());
        } else if ($data = $form->get_data()) {
            set_config('sitedefaultlicense', $data->sitedefaultlicense);
            redirect(helper::get_view_license_manager_url());
        } else {
            $form->set_data(['sitedefaultlicense' => $CFG->sitedefaultlicense]);

            // Display the table of all licenses within this Moodle instance and their statuses.
            $licenses = $this->get_licenses();
            $renderer = $PAGE->get_renderer('tool_licensemanager');
            $txt = get_strings(array('administration', 'settings', 'name', 'enable', 'edit', 'editlock', 'disable', 'none', 'delete'));

            $return = $renderer->header();
            $return .= $renderer->heading(get_string('availablelicenses', 'admin'), 3, 'main', true);
            $return .= $form->render();

            $return .= $renderer->box_start('generalbox editorsui');

            $table = new html_table();
            $table->head  = array($txt->name, $txt->enable, $txt->edit, $txt->delete);
            $table->colclasses = array('text-left', 'text-center', 'text-center', 'text-center');
            $table->id = 'availablelicenses';
            $table->attributes['class'] = 'admintable generaltable';
            $table->data  = array();

            foreach ($licenses as $value) {

                if ($value->custom == 0) {
                    $displayname = html_writer::link($value->source, get_string($value->shortname, 'license'),
                        array('target' => '_blank'));
                } else {
                    $displayname = html_writer::link($value->source, $value->fullname, array('target' => '_blank'));
                }

                if ($value->shortname == $CFG->sitedefaultlicense) {
                    $displayname .= ' '.$renderer->pix_icon('t/locked', get_string('default'));
                    $hideshow = $renderer->pix_icon('t/locked', get_string('default'));
                    $editlicense = $renderer->pix_icon('t/locked', get_string('default'));
                    $deletelicense = $renderer->pix_icon('t/locked', get_string('default'));
                } else {
                    if ($value->enabled == 1) {
                        $hideshow = html_writer::link(helper::get_disable_license_url($value->shortname),
                            $renderer->pix_icon('t/hide', get_string('disable')));
                    } else {
                        $hideshow = html_writer::link(helper::get_enable_license_url($value->shortname),
                            $renderer->pix_icon('t/show', get_string('enable')));
                    }

                    if ($value->custom == 1) {
                        $editlicense = html_writer::link(helper::get_update_license_url($value->shortname),
                            $renderer->pix_icon('t/editinline', $txt->edit));
                    } else {
                        $editlicense = $renderer->pix_icon('t/block', $txt->editlock);
                    }

                    if ($value->custom == 1) {
                        $deletelicense = html_writer::link(helper::get_delete_license_url($value->shortname),
                            $renderer->pix_icon('i/trash', $txt->delete));
                    } else {
                        $deletelicense = $renderer->pix_icon('t/block', $txt->editlock);
                    }
                }

                $table->data[] = array($displayname, $hideshow, $editlicense, $deletelicense);
            }
            $return .= html_writer::table($table);
            $return .= $renderer->box_end();
            $return .= $renderer->single_button(helper::get_create_license_url(), get_string('createlicense', 'tool_licensemanager'));
            $return .= $renderer->footer();
            echo $return;
        }

    }

    /**
     * Set the default site license in configuration based on form data.
     *
     * @throws \moodle_exception
     */
    public function set_site_default() {
        $form = new forms\select_site_default($this);
        if ($data = $form->get_data()) {
            set_config('sitedefaultlicense', $data->sitedefaultlicense);
        }
        redirect(helper::get_view_license_manager_url());
    }

    /**
     * Store active licenses in global $CFG.
     */
    private function set_active_licenses() {
        $licenses = $this->get_licenses(array('enabled' => 1));
        $result = array();
        foreach ($licenses as $license) {
            $result[] = $license->shortname;
        }
        set_config('licenses', implode(',', $result));
    }
}
