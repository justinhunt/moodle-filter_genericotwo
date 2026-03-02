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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

use filter_genericotwo\constants;

$context = context_system::instance();
require_login();
require_capability('moodle/site:config', $context);

$PAGE->set_url(new \moodle_url('/filter/genericotwo/migrate.php'));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('migratelegacy', 'filter_genericotwo'));
$PAGE->set_heading(get_string('migratelegacy', 'filter_genericotwo'));

$action = optional_param('action', '', PARAM_ALPHA);
$ids = optional_param_array('ids', [], PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$g2config = get_config('filter_generico');

if ($action === 'migrate' && $confirm && !empty($ids)) {
    require_sesskey();

    $imported = 0;
    foreach ($ids as $tindex) {
        $props = [];
        // Map Generico to GenericoTwo
        // Keys in filter_generico config are like templatekey_1, template_1, etc.

        $key = 'templatekey_' . $tindex;
        if (!property_exists($g2config, $key)) { continue;
        }

        $templatekey = $g2config->{$key};

        // Double check it doesn't exist
        if ($DB->record_exists('filter_genericotwo_templates', ['templatekey' => $templatekey])) {
             continue;
        }

        $record = new \stdClass();
        $record->templatekey = $templatekey;
        $record->name = isset($g2config->{'templatename_' . $tindex}) ? $g2config->{'templatename_' . $tindex} : $templatekey;
        $record->version = isset($g2config->{'templateversion_' . $tindex}) ? $g2config->{'templateversion_' . $tindex} : '';
        $record->instructions = isset($g2config->{'templateinstructions_' . $tindex}) ? $g2config->{'templateinstructions_' . $tindex} : '';
        $record->content = isset($g2config->{'template_' . $tindex}) ? $g2config->{'template_' . $tindex} : '';
        $record->templateend = isset($g2config->{'templateend_' . $tindex}) ? $g2config->{'templateend_' . $tindex} : '';

        // Map CSS/JS
        $record->importcss = isset($g2config->{'templaterequire_css_' . $tindex}) ? $g2config->{'templaterequire_css_' . $tindex} : '';
        $record->customcss = isset($g2config->{'templatestyle_' . $tindex}) ? $g2config->{'templatestyle_' . $tindex} : '';
        $record->jscontent = isset($g2config->{'templatescript_' . $tindex}) ? $g2config->{'templatescript_' . $tindex} : '';

        // Map Defaults and Dataset
        $record->variabledefaults = isset($g2config->{'templatedefaults_' . $tindex}) ? $g2config->{'templatedefaults_' . $tindex} : '';
        $record->dataset = isset($g2config->{'dataset_' . $tindex}) ? $g2config->{'dataset_' . $tindex} : '';
        $record->datasetvars = isset($g2config->{'datasetvars_' . $tindex}) ? $g2config->{'datasetvars_' . $tindex} : '';

        // Map Security
        $record->allowedcontexts = isset($g2config->{'allowedcontexts_' . $tindex}) ? $g2config->{'allowedcontexts_' . $tindex} : '';
        $record->allowedcontextids = isset($g2config->{'allowedcontextids_' . $tindex}) ? $g2config->{'allowedcontextids_' . $tindex} : '';

        // Convert legacy @@variables@@ to {{mustache}} variables
        $fieldstoconvert = ['content', 'templateend', 'jscontent', 'customcss', 'importcss', 'variabledefaults', 'dataset', 'datasetvars', 'instructions'];
        foreach ($fieldstoconvert as $field) {
            if (!empty($record->$field)) {
                $record->$field = preg_replace('/@@([^@]+)@@/', '{{$1}}', $record->$field);
            }
        }

        // Do our best to implement requiresjs libs from the old config.
        $jswrapper = constants::M_JS_DEFAULT;
        $requirejs = isset($g2config->{'templaterequire_js_' . $tindex}) ? $g2config->{'templaterequire_js_' . $tindex} : '';
        if (!empty($requirejs)) {
            $jswrapper = str_replace("'core/log'", "'core/log','" . $requirejs . "'", $jswrapper);
            // get the library name from the requirejs string, eg "https://example.com/some/somelib.min.js" -> "somelib"
            $libname = basename($requirejs);
            $libname = str_replace('.min.js', '', $libname);
            $jswrapper = str_replace("($, log)", "($, log, $libname)", $jswrapper);
        }

        // Re write the way variables in old generico were concatenated with strings to the generico two way
        // e.g. 'abc' + {{AUTOID}} -> 'abc{{AUTOID}}'
        // e.g {{AUTOID}} + 'abc' -> '{{AUTOID}}abc'
        $jscontent = $record->jscontent;
        $oldjscontent = '';
        while ($oldjscontent !== $jscontent) {
            $oldjscontent = $jscontent;
            $jscontent = preg_replace('/([\'"])\s*\+\s*(\{\{[^}]+\}\})/', '$2$1', $jscontent);
            $jscontent = preg_replace('/(\{\{[^}]+\}\})\s*\+\s*([\'"])/', '$2$1', $jscontent);
        }

        // Sandwich the JS content with the default amd loader.
        $record->jscontent = str_replace("@@REPLACEME@@", $jscontent, $jswrapper);

        $record->timecreated = time();
        $record->timemodified = time();

        $DB->insert_record('filter_genericotwo_templates', $record);
        $imported++;
    }

    redirect(new \moodle_url('/filter/genericotwo/migrate.php'), get_string('migrationsuccess', 'filter_genericotwo', $imported));
}

echo $OUTPUT->header();

// Fetch candidates
$candidates = [];
if ($g2config && property_exists($g2config, 'templatecount')) {
    $existingkeys = $DB->get_fieldset_select('filter_genericotwo_templates', 'templatekey', '1=1');

    // Fetch available presets
    require_once(__DIR__ . '/classes/presets.php');
    $presets = \filter_genericotwo\presets::fetch_presets();
    $presetkeys = [];
    foreach ($presets as $p) {
        if (isset($p['templatekey'])) {
            $presetkeys[] = $p['templatekey'];
        } else if (isset($p['key'])) {
            $presetkeys[] = $p['key'];
        }
    }

    for ($i = 1; $i <= $g2config->templatecount; $i++) {
        $keyprop = 'templatekey_' . $i;
        if (property_exists($g2config, $keyprop)) {
            $key = $g2config->{$keyprop};
            // Only add if key is valid and NOT in existing list
            if (!empty($key) && !in_array($key, $existingkeys)) {
                 $candidates[] = [
                     'id' => $i,
                     'key' => $key,
                     'name' => isset($g2config->{'templatename_' . $i}) ? $g2config->{'templatename_' . $i} : $key,
                     'presetavailable' => in_array($key, $presetkeys),
                 ];
            }
        }
    }
}

$data = [
    'hascandidates' => !empty($candidates),
    'candidates' => $candidates,
    'actionurl' => (new \moodle_url('/filter/genericotwo/migrate.php'))->out(false),
    'sesskey' => sesskey(),
];

echo $OUTPUT->render_from_template('filter_genericotwo/migrate', $data);

echo $OUTPUT->footer();
