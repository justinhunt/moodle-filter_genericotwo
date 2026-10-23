<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

namespace filter_genericotwo\admin;

defined('MOODLE_INTERNAL') || die();

/**
 * Native admin settings page with the plugin's section tabs.
 *
 * @package    filter_genericotwo
 * @copyright  2026 Andreas Giesen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_page extends \admin_settingpage {
    /**
     * Prepend navigation while retaining Moodle's settings form and validation.
     *
     * @return string
     */
    public function output_html() {
        return \filter_genericotwo\output\navigation::tabs('settings') . parent::output_html();
    }
}
