// Vista de Selección de materias de Desideratas
// Fiel a v3/seleccion.php: paneles de profesores (solo jefe de departamento o
// admin), cursos con sus materias, y selección del profesor elegido, con
// hora modal, reordenado por arrastre, y los botones de v3 (estadísticas,
// ficha del profesor, preferencias, Excel, vista previa...)
const SeleccionView = {
    props: {
        usuario: {
            type: Object,
            required: true
        }
    },

    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12 d-flex align-items-center gap-3">
                    <h2 class="mb-0"><i class="bi bi-list-check me-2"></i>Selección de materias</h2>
                    <button class="btn btn-primary btn-sm" @click="ayuda()">Ayuda</button>
                </div>
            </div>

            <!-- v3: si el periodo de desideratas no está activo, los profesores no pueden elegir -->
            <div v-if="desactivada" class="alert alert-info">
                Opción deshabilitada en este momento. La selección de materias solo está disponible durante el periodo de desideratas.
            </div>

            <template v-else>
                <!-- Selectores -->
                <div class="row mb-3">
                    <div class="col-md-4" v-if="esAdmin">
                        <label class="form-label">Departamento</label>
                        <select class="form-select" v-model="idDepartamento" @change="cambiarDepartamento">
                            <option value="">-- Selecciona un departamento --</option>
                            <option v-for="d in departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Escenario</label>
                        <select class="form-select" v-model="idEscenario" @change="cambiarEscenario" :disabled="!idDepartamento">
                            <option value="">-- Selecciona un escenario --</option>
                            <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
                        </select>
                    </div>
                    <div class="col-md-4" v-if="esSuper">
                        <label class="form-label">Profesor</label>
                        <select class="form-select" v-model="idProfesor" @change="cargarSeleccion" :disabled="!idDepartamento">
                            <option value="">-- Selecciona un profesor --</option>
                            <option v-for="p in profesores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                        </select>
                    </div>
                </div>

                <div v-if="!idDepartamento" class="text-center text-muted py-4">
                    Selecciona un departamento para empezar.
                </div>

                <div v-else class="row g-3">
                    <!-- Panel de profesores (solo jefe de departamento o admin) -->
                    <div class="col-md-3" v-if="esSuper">
                        <div class="card shadow-sm h-100">
                            <div class="card-header"><h5 class="h6 mb-0"><i class="bi bi-people me-2"></i>Profesores</h5></div>
                            <div class="card-body">
                                <div class="mb-2 d-flex flex-wrap justify-content-center">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="filtroEspecialidad" id="filtroEspTodos" value="Todos" v-model="idEspecialidad" @change="cargarProfesores">
                                        <label class="form-check-label" for="filtroEspTodos">Todos</label>
                                    </div>
                                    <div v-for="esp in especialidades" :key="esp.id" class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="filtroEspecialidad" :id="'filtroEsp' + esp.id" :value="esp.id" v-model="idEspecialidad" @change="cargarProfesores">
                                        <label class="form-check-label" :for="'filtroEsp' + esp.id">{{ esp.descripcion }}</label>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mb-2">
                                    <button class="btn btn-sm btn-outline-secondary" title="Imprimir las fichas de todos los profesores" @click="imprimirTodos()">
                                        <i class="bi bi-printer"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" title="Imprimir las preferencias de horario de todos los profesores" @click="imprimirPreferenciasTodos()">
                                        <i class="bi bi-calendar-week"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" title="Borrar las selecciones de todos los profesores" @click="borrarTodas()">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </div>
                                <div v-for="p in profesores" :key="p.id"
                                     :class="['cursor-pointer d-flex justify-content-between align-items-center border rounded p-2 mb-2', p.id == idProfesor ? 'text-bg-primary' : '']"
                                     role="button" tabindex="0"
                                     @click="seleccionarProfesor(p)"
                                     @keyup.enter="seleccionarProfesor(p)">
                                    <div>{{ p.nombre }}</div>
                                    <span :class="['badge', claseHoras(p.horas)]">{{ p.horas }} h</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de cursos -->
                    <div :class="esSuper ? 'col-md-5' : 'col-md-6'">
                        <div class="card shadow-sm h-100">
                            <div class="card-header"><h5 class="h6 mb-0"><i class="bi bi-mortarboard me-2"></i>Cursos</h5></div>
                            <div class="card-body">
                                <p class="text-muted small">
                                    Haz clic en cada curso para ver sus asignaturas. El botón '+' añade la asignatura a la lista del profesor seleccionado.
                                </p>
                                <details v-for="c in cursosAgrupados" :key="c.idCurso">
                                    <summary class="fw-bold d-flex justify-content-between align-items-center">
                                        <span>{{ c.nombreCurso }}</span>
                                    </summary>
                                    <div v-for="m in c.materias" :key="m.idMateria"
                                         class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                                        <div class="d-flex align-items-center gap-1 flex-grow-1">
                                            <button v-if="!modoRuedaBloquea" class="btn btn-sm btn-outline-primary" title="Añadir a la selección" @click="elegirHoras(m)">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                            <span>{{ m.nombre }} ({{ m.horas }}h)</span>
                                            <span v-if="m.minNumProfesores > 0 || m.maxGruposProfesor > 0"
                                                  class="badge text-bg-info cursor-pointer" role="button" tabindex="0"
                                                  title="Restricciones de profesores"
                                                  @click="mostrarInfo(m)"
                                                  @keyup.enter="mostrarInfo(m)">?</span>
                                        </div>
                                        <span :class="['badge', 'cursor-pointer', claseElegidas(m)]" role="button" tabindex="0" @click="verProfesoresMateria(m)" @keyup.enter="verProfesoresMateria(m)">
                                            {{ m.elegidas }} / {{ m.cantidad }}
                                        </span>
                                    </div>
                                </details>
                                <div v-if="cursosAgrupados.length === 0" class="text-muted">
                                    No hay cursos disponibles.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de selección -->
                    <div :class="esSuper ? 'col-md-4' : 'col-md-6'">
                        <div class="card shadow-sm h-100">
                            <div class="card-header"><h5 class="h6 mb-0"><i class="bi bi-check2-square me-2"></i>Selección</h5></div>
                            <div class="card-body">
                                <p v-if="arrastrable" class="text-muted small">
                                    Puedes reordenar tus prioridades arrastrando las asignaturas entre ellas.
                                </p>
                                <div v-if="!idProfesor" class="text-muted">
                                    No hay profesor elegido.
                                </div>
                                <template v-else>
                                    <div v-for="(s, i) in selecciones" :key="s.id"
                                         :draggable="!modoRuedaBloquea"
                                         @dragstart="arrastrando = i"
                                         @dragover.prevent
                                         @drop="soltarEn(i)"
                                         :class="['d-flex justify-content-between align-items-center gap-2 border rounded p-2 mb-2', arrastrando === i ? 'bg-warning' : '']">
                                        <div class="flex-grow-1">{{ i + 1 }}. {{ s.nombre }} ({{ s.abrevCurso }}{{ s.mostrar ? ' ' + s.abrevGrupo : '' }}, {{ s.horas }}h)</div>
                                        <button v-if="!(s.asignada_directiva && !esSuper)" class="btn btn-sm btn-outline-danger" title="Quitar de la selección" @click="borrarSeleccion(s)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <div v-if="selecciones.length === 0" class="text-muted">
                                        No hay selecciones.
                                    </div>
                                    <div class="mt-2" v-if="selecciones.length > 0">
                                        <span class="badge bg-primary">Total: {{ totalHoras }} h</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 mt-3">
                                        <button class="btn btn-sm btn-outline-secondary" title="Vaciar la selección del profesor" @click="borrarToda()">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" title="Mostrar estadísticas y conflictos" @click="verEstadisticas()">
                                            <i class="bi bi-graph-up"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" title="Imprimir la ficha del profesor" @click="imprimirSeleccion()">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" title="Imprimir las preferencias de horario del profesor" @click="imprimirPreferencias()">
                                            <i class="bi bi-calendar-week"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" title="Generar hoja Excel con los datos introducidos" @click="generarExcel()">
                                            <i class="bi bi-file-earmark-excel"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" title="Vista general de las selecciones de todos los profesores" @click="vistaPrevia()">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" title="Actualizar el estado de la selección" @click="cargarTodo()">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Modal de horas de una materia -->
            <div class="modal fade" id="modalHoras" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Horas para la materia seleccionada</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">Horas para la materia seleccionada (por defecto, todas):</label>
                            <input type="number" min="1" class="form-control" v-model.number="materia.horas" :readonly="!materia.divisible">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="confirmarHoras">Enviar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de profesores que eligieron una materia -->
            <div class="modal fade" id="modalProfesoresMateria" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Profesores que han seleccionado la materia '{{ materiaNombre.nombre }}' de '{{ materiaNombre.curso }}'</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div v-for="n in nombresProfesoresMateria" :key="n" class="mb-1 fw-bold">{{ n }}</div>
                            <div v-if="nombresProfesoresMateria.length === 0" class="text-muted">Nadie ha seleccionado esta materia.</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    data() {
        return {
            desactivada: false,
            departamentos: [],
            idDepartamento: '',
            escenarios: [],
            idEscenario: '',
            especialidades: [],
            profesores: [],
            idProfesor: '',
            idEspecialidad: 'Todos',
            filasCursos: [],
            modoRueda: false,
            selecciones: [],
            arrastrando: -1,
            materia: { idMateria: 0, idGrupo: 0, horas: 0, divisible: true, especialidad: '' },
            materiaNombre: { nombre: '', curso: '' },
            nombresProfesoresMateria: [],
            modalHoras: null,
            modalMateria: null
        };
    },

    computed: {
        esSuper() {
            return this.usuario && (this.usuario.rol === 'admin' || this.usuario.rol === 'jefeDepartamento');
        },
        esAdmin() {
            return this.usuario && this.usuario.rol === 'admin';
        },
        // v3: si el escenario está en modo rueda y no hay permisos, no se puede reordenar ni elegir
        modoRuedaBloquea() {
            return this.modoRueda && !this.esSuper;
        },
        arrastrable() {
            return !this.modoRuedaBloquea;
        },
        totalHoras() {
            return this.selecciones.reduce((sum, s) => sum + parseInt(s.horas || 0), 0);
        },
        // Agrupa las filas planas de "listar_cursos" por curso, como hacía v3 en su HTML
        cursosAgrupados() {
            const grupos = [];
            for (const fila of this.filasCursos) {
                let grupo = grupos.find(g => g.idCurso === fila.idCurso);
                if (!grupo) {
                    grupo = { idCurso: fila.idCurso, nombreCurso: fila.nombreCurso, materias: [] };
                    grupos.push(grupo);
                }
                grupo.materias.push({
                    idMateria: fila.id,
                    nombre: fila.nombre,
                    horas: fila.horas,
                    divisible: !!fila.divisible,
                    especialidad: fila.idEspecialidad,
                    minNumProfesores: fila.min_num_profesores,
                    maxGruposProfesor: fila.max_grupos_profesor,
                    elegidas: fila.elegidas,
                    cantidad: fila.cantidad,
                    idGrupo: fila.idGrupo
                });
            }
            return grupos;
        },
        especialidadProfesor() {
            const p = this.profesores.find(x => x.id == this.idProfesor);
            return p ? p.idEspecialidad : '';
        }
    },

    mounted() {
        this.modalHoras = new bootstrap.Modal(document.getElementById('modalHoras'));
        this.modalMateria = new bootstrap.Modal(document.getElementById('modalProfesoresMateria'));
        if (this.esSuper) {
            this.inicializar();
        } else {
            // v3: si el periodo no está activo, el profesor no puede elegir
            this.comprobarActivada();
        }
    },

    methods: {
        // v3: si el periodo no está activo, el profesor no puede hacer selecciones
        async comprobarActivada() {
            try {
                const activaciones = await AppAPI.getActivaciones();
                if (activaciones && activaciones.desideratas === false) {
                    this.desactivada = true;
                    return;
                }
            } catch (error) {
                // Si falla, se sigue con la selección (igual que antes)
            }
            this.inicializar();
        },

        async inicializar() {
            if (this.esAdmin) {
                await this.cargarDepartamentos();
            } else if (this.usuario && this.usuario.departamentoUsuario) {
                this.idDepartamento = this.usuario.departamentoUsuario;
            }
            // v3: el jefe de departamento empieza con su propio nombre elegido
            if (!this.esAdmin && this.usuario && this.usuario.idUsuario) {
                this.idProfesor = this.usuario.idUsuario;
            }
            if (this.idDepartamento) {
                await this.cargarEscenarios();
            }
        },

        async cargarDepartamentos() {
            try {
                this.departamentos = await DepartamentosAPI.listar() || [];
            } catch (error) {
                // Si falla, se mantiene el listado anterior
            }
        },

        async cambiarDepartamento() {
            if (!this.idDepartamento) return;
            this.idEscenario = '';
            this.filasCursos = [];
            this.selecciones = [];
            if (this.usuario && this.usuario.idUsuario) {
                this.idProfesor = this.esAdmin ? '' : this.usuario.idUsuario;
            }
            await this.cargarEscenarios();
            this.cargarProfesores();
        },

        async cargarEscenarios() {
            try {
                this.escenarios = await SeleccionAPI.listar_escenarios(this.idDepartamento) || [];
                // Especialidades para el filtro del panel de profesores
                this.especialidades = await SeleccionAPI.listar_especialidades(this.idDepartamento) || [];
            } catch (error) {
                this.escenarios = [];
                this.especialidades = [];
            }
            this.idEscenario = '';
        },

        async cambiarEscenario() {
            if (!this.idEscenario) {
                this.filasCursos = [];
                this.selecciones = [];
                return;
            }
            await Promise.all([this.cargarCursos(), this.cargarSeleccion(), this.cargarProfesores()]);
        },

        async cargarCursos() {
            try {
                const data = await SeleccionAPI.listar_cursos(this.idDepartamento, this.idEscenario);
                this.filasCursos = data.filas || [];
                this.modoRueda = data.modoRueda === 1 || data.modoRueda === '1' || data.modoRueda === true;
            } catch (error) {
                this.filasCursos = [];
            }
        },

        async cargarProfesores() {
            if (!this.idDepartamento || !this.idEscenario) return;
            try {
                this.profesores = await SeleccionAPI.listar_profesores(this.idDepartamento, this.idEspecialidad, this.idEscenario) || [];
            } catch (error) {
                this.profesores = [];
            }
        },

        async cargarSeleccion() {
            if (!this.idProfesor || !this.idEscenario) {
                this.selecciones = [];
                return;
            }
            try {
                this.selecciones = await SeleccionAPI.listar_seleccion(this.idProfesor, this.idEscenario) || [];
            } catch (error) {
                this.selecciones = [];
            }
        },

        seleccionarProfesor(p) {
            this.idProfesor = p.id;
            this.cargarSeleccion();
        },

        claseHoras(horas) {
            if (horas < 17) return 'bg-warning';
            if (horas > 22) return 'bg-danger';
            return 'bg-success';
        },

        claseElegidas(m) {
            if (m.elegidas > m.cantidad) return 'bg-danger';
            if (m.elegidas < m.cantidad) return 'bg-warning';
            return 'bg-success';
        },

        // Modal para elegir las horas de una materia (v3/seleccionarHorasMateria)
        elegirHoras(m) {
            this.materia = {
                idMateria: m.idMateria,
                idGrupo: m.idGrupo,
                horas: m.horas,
                divisible: !!m.divisible,
                especialidad: m.especialidad
            };
            // v3: si la especialidad de la materia no es la del profesor, se pide confirmar
            if (this.especialidadProfesor && m.especialidad && this.especialidadProfesor != m.especialidad) {
                Avisos.confirmar(
                    'Especialidad distinta',
                    "La materia seleccionada no corresponde a la especialidad (" + this.especialidadProfesor + ") del profesor elegido. ¿Confirmas que quieres seleccionarla?"
                ).then(res => {
                    if (res.isConfirmed) this.modalHoras.show();
                });
            } else {
                this.modalHoras.show();
            }
        },

        async confirmarHoras() {
            try {
                await SeleccionAPI.insertar_seleccion({
                    idProfesor: this.idProfesor,
                    idMateria: this.materia.idMateria,
                    idGrupo: this.materia.idGrupo,
                    horas: this.materia.horas,
                    idEscenario: this.idEscenario
                });
                this.modalHoras.hide();
                await this.cargarTodo();
            } catch (error) {
                Avisos.error(error.message);
            }
        },

        // v3: popup con las restricciones de la materia
        mostrarInfo(m) {
            const min = m.minNumProfesores > 0 ? m.minNumProfesores : '-';
            const max = m.maxGruposProfesor > 0 ? m.maxGruposProfesor : '-';
            Swal.fire({
                title: 'Restricciones',
                text: "Esta asignatura necesita " + min + " profesores distintos, y cada uno puede elegir un máximo de " + max + " grupo(s).",
                icon: 'info'
            });
        },

        // v3: modal con los profesores que ya eligieron la materia
        async verProfesoresMateria(m) {
            const fila = this.filasCursos.find(x => x.id === m.idMateria);
            this.materiaNombre = {
                nombre: m.nombre,
                curso: fila ? fila.nombreCurso : ''
            };
            try {
                this.nombresProfesoresMateria = await SeleccionAPI.listar_profesores_materia(m.idMateria, m.idGrupo, this.idEscenario) || [];
            } catch (error) {
                this.nombresProfesoresMateria = [];
            }
            this.modalMateria.show();
        },

        borrarSeleccion(s) {
            Avisos.confirmar('¿Quitar de la selección?', '¿Seguro que deseas quitar la materia seleccionada de la lista?').then(async res => {
                if (res.isConfirmed) {
                    try {
                        await SeleccionAPI.borrar_seleccion(s.id);
                        await this.cargarTodo();
                    } catch (error) {
                        Avisos.error(error.message);
                    }
                }
            });
        },

        borrarToda() {
            Avisos.confirmar('¿Vaciar la selección?', '¿Seguro que deseas vaciar toda la selección del profesor?').then(async res => {
                if (res.isConfirmed) {
                    try {
                        await SeleccionAPI.borrar_toda_seleccion(this.idProfesor, this.idEscenario);
                        await this.cargarTodo();
                    } catch (error) {
                        Avisos.error(error.message);
                    }
                }
            });
        },

        // v3: borrar las selecciones de todos los profesores del escenario
        borrarTodas() {
            Avisos.confirmar(
                '¿Vaciar el escenario?',
                '¿Seguro que deseas eliminar todas las selecciones de todos los profesores para el escenario actual?',
                { boton: '<i class="bi bi-trash me-2"></i>Sí, vaciar', confirmButtonColor: '#dc3545' }
            ).then(async res => {
                if (res.isConfirmed) {
                    try {
                        await SeleccionAPI.borrar_todas_selecciones(this.idEscenario);
                        Avisos.exito('La lista de selecciones ahora está vacía');
                        await this.cargarTodo();
                    } catch (error) {
                        Avisos.error(error.message);
                    }
                }
            });
        },

        // Reordenado por arrastre (v3/ordenar_seleccion.php)
        soltarEn(destino) {
            if (this.arrastrando === -1 || this.arrastrando === destino) return;
            const movido = this.selecciones.splice(this.arrastrando, 1)[0];
            this.selecciones.splice(destino, 0, movido);
            this.arrastrando = -1;
            const ids = this.selecciones.map(s => s.id);
            SeleccionAPI.ordenar_seleccion(ids, this.idEscenario).catch((error) => {
                Avisos.error(error.message);
                this.cargarSeleccion();
            });
        },

        verEstadisticas() {
            this.$emit('navigate', 'estadisticas.php', {
                idDepartamento: this.idDepartamento,
                idEscenario: this.idEscenario
            });
        },

        // v3/imprimirSeleccion(true): ficha del profesor
        imprimirSeleccion() {
            window.open('../backend/pdf/pdf_desiderata.php?idProfesor=' + this.idProfesor + '&idEscenario=' + this.idEscenario, '_blank');
        },

        // v3/imprimirSeleccion(false): fichas de todos los profesores de la especialidad
        imprimirTodos() {
            window.open('../backend/pdf/pdf_desiderata.php?selEsp=' + encodeURIComponent(this.idEspecialidad) + '&idDepartamento=' + this.idDepartamento + '&idEscenario=' + this.idEscenario, '_blank');
        },

        // v3/imprimirPreferenciasSeleccion(true)
        imprimirPreferencias() {
            window.open('../backend/pdf/pdf_preferencias.php?idProfesor=' + this.idProfesor + '&idEscenario=' + this.idEscenario, '_blank');
        },

        // v3/imprimirPreferenciasSeleccion(false)
        imprimirPreferenciasTodos() {
            window.open('../backend/pdf/pdf_preferencias.php?selEsp=' + encodeURIComponent(this.idEspecialidad) + '&idDepartamento=' + this.idDepartamento, '_blank');
        },

        // v3/generarExcel
        generarExcel() {
            window.open('../backend/excel.php?idEscenario=' + this.idEscenario, '_blank');
        },

        // v3/vistaPrevia
        vistaPrevia() {
            this.$emit('navigate', 'historico.php', {
                idDepartamento: this.idDepartamento,
                idEscenario: this.idEscenario
            });
        },

        // v3/actualizar: recargar todo el estado
        async cargarTodo() {
            await Promise.all([this.cargarCursos(), this.cargarSeleccion(), this.cargarProfesores()]);
        },

        // Texto de ayuda fiel a v3/seleccion.php
        ayuda() {
            Swal.fire({
                title: 'Ayuda rápida',
                html: `
                    <p>Selecciona las opciones que desees del panel de <em>Cursos</em> haciendo clic en el botón + de cada opción para añadirla a tu selección (panel <em>Selección</em>). Automáticamente se calcularán las horas totales acumuladas. También puedes quitar un elemento de tu selección eligiéndolo en el panel <em>Selección</em> y pulsando el icono de la papelera junto al listado. Finalmente, puedes vaciar toda tu selección pulsando el icono correspondiente, junto a la papelera.</p>
                    <p><strong>IMPORTANTÍSIMO:</strong> la selección hecha por cada profesor en esta aplicación es meramente orientativa, sujeta a aprobación final por parte del departamento tras el claustro de desideratas. El hecho de elegir un módulo antes que otro compañero/a no da ninguna preferencia sobre él. A la hora de resolver posibles conflictos se tendrán en cuenta, por un lado, el orden de elección de cada profesor/a en función de su antigüedad en el cuerpo, y por otro, el orden de preferencia del módulo para cada profesor.</p>
                    <p><em>EJEMPLO</em>: si el primer profesor en elegir selecciona el módulo de <em>Despliegue de Aplicaciones Web</em> como segunda opción, y el octavo profesor lo elige como primera opción, tendría preferencia este octavo profesor, al ser su primera elección. En cambio, si el primer profesor lo marca también como primera elección, tendría preferencia este profesor, al estar por delante en el turno de elecciones.</p>
                `,
                showCloseButton: true
            });
        }
    }
};
