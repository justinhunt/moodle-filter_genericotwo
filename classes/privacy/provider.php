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

namespace filter_genericotwo\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Privacy Subsystem implementation for filter_genericotwo.
 *
 * The plugin stores one user preference. AI helper requests are handled, recorded and
 * exported by the core_ai subsystem, so they are declared as a link to that subsystem.
 *
 * @package    filter_genericotwo
 * @copyright  2026 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\user_preference_provider {
    /**
     * Describe the personal data this plugin stores or passes on.
     *
     * @param collection $collection The collection to add metadata to.
     * @return collection The updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_user_preference(
            'filter_genericotwo_templates_fullwidth',
            'privacy:preference:templates_fullwidth'
        );
        $collection->link_subsystem('core_ai', 'privacy:metadata:core_ai');
        return $collection;
    }

    /**
     * Export the user preferences belonging to this plugin.
     *
     * @param int $userid The id of the user whose preferences are exported.
     */
    public static function export_user_preferences(int $userid) {
        $fullwidth = get_user_preferences('filter_genericotwo_templates_fullwidth', null, $userid);
        if ($fullwidth !== null) {
            writer::export_user_preference(
                'filter_genericotwo',
                'filter_genericotwo_templates_fullwidth',
                transform::yesno($fullwidth),
                get_string('privacy:preference:templates_fullwidth', 'filter_genericotwo')
            );
        }
    }
}
