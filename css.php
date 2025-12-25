<?php
/**
 * Returns the CSS for a specified template
 *
 * @package    filter_genericotwo
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

require_login();

$id = required_param('id', PARAM_INT);
$DB = $DB ?? $GLOBALS['DB'];

$template = $DB->get_record('filter_genericotwo_templates', ['id' => $id], '*', MUST_EXIST);

$thestyle = $template->customcss;

header('Content-Type: text/css');
// Cache control - cache for a long time as we rely on URL params to bust cache
header('Cache-Control: public, max-age=31536000'); 
echo $thestyle;
