import { defineConfig } from 'astro/config';
import node from '@astrojs/node';
import vue from '@astrojs/vue';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig({
  output: "server",
  adapter: node({
    mode: "standalone"
  }),
  integrations: [vue()],
  vite: {
    plugins: [tailwindcss()],
    resolve: {
      alias: {
        '@': path.resolve('./src'),
      },
    },
  },
  session: {
    driver: 'fs',
    options: {
      cookie: {
        maxAge: 30 * 60,
        httpOnly: true,
        secure: false,
        sameSite: 'lax'
      }
    }
  },
});
