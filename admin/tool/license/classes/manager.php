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
 * @package    tool_license
 * @copyright  2019 Tom Dickman <tomdickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_license;

use tool_license\form\edit_license;
use license_manager;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * License manager, main controller for tool_license.
 *
 * @package    tool_license
 * @copyright  2019 Tom Dickman <tomdickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /**
     * Action for creating a new custom license.
     */
    const ACTION_CREATE = 'create';

    /**
     * Action for updating a custom license's details.
     */
    const ACTION_UPDATE = 'update';

    /**
     * Action for deleting a custom license.
     */
    const ACTION_DELETE = 'delete';

    /**
     * Action for disabling a custom license.
     */
    const ACTION_DISABLE = 'disable';

    /**
     * Action for enabling a custom license.
     */
    const ACTION_ENABLE = 'enable';

    /**
     * Action for displaying the license list view.
     */
    const ACTION_VIEW_LICENSE_MANAGER = 'viewlicensemanager';

    /**
     * Action for moving a license up order.
     */
    const ACTION_MOVE_UP = 'moveup';

    /**
     * Action for moving a license down order.
     */
    const ACTION_MOVE_DOWN = 'movedown';

    /**
     * Entry point for internal license manager.
     *
     * @param string $action the api action to carry out.
     * @param string|object $license the license object or shortname of license to carry action out on.
     */
    public function execute(string $action, $license) : void {

        admin_externalpage_setup('licensemanager');

        // Convert license to a string if it's a full license object.
        if (is_object($license)) {
            $license = $license->shortname;
        }

        $viewmanager = true;

        switch ($action) {
            case self::ACTION_DISABLE:
                license_manager::disable($license);
                break;

            case self::ACTION_ENABLE:
                license_manager::enable($license);
                break;

            case self::ACTION_DELETE:
                license_manager::delete($license);
                break;

            case self::ACTION_CREATE:
            case self::ACTION_UPDATE:
                $viewmanager = $this->edit($action, $license);
                break;

            case self::ACTION_MOVE_UP:
            case self::ACTION_MOVE_DOWN:
                $this->change_license_order($action, $license);
                break;

            case self::ACTION_VIEW_LICENSE_MANAGER:
            default:
                break;
        }
        if ($viewmanager) {
            $this->view_license_manager();
        }
    }

    /**
     * Edit an existing license or create a new license.
     *
     * @param string $action the form action to carry out.
     * @param string $licenseshortname the shortname of the license to edit.
     *
     * @return bool true if license editing complete, false otherwise.
     */
    private function edit(string $action, string $licenseshortname) : bool {

        if ($action != self::ACTION_CREATE && $action != self::ACTION_UPDATE) {
            throw new \coding_exception('license edit actions are limited to create and update');
        }

        $form = new form\edit_license($action, $licenseshortname);

        if ($form->is_cancelled()) {
            return true;
        } else if ($data = $form->get_data()) {

            $license = new stdClass();
            if ($action == self::ACTION_CREATE) {
                // Check that license shortname isn't already in use.
                if (!empty(license_manager::get_license_by_shortname($data->shortname))) {
                    print_error('duplicatelicenseshortname', 'tool_license',
                        helper::get_licensemanager_url(),
                        $data->shortname);
                }
                $license->shortname = $data->shortname;
            } else {
                if (empty(license_manager::get_license_by_shortname($licenseshortname))) {
                    print_error('licensenotfoundshortname', 'tool_license',
                        helper::get_licensemanager_url(),
                        $licenseshortname);
                }
                $license->shortname = $licenseshortname;
            }
            $license->fullname = $data->fullname;
            $license->source = $data->source;
            // Legacy date format maintained to prevent breaking on upgrade.
            $license->version = date('Ymd', $data->version) . '00';
            $license->custom = license_manager::CUSTOM_LICENSE;
            license_manager::add($license);
            license_manager::enable($license->shortname);

            return true;
        } else {
            $this->view_license_editor($action, $licenseshortname, $form);
            return false;
        }
    }

    /**
     * Change license order by moving up or down license order.
     *
     * @param string $direction which direction to move, up or down.
     * @param string $licenseshortname the shortname of the license to move up or down order.
     */
    private function change_license_order(string $direction, string $licenseshortname) : void {

        if (in_array($direction, [self::ACTION_MOVE_UP, self::ACTION_MOVE_DOWN]) && !empty($licenseshortname)) {
            $licenseorder = license_manager::get_license_order();

            $currentindex = array_search($licenseshortname, $licenseorder);

            // Can only move up order if not the top license already.
            $shouldmoveup = $direction == self::ACTION_MOVE_UP && $currentindex > 0;
            // Bottom license cannot be moved down as there is no license to move it under.
            $shouldmovedown = $direction == self::ACTION_MOVE_DOWN && ($currentindex < count($licenseorder) - 1);

            if ($shouldmoveup || $shouldmovedown) {
                $newindex = $shouldmoveup ? $currentindex - 1 : $currentindex + 1;
                $license = array_splice($licenseorder, $currentindex, 1);
                array_splice($licenseorder, $newindex, 0, $license);
            }

            set_config('licenseorder', implode(',', $licenseorder));
        }
    }

    /**
     * View the license editor to create or edit a license.
     *
     * @param string $action
     * @param string $licenseshortname the shortname of the license to create/edit.
     * @param \tool_license\form\edit_license $form the form for submitting edit data.
     */
    protected function view_license_editor(string $action, string $licenseshortname, edit_license $form) : void {
        global $PAGE;

        $renderer = $PAGE->get_renderer('tool_license');

        if ($action == self::ACTION_UPDATE && $license = license_manager::get_license_by_shortname($licenseshortname)) {
            $return = $renderer->render_edit_licence_headers($licenseshortname);

            $form->set_data(['shortname' => $license->shortname]);
            $form->set_data(['fullname' => $license->fullname]);
            $form->set_data(['source' => $license->source]);
            $form->set_data(['version' => helper::convert_version_to_epoch($license->version)]);

        } else {
            $return = $renderer->render_create_licence_headers();
        }
        $return .= $form->render();
        $return .= $renderer->footer();

        echo $return;
    }

    protected function view_license_manager() {
        global $PAGE;

        $renderer = $PAGE->get_renderer('tool_license');
        $html = $renderer->header();
        $html .= $renderer->heading(get_string('licensemanager', 'tool_license'));

        // TODO: Update this to use tool_license renderer and components.
        $table = new \tool_license\output\table();
        $html .= $renderer->render($table);

        $html .= $renderer->footer();

        $PAGE->requires->js_call_amd('tool_license/delete_license');

        echo $html;
    }
}
