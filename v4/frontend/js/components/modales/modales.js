// Fase 9 — Modales reutilizables
// Fiel a v3 (modales/): en v3 cada módulo cargaba por AJAX su modal HTML
// (modales/materia.php, modales/mensaje.php, etc.). En v4 (SPA con Vue 3) los
// modales de cada módulo se definen inline en sus vistas; aquí se aportan los
// modales genéricos reutilizables que sustituyen a v3 "modales/mensaje.php"
// y a los modales de confirmación genérica.

// Componente reutilizable de confirmación (sí/no), equivalente a las ventanas
// de confirmación de v3 que se disparaban antes de borrar/insertar.
const ModalConfirmacion = {
    props: {
        titulo: { type: String, default: 'Confirmar' },
        mensaje: { type: String, default: '' },
        textoConfirmar: { type: String, default: 'Sí, continuar' },
        textoCancelar: { type: String, default: 'Cancelar' },
        mostrar: { type: Boolean, default: false }
    },
    emits: ['confirmar', 'cancelar'],
    template: `
        <div class="modal fade" :class="{ show: mostrar, 'd-block': mostrar, 'modal-abierto': mostrar }" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ titulo }}</h5>
                    </div>
                    <div class="modal-body">
                        <p v-if="mensaje">{{ mensaje }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="$emit('cancelar')">{{ textoCancelar }}</button>
                        <button type="button" class="btn btn-primary" @click="$emit('confirmar')">{{ textoConfirmar }}</button>
                    </div>
                </div>
            </div>
        </div>
    `
};

// Componente reutilizable de mensaje (equivalente a v3 "modales/mensaje.php"),
// muestra un texto y se cierra.
const ModalMensaje = {
    props: {
        titulo: { type: String, default: 'Aviso' },
        mensaje: { type: String, default: '' },
        tipo: { type: String, default: 'info' }, // info | success | danger
        mostrar: { type: Boolean, default: false }
    },
    emits: ['cerrar'],
    template: `
        <div class="modal fade" :class="{ show: mostrar, 'd-block': mostrar, 'modal-abierto': mostrar }" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ titulo }}</h5>
                    </div>
                    <div class="modal-body">
                        <div class="alert" :class="'alert-' + tipo" role="alert">{{ mensaje }}</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" @click="$emit('cerrar')">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    `
};
