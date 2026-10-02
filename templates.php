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
 * Admin CRUD page for genericotwo templates.
 *
 * @package    filter_genericotwo
 * @copyright  2026 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');

use filter_genericotwo\form\template_form;
use filter_genericotwo\presets;
use filter_genericotwo\output\navigation;

$context = context_system::instance();
require_login();
require_capability('moodle/site:config', $context);
global $DB, $OUTPUT, $PAGE;

$PAGE->set_url(new \moodle_url('/filter/genericotwo/templates.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('templates', 'filter_genericotwo'));
$PAGE->set_heading(get_string('templates', 'filter_genericotwo'));


$action = optional_param('action', 'list', PARAM_ALPHA);
$id     = optional_param('id', 0, PARAM_INT);

$template = null;
if ($action === 'edit') {
    $template = $DB->get_record('filter_genericotwo_templates', ['id' => $id], '*', IGNORE_MISSING);
    if (!$template) {
        redirect(new moodle_url('/filter/genericotwo/templates.php'));
    }
    navigation::breadcrumbs(format_string($template->name, true, ['context' => $context]));
} else if ($action === 'add') {
    navigation::breadcrumbs(get_string('addtemplate', 'filter_genericotwo'));
} else {
    navigation::breadcrumbs();
}

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
        echo navigation::tabs('templates');
        echo $OUTPUT->confirm(get_string('deleteconfirm', 'filter_genericotwo'), $deleteurl, $cancelurl);
        echo $OUTPUT->footer();
        exit;
    }
}

if ($action === 'update' && $id) {
    require_sesskey();
    $confirm = optional_param('confirm', 0, PARAM_BOOL);
    $template = $DB->get_record('filter_genericotwo_templates', ['id' => $id], '*', MUST_EXIST);
    $listurl = new \moodle_url('/filter/genericotwo/templates.php');
    if ($confirm) {
        $updated = presets::update_template_from_preset($template);
        redirect($listurl, get_string('templatesupdated', 'filter_genericotwo', $updated ? 1 : 0));
    } else {
        $updateversion = presets::template_has_update($template);
        if (!$updateversion) {
            redirect($listurl);
        }
        $updateurl = new \moodle_url(
            '/filter/genericotwo/templates.php',
            ['action' => 'update', 'id' => $id, 'confirm' => 1, 'sesskey' => sesskey()]
        );
        echo $OUTPUT->header();
        echo navigation::tabs('templates');
        echo $OUTPUT->confirm(get_string('updateconfirm', 'filter_genericotwo', $updateversion), $updateurl, $listurl);
        echo $OUTPUT->footer();
        exit;
    }
}

if ($action === 'updateall') {
    require_sesskey();
    $confirm = optional_param('confirm', 0, PARAM_BOOL);
    $listurl = new \moodle_url('/filter/genericotwo/templates.php');
    if ($confirm) {
        $updatecount = presets::update_all_templates();
        redirect($listurl, get_string('templatesupdated', 'filter_genericotwo', $updatecount));
    } else {
        $updateallurl = new \moodle_url(
            '/filter/genericotwo/templates.php',
            ['action' => 'updateall', 'confirm' => 1, 'sesskey' => sesskey()]
        );
        echo $OUTPUT->header();
        echo navigation::tabs('templates');
        echo $OUTPUT->confirm(get_string('updateallconfirm', 'filter_genericotwo'), $updateallurl, $listurl);
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
    $record->importcss = $data->importcss;
    $record->variabledefaults = $data->variabledefaults;
    $record->allowedcontexts = $data->allowedcontexts;
    $record->allowedcontextids = $data->allowedcontextids;
    $record->dataset = $data->dataset;
    $record->datasetvars = $data->datasetvars;
    $record->templateend = $data->templateend;
    $record->instructions = $data->instructions['text'];
    $record->instructionsformat = $data->instructions['format'];
    $record->test1 = $data->test1;
    $record->test2 = $data->test2;
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

$fullwidth = (bool) get_user_preferences('filter_genericotwo_templates_fullwidth', 0);
if (!$fullwidth) {
    $PAGE->add_body_class('limitedwidth');
}
$PAGE->add_body_class('filter-genericotwo-pagewidth');
$isform = ($action === 'add' || $action === 'edit');
if ($isform) {
    $PAGE->requires->js_call_amd('filter_genericotwo/pagewidth_toggle', 'init');
}

echo $OUTPUT->header();
echo navigation::tabs('templates');

echo html_writer::tag('p', get_string('templatesinstructions', 'filter_genericotwo'));

if ($isform) {
    if ($action === 'edit' && empty($data)) {
        $template->instructions = [
            'text' => $template->instructions,
            'format' => $template->instructionsformat ?? FORMAT_MOODLE,
        ];
        $form->set_data($template);
    }
    echo $OUTPUT->render_from_template('filter_genericotwo/pagewidth_toggle', ['fullwidth' => $fullwidth]);
    $form->display();

    $enableaihelper = get_config('filter_genericotwo', 'enableaihelper') ? true : false;
    $PAGE->requires->js_call_amd('filter_genericotwo/codemirror_init', 'init', [['enableaihelper' => $enableaihelper]]);

    echo $OUTPUT->footer();
    exit;
}

$templates = $DB->get_records('filter_genericotwo_templates', null, 'name ASC');
$presetmap = presets::fetch_presets_by_key();
$haveupdates = false;
$templatedata = [];
foreach ($templates as $tmpl) {
    $updateversion = presets::template_has_update($tmpl, $presetmap);
    $updateurl = '';
    $updatelabel = '';
    if ($updateversion) {
        $haveupdates = true;
        $updateurl = (new moodle_url(
            '/filter/genericotwo/templates.php',
            ['action' => 'update', 'id' => $tmpl->id, 'sesskey' => sesskey()]
        ))->out(false);
        $updatelabel = get_string('updatetoversion', 'filter_genericotwo', $updateversion);
    }

    $templatedata[] = [
        'name' => format_string($tmpl->name),
        'templatekey' => $tmpl->templatekey,
        'version' => $tmpl->version,
        'hasupdate' => (bool) $updateversion,
        'updateurl' => $updateurl,
        'updatelabel' => $updatelabel,
        'editurl' => (new moodle_url('/filter/genericotwo/templates.php', ['action' => 'edit', 'id' => $tmpl->id]))->out(false),
        'deleteurl' => (new moodle_url(
            '/filter/genericotwo/templates.php',
            ['action' => 'delete', 'id' => $tmpl->id, 'sesskey' => sesskey()]
        ))->out(false),
    ];
}

$listdata = [
    'addtemplateurl' => (new moodle_url('/filter/genericotwo/templates.php', ['action' => 'add']))->out(false),
    'haveupdates' => $haveupdates,
    'updateallurl' => $haveupdates
        ? (new moodle_url('/filter/genericotwo/templates.php', ['action' => 'updateall', 'sesskey' => sesskey()]))->out(false)
        : '',
    'templates' => $templatedata,
];

echo $OUTPUT->render_from_template('filter_genericotwo/templates_list', $listdata);

echo $OUTPUT->footer();
