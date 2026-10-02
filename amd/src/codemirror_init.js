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
 * Initialises CodeMirror editors on the template editing form, plus the AI-helper UI.
 *
 * @module     filter_genericotwo/codemirror_init
 * @copyright  2026 Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
	"jquery",
	"tiny_html/codemirror-lazy",
	"core/ajax",
	"core/modal",
	"core/templates",
	"core/str",
	"filter_genericotwo/variables",
], function ($, CM, Ajax, Modal, Templates, Str, Variables) {
	return {
		init: function (config) {
			var EditorState = CM.EditorState;
			var EditorView = CM.EditorView;
			var basicSetup = CM.basicSetup;
			var lang = CM.lang;

			// "vars" adds the insert-variable picker. The dataset editor holds SQL (whose
			// parameters are ? placeholders, not variables) and the CSS editor is not rendered
			// through Mustache at all, so neither of those gets one.
			// "triplestache" inserts {{{name}}}, because Mustache HTML-escapes {{name}} and that
			// would corrupt values used inside JavaScript string literals.
			var textareas = [
				{ id: "id_content", mode: lang.html, vars: true },
				{ id: "id_templateend", mode: lang.html, vars: true },
				{ id: "id_jscontent", mode: lang.javascript, vars: true, triplestache: true },
				{ id: "id_dataset", mode: lang.html },
				{ id: "id_customcss", mode: lang.html },
			];

			// The variable catalogue comes from a hidden field rather than an init argument,
			// because it is well over the 1024 character limit js_call_amd warns about.
			var vargroups = [];
			var vardata = document.getElementById("id_filter_genericotwo_variabledata");
			if (vardata && vardata.value) {
				try {
					vargroups = JSON.parse(vardata.value);
				} catch (e) {
					window.console.error("Could not parse variable catalogue", e);
				}
			}

			var views = {};

			/**
			 * Get (or create) the in-flow toolbar above an editor, so buttons can never overlap
			 * whatever field happens to precede this one.
			 *
			 * @param {Object} container jQuery wrapped editor container.
			 * @returns {Object} jQuery wrapped toolbar.
			 */
			function fetchToolbar(container) {
				var toolbar = container.prev(".genericotwo-codemirror-toolbar");
				if (!toolbar.length) {
					toolbar = $("<div>", {
						class: "genericotwo-codemirror-toolbar",
					}).insertBefore(container);
				}
				return toolbar;
			}

			textareas.forEach(function (item) {
				var textarea = $("#" + item.id);
				if (textarea.length) {
					var container = $("<div>", {
						class:
							"genericotwo-codemirror-container " +
							(textarea.attr("class") || ""),
					}).insertBefore(textarea);

					container.css({
						width: "100%",
						// Bootstrap 4 gives .form-control a fixed height; let the editor wrapper grow.
						height: "auto",
					});

					textarea.hide();

					var rows = parseInt(textarea.attr("rows"));
					var height = rows * 1.5 + "em"; // 1.5 is BS form-control line-height.

					var theme = EditorView.theme({
						"&": { width: "100%", minHeight: height },
						".cm-scroller": { overflow: "auto", minHeight: height },
						".cm-content": {
							minHeight: height,
							fontFamily:
								"ui-monospace, Menlo, Consolas, 'DejaVu Sans Mono', monospace",
						},
						".cm-gutters": { minHeight: height },
					});

					var extensions = [
						basicSetup,
						theme,
						EditorView.lineWrapping,
						EditorView.updateListener.of(function (update) {
							if (update.docChanged) {
								textarea.val(update.state.doc.toString());
							}
						}),
					];

					if (item.mode) {
						extensions.push(item.mode());
					}

					var state = EditorState.create({
						doc: textarea.val(),
						extensions: extensions,
					});

					views[item.id] = new EditorView({
						state: state,
						parent: container[0],
					});

					// Listen for programmatic changes to the textarea (e.g. from presets)
					textarea.on("change", function () {
						if (views[item.id]) {
							var currentViewValue = views[item.id].state.doc.toString();
							var newTextareaValue = textarea.val();
							if (currentViewValue !== newTextareaValue) {
								var transaction = views[item.id].state.update({
									changes: {
										from: 0,
										to: views[item.id].state.doc.length,
										insert: newTextareaValue,
									},
								});
								views[item.id].dispatch(transaction);
							}
						}
					});

					if (item.vars && vargroups.length) {
						Variables.attach(
							fetchToolbar(container)[0],
							vargroups,
							{ view: views[item.id] },
							!!item.triplestache,
						);
					}

					if (config && config.enableaihelper && item.id === "id_content") {
						addAIHelperButton(container, views);
					}
				}
			});

			// The dataset variables field is a plain input, but it takes the same {{variables}},
			// so it gets a picker too.
			var datasetvars = document.getElementById("id_datasetvars");
			if (datasetvars && vargroups.length) {
				var dvtoolbar = $("<div>", {
					class: "genericotwo-codemirror-toolbar",
				}).insertBefore(datasetvars);
				Variables.attach(dvtoolbar[0], vargroups, { field: datasetvars }, false);
			}

			function addAIHelperButton(container, allViews) {
				var toolbar = fetchToolbar(container);

				var button = document.createElement("button");
				button.className =
					"btn btn-secondary btn-sm filter_genericotwo_aihelper_btn";
				button.innerHTML = '<i class="fa fa-magic"></i> AI Wizard';
				button.title = "AI Helper";

				toolbar.append(button);

				button.addEventListener("click", function (e) {
					e.preventDefault();
					openAIHelperModal(allViews);
				});
			}

			function openAIHelperModal(allViews) {
				Str.get_string("aihelper_modal_title", "filter_genericotwo")
					.then(function (title) {
						return Modal.create({
							title: title,
							body: Templates.render("filter_genericotwo/aihelper_modal", {}),
							footer: Templates.render(
								"filter_genericotwo/aihelper_modal_footer",
								{},
							),
							large: false,
						});
					})
					.then(function (modal) {
						modal.show();
						var root = modal.getRoot();

						root.on("click", '[data-action="generate"]', function () {
							var promptInput = root.find(
								"#filter_genericotwo_aihelper_prompt",
							);
							var generateBtn = root.find('[data-action="generate"]');
							var responseContainer = root.find(
								".filter_genericotwo_aihelper_response_container",
							);
							var responsePre = root.find(
								"#filter_genericotwo_aihelper_response",
							);
							var loading = root.find(".filter_genericotwo_aihelper_loading");
							var applyBtn = root.find('[data-action="apply"]');
							var prompt = promptInput.val();

							if (!prompt) return;

							loading.removeClass("d-none");
							responseContainer.addClass("d-none");
							generateBtn.prop("disabled", true);

							// Gather content from all editors
							var editorContents = {};
							for (var id in allViews) {
								if (allViews.hasOwnProperty(id)) {
									editorContents[id] = allViews[id].state.doc.toString();
								}
							}

							Ajax.call([
								{
									methodname: "filter_genericotwo_fetch_aihelp",
									args: {
										prompt: prompt,
										currentcode: JSON.stringify(editorContents),
									},
								},
							])[0]
								.then(function (res) {
									loading.addClass("d-none");
									generateBtn.prop("disabled", false);

									if (res.status) {
										// Assume response contains JSON encoded string of new contents
										responsePre.text(res.message);
										// Store the actual response string on the pre tag using data attribute
										responsePre.attr("data-response", res.response);
										responseContainer.removeClass("d-none");
										applyBtn.removeClass("d-none");
										generateBtn.addClass("d-none");
									} else {
										responsePre.text(
											"Error: " + (res.message || "Unknown error"),
										);
										responseContainer.removeClass("d-none");
									}
								})
								.catch(function (e) {
									loading.addClass("d-none");
									generateBtn.prop("disabled", false);
									responsePre.text("Error: " + e.message);
									responseContainer.removeClass("d-none");
								});
						});

						root.on("click", '[data-action="apply"]', function () {
							var responsePre = root.find(
								"#filter_genericotwo_aihelper_response",
							);
							var rawResponse = responsePre.attr("data-response");
							if (rawResponse) {
								try {
									var newContents = JSON.parse(rawResponse);
									for (var id in newContents) {
										if (newContents.hasOwnProperty(id) && allViews[id]) {
											var view = allViews[id];
											var newContent = newContents[id];
											var transaction = view.state.update({
												changes: {
													from: 0,
													to: view.state.doc.length,
													insert: newContent,
												},
											});
											view.dispatch(transaction);
										}
									}
								} catch (e) {
									console.error("Could not parse AI response", e);
								}
							}
							modal.hide();
						});

						root.on("click", '[data-action="cancel"]', function (e) {
							e.preventDefault();
							modal.hide();
						});
					})
					.catch(function (e) {
						console.error(e);
					});
			}
		},
	};
});
