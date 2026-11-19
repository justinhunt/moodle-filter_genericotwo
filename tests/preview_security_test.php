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

namespace filter_genericotwo;

/**
 * Access and dataset checks for the template preview.
 *
 * @package    filter_genericotwo
 * @copyright  2026 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     ::filter_genericotwo_output_fragment_preview
 * @covers     \filter_genericotwo\text_filter
 */
final class preview_security_test extends \advanced_testcase {
    /**
     * Load the plugin library.
     */
    public static function setUpBeforeClass(): void {
        global $CFG;
        parent::setUpBeforeClass();
        require_once($CFG->dirroot . '/filter/genericotwo/lib.php');
    }

    /**
     * Fragment arguments that run a dataset query against the user table.
     *
     * @return array
     */
    private function leak_args(): array {
        $formdata = http_build_query([
            'dataset' => "SELECT id, username FROM {user} WHERE username = 'admin'",
            'content' => '{{#DATASET}}LEAK:{{username}}{{/DATASET}}',
            'test1' => '{G2:type=leak}',
        ], '', '&');
        return [
            'context' => \context_system::instance(),
            'formdata' => $formdata,
            'testfield' => 'test1',
        ];
    }

    /**
     * A user without any role must not be able to run the preview.
     */
    public function test_preview_denied_for_user_without_role(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $this->expectException(\required_capability_exception::class);
        filter_genericotwo_output_fragment_preview($this->leak_args());
    }

    /**
     * A manager holds managetemplates by default but must still not run arbitrary SQL.
     */
    public function test_preview_denied_for_manager(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->role_assign('manager', $user->id, \context_system::instance()->id);
        $this->setUser($user);

        $this->expectException(\required_capability_exception::class);
        filter_genericotwo_output_fragment_preview($this->leak_args());
    }

    /**
     * Calling preview_filter() directly is guarded too.
     */
    public function test_preview_filter_denied_for_user_without_role(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $template = (object) [
            'content' => '{{#DATASET}}{{username}}{{/DATASET}}',
            'dataset' => 'SELECT id, username FROM {user}',
        ];
        $filter = new text_filter(\context_system::instance(), []);

        $this->expectException(\required_capability_exception::class);
        $filter->preview_filter($template, '{G2:type=leak}');
    }

    /**
     * The admin preview still renders, dataset included.
     */
    public function test_preview_works_for_admin(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $html = filter_genericotwo_output_fragment_preview($this->leak_args());
        $this->assertStringContainsString('LEAK:admin', $html);
    }

    /**
     * Dataset queries must be a single SELECT.
     *
     * @dataProvider select_query_provider
     * @param string $sql The dataset SQL.
     * @param bool $expected Whether it is accepted.
     */
    public function test_is_select_query(string $sql, bool $expected): void {
        $this->assertSame($expected, text_filter::is_select_query($sql));
    }

    /**
     * Data for test_is_select_query.
     *
     * @return array
     */
    public static function select_query_provider(): array {
        return [
            'plain select' => ['SELECT * FROM {user} WHERE id = ?', true],
            'lower case, leading space' => ["  select id from {course}", true],
            'trailing semicolon' => ['SELECT * FROM {user} where NOT id = ?;', true],
            'bracketed select' => ['(SELECT id FROM {user})', true],
            'empty' => ['', false],
            'update' => ["UPDATE {user} SET auth = 'nologin'", false],
            'insert' => ['INSERT INTO {role_assignments} (roleid) VALUES (1)', false],
            'delete' => ['DELETE FROM {user}', false],
            'chained statement' => ['SELECT 1; DELETE FROM {user}', false],
            'select into outfile' => ["SELECT password FROM {user} INTO OUTFILE '/tmp/x'", false],
            'select into table' => ['SELECT * INTO newtable FROM {user}', false],
            'cte' => ['WITH x AS (SELECT 1) DELETE FROM {user}', false],
        ];
    }
}
