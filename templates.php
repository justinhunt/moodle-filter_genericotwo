<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/tablelib.php');

$context = context_system::instance();
require_login();
require_capability('moodle/site:config', $context);
global $DB, $OUTPUT, $PAGE;

$PAGE->set_url(new \moodle_url('/filter/genericotwo/templates.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('templates', 'filter_genericotwo'));
$PAGE->set_heading(get_string('templates', 'filter_genericotwo'));

require_once(__DIR__ . '/classes/form/template_form.php');

use filter_genericotwo\form\template_form;

$action = optional_param('action', 'list', PARAM_ALPHA);
$id     = optional_param('id', 0, PARAM_INT);

if ($action === 'delete' && $id) {
    require_sesskey();
    $confirm = optional_param('confirm', 0, PARAM_BOOL);
    $deleteurl = new \moodle_url('/filter/genericotwo/templates.php', ['action' => 'delete', 'id' => $id, 'confirm' => 1]);
    $cancelurl = new \moodle_url('/filter/genericotwo/templates.php');
    if ($confirm) {
        $DB->delete_records('filter_genericotwo_templates', ['id' => $id]);
        redirect(new \moodle_url('/filter/genericotwo/templates.php'), get_string('templatedeleted', 'filter_genericotwo'));
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('deleteconfirm', 'filter_genericotwo'), $deleteurl, $cancelurl);
        echo $OUTPUT->footer();
        exit;
    }
}

$form = new template_form(null, ['id' => $id]);
if ($data = $form->get_data()) {
    $record = new \stdClass();
    $record->version = $data->version;
    $record->templatekey = $data->templatekey;
    $record->name = $data->name;
    $record->content = $data->content;
    $record->jscontent = $data->jscontent;
    $record->customcss = $data->customcss;
    $record->variables = $data->variables;
    $record->previewcontext = $data->previewcontext;
    $record->timemodified = time();
    if (empty($data->id)) {
        $record->timecreated = time();
        $DB->insert_record('filter_genericotwo_templates', $record);
        redirect(new moodle_url('/filter/genericotwo/templates.php'), get_string('templateadded', 'filter_genericotwo'));
    } else {
        $record->id = $data->id;
        $DB->update_record('filter_genericotwo_templates', $record);
        redirect(new moodle_url('/filter/genericotwo/templates.php'), get_string('templateupdated', 'filter_genericotwo'));
    }
}

echo $OUTPUT->header();

echo html_writer::tag('p', get_string('templatesinstructions', 'filter_genericotwo'));
echo html_writer::link(new moodle_url('/filter/genericotwo/templates.php', ['action' => 'add']), get_string('addtemplate', 'filter_genericotwo'), ['class' => 'btn btn-primary']);
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('br');

if ($action === 'add' || $action === 'edit') {
    if ($action === 'edit' && empty($data)) {
        if ($tmpl = $DB->get_record('filter_genericotwo_templates', ['id' => $id], '*', IGNORE_MISSING)) {
            $form->set_data($tmpl);
        } else {
            redirect(new moodle_url('/filter/genericotwo/templates.php'));
        }
    }
    $form->display();
    echo $OUTPUT->footer();
    exit;
}

$templates = $DB->get_records('filter_genericotwo_templates', null, 'name ASC');
$table = new html_table();
$table->head = [get_string('template_name', 'filter_genericotwo'), get_string('actions')];
foreach ($templates as $tmpl) {
    $editurl = new moodle_url('/filter/genericotwo/templates.php', ['action' => 'edit', 'id' => $tmpl->id]);
    $deleteurl = new moodle_url('/filter/genericotwo/templates.php', ['action' => 'delete', 'id' => $tmpl->id, 'sesskey' => sesskey()]);
    $actions = html_writer::link($editurl, get_string('edit')) . ' | ' . html_writer::link($deleteurl, get_string('delete'));
    $table->data[] = [format_string($tmpl->name), $actions];
}
echo html_writer::table($table);

echo $OUTPUT->footer();