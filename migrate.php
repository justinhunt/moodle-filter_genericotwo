<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

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
        if (!property_exists($g2config, $key)) continue;

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
        $fields_to_convert = ['content', 'templateend', 'jscontent', 'customcss', 'importcss', 'variabledefaults', 'dataset', 'datasetvars', 'instructions'];
        foreach ($fields_to_convert as $field) {
            if (!empty($record->$field)) {
                $record->$field = preg_replace('/@@([^@]+)@@/', '{{$1}}', $record->$field);
            }
        }
        
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
        } elseif (isset($p['key'])) {
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
                     'presetavailable' => in_array($key, $presetkeys)
                 ];
            }
        }
    }
}

$data = [
    'hascandidates' => !empty($candidates),
    'candidates' => $candidates,
    'actionurl' => (new \moodle_url('/filter/genericotwo/migrate.php'))->out(false),
    'sesskey' => sesskey()
];

echo $OUTPUT->render_from_template('filter_genericotwo/migrate', $data);

echo $OUTPUT->footer();
