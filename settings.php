<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('filtersettings', new admin_category('filter_genericotwo', get_string('pluginname', 'filter_genericotwo')));

    $ADMIN->add('filter_genericotwo', new admin_externalpage(
        'filter_genericotwo_templates',
        get_string('templates', 'filter_genericotwo'),
        new moodle_url('/filter/genericotwo/templates.php'),
        'moodle/site:config'
    ));

    $settingspage = new admin_settingpage('filter_genericotwo_general', get_string('generalsettings', 'admin'));
    $ADMIN->add('filter_genericotwo', $settingspage);

    $settingspage->add(new admin_setting_configcheckbox(
        'filter_genericotwo/enableace',
        get_string('enableace', 'filter_genericotwo'),
        get_string('enableace_desc', 'filter_genericotwo'),
        1
    ));

    $settingspage->add(new admin_setting_configselect(
        'filter_genericotwo/acecdn',
        get_string('acecdn', 'filter_genericotwo'),
        get_string('acecdn_desc', 'filter_genericotwo'),
        'jsdelivr',
        [
            'jsdelivr' => 'JSDelivr (Global)',
            'unpkg' => 'Unpkg (Global/China)'
        ]
    ));
}