define(['jquery'], function ($) {
    return {
        init: function () {
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
                // Clear the queue to free memory, though subsequent calls go straight to run().
                window.filter_genericotwo.queue = [];
            }
        }
    };
});
