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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Admin settings for filter_genericotwo.
 *
 * @package    filter_genericotwo
 * @copyright  2026 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// This prevents a section of name: filtersettinggenericotwo being added
// We use this name for the general settings page so that "settings" links..
// For the G2 filter in manage_filters page go to the right place.
$settings = null;

if ($hassiteconfig) {
    // Add folder in property tree for settings pages.
    $g2categoryname = 'filter_genericotwo';
    $ADMIN->add('filtersettings', new admin_category($g2categoryname, get_string('pluginname', 'filter_genericotwo')));
    $settingspage = new \filter_genericotwo\admin\settings_page(
        'filtersettinggenericotwo', get_string('generalsettings', 'admin')
    );
    $ADMIN->add($g2categoryname, $settingspage);
    // Set breadcrumbs before the header, only on this plugin's own settings page.
    if ($PAGE->pagetype === 'admin-setting-filtersettinggenericotwo') {
        \filter_genericotwo\output\navigation::breadcrumbs(get_string('settings'));
    }
    $settingspage->add(new admin_setting_configcheckbox(
        'filter_genericotwo/enableaihelper',
        get_string('enableaihelper', 'filter_genericotwo'),
        get_string('enableaihelper_desc', 'filter_genericotwo'),
        1
    ));
    $settingspage->add(new admin_setting_configcheckbox(
        'filter_genericotwo/handlelegacytags',
        get_string('handlelegacytags', 'filter_genericotwo'),
        get_string('handlelegacytags_desc', 'filter_genericotwo'),
        0
    ));

    $ADMIN->add($g2categoryname, new admin_externalpage(
        'filter_genericotwo_migrate',
        get_string('migratelegacy', 'filter_genericotwo'),
        new moodle_url('/filter/genericotwo/migrate.php'),
        'moodle/site:config'
    ));

    $ADMIN->add($g2categoryname, new admin_externalpage(
        'filter_genericotwo_templates',
        get_string('templates', 'filter_genericotwo'),
        new moodle_url('/filter/genericotwo/templates.php'),
        'moodle/site:config'
    ));
}
