define([
    'jquery',
    'tiny_html/codemirror-lazy',
    'core/ajax',
    'core/modal',
    'core/templates',
    'core/str'
], function ($, CM, Ajax, Modal, Templates, Str) {
    return {
        init: function (config) {
            var EditorState = CM.EditorState;
            var EditorView = CM.EditorView;
            var basicSetup = CM.basicSetup;
            var lang = CM.lang;

            var textareas = [
                { id: 'id_content', mode: lang.html },
                { id: 'id_templateend', mode: lang.html },
                { id: 'id_jscontent', mode: lang.javascript },
                { id: 'id_dataset', mode: lang.html },
                { id: 'id_customcss', mode: lang.html }
            ];

            var views = {};

            textareas.forEach(function (item) {
                var textarea = $('#' + item.id);
                if (textarea.length) {
                    var container = $('<div>', {
                        'class': 'genericotwo-codemirror-container ' + (textarea.attr('class') || '')
                    }).insertBefore(textarea);

                    container.css({
                        'border': '1px solid #ccc',
                        'border-radius': '4px',
                        'margin-bottom': '10px',
                        'width': '100%',
                        'position': 'relative' // Added for button positioning
                    });

                    textarea.hide();

                    var theme = EditorView.theme({
                        "&": { width: "100%" },
                        ".cm-scroller": { overflow: "auto" }
                    });

                    var extensions = [
                        basicSetup,
                        theme,
                        EditorView.updateListener.of(function (update) {
                            if (update.docChanged) {
                                textarea.val(update.state.doc.toString());
                            }
                        })
                    ];

                    if (item.mode) {
                        extensions.push(item.mode());
                    }

                    var state = EditorState.create({
                        doc: textarea.val(),
                        extensions: extensions
                    });

                    views[item.id] = new EditorView({
                        state: state,
                        parent: container[0]
                    });

                    if (config && config.enableaihelper && item.id === 'id_content') {
                        addAIHelperButton(container[0], views);
                    }
                }
            });

            function addAIHelperButton(container, allViews) {
                var button = document.createElement("button");
                button.className = "btn btn-secondary btn-sm filter_genericotwo_aihelper_btn";
                button.innerHTML = '<i class="fa fa-magic"></i> AI Wizard';
                button.title = "AI Helper";
                button.style.position = "absolute";
                button.style.top = "-35px";
                button.style.right = "0";
                button.style.zIndex = "10";
                
                container.appendChild(button);
                
                button.addEventListener("click", function(e) {
                    e.preventDefault();
                    openAIHelperModal(allViews);
                });
            }

            function openAIHelperModal(allViews) {
                Str.get_string("aihelper_modal_title", "filter_genericotwo").then(function(title) {
                    return Modal.create({
                        title: title,
                        body: Templates.render("filter_genericotwo/aihelper_modal", {}),
                        footer: Templates.render("filter_genericotwo/aihelper_modal_footer", {}),
                        large: false
                    });
                }).then(function(modal) {
                    modal.show();
                    var root = modal.getRoot();
                    
                    root.on("click", '[data-action="generate"]', function() {
                        var promptInput = root.find("#filter_genericotwo_aihelper_prompt");
                        var generateBtn = root.find('[data-action="generate"]');
                        var responseContainer = root.find(".filter_genericotwo_aihelper_response_container");
                        var responsePre = root.find("#filter_genericotwo_aihelper_response");
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
                        
                        Ajax.call([{
                            methodname: "filter_genericotwo_fetch_aihelp",
                            args: {
                                prompt: prompt,
                                currentcode: JSON.stringify(editorContents)
                            }
                        }])[0].then(function(res) {
                            loading.addClass("d-none");
                            generateBtn.prop("disabled", false);
                            
                            if (res.status) {
                                // Assume response contains JSON encoded string of new contents
                                responsePre.text("AI Generation Successful!\nClick Apply to update all editors.");
                                // Store the actual response string on the pre tag using data attribute
                                responsePre.attr('data-response', res.response);
                                responseContainer.removeClass("d-none");
                                applyBtn.removeClass("d-none");
                                generateBtn.addClass("d-none");
                            } else {
                                responsePre.text("Error: " + (res.message || "Unknown error"));
                                responseContainer.removeClass("d-none");
                            }
                        }).catch(function(e) {
                            loading.addClass("d-none");
                            generateBtn.prop("disabled", false);
                            responsePre.text("Error: " + e.message);
                            responseContainer.removeClass("d-none");
                        });
                    });
                    
                    root.on("click", '[data-action="apply"]', function() {
                        var responsePre = root.find("#filter_genericotwo_aihelper_response");
                        var rawResponse = responsePre.attr('data-response');
                        if (rawResponse) {
                            try {
                                var newContents = JSON.parse(rawResponse);
                                for (var id in newContents) {
                                    if (newContents.hasOwnProperty(id) && allViews[id]) {
                                        var view = allViews[id];
                                        var newContent = newContents[id];
                                        var transaction = view.state.update({
                                            changes: { from: 0, to: view.state.doc.length, insert: newContent }
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
                }).catch(function(e) {
                    console.error(e);
                });
            }
        }
    };
});
