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

        // Add a unique id to the filter props.
        $filterprops['uniqid'] = uniqid('fg_');
        

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
                $jsstring = $template->jscontent;
                $datasetvars = $template->datasetvars;
            } else {
                return '';
            }
        } else {
            return '';
        }

        // Check context is allowed.
        if(!empty($template->allowedcontexts) || !empty($template->allowedcontextids)) {
            if (!$context) {
                $context = \context_course::instance($COURSE->id);
            }
            if (!$this->is_context_allowed($context, $template)) {
                return '';
            }
        }

        //Add context from /  URLPARAMS / COURSE/ USER / Defaults
        $haystack = $mustachestring . ' ' . $datasetvars . ' ' . $jsstring;
        // Fetch URL params for this template
        $urlprops = $this->fetch_url_params($haystack);
        if (!empty($urlprops)) {
            $filterprops = array_merge($filterprops, $urlprops);
        }

        // Fetch course props for this template
        $courseprops = $this->fetch_course_props($haystack);
        if (!empty($courseprops)) {
            $filterprops = array_merge($filterprops, $courseprops);
        }

         //Fetch user props for this template
        $userprops = $this->fetch_user_props($haystack);
        if (!empty($userprops)) {
            $filterprops = array_merge($filterprops, $userprops);
        }

        // Fetch defaults for this template.
        $defaults = $template->variabledefaults;
        $defaultprops = [];
        if (!empty($defaults)) {
            $defaults = "{G2:" . $defaults . "}";
            $defaultprops = \filter_genericotwo\utils::fetch_filter_properties($defaults);
            // Replace our defaults, if not spec in the the filter string.
            if (!empty($defaultprops)) {
                foreach ($defaultprops as $name => $value) {
                    // This prevents overwriting any props that are already set.
                    if (!array_key_exists($name, $filterprops)) {
                        // If we have options as defaults, lets just take the first one.
                        if (strpos($value, '|') !== false) {
                            $value = explode('|', $value)[0];
                            $defaultprops[$name] = $value;
                        }
                    }
                }   
            }
        }
        if (!empty($defaultprops)) {
            $filterprops = array_merge($filterprops, $defaultprops);
        }
        
       
       // Dataset
        if(!empty($template->dataset)) {
             //replace any variables from filterprops in datasetvars 
             // A dataset vars might look like: {{COURSE:id}},{{USER:firstname}},'hello',{{weather}}
             // in filterprops we might have: COURSE:id, USER:firstname, weather
             // so we surround our filterprops field name with {{ and }} and do an str_replace
             // We DON'T put any variables in the dataset body, because we want to put them all through moodles cleaning process
             if(!empty($template->datasetvars)) {
                 $datasetvars = $template->datasetvars;
                 foreach ($filterprops as $name => $value) {
                     $datasetvars = str_replace('{{' . $name . '}}', $value, $datasetvars);
                 }
             }
            // Fetch dataset for this template.
            $dataset = $this->fetch_dataset($template, $datasetvars);
            if (!empty($dataset)) {
                $filterprops['DATASET'] = $dataset;
            } else {
                $filterprops['DATASET'] = [];
            }   
        } 

        //Ready to go so ..
        $renderer = $PAGE->get_renderer(constants::M_COMPONENT);
        return $renderer->do_render($mustachestring, $jsstring, $filterprops);
    }

    private function fetch_url_params($templatebody) {
        $filterprops = [];
        if (strpos($templatebody, '{{URLPARAM:') !== false) {
            $matches = [];
            preg_match_all('/\{\{URLPARAM:([^}]+)\}\}/', $templatebody, $matches);
            $thefields = array_unique($matches[1]);

            foreach ($thefields as $urlprop) {

                if (empty($urlprop)) {
                    continue;   
                }

                // Check if it exists in the params to the url and if so, set it.
                $propvalue = optional_param($urlprop, '', PARAM_TEXT);
                
                // Add prop to return array
                $filterprops['URLPARAM:' . $urlprop] = $propvalue;
            }
        }
        return $filterprops;
    }

    private function fetch_course_props($templatebody) {
        global $COURSE;
        $filterprops = [];

        if (strpos($templatebody, '{{COURSE:') !== false) {
            $coursevars = get_object_vars($COURSE);
            
            // Custom fields.
            if (class_exists('\core_customfield\handler')) {
                $handler = \core_customfield\handler::get_handler('core_course', 'course');
                $customfields = $handler->get_instance_data($COURSE->id);
                foreach ($customfields as $customfield) {
                    if (empty($customfield->get_value())) {
                        continue;
                    }
                    $shortname = $customfield->get_field()->get('shortname');
                    $coursevars[$shortname] = $customfield->get_value();
                }
            }

            $matches = [];
            preg_match_all('/\{\{COURSE:([^}]+)\}\}/', $templatebody, $matches);
            $thefields = array_unique($matches[1]);

            foreach ($thefields as $thefield) {
                $propvalue = false;
                $courseprop = strtolower($thefield);

                // Check if it exists in course.
                if (array_key_exists($courseprop, $coursevars)) {
                    $propvalue = $coursevars[$courseprop];
                } else if ($courseprop == 'contextid') {
                    $context = \context_course::instance($COURSE->id);
                    if ($context) {
                        $propvalue = $context->id;
                    }
                }

                // If we have a propname and a propvalue, do the replace.
                if (!empty($courseprop) && !is_null($propvalue)) {
                    $filterprops['COURSE:' . $thefield] = $propvalue;
                }
            }
        }
        return $filterprops; 
    }

    private function fetch_user_props($templatebody) {
        global $USER, $CFG; 
        $filterprops = [];

        // If we have user variables e.g {{USER:firstname}}
        if (strpos($templatebody, '{{USER:') !== false) {
            $uservars = get_object_vars($USER);
            $matches = [];
            // This pattern looks for {{USER: followed by any characters until the closing }}
            // The ([^}]+) captures the "someprop" part
            preg_match_all('/\{\{USER:([^}]+)\}\}/', $templatebody, $matches);
            // $matches[1] contains the captured groups (the property names)
            $thefields = array_unique($matches[1]);    
 
         
            // User Props.
            $profileprops = false;
            foreach ($thefields as $thefield) {
                // Init our prop value.
                $propvalue = false;

                // Lowercase the property name.
                $userprop = strtolower($thefield);

                // Check if it exists in user, else look for it in profile fields.
                if (array_key_exists($userprop, $uservars)) {
                    $propvalue = $uservars[$userprop];
                } else {
                    if (!$profileprops) {
                        require_once("$CFG->dirroot/user/profile/lib.php");
                        $profileprops = get_object_vars(profile_user_record($USER->id));
                    }
                    if ($profileprops && array_key_exists($userprop, $profileprops)) {
                        $propvalue = $profileprops[$userprop];
                    } else {
                        switch ($userprop) {
                            case 'picurl':
                                require_once("$CFG->libdir/outputcomponents.php");
                                global $PAGE;
                                $userpicture = new \user_picture($USER);
                                $propvalue = $userpicture->get_url($PAGE);
                                break;

                            case 'pic':
                                global $OUTPUT;
                                $propvalue = $OUTPUT->user_picture($USER, ['popup' => true]);
                                break;
                        }
                    }
                }

                // If we have a propname and a propvalue, do the replace.
                if (!empty($userprop) && !is_null($propvalue)) {
                    // Add prop to return array
                    $filterprops['USER:' . $thefield] = $propvalue;
                }
            }
        }//end of of we {{USER:xxx}}    
        return $filterprops;
    }

    private function fetch_dataset($template, $datasetvars) {
        global $DB;
        $vars = [];
        if ($datasetvars && !empty($datasetvars)) {
            $vars = explode(',', $datasetvars);
        }
        // Turn numeric vars into numbers (not strings).
        $queryvars = [];
        for ($i = 0; $i < count($vars); $i++) {
            if (is_numeric($vars[$i])) {
                $queryvars[] = 0 + $vars[$i];
            } else {
                $queryvars[] = $vars[$i];
            }
        }

        try {
            $alldata = $DB->get_records_sql($template->dataset, $queryvars);
            if ($alldata) {
                return array_values($alldata);
            } else {
                return [];
            }
        } catch (Exception $e) {
            return [];
        }
        
    }

    /**
     * Determines if a specific context is allowed to use a given template
     *
     * @param context|null $context
     * @param int $templateidx Template index
     * @return bool true if allowed, else false.
     */
    private function is_context_allowed(\context $context, $template): bool {
        // Allowed context levels, e.g. "system", "course", "mod_xxxx".
        $allowedcontexts = $this->explode_csv_list($template->allowedcontexts);
        if (!empty($allowedcontexts) && !in_array($this->get_context_name($context), $allowedcontexts)) {
            return false;
        }

        // Allowed specific context ids.    
        $allowedcontextids = $this->explode_csv_list($template->allowedcontextids);
        if (!empty($allowedcontextids) && !in_array($context->id, $allowedcontextids)) {
            return false;
        }

        return true;
    }

     /**
     * Get the context name
     *
     * @param context|null $context
     * @return string
     */
    private function get_context_name(\context $context): string {
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

    /**
     * Explodes a CSV list of values and cleans any extra whitespace.
     *
     * @param string $csvlist string with csv values in it
     * @return array exploded values
     */
    private function explode_csv_list(string $csvlist): array {
        if(!$csvlist || empty($csvlist)) {
            return [];
        }
        return array_filter(array_map(fn($v) => trim($v), explode(',', $csvlist)));
    }

}