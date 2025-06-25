import _ from 'lodash';
window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true; // 允許跨域攜帶 Cookie (JWT 通常不需要，但 CSRF 需要)

// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';

// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     wsHost: import.meta.env.VITE_PUSHER_APP_HOST ?? '127.0.0.1',
//     wsPort: import.meta.env.VITE_PUSHER_APP_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_APP_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_APP_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });
