define(['jquery', 'core/log'], function ($, log) {

    "use strict";

    log.debug('Filter GenericoTwo Presets initialising');

    return {

        presetdata: false,

        // IDs of fields in the form. 'id_' + fieldname
        dataitems: [
            'version',
            'templatekey',
            'name',
            'instructions',
            'content',
            'templateend',
            'variabledefaults',
            'jscontent',
            'previewcontext',
            'importcss',
            'customcss',
            'dataset',
            'datasetvars',
            'allowedcontexts',
            'allowedcontextids'
        ],

        // Mapping from preset keys (Generico style or common sense) to our form fields
        // Simplifies things if we use same keys in JSON export as form fields.

        fetchcontrols: function () {
            var controls = {};
            // Loop through dataitems and get element by id
            this.dataitems.forEach(function (item) {
                controls[item] = document.getElementById('id_' + item);
            });
            return controls;
        },

        fetchjsonbundle: function (controls) {
            var bundle = {};
            this.dataitems.forEach(function (item) {
                if (controls[item]) {
                    bundle[item] = controls[item].value;
                }
            });
            var jsonbundle = JSON.stringify(bundle);
            return jsonbundle;
        },

        exportbundle: function () {
            var controls = this.fetchcontrols();
            if (!controls.templatekey || controls.templatekey.value == '') {
                // If no key, maybe use name, or default
                var filename = 'preset';
                if (controls.name && controls.name.value) {
                    filename = controls.name.value;
                }
            } else {
                filename = controls.templatekey.value;
            }

            var jsonbundle = this.fetchjsonbundle(controls);

            var pom = document.createElement('a');
            pom.setAttribute('href', "data:text/json;charset=utf-8," + encodeURIComponent(jsonbundle));
            pom.setAttribute('download', filename + '.txt');

            if (document.createEvent) {
                var event = document.createEvent('MouseEvents');
                event.initEvent('click', true, true);
                pom.dispatchEvent(event);
            }
            else {
                pom.click();
            }
        },



        populateform: function (presetindex, presetdata) {
            var controls = this.fetchcontrols();

            // Check if we have data (index 0 is valid so check specifically for null/empty string)
            if ((presetindex === '' || presetindex === null) && !presetdata) {
                return;
            }

            var selectedPreset = null;

            if (typeof presetdata !== 'undefined' && presetdata) {
                // Passed directly (drag and drop)
                selectedPreset = presetdata[0]; // Generico passes array of 1
            } else {
                // From dropdown
                if (!this.presetdata) {
                    return;
                }
                selectedPreset = this.presetdata[presetindex];
            }

            if (!selectedPreset) {
                return;
            }

            var that = this;
            // Iterate and set values
            this.dataitems.forEach(function (item) {
                // Handling mapping from Generico preset names to our names if needed?
                // Generico keys: key, name, instructions, body, bodyend, requirecss, requirejs, etc.
                // Our keys: templatekey, name, instructions, content, templateend, importcss, jscontent, etc.

                // Let's check for standard Generico keys and map them if the specific item is missing in preset
                var value = null;

                // Direct match
                if (selectedPreset.hasOwnProperty(item)) {
                    value = selectedPreset[item];
                }
                // Mappings for compatibility with Generico Export files
                else {
                    switch (item) {
                        case 'templatekey':
                            if (selectedPreset.hasOwnProperty('key')) value = selectedPreset.key;
                            break;
                        case 'content':
                            if (selectedPreset.hasOwnProperty('body')) value = selectedPreset.body;
                            break;
                        case 'templateend':
                            if (selectedPreset.hasOwnProperty('bodyend')) value = selectedPreset.bodyend;
                            break;
                        case 'importcss':
                            if (selectedPreset.hasOwnProperty('requirecss')) value = selectedPreset.requirecss;
                            break;
                        case 'customcss':
                            if (selectedPreset.hasOwnProperty('style')) value = selectedPreset.style;
                            break;
                        case 'jscontent':
                            // Generico has 'script' (custom JS) and 'requirejs' (url)
                            // GenericoTwo has 'jscontent'. 
                            if (selectedPreset.hasOwnProperty('script')) value = selectedPreset.script;
                            break;
                        case 'variabledefaults':
                            if (selectedPreset.hasOwnProperty('defaults')) value = selectedPreset.defaults;
                            break;
                    }
                }

                if (controls[item] && value !== null) {

                    // Convert legacy @@variables@@ to {{mustache}} variables
                    if (typeof value === 'string') {
                        value = value.replace(/@@([^@]+)@@/g, '{{$1}}');
                    }

                    // Check Ace editor
                    if (window.ace) {
                        // Find editor
                        // The textarea might be hidden by Ace
                        // But Ace syncs on change.
                        // We need to set value on Ace if it exists for this field.
                        // But we don't have direct ref here easily unless we look for it.
                        // Simplest is to update textarea, then if Ace exists, update it?
                        // Or update textarea and trigger change?
                        $(controls[item]).val(value).trigger('change');

                        // Manually check if there is an ace editor associated
                        // Our ace_init creates div with class same as textarea
                        // Easier: check id
                        // But we don't know the editor instance.
                        // Let's trigger a custom event or check if ace_init handles external updates? 
                        // ace_init listens on editor change to update textarea.
                        // It does NOT listen on textarea change to update editor.
                        // So we might need to find the editor. 
                        var editorEnv = ace.edit($(controls[item]).prev()[0]); // previous sibling is the editDiv
                        if (editorEnv && editorEnv.setValue) {
                            editorEnv.setValue(value, 1);
                        }
                    } else {
                        $(controls[item]).val(value);
                    }
                }
            });

        },

        dopopulate: function (templatedata) {
            this.populateform(0, new Array(templatedata));
        },

        init: function (opts) {
            if (!this.presetdata) {
                var controlid = '#id_filter_genericotwo_presetdata';
                var presetcontrol = $(controlid).get(0);
                if (presetcontrol) {
                    this.presetdata = JSON.parse(presetcontrol.value);
                    // $(controlid).remove(); // Keep it for debug? Or remove like Generico?
                }
            }

            var thismodule = this;
            // Handle the select box change event
            $('#id_filter_genericotwo_presets').change(function () {
                thismodule.populateform($(this).val());
            });

            // Drag drop square events
            var ddsquareid = '#id_filter_genericotwo_dragdropsquare';

            // Export the current bundle
            $(ddsquareid).on("click", function () {
                thismodule.exportbundle();
            });


            // Handle the drop event.
            $(ddsquareid).on("dragover", function (event) {
                event.preventDefault();
                event.stopPropagation();
                $(this).addClass('filter_genericotwo_dragging');
            });

            $(ddsquareid).on("dragleave", function (event) {
                event.preventDefault();
                event.stopPropagation();
                $(this).removeClass('filter_genericotwo_dragging');
            });

            $(ddsquareid).on('drop', function (event) {
                event.preventDefault();
                var files = event.originalEvent.dataTransfer.files;

                if (files.length) {
                    var f = files[0];
                    if (f) {
                        var r = new FileReader();
                        r.onload = function (e) {
                            var contents = e.target.result;
                            try {
                                var templatedata = JSON.parse(contents);
                                // Check if it looks valid?
                                if (templatedata.key || templatedata.templatekey) {
                                    thismodule.dopopulate(templatedata);
                                }
                            } catch (e) {
                                alert("Invalid JSON file");
                            }
                        };
                        r.readAsText(f);
                    }
                }
                $(this).removeClass('filter_genericotwo_dragging');
            });
        }
    };
});
