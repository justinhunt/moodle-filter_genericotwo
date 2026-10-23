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
 * Strings for component 'filter_genericotwo'.
 *
 * @package    filter_genericotwo
 * @copyright  2026 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addtemplate'] = 'Add template';
$string['aigensuccess'] = "AI generation successful";
$string['aihelper_apply_btn'] = 'Apply';
$string['aihelper_generate_btn'] = 'Generate';
$string['aihelper_generating'] = 'Generating...';
$string['aihelper_modal_instruction'] = 'Enter instructions for the AI to modify the template content. The AI will receive the content of all editors on this page.';
$string['aihelper_modal_title'] = 'AI wizard';
$string['aihelper_prompt_label'] = 'Instructions';
$string['aihelper_prompt_placeholder'] = 'E.g., Format the HTML, add a basic layout, etc.';
$string['aihelper_response_label'] = 'AI response';
$string['airesponsetruncated'] = 'The AI response was cut off before it finished (the model reached its output limit). Try a more specific instruction that changes fewer editors, or use a model with a larger output limit.';
$string['bundle'] = 'Bundle';
$string['deleteconfirm'] = 'Are you sure you want to delete this template?';
$string['deletetemplate'] = 'Delete template';
$string['edittemplate'] = 'Edit template';
$string['enableaihelper'] = 'Enable AI helper';
$string['enableaihelper_desc'] = 'Enable the AI wizard button for template editing.';
$string['filtername'] = 'Generico Two filter';
$string['finished'] = 'Finished';
$string['fullwidth'] = 'Full width';
$string['genericotwo:managetemplates'] = 'Manage templates';
$string['handlelegacytags'] = 'Handle legacy tags';
$string['handlelegacytags_desc'] = 'If enabled, this filter will also process {GENERICO:type="xx"} tags using Generico Two templates.';
$string['jsonparsefail'] = 'Failed to parse JSON response from AI provider.';
$string['jsonparsefaildetail'] = '({$a->error}) The response began: {$a->snippet}';
$string['managetemplates'] = 'Manage templates';
$string['migrateconfirm'] = 'Are you sure you want to migrate the selected templates?';
$string['migratelegacy'] = 'Migrate legacy';
$string['migrateselected'] = 'Migrate selected templates';
$string['migration'] = 'Migration';
$string['migrationsuccess'] = 'Successfully migrated {$a} templates.';
$string['navigationtitle'] = 'Generico Two';
$string['nomigrationcandidates'] = 'No templates available for migration (all Generico templates already exist in Generico Two).';
$string['nopreviewstring'] = 'The test string is empty. It should be something like {G2:type=mytemplate,var=abc}';
$string['paused'] = 'Paused';
$string['play'] = 'Play';
$string['playbackspeed'] = 'Playback speed';
$string['playing'] = 'Playing';
$string['pluginname'] = 'Generico Two (G2) filter';
$string['presetavailable'] = 'Generico Two preset available';
$string['presets'] = 'Presets';
$string['presets_help'] = 'Choose a preset to populate this template with preconfigured values, then click Save changes to create the template. Presets are not available for use until they have been saved as templates.';
$string['preview'] = 'Preview';
$string['preview_desc'] = 'Preview the template output using the Test 1 or Test 2 strings.';
$string['privacy:metadata'] = 'The Generico Two (G2) filter does not store any personal data.';
$string['privacy:preference:templates_fullwidth'] = 'Whether the templates page is shown at full or limited width.';
$string['ready'] = 'Ready';
$string['remainingplays'] = 'Remaining plays';
$string['required'] = 'This field is required.';
$string['restart'] = 'Restart';
$string['selectall'] = 'Select all';
$string['skipback'] = 'Skip back';
$string['skipforward'] = 'Skip forward';
$string['skiptoend'] = 'Skip to end';
$string['speeddown'] = 'Speed down';
$string['speedup'] = 'Speed up';
$string['template'] = 'Template';
$string['template_allowedcontextids'] = 'Allowed context IDs';
$string['template_allowedcontextids_help'] = 'Comma-separated context IDs where this template may render. Leave blank for all contexts. E.g. 42,56,103';
$string['template_allowedcontexts'] = 'Allowed contexts';
$string['template_allowedcontexts_help'] = 'Comma-separated context names where this template may render. Leave blank for all contexts. E.g.course,system,block,mod_forum,coursecat,user';
$string['template_content'] = 'Content';
$string['template_content_help'] = 'The HTML that replaces the filter tag, rendered as a Mustache template. Insert variables with {{variablename}}, or use the Insert variable button. E.g. <div id="{{AUTOID}}">{{heading}}</div>';
$string['template_cssstyles'] = 'CSS styles';
$string['template_customcss'] = 'Custom CSS';
$string['template_customcss_help'] = 'CSS applied wherever this template renders. It is plain CSS, not a Mustache template, so variables do not work here. Scope your rules with the template\'s own class names so they cannot leak into the rest of the page.';
$string['template_dataset'] = 'Dataset body';
$string['template_dataset_help'] = 'Fetch data from the database using parameterised SQL queries using ? as placeholders. E.g. SELECT id, firstname, lastname FROM {user} WHERE id = ?';
$string['template_datasetsettings'] = 'Dataset';
$string['template_datasetvars'] = 'Dataset variables';
$string['template_datasetvars_help'] = 'Comma-separated values passed as the ? parameters: {{USER:id}}. Results available in template as {{#DATASET}}{{firstname}}{{/DATASET}}.';
$string['template_importcss'] = 'Import CSS URL';
$string['template_importcss_help'] = 'URL of a stylesheet to load wherever this template renders, for example a CDN hosted library. A URL starting with // takes this site\'s protocol, and one starting with / is treated as a path within this site. E.g. https://cdn.example.com/widget.css';
$string['template_instructions'] = 'Instructions';
$string['template_instructions_help'] = 'Shown to the user when they insert this widget from the editor. Explain what the widget does, what to put between the start and end tags if it has both, and what each of its variables is for. Bundled presets supply their own instructions.';
$string['template_jscontent'] = 'JS content';
$string['template_jscontent_help'] = 'Add JavaScript to control the template\'s behavior.';
$string['template_name'] = 'Name';
$string['template_name_help'] = 'A readable name for this template, shown in the templates list. It is not used in the filter tag, so it can be changed at any time.';
$string['template_previewcontext'] = 'Preview context';
$string['template_security'] = 'Security';
$string['template_templateend'] = 'Template end';
$string['template_templateend_help'] = 'Rendered using {G2:type=templatekey_end}. E.g. {G2:type=templatekey}your content here{G2:type=templatekey_end}';
$string['template_templatekey'] = 'Template key';
$string['template_templatekey_help'] = 'The name used in the filter tag to call this template, as {G2:type=templatekey}. It must be unique across all templates. Changing it breaks any existing tags that use the old key.';
$string['template_test1'] = 'Test string 1';
$string['template_test1_help'] = 'Enter a filter tag to preview this template. Include variables to test them: {G2:type=templatekey,heading=Welcome}. To test Template end, include both tags: {G2:type=templatekey}{G2:type=templatekey_end}';
$string['template_test2'] = 'Test string 2';
$string['template_test2_help'] = 'Enter a filter tag to preview this template. Include variables to test them: {G2:type=templatekey,heading=Welcome}. To test Template end, include both tags: {G2:type=templatekey}{G2:type=templatekey_end}';
$string['template_variabledefaults'] = 'Variable defaults';
$string['template_variabledefaults_help'] = 'Define default values for your variables, e.g., `width=500,height=300,heading=welcome`.';
$string['template_version'] = 'Version';
$string['template_version_help'] = 'The version of this template, e.g. 1.0.1. For a template that came from a bundled preset, this is compared against the preset\'s version to offer an update on the templates list, so lower it or clear it at your own risk.';
$string['templateadded'] = 'Template added successfully';
$string['templatedeleted'] = 'Template deleted successfully';
$string['templates'] = 'Templates';
$string['templatesinstructions'] = 'Add or edit a template here to make it available for use by the filter. A template is a mustache template with some fields, and javascript and CSS.';
$string['templatesupdated'] = 'Updated {$a} template(s) from presets';
$string['templateupdated'] = 'Template updated successfully';
$string['test1'] = 'Test 1';
$string['test2'] = 'Test 2';
$string['updateall'] = 'Update all';
$string['updateallconfirm'] = 'Are you sure you want to update all updateable templates from their newer presets? Any local changes to those templates will be overwritten. Allowed contexts and test strings will be kept.';
$string['updateconfirm'] = 'Are you sure you want to update this template from its newer preset (version {$a})? Any local changes to this template will be overwritten. Allowed contexts and test strings will be kept.';
$string['updatetoversion'] = 'Update to {$a}';
$string['variables_autoid'] = 'A unique DOM id for this tag. Use it to name elements in the body, then select them in the JS.';
$string['variables_coursecontextid'] = 'The context id of the current course';
$string['variables_coursecustomfield'] = 'Course custom field: {$a}';
$string['variables_coursefield'] = 'Current course: {$a}';
$string['variables_datasetcolumn'] = 'A column of the current dataset row, used inside a dataset section';
$string['variables_datasetempty'] = 'Shown only when the dataset query returned no rows';
$string['variables_datasetloop'] = 'Repeats its contents once for each row returned by the dataset query';
$string['variables_group_course'] = 'Course';
$string['variables_group_dataset'] = 'Dataset';
$string['variables_group_helpers'] = 'Moodle helpers';
$string['variables_group_system'] = 'System';
$string['variables_group_template'] = 'This template';
$string['variables_group_urlparam'] = 'URL parameters';
$string['variables_group_user'] = 'User';
$string['variables_helpercleanstr'] = 'A Moodle language string with HTML stripped';
$string['variables_helperpix'] = 'A Moodle icon';
$string['variables_helperstr'] = 'A Moodle language string';
$string['variables_insert'] = 'Insert variable';
$string['variables_jsnote'] = 'Variables are inserted unescaped ({{{name}}}) here, which is what JavaScript string literals need.';
$string['variables_moodlepageid'] = 'The id parameter of the current page URL, or 0 if there is none';
$string['variables_nomatches'] = 'No matching variables';
$string['variables_search'] = 'Search variables';
$string['variables_uniqid'] = 'The same unique id as AUTOID';
$string['variables_urlparam'] = 'URL parameter: {$a}';
$string['variables_urlparamother'] = 'Any other parameter of the current page URL. Type the parameter name.';
$string['variables_userfield'] = 'Current user: {$a}';
$string['variables_userpic'] = 'The current user\'s profile picture, as an HTML image';
$string['variables_userpicurl'] = 'The URL of the current user\'s profile picture';
$string['variables_userprofilefield'] = 'User profile field: {$a}';
$string['variables_wwwroot'] = 'The URL of this Moodle site';
$string['volumedown'] = 'Volume down';
$string['volumeup'] = 'Volume up';
