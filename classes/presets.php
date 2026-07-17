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

namespace filter_genericotwo;

defined('MOODLE_INTERNAL') || die();

/**
 * Presets helper class
 *
 * @package    filter_genericotwo
 */
class presets {

    /**
     * Parse preset template
     *
     * @param \SplFileInfo $fileinfo
     * @return array|false
     */
    protected static function parse_preset_template(\SplFileInfo $fileinfo) {
        $file = $fileinfo->openFile("r");
        $content = "";
        while (!$file->eof()) {
            $content .= $file->fgets();
        }
        $presetobject = json_decode($content);
        if ($presetobject && is_object($presetobject)) {
            return get_object_vars($presetobject);
        } else {
            return false;
        }
    }

    /**
     * Fetch presets
     * @return array
     */
    public static function fetch_presets() {
        global $CFG, $PAGE;
        // Init return array.
        $ret = [];
        $dirs = [];

        // We search the plugin "presets" and the themes "genericotwo" folders for presets.
        $presetsdir = $CFG->dirroot . '/filter/genericotwo/presets';
        $themepresetsdir = $PAGE->theme->dir . '/genericotwo';

        if (file_exists($presetsdir)) {
            $dirs[] = new \DirectoryIterator($presetsdir);
        }
        if (file_exists($themepresetsdir)) {
            $dirs[] = new \DirectoryIterator($themepresetsdir);
        }

        foreach ($dirs as $dir) {
            foreach ($dir as $fileinfo) {
                if (!$fileinfo->isDot() && !$fileinfo->isDir()) {
                    // Check extension
                    if ($fileinfo->getExtension() !== 'txt' && $fileinfo->getExtension() !== 'json') {
                         continue;
                    }
                    $preset = self::parse_preset_template($fileinfo);
                    if ($preset) {
                        $ret[] = $preset;
                    }
                }
            }
        }

        uasort(
            $ret,
            fn($a, $b) => strnatcasecmp($a['name'], $b['name'])
        );
        return $ret;
    }

    /**
     * Fetch a specific preset by key
     * @param string $key
     * @return \stdClass|false
     */
    public static function fetch_preset($key) {
        $presets = self::fetch_presets_by_key();
        if (array_key_exists($key, $presets)) {
            return (object)$presets[$key];
        }
        return false;
    }

    /**
     * Normalise a preset to Generico Two field names.
     *
     * Legacy Generico presets use different field names (key, body, script etc) and
     * double-at variable placeholders. This maps them to our names and {{mustache}}
     * placeholders, mirroring the client-side mapping in amd/src/presets.js.
     *
     * @param array $preset raw preset fields
     * @return array preset with Generico Two field names
     */
    protected static function normalise_preset(array $preset) {
        // Legacy Generico field name => our field name.
        $legacyfields = [
            'key' => 'templatekey',
            'body' => 'content',
            'bodyend' => 'templateend',
            'requirecss' => 'importcss',
            'style' => 'customcss',
            'script' => 'jscontent',
            'defaults' => 'variabledefaults',
        ];

        $islegacy = !isset($preset['templatekey']) && isset($preset['key']);
        foreach ($legacyfields as $oldname => $newname) {
            if (!isset($preset[$newname]) && isset($preset[$oldname])) {
                $preset[$newname] = $preset[$oldname];
            }
        }
        if ($islegacy) {
            // Convert legacy @@variable@@ placeholders to {{mustache}} placeholders.
            foreach ($preset as $field => $value) {
                if (is_string($value)) {
                    $preset[$field] = preg_replace('/@@([^@]+)@@/', '{{$1}}', $value);
                }
            }
        }
        return $preset;
    }

    /**
     * Fetch all presets keyed by template key.
     *
     * Presets are normalised to Generico Two field names. If two presets share a key
     * (e.g. a theme preset shadowing a bundled one), the highest version wins.
     *
     * @return array of preset arrays, keyed by templatekey
     */
    public static function fetch_presets_by_key() {
        $ret = [];
        foreach (self::fetch_presets() as $preset) {
            $preset = self::normalise_preset($preset);
            if (empty($preset['templatekey'])) {
                continue;
            }
            $key = $preset['templatekey'];
            if (array_key_exists($key, $ret)) {
                $existingversion = (string)($ret[$key]['version'] ?? '');
                $newversion = (string)($preset['version'] ?? '');
                if (version_compare($newversion, $existingversion) <= 0) {
                    continue;
                }
            }
            $ret[$key] = $preset;
        }
        return $ret;
    }

    /**
     * Check if a newer preset exists for a registered template.
     *
     * @param \stdClass $template template record from filter_genericotwo_templates
     * @param array|null $presetmap output of fetch_presets_by_key(), to avoid re-reading preset files
     * @return string|false the preset version if it is newer than the template's, else false
     */
    public static function template_has_update(\stdClass $template, ?array $presetmap = null) {
        if ($presetmap === null) {
            $presetmap = self::fetch_presets_by_key();
        }
        if (!array_key_exists($template->templatekey, $presetmap)) {
            return false;
        }
        $presetversion = (string)($presetmap[$template->templatekey]['version'] ?? '');
        if ($presetversion !== '' && version_compare($presetversion, (string)$template->version) > 0) {
            return $presetversion;
        }
        return false;
    }

    /**
     * Update a registered template from its (newer) preset.
     *
     * Overwrites the authored content fields with the preset values. Site-local fields
     * (allowedcontexts, allowedcontextids, test1, test2) are deliberately preserved.
     *
     * @param \stdClass $template template record from filter_genericotwo_templates
     * @param array|null $presetmap output of fetch_presets_by_key(), to avoid re-reading preset files
     * @return bool true if the template was updated
     */
    public static function update_template_from_preset(\stdClass $template, ?array $presetmap = null) {
        global $DB;

        if ($presetmap === null) {
            $presetmap = self::fetch_presets_by_key();
        }
        if (!self::template_has_update($template, $presetmap)) {
            return false;
        }
        $preset = $presetmap[$template->templatekey];

        // The authored content fields owned by the preset. Missing fields are blanked so
        // stale content (e.g. old JS) never lingers under a new version number.
        $presetfields = ['version', 'name', 'instructions', 'content', 'templateend',
            'jscontent', 'variabledefaults', 'importcss', 'customcss', 'dataset', 'datasetvars'];

        $record = new \stdClass();
        $record->id = $template->id;
        foreach ($presetfields as $field) {
            $record->{$field} = isset($preset[$field]) ? $preset[$field] : '';
        }
        $record->instructionsformat = FORMAT_HTML;
        // Bump timemodified so the css.php?rev= cache buster picks up new custom CSS.
        $record->timemodified = time();
        $DB->update_record('filter_genericotwo_templates', $record);
        return true;
    }

    /**
     * Update all registered templates that have a newer preset available.
     *
     * @return int the number of templates updated
     */
    public static function update_all_templates() {
        global $DB;

        $presetmap = self::fetch_presets_by_key();
        $updatecount = 0;
        $templates = $DB->get_records('filter_genericotwo_templates');
        foreach ($templates as $template) {
            if (self::update_template_from_preset($template, $presetmap)) {
                $updatecount++;
            }
        }
        return $updatecount;
    }
}
