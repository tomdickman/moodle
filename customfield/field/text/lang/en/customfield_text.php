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
 * Customfield text plugin
 *
 * @package   customfield_text
 * @copyright 2018 Toni Barbera <toni@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['displayrows'] = 'Form input height';
$string['displayrows_help'] = 'The height of the textarea in rows, this is only utilised if form input is a textarea';
$string['displaysize'] = 'Form input size';
$string['displaysize_help'] = 'The width of the input in characters (for a textarea input this is the number of columns displayed)';
$string['errorconfigdisplayrows'] = 'The form input height must be between 1 and 10 rows.';
$string['errorconfigdisplaysize'] = 'The form input size must be between 1 and 200 characters.';
$string['errorconfiglinkplaceholder'] = 'The link must contain a placeholder $$.';
$string['errorconfiglinksyntax'] = 'The link must be a valid URL starting with either http:// or https://.';
$string['errorconfigmaxlen'] = 'The maximum number of characters allowed must be between 1 and 1333.';
$string['errormaxlength'] = 'The maximum number of characters allowed in this field is {$a}.';
$string['errortextareapasswordconflict'] = 'Text area fields cannot be password fields.';
$string['islink'] = 'Link field';
$string['islink_help'] = 'To transform the text into a link, enter a URL containing $$ as a placeholder, where $$ will be replaced with the text. For example, to transform a Twitter ID to a link, enter https://twitter.com/$$.';
$string['ispassword'] = 'Password field';
$string['istextarea'] = 'Use textarea';
$string['istextarea_help'] = 'Select to display a larger, multi-line textarea input type for this field when used in forms, rather than a single line text input type. This is good for fields which are expected to contain longer values spanning multiple lines and/or paragraphs.';
$string['linktarget'] = 'Link target';
$string['maxlength'] = 'Maximum number of characters';
$string['newwindow'] = 'New window';
$string['none'] = 'None';
$string['pluginname'] = 'Short text';
$string['privacy:metadata'] = 'The Short text field type plugin doesn\'t store any personal data; it uses tables defined in core.';
$string['sameframe'] = 'Same frame';
$string['samewindow'] = 'Same window';
$string['specificsettings'] = 'Short text field settings';
