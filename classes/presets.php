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
        $presets = self::fetch_presets();
        foreach ($presets as $preset) {
            if (isset($preset['templatekey']) && $preset['templatekey'] === $key) {
                return (object)$preset;
            } else if (isset($preset['key']) && $preset['key'] === $key) {
                return (object)$preset;
            }
        }
        return false;
    }
}
