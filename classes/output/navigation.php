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

namespace filter_genericotwo\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Shared navigation for the plugin's administration pages.
 *
 * @package    filter_genericotwo
 * @copyright  2026 Andreas Giesen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class navigation {
    /**
     * Render native Moodle tabs, including a linked active Templates tab.
     *
     * @param string $active The current section: templates, migration or settings.
     * @return string
     */
    public static function tabs(string $active): string {
        global $CFG, $OUTPUT;

        $tabs = [
            new \tabobject('templates', new \moodle_url('/filter/genericotwo/templates.php'),
                get_string('templates', 'filter_genericotwo'), '', true),
            new \tabobject('migration', new \moodle_url('/filter/genericotwo/migrate.php'),
                get_string('migration', 'filter_genericotwo')),
            new \tabobject('settings', new \moodle_url('/' . $CFG->admin . '/settings.php',
                ['section' => 'filtersettinggenericotwo']), get_string('settings')),
        ];

        return $OUTPUT->tabtree($tabs, $active);
    }

    /**
     * Add a consistent breadcrumb with a link back to the template list.
     *
     * Moodle supplies Home; the final page label is deliberately not linked.
     *
     * @param string|null $label The editor, migration or settings page label.
     */
    public static function breadcrumbs(?string $label = null): void {
        global $CFG, $PAGE;

        // The admin tree can be rebuilt during settings validation.
        if ($PAGE->navbar->get('filter_genericotwo')) {
            return;
        }

        $PAGE->navbar->ignore_active();
        $PAGE->navbar->add(get_string('administrationsite'), new \moodle_url('/' . $CFG->admin . '/index.php'));
        $PAGE->navbar->add(get_string('filters', 'admin'),
            new \moodle_url('/' . $CFG->admin . '/category.php', ['category' => 'filtersettings']));
        $PAGE->navbar->add(get_string('navigationtitle', 'filter_genericotwo'),
            new \moodle_url('/filter/genericotwo/templates.php'), \navigation_node::TYPE_CUSTOM,
            null, 'filter_genericotwo');
        if ($label !== null) {
            $PAGE->navbar->add($label);
        }
    }
}
