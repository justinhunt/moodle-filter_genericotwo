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
}