import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Zenith';

const persistedTheme = localStorage.getItem('za-theme') || 'auto';
const shouldUseDark =
    persistedTheme === 'dark' ||
    (persistedTheme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);

if (shouldUseDark) {
    document.documentElement.classList.add('theme-dark');
} else {
    document.documentElement.classList.remove('theme-dark');
}

document.documentElement.dataset.theme = persistedTheme;

document.documentElement.dataset.density = localStorage.getItem('za-density') || 'medium';
document.documentElement.dataset.accent = localStorage.getItem('za-accent') || 'sky';

createInertiaApp({
    title: (title) => `${title} | ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#434371',
    },
});
