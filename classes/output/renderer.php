<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/26
 * Time: 13:16
 */

namespace filter_genericotwo\output;

use renderable;


class renderer extends \plugin_renderer_base implements renderable {
    public function do_render($mustachestring, $jsstring, $templatedata) {
        // here we fetch the mustache engine, reset the loader to string loader
        // render the custom finish screen, and restore the original loader
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
            $finishedjs = $jstpl->render($templatedata);
        }

        $mustache->setLoader($oldloader);

        if (!empty($finishedjs)) {
            $jsloaderdata = ['jscontent' => $finishedjs];
            $loader_tpl = $mustache->loadTemplate('filter_genericotwo/jsloader');
            $finishedcontents .= $loader_tpl->render($jsloaderdata);
            $this->page->requires->js_call_amd('filter_genericotwo/loader', 'init');
        }

        return $finishedcontents;
    }
}