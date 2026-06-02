define(['jquery', 'tiny_html/codemirror-lazy'], function ($, CM) {
    return {
        init: function () {
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
                        'width': '100%'
                    });

                    textarea.hide();

                    var theme = EditorView.theme({
                        "&": {
                            width: "100%"
                        },
                        ".cm-scroller": {
                            overflow: "auto"
                        }
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

                    new EditorView({
                        state: state,
                        parent: container[0]
                    });
                }
            });
        }
    };
});
