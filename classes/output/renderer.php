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
 * Renderer for filter_genericotwo.
 *
 * @package    filter_genericotwo
 * @copyright  2026 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_genericotwo\output;

use renderable;

/**
 * Renderer for filter_genericotwo.
 *
 * @package    filter_genericotwo
 * @copyright  2026 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \plugin_renderer_base implements renderable {
    /**
     * Render a template's mustache content and JS through the string loader.
     *
     * @param string $mustachestring the template's mustache content
     * @param string $jsstring the template's mustache JS content
     * @param array $templatedata the context to render both against
     * @param bool $usescriptfallback true if the JS can't rely on this request's own page
     *      lifecycle (or the Fragment API) to deliver it, so it must be embedded as a
     *      self-executing inline script instead of routed through js_amd_inline().
     * @return string the rendered HTML (with JS appended via the jsloader wrapper)
     */
    public function do_render($mustachestring, $jsstring, $templatedata, $usescriptfallback = false) {
        // Fetch the mustache engine, reset the loader to string loader,
        // render the custom finish screen, and restore the original loader.
        $mustache = $this->get_mustache();
        $oldloader = $mustache->getLoader();
        $mustache->setLoader(new \Mustache_Loader_StringLoader());

        // Render the HTML content headers.
        $tpl = $mustache->loadTemplate($mustachestring);
        $finishedcontents = $tpl->render($templatedata);

        // Render the JS content if we have any.
        $finishedjs = '';
        if (!empty($jsstring)) {
            $jstpl = $mustache->loadTemplate($jsstring);
            $finishedjs = $jstpl->render($this->prepare_js_data($templatedata));
        }

        $mustache->setLoader($oldloader);

        if (!empty($finishedjs)) {
            $jsloaderdata = ['jscontent' => $finishedjs];
            $templatename = $usescriptfallback
                ? 'filter_genericotwo/jsloader_fallback'
                : 'filter_genericotwo/jsloader';
            $loadertpl = $mustache->loadTemplate($templatename);
            $finishedcontents .= $loadertpl->render($jsloaderdata);
        }

        // Prepare CSS URLs for late loading if needed.
        $cssurls = [];
        if (!empty($templatedata['CSSLINK'])) {
            $cssurls[] = $templatedata['CSSLINK'];
        }
        if (!empty($templatedata['CSSCUSTOM'])) {
            $cssurls[] = $templatedata['CSSCUSTOM'];
        }

        // Always call init, passing CSS if any.
        $this->page->requires->js_call_amd('filter_genericotwo/loader', 'init', [$cssurls]);

        return $finishedcontents;
    }

    /**
     * Make the template data safe to interpolate into JavaScript string literals.
     *
     * Mustache's {{var}} escaping is HTML escaping only: it leaves single quotes and backslashes
     * alone, so a tag parameter or URL parameter could close a quoted string in the template's JS
     * (e.g. volume=');alert(1);//) or use a trailing backslash to swallow the closing quote. Here the
     * characters that can end or alter a JS string literal are backslash-escaped. Mustache's own
     * HTML escaping still runs afterwards, so the value reads as it always did within the string.
     *
     * @param mixed $data a context value: scalar, array or object
     * @return mixed the same structure with all strings made safe for JS string literals
     */
    protected function prepare_js_data($data) {
        if (is_string($data)) {
            return preg_replace_callback('/[\\\\\'\x60$\x00-\x1f\x{2028}\x{2029}]/u', function ($m) {
                $char = $m[0];
                switch ($char) {
                    case "\n":
                        return '\\n';
                    case "\r":
                        return '\\r';
                    case '\\':
                    case "'":
                    case "\x60":
                    case '$':
                        return '\\' . $char;
                    default:
                        return sprintf('\\u%04x', \core_text::utf8ord($char));
                }
            }, $data) ?? '';
        }
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        if (is_array($data)) {
            return array_map([$this, 'prepare_js_data'], $data);
        }
        return $data;
    }
}
