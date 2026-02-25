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

namespace filter_genericotwo\form;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

use moodleform;
use filter_genericotwo\constants;

/**
 * Form for adding and editing templates.
 */
class template_form extends moodleform {
    /**
     * Define form elements.
     */
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // Presets Control
        $presets = \filter_genericotwo\presets::fetch_presets();
        $presetopts = ['' => get_string('choosedots')];
        $presetdata = [];
        // Re-key for simple array access in JS if needed, or just keep as list
        // We will store all data in a hidden field
        // Just keys for dropdown
        $pcount = 0;
        foreach ($presets as $preset) {
            $pname = isset($preset['name']) ? $preset['name'] : (isset($preset['key']) ? $preset['key'] : 'preset'.$pcount);
            $presetopts[$pcount] = $pname;
            $presetdata[$pcount] = $preset;
            $pcount++;
        }

        $jsonpresets = json_encode($presetdata);

        $html = \html_writer::tag('div', '', ['id' => 'filter_genericotwo_presets_container', 'class' => 'form-group row fitem']);
        // Hidden data field
        $mform->addElement('html', \html_writer::tag('input', '',
            ['id' => 'id_filter_genericotwo_presetdata', 'type' => 'hidden', 'value' => $jsonpresets]));

        // Drag Drop Square
        // We put it in a custom HTML element
        $dragdropsquare = \html_writer::tag('div', get_string('bundle', 'filter_genericotwo'),
                ['id' => 'id_filter_genericotwo_dragdropsquare',
                        'class' => 'filter_genericotwo_dragdropsquare',
                        'style' => 'background: #cfc; border: 1px dashed #090; padding: 10px; text-align: center; cursor: pointer; float: right; width: 100px; display:inline-block; margin-left:10px;']);

        // Select Box
        // 4th arg is 'nothing' option (null to use default or generated from opts), 5th is attributes.
        // We manually added 'choosedots' to options, so we can pass null for 'nothing'.
        $select = \html_writer::select($presetopts, 'filter_genericotwo_presets', '', null, ['id' => 'id_filter_genericotwo_presets', 'class' => 'custom-select']);

        $label = \html_writer::tag('label', get_string('presets', 'filter_genericotwo') . ' ', ['for' => 'id_filter_genericotwo_presets']);

        $controlhtml = \html_writer::tag('div', $dragdropsquare . $label . $select, ['class' => 'col-md-9 checkbox']);
        $labelhtml = \html_writer::tag('div', '', ['class' => 'col-md-3 col-form-label pb-0 pt-0']); // Empty label col

        $mform->addElement('html', \html_writer::tag('div', $labelhtml . $controlhtml, ['class' => 'form-group row fitem']));

        // Init JS
        global $PAGE;
        $PAGE->requires->js_call_amd('filter_genericotwo/presets', 'init');

        $mform->addElement('text', 'version', get_string('template_version', constants::M_COMPONENT), ['size' => 64]);
        $mform->setType('version', PARAM_TEXT);
        $mform->addRule('version', null, 'required', null, 'client');

        $mform->addElement('text', 'templatekey', get_string('template_templatekey', constants::M_COMPONENT), ['size' => 64]);
        $mform->setType('templatekey', PARAM_TEXT);
        $mform->addRule('templatekey', null, 'required', null, 'client');

        $mform->addElement('text', 'name', get_string('template_name', constants::M_COMPONENT), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('editor', 'instructions', get_string('template_instructions', constants::M_COMPONENT));
        $mform->setType('instructions', PARAM_RAW);

        $mform->addElement('textarea', 'content', get_string('template_content', constants::M_COMPONENT), ['rows' => 10]);
        $mform->setType('content', PARAM_RAW);
        $mform->addRule('content', null, 'required', null, 'client');

        $mform->addElement('textarea', 'templateend', get_string('template_templateend', constants::M_COMPONENT), ['rows' => 4]);
        $mform->setType('templateend', PARAM_RAW);

        $mform->addElement('textarea', 'variabledefaults', get_string('template_variabledefaults', constants::M_COMPONENT), ['rows' => 4]);
        $mform->setType('variabledefaults', PARAM_RAW);

        $mform->addElement('textarea', 'jscontent', get_string('template_jscontent', constants::M_COMPONENT), ['rows' => 10]);
        $mform->setType('jscontent', PARAM_RAW);

        // Preview Settings
        $mform->addElement('header', 'hdr_preview', get_string('preview', constants::M_COMPONENT));

        $mform->addElement('textarea', 'test1', get_string('template_test1', constants::M_COMPONENT),
            ['rows' => 4, 'id' => 'id_filter_genericotwo_test1', 'placeholder' => '{G2:type=mytemplatename,var1=val1,var2=val2}', 'class' => 'filter_genericotwo_teststring']);
        $mform->setType('test1', PARAM_RAW);

        $mform->addElement('textarea', 'test2', get_string('template_test2', constants::M_COMPONENT),
            ['rows' => 4, 'id' => 'id_filter_genericotwo_test2', 'placeholder' => '{G2:type=mytemplatename,var1=val1,var2=val2}', 'class' => 'filter_genericotwo_teststring']);
        $mform->setType('test2', PARAM_RAW);

        // Preview rendering area
        $teststringselect = \html_writer::select(
            [
                'test1' => get_string('template_test1', constants::M_COMPONENT),
                'test2' => get_string('template_test2', constants::M_COMPONENT),
            ],
            'filter_genericotwo_teststring_select',
            'test1',
            false,
            ['class' => 'custom-select mr-2 mb-3 form-select d-inline-block w-auto filter_genericotwo_teststring_select']
        );

        $previewbtn = \html_writer::tag('button', get_string('preview', constants::M_COMPONENT), [
            'type' => 'button',
            'class' => 'btn btn-secondary mb-3 filter_genericotwo_preview_btn',
        ]);

        $previewarea = \html_writer::tag('div', '', [
            'id' => 'filter_genericotwo_preview_area',
            'class' => 'border p-3 bg-white min-h-200',
            'style' => 'min-height: 200px;',
        ]);

        $previewhtml = \html_writer::tag('div', $teststringselect . $previewbtn . $previewarea, ['class' => 'col-md-9']);
        $previewlabel = \html_writer::tag('div', get_string('preview_desc', constants::M_COMPONENT), ['class' => 'col-md-3 col-form-label pb-0 pt-0']);

        $mform->addElement('html', \html_writer::tag('div', $previewlabel . $previewhtml, ['class' => 'form-group row fitem']));

        $PAGE->requires->js_call_amd('filter_genericotwo/preview', 'init');

        // CSS Styles.
        $mform->addElement('header', 'hdr_css', get_string('template_cssstyles', constants::M_COMPONENT));
        // $mform->setExpanded('hdr_css');
        $mform->addElement('text', 'importcss', get_string('template_importcss', constants::M_COMPONENT), ['size' => 64]);
        $mform->setType('importcss', PARAM_TEXT);

        $mform->addElement('textarea', 'customcss', get_string('template_customcss', constants::M_COMPONENT), ['rows' => 10]);
        $mform->setType('customcss', PARAM_TEXT);

        // Dataset.
        $mform->addElement('header', 'hdr_dataset', get_string('template_datasetsettings', constants::M_COMPONENT));
        // $mform->setExpanded('hdr_dataset');
        $mform->addElement('textarea', 'dataset', get_string('template_dataset', constants::M_COMPONENT), ['rows' => 10]);
        $mform->setType('dataset', PARAM_TEXT);

        $mform->addElement('text', 'datasetvars', get_string('template_datasetvars', constants::M_COMPONENT), ['size' => 64]);
        $mform->setType('datasetvars', PARAM_RAW);

        // Security.
        $mform->addElement('header', 'hdr_security', get_string('template_security', constants::M_COMPONENT));
        // $mform->setExpanded('hdr_security');
        $mform->addElement('text', 'allowedcontexts', get_string('template_allowedcontexts', constants::M_COMPONENT), ['size' => 64]);
        $mform->setType('allowedcontexts', PARAM_TEXT);

        $mform->addElement('text', 'allowedcontextids', get_string('template_allowedcontextids', constants::M_COMPONENT), ['size' => 64]);
        $mform->setType('allowedcontextids', PARAM_TEXT);

        $this->add_action_buttons();
    }

    /**
     * Form validation.
     *
     * @param array $data Form data.
     * @param array $files File data.
     * @return array Errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['name'])) {
            $errors['name'] = get_string('required');
        }
        return $errors;
    }
}
