define(['jquery'], function ($) {
    return {
        init: function (cssurls) {

            // Inject CSS if provided
            if (cssurls && cssurls.length > 0) {
                cssurls.forEach(function (url) {
                    this.injectcss(url);
                }.bind(this));
            }

            // Run any queued JS scripts
            if (typeof window.filter_genericotwo !== 'undefined') {
                window.filter_genericotwo.ready = true;
                // Run all queued functions.
                for (var i = 0; i < window.filter_genericotwo.queue.length; i++) {
                    try {
                        window.filter_genericotwo.queue[i]();
                    } catch (e) {
                        // eslint-disable-next-line no-console
                        console.error('GenericoTwo script error:', e);
                    }
                }
                // Clear the queue, subsequent calls go straight to run().
                window.filter_genericotwo.queue = [];
            }
        },

        injectcss: function (csslink) {
            // Check if already exists to avoid duplicates
            if (document.querySelector('link[href="' + csslink + '"]')) {
                return;
            }
            var link = document.createElement("link");
            link.href = csslink;
            link.type = "text/css";
            link.rel = "stylesheet";
            document.getElementsByTagName("head")[0].appendChild(link);
        }
    };
});
