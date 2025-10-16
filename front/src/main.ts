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

import './plugins/axios';

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
          surface: '#FFFFFF',     // superfícies principais (cards, containers, etc.)
          background: '#F5F6FA',  // fundo leve e neutro
          primary: '#2563EB',     // azul mais profundo e elegante (bom contraste)
          secondary: '#475569',   // tom neutro escuro (bom para texto secundário)
          accent: '#FACC15',      // amarelo suave opcional, dá vida sem cansar
          text: '#1E293B',        // cor principal do texto (quase preto)
        }
      },
      dark: {
        dark: true,
        colors: {
          surface: '#1E1E1E',     // superfícies principais (cards, containers, etc.)
          background: '#181818',  // fundo principal (quase preto)
          primary: '#3B82F6',     // mantém coerência com o modo claro, bom contraste no escuro
          secondary: '#94A3B8',   // cinza frio e claro pra texto secundário
          accent: '#FACC15',      // mesmo amarelo pra continuidade de marca
          text: '#F1F5F9',        // texto principal (quase branco, sem ser puro)
        }
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
