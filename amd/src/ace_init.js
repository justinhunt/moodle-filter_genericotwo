define(['jquery'], function ($) {
    return {
        init: function (params) {
            if (!params.enableace || params.enableace == '0') {
                return;
            }

            var cdnUrl = 'https://cdn.jsdelivr.net/npm/ace-builds@1.5.0/src-min-noconflict/';
            if (params.acecdn) {
                switch (params.acecdn) {
                    case 'unpkg':
                        cdnUrl = 'https://unpkg.com/ace-builds@1.5.0/src-min-noconflict/';
                        break;
                    case 'jsdelivr':
                    default:
                        cdnUrl = 'https://cdn.jsdelivr.net/npm/ace-builds@1.5.0/src-min-noconflict/';
                        break;
                }
            }

            // Ace checks for 'requirejs' and aborts if found, but doesn't register properly as anonymous module.
            // We temporarily hide requirejs to force Ace to register globally.
            var oldRequire = window.require;
            var oldDefine = window.define;
            var oldRequirejs = window.requirejs;

            window.require = undefined;
            window.define = undefined;
            window.requirejs = undefined;

            $.getScript(cdnUrl + 'ace.js', function () {
                // Restore RequireJS
                window.require = oldRequire;
                window.define = oldDefine;
                window.requirejs = oldRequirejs;

                var aceEditor = window.ace;
                if (!aceEditor) {
                    console.error('GenericoTwo: Ace Editor failed to load (global ace not found).');
                    return;
                }

                // Determine modes for each textarea
                var textareas = [
                    { id: 'id_content', mode: 'html' },
                    { id: 'id_templateend', mode: 'html' },
                    { id: 'id_jscontent', mode: 'javascript' },
                    { id: 'id_dataset', mode: 'sql' },
                    { id: 'id_customcss', mode: 'css' }
                ];

                aceEditor.config.set('basePath', cdnUrl);


                textareas.forEach(function (item) {
                    var textarea = $('#' + item.id);
                    if (textarea.length) {
                        // Create container
                        var editDiv = $('<div>', {
                            position: 'absolute',
                            width: '100%',
                            height: textarea.height(),
                            'class': textarea.attr('class')
                        }).insertBefore(textarea);

                        // Style specifically to match bootstrap controls somewhat
                        editDiv.css({
                            'min-height': '300px', // Ace needs height
                            'border': '1px solid #ccc',
                            'border-radius': '4px'
                        });

                        textarea.hide();

                        var editor = aceEditor.edit(editDiv[0]);
                        editor.getSession().setMode('ace/mode/' + item.mode);
                        editor.setTheme('ace/theme/chrome');
                        editor.setValue(textarea.val(), 1); // 1 = moves cursor to end

                        // Update textarea on change
                        editor.getSession().on('change', function () {
                            textarea.val(editor.getSession().getValue());
                        });
                    }
                });
            });
        }
    };
});
