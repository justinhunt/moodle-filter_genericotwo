<?php
namespace filter_genericotwo\form;

defined('MOODLE_INTERNAL') || die();
require_once($GLOBALS['CFG']->libdir . '/formslib.php');

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

        $mform->addElement('text', 'version', get_string('template_version', constants::M_COMPONENT), ['size' => 64]);
        $mform->setType('version', PARAM_TEXT);
        $mform->addRule('version', null, 'required', null, 'client');

        $mform->addElement('text', 'templatekey', get_string('template_templatekey', constants::M_COMPONENT), ['size' => 64]);
        $mform->setType('templatekey', PARAM_TEXT);
        $mform->addRule('templatekey', null, 'required', null, 'client');

        $mform->addElement('text', 'name', get_string('template_name', constants::M_COMPONENT), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'customcss', get_string('template_customcss', constants::M_COMPONENT), ['size' => 64]);
        $mform->setType('customcss', PARAM_TEXT);


        $mform->addElement('textarea', 'variables', get_string('template_variables', constants::M_COMPONENT));
        $mform->setType('variables', PARAM_RAW);

        $mform->addElement('textarea', 'content', get_string('template_content', constants::M_COMPONENT));
        $mform->setType('content', PARAM_RAW);
        $mform->addRule('content', null, 'required', null, 'client');

        $mform->addElement('textarea', 'jscontent', get_string('template_jscontent', constants::M_COMPONENT));
        $mform->setType('jscontent', PARAM_RAW);

        $mform->addElement('textarea', 'previewcontext', get_string('template_previewcontext', constants::M_COMPONENT));
        $mform->setType('previewcontext', PARAM_RAW);

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