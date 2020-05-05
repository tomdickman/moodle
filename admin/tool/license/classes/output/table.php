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
 * Strings for component 'tool_license', language 'en'
 *
 * @package   tool_license
 * @copyright 2019 Tom Dickman <tomdickman@catalyst-au.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace tool_license\output;

use html_table;
use html_table_cell;
use html_table_row;
use html_writer;
use license_manager;

defined('MOODLE_INTERNAL') || die();

class table implements \renderable {

    /**
     * Get table row data for a license.
     *
     * @param object $license the license to populate row data for.
     * @param bool $canmoveup can this row move up.
     * @param bool $canmovedown can this row move down.
     *
     * @return \html_table_row of columns values for row.
     */
    protected function get_license_table_row_data(object $license, bool $canmoveup, bool $canmovedown) {
        global $CFG, $OUTPUT;

        $source = html_writer::link($license->source, $license->source, ['target' => '_blank']);

        $summary = $license->fullname . ' ('. $license->shortname . ')<br>' . $source;
        $summarycell = new html_table_cell($summary);
        $summarycell->attributes['class'] = 'license-summary';
        $versioncell = new html_table_cell($license->version);
        $versioncell->attributes['class'] = 'license-version';

        if ($license->shortname == $CFG->sitedefaultlicense) {
            $hideshow = $OUTPUT->pix_icon('t/locked', get_string('default'));
            $deletelicense = $OUTPUT->pix_icon('t/locked', get_string('default'));
        } else {
            if ($license->enabled == license_manager::LICENSE_ENABLED) {
                $hideshow = html_writer::link(\tool_license\helper::get_disable_license_url($license->shortname),
                    $OUTPUT->pix_icon('t/hide', get_string('disable')));
            } else {
                $hideshow = html_writer::link(\tool_license\helper::get_enable_license_url($license->shortname),
                    $OUTPUT->pix_icon('t/show', get_string('enable')));
            }

            if ($license->custom == license_manager::CUSTOM_LICENSE) {
                // Link url is added by the JS `delete_license` modal used for confirmation of deletion, to avoid
                // link being usable before JavaScript loads on page.
                $deletelicense = html_writer::link('#',
                    $OUTPUT->pix_icon('i/trash', get_string('delete')),
                    ['class' => 'delete-license', 'data-license' => $license->shortname]);
            } else {
                $deletelicense = '';
            }
        }
        $hideshowcell = new html_table_cell($hideshow);
        $hideshowcell->attributes['class'] = 'license-status';

        if ($license->custom == license_manager::CUSTOM_LICENSE) {
            $editlicense = html_writer::link(\tool_license\helper::get_update_license_url($license->shortname),
                $OUTPUT->pix_icon('t/editinline', get_string('edit')),
                ['class' => 'edit-license']);
        } else {
            $editlicense = '';
        }
        $editlicensecell = new html_table_cell($editlicense);
        $editlicensecell->attributes['class'] = 'edit-license';

        $spacer = $OUTPUT->pix_icon('spacer', '', 'moodle', ['class' => 'iconsmall']);
        $updown = '';
        if ($canmoveup) {
            $updown .= html_writer::link(\tool_license\helper::get_moveup_license_url($license->shortname),
                    $OUTPUT->pix_icon('t/up', get_string('up'), 'moodle', ['class' => 'iconsmall']),
                    ['class' => 'move-up']). '';
        } else {
            $updown .= $spacer;
        }

        if ($canmovedown) {
            $updown .= '&nbsp;'.html_writer::link(\tool_license\helper::get_movedown_license_url($license->shortname),
                    $OUTPUT->pix_icon('t/down', get_string('down'), 'moodle', ['class' => 'iconsmall']),
                    ['class' => 'move-down']);
        } else {
            $updown .= $spacer;
        }
        $updowncell = new html_table_cell($updown);
        $updowncell->attributes['class'] = 'license-order';

        $row = new html_table_row([$hideshowcell, $summarycell, $versioncell, $updowncell, $editlicensecell, $deletelicense]);
        $row->attributes['data-license'] = $license->shortname;
        $row->attributes['class'] = strtolower(get_string('license', 'tool_license'));

        return $row;
    }

    /**
     * Render the table.
     *
     * @return string XHTML.
     */
    public function output() {
        global $OUTPUT;

        $licenses = license_manager::get_licenses_in_order();

        // Add the create license button.
        $html = html_writer::link(\tool_license\helper::get_create_license_url(),
            get_string('createlicensebuttontext', 'tool_license'),
            ['class' => 'btn btn-secondary mb-3']);

        // Add the table containing licenses for management.
        $html .= $OUTPUT->box_start('generalbox editorsui');

        $table = new html_table();
        $table->head  = [
            get_string('enable'),
            get_string('license', 'tool_license'),
            get_string('version'),
            get_string('order'),
            get_string('edit'),
            get_string('delete'),
        ];
        $table->colclasses = [
            'text-center',
            'text-left',
            'text-left',
            'text-center',
            'text-center',
            'text-center',
        ];
        $table->id = 'manage-licenses';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = [];

        $rownumber = 0;
        $rowcount = count($licenses);

        foreach ($licenses as $key => $value) {
            $canmoveup = $rownumber > 0;
            $canmovedown = $rownumber < $rowcount - 1;
            $table->data[] = $this->get_license_table_row_data($value, $canmoveup, $canmovedown);
            $rownumber++;
        }

        $html .= html_writer::table($table);
        $html .= $OUTPUT->box_end();

        return $html;
    }
}
