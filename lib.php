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

/**
 * Library of functions and constants for filter_genericotwo.
 *
 * @package    filter_genericotwo
 * @copyright  2024 OpenAI Codex
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the HTML for the Generico Two template preview fragment.
 *
 * @param array $args The arguments for the fragment, including the form data.
 * @return string The HTML for the preview.
 */
function filter_genericotwo_output_fragment_preview($args) {
    global $PAGE, $CFG;
    require_once($CFG->dirroot . '/filter/genericotwo/classes/text_filter.php');

    $args = (object) $args;
    $formdata = [];
    parse_str($args->formdata, $formdata);

    // Create a dummy template object from the form data.
    $template = new stdClass();
    $template->content = isset($formdata['content']) ? $formdata['content'] : '';
    $template->jscontent = isset($formdata['jscontent']) ? $formdata['jscontent'] : '';
    $template->templateend = isset($formdata['templateend']) ? $formdata['templateend'] : '';
    $template->variabledefaults = isset($formdata['variabledefaults']) ? $formdata['variabledefaults'] : '';
    $template->dataset = isset($formdata['dataset']) ? $formdata['dataset'] : '';
    $template->datasetvars = isset($formdata['datasetvars']) ? $formdata['datasetvars'] : '';
    $template->customcss = isset($formdata['customcss']) ? $formdata['customcss'] : '';
    $template->importcss = isset($formdata['importcss']) ? $formdata['importcss'] : '';
    // Use the id if available for cache busting or paths.
    $template->id = isset($formdata['id']) ? $formdata['id'] : 0;

    // Choose which test string to run.
    // The form will pass an argument 'testfield' ('test1' or 'test2') to know which one was clicked.
    $testtouse = isset($args->testfield) ? $args->testfield : 'test1';
    $filterstring = isset($formdata[$testtouse]) ? $formdata[$testtouse] : '';

    if (empty($filterstring)) {
        return html_writer::tag('div', get_string('nopreviewstring', 'filter_genericotwo'), ['class' => 'alert alert-info']);
    }

    // Since we're in a fragment, we have to collect any requirements and append them.
    // We instantiate the text_filter class without the $context property, as it's not strictly needed for the dummy pass.
    $filter = new \filter_genericotwo\text_filter(context_system::instance(), []);
    $html = $filter->preview_filter($template, $filterstring);

    // After calling the filter, the renderer might have added JS or CSS to $PAGE->requires.
    // Moodle's fragment logic will automatically capture standard $PAGE->requires added during the fragment rendering.

    // If there is custom CSS, since css.php needs the template ID, and we haven't saved it yet,
    // we should render it directly inline for the preview instead of using the URL.
    $customcsshtml = '';
    if (!empty($template->customcss)) {
        // Simple scoped css approximation or just raw styles.
        $customcsshtml = html_writer::tag('style', $template->customcss);
    }

    // Check for imported CSS.
    $importcsshtml = '';
    if (!empty($template->importcss)) {
        $importcss = $template->importcss;
        $scheme = parse_url($CFG->wwwroot, PHP_URL_SCHEME) . ':';
        if (strpos($importcss, '//') === 0) {
            $importcss = $scheme . $importcss;
        } else if (strpos($importcss, '/') === 0) {
            $importcss = $CFG->wwwroot . $importcss;
        }
        $importcsshtml = html_writer::empty_tag('link', ['rel' => 'stylesheet', 'href' => $importcss]);
    }

    // Moodle Fragment API returns the HTML.
    return $importcsshtml . $customcsshtml . $html;
}
