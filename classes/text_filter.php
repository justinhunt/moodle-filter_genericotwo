<?php
namespace filter_genericotwo;

defined('MOODLE_INTERNAL') || die();

/**
 * Simple filter to replace G2 tags with a placeholder.
 *
 * @package    filter_genericotwo
 */
class text_filter extends \core_filters\text_filter {
    /**
     * Apply filter to text.
     *
     * @param string $text Text to be processed.
     * @param array  $options Filter options.
     * @return string Processed text.
     */
    public function filter($text, array $options = []) {
        // If we don't even have our tag, just bail out.
        if (strpos($text, '{G2:') === false) {
            return $text;
        }

        $search = '/{G2:.*?}/is';
        if (!is_string($text)) {
            // Non string data can not be filtered anyway.
            return $text;
        }
        $newtext = $text;

        $newtext = preg_replace_callback($search, [$this, 'filter_genericotwo_callback'], $newtext);

        if (is_null($newtext) || $newtext === $text) {
            // Error or not filtered.
            return $text;
        }

        return $newtext;

    }


    /**
     * Generico callback
     * @param array $link
     * @return mixed
     */
    private function filter_genericotwo_callback(array $matches)
    {
        global $CFG, $COURSE, $USER, $PAGE, $DB;

        $conf = get_object_vars(get_config('filter_genericotwo'));
        $context = false; // We get this if/when we need it.

        // Get our filter props.
        $filterprops = utils::fetch_filter_properties($matches[0]);

        // If we have no props, quit.
        if (empty($filterprops)) {
            return "";
        }

        // We use this to see if its a web service calling this.
        // in which case we return the alternate content.
        $climode = defined('CLI_SCRIPT') && CLI_SCRIPT;
        $iswebservice = false;
        if (!$climode) {
            // We get a warning here if the PAGE url is not set. But its not dangerous. just annoying.
            $iswebservice = strpos($PAGE->url, $CFG->wwwroot . '/webservice/') === 0;
        }

        // If we want to ignore the filter (for "how to use generico" or "cut and paste" this style use) we let it go
        // to use this, make the last parameter of the filter passthrough=1.
        if (!empty($filterprops['passthrough'])) {
            return str_replace(",passthrough=1", "", $matches[0]);
        }

        // Perform role/permissions check on this filter.
        if (!empty($filterprops['viewcapability'])) {
            if (!$context) {
                $context = \context_course::instance($COURSE->id);
            }
            if (!has_capability($filterprops['viewcapability'], $context)) {
                return '';
            }
        }
        if (!empty($filterprops['hidecapability'])) {
            if (!$context) {
                $context = \context_course::instance($COURSE->id);
            }
            if (has_capability($filterprops['hidecapability'], $context)) {
                return '';
            }
        }

        // Determine which template we are using.
        if (isset($filterprops['type']) && !empty($filterprops['type'])) {
            $template = $DB->get_record('filter_genericotwo_templates', ['templatekey' => $filterprops['type']]);
            if($template) {
                $mustachestring = $template->content;
            } else {
                return '';
            }
        } else {
            return '';
        }


        // Check context against the template index. -  not implemented yet TO DO
        /*
        if (!\filter_generico\generico_utils::is_context_allowed($this->context, $filterprops['type'])) {
            return '';
        }
        */

        //Ready to go so ..
        $renderer = $PAGE->get_renderer(constants::M_COMPONENT);
        return $renderer->do_render($mustachestring, $filterprops);
    }
}