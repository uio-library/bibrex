// For IE11 support
import 'babel-polyfill';

// Error logging to Sentry
import Raven from 'raven-js';
Raven
    .config('https://51e9cb7c8a32430fbfd160e1e5028860@sentry.io/1229992')
    .install();

window.Raven = Raven;

// Import the datatables jquery plugin
window.$ = window.jQuery = require('jquery');
require('datatables.net-bs4');
require('datatables.net-select');

// At least for now, use the standard bootstrap js for the navbar dropdowns.
import Popper from 'popper.js';
import 'bootstrap';

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.post['X-CSRF-TOKEN'] = token.content;
} else {
    console.log('Laravel CSRF token not found');
}

if (!window.sessionStorage.getItem('bibrexWindowId')) {
    // One-liner UUIDv4. Not super-efficient, but good enough for us.
    // Source: https://stackoverflow.com/a/2117523/489916
    window.sessionStorage.setItem('bibrexWindowId', 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
        return v.toString(16);
    }));
}

window.axios.defaults.headers.post['X-Bibrex-Window'] = window.sessionStorage.getItem('bibrexWindowId');

// Set a default timeout of 30s.
window.axios.defaults.timeout = 30000;

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from "laravel-echo"

if (process.env.MIX_PUSHER_APP_KEY && process.env.MIX_PUSHER_APP_CLUSTER) {

    window.Pusher = require('pusher-js');

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: process.env.MIX_PUSHER_APP_KEY,
        cluster: process.env.MIX_PUSHER_APP_CLUSTER,
        encrypted: true,
        disableStats: true,
    });

} else {

    window.Echo = null;

}
