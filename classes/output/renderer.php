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
    public function do_render($mustachestring, $templatedata) {
        // here we fetch the mustache engine, reset the loader to string loader
        // render the custom finish screen, and restore the original loader
        $mustache = $this->get_mustache();
        $oldloader = $mustache->getLoader();
        $mustache->setLoader(new \Mustache_Loader_StringLoader());
        $tpl = $mustache->loadTemplate($mustachestring);
        $finishedcontents = $tpl->render($templatedata);
        $mustache->setLoader($oldloader);
        return $finishedcontents;
    }
}