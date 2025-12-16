<?php
namespace filter_genericotwo\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem null provider for filter_genericotwo.
 *
 * @package    filter_genericotwo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Get reason why plugin stores no data.
     *
     * @return string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}