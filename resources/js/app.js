import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
// import { VueWindowSizePlugin } from 'vue-window-size/plugin';
import Toast, { POSITION } from 'vue-toastification';
import PubNub from 'pubnub';

import 'bootstrap/dist/js/bootstrap.js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    return (
      createApp({ render: () => h(App, props) })
        .use(plugin)
        .use(ZiggyVue)
        // .use(VueWindowSizePlugin)
        .use(Toast, {
          position: POSITION.BOTTOM_RIGHT,
          pauseOnFocusLoss: false,
        })
        .mount(el)
    );
  },
  progress: {
    color: '#4B5563',
  },
});

const deviceId = btoa(navigator.userAgent);
let pubnub;
const setupPubNub = () => {
  pubnub = new PubNub({
    publishKey: import.meta.env.VITE_PUBNUB_PUBLISH_KEY,
    subscribeKey: import.meta.env.VITE_PUBNUB_SUBSCRIBE_KEY,
    uuid: deviceId,
  });

  const listener = {
    status: (statusEvent) => {
      console.log('statusEvent', statusEvent);
      if (statusEvent.category === 'PNConnectedCategory') {
        console.log('Connected');
      }
    },
    message: (messageEvent) => {
      console.log('messageEvent', messageEvent);
      if (messageEvent.message?.SendingCurrentWeight === 'yes') {
        document.dispatchEvent(new CustomEvent('GetWeight', { detail: messageEvent.message }));
      }
    },
  };
  pubnub.addListener(listener);

  pubnub.subscribe({
    channels: [deviceId, 'weighbridge', 'BU2', 'BU3'],
  });
};
setupPubNub();
window.pubnub = {
  deviceId: deviceId,
  instance: pubnub,
};
