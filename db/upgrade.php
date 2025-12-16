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
 * Upgrade script for filter_genericotwo.
 *
 * @package    filter_genericotwo
 * @copyright  2024 OpenAI Codex
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for filter_genericotwo.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool true on success.
 */
function xmldb_filter_genericotwo_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025121601) {
        // Define table filter_genericotwo_templates to be created.
        $table = new xmldb_table('filter_genericotwo_templates');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', true, true, true);
        $table->add_field('version', XMLDB_TYPE_CHAR, '255', null, true);
        $table->add_field('templatekey', XMLDB_TYPE_CHAR, '255', null, true);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, true);
        $table->add_field('customcss', XMLDB_TYPE_TEXT, null, null, false);
        $table->add_field('variables', XMLDB_TYPE_TEXT, null, null, false);
        $table->add_field('content', XMLDB_TYPE_TEXT, null, null, true);
        $table->add_field('jscontent', XMLDB_TYPE_TEXT, null, null, false);
        $table->add_field('previewcontext', XMLDB_TYPE_TEXT, null, null, false);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, true, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, true, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2025121601, 'filter', 'genericotwo');
    }

    return true;
}