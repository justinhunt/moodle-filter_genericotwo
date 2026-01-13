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

namespace filter_genericotwo;

use filter_genericotwo\constants;

/**
 * Generico utilities
 *
 * @package    filter_genericotwo
 * @subpackage genericotwo
 * @copyright  2025 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils {

    /**
     * Empty prop array
     * @return array
     */
    public static function fetch_emptyproparray() {
        $proparray = [];
        $proparray['AUTOID'] = '';
        $proparray['CSSLINK'] = '';
        return $proparray;
    }


    /**
     * Fetch filter properties
     *
     * @param string $filterstring
     * @return array
     */
    public static function fetch_filter_properties($filterstring) {
        // Let's do a general clean of all input here.
        $filterstring = clean_param($filterstring, PARAM_TEXT);

        // Remove the opening tag (G2 or GENERICO)
        $rawproperties = preg_replace('/^\{(?:G2|GENERICO):/i', '', $filterstring);
        
        // Remove the closing brace and any trailing content (though matched string usually ends with })
        // We split by closing brace to get the inner content
        $rawproperties = explode("}", $rawproperties);
        
        // Here we remove any html tags we find. They should not be in here
        // and we return the guts of the filter string for parsing.
        $rawproperties = strip_tags($rawproperties[0]);

        // Now we just have our properties string
        // Lets run our regular expression over them
        // string should be property=value,property=value
        // got this regexp from http://stackoverflow.com/questions/168171/regular-expression-for-parsing-name-value-pairs .
        $regexpression = '/([^=,]*)=("[^"]*"|[^,"]*)/';
        $matches = [];

        // Here we match the filter string and split into name array (matches[1]) and value array (matches[2]).
        // We then add those to a name value array.
        $itemprops = [];
        if (preg_match_all($regexpression, $rawproperties, $matches, PREG_PATTERN_ORDER)) {
            $propscount = count($matches[1]);
            for ($cnt = 0; $cnt < $propscount; $cnt++) {
                // Prepare the new value.
                $newvalue = $matches[2][$cnt];
                // This could be done better, I am sure. WE are removing the quotes from start and end.
                // This wil however remove multiple quotes id they exist at start and end. NG really.
                $newvalue = trim($newvalue, '"');

                // Remove any { or } characters from the new value - that would be some sort of variable injection.
                $newvalue = str_replace('{', '', $newvalue);
                $newvalue = str_replace('}', '', $newvalue);

                // Prepare the new key.
                $newkey = trim($matches[1][$cnt]);

                // Remove any attempts to overwrite simple system values via the key.
                $systemvars = ['AUTOID', 'WWWROOT', 'MOODLEPAGEID'];
                if (in_array($newkey, $systemvars)) {
                    continue;
                }

                // Remove any attempts to overwrite system values that are sets of data.
                $systemvarspartial = ['URLPARAM:', 'COURSE:', 'USER:', 'DATASET:'];
                foreach ($systemvarspartial as $systemvar) {
                    if (stripos($newkey, $systemvar) === 0) {
                        $newkey = '';
                        break;
                    }
                }
                if (empty($newkey)) {
                    continue;
                }

                // Store the key/value pair.
                $itemprops[$newkey] = $newvalue;
            }
        }
        return $itemprops;
    }



    /**
     * Update generico revision
     */
    public static function update_revision() {
        set_config('revision', time(), constants::M_COMPONENT);
    }


    /**
     * Determins if a specific context is allowed to use a given template
     *
     * @param context|null $context
     * @param int $templateidx Template index
     * @return bool true if allowed, else false.
     */
    public static function is_context_allowed(?\context $context, int $templatekey): bool {
        // Allowed context levels, e.g. "system", "course", "mod_xxxx".
        $allowedcontexts = self::explode_csv_list((string) get_config(constants::M_COMPONENT, 'allowedcontexts_' . $templatekey));
        if (!empty($allowedcontexts) && !in_array(self::get_context_name($context), $allowedcontexts)) {
            return false;
        }

        // Allowed specific context ids.
        $allowedcontextids = self::explode_csv_list((string) get_config(constants::M_COMPONENT, 'allowedcontextids_' . $templatekey));
        if (!empty($allowedcontextids) && !in_array($context->id, $allowedcontextids)) {
            return false;
        }

        return true;
    }

    /**
     * Explodes a CSV list of values and cleans any extra whitespace.
     *
     * @param string $csvlist string with csv values in it
     * @return array exploded values
     */
    private static function explode_csv_list(string $csvlist): array {
        return array_filter(array_map(fn($v) => trim($v), explode(',', $csvlist)));
    }

    /**
     * Get the context name
     *
     * @param context|null $context
     * @return string
     */
    private static function get_context_name(?\context $context): string {
        if (empty($context)) {
            return 'empty';
        }

        switch ($context->contextlevel) {
            case CONTEXT_MODULE:
                return 'mod_' . get_coursemodule_from_id(null, $context->instanceid, 0, false, MUST_EXIST)->modname;
            // We would use get_short_name here, but that is only available in 4.2+, so we must hardcode it :(.
            case CONTEXT_SYSTEM:
                return 'system';
            case CONTEXT_USER:
                return 'user';
            case CONTEXT_COURSE:
                return 'course';
            case CONTEXT_COURSECAT:
                return 'coursecat';
            case CONTEXT_BLOCK:
                return 'block';
            default:
                throw new \coding_exception("Unhandled contextlevel " . $context->contextlevel);
        }
    }
}
