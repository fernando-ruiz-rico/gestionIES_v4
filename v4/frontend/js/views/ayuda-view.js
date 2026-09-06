// Vista de Ayuda (port de v3/ayuda.php)
//
// Los manuales son archivos Markdown servidos como estáticos desde docs/.
// El parámetro «doc» lo fija app-layout según el enlace del menú:
//   «ayuda» -> Manual_Profe.md ; «ayuda_admin» -> Manual_Admin.md
const AyudaView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-9">
                    <div class="card shadow-sm">
                        <div class="card-body ayuda-md">
                            <div v-if="cargando" class="text-muted">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Cargando la ayuda...
                            </div>
                            <div v-else-if="error" class="alert alert-warning mb-0">{{ error }}</div>
                            <div v-else v-html="html"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    props: {
        usuario: { type: Object, default: null },
        params: { type: Object, default: () => ({}) }
    },

    data() {
        return { html: '', cargando: true, error: '' };
    },

    computed: {
        doc() {
            // Solo los dos manuales conocidos (la ruta nunca viene de usuario libre)
            return this.params.doc === 'Manual_Admin.md' ? 'Manual_Admin.md' : 'Manual_Profe.md';
        }
    },

    watch: {
        doc() { this.cargar(); }
    },

    mounted() {
        this.cargar();
    },

    methods: {
        async cargar() {
            this.cargando = true;
            this.error = '';
            try {
                const respuesta = await fetch('docs/' + this.doc);
                if (!respuesta.ok) throw new Error(String(respuesta.status));
                const md = await respuesta.text();
                this.html = window.marked ? marked.parse(md) : '<p class="text-warning">Markdown no disponible.</p>';
            } catch (e) {
                this.error = 'No se pudo cargar el manual. Recarga la página e inténtalo de nuevo.';
                this.html = '';
            } finally {
                this.cargando = false;
            }
        }
    }
};
