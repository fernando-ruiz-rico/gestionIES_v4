// Vista de Materias
//
// Fiel a v3 (modales/materias.php, modales/materias_grupos.php,
// modales/competencias_materia.php y js/materias.js):
//  - El admin ve el desplegable de curso (primer elemento) y las materias
//    del curso elegido.
//  - Cada materia tiene botones para: asociar competencias, gestionar los
//    datos por grupo (si el curso tiene grupos), editar y borrar.
//  - El formulario de alta/edición pide todos los datos de v3 (código/nombre
//    oficial, ECTS, horas anuales, tipo, departamento, especialidad en
//    cascada, casillas e información de referencia).
//  - Las competencias solo las gestiona el admin (fiel a v3: los endpoints
//    de asociar/borrar son solo-admin).

const MateriasView = {
    template: `
        <div class="container-fluid py-4">
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h2><i class="bi bi-book"></i> Materias</h2>
                    <button v-if="puedeEditar" class="btn btn-primary" @click="abrirModal()">
                        <i class="bi bi-plus-lg"></i> Nueva Materia
                    </button>
                </div>
            </div>

            <!-- Desplegable de curso: el primer elemento, fiel a v3 (materias.php) -->
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label" for="cursoMateria">Curso</label>
                    <select id="cursoMateria" class="form-select col-md-6" v-model="selCurso" @change="cargar()">
                        <option :value="0">--Selecciona un curso--</option>
                        <option v-for="c in cursos" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="m in materias" :key="m.id">
                                        <td>{{ m.id }}</td>
                                        <td>{{ m.nombre }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-secondary me-1" title="Asociar competencias" @click="abrirCompetencias(m)">
                                                <i class="bi bi-lightning"></i>
                                            </button>
                                            <button v-if="cursoTieneGrupos" class="btn btn-sm btn-outline-primary me-1" title="Gestionar datos por grupo" @click="abrirGrupos(m)">
                                                <i class="bi bi-people"></i>
                                            </button>
                                            <button v-if="puedeEditar" class="btn btn-sm btn-outline-primary me-1" title="Editar" @click="editar(m)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button v-if="puedeEditar" class="btn btn-sm btn-outline-danger" title="Borrar" @click="eliminar(m)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="!selCurso">
                                        <td colspan="3" class="text-center text-muted py-4">Selecciona un curso para ver sus materias</td>
                                    </tr>
                                    <tr v-else-if="!materias.length">
                                        <td colspan="3" class="text-center text-muted py-4">Sin materias</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: formulario de alta/edición de materia -->
            <div class="modal fade" id="modalMateria" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ esEdicion ? 'Editar' : 'Nueva' }} Materia</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" class="form-control" v-model="form.nombre" required>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Código oficial</label>
                                    <input type="text" class="form-control" v-model="form.codigoOficial">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nombre oficial</label>
                                    <input type="text" class="form-control" v-model="form.nombreOficial">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Créditos ECTS</label>
                                    <input type="number" min="0" class="form-control" v-model="form.creditosECTS">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Horas anuales</label>
                                    <input type="number" min="0" class="form-control" v-model="form.horasAnuales">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tipo de materia</label>
                                    <select class="form-select" v-model="form.tipo">
                                        <option value="TUTORIA">Tutoría</option>
                                        <option value="INGLES">Inglés</option>
                                        <option value="OTRA">Otras materias</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Departamento</label>
                                    <select class="form-select" v-model="form.idDepartamento" @change="form.idEspecialidad = ''">
                                        <option :value="0">--Selecciona un departamento--</option>
                                        <option v-for="d in departamentos" :key="d.id" :value="d.id">{{ d.nombre }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Especialidad</label>
                                    <select class="form-select" v-model="form.idEspecialidad">
                                        <option :value="''">{{ form.idDepartamento ? '--Selecciona una especialidad--' : 'Selecciona primero un departamento' }}</option>
                                        <option v-for="e in especialidadesDelDepto" :key="e.id" :value="e.id">{{ e.descripcion }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check mb-1">
                                    <input type="checkbox" class="form-check-input" id="chkCompMat" v-model="form.computables_horas_grupo">
                                    <label class="form-check-label" for="chkCompMat">Computables para las horas semanales del grupo</label>
                                </div>
                                <div class="form-check mb-1">
                                    <input type="checkbox" class="form-check-input" id="chkAsigDir" v-model="form.asignada_directiva">
                                    <label class="form-check-label" for="chkAsigDir">Asignada por el equipo directivo</label>
                                </div>
                                <div class="form-check mb-1">
                                    <input type="checkbox" class="form-check-input" id="chkProg" v-model="form.tiene_programacion">
                                    <label class="form-check-label" for="chkProg">Tiene programación didáctica asociada</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="chkDiv" v-model="form.divisible">
                                    <label class="form-check-label" for="chkDiv">Divisible</label>
                                </div>
                            </div>
                            <p class="fw-bold">Información de referencia para la materia (a concretar en cada grupo)</p>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Cantidad de unidades por grupo</label>
                                    <input type="number" min="0" class="form-control" v-model="form.cantidad">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Horas / semana</label>
                                    <input type="number" min="0" class="form-control" v-model="form.horas">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Horas complementarias / semana</label>
                                    <input type="number" min="0" class="form-control" v-model="form.horas_complementarias">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mín. nº de profesores (0 para no limitar)</label>
                                    <input type="number" min="0" class="form-control" v-model="form.min_num_profesores">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Máx. nº de grupos por profesor (0 para no limitar)</label>
                                    <input type="number" min="0" class="form-control" v-model="form.max_grupos_profesor">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" @click="guardar">
                                {{ esEdicion ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: datos de la materia por grupo (fiel a v3) -->
            <div class="modal fade" id="modalMateriasGrupos" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Datos de {{ gruposModal.nombreMateria }} por grupo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm" @click="importarDatos">
                                    <i class="bi bi-download"></i> Importar datos generales de la materia
                                </button>
                            </div>
                            <div v-for="g in gruposModal.grupos" :key="g.id" class="card mb-2">
                                <div class="card-header py-1">{{ g.nombre }}</div>
                                <div class="card-body">
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label">Cantidad de unidades por grupo</label>
                                            <input type="number" min="0" class="form-control" v-model="g.cantidad">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Mín. profesores (0 para no limitar)</label>
                                            <input type="number" min="0" class="form-control" v-model="g.min_num_profesores">
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label">Horas / semana</label>
                                            <input type="number" min="0" class="form-control" v-model="g.horas">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Máx. grupos por profesor (0 para no limitar)</label>
                                            <input type="number" min="0" class="form-control" v-model="g.max_grupos_profesor">
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label">Horas complementarias / semana</label>
                                            <input type="number" min="0" class="form-control" v-model="g.horas_complementarias">
                                        </div>
                                        <div class="col-md-6 d-flex align-items-end">
                                            <button type="button" class="btn btn-success btn-sm me-1" @click="guardarGrupo(g)"><i class="bi bi-save"></i> Guardar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal: competencias asociadas a la materia (fiel a v3) -->
            <div class="modal fade" id="modalCompetencias" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Competencias asociadas a {{ compModal.nombreMateria }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p v-if="!compModal.asociadas.length" class="text-muted">Sin competencias asociadas.</p>
                            <p v-for="c in compModal.asociadas" :key="c.id" class="mb-1">
                                <button type="button" class="btn btn-sm btn-outline-danger me-2" @click="borrarCompetencia(c.id)"><i class="bi bi-trash"></i></button>
                                {{ c.codigo }} - {{ c.texto }}
                            </p>
                            <p class="fw-bold mt-3">Asociar nuevas competencias</p>
                            <div class="d-flex gap-2 align-items-center">
                                <select class="form-select flex-grow-1" v-model="compModal.selCompetencia">
                                    <option :value="0">--Selecciona una competencia--</option>
                                    <option v-for="o in compModal.opciones" :key="o.id" :value="o.id">{{ o.codigo }} - {{ o.texto }}</option>
                                </select>
                                <button type="button" class="btn btn-primary" @click="asociarCompetencia">Añadir</button>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `,

    props: {
        usuario: {
            type: Object,
            default: null
        }
    },

    data() {
        return {
            selCurso: 0,
            cursos: [],
            materias: [],
            gruposPorCurso: {},
            departamentos: [],
            especialidades: [],
            form: {
                id: 0, nombre: '', idCurso: 0, codigoOficial: '', nombreOficial: '',
                creditosECTS: '', horasAnuales: '', tipo: 'OTRA', idDepartamento: 0,
                idEspecialidad: '', computables_horas_grupo: true, asignada_directiva: false,
                tiene_programacion: true, divisible: true, cantidad: 1, horas: '',
                horas_complementarias: '', min_num_profesores: 0, max_grupos_profesor: 0
            },
            esEdicion: false,
            gruposModal: { idMateria: 0, idCurso: 0, nombreCurso: '', nombreMateria: '', general: null, grupos: [] },
            compModal: { idMateria: 0, nombreMateria: '', asociadas: [], opciones: [], selCompetencia: 0 },
            modal: null,
            modalGrupos: null,
            modalCompetencias: null
        };
    },

    computed: {
        esAdmin() {
            return this.usuario && this.usuario.rol === 'admin';
        },
        esJefe() {
            return this.usuario && this.usuario.rol === 'jefeDepartamento';
        },
        // guardar/eliminar y los datos por grupo son jefe/admin (fiel a v3)
        puedeEditar() {
            return this.esAdmin || this.esJefe;
        },
        cursoTieneGrupos() {
            return (this.gruposPorCurso[this.selCurso] || 0) > 0;
        },
        especialidadesDelDepto() {
            if (!this.form.idDepartamento) return [];
            return this.especialidades.filter(e => e.idDepartamento == this.form.idDepartamento);
        }
    },

    mounted() {
        this.cargarCursos();
        this.cargarGrupos();
        this.cargarDepartamentos();
        this.cargarEspecialidades();
        this.modal = new bootstrap.Modal(document.getElementById('modalMateria'));
        this.modalGrupos = new bootstrap.Modal(document.getElementById('modalMateriasGrupos'));
        this.modalCompetencias = new bootstrap.Modal(document.getElementById('modalCompetencias'));
    },

    methods: {
        async cargar() {
            if (!this.selCurso) {
                this.materias = [];
                return;
            }
            const result = await MateriasAPI.listar(this.selCurso);
            this.materias = result.success ? result.data : [];
        },

        async cargarCursos() {
            const result = await CursosAPI.listar();
            this.cursos = result.success ? result.data : [];
        },

        // Cuántos grupos tiene cada curso (para el botón "datos por grupo", fiel a v3)
        async cargarGrupos() {
            try {
                const result = await GruposAPI.listar();
                if (result.success) {
                    const m = {};
                    result.data.forEach(g => { m[g.idCurso] = (m[g.idCurso] || 0) + 1; });
                    this.gruposPorCurso = m;
                }
            } catch (e) {
                this.gruposPorCurso = {};
            }
        },

        async cargarDepartamentos() {
            try {
                const response = await fetch('../backend/api/departamentos/listar.php', { credentials: 'include' });
                const data = await response.json();
                this.departamentos = (data.success && Array.isArray(data.data)) ? data.data : [];
            } catch (e) {
                this.departamentos = [];
            }
        },

        async cargarEspecialidades() {
            try {
                const result = await EspecialidadesAPI.listar();
                this.especialidades = result.success ? result.data : [];
            } catch (e) {
                this.especialidades = [];
            }
        },

        // Alta: exige tener un curso elegido, fiel a v3
        abrirModal() {
            if (!this.selCurso) {
                Avisos.aviso('Debes seleccionar un curso primero');
                return;
            }
            this.form = {
                id: 0, nombre: '', idCurso: this.selCurso, codigoOficial: '', nombreOficial: '',
                creditosECTS: '', horasAnuales: '', tipo: 'OTRA', idDepartamento: 0,
                idEspecialidad: '', computables_horas_grupo: true, asignada_directiva: false,
                tiene_programacion: true, divisible: true, cantidad: 1, horas: '',
                horas_complementarias: '', min_num_profesores: 0, max_grupos_profesor: 0
            };
            this.esEdicion = false;
            this.modal.show();
        },

        // Edición: mapea las columnas de BD a las claves del formulario (fiel a v3)
        editar(m) {
            this.form = {
                id: m.id,
                nombre: m.nombre,
                idCurso: m.idCurso,
                codigoOficial: m.codigo_oficial || '',
                nombreOficial: m.nombre_oficial || '',
                creditosECTS: (m.creditos_ects == null) ? '' : m.creditos_ects,
                horasAnuales: (m.horas_anuales == null) ? '' : m.horas_anuales,
                tipo: (m.tipo || 'OTRA'),
                idDepartamento: (m.idDepartamento ? m.idDepartamento : 0),
                idEspecialidad: (m.idEspecialidad ? m.idEspecialidad : ''),
                computables_horas_grupo: (m.computables_horas_grupo == 1),
                asignada_directiva: (m.asignada_directiva == 1),
                tiene_programacion: (m.tiene_programacion == 1),
                divisible: (m.divisible == 1),
                cantidad: m.cantidad,
                horas: m.horas,
                horas_complementarias: m.horas_complementarias,
                min_num_profesores: m.min_num_profesores,
                max_grupos_profesor: m.max_grupos_profesor
            };
            this.esEdicion = true;
            this.modal.show();
        },

        async guardar() {
            const result = await MateriasAPI.guardar(this.form);

            if (result.success) {
                Avisos.exito('Éxito', result.message);
                this.modal.hide();
                this.cargar();
            } else {
                Avisos.error(result.error || 'No se pudo guardar la materia');
            }
        },

        eliminar(m) {
            Avisos.confirmar('¿Eliminar materia?', m.nombre).then(async (res) => {
                if (res.isConfirmed) {
                    const result = await MateriasAPI.eliminar(m.id);
                    if (result.success) {
                        Avisos.exito('Materia eliminada');
                        this.cargar();
                    } else {
                        Avisos.error(result.error);
                    }
                }
            });
        },

        // Datos por grupo (fiel a v3: cargar_forms_materias_grupos.php)
        async abrirGrupos(m) {
            const result = await MateriasAPI.listar_materias_grupos(m.id, m.idCurso);
            if (!result.success || !result.data) {
                Avisos.error('No se pudieron cargar los datos de los grupos');
                return;
            }
            const d = result.data;
            this.gruposModal = {
                idMateria: d.idMateria,
                idCurso: d.idCurso,
                nombreCurso: d.nombreCurso,
                nombreMateria: d.nombreMateria,
                general: d.general,
                grupos: d.grupos
            };
            this.modalGrupos.show();
        },

        // Rellena todos los grupos con los datos generales (sin guardar, fiel a v3)
        importarDatos() {
            if (!this.gruposModal.general) return;
            const gen = this.gruposModal.general;
            this.gruposModal.grupos.forEach(g => {
                g.cantidad = gen.cantidad;
                g.horas = gen.horas;
                g.horas_complementarias = gen.horas_complementarias;
                g.min_num_profesores = gen.min_num_profesores;
                g.max_grupos_profesor = gen.max_grupos_profesor;
            });
        },

        async guardarGrupo(g) {
            const result = await MateriasAPI.insertar_materia_grupo({
                idMateria: this.gruposModal.idMateria,
                idGrupo: g.id,
                cantidad: g.cantidad,
                horas: g.horas,
                horas_complementarias: g.horas_complementarias,
                min_num_profesores: g.min_num_profesores,
                max_grupos_profesor: g.max_grupos_profesor
            });
            if (result.success) {
                Avisos.exito('Grupo guardado', 'Los datos del grupo se han actualizado');
            } else {
                Avisos.error(result.error || 'No se pudieron guardar los datos del grupo');
            }
        },

        // Competencias (fiel a v3: cargar_competencias_materia.php)
        async abrirCompetencias(m) {
            const result = await MateriasAPI.competencias_listar(m.id);
            if (!result.success || !result.data) {
                Avisos.error('No se pudieron cargar las competencias');
                return;
            }
            const d = result.data;
            this.compModal = {
                idMateria: d.idMateria,
                nombreMateria: d.nombreMateria,
                asociadas: d.asociadas,
                opciones: d.opciones,
                selCompetencia: 0
            };
            this.modalCompetencias.show();
        },

        // Recarga la lista de competencias tras un cambio (fiel a v3)
        async recargarCompetencias() {
            const result = await MateriasAPI.competencias_listar(this.compModal.idMateria);
            if (result.success && result.data) {
                this.compModal.asociadas = result.data.asociadas;
                this.compModal.opciones = result.data.opciones;
            }
        },

        async asociarCompetencia() {
            if (!this.compModal.selCompetencia) {
                Avisos.aviso('Selecciona una competencia');
                return;
            }
            const result = await MateriasAPI.competencias_asociar(this.compModal.idMateria, this.compModal.selCompetencia);
            if (result.success) {
                Avisos.exito('Competencia asociada');
                this.compModal.selCompetencia = 0;
                this.recargarCompetencias();
            } else {
                Avisos.error(result.error || 'No se pudo asociar la competencia');
            }
        },

        async borrarCompetencia(idCompetencia) {
            const result = await MateriasAPI.competencias_borrar(this.compModal.idMateria, idCompetencia);
            if (result.success) {
                Avisos.exito('Competencia desvinculada');
                this.recargarCompetencias();
            } else {
                Avisos.error(result.error || 'No se pudo desvincular la competencia');
            }
        }
    }
};
