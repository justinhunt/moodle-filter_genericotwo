<?php
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
}
