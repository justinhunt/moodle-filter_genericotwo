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

class fetch_aihelp extends \core_external\external_api {

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
        $editors = json_decode($params['currentcode'], true);
        $responsedata = [];

        if (is_array($editors)) {
            foreach ($editors as $id => $content) {
                // For now, just return "hello world" for each editor.
                $responsedata[$id] = "hello world";
            }
        }

        return [
            'status' => true,
            'response' => json_encode($responsedata),
            'message' => 'Success',
            'provider' => 'stub',
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
}
