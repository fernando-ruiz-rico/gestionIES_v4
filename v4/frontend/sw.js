/* PWA — GestionIES v4
 *
 * Estrategia:
 *  - El *shell* (PRECACHE) se precachea al instalar.
 *  - El resto de estáticos propios (css/, js/, lib/, imágenes, fuentes)
 *    se cachea bajo demanda, cache-first: la primera visita online rellena
 *    la caché y las visitas siguientes funcionan offline.
 *  - CDN (jsdelivr / unpkg) se cachean igual (respuestas opacas, no-cors):
 *    Vue, Bootstrap, SweetAlert2, TinyMCE y los temas Bootswatch.
 *  - /backend/ NO se cachea: los datos son siempre en vivo; sin red, las
 *    peticiones al API fallan y la app lo muestra (la interfaz sigue).
 *
 * Actualizaciones: cada versión del SW (subir NIVEL) se activa cuando las
 * pestañas abiertas se cierran; sin recargas automáticas, para no cortar
 * una edición a medias en TinyMCE.
 *
 * Al añadir/eliminar archivos, sube NIVEL para forzar la re-caché.
 */
const NIVEL = 'v4-pwa-6';
const PRECACHE = [
    'index.html',
    'manifest.webmanifest',
    'css/app.css',
    'css/estilos_tiny.css',
    'js/app.js?v=2', // mismo querystring que index.html
    'js/avisos.js',
    'js/tinymce-helpers.js',
    'icons/icon-192.png',
    'icons/icon-512.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(NIVEL)
            .then((cache) => Promise.allSettled(PRECACHE.map((r) => cache.add(r))))
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            const claves = await caches.keys();
            await Promise.all(
                claves
                    .filter((k) => k !== NIVEL)
                    .map((k) => caches.delete(k))
            );
        })()
    );
});

function esEstatico(url) {
    const nombre = new URL(url).pathname.toLowerCase();
    return (
        nombre.endsWith('.css') ||
        nombre.endsWith('.js') ||
        nombre.endsWith('.html') ||
        nombre.endsWith('.svg') ||
        nombre.endsWith('.png') ||
        nombre.endsWith('.ico') ||
        nombre.endsWith('.woff') ||
        nombre.endsWith('.woff2') ||
        nombre.endsWith('.ttf')
    );
}

async function cachePrimero(cache, request) {
    const hit = await caches.match(request);
    if (hit) { return hit; }
    const respuesta = await fetch(request);
    if (respuesta.ok || respuesta.type === 'opaque') {
        const copia = respuesta.clone();
        cache.put(request, copia).catch(() => {});
    }
    return respuesta;
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') { return; }

    const url = new URL(request.url);

    // API PHP: nunca se cachea (datos en vivo).
    if (url.pathname.startsWith('/backend/')) { return; }

    // Navegación (SPA): red primero, si no, index.html cacheado.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match('index.html')));
        return;
    }

    const CDN = new Set([
        'cdn.jsdelivr.net',
        'unpkg.com',
        'fonts.googleapis.com',
        'fonts.gstatic.com'
    ]);

    event.respondWith(
        (async () => {
            const cache = await caches.open(NIVEL);
            // Estáticos propios o de CDN.
            if (esEstatico(url) || CDN.has(url.hostname)) {
                return cachePrimero(cache, request);
            }
            return fetch(request);
        })()
    );
});
