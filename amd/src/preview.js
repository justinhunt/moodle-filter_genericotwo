/**
 * Javascript module for handling the template preview in Generico Two.
 *
 * @module     filter_genericotwo/preview
 * @copyright  2024 OpenAI Codex
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/log', 'core/str', 'core/fragment', 'core/templates'],
    function ($, log, str, Fragment, Templates) {

        "use strict";

        return {

            strings: {},
            controls: {},

            init: function () {
                var self = this;
                self.init_strings();
                self.init_controls();
                self.register_events();
            },

            init_strings: function () {
                // Pre-load localized strings here if needed.
                str.get_strings([
                    'nopreviewstring'
                ]).done(function (s) {
                    self.strings.nopreviewstring = s[0];
                });
            },

            init_controls: function () {
                var self = this;
                self.controls = {
                    previewBtn: $('.filter_genericotwo_preview_btn'),
                    previewArea: $('#filter_genericotwo_preview_area'),
                };
            },

            register_events: function () {
                var self = this;
                if (!self.controls.previewBtn.length) {
                    return;
                }

                self.controls.previewBtn.on('click', function (e) {
                    e.preventDefault();
                    self.load_preview(this);
                });
            },

            load_preview: function (previewbtn) {
                var self = this;
                var btn = $(previewbtn);
                var form = btn.closest('form');

                if (!form.length) {
                    log.debug("Could not find the template form.");
                    return;
                }

                self.controls.previewArea.html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');

                // Find the closest select dropdown relative to the clicked button.
                var testStringSelect = btn.siblings('.filter_genericotwo_teststring_select');
                if (!testStringSelect.length) {
                    // Fallback: Grab the first select in the form
                    testStringSelect = form.find('.filter_genericotwo_teststring_select').first();
                }

                var testfieldname = 'test1';
                if (testStringSelect.length) {
                    testfieldname = testStringSelect.val();
                }

                var formElement = form[0];
                var formData = new URLSearchParams(new FormData(formElement)).toString();

                var contextId = M.cfg.contextid || 1;

                var params = {
                    formdata: formData,
                    testfield: testfieldname
                };

                var fragment = Fragment.loadFragment('filter_genericotwo', 'preview', contextId, params);

                fragment.done(function (html, js) {
                    //If the html does not start with a div, wrap it in one. Otherwwise it will break
                    if (!html.startsWith('<div')) {
                        html = '<div>' + html + '</div>';
                    }
                    Templates.replaceNodeContents(self.controls.previewArea.get(0), html, js);
                }).fail(function (ex) {
                    self.controls.previewArea.html('<div class="alert alert-danger">' + ex.message + '</div>');
                });
            }
        };
    });
