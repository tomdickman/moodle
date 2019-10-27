<?php

/**
 * Renderable for table of licenses class.
 *
 * @package    tool_licensemanager
 * @copyright  2019 Tom Dickman <tomdickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_licensemanager\output;

use renderable;
use renderer_base;
use stdClass;
use templatable;

defined('MOODLE_INTERNAL') || die();

class license_table implements renderable, templatable {

    /**
     * @var array of objects representing licenses.
     */
    private $licenses;

    public function __construct($licenses) {
        $this->licenses = $licenses;
    }

    public function export_for_template(renderer_base $output) {
        $context = new stdClass();
        $context->licenses = $this->licenses;

    }

}