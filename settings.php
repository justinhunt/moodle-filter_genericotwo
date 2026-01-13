<?php
defined('MOODLE_INTERNAL') || die();

// This prevents a section of name: filtersettinggenericotwo being added
// We use this name for the general settings page so that "settings" links..
// For the G2 filter in manage_filters page go to the right place.
$settings = null;

if ($hassiteconfig) {
    // Add folder in property tree for settings pages.
    $G2_categoryname = 'filter_genericotwo';
    $ADMIN->add('filtersettings', new admin_category($G2_categoryname, get_string('pluginname', 'filter_genericotwo')));
    $settingspage = new admin_settingpage('filtersettinggenericotwo', get_string('generalsettings', 'admin'));
    $ADMIN->add($G2_categoryname, $settingspage);

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

    $settingspage->add(new admin_setting_configcheckbox(
        'filter_genericotwo/handlelegacytags',
        get_string('handlelegacytags', 'filter_genericotwo'),
        get_string('handlelegacytags_desc', 'filter_genericotwo'),
        0
    ));

    $ADMIN->add($G2_categoryname, new admin_externalpage(
        'filter_genericotwo_migrate',
        get_string('migratelegacy', 'filter_genericotwo'),
        new moodle_url('/filter/genericotwo/migrate.php'),
        'moodle/site:config'
    ));

    $ADMIN->add($G2_categoryname, new admin_externalpage(
        'filter_genericotwo_templates',
        get_string('templates', 'filter_genericotwo'),
        new moodle_url('/filter/genericotwo/templates.php'),
        'moodle/site:config'
    ));
}