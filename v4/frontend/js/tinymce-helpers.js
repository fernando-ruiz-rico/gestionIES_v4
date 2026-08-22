// Utilidades de ciclo de vida de TinyMCE 7 (editor WYSIWYG)
//
// Dos avisos de esta versión (TinyMCE 7.9.1, verificado en el navegador):
//   * tinymce.init() es asíncrono (devuelve una Promesa).
//   * tinymce.remove('id') NO hace nada en esta build: el teardown real es
//     editor.destroy() (síncrono en esta build; en otras devuelve Promesa).
// Si se vuelve a inicializar un id cuya instancia anterior sigue viva, el
// init nuevo compite con la instancia pendiente y el editor queda en un
// estado inconsistente (funciona la primera vez, pero la segunda vez
// "no sale bien").
//
// Este módulo serializa ese ciclo: quitar() destruye de verdad el editor
// (una sola por id, para no doble-destruir) e iniciar() garantiza que no
// quede ninguna instancia viva ni destrucción pendiente antes del init.
//
// Los editores se gestionan por el id de su textarea (el que usa cada
// vista). Así, aunque dos vistas reutilicen el mismo id (p. ej.
// contexto/recursos/metodologia/adaptaciones en "Unidades" y "Contenidos
// por defecto de temas"), la destrucción de la vista saliente se espera
// antes de inicializar la entrante.
const TinyMCEUtils = {
    // Promesas de destrucción en curso, por id (para no lanzar dos remove)
    _quedando: {},

    // TinyMCE cargado en la ventana
    disponible() {
        return !!window.tinymce;
    },

    // Promesa de destrucción pendiente del id (o null)
    _quedandoDe(id) {
        return this._quedando[id] || null;
    },

    // Destruye el editor registrado con el id y espera a que la
    // destrucción termine de verdad. Si no hay editor registrado (ni
    // destrucción pendiente), resuelve de inmediato.
    // Acepta un id o una lista de ids; devuelve una Promesa.
    quitar(ids) {
        const lista = Array.isArray(ids) ? ids : [ids];
        return Promise.all(lista.map((id) => {
            const pendiente = this._quedandoDe(id);
            if (pendiente) {
                return pendiente;
            }
            if (!window.tinymce || !tinymce.get(id)) {
                return Promise.resolve();
            }
            const promesa = Promise.resolve()
                .then(() => {
                    const actual = tinymce.get(id);
                    if (!actual) return;
                    // TinyMCE 7: el teardown real es editor.destroy()
                    // (en esta build tinymce.remove con id string no hace
                    // nada). Si devolviera una Promesa, igualmente la
                    // esperamos antes de seguir.
                    const r = actual.destroy();
                    return (r && r.then) ? r : undefined;
                })
                .catch(() => {
                    // Doble destrucción u otro error de teardown: se ignora
                })
                .then(() => {
                    delete TinyMCEUtils._quedando[id];
                });
            this._quedando[id] = promesa;
            return promesa;
        }));
    },

    // Inicializa el editor con la configuración clásica de tinymce.init
    // (selector, height, plugins, toolbar, ...), esperando antes a que no
    // quede ningún teardown pendiente de los ids objetivo.
    // ids: ids de los textareas del editor (para el teardown previo).
    // Devuelve la Promesa de init de TinyMCE (o null si no está cargado).
    async iniciar(config, ids) {
        if (!window.tinymce) {
            console.warn('TinyMCE no disponible — se muestran los textareas planos');
            return null;
        }
        await this.quitar(ids);
        // Salvaguarda: si a pesar de todo aún quedara un editor vivo
        // (estado anómalo), destrúyelo de nuevo antes del init.
        const lista = Array.isArray(ids) ? ids : [ids];
        for (const id of lista) {
            if (tinymce.get(id)) {
                await this.quitar([id]);
            }
        }
        let r;
        try {
            r = tinymce.init(config);
        } catch (error) {
            console.error('Error al inicializar TinyMCE:', error);
            return null;
        }
        if (r && r.then) {
            return r.catch(() => {
                console.error('El editor no pudo inicializarse');
            });
        }
        return r;
    },

    // Contenido del editor id (guardando antes en el textarea), o null
    leer(id) {
        const editor = this.get(id);
        if (!editor) return null;
        editor.save();
        return editor.getContent();
    },

    // Editor por id (o null)
    get(id) {
        return window.tinymce ? (tinymce.get(id) || null) : null;
    }
};
