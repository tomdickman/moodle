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
 * Shown tours filter. Used to determine if USER should see a tour.
 *
 * @package    tool_usertours
 * @copyright  2019 Tom Dickman <tomdickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_usertours\local\filter;

defined('MOODLE_INTERNAL') || die();

use tool_usertours\tour;
use context;

/**
 * Shown tours filter. Used to determine if USER should see a tour.
 *
 * @copyright  2019 Tom Dickman <tomdickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class shown extends base {

    /**
     * The name of the filter.
     *
     * @return  string
     */
    public static function get_filter_name() {
        return 'shown';
    }

    /**
     * Retrieve the list of available filter options.
     *
     * @return  array  An array whose keys are the valid options
     *                 And whose values are the values to display
     * @throws \coding_exception
     */
    public static function get_filter_options() {

        return array(
            tour::FILTER_LAST_UPDATE => get_string('filter_last_update', 'tool_usertours'),
            tour::FILTER_ACCOUNT_CREATION => get_string('filter_account_creation', 'tool_usertours'),
            tour::FILTER_FIRST_LOGIN => get_string('filter_first_login', 'tool_usertours'),
            tour::FILTER_LAST_LOGIN => get_string('filter_last_login', 'tool_usertours'),
        );

    }

    /**
     * Add the form elements for the filter to the supplied form.
     * Overrides the base method to ommit the 'All' option.
     *
     * @param   MoodleQuickForm $mform      The form to add filter settings to.
     */
    public static function add_filter_to_form(\MoodleQuickForm &$mform) {
        $options = static::get_filter_options();

        $filtername = static::get_filter_name();
        $key = "filter_{$filtername}";

        $radioarray = array();
        foreach ($options as $value => $option) {
            $radioarray[] = $mform->createElement('radio', $key, '', $option, $value);
        }
        $mform->addGroup($radioarray, $key, get_string($key, 'tool_usertours'), ' ', false);

        $mform->setDefault($key, tour::FILTER_LAST_UPDATE);
        $mform->addHelpButton($key, $key, 'tool_usertours');

    }

    /**
     * Prepare the filter values for the form.
     *
     * @param   tour            $tour       The tour to prepare values from
     * @param   stdClass        $data       The data value
     * @return  stdClass
     */
    public static function prepare_filter_values_for_form(tour $tour, \stdClass $data) {
        $filtername = static::get_filter_name();

        $key = "filter_{$filtername}";
        $values = $tour->get_filter_values($filtername);
        if (empty($values)) {
            $data->$key = tour::FILTER_LAST_UPDATE;
        } else {
            // Single value field only, assume zeroth array element.
            $data->$key = reset($values);
        }

        return $data;
    }

}
