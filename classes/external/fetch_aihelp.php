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
 * External function for fetching AI help.
 *
 * @package    filter_genericotwo
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_genericotwo\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;

defined('MOODLE_INTERNAL') || die();

class fetch_aihelp extends external_api {

    /**
     * Parameters for the execute function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'prompt' => new external_value(PARAM_RAW, 'The prompt/instructions from the user', VALUE_REQUIRED),
            'currentcode' => new external_value(PARAM_RAW, 'JSON encoded string containing all editor contents', VALUE_REQUIRED),
        ]);
    }

    /**
     * Execute the external function.
     *
     * @param string $prompt The user prompt.
     * @param string $currentcode The JSON encoded editor contents.
     * @return array Result containing status and JSON encoded responses.
     */
    public static function execute($prompt, $currentcode) {
        $params = self::validate_parameters(self::execute_parameters(), [
            'prompt' => $prompt,
            'currentcode' => $currentcode,
        ]);

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('filter/genericotwo:managetemplates', $context);

        // Decode the incoming JSON payload.
        /*
        $editors = json_decode($params['currentcode'], true);
        $responsedata = [];

        if (is_array($editors)) {
            foreach ($editors as $id => $content) {
                // For now, just return "hello world" for each editor.
                $responsedata[$id] = "hello world";
            }
        }
        */

        // Build the full prompt
        $thefullprompt = self::fetch_full_prompt($prompt, $currentcode);

        global $USER;
        $action = new \core_ai\aiactions\generate_text(
            contextid: $context->id,
            userid: $USER->id,
            prompttext: $thefullprompt
        );
        $manager = \core\di::get(\core_ai\manager::class);
        $llmresponse = $manager->process_action($action);
        $responsedata = $llmresponse->get_response_data();

        if (
            is_null($responsedata) ||
            !is_array($responsedata) ||
            !array_key_exists('generatedcontent', $responsedata) ||
            is_null($responsedata['generatedcontent'])
        ) {
            return [
                'status' => false,
                'response' => '',
                'message' => 'Failed to get valid response from AI provider.',
            ];
        }

        $generatedcontent = $responsedata['generatedcontent'];

        // Extract JSON in case the AI wraps it in backticks
        $jsonstart = strpos($generatedcontent, '{');
        $jsonend = strrpos($generatedcontent, '}');
        if ($jsonstart !== false && $jsonend !== false) {
            $generatedcontent = substr($generatedcontent, $jsonstart, $jsonend - $jsonstart + 1);
        }

        $airesponse = json_decode($generatedcontent);

        if (empty($airesponse) || !isset($airesponse->editors)) {
            return [
                'status' => false,
                'response' => '',
                'message' => get_string('jsonparsefail', 'filter_genericotwo'),
            ];
        }

        return [
            'status' => true,
            'response' => json_encode($airesponse->editors),
            'message' => $airesponse->description ?? get_string('aigensuccess', 'filter_genericotwo'),
        ];
    }

    /**
     * Returns description of method result value.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'True if successful'),
            'response' => new external_value(PARAM_RAW, 'JSON string containing new contents for editors'),
            'message' => new external_value(PARAM_TEXT, 'Error or success message', VALUE_OPTIONAL),
            'provider' => new external_value(PARAM_TEXT, 'The AI provider used', VALUE_OPTIONAL),
        ]);
    }

    private static function fetch_full_prompt($prompt, $currentcode) {
        // Build a prompt using the template below;
        $promptbits = [];
        $promptbits[] = "You are an expert front end developer for Moodle (a learning management system).";
        $promptbits[] = "You developing a front end widget using Moodle's Generic Two widget authoring system.";
        $promptbits[] = "The widget edit page contains 5 code editing areas.";
        $promptbits[] = "For HTML and JS code, Generico Two uses parameter placeholders of the format \{\{{parametername}\}\}. \n For SQL dataset parameters use ? placeholders.";
        $promptbits[] = "The five coding areas are:";
        $promptbits[] = "'id_content'. This is the main content area, which contains html and mustache. (field label: Body)";
        $tend = "'id_templateend'. This is an optional content area which also contains html and mustache. It is used when the user at runtime may place content between this code and the code from id_content. ";
        $tend .= "e.g for an audio player widget, the user may place a media link between the id_content code and the id_templateend code at runtime. (field label: Template End)";
        $promptbits[] = $tend;
        $examplejs = "require(['jquery','core/log'],
                function($, log) {
                    $('#{{AUTOID}}_greetingbox').text('hello');
                }
            );";
        $promptbits[] = "'id_jscontent'. This contains javascript, probably but not always, the definition of an AMD module. It will usually perform some action on the html/mustache content. (field label: JS Content). An example script is: " . PHP_EOL . $examplejs;
        $promptbits[] = "'id_dataset'. This contains SQL that may have ?parameters that will be replaced by user input values at runtime. (field label: Dataset Body)";
        $promptbits[] = "'id_customcss'. This is the custom css area. CSS declared is injected onto the page at runtime during page load. (field label: Custom CSS)";
        $promptbits[] = "The current editor content is:" . PHP_EOL . $currentcode;
        $promptbits[] = "You should follow the instructions below to add/edit editor content.";
        $promptbits[] = "Return a similarly structured JSON object to the current editor content, but with the suggested replacement contents for each of the editors, but only edit content that needs to be changed.";
        $promptbits[] = "Also return a text description of the changes you have made to the content.";
        $promptbits[] = "Your response should be a JSON object with two keys: 'editors' (the editor contents as a JSON object with the same keys as the current editor content) and 'description' (a text description of the changes you have made to the content).";
        $promptbits[] = "Your instructions for this task are:". PHP_EOL . $prompt;
        return implode(PHP_EOL, $promptbits);
    }

}
