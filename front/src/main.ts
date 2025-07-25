import { createApp } from 'vue';
import App from './App.vue';

import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

// @ts-ignore
import 'vuetify/styles';
import { aliases, mdi } from 'vuetify/iconsets/mdi';
import { pt } from 'vuetify/locale';
import '@mdi/font/css/materialdesignicons.css';

import router from './router';

import './assets/styles/global.css';

const savedTheme =
  (localStorage.getItem('theme') as 'light' | 'dark') ?? 'dark';
const isDark = savedTheme ? savedTheme === 'dark' : true;

document.documentElement.setAttribute('data-theme', savedTheme);

const vuetify = createVuetify({
  components,
  directives,
  locale: {
    locale: 'pt',
    messages: { pt },
  },
  theme: {
    defaultTheme: isDark ? 'dark' : 'light',
    themes: {
      light: {
        dark: false,
        colors: {
          surface: '#FFFFFF',
          background: '#D3D1CB',
          primary: '#3B82F6',
          secondary: '#64748B',
        },
      },
      dark: {
        dark: true,
        colors: {
          background: '#181818',
        },
      },
    },
  },
  icons: {
    defaultSet: 'mdi',
    aliases,
    sets: { mdi },
  },
});

const app = createApp(App);

app.use(vuetify);
app.use(router);
app.mount('#app');
