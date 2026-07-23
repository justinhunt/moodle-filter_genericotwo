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

defined('MOODLE_INTERNAL') || die();

$string['acecdn'] = 'Ace Editor CDN';
$string['acecdn_desc'] = 'Choose the CDN to load Ace Editor from. Staticfile is recommended for users in China.';
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
$string['bundle'] = 'Bundle';
$string['deleteconfirm'] = 'Are you sure you want to delete this template?';
$string['deletetemplate'] = 'Delete template';
$string['edittemplate'] = 'Edit template';
$string['enableace'] = 'Enable Ace Editor';
$string['enableace_desc'] = 'Enable syntax highlighting options using Ace Editor.';
$string['enableaihelper'] = 'Enable AI helper';
$string['enableaihelper_desc'] = 'Enable the AI wizard button for template editing.';
$string['filtername'] = 'Generico Two filter';
$string['finished'] = 'Finished';
$string['genericotwo:managetemplates'] = 'Manage templates';
$string['handlelegacytags'] = 'Handle legacy tags';
$string['handlelegacytags_desc'] = 'If enabled, this filter will also process {GENERICO:type="xx"} tags using Generico Two templates.';
$string['jsonparsefail'] = 'Failed to parse JSON response from AI provider.';
$string['managetemplates'] = 'Manage templates';
$string['migrateconfirm'] = 'Are you sure you want to migrate the selected templates?';
$string['migratelegacy'] = 'Migrate legacy';
$string['migrateselected'] = 'Migrate selected templates';
$string['migrationsuccess'] = 'Successfully migrated {$a} templates.';
$string['nomigrationcandidates'] = 'No templates available for migration (all Generico templates already exist in Generico Two).';
$string['nopreviewstring'] = 'The test string is empty. It should be something like {G2:type=mytemplate,var=abc}';
$string['paused'] = 'Paused';
$string['play'] = 'Play';
$string['playbackspeed'] = 'Playback speed';
$string['playing'] = 'Playing';
$string['pluginname'] = 'Generico Two filter';
$string['presetavailable'] = 'Generico Two preset available';
$string['presets'] = 'Presets';
$string['presets_help'] = 'Choose a preset to populate this template with preconfigured values, then click Save changes to create the template. Presets are not available for use until they have been saved as templates.';
$string['preview'] = 'Preview';
$string['preview_desc'] = 'Preview the template output using the Test 1 or Test 2 strings.';
$string['privacy:metadata'] = 'The Generico Two filter does not store any personal data.';
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
$string['template_cssstyles'] = 'CSS styles';
$string['template_customcss'] = 'Custom CSS';
$string['template_dataset'] = 'Dataset body';
$string['template_dataset_help'] = 'Fetch data from the database using parameterised SQL queries using ? as placeholders. E.g. SELECT id, firstname, lastname FROM {user} WHERE id = ?';
$string['template_datasetsettings'] = 'Dataset';
$string['template_datasetvars'] = 'Dataset variables';
$string['template_datasetvars_help'] = 'Comma-separated values passed as the ? parameters: {{USER:id}}. Results available in template as {{#DATASET}}{{firstname}}{{/DATASET}}.';
$string['template_importcss'] = 'Import CSS URL';
$string['template_instructions'] = 'Instructions';
$string['template_jscontent'] = 'JS content';
$string['template_jscontent_help'] = 'Add JavaScript to control the template\'s behavior.';
$string['template_name'] = 'Name';
$string['template_previewcontext'] = 'Preview context';
$string['template_security'] = 'Security';
$string['template_templateend'] = 'Template end';
$string['template_templateend_help'] = 'Rendered using {G2:type=templatekey_end}. E.g. {G2:type=templatekey}your content here{G2:type=templatekey_end}';
$string['template_templatekey'] = 'Template key';
$string['template_test1'] = 'Test string 1';
$string['template_test1_help'] = 'Enter a filter tag to preview this template. Include variables to test them: {G2:type=templatekey,heading=Welcome}. To test Template end, include both tags: {G2:type=templatekey}{G2:type=templatekey_end}';
$string['template_test2'] = 'Test string 2';
$string['template_test2_help'] = 'Enter a filter tag to preview this template. Include variables to test them: {G2:type=templatekey,heading=Welcome}. To test Template end, include both tags: {G2:type=templatekey}{G2:type=templatekey_end}';
$string['template_variabledefaults'] = 'Variable defaults';
$string['template_variabledefaults_help'] = 'Define default values for your variables, e.g., `width=500,height=300,heading=welcome`.';
$string['template_version'] = 'Version';
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
$string['volumedown'] = 'Volume down';
$string['volumeup'] = 'Volume up';

